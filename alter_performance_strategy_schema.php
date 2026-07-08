<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db_config.php';

function migrationMessage(string $status, string $message): string
{
    return '[' . strtoupper($status) . '] ' . $message;
}

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    if (!$stmt) {
        throw new RuntimeException('Column check prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return ((int)($row['total'] ?? 0)) > 0;
}

function indexExists(mysqli $conn, string $table, string $index): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND INDEX_NAME = ?"
    );
    if (!$stmt) {
        throw new RuntimeException('Index check prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return ((int)($row['total'] ?? 0)) > 0;
}

function runPerformanceStrategyMigration(): array
{
    $messages = [];
    $conn = get_db_connection();
    $table = 'performance_returns';
    $strategyKeyColumn = 'strategy_key';
    $strategyIndex = 'idx_active_strategy_month_order';

    if (!columnExists($conn, $table, $strategyKeyColumn)) {
        $sql = "
            ALTER TABLE performance_returns
            ADD COLUMN strategy_key VARCHAR(32) NOT NULL DEFAULT 'fusion' AFTER id
        ";
        if (!$conn->query($sql)) {
            throw new RuntimeException('Failed to add strategy_key column: ' . $conn->error);
        }
        $messages[] = migrationMessage('ok', 'Added strategy_key column.');
    } else {
        $messages[] = migrationMessage('skip', 'strategy_key column already exists.');
    }

    $updates = [
        "UPDATE performance_returns
         SET strategy_key = 'fusion'
         WHERE strategy = 'PlusWealth Fusion'
            OR strategy LIKE 'Benchmark: NSE Multi Asset Index 2%'",
        "UPDATE performance_returns
         SET strategy_key = 'catalyst'
         WHERE strategy = 'PlusWealth Catalyst'
            OR strategy = 'Benchmark: NIFTY 500 TRI'",
    ];

    foreach ($updates as $sql) {
        if (!$conn->query($sql)) {
            throw new RuntimeException('Failed to update existing strategy keys: ' . $conn->error);
        }
    }
    $messages[] = migrationMessage('ok', 'Existing rows mapped to fusion/catalyst strategy keys.');

    if (!indexExists($conn, $table, $strategyIndex)) {
        $sql = "
            CREATE INDEX idx_active_strategy_month_order
            ON performance_returns (is_active, strategy_key, month_year, display_order)
        ";
        if (!$conn->query($sql)) {
            throw new RuntimeException('Failed to create strategy index: ' . $conn->error);
        }
        $messages[] = migrationMessage('ok', 'Added idx_active_strategy_month_order index.');
    } else {
        $messages[] = migrationMessage('skip', 'idx_active_strategy_month_order index already exists.');
    }

    return $messages;
}

$isCli = (PHP_SAPI === 'cli');

try {
    $messages = runPerformanceStrategyMigration();

    if ($isCli) {
        foreach ($messages as $message) {
            echo $message . PHP_EOL;
        }
        exit(0);
    }

    header('Content-Type: text/plain; charset=UTF-8');
    echo "Performance strategy schema migration\n";
    echo "=====================================\n";
    foreach ($messages as $message) {
        echo $message . "\n";
    }
} catch (Throwable $e) {
    if ($isCli) {
        fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '[ERROR] ' . $e->getMessage() . "\n";
}

