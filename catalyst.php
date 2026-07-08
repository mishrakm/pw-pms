<?php
$page_title = 'The strategy - PlusWealth Catalyst';
$current_strategy = 'catalyst';
include 'header.php';

$performanceRows = [
  [
    'month_year' => '2026-03',
    'strategy' => 'PlusWealth Catalyst',
    'one_month' => '-',
    'three_month' => '-',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '-',
  ],
  [
    'month_year' => '2026-03',
    'strategy' => 'Benchmark: NIFTY 500 TRI',
    'one_month' => '-',
    'three_month' => '-',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '-',
  ],
];

$availableMonths = [
  [
    'value' => '2026-03',
    'label' => 'Mar 2026',
  ],
];
$selectedMonthYear = '2026-03';
$latestDataDate = 'Date unknown';

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
     WHERE is_active = 1 AND strategy_key = 'catalyst' AND month_year IS NOT NULL
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
     WHERE is_active = 1 AND strategy_key = 'catalyst' AND DATE_FORMAT(month_year, '%Y-%m') = ?
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

  $r = $conn->query(
    "SELECT DATE_FORMAT(LAST_DAY(MAX(month_year)), '%d %b %Y') AS latest
     FROM performance_returns
     WHERE is_active = 1 AND strategy_key = 'catalyst'"
  );
  if ($r instanceof mysqli_result && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    $latestDataDate = $row['latest'] ?? 'Date unknown';
  }
} catch (Throwable $e) {
  error_log('Catalyst performance table load failed: ' . $e->getMessage());
}
?>

<style>
.catalyst-stat-row {
  margin-top: 48px;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
}
.catalyst-stat {
  background: var(--panel);
  padding: 28px 24px 24px;
  position: relative;
  overflow: hidden;
}
.catalyst-stat-bar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
}
.catalyst-stat-value {
  font-family: var(--f-display);
  font-size: 42px;
  font-weight: 600;
  line-height: 1;
  margin-bottom: 8px;
}
.catalyst-stat-value.g { color: var(--green); }
.catalyst-stat-value.go { color: var(--brand-soft); }
.catalyst-stat-value.t { color: var(--brand-soft2); }
.catalyst-stat-value.w { color: var(--white); }
.catalyst-stat-label {
  font-size: 12px;
  color: var(--slate);
  line-height: 1.45;
}
@media (max-width: 900px) {
  .catalyst-stat-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
  .catalyst-stat-row { grid-template-columns: 1fr; }
}
</style>

<div class="section-wrap alt" id="strategy">
  <div class="inner">
    <div class="strategy-intro">
      <div>
        <div class="s-eyebrow reveal" style="font-size: 1rem;">The Strategy - PlusWealth Catalyst</div>
        <h2 class="s-title reveal d1">Focused trends.<br><em>Fast discipline.</em></h2>
      </div>
      <div class="s-body reveal d2" style="margin: 0;">
        PlusWealth Catalyst runs 8–12 concentrated small cap positions, selected through a dual fundamental-and-technical framework — built for investors with a 3+ year horizon who want outsized, asymmetric compounding.
      </div>
    </div>

    <div class="strategy-cards reveal d1">
      <div class="s-card c1">
        <div class="s-card-num">01 / SCREENING</div>
        <div class="s-card-icon t">C</div>
        <h3>Leadership-First<br>Fundamental Screening</h3>
        <p>Business quality, earnings trajectory, management integrity, competitive moats, and balance sheet strength — filtered across 250+ small caps before any name reaches the watchlist.</p>
      </div>
      <div class="s-card c2">
        <div class="s-card-num">02 / TIMING</div>
        <div class="s-card-icon g">T</div>
        <h3>Regime-Based<br>Technical Validation</h3>
        <p>Price action, volume patterns, momentum signals, entry timing, and exit discipline. A great business at the wrong price is still the wrong trade.</p>
      </div>
      <div class="s-card c3">
        <div class="s-card-num">03 / CONVICTION</div>
        <div class="s-card-icon gr">R</div>
        <h3>Conviction Position</h3>
        <p>Watchlist becomes a focused position once thesis is confirmed by chart data — sized by conviction and liquidity, with ongoing thesis monitoring. 8–12 holdings only, no dilution.</p>
      </div>
    </div>

    <div class="catalyst-stat-row reveal d2">
      <div class="catalyst-stat">
        <div class="catalyst-stat-bar" style="background:var(--green);"></div>
        <div class="catalyst-stat-value g">40%+</div>
        <div class="catalyst-stat-label">Cumulative return</div>
      </div>
      <div class="catalyst-stat">
        <div class="catalyst-stat-bar" style="background:var(--brand-soft);"></div>
        <div class="catalyst-stat-value go">&#8377;50L</div>
        <div class="catalyst-stat-label">Minimum investment</div>
      </div>
      <div class="catalyst-stat">
        <div class="catalyst-stat-bar" style="background:var(--brand-soft2);"></div>
        <div class="catalyst-stat-value t">26.56%</div>
        <div class="catalyst-stat-label">Annualised volatility</div>
      </div>
      <div class="catalyst-stat">
        <div class="catalyst-stat-bar" style="background:var(--white);"></div>
        <div class="catalyst-stat-value w">3+ Yrs</div>
        <div class="catalyst-stat-label">Recommended horizon</div>
      </div>
    </div>
  </div>
</div>

<div class="section-wrap" id="framework">
  <div class="inner">
    <div style="max-width:680px;margin:0 0 48px;">
      <div class="s-eyebrow reveal" style="justify-content:flex-start;">How We Invest</div>
      <h2 class="s-title reveal d1">Systematic,<br><em>repeatable conviction</em></h2>
      <p class="s-body reveal d2" style="margin:16px 0 0;">
        Five disciplined steps for every position — no shortcuts, no overrides based on tips or market noise.
      </p>
    </div>

    <div class="process-track">
      <div class="p-step reveal d1">
        <div class="p-circle">1</div>
        <h4>Universe Screening</h4>
        <p>Quantitative filters across 250+ small caps: earnings growth, ROE, debt, promoter holding, liquidity thresholds.</p>
      </div>
      <div class="p-step reveal d2">
        <div class="p-circle">2</div>
        <h4>Fundamental Deep Dive</h4>
        <p>Bottoms-up analysis of business model, competitive positioning, management quality, earnings trajectory.</p>
      </div>
      <div class="p-step reveal d3">
        <div class="p-circle">3</div>
        <h4>Governance Check</h4>
        <p>Promoter pledging, related-party transactions, media signals, and SEBI actions screened out.</p>
      </div>
      <div class="p-step reveal d4">
        <div class="p-circle">4</div>
        <h4>Technical Entry</h4>
        <p>Chart analysis identifies the optimal entry window — entry discipline is non-negotiable.</p>
      </div>
      <div class="p-step reveal d5">
        <div class="p-circle">5</div>
        <h4>Position Sizing</h4>
        <p>Sized by conviction, liquidity, and portfolio risk limits — no single name exceeds concentration limits.</p>
      </div>
    </div>
  </div>
</div>

<div class="section-wrap" id="performance">
  <div class="inner">
    <div class="perf-header">
      <div>
        <div class="s-eyebrow reveal">Actual Performance</div>
        <h2 class="s-title reveal d1">Catalyst results.<br><em>Tracked separately.</em></h2>
      </div>
      <div class="actual-badge reveal d2">
        <div class="signal-dot"></div>
        LIVE DATA - UPDATED <?= htmlspecialchars($latestDataDate, ENT_QUOTES) ?>
      </div>
    </div>

    <div class="reveal d3" style="margin-bottom:48px;">
      <canvas id="catalystPerfChart" style="width:100%;max-height:360px;"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function(){
      <?php
        $toNum = function($v) { $v = trim((string)$v); return ($v === '-' || $v === '') ? 'null' : (float)str_replace('%','',$v); };
        $periods   = ['1 Month','3 Month','6 Month','1 Year','2 Year','3 Year','4 Year','5 Year','Since Inception'];
        $keys      = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];
        $strategyRow = $performanceRows[0] ?? [];
        $benchRow    = $performanceRows[1] ?? [];
        $strategyVals = array_map(fn($k) => $toNum($strategyRow[$k] ?? '-'), $keys);
        $benchVals    = array_map(fn($k) => $toNum($benchRow[$k]  ?? '-'), $keys);
        echo 'const labels  = ' . json_encode($periods) . ";\n";
        echo 'const catalyst = [' . implode(',', $strategyVals) . "];\n";
        echo 'const bench   = [' . implode(',', $benchVals)  . "];\n";
      ?>
      const chartEl = document.getElementById('catalystPerfChart');
      if (!chartEl || typeof Chart === 'undefined') return;
      new Chart(chartEl.getContext('2d'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'PlusWealth Catalyst',
              data: catalyst,
              backgroundColor: catalyst.map(v => v === null ? 'transparent' : v >= 0 ? 'rgba(69,179,227,0.82)' : 'rgba(248,113,113,0.82)'),
              borderRadius: 4,
              borderSkipped: false,
              skipNull: true,
            },
            {
              label: 'Benchmark',
              data: bench,
              backgroundColor: 'rgba(110,111,111,0.50)',
              borderRadius: 4,
              borderSkipped: false,
              skipNull: true,
            }
          ]
        },
        options: {
          responsive: true,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: {
              labels: { color: '#ddd', font: { family: "'DM Mono', monospace", size: 11 }, boxWidth: 14, padding: 20 }
            },
            tooltip: {
              callbacks: {
                label: c => c.parsed.y === null ? null : ' ' + c.dataset.label + ': ' + (c.parsed.y >= 0 ? '+' : '') + c.parsed.y.toFixed(2) + '%'
              }
            }
          },
          scales: {
            x: {
              ticks: { color: '#aaa', font: { family: "'DM Mono', monospace", size: 11 } },
              grid: { color: 'rgba(255,255,255,0.06)' }
            },
            y: {
              ticks: {
                color: '#aaa',
                font: { family: "'DM Mono', monospace", size: 11 },
                callback: v => (v >= 0 ? '+' : '') + v + '%'
              },
              grid: { color: 'rgba(255,255,255,0.06)' },
              border: { dash: [4,4] }
            }
          }
        }
      });
    })();
    </script>

    <?php
    $fields       = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];
    $periodLabels = ['1 Month','3 Month','6 Month','1 Year','2 Year','3 Year','4 Year','5 Year','Since Inception'];
    $strategyRow  = $performanceRows[0] ?? [];
    $benchRow     = $performanceRows[1] ?? [];
    $fmtVal = function($v) {
      $v = trim((string)$v);
      if ($v === '-' || $v === '') return null;
      $n = (float)str_replace('%', '', $v);
      return ['num' => $n, 'str' => ($n >= 0 ? '+' : '') . number_format($n, 2) . '%'];
    };
    ?>
    <div class="reveal d3" style="margin-bottom:48px;overflow-x:auto;-webkit-overflow-scrolling:touch;">
      <div style="min-width:780px;">
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);border-bottom:none;">
        <div style="background:var(--panel);padding:10px 14px;font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--slate);">Strategy</div>
        <?php foreach ($periodLabels as $lbl): ?>
        <div style="background:var(--panel);padding:10px 8px;font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--slate);text-align:center;"><?= $lbl ?></div>
        <?php endforeach; ?>
      </div>
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);border-bottom:none;">
        <div style="background:var(--panel);padding:20px 14px;font-family:var(--f-body);font-size:13px;letter-spacing:0;color:var(--white);font-weight:600;display:flex;align-items:center;">PlusWealth Catalyst</div>
        <?php foreach ($fields as $f):
          $d = $fmtVal($strategyRow[$f] ?? '-');
          $col = $d === null ? 'var(--ash)' : ($d['num'] >= 0 ? 'var(--green)' : 'var(--red)');
          $bar = $d === null ? 'transparent' : ($d['num'] >= 0 ? 'var(--brand-soft)' : 'var(--red)');
        ?>
        <div style="background:var(--panel);padding:20px 8px 16px;position:relative;overflow:hidden;text-align:center;">
          <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $bar ?>"></div>
          <div style="font-family:var(--f-display);font-size:22px;font-weight:600;color:<?= $col ?>;line-height:1;">
            <?= $d ? htmlspecialchars($d['str'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--ash);font-size:16px;">&mdash;</span>' ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);">
        <div style="background:var(--panel);padding:20px 14px;font-family:var(--f-body);font-size:13px;letter-spacing:0;color:var(--slate);font-weight:500;display:flex;align-items:center;">Benchmark*</div>
        <?php foreach ($fields as $f):
          $d = $fmtVal($benchRow[$f] ?? '-');
          $col = $d === null ? 'var(--ash)' : ($d['num'] >= 0 ? 'var(--green)' : 'var(--red)');
        ?>
        <div style="background:var(--panel);padding:20px 8px 16px;text-align:center;">
          <div style="font-family:var(--f-display);font-size:22px;font-weight:600;color:<?= $col ?>;line-height:1;">
            <?= $d ? htmlspecialchars($d['str'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--ash);font-size:16px;">&mdash;</span>' ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      </div>
    </div>

    <div class="perf-note reveal d3" style="margin-top:10px;">
      *Benchmark: NIFTY 500 TRI
      <br><br>
      Six months live. Monthly return breakdowns and independent category-ranking verification aren't yet available for Catalyst &mdash; shown here only once a custodian statement or PMS Bazaar listing publishes verified figures. Past performance is not indicative of future results.
    </div>
  </div>
</div>

<div class="section-wrap alt" id="contact-catalyst">
  <div class="inner" style="max-width:900px;">
    <div class="perf-header" style="align-items:flex-end;">
      <div>
        <div class="s-eyebrow reveal">Explore The Strategy</div>
        <h2 class="s-title reveal d1">Interested in Catalyst?<br><em>Speak with the team.</em></h2>
      </div>
      <a href="contact.php" class="nav-btn reveal d2">Connect</a>
    </div>
    <p class="s-body reveal d3" style="max-width:720px;margin:20px 0 0;">
      We can walk you through how Catalyst differs from Fusion, where it may fit within an overall allocation, and how the portfolio rules are intended to behave across market regimes.
    </p>
  </div>
</div>

<?php include 'footer.php'; ?>
