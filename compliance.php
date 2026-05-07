<?php
$page_title = 'Compliance - PlusWealth PMS';
include 'header.php';
require_once __DIR__ . '/includes/db_config.php';

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

$data = $defaults;

try {
  $conn = get_db_connection();
  $result = $conn->query("SELECT data_key, data_value FROM compliance_kv WHERE is_active = 1");
  if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
      $data[$row['data_key']] = $row['data_value'];
    }
  }
} catch (Throwable $e) {
  error_log('Compliance page data query failed: ' . $e->getMessage());
}

function cval(array $data, string $key): string {
  return htmlspecialchars((string)($data[$key] ?? ''), ENT_QUOTES);
}

function is_na(string $v): bool {
  return strtolower(trim($v)) === 'not applicable';
}
?>

<div class="section-wrap" id="compliance">
  <div class="inner">
    <h3 style="font-family:var(--f-body);font-size:15px;font-weight:600;color:var(--white);margin:56px 0 6px;letter-spacing:0.5px;">Data for the month ending <?= cval($data, 'month_ending') ?></h3>

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
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:14px 10px;color:var(--slate);"><?= $i ?></td>
            <td style="padding:14px 10px;color:var(--white);"><?= cval($data, 't1_r' . $i . '_source') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_pending_last') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_received') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_resolved') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_total_pending') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_pending_gt3') ?></td>
            <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't1_r' . $i . '_avg_days') ?></td>
          </tr>
        <?php endfor; ?>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_pending_last') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_received') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_resolved') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_pending') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_pending_gt3') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't1_total_avg_days') ?></td>
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
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:14px 10px;color:var(--slate);">1</td>
          <td style="padding:14px 10px;color:var(--white);"><?= cval($data, 't2_r1_month') ?></td>
          <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't2_r1_carried') ?></td>
          <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't2_r1_received') ?></td>
          <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't2_r1_resolved') ?></td>
          <td style="padding:14px 10px;text-align:center;color:var(--slate);"><?= cval($data, 't2_r1_pending') ?></td>
        </tr>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't2_total_carried') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't2_total_received') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't2_total_resolved') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't2_total_pending') ?></td>
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
        <?php for ($i = 1; $i <= 4; $i++): ?>
          <?php $isNa = is_na($data['t3_r' . $i . '_carried'] ?? ''); ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:14px 10px;color:var(--slate);"><?= $i ?></td>
            <td style="padding:14px 10px;color:var(--white);"><?= cval($data, 't3_r' . $i . '_year') ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cval($data, 't3_r' . $i . '_carried') ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cval($data, 't3_r' . $i . '_received') ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cval($data, 't3_r' . $i . '_resolved') ?></td>
            <td style="padding:14px 10px;text-align:center;color:<?= $isNa ? 'var(--ash)' : 'var(--slate)' ?>;<?= $isNa ? 'font-style:italic;' : '' ?>"><?= cval($data, 't3_r' . $i . '_pending') ?></td>
          </tr>
        <?php endfor; ?>
        <tr style="border-top:2px solid var(--border);">
          <td colspan="2" style="padding:14px 10px;font-weight:700;color:var(--white);">Grand Total</td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't3_total_carried') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't3_total_received') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't3_total_resolved') ?></td>
          <td style="padding:14px 10px;text-align:center;font-weight:700;color:var(--white);"><?= cval($data, 't3_total_pending') ?></td>
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
