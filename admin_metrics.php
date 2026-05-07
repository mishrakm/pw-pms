<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

$authenticated = false;
$error_msg = '';
$success_msg = '';
$metrics = [];

session_start();

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
      // Update max_drawdown metric
      $metric_value = trim($_POST['max_drawdown_value'] ?? '');
      $benchmark_value = trim($_POST['benchmark_value'] ?? '');

      $stmt = $conn->prepare(
        "UPDATE key_metrics
         SET metric_value = ?, benchmark_value = ?
         WHERE metric_key = 'max_drawdown'"
      );
      if ($stmt) {
        $stmt->bind_param('ss', $metric_value, $benchmark_value);
        if ($stmt->execute()) {
          $success_msg = 'Metrics updated successfully';
        } else {
          $error_msg = 'Failed to update metrics: ' . $stmt->error;
        }
        $stmt->close();
      }
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
      <h2 style="margin: 0 0 20px 0; font-size: 18px;">Max Drawdown Metrics</h2>
      
      <form method="post">
        <input type="hidden" name="action" value="save_metrics">
        
        <?php foreach ($metrics as $metric): ?>
          <?php if ($metric['metric_key'] === 'max_drawdown'): ?>
            <div class="form-row">
              <div class="form-group">
                <label for="max_drawdown_value">Max Drawdown</label>
                <input 
                  type="text" 
                  id="max_drawdown_value"
                  name="max_drawdown_value" 
                  value="<?= htmlspecialchars($metric['metric_value']) ?>"
                  placeholder="e.g., −5.07%"
                >
              </div>
              <div class="form-group">
                <label for="benchmark_value">Benchmark (Nifty)</label>
                <input 
                  type="text" 
                  id="benchmark_value"
                  name="benchmark_value" 
                  value="<?= htmlspecialchars($metric['benchmark_value'] ?? '') ?>"
                  placeholder="e.g., −5.71%"
                >
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

        <button type="submit">Save Metrics</button>
      </form>
    </div>

    <div class="logout">
      <a href="?logout=1">Logout</a>
    </div>
  </div>

  <script>
    if (new URL(window.location).searchParams.get('logout')) {
      <?php session_destroy(); ?>
      window.location.href = window.location.pathname;
    }
  </script>
</body>
</html>
