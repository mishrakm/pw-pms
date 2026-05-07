<?php
/**
 * Setup script for key_metrics table
 * Run once via browser: http://yoursite.com/setup_key_metrics.php
 */

require_once __DIR__ . '/includes/db_config.php';

$success = false;
$error = '';
$output = '';

try {
  $conn = get_db_connection();

  // Create table
  $create_sql = "CREATE TABLE IF NOT EXISTS key_metrics (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    metric_key VARCHAR(100) NOT NULL UNIQUE,
    metric_label VARCHAR(255) NOT NULL,
    metric_value VARCHAR(100) NOT NULL,
    benchmark_value VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_metric_key (metric_key)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

  if (!$conn->query($create_sql)) {
    throw new Exception('Failed to create table: ' . $conn->error);
  }
  $output .= "✓ Table created successfully\n";

  // Delete existing data
  $conn->query("DELETE FROM key_metrics WHERE metric_key = 'max_drawdown'");
  $output .= "✓ Cleared existing data\n";

  // Insert initial data
  $insert_sql = "INSERT INTO key_metrics (metric_key, metric_label, metric_value, benchmark_value, is_active)
                 VALUES ('max_drawdown', 'Max drawdown', '−5.07%', '−5.71%', 1)";
  
  if (!$conn->query($insert_sql)) {
    throw new Exception('Failed to insert data: ' . $conn->error);
  }
  $output .= "✓ Initial data inserted\n";

  $success = true;
  $output .= "\n✓ Setup complete! You can now delete this file.";

} catch (Throwable $e) {
  $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Setup - Key Metrics</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: #0a0a0a;
      color: #fff;
      padding: 40px 20px;
      margin: 0;
    }
    .container {
      max-width: 500px;
      margin: 0 auto;
      background: #1a1a1a;
      padding: 30px;
      border-radius: 8px;
    }
    h1 {
      font-size: 24px;
      margin: 0 0 20px 0;
      text-align: center;
    }
    .success {
      background: rgba(81, 207, 102, 0.1);
      border-left: 4px solid #51cf66;
      color: #51cf66;
      padding: 15px;
      border-radius: 4px;
      font-family: "Courier New", monospace;
      white-space: pre-wrap;
      word-wrap: break-word;
      font-size: 14px;
      line-height: 1.6;
    }
    .error {
      background: rgba(255, 107, 107, 0.1);
      border-left: 4px solid #ff6b6b;
      color: #ff6b6b;
      padding: 15px;
      border-radius: 4px;
      font-family: "Courier New", monospace;
      white-space: pre-wrap;
      word-wrap: break-word;
      font-size: 14px;
      line-height: 1.6;
    }
    .note {
      margin-top: 20px;
      padding: 12px;
      background: rgba(70, 130, 180, 0.1);
      border-left: 4px solid #4682b4;
      color: #aaa;
      font-size: 13px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Setup - Key Metrics Table</h1>
    
    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($output) ?></div>
      <div class="note">
        You can now:
        <br>• Visit <strong>/admin_metrics.php</strong> to manage metrics
        <br>• Delete this file (setup_key_metrics.php)
      </div>
    <?php else: ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
