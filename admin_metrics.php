<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

$authenticated = false;
$error_msg = '';
$success_msg = '';
$metrics = [];

session_start();

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  session_destroy();
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  exit;
}

// Check authentication
if (!isset($_SESSION['admin_authenticated'])) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
      $_SESSION['admin_authenticated'] = true;
      $_SESSION['admin_time'] = time();
      $authenticated = true;
    } else {
      $error_msg = 'Invalid password';
    }
  }
} else {
  $authenticated = true;
}

try {
  $conn = get_db_connection();

  // Handle form submission
  if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_metrics') {
      $annualized = trim($_POST['annualized_return'] ?? '');
      $nifty = trim($_POST['nifty_return'] ?? '');
      $maxDrawdown = trim($_POST['max_drawdown_value'] ?? '');
      $benchmarkDrawdown = trim($_POST['benchmark_value'] ?? '');
      $latestDate = trim($_POST['latest_data_date'] ?? '');

      $updates = [
        ['annualized_return', $annualized, null],
        ['nifty_return', $nifty, null],
        ['max_drawdown', $maxDrawdown, $benchmarkDrawdown],
        ['latest_data_date', $latestDate, null],
      ];

      $stmt = $conn->prepare(
        "INSERT INTO key_metrics (metric_key, metric_label, metric_value, benchmark_value, is_active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
           metric_label = VALUES(metric_label),
           metric_value = VALUES(metric_value),
           benchmark_value = VALUES(benchmark_value),
           is_active = 1"
      );

      if (!$stmt) {
        throw new Exception('Failed to prepare metric update statement: ' . $conn->error);
      }

      $labels = [
        'annualized_return' => 'Annualized return (live, since inception)',
        'nifty_return' => 'Nifty return (benchmark)',
        'max_drawdown' => 'Max drawdown',
        'latest_data_date' => 'Latest data date',
      ];

      foreach ($updates as $update) {
        $key = $update[0];
        $value = $update[1];
        $benchmark = $update[2];
        $label = $labels[$key];
        $stmt->bind_param('ssss', $key, $label, $value, $benchmark);
        if (!$stmt->execute()) {
          throw new Exception('Failed to update ' . $key . ': ' . $stmt->error);
        }
      }
      $stmt->close();
      $success_msg = 'Metrics updated successfully';
    }
  }

  // Fetch current metrics
  if ($authenticated) {
    $result = $conn->query(
      "SELECT metric_key, metric_label, metric_value, benchmark_value, is_active
       FROM key_metrics
       ORDER BY metric_key"
    );
    if ($result instanceof mysqli_result) {
      while ($row = $result->fetch_assoc()) {
        $metrics[] = $row;
      }
    }
  }
} catch (Throwable $e) {
  $error_msg = 'Database error: ' . $e->getMessage();
}

if (!$authenticated) {
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <title>Admin - Key Metrics</title>
    <style>
      body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: #0a0a0a;
        color: #fff;
        padding: 40px 20px;
        margin: 0;
      }
      .container {
        max-width: 400px;
        margin: 0 auto;
        background: #1a1a1a;
        padding: 30px;
        border-radius: 8px;
      }
      h1 {
        font-size: 24px;
        margin: 0 0 30px 0;
        text-align: center;
      }
      .form-group {
        margin-bottom: 20px;
      }
      label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #aaa;
      }
      input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #333;
        border-radius: 4px;
        background: #0a0a0a;
        color: #fff;
        font-size: 14px;
        box-sizing: border-box;
      }
      button {
        width: 100%;
        padding: 10px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
      }
      button:hover {
        background: #0056b3;
      }
      .error {
        color: #ff6b6b;
        font-size: 14px;
        margin-bottom: 20px;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Admin - Key Metrics</h1>
      <?php if ($error_msg): ?>
        <div class="error"><?= htmlspecialchars($error_msg) ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="form-group">
          <label for="password">Admin Password</label>
          <input type="password" id="password" name="admin_password" required autofocus>
        </div>
        <button type="submit">Authenticate</button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// Authenticated: Show admin form
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin - Key Metrics</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: #0a0a0a;
      color: #fff;
      padding: 40px 20px;
      margin: 0;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
    }
    h1 {
      font-size: 28px;
      margin: 0 0 30px 0;
      text-align: center;
    }
    .metrics-form {
      background: #1a1a1a;
      padding: 30px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      color: #aaa;
      font-weight: 500;
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #333;
      border-radius: 4px;
      background: #0a0a0a;
      color: #fff;
      font-size: 14px;
      box-sizing: border-box;
    }
    input[type="text"]:focus {
      outline: none;
      border-color: #007bff;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    button {
      padding: 10px 20px;
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s;
      width: 100%;
    }
    button:hover {
      background: #0056b3;
    }
    .success {
      color: #51cf66;
      font-size: 14px;
      margin-bottom: 20px;
      padding: 12px;
      background: rgba(81, 207, 102, 0.1);
      border-left: 3px solid #51cf66;
      border-radius: 4px;
    }
    .error {
      color: #ff6b6b;
      font-size: 14px;
      margin-bottom: 20px;
      padding: 12px;
      background: rgba(255, 107, 107, 0.1);
      border-left: 3px solid #ff6b6b;
      border-radius: 4px;
    }
    .logout {
      text-align: center;
      margin-top: 20px;
    }
    .logout a {
      color: #aaa;
      text-decoration: none;
      font-size: 14px;
    }
    .logout a:hover {
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Admin - Key Metrics</h1>
    
    <?php if ($success_msg): ?>
      <div class="success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
      <div class="error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="metrics-form">
      <h2 style="margin: 0 0 20px 0; font-size: 18px;">About Page Metrics</h2>
      
      <form method="post">
        <input type="hidden" name="action" value="save_metrics">
        <?php
          $metricsByKey = [];
          foreach ($metrics as $metric) {
            $metricsByKey[$metric['metric_key']] = $metric;
          }
        ?>

        <div class="form-row">
          <div class="form-group">
            <label for="annualized_return">Annualized Return</label>
            <input 
              type="text" 
              id="annualized_return"
              name="annualized_return" 
              value="<?= htmlspecialchars($metricsByKey['annualized_return']['metric_value'] ?? '+5.9%') ?>"
              placeholder="e.g., +5.9%"
            >
          </div>
          <div class="form-group">
            <label for="nifty_return">Vs Nifty Return</label>
            <input 
              type="text" 
              id="nifty_return"
              name="nifty_return" 
              value="<?= htmlspecialchars($metricsByKey['nifty_return']['metric_value'] ?? '+0.0%') ?>"
              placeholder="e.g., +0.0%"
            >
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="max_drawdown_value">Max Drawdown</label>
            <input 
              type="text" 
              id="max_drawdown_value"
              name="max_drawdown_value" 
              value="<?= htmlspecialchars($metricsByKey['max_drawdown']['metric_value'] ?? '-5.8%') ?>"
              placeholder="e.g., -5.8%"
            >
          </div>
          <div class="form-group">
            <label for="benchmark_value">Max Drawdown Vs Nifty</label>
            <input 
              type="text" 
              id="benchmark_value"
              name="benchmark_value" 
              value="<?= htmlspecialchars($metricsByKey['max_drawdown']['benchmark_value'] ?? '-15.2%') ?>"
              placeholder="e.g., -15.2%"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="latest_data_date">Latest Data Date (user entered)</label>
          <input 
            type="text" 
            id="latest_data_date"
            name="latest_data_date" 
            value="<?= htmlspecialchars($metricsByKey['latest_data_date']['metric_value'] ?? '') ?>"
            placeholder="e.g., 07 May 2026"
          >
        </div>

        <button type="submit">Save Metrics</button>
      </form>
    </div>

    <div class="logout">
      <a href="?logout=1">Logout</a>
    </div>
  </div>
</body>
</html>
