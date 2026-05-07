<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

session_start();

$authenticated = false;
$error_msg = '';
$success_msg = '';

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  session_destroy();
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  exit;
}

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

$defaultMeta = ['month_ending' => 'Apr 30, 2026'];
$defaultMonthlyComplaints = [
  ['received_from' => 'Directly from Investors', 'pending_last_month' => '0', 'received' => '0', 'resolved' => '0', 'total_pending' => '0', 'pending_gt_3_months' => '0', 'avg_resolution_days' => '0'],
  ['received_from' => 'SEBI (SCORES)', 'pending_last_month' => '0', 'received' => '0', 'resolved' => '0', 'total_pending' => '0', 'pending_gt_3_months' => '0', 'avg_resolution_days' => '0'],
  ['received_from' => 'Other Sources (if any)', 'pending_last_month' => '0', 'received' => '0', 'resolved' => '0', 'total_pending' => '0', 'pending_gt_3_months' => '0', 'avg_resolution_days' => '0'],
];
$defaultMonthlyTrend = [
  ['month' => 'April 2026', 'carried_forward' => '0', 'received' => '0', 'resolved' => '0', 'pending' => '0'],
];
$defaultAnnualTrend = [
  ['year' => '2023-2024', 'carried_forward' => 'Not Applicable', 'received' => 'Not Applicable', 'resolved' => 'Not Applicable', 'pending' => 'Not Applicable'],
  ['year' => '2024-2025', 'carried_forward' => 'Not Applicable', 'received' => 'Not Applicable', 'resolved' => 'Not Applicable', 'pending' => 'Not Applicable'],
  ['year' => '2025-2026', 'carried_forward' => '0', 'received' => '0', 'resolved' => '0', 'pending' => '0'],
  ['year' => '2026-2027', 'carried_forward' => '0', 'received' => '0', 'resolved' => '0', 'pending' => '0'],
];

$meta = $defaultMeta;
$monthlyComplaints = $defaultMonthlyComplaints;
$monthlyTrend = $defaultMonthlyTrend;
$annualTrend = $defaultAnnualTrend;

function section_load(mysqli $conn, string $key, array $fallback): array {
  $stmt = $conn->prepare('SELECT data_json FROM compliance_sections WHERE section_key = ? LIMIT 1');
  if (!$stmt) {
    return $fallback;
  }
  $stmt->bind_param('s', $key);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res instanceof mysqli_result && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $decoded = json_decode($row['data_json'] ?? '', true);
    $stmt->close();
    return is_array($decoded) ? $decoded : $fallback;
  }
  $stmt->close();
  return $fallback;
}

function section_save(mysqli $conn, string $key, array $value): void {
  $json = json_encode($value, JSON_UNESCAPED_SLASHES);
  $stmt = $conn->prepare(
    'INSERT INTO compliance_sections (section_key, data_json) VALUES (?, ?) ON DUPLICATE KEY UPDATE data_json = VALUES(data_json)'
  );
  if (!$stmt) {
    throw new Exception('Failed to prepare save statement for ' . $key);
  }
  $stmt->bind_param('ss', $key, $json);
  if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    throw new Exception('Failed to save section ' . $key . ': ' . $err);
  }
  $stmt->close();
}

try {
  $conn = get_db_connection();
  $conn->query(
    'CREATE TABLE IF NOT EXISTS compliance_sections (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      section_key VARCHAR(80) NOT NULL UNIQUE,
      data_json LONGTEXT NOT NULL,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY idx_section_key (section_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
  );

  if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_compliance') {
    $metaInput = [
      'month_ending' => trim($_POST['month_ending'] ?? ''),
    ];
    if ($metaInput['month_ending'] === '') {
      $metaInput['month_ending'] = $defaultMeta['month_ending'];
    }

    $mcSources = $_POST['mc_received_from'] ?? [];
    $mcPendingLast = $_POST['mc_pending_last_month'] ?? [];
    $mcReceived = $_POST['mc_received'] ?? [];
    $mcResolved = $_POST['mc_resolved'] ?? [];
    $mcTotalPending = $_POST['mc_total_pending'] ?? [];
    $mcPendingGt3 = $_POST['mc_pending_gt_3_months'] ?? [];
    $mcAvgDays = $_POST['mc_avg_resolution_days'] ?? [];

    $monthlyComplaintsInput = [];
    $mcCount = max(count($mcSources), count($mcPendingLast), count($mcReceived), count($mcResolved), count($mcTotalPending), count($mcPendingGt3), count($mcAvgDays));
    for ($i = 0; $i < $mcCount; $i++) {
      $monthlyComplaintsInput[] = [
        'received_from' => trim((string)($mcSources[$i] ?? '')),
        'pending_last_month' => trim((string)($mcPendingLast[$i] ?? '')),
        'received' => trim((string)($mcReceived[$i] ?? '')),
        'resolved' => trim((string)($mcResolved[$i] ?? '')),
        'total_pending' => trim((string)($mcTotalPending[$i] ?? '')),
        'pending_gt_3_months' => trim((string)($mcPendingGt3[$i] ?? '')),
        'avg_resolution_days' => trim((string)($mcAvgDays[$i] ?? '')),
      ];
    }
    if (count($monthlyComplaintsInput) === 0) {
      $monthlyComplaintsInput = $defaultMonthlyComplaints;
    }

    $mdMonth = $_POST['md_month'] ?? [];
    $mdCarried = $_POST['md_carried_forward'] ?? [];
    $mdReceived = $_POST['md_received'] ?? [];
    $mdResolved = $_POST['md_resolved'] ?? [];
    $mdPending = $_POST['md_pending'] ?? [];

    $monthlyTrendInput = [];
    $mdCount = max(count($mdMonth), count($mdCarried), count($mdReceived), count($mdResolved), count($mdPending));
    for ($i = 0; $i < $mdCount; $i++) {
      $row = [
        'month' => trim((string)($mdMonth[$i] ?? '')),
        'carried_forward' => trim((string)($mdCarried[$i] ?? '')),
        'received' => trim((string)($mdReceived[$i] ?? '')),
        'resolved' => trim((string)($mdResolved[$i] ?? '')),
        'pending' => trim((string)($mdPending[$i] ?? '')),
      ];
      if (implode('', $row) !== '') {
        $monthlyTrendInput[] = $row;
      }
    }
    if (count($monthlyTrendInput) === 0) {
      $monthlyTrendInput = $defaultMonthlyTrend;
    }

    $adYear = $_POST['ad_year'] ?? [];
    $adCarried = $_POST['ad_carried_forward'] ?? [];
    $adReceived = $_POST['ad_received'] ?? [];
    $adResolved = $_POST['ad_resolved'] ?? [];
    $adPending = $_POST['ad_pending'] ?? [];

    $annualTrendInput = [];
    $adCount = max(count($adYear), count($adCarried), count($adReceived), count($adResolved), count($adPending));
    for ($i = 0; $i < $adCount; $i++) {
      $row = [
        'year' => trim((string)($adYear[$i] ?? '')),
        'carried_forward' => trim((string)($adCarried[$i] ?? '')),
        'received' => trim((string)($adReceived[$i] ?? '')),
        'resolved' => trim((string)($adResolved[$i] ?? '')),
        'pending' => trim((string)($adPending[$i] ?? '')),
      ];
      if (implode('', $row) !== '') {
        $annualTrendInput[] = $row;
      }
    }
    if (count($annualTrendInput) === 0) {
      $annualTrendInput = $defaultAnnualTrend;
    }

    section_save($conn, 'meta', $metaInput);
    section_save($conn, 'monthly_complaints', $monthlyComplaintsInput);
    section_save($conn, 'monthly_trend', $monthlyTrendInput);
    section_save($conn, 'annual_trend', $annualTrendInput);

    $success_msg = 'Compliance data saved successfully';
  }

  $meta = section_load($conn, 'meta', $defaultMeta);
  $monthlyComplaints = section_load($conn, 'monthly_complaints', $defaultMonthlyComplaints);
  $monthlyTrend = section_load($conn, 'monthly_trend', $defaultMonthlyTrend);
  $annualTrend = section_load($conn, 'annual_trend', $defaultAnnualTrend);
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
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0a0a; color: #fff; padding: 40px 20px; margin: 0; }
    .container { max-width: 420px; margin: 0 auto; background: #1a1a1a; padding: 30px; border-radius: 8px; }
    h1 { font-size: 24px; margin: 0 0 24px 0; text-align: center; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; color: #aaa; font-size: 14px; }
    input[type='password'] { width: 100%; padding: 10px; border: 1px solid #333; border-radius: 4px; background: #0a0a0a; color: #fff; box-sizing: border-box; }
    button { width: 100%; padding: 10px; background: #007bff; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
    .error { color: #ff6b6b; margin-bottom: 14px; font-size: 14px; }
  </style>
</head>
<body>
  <div class='container'>
    <h1>Admin - Compliance Data</h1>
    <?php if ($error_msg): ?><div class='error'><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
    <form method='post'>
      <div class='form-group'>
        <label for='admin_password'>Admin Password</label>
        <input type='password' id='admin_password' name='admin_password' required autofocus>
      </div>
      <button type='submit'>Authenticate</button>
    </form>
  </div>
</body>
</html>
<?php
  exit;
}

function esc(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin - Compliance Data</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0a0a; color: #fff; padding: 30px 16px; margin: 0; }
    .container { max-width: 1200px; margin: 0 auto; }
    h1 { font-size: 28px; margin: 0 0 22px 0; }
    h2 { margin: 0 0 14px 0; font-size: 18px; }
    .card { background: #1a1a1a; padding: 20px; border-radius: 8px; margin-bottom: 18px; }
    .row-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; margin-bottom: 10px; }
    .row-grid-5 { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 10px; }
    label { display: block; margin-bottom: 6px; font-size: 12px; color: #aaa; }
    input[type='text'] { width: 100%; padding: 9px; border: 1px solid #333; border-radius: 4px; background: #0a0a0a; color: #fff; box-sizing: border-box; }
    button { padding: 10px 16px; background: #007bff; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
    .secondary { background: #2a2a2a; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .success { color: #51cf66; margin-bottom: 14px; }
    .error { color: #ff6b6b; margin-bottom: 14px; }
    .logout { margin-top: 14px; }
    .logout a { color: #aaa; text-decoration: none; }
    @media (max-width: 1100px) {
      .row-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .row-grid-5 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
  </style>
</head>
<body>
  <div class='container'>
    <h1>Admin - Compliance Data</h1>
    <?php if ($success_msg): ?><div class='success'><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class='error'><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

    <form method='post'>
      <input type='hidden' name='action' value='save_compliance'>

      <div class='card'>
        <h2>Section 1: Header</h2>
        <label for='month_ending'>Data for the month ending</label>
        <input type='text' id='month_ending' name='month_ending' value='<?= esc((string)($meta['month_ending'] ?? '')) ?>'>
      </div>

      <div class='card'>
        <h2>Section 2: Monthly Complaint Data</h2>
        <div style='font-size:12px;color:#aaa;margin-bottom:10px;'>This section is separate. Edit each source row below.</div>
        <?php foreach ($monthlyComplaints as $i => $row): ?>
          <div style='margin:12px 0 6px 0;'>Row <?= $i + 1 ?></div>
          <div class='row-grid'>
            <div><label>Received From</label><input type='text' name='mc_received_from[]' value='<?= esc((string)($row['received_from'] ?? '')) ?>'></div>
            <div><label>Pending Last Month</label><input type='text' name='mc_pending_last_month[]' value='<?= esc((string)($row['pending_last_month'] ?? '')) ?>'></div>
            <div><label>Received</label><input type='text' name='mc_received[]' value='<?= esc((string)($row['received'] ?? '')) ?>'></div>
            <div><label>Resolved</label><input type='text' name='mc_resolved[]' value='<?= esc((string)($row['resolved'] ?? '')) ?>'></div>
            <div><label>Total Pending</label><input type='text' name='mc_total_pending[]' value='<?= esc((string)($row['total_pending'] ?? '')) ?>'></div>
            <div><label>Pending > 3 Months</label><input type='text' name='mc_pending_gt_3_months[]' value='<?= esc((string)($row['pending_gt_3_months'] ?? '')) ?>'></div>
            <div><label>Avg Resolution Days</label><input type='text' name='mc_avg_resolution_days[]' value='<?= esc((string)($row['avg_resolution_days'] ?? '')) ?>'></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class='card'>
        <h2>Section 3: Trend of Monthly Disposal</h2>
        <div id='monthly-trend-rows'>
          <?php foreach ($monthlyTrend as $i => $row): ?>
            <div class='row-grid-5 monthly-trend-row'>
              <div><label>Month</label><input type='text' name='md_month[]' value='<?= esc((string)($row['month'] ?? '')) ?>'></div>
              <div><label>Carried Forward</label><input type='text' name='md_carried_forward[]' value='<?= esc((string)($row['carried_forward'] ?? '')) ?>'></div>
              <div><label>Received</label><input type='text' name='md_received[]' value='<?= esc((string)($row['received'] ?? '')) ?>'></div>
              <div><label>Resolved</label><input type='text' name='md_resolved[]' value='<?= esc((string)($row['resolved'] ?? '')) ?>'></div>
              <div><label>Pending</label><input type='text' name='md_pending[]' value='<?= esc((string)($row['pending'] ?? '')) ?>'></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class='actions'>
          <button type='button' class='secondary' id='add-monthly-row'>Add Monthly Row</button>
        </div>
      </div>

      <div class='card'>
        <h2>Section 4: Trend of Annual Disposal</h2>
        <div id='annual-trend-rows'>
          <?php foreach ($annualTrend as $i => $row): ?>
            <div class='row-grid-5 annual-trend-row'>
              <div><label>Year</label><input type='text' name='ad_year[]' value='<?= esc((string)($row['year'] ?? '')) ?>'></div>
              <div><label>Carried Forward</label><input type='text' name='ad_carried_forward[]' value='<?= esc((string)($row['carried_forward'] ?? '')) ?>'></div>
              <div><label>Received</label><input type='text' name='ad_received[]' value='<?= esc((string)($row['received'] ?? '')) ?>'></div>
              <div><label>Resolved</label><input type='text' name='ad_resolved[]' value='<?= esc((string)($row['resolved'] ?? '')) ?>'></div>
              <div><label>Pending</label><input type='text' name='ad_pending[]' value='<?= esc((string)($row['pending'] ?? '')) ?>'></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class='actions'>
          <button type='button' class='secondary' id='add-annual-row'>Add Annual Row</button>
        </div>
      </div>

      <button type='submit'>Save All Sections</button>
    </form>

    <div class='logout'><a href='?logout=1'>Logout</a></div>
  </div>

  <script>
    function addRow(containerId, namePrefix) {
      const container = document.getElementById(containerId);
      const row = document.createElement('div');
      row.className = 'row-grid-5';

      const fieldA = namePrefix === 'md' ? 'Month' : 'Year';
      const html = `
        <div><label>${fieldA}</label><input type='text' name='${namePrefix}_${namePrefix === 'md' ? 'month' : 'year'}[]' value=''></div>
        <div><label>Carried Forward</label><input type='text' name='${namePrefix}_carried_forward[]' value=''></div>
        <div><label>Received</label><input type='text' name='${namePrefix}_received[]' value=''></div>
        <div><label>Resolved</label><input type='text' name='${namePrefix}_resolved[]' value=''></div>
        <div><label>Pending</label><input type='text' name='${namePrefix}_pending[]' value=''></div>
      `;

      row.innerHTML = html;
      container.appendChild(row);
    }

    document.getElementById('add-monthly-row').addEventListener('click', function () {
      addRow('monthly-trend-rows', 'md');
    });

    document.getElementById('add-annual-row').addEventListener('click', function () {
      addRow('annual-trend-rows', 'ad');
    });
  </script>
</body>
</html>
