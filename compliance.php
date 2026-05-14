<?php
$page_title = 'Compliance - PlusWealth PMS';
include 'header.php';
require_once __DIR__ . '/includes/db_config.php';

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

function section_load_display(mysqli $conn, string $key, array $fallback): array {
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

  $meta = section_load_display($conn, 'meta', $defaultMeta);
  $monthlyComplaints = section_load_display($conn, 'monthly_complaints', $defaultMonthlyComplaints);
  $monthlyTrend = section_load_display($conn, 'monthly_trend', $defaultMonthlyTrend);
  $annualTrend = section_load_display($conn, 'annual_trend', $defaultAnnualTrend);
} catch (Throwable $e) {
  error_log('Compliance page data query failed: ' . $e->getMessage());
}

function cesc(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES);
}

function is_na_row(string $v): bool {
  return strtolower(trim($v)) === 'not applicable';
}

function to_num($v): int {
  return is_numeric($v) ? (int)$v : 0;
}

$t1 = ['pending_last_month' => 0, 'received' => 0, 'resolved' => 0, 'total_pending' => 0, 'pending_gt_3_months' => 0, 'avg_resolution_days' => 0];
foreach ($monthlyComplaints as $r) {
  $t1['pending_last_month'] += to_num($r['pending_last_month'] ?? 0);
  $t1['received'] += to_num($r['received'] ?? 0);
  $t1['resolved'] += to_num($r['resolved'] ?? 0);
  $t1['total_pending'] += to_num($r['total_pending'] ?? 0);
  $t1['pending_gt_3_months'] += to_num($r['pending_gt_3_months'] ?? 0);
  $t1['avg_resolution_days'] += to_num($r['avg_resolution_days'] ?? 0);
}

$t2 = ['carried_forward' => 0, 'received' => 0, 'resolved' => 0, 'pending' => 0];
foreach ($monthlyTrend as $r) {
  $t2['carried_forward'] += to_num($r['carried_forward'] ?? 0);
  $t2['received'] += to_num($r['received'] ?? 0);
  $t2['resolved'] += to_num($r['resolved'] ?? 0);
  $t2['pending'] += to_num($r['pending'] ?? 0);
}

$t3 = ['carried_forward' => 0, 'received' => 0, 'resolved' => 0, 'pending' => 0];
foreach ($annualTrend as $r) {
  $t3['carried_forward'] += to_num($r['carried_forward'] ?? 0);
  $t3['received'] += to_num($r['received'] ?? 0);
  $t3['resolved'] += to_num($r['resolved'] ?? 0);
  $t3['pending'] += to_num($r['pending'] ?? 0);
}
?>

<div class="section-wrap" id="compliance">
  <div class="inner">
    <div style="margin-bottom:48px;">
      <div class="s-eyebrow reveal">Compliance</div>
      <h2 class="s-title reveal d1">Grievances</h2>
    </div>

    <h3 style="font-family:var(--f-body);font-size:15px;font-weight:600;color:var(--white);margin:56px 0 6px;letter-spacing:0.5px;">Data for the month ending <?= cesc((string)($meta['month_ending'] ?? '')) ?></h3>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin-top:20px;">
    <table style="width:100%;min-width:750px;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid var(--border);">
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Sr. No.</th>
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Received From</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Pending at the<br>end of last month</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Received</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Resolved ^</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Total<br>Pending #</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Pending complaints<br>&gt; 3 months</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Average resolution<br>time in days ^</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($monthlyComplaints as $idx => $row): ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:14px 10px;color:var(--slate);"><?= $idx + 1 ?></td>
            <td style="padding:14px 10px;color:var(--white);"><?= cesc((string)($row['received_from'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['pending_last_month'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['received'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['resolved'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['total_pending'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['pending_gt_3_months'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['avg_resolution_days'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['pending_last_month'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['received'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['resolved'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['total_pending'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['pending_gt_3_months'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t1['avg_resolution_days'] ?></td>
        </tr>
      </tbody>
    </table>
    </div>
    <p style="font-family:var(--f-body);font-size:12px;color:var(--ash);line-height:1.7;margin-top:16px;">^ Average Resolution time is the sum total of time taken to resolve each complaint in days, in the current month divided by total number of complaints resolved in the current month.</p>

    <h3 style="font-family:var(--f-body);font-size:15px;font-weight:600;color:var(--white);margin:56px 0 20px;letter-spacing:0.5px;">Trend of monthly disposal of complaint</h3>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table style="width:100%;min-width:560px;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid var(--border);">
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Sr. No.</th>
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Month</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Carried forward<br>from previous month</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Received</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Resolved</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Pending #</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($monthlyTrend as $idx => $row): ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:14px 10px;color:var(--slate);"><?= $idx + 1 ?></td>
            <td style="padding:14px 10px;color:var(--white);"><?= cesc((string)($row['month'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['carried_forward'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['received'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['resolved'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cesc((string)($row['pending'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t2['carried_forward'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t2['received'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t2['resolved'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t2['pending'] ?></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div style="margin-top:16px;display:flex;flex-direction:column;gap:6px;">
      <p style="font-family:var(--f-body);font-size:12px;color:var(--ash);line-height:1.7;">* Inclusive of complaints of previous months resolved in the current month.</p>
      <p style="font-family:var(--f-body);font-size:12px;color:var(--ash);line-height:1.7;"># Inclusive of complaints pending as on the last day of the month.</p>
    </div>

    <h3 style="font-family:var(--f-body);font-size:15px;font-weight:600;color:var(--white);margin:56px 0 20px;letter-spacing:0.5px;">Trend of annual disposal of complaints</h3>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table style="width:100%;min-width:560px;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="border-bottom:1px solid var(--border);">
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">SN</th>
          <th style="padding:12px 10px;text-align:left;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Year</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);white-space:nowrap;">Carried forward<br>from previous year</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Received</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Resolved **</th>
          <th style="padding:12px 10px;text-align:center;font-family:var(--f-mono);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--slate);">Pending ##</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($annualTrend as $idx => $row): ?>
          <?php $isNa = is_na_row((string)($row['carried_forward'] ?? '')); ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:14px 10px;color:var(--slate);"><?= $idx + 1 ?></td>
            <td style="padding:14px 10px;color:var(--white);"><?= cesc((string)($row['year'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cesc((string)($row['carried_forward'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cesc((string)($row['received'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cesc((string)($row['resolved'] ?? '')) ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cesc((string)($row['pending'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t3['carried_forward'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t3['received'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t3['resolved'] ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= $t3['pending'] ?></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div style="margin-top:16px;display:flex;flex-direction:column;gap:6px;">
      <p style="font-family:var(--f-body);font-size:12px;color:var(--ash);line-height:1.7;">** Inclusive of complaints of previous years resolved in the current year.</p>
      <p style="font-family:var(--f-body);font-size:12px;color:var(--ash);line-height:1.7;">## Inclusive of complaints pending as on last day of the year.</p>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
