<?php
$page_title = 'Strategy — PlusWealth PMS';
include 'header.php';

$performanceRows = [
  [
    'month_year' => '2026-03',
    'strategy' => 'PlusWealth Fusion',
    'one_month' => '-4.76',
    'three_month' => '-2.6',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '2.81%',
  ],
  [
    'month_year' => '2026-03',
    'strategy' => "Benchmark: NSE Multi Asset Index 2\n(50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)",
    'one_month' => '-6.2',
    'three_month' => '-6.88',
    'six_month' => '-',
    'one_year' => '-',
    'two_year' => '-',
    'three_year' => '-',
    'four_year' => '-',
    'five_year' => '-',
    'since_inception' => '-4.78%',
  ],
];

$availableMonths = [
  [
    'value' => '2026-03',
    'label' => 'Mar 2026',
  ],
];
$selectedMonthYear = '2026-03';

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

<!-- STRATEGY -->
<div class="section-wrap alt" id="strategy">
  <div class="inner">
    <div class="strategy-intro">
      <div>
        <div class="s-eyebrow reveal">The Strategy</div>
        <h2 class="s-title reveal d1">Three engines.<br><em>One mission.</em></h2>
      </div>
      <div class="s-body reveal d2" style="margin: 0;">
        PlusWealth Fusion combines three independent alpha sources working in concert — multi-asset allocation, factor rotation, and an adaptive hedge overlay — to deliver smoother, superior compounding across all market regimes.
      </div>
    </div>

    <div class="strategy-cards reveal d1">
      <div class="s-card c1">
        <div class="s-card-num">01 / ALLOCATION</div>
        <div class="s-card-icon t">🔄</div>
        <h3>Multi-Asset<br>Allocation</h3>
        <p>Dynamically rotate between equity, debt, and gold based on quantitative macro signals — not hunches. Capital shifts to safety before markets punish those who wait.</p>
        <div class="s-card-proof t">Drawdown 70% smaller vs pure equity</div>
      </div>
      <div class="s-card c2">
        <div class="s-card-num">02 / EQUITY</div>
        <div class="s-card-icon g">⚡</div>
        <h3>Factor-Based<br>Equity Exposure</h3>
        <p>Rotate between Value, Quality, and Momentum factors as market leadership shifts. Our signals identify the winning factor monthly — and tilt sharply toward it.</p>
        <div class="s-card-proof g">66% monthly outperformance rate (backtest)</div>
      </div>
      <div class="s-card c3">
        <div class="s-card-num">03 / PROTECTION</div>
        <div class="s-card-icon gr">🛡️</div>
        <h3>Protective Hedge<br>Overlay</h3>
        <p>Adaptive NIFTY put options — fully funded, zero leverage. Activated at elevated valuations. In Sep 2022, when NIFTY fell 6.7%, our put position gained +127%, limiting portfolio loss to −0.76%.</p>
        <div class="s-card-proof gr">+127% put gain in Sep 2022 correction</div>
      </div>
    </div>


  </div>
</div>

<!-- GOVERNANCE FILTER -->
<div class="section-wrap" id="governance">
  <div class="inner">
    <div style="max-width:680px;margin:0 0 48px;">
      <div class="s-eyebrow reveal" style="justify-content:flex-start;">Governance Filter</div>
      <h2 class="s-title reveal d1">Every stock screened before<br>capital is deployed</h2>
      <p class="s-body reveal d2" style="margin:16px 0 0;">
        After our quant signal fires, each equity position clears a 15-signal governance check — quantitative and qualitative — before allocation proceeds.
      </p>
    </div>

    <!-- 4-card grid -->
    <div class="reveal d2" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:16px;padding:40px;display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-bottom:32px;">

      <!-- 01 Promoter behaviour -->
      <div style="padding:0 28px 0 0;border-right:1px solid var(--border);">
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;">
          <span style="font-family:var(--f-mono);font-size:11px;color:var(--ash);">01</span>
          <span style="font-family:var(--f-display);font-size:32px;font-weight:700;color:var(--brand);">30%</span>
        </div>
        <div style="font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;color:var(--ash);text-transform:uppercase;margin-bottom:16px;">filtered</div>
        <div style="width:32px;height:3px;background:var(--brand);margin-bottom:20px;"></div>
        <h4 style="font-family:var(--f-body);font-size:15px;font-weight:700;color:var(--white);margin:0 0 10px;">Promoter behaviour</h4>
        <p style="font-family:var(--f-body);font-size:13px;color:var(--slate);margin:0 0 16px;line-height:1.6;">Assessing owner integrity and commitment through capital actions.</p>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;"></span>Pledge level</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;"></span>Insider trading</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;"></span>Promoter stake</li>
        </ul>
      </div>

      <!-- 02 Financial integrity -->
      <div style="padding:0 28px;border-right:1px solid var(--border);">
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;">
          <span style="font-family:var(--f-mono);font-size:11px;color:var(--ash);">02</span>
          <span style="font-family:var(--f-display);font-size:32px;font-weight:700;color:var(--green);">38%</span>
        </div>
        <div style="font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;color:var(--ash);text-transform:uppercase;margin-bottom:16px;">filtered</div>
        <div style="width:32px;height:3px;background:var(--green);margin-bottom:20px;"></div>
        <h4 style="font-family:var(--f-body);font-size:15px;font-weight:700;color:var(--white);margin:0 0 10px;">Financial integrity</h4>
        <p style="font-family:var(--f-body);font-size:13px;color:var(--slate);margin:0 0 16px;line-height:1.6;">Validating balance sheet quality and earnings sustainability.</p>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--green);flex-shrink:0;"></span>CFO / PAT quality</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--green);flex-shrink:0;"></span>RPT % monitoring</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:var(--green);flex-shrink:0;"></span>Debt growth</li>
        </ul>
      </div>

      <!-- 03 Capital allocation -->
      <div style="padding:0 28px;border-right:1px solid var(--border);">
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;">
          <span style="font-family:var(--f-mono);font-size:11px;color:var(--ash);">03</span>
          <span style="font-family:var(--f-display);font-size:32px;font-weight:700;color:#F59E0B;">17%</span>
        </div>
        <div style="font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;color:var(--ash);text-transform:uppercase;margin-bottom:16px;">filtered</div>
        <div style="width:32px;height:3px;background:#F59E0B;margin-bottom:20px;"></div>
        <h4 style="font-family:var(--f-body);font-size:15px;font-weight:700;color:var(--white);margin:0 0 10px;">Capital allocation</h4>
        <p style="font-family:var(--f-body);font-size:13px;color:var(--slate);margin:0 0 16px;line-height:1.6;">Evaluating management's efficiency in deploying capital.</p>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#F59E0B;flex-shrink:0;"></span>Capex execution</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#F59E0B;flex-shrink:0;"></span>ROE trend</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#F59E0B;flex-shrink:0;"></span>Dividend consistency</li>
        </ul>
      </div>

      <!-- 04 News & qualitative signals -->
      <div style="padding:0 0 0 28px;">
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;">
          <span style="font-family:var(--f-mono);font-size:11px;color:var(--ash);">04</span>
          <span style="font-family:var(--f-display);font-size:32px;font-weight:700;color:#A78BFA;">15%</span>
        </div>
        <div style="font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;color:var(--ash);text-transform:uppercase;margin-bottom:16px;">filtered</div>
        <div style="width:32px;height:3px;background:#A78BFA;margin-bottom:20px;"></div>
        <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(167,139,250,0.12);border:1px solid rgba(167,139,250,0.3);border-radius:20px;padding:3px 10px;margin-bottom:12px;">
          <span style="width:6px;height:6px;border-radius:50%;background:#A78BFA;"></span>
          <span style="font-family:var(--f-mono);font-size:9px;letter-spacing:1px;color:#A78BFA;text-transform:uppercase;">AI-powered</span>
        </div>
        <h4 style="font-family:var(--f-body);font-size:15px;font-weight:700;color:var(--white);margin:0 0 10px;">News &amp; qualitative signals</h4>
        <p style="font-family:var(--f-body);font-size:13px;color:var(--slate);margin:0 0 16px;line-height:1.6;">Real-time monitoring of corporate actions and management tone.</p>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#A78BFA;flex-shrink:0;"></span>SEBI actions</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#A78BFA;flex-shrink:0;"></span>Management credibility</li>
          <li style="font-family:var(--f-body);font-size:12px;color:var(--ash);display:flex;align-items:center;gap:6px;"><span style="width:6px;height:6px;border-radius:50%;background:#A78BFA;flex-shrink:0;"></span>Guidance accuracy</li>
        </ul>
      </div>

    </div>

    <!-- Pipeline footer bar -->
    <div class="reveal d3" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <span style="font-family:var(--f-body);font-size:14px;color:var(--white);"><strong>1,000+</strong> stocks universe</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ash)" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        <span style="font-family:var(--f-body);font-size:14px;color:var(--white);">15-signal check</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ash)" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        <span style="font-family:var(--f-body);font-size:14px;color:var(--white);"><strong>Only the best</strong> enter the portfolio</span>
      </div>
      <span style="font-family:var(--f-mono);font-size:11px;color:var(--ash);letter-spacing:1px;">Weightings reviewed quarterly</span>
    </div>

  </div>
</div>

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
        LIVE DATA · UPDATED 31 MAR 2026
      </div>
    </div>


    <!-- MONTHLY RETURNS CHART -->
    <div class="reveal d3" style="margin-bottom:48px;">
      <canvas id="perfChart" style="width:100%;max-height:360px;"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    (function(){
      <?php
        // Build per-period arrays from $performanceRows (null for '-')
        $toNum = function($v) { $v = trim((string)$v); return ($v === '-' || $v === '') ? 'null' : (float)str_replace('%','',$v); };
        $periods   = ['1 Month','3 Month','6 Month','1 Year','2 Year','3 Year','4 Year','5 Year','Since Inception'];
        $keys      = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];
        $fusionRow = $performanceRows[0] ?? [];
        $benchRow  = $performanceRows[1] ?? [];
        $fusionVals = array_map(fn($k) => $toNum($fusionRow[$k] ?? '-'), $keys);
        $benchVals  = array_map(fn($k) => $toNum($benchRow[$k]  ?? '-'), $keys);
        echo 'const labels  = ' . json_encode($periods) . ";\n";
        echo 'const fusion  = [' . implode(',', $fusionVals) . "];\n";
        echo 'const bench   = [' . implode(',', $benchVals)  . "];\n";
      ?>
      const ctx = document.getElementById('perfChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'PlusWealth Fusion',
              data: fusion,
              backgroundColor: fusion.map(v => v === null ? 'transparent' : v >= 0 ? 'rgba(34,197,94,0.82)' : 'rgba(248,113,113,0.82)'),
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

    <!-- PERIOD RETURNS TABLE -->
    <?php
    $fields      = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];
    $periodLabels= ['1 Month','3 Month','6 Month','1 Year','2 Year','3 Year','4 Year','5 Year','Since Inception'];
    $fusionRow   = $performanceRows[0] ?? [];
    $benchRow    = $performanceRows[1] ?? [];
    $fmtVal = function($v) {
      $v = trim((string)$v);
      if ($v === '-' || $v === '') return null;
      $n = (float)str_replace('%', '', $v);
      return ['num' => $n, 'str' => ($n >= 0 ? '+' : '') . number_format($n, 2) . '%'];
    };
    ?>
    <div class="reveal d3" style="margin-bottom:48px;overflow-x:auto;-webkit-overflow-scrolling:touch;">
      <div style="min-width:780px;">
      <!-- header row -->
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);border-bottom:none;">
        <div style="background:var(--panel);padding:10px 14px;font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--slate);">Strategy</div>
        <?php foreach ($periodLabels as $lbl): ?>
        <div style="background:var(--panel);padding:10px 8px;font-family:var(--f-mono);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--slate);text-align:center;"><?= $lbl ?></div>
        <?php endforeach; ?>
      </div>
      <!-- Fusion row -->
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);border-bottom:none;">
        <div style="background:var(--panel);padding:20px 14px;font-family:var(--f-body);font-size:13px;letter-spacing:0;color:var(--white);font-weight:600;display:flex;align-items:center;">PlusWealth Fusion</div>
        <?php foreach ($fields as $f):
          $d = $fmtVal($fusionRow[$f] ?? '-');
          $col = $d === null ? 'var(--ash)' : ($d['num'] >= 0 ? 'var(--green)' : 'var(--red)');
          $bar = $d === null ? 'transparent' : ($d['num'] >= 0 ? 'var(--green)' : 'var(--red)');
        ?>
        <div style="background:var(--panel);padding:20px 8px 16px;position:relative;overflow:hidden;text-align:center;">
          <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $bar ?>"></div>
          <div style="font-family:var(--f-display);font-size:22px;font-weight:600;color:<?= $col ?>;line-height:1;">
            <?= $d ? htmlspecialchars($d['str'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--ash);font-size:16px;">—</span>' ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <!-- Benchmark row -->
      <div style="display:grid;grid-template-columns:160px repeat(9,1fr);gap:1px;background:var(--border);border:1px solid var(--border);">
        <div style="background:var(--panel);padding:20px 14px;font-family:var(--f-body);font-size:13px;letter-spacing:0;color:var(--slate);font-weight:500;display:flex;align-items:center;">Benchmark*</div>
        <?php foreach ($fields as $f):
          $d = $fmtVal($benchRow[$f] ?? '-');
          $col = $d === null ? 'var(--ash)' : ($d['num'] >= 0 ? 'var(--green)' : 'var(--red)');
        ?>
        <div style="background:var(--panel);padding:20px 8px 16px;text-align:center;">
          <div style="font-family:var(--f-display);font-size:22px;font-weight:600;color:<?= $col ?>;line-height:1;">
            <?= $d ? htmlspecialchars($d['str'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--ash);font-size:16px;">—</span>' ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      </div>
    </div>

    <div class="perf-note reveal d3" style="margin-top:10px;">
      *Benchmark: NSE Multi Asset Index 2 — 50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT
    </div>

  </div>
</div>

<!-- INVESTMENT PROCESS -->
<div class="section-wrap" id="process">
  <div class="inner">
    <div style="max-width:680px;margin:0 0 48px;">
      <div class="s-eyebrow reveal" style="justify-content:flex-start;">How We Invest</div>
      <h2 class="s-title reveal d1">A repeatable, <em>rules-driven</em> process</h2>
      <p class="s-body reveal d2" style="margin: 16px 0 0;">
        Five disciplined steps — executed algorithmically, monitored continuously. No emotion. No discretion.
      </p>
    </div>
    <div class="process-track">
      <div class="p-step reveal d1">
        <div class="p-circle">1</div>
        <h4>Model Formation</h4>
        <p>Investment hypotheses derived from macro data, historical patterns, and economic frameworks</p>
      </div>
      <div class="p-step reveal d2">
        <div class="p-circle">2</div>
        <h4>Validation & Testing</h4>
        <p>Backtesting, scenario analysis, and bias checks before any capital is deployed</p>
      </div>
      <div class="p-step reveal d3">
        <div class="p-circle">3</div>
        <h4>Signal Generation</h4>
        <p>Model outputs translated to buy/sell/rotate signals across asset classes and factors</p>
      </div>
      <div class="p-step reveal d4">
        <div class="p-circle">4</div>
        <h4>Allocation & Execution</h4>
        <p>Capital deployed against risk limits, portfolio constraints, and optimisation rules</p>
      </div>
      <div class="p-step reveal d5">
        <div class="p-circle">5</div>
        <h4>Monitor & Rebalance</h4>
        <p>Continuous position monitoring; disciplined rebalancing when thresholds are breached</p>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
