<?php
$page_title = 'About — PlusWealth PMS';
include 'header.php';

require_once __DIR__ . '/includes/db_config.php';

$annualizedReturn = '+6.95%';
$latestDataDate = '24 Feb 2026';
$maxDrawdown = '−5.07%';
$benchmarkDrawdown = '−5.71%';

try {
  $conn = get_db_connection();
  $r = $conn->query(
    "SELECT since_inception, DATE_FORMAT(LAST_DAY(month_year), '%d %b %Y') AS latest_date
     FROM performance_returns
     WHERE is_active = 1 AND strategy = 'PlusWealth Fusion'
     ORDER BY month_year DESC
     LIMIT 1"
  );
  if ($r instanceof mysqli_result && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    $val = trim($row['since_inception'] ?? '');
    if ($val && $val !== '-') {
      $annualizedReturn = (strpos($val, '%') === false ? '+' : '') . htmlspecialchars($val, ENT_QUOTES);
    }
    $latestDataDate = htmlspecialchars($row['latest_date'], ENT_QUOTES);
  }

  // Fetch max drawdown from key_metrics table
  $m = $conn->query(
    "SELECT metric_value, benchmark_value
     FROM key_metrics
     WHERE metric_key = 'max_drawdown' AND is_active = 1"
  );
  if ($m instanceof mysqli_result && $m->num_rows > 0) {
    $metrics = $m->fetch_assoc();
    $maxDrawdown = htmlspecialchars($metrics['metric_value'], ENT_QUOTES);
    $benchmarkDrawdown = htmlspecialchars($metrics['benchmark_value'], ENT_QUOTES);
  }
} catch (Throwable $e) {
  error_log('About page stats query failed: ' . $e->getMessage());
}
?>

<!-- WHO WE ARE -->
<div class="section-wrap" id="about">
  <div class="inner">
    <div class="who-grid">
      <div>
        <div class="s-eyebrow reveal">Who We Are</div>
        <h2 class="s-title reveal d1">Built for investors who<br><em>think in decades.</em></h2>
        <p class="s-body reveal d2" style="margin-top:24px;">
          PlusWealth is a SEBI-registered PMS designed for high-net-worth investors who want a disciplined, research-backed approach — not market speculation, not emotion-driven calls. Every decision is codified, backtested, and executed by algorithm.
        </p>

        <div class="who-pillars reveal d3">
          <div class="pillar">
            <span class="pillar-icon">📐</span>
            <div class="pillar-title">Evidence-Based</div>
            <div class="pillar-body">Every strategy is derived from quantitative research and validated through rigorous historical testing.</div>
          </div>
          <div class="pillar">
            <span class="pillar-icon">🛡️</span>
            <div class="pillar-title">Capital Preservation</div>
            <div class="pillar-body">Regime detection and tactical hedges activate before drawdowns compound into wealth destruction.</div>
          </div>
          <div class="pillar">
            <span class="pillar-icon">🔁</span>
            <div class="pillar-title">Consistent Process</div>
            <div class="pillar-body">Rules remove bias. Codified decisions can be measured, improved, and replicated across market cycles.</div>
          </div>
          <div class="pillar">
            <span class="pillar-icon">🔭</span>
            <div class="pillar-title">Long-Horizon Compounding</div>
            <div class="pillar-body">Protecting the compounding curve matters more than chasing short-term alpha. We minimise deep drawdowns.</div>
          </div>
        </div>
      </div>

      <div style="padding-top:210px;">
        <div class="stat-stack reveal d2">
          <div class="stat-row">
            <div class="stat-row-label">Annualised return (live, since inception)</div>
            <div class="stat-row-val g"><?= $annualizedReturn ?></div>
          </div>
          <div class="stat-row">
            <div class="stat-row-label">Max drawdown (vs Nifty <?= $benchmarkDrawdown ?>)</div>
            <div class="stat-row-val w"><?= $maxDrawdown ?></div>
          </div>
        </div>
        <div style="font-size:11px;color:var(--slate);margin-top:12px;font-style:italic;line-height:1.6;">
          Live data as of <?= $latestDataDate ?>. Past performance is not indicative of future results.
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
