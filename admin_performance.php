<?php
/**
 * Admin: Performance Returns Data Entry
 * Password-protected form to insert / update performance_returns rows.
 */

require_once __DIR__ . '/includes/db_config.php';

// ── Simple password gate ────────────────────────────────────────────────────
define('ADMIN_PASSWORD', 'PwAdmin2026!');

session_start();
$authError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['pw_admin_auth'] = true;
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_time'] = time();
    } else {
        $authError = 'Incorrect password.';
    }
}
if (isset($_POST['admin_logout'])) {
    unset($_SESSION['pw_admin_auth']);
  unset($_SESSION['admin_authenticated']);
}

$authed = !empty($_SESSION['pw_admin_auth']) || !empty($_SESSION['admin_authenticated']);

// ── Process form submission ─────────────────────────────────────────────────
$successMsg = '';
$errorMsg   = '';

// Delete month
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_month'])) {
    try {
        $conn      = get_db_connection();
        $delMonth  = trim($_POST['delete_month']);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $delMonth)) {
            throw new InvalidArgumentException('Invalid month.');
        }
        $d = $conn->prepare("DELETE FROM performance_returns WHERE DATE_FORMAT(month_year,'%Y-%m') = ?");
        $d->bind_param('s', $delMonth);
        $d->execute();
        $affected = $d->affected_rows;
        $d->close();
        $successMsg = "Deleted <strong>" . htmlspecialchars($delMonth, ENT_QUOTES) . "</strong> ($affected rows removed).";
    } catch (Throwable $e) {
        $errorMsg = 'Delete failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        error_log('admin_performance delete error: ' . $e->getMessage());
    }
}

if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_performance'])) {
    try {
        $conn = get_db_connection();

        $monthYear = trim($_POST['month_year'] ?? '');
        // Validate YYYY-MM format
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthYear)) {
            throw new InvalidArgumentException('Invalid month format. Use YYYY-MM.');
        }
        $monthDate = $monthYear . '-01'; // Store as first of month

        $rows = [
            [
                'strategy_key'  => 'fusion',
                'strategy'      => 'PlusWealth Fusion',
                'display_order' => 1,
                'fields'        => 'fusion',
            ],
            [
                'strategy_key'  => 'fusion',
                'strategy'      => "Benchmark: NSE Multi Asset Index 2\n(50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)",
                'display_order' => 2,
                'fields'        => 'bench',
            ],
            [
                'strategy_key'  => 'catalyst',
                'strategy'      => 'PlusWealth Catalyst',
                'display_order' => 1,
                'fields'        => 'catalyst',
            ],
            [
                'strategy_key'  => 'catalyst',
                'strategy'      => 'Benchmark: NSE Multi Asset Index 2',
                'display_order' => 2,
                'fields'        => 'catalyst_bench',
            ],
        ];

        $periods = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];

        // Delete existing rows for this month before re-inserting (upsert via replace)
        $del = $conn->prepare("DELETE FROM performance_returns WHERE DATE_FORMAT(month_year,'%Y-%m') = ?");
        $del->bind_param('s', $monthYear);
        $del->execute();
        $del->close();

        $sql = "INSERT INTO performance_returns
                    (month_year, strategy_key, strategy, one_month, three_month, six_month,
                     one_year, two_year, three_year, four_year, five_year,
                     since_inception, is_active, display_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";

        $stmt = $conn->prepare($sql);

        foreach ($rows as $row) {
            $prefix = $row['fields'];
            $vals   = [];
            foreach ($periods as $p) {
                $raw = trim($_POST["{$prefix}_{$p}"] ?? '');
                $vals[] = $raw === '' ? '-' : $raw;
            }

            $stmt->bind_param(
                'ssssssssssssi',
                $monthDate,
                $row['strategy_key'],
                $row['strategy'],
                $vals[0], $vals[1], $vals[2],
                $vals[3], $vals[4], $vals[5],
                $vals[6], $vals[7], $vals[8],
                $row['display_order']
            );
            $stmt->execute();
        }
        $stmt->close();

        $successMsg = "Performance data for <strong>" . htmlspecialchars($monthYear, ENT_QUOTES) . "</strong> saved successfully.";

    } catch (Throwable $e) {
        $errorMsg = 'Save failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        error_log('admin_performance save error: ' . $e->getMessage());
    }
}

// ── Load existing months for the edit selector ──────────────────────────────
$existingMonths = [];
$editData       = [];

if ($authed) {
    try {
        $conn   = get_db_connection();
        $result = $conn->query(
            "SELECT DATE_FORMAT(month_year,'%Y-%m') AS mv, DATE_FORMAT(month_year,'%b %Y') AS ml
             FROM performance_returns
             WHERE is_active = 1
             GROUP BY DATE_FORMAT(month_year,'%Y-%m'), DATE_FORMAT(month_year,'%b %Y')
             ORDER BY month_year DESC"
        );
        while ($r = $result->fetch_assoc()) {
            $existingMonths[] = $r;
        }

        // Load a month for editing if requested
        if (!empty($_GET['edit'])) {
            $editMonth = trim($_GET['edit']);
            if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $editMonth)) {
                $s = $conn->prepare(
                    "SELECT * FROM performance_returns
                     WHERE is_active = 1 AND DATE_FORMAT(month_year,'%Y-%m') = ?
                     ORDER BY display_order ASC"
                );
                $s->bind_param('s', $editMonth);
                $s->execute();
                $res = $s->get_result();
                while ($row = $res->fetch_assoc()) {
                    $strategyKey = $row['strategy_key'] ?? 'fusion';
                    if ($strategyKey === 'catalyst') {
                        $key = ((int)$row['display_order'] === 1) ? 'catalyst' : 'catalyst_bench';
                    } else {
                        $key = ((int)$row['display_order'] === 1) ? 'fusion' : 'bench';
                    }
                    $editData[$key]               = $row;
                    $editData['month_year']       = $editMonth;
                }
                $s->close();
            }
        }
    } catch (Throwable $e) {
        error_log('admin_performance load error: ' . $e->getMessage());
    }
}

// Helper: get field value for edit pre-fill
function ev(array $editData, string $prefix, string $field): string {
    $v = $editData[$prefix][$field] ?? '';
    return htmlspecialchars($v === '-' ? '' : $v, ENT_QUOTES);
}

$periods      = ['one_month','three_month','six_month','one_year','two_year','three_year','four_year','five_year','since_inception'];
$periodLabels = ['1 Month','3 Month','6 Month','1 Year','2 Year','3 Year','4 Year','5 Year','Since Inception'];
$editMonthVal = htmlspecialchars($editData['month_year'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Performance Data Entry — PlusWealth Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&family=Cormorant+Garamond:ital,wght@0,600;1,500&display=swap" rel="stylesheet">
  <style>
    :root {
      --ink:    #080d16;
      --panel:  #0a101c;
      --border: rgba(255,255,255,0.10);
      --white:  #fff;
      --ash:    #fff;
      --slate:  #fff;
      --brand:  #015cd3;
      --brand-soft: #45b3e3;
      --green:  #22C55E;
      --red:    #F87171;
      --f-body: 'DM Sans', system-ui, sans-serif;
      --f-mono: 'DM Mono', monospace;
      --f-disp: 'Cormorant Garamond', Georgia, serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--ink); color: var(--white); font-family: var(--f-body); line-height: 1.6; min-height: 100vh; }

    /* ── Layout ── */
    .admin-wrap { max-width: 1100px; margin: 0 auto; padding: 48px 32px 80px; }
    .admin-header { border-bottom: 1px solid var(--border); padding-bottom: 24px; margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .admin-header h1 { font-family: var(--f-disp); font-size: 32px; font-weight: 600; }
    .admin-header .eyebrow { font-family: var(--f-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--brand-soft); margin-bottom: 6px; }

    /* ── Login box ── */
    .login-box { max-width: 380px; margin: 120px auto; background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 40px; }
    .login-box h2 { font-family: var(--f-disp); font-size: 26px; margin-bottom: 24px; }

    /* ── Cards ── */
    .card { background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 32px; margin-bottom: 32px; }
    .card-title { font-family: var(--f-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--brand-soft); margin-bottom: 20px; }

    /* ── Form elements ── */
    label { display: block; font-size: 12px; font-family: var(--f-mono); color: var(--ash); letter-spacing: 0.5px; margin-bottom: 6px; }
    input[type="text"], input[type="password"], input[type="month"], select {
      width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border);
      color: var(--white); font-family: var(--f-body); font-size: 14px;
      padding: 10px 14px; border-radius: 6px; outline: none; transition: border-color 0.2s;
    }
    input:focus, select:focus { border-color: var(--brand-soft); }
    .field-group { margin-bottom: 18px; }

    /* ── Period grid ── */
    .period-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    @media (max-width: 640px) { .period-grid { grid-template-columns: 1fr 1fr; } }

    /* ── Strategy section ── */
    .strategy-section { margin-bottom: 32px; }
    .strategy-label { font-size: 14px; font-weight: 600; color: var(--white); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }

    /* ── Buttons ── */
    .btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--brand); color: var(--white);
      border: none; padding: 12px 28px; font-family: var(--f-body); font-size: 14px; font-weight: 600;
      border-radius: 6px; cursor: pointer; transition: background 0.2s;
    }
    .btn-primary:hover { background: #0270ff; }
    .btn-ghost {
      display: inline-flex; align-items: center;
      background: transparent; color: var(--ash); border: 1px solid var(--border);
      padding: 10px 20px; font-family: var(--f-body); font-size: 13px;
      border-radius: 6px; cursor: pointer; text-decoration: none; transition: border-color 0.2s;
    }
    .btn-ghost:hover { border-color: var(--white); color: var(--white); }
    .btn-sm { padding: 7px 16px; font-size: 12px; }

    /* ── Alert ── */
    .alert { padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 24px; }
    .alert-success { background: rgba(34,197,94,0.10); border: 1px solid rgba(34,197,94,0.25); color: var(--green); }
    .alert-error   { background: rgba(248,113,113,0.10); border: 1px solid rgba(248,113,113,0.25); color: var(--red); }

    /* ── Existing months table ── */
    .months-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .months-table th { font-family: var(--f-mono); font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--ash); padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--border); }
    .months-table td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
    .months-table tr:last-child td { border-bottom: none; }
    .months-table tr:hover td { background: rgba(255,255,255,0.03); }

    /* ── Month selector bar ── */
    .month-bar { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  </style>
</head>
<body>
<div class="admin-wrap">

<?php if (!$authed): ?>
  <!-- ── LOGIN ── -->
  <div class="login-box">
    <div style="font-family:var(--f-mono);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--brand-soft);margin-bottom:8px;">PlusWealth Admin</div>
    <h2>Performance Data Entry</h2>
    <?php if ($authError): ?>
      <div class="alert alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($authError, ENT_QUOTES) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field-group">
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" autofocus required>
      </div>
      <input type="hidden" name="admin_login" value="1">
      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Sign In</button>
    </form>
  </div>

<?php else: ?>
  <!-- ── HEADER ── -->
  <div class="admin-header">
    <div>
      <div class="eyebrow">PlusWealth Admin</div>
      <h1>Performance Data Entry</h1>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="fusion.php" class="btn-ghost btn-sm">← View Fusion Page</a>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="admin_logout" value="1">
        <button type="submit" class="btn-ghost btn-sm">Sign Out</button>
      </form>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="alert alert-success"><?= $successMsg ?></div>
  <?php endif; ?>
  <?php if ($errorMsg): ?>
    <div class="alert alert-error"><?= $errorMsg ?></div>
  <?php endif; ?>

  <!-- ── EXISTING MONTHS ── -->
  <?php if (!empty($existingMonths)): ?>
  <div class="card" style="margin-bottom:32px;">
    <div class="card-title">Existing Records — click to edit</div>
    <table class="months-table">
      <thead>
        <tr>
          <th>Month</th>
          <th>Rows</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($existingMonths as $m): ?>
        <tr>
          <td style="font-weight:600;"><?= htmlspecialchars($m['ml'], ENT_QUOTES) ?></td>
          <td style="color:var(--ash);">Fusion + Catalyst rows</td>
          <td style="display:flex;gap:8px;">
            <a href="?edit=<?= urlencode($m['mv']) ?>" class="btn-ghost btn-sm">Edit</a>
            <form method="POST" onsubmit="return confirm('Delete all data for <?= htmlspecialchars($m['ml'], ENT_QUOTES) ?>? This cannot be undone.')">
              <input type="hidden" name="delete_month" value="<?= htmlspecialchars($m['mv'], ENT_QUOTES) ?>">
              <button type="submit" class="btn-ghost btn-sm" style="color:var(--red);border-color:rgba(248,113,113,0.3);">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- ── DATA ENTRY FORM ── -->
  <div class="card">
    <div class="card-title"><?= $editMonthVal ? 'Editing: ' . htmlspecialchars($editData['month_year'] ?? '', ENT_QUOTES) : 'Add New Month' ?></div>

    <form method="POST">
      <input type="hidden" name="save_performance" value="1">

      <!-- Month selector -->
      <div class="field-group" style="max-width:260px;margin-bottom:32px;">
        <label for="month_year">Month (YYYY-MM)</label>
        <input type="month" id="month_year" name="month_year"
               value="<?= $editMonthVal ?: date('Y-m') ?>" required>
        <div style="font-size:11px;color:var(--ash);margin-top:6px;">
          Saving will replace any existing data for this month.
        </div>
      </div>

      <!-- ── PlusWealth Fusion ── -->
      <div class="strategy-section">
        <div class="strategy-label">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--green);margin-right:8px;vertical-align:middle;"></span>
          PlusWealth Fusion
        </div>
        <div class="period-grid">
          <?php foreach ($periods as $i => $p): ?>
          <div class="field-group">
            <label><?= $periodLabels[$i] ?></label>
            <input type="text" name="fusion_<?= $p ?>"
                   value="<?= ev($editData, 'fusion', $p) ?>"
                   placeholder="e.g. 4.77 or -2.6 or -">
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ── Benchmark ── -->
      <div class="strategy-section">
        <div class="strategy-label">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--ash);opacity:0.5;margin-right:8px;vertical-align:middle;"></span>
          Benchmark: NSE Multi Asset Index 2
        </div>
        <div class="period-grid">
          <?php foreach ($periods as $i => $p): ?>
          <div class="field-group">
            <label><?= $periodLabels[$i] ?></label>
            <input type="text" name="bench_<?= $p ?>"
                   value="<?= ev($editData, 'bench', $p) ?>"
                   placeholder="e.g. 0.65 or -6.2 or -">
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="strategy-section">
        <div class="strategy-label">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--brand-soft);margin-right:8px;vertical-align:middle;"></span>
          PlusWealth Catalyst
        </div>
        <div class="period-grid">
          <?php foreach ($periods as $i => $p): ?>
          <div class="field-group">
            <label><?= $periodLabels[$i] ?></label>
            <input type="text" name="catalyst_<?= $p ?>"
                   value="<?= ev($editData, 'catalyst', $p) ?>"
                   placeholder="e.g. 4.77 or -2.6 or -">
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="strategy-section">
        <div class="strategy-label">
          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--ash);opacity:0.5;margin-right:8px;vertical-align:middle;"></span>
          Catalyst Benchmark: NSE Multi Asset Index 2
        </div>
        <div class="period-grid">
          <?php foreach ($periods as $i => $p): ?>
          <div class="field-group">
            <label><?= $periodLabels[$i] ?></label>
            <input type="text" name="catalyst_bench_<?= $p ?>"
                   value="<?= ev($editData, 'catalyst_bench', $p) ?>"
                   placeholder="e.g. 0.65 or -6.2 or -">
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:16px;padding-top:8px;">
        <button type="submit" class="btn-primary">Save Performance Data</button>
        <?php if ($editMonthVal): ?>
          <a href="admin_performance.php" class="btn-ghost">+ Add New Month</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

<?php endif; ?>
</div>
</body>
</html>
