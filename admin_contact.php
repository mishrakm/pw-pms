<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

session_start();

$authError = '';
$errorMsg = '';
$successMsg = '';

$authed = !empty($_SESSION['admin_authenticated']) || !empty($_SESSION['pw_admin_auth']);

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  unset($_SESSION['admin_authenticated'], $_SESSION['pw_admin_auth'], $_SESSION['admin_time']);
  session_destroy();
  header('Location: admin_contact.php');
  exit;
}

if (!$authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
  $entered = (string)($_POST['admin_password'] ?? '');
  if ($entered === ADMIN_PASSWORD) {
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['pw_admin_auth'] = true;
    $_SESSION['admin_time'] = time();
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
    <title>Admin - Contact Leads</title>
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
        <h1>Contact Leads</h1>
        <p>CMS access required.</p>
        <?php if ($authError): ?><div class="err"><?= htmlspecialchars($authError, ENT_QUOTES) ?></div><?php endif; ?>
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" required autofocus>
        <button type="submit" name="admin_login" value="1">Login</button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}

$statusFilter = (string)($_GET['status'] ?? 'all');
$search = trim((string)($_GET['search'] ?? ''));
$allowedStatuses = ['new', 'contacted', 'converted', 'closed'];
if ($statusFilter !== 'all' && !in_array($statusFilter, $allowedStatuses, true)) {
  $statusFilter = 'all';
}

try {
  $conn = get_db_connection();

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? 'new');
    $notes = trim((string)($_POST['notes'] ?? ''));

    if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
      throw new InvalidArgumentException('Invalid status update request.');
    }

    $u = $conn->prepare('UPDATE contact_submissions SET status = ?, notes = ? WHERE id = ?');
    if (!$u) {
      throw new Exception('Failed to prepare status update statement.');
    }
    $u->bind_param('ssi', $status, $notes, $id);
    if (!$u->execute()) {
      $err = $u->error;
      $u->close();
      throw new Exception('Update failed: ' . $err);
    }
    $u->close();
    $successMsg = 'Lead updated successfully.';
  }

  $statsSql = "SELECT
      COUNT(*) AS total,
      SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
      SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) AS contacted_count,
      SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) AS converted_count,
      SUM(CASE WHEN DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count
    FROM contact_submissions";
  $stats = $conn->query($statsSql);
  $statsRow = $stats ? $stats->fetch_assoc() : ['total' => 0, 'new_count' => 0, 'contacted_count' => 0, 'converted_count' => 0, 'today_count' => 0];

  $sql = "SELECT id, submitted_at, name, email, phone, city, ticket_size, status, notes FROM contact_submissions WHERE 1=1";
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
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
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
  $statsRow = ['total' => 0, 'new_count' => 0, 'contacted_count' => 0, 'converted_count' => 0, 'today_count' => 0];
  $result = false;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Contact Leads</title>
  <style>
    body { font-family: "Segoe UI", Arial, sans-serif; background:#0b1220; margin:0; color:#fff; }
    .wrap { max-width:1320px; margin:0 auto; padding:24px; }
    .header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; }
    .title { margin:0; font-size:26px; }
    .logout { color:#b7c3d9; text-decoration:none; border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 10px; font-size:13px; }
    .stats { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .stat { background:#121a2a; border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:12px; }
    .stat p { margin:0; color:#b7c3d9; font-size:12px; }
    .stat h3 { margin:5px 0 0; font-size:24px; }
    .panel { background:#121a2a; border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:14px; margin-bottom:14px; }
    .filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .filters select, .filters input { background:#0d1628; border:1px solid rgba(255,255,255,0.14); color:#fff; border-radius:8px; padding:9px 10px; }
    .filters input[type="text"] { min-width:240px; }
    .filters button, .filters a { background:#0d78ff; color:#fff; text-decoration:none; border:none; border-radius:8px; padding:9px 12px; cursor:pointer; font-size:13px; }
    .filters a { background:#46526a; }
    .ok { color:#22c55e; margin-bottom:10px; }
    .err { color:#f87171; margin-bottom:10px; }
    .table-wrap { overflow-x:auto; overflow-y:hidden; -webkit-overflow-scrolling:touch; }
    table { width:100%; border-collapse:collapse; min-width:1250px; }
    th, td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.08); font-size:13px; vertical-align:top; }
    th { text-align:left; color:#b7c3d9; font-size:12px; background:#121a2a; position:sticky; top:0; z-index:2; }
    th:last-child,
    td:last-child {
      position: sticky;
      right: 0;
      background: #121a2a;
      z-index: 3;
      box-shadow: -8px 0 14px rgba(0,0,0,0.35);
    }
    .badge { display:inline-block; padding:3px 8px; border-radius:999px; font-size:11px; font-weight:700; text-transform:capitalize; }
    .new { background:rgba(59,130,246,0.2); color:#93c5fd; }
    .contacted { background:rgba(245,158,11,0.2); color:#fcd34d; }
    .converted { background:rgba(34,197,94,0.2); color:#86efac; }
    .closed { background:rgba(156,163,175,0.22); color:#d1d5db; }
    textarea, select { width:100%; background:#0d1628; border:1px solid rgba(255,255,255,0.14); color:#fff; border-radius:8px; padding:8px; font-size:12px; box-sizing:border-box; }
    textarea { min-height:58px; resize:vertical; }
    .btn-update { margin-top:6px; width:100%; background:#0d78ff; border:none; border-radius:8px; color:#fff; padding:8px; cursor:pointer; font-size:12px; }
    .muted { color:#b7c3d9; font-size:12px; }
    @media (max-width: 980px) { .stats { grid-template-columns:repeat(2,minmax(0,1fr)); } }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1 class="title">Contact Us Leads</h1>
      <a class="logout" href="admin_contact.php?logout=1">Logout</a>
    </div>

    <div class="stats">
      <div class="stat"><p>Total</p><h3><?= (int)($statsRow['total'] ?? 0) ?></h3></div>
      <div class="stat"><p>Today</p><h3><?= (int)($statsRow['today_count'] ?? 0) ?></h3></div>
      <div class="stat"><p>New</p><h3><?= (int)($statsRow['new_count'] ?? 0) ?></h3></div>
      <div class="stat"><p>Contacted</p><h3><?= (int)($statsRow['contacted_count'] ?? 0) ?></h3></div>
      <div class="stat"><p>Converted</p><h3><?= (int)($statsRow['converted_count'] ?? 0) ?></h3></div>
    </div>

    <div class="panel">
      <?php if ($successMsg): ?><div class="ok"><?= htmlspecialchars($successMsg, ENT_QUOTES) ?></div><?php endif; ?>
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
        <a href="admin_contact.php">Reset</a>
      </form>
    </div>

    <div class="panel table-wrap">
      <?php if ($result instanceof mysqli_result && $result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>City</th>
            <th>Ticket Size</th>
            <th>Status</th>
            <th style="min-width:260px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = (string)($row['status'] ?? 'new'); ?>
            <tr>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars(date('M d, Y H:i', strtotime((string)$row['submitted_at'])), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES) ?></td>
              <td><a style="color:#93c5fd;" href="mailto:<?= htmlspecialchars((string)($row['email'] ?? ''), ENT_QUOTES) ?>"><?= htmlspecialchars((string)($row['email'] ?? ''), ENT_QUOTES) ?></a></td>
              <td><?= htmlspecialchars((string)($row['phone'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['city'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($row['ticket_size'] ?? ''), ENT_QUOTES) ?></td>
              <td><span class="badge <?= htmlspecialchars($status, ENT_QUOTES) ?>"><?= htmlspecialchars($status, ENT_QUOTES) ?></span></td>
              <td>
                <form method="post">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <select name="status">
                    <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                    <option value="converted" <?= $status === 'converted' ? 'selected' : '' ?>>Converted</option>
                    <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                  </select>
                  <textarea name="notes" placeholder="Notes"><?= htmlspecialchars((string)($row['notes'] ?? ''), ENT_QUOTES) ?></textarea>
                  <button class="btn-update" type="submit" name="update_status" value="1">Save</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="muted">No contact submissions found.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
