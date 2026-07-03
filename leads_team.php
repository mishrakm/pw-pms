<?php
require_once __DIR__ . '/includes/db_config.php';
$appConfig = require __DIR__ . '/includes/app_config.php';

session_start();

$portalPassword = (string)($appConfig['leads_portal']['password'] ?? 'LeadsTeam2026!');
$authError = '';
$errorMsg = '';

$authed = !empty($_SESSION['leads_team_authenticated']);

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  unset($_SESSION['leads_team_authenticated'], $_SESSION['leads_team_auth_time']);
  session_destroy();
  header('Location: leads_team.php');
  exit;
}

if (!$authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_login'])) {
  $entered = (string)($_POST['team_password'] ?? '');
  if ($entered === $portalPassword) {
    $_SESSION['leads_team_authenticated'] = true;
    $_SESSION['leads_team_auth_time'] = time();
    $authed = true;
  } else {
    $authError = 'Incorrect password.';
  }
}

if (!$authed) {
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Team Access</title>
    <style>
      body { font-family: "Segoe UI", Arial, sans-serif; background:#0a0f1a; margin:0; color:#fff; }
      .wrap { min-height:100vh; display:grid; place-items:center; padding:20px; }
      .card { width:100%; max-width:420px; background:#121a2a; border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:28px; }
      h1 { margin:0 0 8px; font-size:28px; }
      p { margin:0 0 18px; color:#b7c3d9; font-size:14px; }
      label { display:block; margin:0 0 6px; color:#b7c3d9; font-size:13px; }
      input[type="password"] { width:100%; padding:10px 12px; border:1px solid rgba(255,255,255,0.15); border-radius:8px; background:#0b1220; color:#fff; margin-bottom:12px; box-sizing:border-box; }
      button { width:100%; border:none; background:#0d78ff; color:#fff; padding:11px 14px; border-radius:8px; cursor:pointer; font-weight:600; }
      .err { margin:0 0 10px; color:#f87171; font-size:13px; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <form class="card" method="post">
        <h1>Leads Team</h1>
        <p>View and export leads without CMS access.</p>
        <?php if ($authError): ?><div class="err"><?= htmlspecialchars($authError, ENT_QUOTES) ?></div><?php endif; ?>
        <label for="team_password">Access Password</label>
        <input type="password" id="team_password" name="team_password" required autofocus>
        <button type="submit" name="team_login" value="1">Login</button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}

$search = trim((string)($_GET['search'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? 'all');
$allowedStatuses = ['new', 'contacted', 'converted', 'closed'];
if ($statusFilter !== 'all' && !in_array($statusFilter, $allowedStatuses, true)) {
  $statusFilter = 'all';
}

try {
  $conn = get_db_connection();

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    $rawIds = explode(',', (string)($_POST['export_ids'] ?? ''));
    $ids = array_values(array_filter(array_map('intval', $rawIds)));

    if (!empty($ids)) {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $types = str_repeat('i', count($ids));
      $expStmt = $conn->prepare(
        "SELECT id, submitted_at, name, email, phone, city, ticket_size, status, notes
         FROM contact_submissions
         WHERE id IN ($placeholders)
         ORDER BY submitted_at DESC"
      );
      if (!$expStmt) {
        throw new Exception('Failed to prepare export statement.');
      }

      $expStmt->bind_param($types, ...$ids);
      $expStmt->execute();
      $expResult = $expStmt->get_result();

      header('Content-Type: text/csv; charset=UTF-8');
      header('Content-Disposition: attachment; filename="leads_team_' . date('Y-m-d') . '.csv"');
      header('Cache-Control: no-cache, no-store, must-revalidate');

      $out = fopen('php://output', 'w');
      fwrite($out, "\xEF\xBB\xBF");
      fputcsv($out, ['ID', 'Date', 'Name', 'Email', 'Phone', 'City', 'Ticket Size', 'Status', 'Notes']);

      while ($r = $expResult->fetch_assoc()) {
        fputcsv($out, [
          $r['id'],
          $r['submitted_at'],
          $r['name'],
          $r['email'],
          $r['phone'],
          $r['city'] ?? '',
          $r['ticket_size'] ?? '',
          $r['status'] ?? 'new',
          $r['notes'] ?? '',
        ]);
      }

      fclose($out);
      exit;
    }
  }

  $sql = "SELECT id, submitted_at, name, email, phone, city, ticket_size, status, notes
          FROM contact_submissions
          WHERE 1=1";
  $params = [];
  $types = '';

  if ($statusFilter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= 's';
  }

  if ($search !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
  }

  $sql .= " ORDER BY submitted_at DESC";

  if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      throw new Exception('Failed to prepare leads query.');
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {
    $result = $conn->query($sql);
  }
} catch (Throwable $e) {
  $errorMsg = $e->getMessage();
  $result = false;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leads Team - Contact Leads</title>
  <style>
    body { font-family: "Segoe UI", Arial, sans-serif; background:#0b1220; margin:0; color:#fff; }
    .wrap { max-width:1320px; margin:0 auto; padding:24px; }
    .header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; }
    .title { margin:0; font-size:26px; }
    .logout { color:#b7c3d9; text-decoration:none; border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 10px; font-size:13px; }
    .panel { background:#121a2a; border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:14px; margin-bottom:14px; }
    .filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .filters select, .filters input { background:#0d1628; border:1px solid rgba(255,255,255,0.14); color:#fff; border-radius:8px; padding:9px 10px; }
    .filters input[type="text"] { min-width:240px; }
    .filters button, .filters a { background:#0d78ff; color:#fff; text-decoration:none; border:none; border-radius:8px; padding:9px 12px; cursor:pointer; font-size:13px; }
    .filters a { background:#46526a; }
    .err { color:#f87171; margin-bottom:10px; }
    .table-wrap { overflow-x:auto; overflow-y:hidden; -webkit-overflow-scrolling:touch; }
    table { width:100%; border-collapse:collapse; min-width:1100px; }
    th, td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.08); font-size:13px; vertical-align:top; }
    th { text-align:left; color:#b7c3d9; font-size:12px; background:#121a2a; position:sticky; top:0; z-index:2; }
    .badge { display:inline-block; padding:3px 8px; border-radius:999px; font-size:11px; font-weight:700; text-transform:capitalize; }
    .new { background:rgba(59,130,246,0.2); color:#93c5fd; }
    .contacted { background:rgba(245,158,11,0.2); color:#fcd34d; }
    .converted { background:rgba(34,197,94,0.2); color:#86efac; }
    .closed { background:rgba(156,163,175,0.22); color:#d1d5db; }
    .cb-col { width:36px; text-align:center; }
    input[type="checkbox"] { width:15px; height:15px; cursor:pointer; accent-color:#0d78ff; }
    .toolbar { display:flex; gap:10px; align-items:center; margin-bottom:10px; flex-wrap:wrap; }
    .btn-dl { background:#16a34a; color:#fff; border:none; border-radius:8px; padding:9px 14px; cursor:pointer; font-size:13px; font-weight:600; }
    .btn-dl:disabled { opacity:0.4; cursor:not-allowed; }
    .sel-count { color:#b7c3d9; font-size:13px; }
    tr.selected-row { background: rgba(13,120,255,0.08); }
    .muted { color:#b7c3d9; font-size:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1 class="title">Leads Team Dashboard</h1>
      <a class="logout" href="leads_team.php?logout=1">Logout</a>
    </div>

    <div class="panel">
      <?php if ($errorMsg): ?><div class="err"><?= htmlspecialchars($errorMsg, ENT_QUOTES) ?></div><?php endif; ?>
      <form class="filters" method="get">
        <select name="status">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
          <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
          <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
          <option value="converted" <?= $statusFilter === 'converted' ? 'selected' : '' ?>>Converted</option>
          <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" placeholder="Search name, email, phone, city">
        <button type="submit">Apply</button>
        <a href="leads_team.php">Reset</a>
      </form>
    </div>

    <div class="panel table-wrap">
      <?php if ($result instanceof mysqli_result && $result->num_rows > 0): ?>
      <form id="export-form" method="post">
        <input type="hidden" name="export_csv" value="1">
        <input type="hidden" name="export_ids" id="export-ids-input">
      </form>

      <div class="toolbar">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
          <input type="checkbox" id="cb-all"> Select all
        </label>
        <span class="sel-count" id="sel-count">0 selected</span>
        <button class="btn-dl" id="btn-download" type="button" disabled onclick="submitExport()">Download Excel</button>
      </div>

      <table>
        <thead>
          <tr>
            <th class="cb-col"></th>
            <th>ID</th>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>City</th>
            <th>Ticket Size</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = (string)($row['status'] ?? 'new'); ?>
            <tr>
              <td class="cb-col"><input type="checkbox" class="lead-cb" value="<?= (int)$row['id'] ?>"></td>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars(date('M d, Y H:i', strtotime((string)$row['submitted_at'])), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES) ?></td>
              <td><a style="color:#93c5fd;" href="mailto:<?= htmlspecialchars((string)($row['email'] ?? ''), ENT_QUOTES) ?>"><?= htmlspecialchars((string)($row['email'] ?? ''), ENT_QUOTES) ?></a></td>
              <td><?= htmlspecialchars((string)($row['phone'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['city'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['ticket_size'] ?? ''), ENT_QUOTES) ?></td>
              <td><span class="badge <?= htmlspecialchars($status, ENT_QUOTES) ?>"><?= htmlspecialchars($status, ENT_QUOTES) ?></span></td>
              <td><?= nl2br(htmlspecialchars((string)($row['notes'] ?? ''), ENT_QUOTES)) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="muted">No contact submissions found.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const cbAll = document.getElementById('cb-all');
    const btnDl = document.getElementById('btn-download');
    const selCount = document.getElementById('sel-count');

    function updateToolbar() {
      const cbs = document.querySelectorAll('.lead-cb');
      const checked = document.querySelectorAll('.lead-cb:checked');
      if (cbAll) {
        cbAll.indeterminate = checked.length > 0 && checked.length < cbs.length;
        cbAll.checked = cbs.length > 0 && checked.length === cbs.length;
      }
      if (btnDl) btnDl.disabled = checked.length === 0;
      if (selCount) selCount.textContent = checked.length + ' selected';
      document.querySelectorAll('tr').forEach(tr => {
        const cb = tr.querySelector('.lead-cb');
        if (cb) tr.classList.toggle('selected-row', cb.checked);
      });
    }

    if (cbAll) {
      cbAll.addEventListener('change', () => {
        document.querySelectorAll('.lead-cb').forEach(cb => {
          cb.checked = cbAll.checked;
        });
        updateToolbar();
      });
    }

    document.addEventListener('change', e => {
      if (e.target.classList.contains('lead-cb')) {
        updateToolbar();
      }
    });

    function submitExport() {
      const ids = Array.from(document.querySelectorAll('.lead-cb:checked')).map(cb => cb.value).join(',');
      document.getElementById('export-ids-input').value = ids;
      document.getElementById('export-form').submit();
    }
  </script>
</body>
</html>
