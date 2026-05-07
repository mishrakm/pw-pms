<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

$authenticated = false;
$error_msg = '';
$success_msg = '';
$data = [];

session_start();

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  session_destroy();
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  exit;
}

$defaults = [
  'month_ending' => 'Apr 30, 2026',

  't1_r1_source' => 'Directly from Investors',
  't1_r1_pending_last' => '0',
  't1_r1_received' => '0',
  't1_r1_resolved' => '0',
  't1_r1_total_pending' => '0',
  't1_r1_pending_gt3' => '0',
  't1_r1_avg_days' => '0',

  't1_r2_source' => 'SEBI (SCORES)',
  't1_r2_pending_last' => '0',
  't1_r2_received' => '0',
  't1_r2_resolved' => '0',
  't1_r2_total_pending' => '0',
  't1_r2_pending_gt3' => '0',
  't1_r2_avg_days' => '0',

  't1_r3_source' => 'Other Sources (if any)',
  't1_r3_pending_last' => '0',
  't1_r3_received' => '0',
  't1_r3_resolved' => '0',
  't1_r3_total_pending' => '0',
  't1_r3_pending_gt3' => '0',
  't1_r3_avg_days' => '0',

  't1_total_pending_last' => '0',
  't1_total_received' => '0',
  't1_total_resolved' => '0',
  't1_total_pending' => '0',
  't1_total_pending_gt3' => '0',
  't1_total_avg_days' => '0',

  't2_r1_month' => 'April 2026',
  't2_r1_carried' => '0',
  't2_r1_received' => '0',
  't2_r1_resolved' => '0',
  't2_r1_pending' => '0',

  't2_total_carried' => '0',
  't2_total_received' => '0',
  't2_total_resolved' => '0',
  't2_total_pending' => '0',

  't3_r1_year' => '2023-2024',
  't3_r1_carried' => 'Not Applicable',
  't3_r1_received' => 'Not Applicable',
  't3_r1_resolved' => 'Not Applicable',
  't3_r1_pending' => 'Not Applicable',

  't3_r2_year' => '2024-2025',
  't3_r2_carried' => 'Not Applicable',
  't3_r2_received' => 'Not Applicable',
  't3_r2_resolved' => 'Not Applicable',
  't3_r2_pending' => 'Not Applicable',

  't3_r3_year' => '2025-2026',
  't3_r3_carried' => '0',
  't3_r3_received' => '0',
  't3_r3_resolved' => '0',
  't3_r3_pending' => '0',

  't3_r4_year' => '2026-2027',
  't3_r4_carried' => '0',
  't3_r4_received' => '0',
  't3_r4_resolved' => '0',
  't3_r4_pending' => '0',

  't3_total_carried' => '0',
  't3_total_received' => '0',
  't3_total_resolved' => '0',
  't3_total_pending' => '0',
];

$labels = [
  'month_ending' => 'Month Ending (Header)',

  't1_r1_source' => 'Table 1 Row 1 Source',
  't1_r1_pending_last' => 'Table 1 Row 1 Pending Last Month',
  't1_r1_received' => 'Table 1 Row 1 Received',
  't1_r1_resolved' => 'Table 1 Row 1 Resolved',
  't1_r1_total_pending' => 'Table 1 Row 1 Total Pending',
  't1_r1_pending_gt3' => 'Table 1 Row 1 Pending > 3 Months',
  't1_r1_avg_days' => 'Table 1 Row 1 Avg Resolution Days',

  't1_r2_source' => 'Table 1 Row 2 Source',
  't1_r2_pending_last' => 'Table 1 Row 2 Pending Last Month',
  't1_r2_received' => 'Table 1 Row 2 Received',
  't1_r2_resolved' => 'Table 1 Row 2 Resolved',
  't1_r2_total_pending' => 'Table 1 Row 2 Total Pending',
  't1_r2_pending_gt3' => 'Table 1 Row 2 Pending > 3 Months',
  't1_r2_avg_days' => 'Table 1 Row 2 Avg Resolution Days',

  't1_r3_source' => 'Table 1 Row 3 Source',
  't1_r3_pending_last' => 'Table 1 Row 3 Pending Last Month',
  't1_r3_received' => 'Table 1 Row 3 Received',
  't1_r3_resolved' => 'Table 1 Row 3 Resolved',
  't1_r3_total_pending' => 'Table 1 Row 3 Total Pending',
  't1_r3_pending_gt3' => 'Table 1 Row 3 Pending > 3 Months',
  't1_r3_avg_days' => 'Table 1 Row 3 Avg Resolution Days',

  't1_total_pending_last' => 'Table 1 Grand Total Pending Last Month',
  't1_total_received' => 'Table 1 Grand Total Received',
  't1_total_resolved' => 'Table 1 Grand Total Resolved',
  't1_total_pending' => 'Table 1 Grand Total Pending',
  't1_total_pending_gt3' => 'Table 1 Grand Total Pending > 3 Months',
  't1_total_avg_days' => 'Table 1 Grand Total Avg Resolution Days',

  't2_r1_month' => 'Table 2 Row 1 Month',
  't2_r1_carried' => 'Table 2 Row 1 Carried Forward',
  't2_r1_received' => 'Table 2 Row 1 Received',
  't2_r1_resolved' => 'Table 2 Row 1 Resolved',
  't2_r1_pending' => 'Table 2 Row 1 Pending',

  't2_total_carried' => 'Table 2 Grand Total Carried Forward',
  't2_total_received' => 'Table 2 Grand Total Received',
  't2_total_resolved' => 'Table 2 Grand Total Resolved',
  't2_total_pending' => 'Table 2 Grand Total Pending',

  't3_r1_year' => 'Table 3 Row 1 Year',
  't3_r1_carried' => 'Table 3 Row 1 Carried Forward',
  't3_r1_received' => 'Table 3 Row 1 Received',
  't3_r1_resolved' => 'Table 3 Row 1 Resolved',
  't3_r1_pending' => 'Table 3 Row 1 Pending',

  't3_r2_year' => 'Table 3 Row 2 Year',
  't3_r2_carried' => 'Table 3 Row 2 Carried Forward',
  't3_r2_received' => 'Table 3 Row 2 Received',
  't3_r2_resolved' => 'Table 3 Row 2 Resolved',
  't3_r2_pending' => 'Table 3 Row 2 Pending',

  't3_r3_year' => 'Table 3 Row 3 Year',
  't3_r3_carried' => 'Table 3 Row 3 Carried Forward',
  't3_r3_received' => 'Table 3 Row 3 Received',
  't3_r3_resolved' => 'Table 3 Row 3 Resolved',
  't3_r3_pending' => 'Table 3 Row 3 Pending',

  't3_r4_year' => 'Table 3 Row 4 Year',
  't3_r4_carried' => 'Table 3 Row 4 Carried Forward',
  't3_r4_received' => 'Table 3 Row 4 Received',
  't3_r4_resolved' => 'Table 3 Row 4 Resolved',
  't3_r4_pending' => 'Table 3 Row 4 Pending',

  't3_total_carried' => 'Table 3 Grand Total Carried Forward',
  't3_total_received' => 'Table 3 Grand Total Received',
  't3_total_resolved' => 'Table 3 Grand Total Resolved',
  't3_total_pending' => 'Table 3 Grand Total Pending',
];

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

  $conn->query(
    "CREATE TABLE IF NOT EXISTS compliance_kv (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      data_key VARCHAR(120) NOT NULL UNIQUE,
      data_value TEXT NOT NULL,
      is_active TINYINT(1) NOT NULL DEFAULT 1,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY idx_data_key (data_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );

  $seedStmt = $conn->prepare(
    "INSERT INTO compliance_kv (data_key, data_value, is_active)
     VALUES (?, ?, 1)
     ON DUPLICATE KEY UPDATE
       data_value = IF(data_value IS NULL OR data_value = '', VALUES(data_value), data_value),
       is_active = 1"
  );
  if ($seedStmt) {
    foreach ($defaults as $k => $v) {
      $seedStmt->bind_param('ss', $k, $v);
      $seedStmt->execute();
    }
    $seedStmt->close();
  }

  if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_compliance') {
    $saveStmt = $conn->prepare(
      "INSERT INTO compliance_kv (data_key, data_value, is_active)
       VALUES (?, ?, 1)
       ON DUPLICATE KEY UPDATE data_value = VALUES(data_value), is_active = 1"
    );

    if (!$saveStmt) {
      throw new Exception('Failed to prepare save statement: ' . $conn->error);
    }

    foreach ($defaults as $k => $v) {
      $posted = trim($_POST[$k] ?? '');
      $val = ($posted === '') ? $v : $posted;
      $saveStmt->bind_param('ss', $k, $val);
      if (!$saveStmt->execute()) {
        throw new Exception('Failed to save ' . $k . ': ' . $saveStmt->error);
      }
    }

    $saveStmt->close();
    $success_msg = 'Compliance data saved successfully';
  }

  if ($authenticated) {
    $result = $conn->query("SELECT data_key, data_value FROM compliance_kv WHERE is_active = 1");
    if ($result instanceof mysqli_result) {
      while ($row = $result->fetch_assoc()) {
        $data[$row['data_key']] = $row['data_value'];
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
  <title>Admin - Compliance Data</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0a0a0a; color: #fff; padding: 40px 20px; margin: 0; }
    .container { max-width: 420px; margin: 0 auto; background: #1a1a1a; padding: 30px; border-radius: 8px; }
    h1 { font-size: 24px; margin: 0 0 24px 0; text-align: center; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; color: #aaa; font-size: 14px; }
    input[type="password"] { width: 100%; padding: 10px; border: 1px solid #333; border-radius: 4px; background: #0a0a0a; color: #fff; box-sizing: border-box; }
    button { width: 100%; padding: 10px; background: #007bff; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
    .error { color: #ff6b6b; margin-bottom: 14px; font-size: 14px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Admin - Compliance Data</h1>
    <?php if ($error_msg): ?><div class="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" required autofocus>
      </div>
      <button type="submit">Authenticate</button>
    </form>
  </div>
</body>
</html>
<?php
  exit;
}

function val(array $data, array $defaults, string $key): string {
  return htmlspecialchars($data[$key] ?? $defaults[$key] ?? '', ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin - Compliance Data</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0a0a0a; color: #fff; padding: 30px 16px; margin: 0; }
    .container { max-width: 1100px; margin: 0 auto; }
    h1 { font-size: 28px; margin: 0 0 22px 0; }
    .card { background: #1a1a1a; padding: 20px; border-radius: 8px; margin-bottom: 18px; }
    .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .field { margin-bottom: 10px; }
    label { display: block; margin-bottom: 6px; font-size: 12px; color: #aaa; }
    input[type="text"] { width: 100%; padding: 9px; border: 1px solid #333; border-radius: 4px; background: #0a0a0a; color: #fff; box-sizing: border-box; }
    button { padding: 11px 18px; background: #007bff; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
    .success { color: #51cf66; margin-bottom: 14px; }
    .error { color: #ff6b6b; margin-bottom: 14px; }
    .logout { margin-top: 14px; }
    .logout a { color: #aaa; text-decoration: none; }
    @media (max-width: 900px) { .grid, .grid-2 { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="container">
    <h1>Admin - Compliance Data</h1>
    <?php if ($success_msg): ?><div class="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="save_compliance">

      <div class="card">
        <h2 style="margin-top:0;">Header</h2>
        <div class="field">
          <label for="month_ending">Data for the month ending</label>
          <input type="text" id="month_ending" name="month_ending" value="<?= val($data, $defaults, 'month_ending') ?>">
        </div>
      </div>

      <div class="card">
        <h2 style="margin-top:0;">Monthly Complaint Data (Table 1)</h2>
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <h3>Row <?= $i ?></h3>
          <div class="grid">
            <div class="field"><label>Source</label><input type="text" name="t1_r<?= $i ?>_source" value="<?= val($data, $defaults, 't1_r' . $i . '_source') ?>"></div>
            <div class="field"><label>Pending Last Month</label><input type="text" name="t1_r<?= $i ?>_pending_last" value="<?= val($data, $defaults, 't1_r' . $i . '_pending_last') ?>"></div>
            <div class="field"><label>Received</label><input type="text" name="t1_r<?= $i ?>_received" value="<?= val($data, $defaults, 't1_r' . $i . '_received') ?>"></div>
            <div class="field"><label>Resolved</label><input type="text" name="t1_r<?= $i ?>_resolved" value="<?= val($data, $defaults, 't1_r' . $i . '_resolved') ?>"></div>
            <div class="field"><label>Total Pending</label><input type="text" name="t1_r<?= $i ?>_total_pending" value="<?= val($data, $defaults, 't1_r' . $i . '_total_pending') ?>"></div>
            <div class="field"><label>Pending > 3 Months</label><input type="text" name="t1_r<?= $i ?>_pending_gt3" value="<?= val($data, $defaults, 't1_r' . $i . '_pending_gt3') ?>"></div>
            <div class="field"><label>Avg Resolution Days</label><input type="text" name="t1_r<?= $i ?>_avg_days" value="<?= val($data, $defaults, 't1_r' . $i . '_avg_days') ?>"></div>
          </div>
        <?php endfor; ?>

        <h3>Grand Total</h3>
        <div class="grid">
          <div class="field"><label>Pending Last Month</label><input type="text" name="t1_total_pending_last" value="<?= val($data, $defaults, 't1_total_pending_last') ?>"></div>
          <div class="field"><label>Received</label><input type="text" name="t1_total_received" value="<?= val($data, $defaults, 't1_total_received') ?>"></div>
          <div class="field"><label>Resolved</label><input type="text" name="t1_total_resolved" value="<?= val($data, $defaults, 't1_total_resolved') ?>"></div>
          <div class="field"><label>Total Pending</label><input type="text" name="t1_total_pending" value="<?= val($data, $defaults, 't1_total_pending') ?>"></div>
          <div class="field"><label>Pending > 3 Months</label><input type="text" name="t1_total_pending_gt3" value="<?= val($data, $defaults, 't1_total_pending_gt3') ?>"></div>
          <div class="field"><label>Avg Resolution Days</label><input type="text" name="t1_total_avg_days" value="<?= val($data, $defaults, 't1_total_avg_days') ?>"></div>
        </div>
      </div>

      <div class="card">
        <h2 style="margin-top:0;">Trend of Monthly Disposal (Table 2)</h2>
        <div class="grid">
          <div class="field"><label>Month</label><input type="text" name="t2_r1_month" value="<?= val($data, $defaults, 't2_r1_month') ?>"></div>
          <div class="field"><label>Carried Forward</label><input type="text" name="t2_r1_carried" value="<?= val($data, $defaults, 't2_r1_carried') ?>"></div>
          <div class="field"><label>Received</label><input type="text" name="t2_r1_received" value="<?= val($data, $defaults, 't2_r1_received') ?>"></div>
          <div class="field"><label>Resolved</label><input type="text" name="t2_r1_resolved" value="<?= val($data, $defaults, 't2_r1_resolved') ?>"></div>
          <div class="field"><label>Pending</label><input type="text" name="t2_r1_pending" value="<?= val($data, $defaults, 't2_r1_pending') ?>"></div>
        </div>

        <h3>Grand Total</h3>
        <div class="grid-2">
          <div class="field"><label>Carried Forward</label><input type="text" name="t2_total_carried" value="<?= val($data, $defaults, 't2_total_carried') ?>"></div>
          <div class="field"><label>Received</label><input type="text" name="t2_total_received" value="<?= val($data, $defaults, 't2_total_received') ?>"></div>
          <div class="field"><label>Resolved</label><input type="text" name="t2_total_resolved" value="<?= val($data, $defaults, 't2_total_resolved') ?>"></div>
          <div class="field"><label>Pending</label><input type="text" name="t2_total_pending" value="<?= val($data, $defaults, 't2_total_pending') ?>"></div>
        </div>
      </div>

      <div class="card">
        <h2 style="margin-top:0;">Trend of Annual Disposal (Table 3)</h2>
        <?php for ($i = 1; $i <= 4; $i++): ?>
          <h3>Row <?= $i ?></h3>
          <div class="grid">
            <div class="field"><label>Year</label><input type="text" name="t3_r<?= $i ?>_year" value="<?= val($data, $defaults, 't3_r' . $i . '_year') ?>"></div>
            <div class="field"><label>Carried Forward</label><input type="text" name="t3_r<?= $i ?>_carried" value="<?= val($data, $defaults, 't3_r' . $i . '_carried') ?>"></div>
            <div class="field"><label>Received</label><input type="text" name="t3_r<?= $i ?>_received" value="<?= val($data, $defaults, 't3_r' . $i . '_received') ?>"></div>
            <div class="field"><label>Resolved</label><input type="text" name="t3_r<?= $i ?>_resolved" value="<?= val($data, $defaults, 't3_r' . $i . '_resolved') ?>"></div>
            <div class="field"><label>Pending</label><input type="text" name="t3_r<?= $i ?>_pending" value="<?= val($data, $defaults, 't3_r' . $i . '_pending') ?>"></div>
          </div>
        <?php endfor; ?>

        <h3>Grand Total</h3>
        <div class="grid-2">
          <div class="field"><label>Carried Forward</label><input type="text" name="t3_total_carried" value="<?= val($data, $defaults, 't3_total_carried') ?>"></div>
          <div class="field"><label>Received</label><input type="text" name="t3_total_received" value="<?= val($data, $defaults, 't3_total_received') ?>"></div>
          <div class="field"><label>Resolved</label><input type="text" name="t3_total_resolved" value="<?= val($data, $defaults, 't3_total_resolved') ?>"></div>
          <div class="field"><label>Pending</label><input type="text" name="t3_total_pending" value="<?= val($data, $defaults, 't3_total_pending') ?>"></div>
        </div>
      </div>

      <button type="submit">Save All Compliance Data</button>
    </form>

    <div class="logout"><a href="?logout=1">Logout</a></div>
  </div>
</body>
</html>
