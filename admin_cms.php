<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

session_start();

$authError = '';
$isAuthed = !empty($_SESSION['admin_authenticated']) || !empty($_SESSION['pw_admin_auth']);

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  unset($_SESSION['admin_authenticated'], $_SESSION['pw_admin_auth'], $_SESSION['admin_time']);
  session_destroy();
  header('Location: admin_cms.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cms_login'])) {
  $entered = (string)($_POST['admin_password'] ?? '');
  if ($entered === ADMIN_PASSWORD) {
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['pw_admin_auth'] = true;
    $_SESSION['admin_time'] = time();
    $isAuthed = true;
  } else {
    $authError = 'Incorrect password.';
  }
}

$modules = [
  'downloads' => [
    'title' => 'Downloads Uploads',
    'file' => 'admin_uploads.php',
    'desc' => 'Upload, enable/disable, and delete downloadable files',
  ],
  'contacts' => [
    'title' => 'Contact Leads',
    'file' => 'admin_contact.php',
    'desc' => 'Review Contact Us submissions and update lead status',
  ],
  'performance' => [
    'title' => 'Performance Returns',
    'file' => 'admin_performance.php',
    'desc' => 'Manage monthly strategy and benchmark returns',
  ],
  'metrics' => [
    'title' => 'Key Metrics',
    'file' => 'admin_metrics.php',
    'desc' => 'Manage annualized return, drawdown, and latest date',
  ],
  'compliance' => [
    'title' => 'Compliance Data',
    'file' => 'admin_compliance.php',
    'desc' => 'Manage all compliance tables and trend rows',
  ],
];

$current = (string)($_GET['module'] ?? 'dashboard');
if ($current !== 'dashboard' && !isset($modules[$current])) {
  $current = 'dashboard';
}

$selected = $modules[$current] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlusWealth CMS</title>
  <style>
    :root {
      --bg: #080d16;
      --panel: #0f1522;
      --panel-soft: #121b2c;
      --border: rgba(255,255,255,0.12);
      --text: #ffffff;
      --muted: #c6d0df;
      --brand: #0d78ff;
      --good: #22c55e;
      --bad: #f87171;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
      background: radial-gradient(circle at 20% 0%, #13203a 0%, var(--bg) 42%);
      color: var(--text);
      min-height: 100vh;
    }
    .login-wrap {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 28px;
    }
    .login-title {
      margin: 0 0 6px;
      font-size: 28px;
      font-weight: 700;
    }
    .login-sub {
      margin: 0 0 18px;
      color: var(--muted);
      font-size: 14px;
    }
    .field {
      margin-bottom: 14px;
    }
    .field label {
      display: block;
      margin-bottom: 6px;
      color: var(--muted);
      font-size: 13px;
    }
    .field input {
      width: 100%;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #0a101b;
      color: var(--text);
      padding: 10px 12px;
      font-size: 14px;
    }
    .btn {
      width: 100%;
      border: none;
      border-radius: 8px;
      background: var(--brand);
      color: #fff;
      padding: 11px 14px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }
    .err {
      margin-bottom: 12px;
      color: var(--bad);
      font-size: 13px;
    }
    .shell {
      display: grid;
      grid-template-columns: 280px 1fr;
      min-height: 100vh;
    }
    .sidebar {
      border-right: 1px solid var(--border);
      background: rgba(9, 14, 24, 0.88);
      backdrop-filter: blur(6px);
      padding: 18px;
    }
    .brand {
      margin: 0 0 4px;
      font-size: 22px;
      font-weight: 700;
    }
    .meta {
      margin: 0 0 18px;
      color: var(--muted);
      font-size: 12px;
    }
    .nav {
      display: grid;
      gap: 10px;
    }
    .nav a {
      display: block;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px;
      text-decoration: none;
      color: var(--text);
      background: var(--panel);
      transition: transform 0.12s ease, border-color 0.12s ease;
    }
    .nav a:hover {
      transform: translateY(-1px);
      border-color: #5f7aa9;
    }
    .nav .active {
      border-color: var(--brand);
      background: #0f1930;
    }
    .nav .t {
      margin: 0 0 4px;
      font-size: 14px;
      font-weight: 600;
    }
    .nav .d {
      margin: 0;
      font-size: 12px;
      color: var(--muted);
      line-height: 1.4;
    }
    .logout {
      margin-top: 18px;
      display: inline-block;
      color: var(--muted);
      font-size: 12px;
      text-decoration: none;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 8px 10px;
    }
    .main {
      padding: 16px;
    }
    .frame-wrap {
      height: calc(100vh - 32px);
      border: 1px solid var(--border);
      border-radius: 12px;
      overflow: hidden;
      background: var(--panel-soft);
    }
    .frame-wrap iframe {
      width: 100%;
      height: 100%;
      border: none;
      background: #080d16;
    }
    .dashboard {
      display: grid;
      gap: 14px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      padding: 6px;
    }
    .dash-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: var(--panel);
      padding: 16px;
      text-decoration: none;
      color: var(--text);
    }
    .dash-card h3 {
      margin: 0 0 6px;
      font-size: 16px;
    }
    .dash-card p {
      margin: 0;
      color: var(--muted);
      font-size: 13px;
      line-height: 1.45;
    }
    .badge {
      display: inline-block;
      margin-bottom: 10px;
      padding: 3px 8px;
      border-radius: 999px;
      background: rgba(34,197,94,0.15);
      color: var(--good);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }
    @media (max-width: 1000px) {
      .shell { grid-template-columns: 1fr; }
      .sidebar { border-right: none; border-bottom: 1px solid var(--border); }
      .dashboard { grid-template-columns: 1fr; }
      .frame-wrap { height: 72vh; }
    }
  </style>
</head>
<body>
<?php if (!$isAuthed): ?>
  <div class="login-wrap">
    <form class="login-card" method="post">
      <h1 class="login-title">PlusWealth CMS</h1>
      <p class="login-sub">Single admin access for performance, metrics, and compliance.</p>
      <?php if ($authError): ?><div class="err"><?= htmlspecialchars($authError, ENT_QUOTES) ?></div><?php endif; ?>
      <div class="field">
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" required autofocus>
      </div>
      <button class="btn" type="submit" name="cms_login" value="1">Enter CMS</button>
    </form>
  </div>
<?php else: ?>
  <div class="shell">
    <aside class="sidebar">
      <h2 class="brand">PlusWealth CMS</h2>
      <p class="meta">Unified Admin Workspace</p>

      <nav class="nav">
        <a href="admin_cms.php" class="<?= $current === 'dashboard' ? 'active' : '' ?>">
          <p class="t">Dashboard</p>
          <p class="d">All admin modules in one place</p>
        </a>
        <?php foreach ($modules as $slug => $module): ?>
          <a href="admin_cms.php?module=<?= urlencode($slug) ?>" class="<?= $current === $slug ? 'active' : '' ?>">
            <p class="t"><?= htmlspecialchars($module['title'], ENT_QUOTES) ?></p>
            <p class="d"><?= htmlspecialchars($module['desc'], ENT_QUOTES) ?></p>
          </a>
        <?php endforeach; ?>
      </nav>

      <a class="logout" href="admin_cms.php?logout=1">Logout</a>
    </aside>

    <main class="main">
      <?php if ($current === 'dashboard'): ?>
        <div class="dashboard">
          <?php foreach ($modules as $slug => $module): ?>
            <a class="dash-card" href="admin_cms.php?module=<?= urlencode($slug) ?>">
              <span class="badge">Module</span>
              <h3><?= htmlspecialchars($module['title'], ENT_QUOTES) ?></h3>
              <p><?= htmlspecialchars($module['desc'], ENT_QUOTES) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="frame-wrap">
          <iframe src="<?= htmlspecialchars($selected['file'], ENT_QUOTES) ?>" title="<?= htmlspecialchars($selected['title'], ENT_QUOTES) ?>"></iframe>
        </div>
      <?php endif; ?>
    </main>
  </div>
<?php endif; ?>
</body>
</html>
