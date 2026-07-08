<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db_config.php';

function runSetup(): array
{
    $messages = [];

    $conn = get_db_connection();

    $createTableSql = "
        CREATE TABLE IF NOT EXISTS performance_returns (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            strategy_key VARCHAR(32) NOT NULL DEFAULT 'fusion',
            month_year DATE NOT NULL,
            strategy VARCHAR(500) NOT NULL,
            one_month VARCHAR(32) DEFAULT NULL,
            three_month VARCHAR(32) DEFAULT NULL,
            six_month VARCHAR(32) DEFAULT NULL,
            one_year VARCHAR(32) DEFAULT NULL,
            two_year VARCHAR(32) DEFAULT NULL,
            three_year VARCHAR(32) DEFAULT NULL,
            four_year VARCHAR(32) DEFAULT NULL,
            five_year VARCHAR(32) DEFAULT NULL,
            since_inception VARCHAR(32) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_active_strategy_month_order (is_active, strategy_key, month_year, display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    if (!$conn->query($createTableSql)) {
        throw new RuntimeException('Table creation failed: ' . $conn->error);
    }

    $messages[] = 'Table performance_returns is ready.';

    $strategyKeyColumnCheckSql = "
        SELECT COUNT(*) AS total_columns
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'performance_returns'
          AND COLUMN_NAME = 'strategy_key'
    ";
    $strategyKeyColumnCheckResult = $conn->query($strategyKeyColumnCheckSql);
    if (!$strategyKeyColumnCheckResult) {
        throw new RuntimeException('Unable to validate strategy_key column: ' . $conn->error);
    }

    $strategyKeyColumnRow = $strategyKeyColumnCheckResult->fetch_assoc();
    $hasStrategyKeyColumn = ((int)($strategyKeyColumnRow['total_columns'] ?? 0)) > 0;

    if (!$hasStrategyKeyColumn) {
        $alterSql = "
            ALTER TABLE performance_returns
            ADD COLUMN strategy_key VARCHAR(32) NOT NULL DEFAULT 'fusion' AFTER id
        ";

        if (!$conn->query($alterSql)) {
            throw new RuntimeException('Failed to add strategy_key column: ' . $conn->error);
        }

        $messages[] = 'Added strategy_key column for strategy-specific performance.';
    }

    $columnCheckSql = "
        SELECT COUNT(*) AS total_columns
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'performance_returns'
          AND COLUMN_NAME = 'month_year'
    ";
    $columnCheckResult = $conn->query($columnCheckSql);
    if (!$columnCheckResult) {
        throw new RuntimeException('Unable to validate month_year column: ' . $conn->error);
    }

    $columnRow = $columnCheckResult->fetch_assoc();
    $hasMonthYearColumn = ((int)($columnRow['total_columns'] ?? 0)) > 0;

    if (!$hasMonthYearColumn) {
        $alterSql = "
            ALTER TABLE performance_returns
            ADD COLUMN month_year DATE NOT NULL DEFAULT '2026-01-01' AFTER id
        ";

        if (!$conn->query($alterSql)) {
            throw new RuntimeException('Failed to add month_year column: ' . $conn->error);
        }

        $messages[] = 'Added month_year column for existing table.';
    }

    $indexCheckSql = "
        SELECT COUNT(*) AS total_indexes
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'performance_returns'
          AND INDEX_NAME = 'idx_active_strategy_month_order'
    ";
    $indexCheckResult = $conn->query($indexCheckSql);
    if (!$indexCheckResult) {
        throw new RuntimeException('Unable to validate index: ' . $conn->error);
    }

    $indexRow = $indexCheckResult->fetch_assoc();
    $hasMonthIndex = ((int)($indexRow['total_indexes'] ?? 0)) > 0;

    if (!$hasMonthIndex) {
        if (!$conn->query('CREATE INDEX idx_active_strategy_month_order ON performance_returns (is_active, strategy_key, month_year, display_order)')) {
            throw new RuntimeException('Failed to create strategy month index: ' . $conn->error);
        }

        $messages[] = 'Added index idx_active_strategy_month_order.';
    }

    $countResult = $conn->query('SELECT COUNT(*) AS total_rows FROM performance_returns');
    if (!$countResult) {
        throw new RuntimeException('Unable to check existing rows: ' . $conn->error);
    }

    $row = $countResult->fetch_assoc();
    $totalRows = (int)($row['total_rows'] ?? 0);

    if ($totalRows === 0) {
        $insertSql = "
            INSERT INTO performance_returns (
                month_year,
                strategy_key,
                strategy,
                one_month,
                three_month,
                six_month,
                one_year,
                two_year,
                three_year,
                four_year,
                five_year,
                since_inception,
                is_active,
                display_order
            ) VALUES
            (
                '2026-01-01',
                'fusion',
                'PlusWealth Fusion',
                '4.77',
                '3.5',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '7.95%',
                1,
                1
            ),
            (
                '2026-01-01',
                'fusion',
                'Benchmark: NSE Multi Asset Index 2\n(50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)',
                '0.65',
                '-0.76',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '1.51%',
                1,
                2
            )
        ";

        if (!$conn->query($insertSql)) {
            throw new RuntimeException('Seed insert failed: ' . $conn->error);
        }

        $messages[] = 'Seed rows inserted (2 records).';
    } else {
        $messages[] = 'Seed skipped (table already has data).';
    }

    return $messages;
}

$isCli = (PHP_SAPI === 'cli');

try {
    $messages = runSetup();

    if ($isCli) {
        foreach ($messages as $message) {
            echo '[OK] ' . $message . PHP_EOL;
        }
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        foreach ($messages as $message) {
            echo '[OK] ' . $message . "\n";
        }
    }
} catch (Throwable $e) {
    if ($isCli) {
        fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }

    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    echo '[ERROR] ' . $e->getMessage();
}
