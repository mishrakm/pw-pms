<?php
define('ADMIN_PASSWORD', 'PwAdmin2026!');

require_once __DIR__ . '/includes/db_config.php';

session_start();

$authError = '';
$successMsg = '';
$errorMsg = '';

$authed = !empty($_SESSION['admin_authenticated']) || !empty($_SESSION['pw_admin_auth']);

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
  unset($_SESSION['admin_authenticated'], $_SESSION['pw_admin_auth'], $_SESSION['admin_time']);
  session_destroy();
  header('Location: admin_uploads.php');
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
    <title>Admin - Downloads</title>
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
        <h1>Downloads</h1>
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

$downloads = [];

try {
  $conn = get_db_connection();

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));

    if ($title === '' || !isset($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      throw new InvalidArgumentException('Please provide title and select a file.');
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Failed to create upload directory.');
      }
    }

    $originalName = (string)$_FILES['file']['name'];
    $fileSize = (int)$_FILES['file']['size'];
    $fileType = (string)($_FILES['file']['type'] ?? 'application/octet-stream');
    $tmpPath = (string)$_FILES['file']['tmp_name'];

    $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
    $uniqueName = time() . '_' . $safeName;
    $relativePath = 'uploads/' . $uniqueName;
    $fullPath = $uploadDir . $uniqueName;

    if (!move_uploaded_file($tmpPath, $fullPath)) {
      throw new RuntimeException('Failed to upload file.');
    }

    $stmt = $conn->prepare('INSERT INTO downloads (title, description, filename, filepath, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
      throw new Exception('Failed to prepare insert statement.');
    }
    $stmt->bind_param('ssssis', $title, $description, $originalName, $relativePath, $fileSize, $fileType);
    if (!$stmt->execute()) {
      $err = $stmt->error;
      $stmt->close();
      throw new Exception('DB insert failed: ' . $err);
    }
    $stmt->close();

    $successMsg = 'File uploaded successfully.';
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $id = (int)($_POST['file_id'] ?? 0);
    if ($id <= 0) {
      throw new InvalidArgumentException('Invalid file ID for delete.');
    }

    $s = $conn->prepare('SELECT filepath FROM downloads WHERE id = ?');
    $s->bind_param('i', $id);
    $s->execute();
    $res = $s->get_result();
    if ($row = $res->fetch_assoc()) {
      $pathFromDb = (string)$row['filepath'];
      $candidateA = __DIR__ . '/' . $pathFromDb;
      $candidateB = __DIR__ . '/../' . $pathFromDb;
      if (file_exists($candidateA)) {
        unlink($candidateA);
      } elseif (file_exists($candidateB)) {
        unlink($candidateB);
      }
    }
    $s->close();

    $d = $conn->prepare('DELETE FROM downloads WHERE id = ?');
    $d->bind_param('i', $id);
    $d->execute();
    $d->close();

    $successMsg = 'File deleted successfully.';
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_file'])) {
    $id = (int)($_POST['file_id'] ?? 0);
    if ($id <= 0) {
      throw new InvalidArgumentException('Invalid file ID for toggle.');
    }

    $t = $conn->prepare('UPDATE downloads SET is_active = NOT is_active WHERE id = ?');
    $t->bind_param('i', $id);
    $t->execute();
    $t->close();

    $successMsg = 'File status updated.';
  }

  $r = $conn->query('SELECT * FROM downloads ORDER BY upload_date DESC');
  if ($r instanceof mysqli_result) {
    while ($row = $r->fetch_assoc()) {
      $downloads[] = $row;
    }
  }
} catch (Throwable $e) {
  $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Downloads</title>
  <style>
    body { font-family: "Segoe UI", Arial, sans-serif; background:#0b1220; margin:0; color:#fff; }
    .wrap { max-width:1200px; margin:0 auto; padding:24px; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .title { margin:0; font-size:26px; }
    .logout { color:#b7c3d9; text-decoration:none; border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:8px 10px; font-size:13px; }
    .panel { background:#121a2a; border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:14px; margin-bottom:14px; }
    .grid { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:10px; }
    .field label { display:block; color:#b7c3d9; font-size:12px; margin-bottom:6px; }
    .field input, .field textarea { width:100%; background:#0d1628; border:1px solid rgba(255,255,255,0.14); color:#fff; border-radius:8px; padding:9px; box-sizing:border-box; }
    .field textarea { min-height:90px; resize:vertical; }
    .btn { background:#0d78ff; color:#fff; border:none; border-radius:8px; padding:9px 12px; cursor:pointer; font-size:13px; }
    .ok { color:#22c55e; margin-bottom:10px; }
    .err { color:#f87171; margin-bottom:10px; }
    .files { display:grid; gap:10px; }
    .row { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center; background:#0f182a; border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:12px; }
    .name { margin:0; font-weight:600; }
    .meta { margin:4px 0 0; color:#b7c3d9; font-size:12px; }
    .actions { display:flex; gap:6px; align-items:center; }
    .warn { background:#d97706; }
    .danger { background:#dc2626; }
    .muted { color:#b7c3d9; font-size:12px; }
    @media (max-width:900px) { .grid { grid-template-columns:1fr; } .row { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1 class="title">Downloads Upload Module</h1>
      <a class="logout" href="admin_uploads.php?logout=1">Logout</a>
    </div>

    <div class="panel">
      <?php if ($successMsg): ?><div class="ok"><?= htmlspecialchars($successMsg, ENT_QUOTES) ?></div><?php endif; ?>
      <?php if ($errorMsg): ?><div class="err"><?= htmlspecialchars($errorMsg, ENT_QUOTES) ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <div class="grid">
          <div class="field">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
          </div>
          <div class="field">
            <label for="file">File</label>
            <input type="file" id="file" name="file" required>
          </div>
          <div class="field" style="display:flex;align-items:end;">
            <button class="btn" type="submit" name="upload_file" value="1" style="width:100%;">Upload File</button>
          </div>
        </div>
        <div class="field" style="margin-top:10px;">
          <label for="description">Description (optional)</label>
          <textarea id="description" name="description"></textarea>
        </div>
      </form>
    </div>

    <div class="panel">
      <h2 style="margin:0 0 8px 0;font-size:18px;">Uploaded Files (<?= count($downloads) ?>)</h2>
      <?php if (empty($downloads)): ?>
        <p class="muted">No files uploaded yet.</p>
      <?php else: ?>
        <div class="files">
          <?php foreach ($downloads as $download): ?>
            <div class="row">
              <div>
                <p class="name"><?= htmlspecialchars((string)$download['title'], ENT_QUOTES) ?></p>
                <?php if (!empty($download['description'])): ?>
                  <p class="meta"><?= htmlspecialchars((string)$download['description'], ENT_QUOTES) ?></p>
                <?php endif; ?>
                <p class="meta">
                  <?= htmlspecialchars(date('M d, Y', strtotime((string)$download['upload_date'])), ENT_QUOTES) ?>
                  · <?= htmlspecialchars(number_format(((int)$download['file_size']) / 1024, 2), ENT_QUOTES) ?> KB
                  · <?= (int)$download['downloads_count'] ?> downloads
                  · <?= !empty($download['is_active']) ? 'Active' : 'Inactive' ?>
                </p>
              </div>
              <div class="actions">
                <form method="post" style="display:inline;">
                  <input type="hidden" name="file_id" value="<?= (int)$download['id'] ?>">
                  <button class="btn warn" type="submit" name="toggle_file" value="1"><?= !empty($download['is_active']) ? 'Disable' : 'Enable' ?></button>
                </form>
                <a class="btn" style="text-decoration:none;" href="download_file.php?id=<?= (int)$download['id'] ?>">Download</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this file?');">
                  <input type="hidden" name="file_id" value="<?= (int)$download['id'] ?>">
                  <button class="btn danger" type="submit" name="delete_file" value="1">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
