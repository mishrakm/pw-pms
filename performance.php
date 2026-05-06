<?php
$page_title = 'Performance — PlusWealth PMS';
include 'header.php';

$performanceRows = [
  [
    'month_year' => '2026-01',
    'strategy' => 'PlusWealth Fusion',
    'one_month' => '4.77',
    'three_month' => '3.5',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '7.95%',
  ],
  [
    'month_year' => '2026-01',
    'strategy' => "Benchmark: NSE Multi Asset Index 2\n(50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)",
    'one_month' => '0.65',
    'three_month' => '-0.76',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '1.51%',
  ],
];

$availableMonths = [
  [
    'value' => '2026-01',
    'label' => 'Jan 2026',
  ],
];
$selectedMonthYear = '2026-01';

$normalizePerformanceValue = static function ($value) {
  if ($value === null) {
    return '-';
  }

  $value = trim((string) $value);
  return $value === '' ? '-' : $value;
};

try {
  require_once __DIR__ . '/includes/db_config.php';
  $conn = get_db_connection();
  $monthsResult = $conn->query(
    "SELECT DATE_FORMAT(month_year, '%Y-%m') AS month_value, DATE_FORMAT(month_year, '%b %Y') AS month_label
     FROM performance_returns
     WHERE is_active = 1 AND month_year IS NOT NULL
     GROUP BY DATE_FORMAT(month_year, '%Y-%m'), DATE_FORMAT(month_year, '%b %Y')
     ORDER BY month_year DESC"
  );

  if ($monthsResult instanceof mysqli_result && $monthsResult->num_rows > 0) {
    $availableMonths = [];
    while ($monthRow = $monthsResult->fetch_assoc()) {
      $availableMonths[] = [
        'value' => $monthRow['month_value'],
        'label' => $monthRow['month_label'],
      ];
    }
  }

  $requestedMonthYear = isset($_GET['month_year']) ? trim((string) $_GET['month_year']) : '';
  $validMonthValues = array_column($availableMonths, 'value');

  if ($requestedMonthYear !== '' && in_array($requestedMonthYear, $validMonthValues, true)) {
    $selectedMonthYear = $requestedMonthYear;
  } else {
    $selectedMonthYear = $availableMonths[0]['value'];
  }

  $stmt = $conn->prepare(
    "SELECT strategy, one_month, three_month, six_month, one_year, two_year, three_year, four_year, five_year, since_inception
     FROM performance_returns
     WHERE is_active = 1 AND DATE_FORMAT(month_year, '%Y-%m') = ?
     ORDER BY display_order ASC, id ASC"
  );

  if ($stmt) {
    $stmt->bind_param('s', $selectedMonthYear);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result instanceof mysqli_result && $result->num_rows > 0) {
      $performanceRows = [];
      while ($row = $result->fetch_assoc()) {
        $performanceRows[] = [
          'month_year' => $selectedMonthYear,
          'strategy' => $normalizePerformanceValue($row['strategy'] ?? null),
          'one_month' => $normalizePerformanceValue($row['one_month'] ?? null),
          'three_month' => $normalizePerformanceValue($row['three_month'] ?? null),
          'six_month' => $normalizePerformanceValue($row['six_month'] ?? null),
          'one_year' => $normalizePerformanceValue($row['one_year'] ?? null),
          'two_year' => $normalizePerformanceValue($row['two_year'] ?? null),
          'three_year' => $normalizePerformanceValue($row['three_year'] ?? null),
          'four_year' => $normalizePerformanceValue($row['four_year'] ?? null),
          'five_year' => $normalizePerformanceValue($row['five_year'] ?? null),
          'since_inception' => $normalizePerformanceValue($row['since_inception'] ?? null),
        ];
      }
    }

    $stmt->close();
  }
} catch (Throwable $e) {
  error_log('Performance table load failed: ' . $e->getMessage());
}
?>

<!-- LIVE PERFORMANCE -->
<div class="section-wrap" id="performance">
  <div class="inner">
    <div class="perf-header">
      <div>
        <div class="s-eyebrow reveal">Actual Performance</div>
        <h2 class="s-title reveal d1">Actual results.<br><em>Not simulated.</em></h2>
      </div>
      <div class="actual-badge reveal d2">
        <div class="signal-dot"></div>
        LIVE DATA · UPDATED 31 JAN 2026
      </div>
    </div>

    <div class="strategy-performance" style="margin-bottom:24px;text-align:center;">
      <img src="PlusWealth_Fusion_Jan-2026_return.jpeg" alt="Fusion Performance" style="width:100%;height:auto;max-width:800px;" class="reveal d3" />
    </div>

    <div class="reveal d3" style="margin-bottom:12px;display:flex;justify-content:flex-end;">
      <form method="get" action="" style="display:flex;gap:8px;align-items:center;">
        <label for="month_year" style="font-weight:600;">Month Year:</label>
        <select id="month_year" name="month_year" onchange="this.form.submit()" style="padding:8px 10px;border-radius:8px;">
          <?php foreach ($availableMonths as $monthOption): ?>
          <option value="<?= htmlspecialchars($monthOption['value'], ENT_QUOTES, 'UTF-8') ?>" <?= $monthOption['value'] === $selectedMonthYear ? 'selected' : '' ?>>
            <?= htmlspecialchars($monthOption['label'], ENT_QUOTES, 'UTF-8') ?>
          </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="reveal d3" style="margin-bottom:28px;overflow-x:auto;">
      <table class="perf-table" style="width:100%;min-width:980px;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:12px 10px;">Strategy</th>
            <th style="padding:12px 10px;">1 Month</th>
            <th style="padding:12px 10px;">3 Month</th>
            <th style="padding:12px 10px;">6 Month</th>
            <th style="padding:12px 10px;">1 Year</th>
            <th style="padding:12px 10px;">2 Year</th>
            <th style="padding:12px 10px;">3 Year</th>
            <th style="padding:12px 10px;">4 Year</th>
            <th style="padding:12px 10px;">5 Year</th>
            <th style="padding:12px 10px;">Since Inception</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($performanceRows as $row): ?>
          <tr>
            <td style="text-align:left;padding:12px 10px;"><?= nl2br(htmlspecialchars($row['strategy'], ENT_QUOTES, 'UTF-8')) ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['one_month'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['three_month'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['six_month'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['one_year'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['two_year'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['three_year'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['four_year'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['five_year'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;padding:12px 10px;"><?= htmlspecialchars($row['since_inception'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="perf-kpi-row reveal d1">
      <div class="kpi-tile">
        <div class="kpi-tile-bar" style="background:var(--green)"></div>
        <div class="kpi-tile-lbl">Total Return</div>
        <div class="kpi-tile-val g">+6.95%</div>
        <div class="kpi-tile-sub">Since inception · Oct 2025</div>
        <div class="kpi-vs">↑ Nifty returned only +0.96%</div>
      </div>
      <div class="kpi-tile">
        <div class="kpi-tile-bar" style="background:var(--brand-soft2)"></div>
        <div class="kpi-tile-lbl">Alpha Generated</div>
        <div class="kpi-tile-val t">+5.98%</div>
        <div class="kpi-tile-sub">Outperformance vs Nifty 50</div>
        <div class="kpi-vs">↑ 4 of 5 months ahead</div>
      </div>
      <div class="kpi-tile">
        <div class="kpi-tile-bar" style="background:var(--brand)"></div>
        <div class="kpi-tile-lbl">Max Drawdown</div>
        <div class="kpi-tile-val go">−5.07%</div>
        <div class="kpi-tile-sub">Peak-to-trough, contained</div>
        <div class="kpi-vs">↑ Nifty fell −5.71%</div>
      </div>
      <div class="kpi-tile">
        <div class="kpi-tile-bar" style="background:var(--ash)"></div>
        <div class="kpi-tile-lbl">Sharpe Ratio</div>
        <div class="kpi-tile-val w">0.62</div>
        <div class="kpi-tile-sub">6.5% risk-free rate assumed</div>
        <div class="kpi-vs">↑ Nifty Sharpe: −0.33</div>
      </div>
    </div>

    <div class="monthly-grid reveal d2">
      <div class="m-cell">
        <div class="m-cell-mo">Oct 2025</div>
        <div class="m-cell-ret pos">+4.30%</div>
        <div class="m-cell-bench">Nifty: +2.15%</div>
        <div class="m-cell-alpha pos">α +2.15%</div>
      </div>
      <div class="m-cell">
        <div class="m-cell-mo">Nov 2025</div>
        <div class="m-cell-ret flat">+0.00%</div>
        <div class="m-cell-bench">Nifty: +1.71%</div>
        <div class="m-cell-alpha neg">α −1.71%</div>
      </div>
      <div class="m-cell">
        <div class="m-cell-mo">Dec 2025</div>
        <div class="m-cell-ret pos">+1.40%</div>
        <div class="m-cell-bench">Nifty: −0.18%</div>
        <div class="m-cell-alpha pos">α +1.58%</div>
      </div>
      <div class="m-cell">
        <div class="m-cell-mo">Jan 2026</div>
        <div class="m-cell-ret neg">−2.61%</div>
        <div class="m-cell-bench">Nifty: −3.16%</div>
        <div class="m-cell-alpha pos">α +0.55%</div>
      </div>
      <div class="m-cell">
        <div class="m-cell-mo">Feb 2026*</div>
        <div class="m-cell-ret pos">+3.60%</div>
        <div class="m-cell-bench">Nifty: +2.41%</div>
        <div class="m-cell-alpha pos">α +1.18%</div>
      </div>
    </div>
    <div class="perf-note reveal d3">
      *Feb 2026 data through 24th Feb. Returns computed from SEBI-registered custodian records. Past performance is not indicative of future results.
    </div>

    <div style="margin-top:56px;">
      <div class="s-eyebrow reveal" style="margin-bottom:0;">Independent Ranking Verification</div>
      <div class="rank-grid">
        <div class="rank-card reveal d1">
          <div class="rank-card-month">October 2025</div>
          <div class="rank-card-num">#1</div>
          <div class="rank-card-of">of 12 Multi Asset PMS funds</div>
          <div class="rank-card-ret">+4.29%</div>
          <div class="rank-card-context">Category rank in our very first full month of operation. Verified on PMS Bazaar.</div>
        </div>
        <div class="rank-card reveal d2">
          <div class="rank-card-month">December 2025</div>
          <div class="rank-card-num">#1</div>
          <div class="rank-card-of">of 13 Multi Asset PMS funds</div>
          <div class="rank-card-ret">+5.55% since inception</div>
          <div class="rank-card-context">Maintained #1 rank 2 months later. AUM grew to ₹10.14 Cr within 60 days of launch.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
