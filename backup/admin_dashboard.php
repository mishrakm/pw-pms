<?php
/**
 * Admin Dashboard - Multi-role authentication system
 * Roles: admin (full access), portal_manager (downloads only)
 */

require_once __DIR__ . '/includes/db_config.php';

// User authentication
session_start();

// Authenticate user
function authenticateUser($username, $password) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Update last login
            $update = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $row['id']);
            $update->execute();
            $update->close();
            
            $stmt->close();
            return $row;
        }
    }
    $stmt->close();
    return false;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $user = authenticateUser($_POST['username'], $_POST['password']);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
        } else {
            $error = "Invalid username or password";
        }
    }
    
    if (!isset($_SESSION['user_id'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login - PlusWealth PMS</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f7fc; padding: 50px; }
                .login-box { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                h2 { color: #1029a6; margin-bottom: 20px; }
                input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
                button { width: 100%; padding: 12px; background: #1029a6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                button:hover { background: #244ae2; }
                .error { color: red; margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; color: #3D4270; font-weight: 500; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>PlusWealth PMS Login</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter username" required autofocus>
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE contact_submissions SET status = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle file upload
if (isset($_POST['upload_file'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (!empty($title) && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_type = $_FILES['file']['type'];
        $file_tmp = $_FILES['file']['tmp_name'];
        
        // Generate unique filename
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file_name);
        $file_path = 'uploads/' . $unique_name;
        $full_path = $upload_dir . $unique_name;
        
        if (move_uploaded_file($file_tmp, $full_path)) {
            $conn = get_db_connection();
            $stmt = $conn->prepare("INSERT INTO downloads (title, description, filename, filepath, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $title, $description, $file_name, $file_path, $file_size, $file_type);
            $stmt->execute();
            $stmt->close();
            $upload_success = "File uploaded successfully!";
        } else {
            $upload_error = "Failed to upload file.";
        }
    } else {
        $upload_error = "Please provide title and select a file.";
    }
}

// Handle file deletion
if (isset($_POST['delete_file'])) {
    $id = intval($_POST['file_id']);
    $conn = get_db_connection();
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT filepath FROM downloads WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $file_path = __DIR__ . '/' . $row['filepath'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    $stmt->close();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM downloads WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Handle file toggle active status
if (isset($_POST['toggle_file'])) {
    $id = intval($_POST['file_id']);
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE downloads SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Handle password change (own password only)
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Get current user's password
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
    
    if ($user_data && password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password && strlen($new_password) >= 8) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Failed to update password.";
            }
            $stmt->close();
        } else {
            $password_error = "New passwords don't match or are too short (minimum 8 characters).";
        }
    } else {
        $password_error = "Current password is incorrect.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_dashboard.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Fetch submissions
$conn = get_db_connection();

$sql = "SELECT * FROM contact_submissions WHERE 1=1";
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$sql .= " ORDER BY submitted_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_count,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_count,
    SUM(CASE WHEN DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) as today_count
FROM contact_submissions")->fetch_assoc();

// Fetch downloads
$downloads_result = $conn->query("SELECT * FROM downloads ORDER BY upload_date DESC");
$downloads = [];
if ($downloads_result) {
    while ($row = $downloads_result->fetch_assoc()) {
        $downloads[] = $row;
    }
}

// Get current view (default to downloads for portal_manager, contacts for admin)
$current_view = $_GET['view'] ?? ($_SESSION['user_role'] === 'admin' ? 'contacts' : 'downloads');

// Access control: prevent portal_manager from accessing restricted views
if ($_SESSION['user_role'] !== 'admin' && $current_view === 'contacts') {
    $current_view = 'downloads';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - PlusWealth PMS</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f4f7fc; }
        .header { background: linear-gradient(135deg, #1029a6 0%, #244ae2 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .logout-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border: 1px solid rgba(255,255,255,0.3); border-radius: 4px; text-decoration: none; font-size: 14px; }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #65677E; font-size: 14px; margin-bottom: 8px; font-weight: 500; }
        .stat-card .number { color: #1029a6; font-size: 32px; font-weight: bold; }
        .filters { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .filters select, .filters input { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .filters input[type="text"] { flex: 1; min-width: 200px; }
        .filters button { padding: 10px 20px; background: #1029a6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .filters button:hover { background: #244ae2; }
        .submissions { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; color: #3D4270; border-bottom: 2px solid #e9ecef; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; font-size: 14px; }
        tr:hover { background: #f8f9fa; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-new { background: #e3f2fd; color: #1565c0; }
        .status-contacted { background: #fff3e0; color: #e65100; }
        .status-converted { background: #e8f5e9; color: #2e7d32; }
        .status-closed { background: #f5f5f5; color: #616161; }
        .action-btn { padding: 6px 12px; background: #1029a6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-right: 5px; }
        .action-btn:hover { background: #244ae2; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { color: #1029a6; }
        .close { font-size: 28px; cursor: pointer; color: #999; }
        .close:hover { color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #3D4270; font-weight: 500; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group textarea { min-height: 100px; resize: vertical; font-family: inherit; }
        .form-group input[type="text"], .form-group input[type="file"], .form-group input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .detail-row { margin-bottom: 10px; }
        .detail-row strong { color: #3D4270; display: inline-block; width: 120px; }
        .no-data { text-align: center; padding: 40px; color: #65677E; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .tab { padding: 12px 24px; background: white; border-radius: 8px; text-decoration: none; color: #3D4270; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s; }
        .tab:hover { background: #f8f9fa; }
        .tab.active { background: linear-gradient(135deg, #1029a6 0%, #244ae2 100%); color: white; }
        .upload-section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .upload-section h2 { color: #1029a6; margin-bottom: 20px; font-size: 20px; }
        .alert { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .file-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 10px; }
        .file-info { flex: 1; }
        .file-actions { display: flex; gap: 5px; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-warning:hover { background: #e0a800; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 <?php echo $_SESSION['user_role'] === 'admin' ? 'Admin Dashboard' : 'Portal Manager Dashboard'; ?></h1>
        <div>
            <span style="margin-right: 20px; opacity: 0.9;">👤 <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo ucfirst($_SESSION['user_role']); ?>)</span>
            <a href="?logout=1" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Navigation Tabs -->
        <div class="tabs">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="?view=contacts" class="tab <?php echo $current_view === 'contacts' ? 'active' : ''; ?>">📧 Contact Submissions</a>
            <?php endif; ?>
            <a href="?view=downloads" class="tab <?php echo $current_view === 'downloads' ? 'active' : ''; ?>">📁 Downloads</a>
            <a href="?view=settings" class="tab <?php echo $current_view === 'settings' ? 'active' : ''; ?>">⚙️ Settings</a>
        </div>

        <?php if ($current_view === 'settings'): ?>
            <!-- Settings Section -->
            <?php if (isset($password_success)): ?>
                <div class="alert alert-success"><?php echo $password_success; ?></div>
            <?php endif; ?>
            <?php if (isset($password_error)): ?>
                <div class="alert alert-error"><?php echo $password_error; ?></div>
            <?php endif; ?>
            
            <div class="upload-section">
                <h2>Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label>New Password * (minimum 8 characters)</label>
                        <input type="password" name="new_password" required minlength="8" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" required minlength="8" placeholder="Confirm new password">
                    </div>
                    <button type="submit" name="change_password" class="action-btn" style="padding: 12px 24px; font-size: 14px;">🔒 Change Password</button>
                </form>
            </div>

        <?php elseif ($current_view === 'downloads'): ?>
            <!-- Downloads Management Section -->
            <?php if (isset($upload_success)): ?>
                <div class="alert alert-success"><?php echo $upload_success; ?></div>
            <?php endif; ?>
            <?php if (isset($upload_error)): ?>
                <div class="alert alert-error"><?php echo $upload_error; ?></div>
            <?php endif; ?>
            
            <div class="upload-section">
                <h2>Upload New File</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required placeholder="e.g., Fusion Fact Sheet Q4 2025">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Optional description of the file..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>File *</label>
                        <input type="file" name="file" required>
                    </div>
                    <button type="submit" name="upload_file" class="action-btn" style="padding: 12px 24px; font-size: 14px;">📤 Upload File</button>
                </form>
            </div>

            <div class="submissions">
                <div style="padding: 20px; border-bottom: 2px solid #e9ecef;">
                    <h2 style="color: #1029a6; font-size: 20px; margin: 0;">Uploaded Files (<?php echo count($downloads); ?>)</h2>
                </div>
                <?php if (empty($downloads)): ?>
                    <div class="no-data">No files uploaded yet</div>
                <?php else: ?>
                    <div style="padding: 20px;">
                        <?php foreach ($downloads as $download): ?>
                            <div class="file-item">
                                <div class="file-info">
                                    <strong style="color: #1029a6; font-size: 16px;"><?php echo htmlspecialchars($download['title']); ?></strong>
                                    <?php if (!empty($download['description'])): ?>
                                        <p style="margin: 5px 0; color: #65677E; font-size: 13px;"><?php echo htmlspecialchars($download['description']); ?></p>
                                    <?php endif; ?>
                                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                        <span>📅 <?php echo date('M d, Y', strtotime($download['upload_date'])); ?></span>
                                        <span style="margin-left: 15px;">📦 <?php echo number_format($download['file_size'] / 1024, 2); ?> KB</span>
                                        <span style="margin-left: 15px;">⬇️ <?php echo $download['downloads_count']; ?> downloads</span>
                                        <span style="margin-left: 15px;">📄 <?php echo htmlspecialchars($download['filename']); ?></span>
                                        <span style="margin-left: 15px; font-weight: bold; color: <?php echo $download['is_active'] ? '#2e7d32' : '#999'; ?>">
                                            <?php echo $download['is_active'] ? '✓ Active' : '✗ Inactive'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="file_id" value="<?php echo $download['id']; ?>">
                                        <button type="submit" name="toggle_file" class="action-btn btn-warning" title="Toggle Active/Inactive">
                                            <?php echo $download['is_active'] ? '👁️' : '🚫'; ?>
                                        </button>
                                    </form>
                                    <a href="download_file.php?id=<?php echo $download['id']; ?>" class="action-btn" title="Download">⬇️</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                        <input type="hidden" name="file_id" value="<?php echo $download['id']; ?>">
                                        <button type="submit" name="delete_file" class="action-btn btn-danger" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Contact Submissions Section (Admin Only) -->
            <?php if ($_SESSION['user_role'] !== 'admin'): ?>
                <div class="alert alert-error" style="margin: 20px;">
                    <strong>Access Denied:</strong> You don't have permission to view this section. Please contact an administrator.
                </div>
            <?php else: ?>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Submissions</h3>
                <div class="number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card">
                <h3>New Today</h3>
                <div class="number"><?php echo $stats['today_count']; ?></div>
            </div>
            <div class="stat-card">
                <h3>New Leads</h3>
                <div class="number"><?php echo $stats['new_count']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Contacted</h3>
                <div class="number"><?php echo $stats['contacted_count']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Converted</h3>
                <div class="number"><?php echo $stats['converted_count']; ?></div>
            </div>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="filters">
            <select name="status" onchange="this.form.submit()">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="contacted" <?php echo $status_filter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            <input type="text" name="search" placeholder="Search by name, email, phone, or city..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search) || $status_filter !== 'all'): ?>
                <a href="admin_dashboard.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">Clear Filters</a>
            <?php endif; ?>
        </form>
        
        <!-- Submissions Table -->
        <div class="submissions">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['submitted_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['city']); ?></td>
                                <td><?php echo htmlspecialchars($row['ticket_size']); ?></td>
                                <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <button class="action-btn" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">View</button>
                                    <button class="action-btn" onclick="updateStatus(<?php echo htmlspecialchars(json_encode($row)); ?>)">Update</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">No submissions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- View Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Submission Details</h2>
                <span class="close" onclick="closeModal('detailsModal')">&times;</span>
            </div>
            <div id="detailsContent"></div>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Status</h2>
                <span class="close" onclick="closeModal('updateModal')">&times;</span>
            </div>
            <form method="POST" id="updateForm">
                <input type="hidden" name="id" id="update_id">
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" id="update_status" required>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="converted">Converted</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" id="update_notes" placeholder="Add notes about this lead..."></textarea>
                </div>
                <button type="submit" name="update_status" class="action-btn" style="padding: 10px 20px; font-size: 14px;">Save Changes</button>
            </form>
        </div>
    </div>
    
    <script>
        function viewDetails(data) {
            const content = `
                <div class="detail-row"><strong>ID:</strong> ${data.id}</div>
                <div class="detail-row"><strong>Name:</strong> ${data.name}</div>
                <div class="detail-row"><strong>Email:</strong> <a href="mailto:${data.email}">${data.email}</a></div>
                <div class="detail-row"><strong>Phone:</strong> <a href="tel:${data.phone}">${data.phone}</a></div>
                <div class="detail-row"><strong>City:</strong> ${data.city}</div>
                <div class="detail-row"><strong>Ticket Size:</strong> ${data.ticket_size}</div>
                <div class="detail-row"><strong>Submitted:</strong> ${new Date(data.submitted_at).toLocaleString()}</div>
                <div class="detail-row"><strong>IP Address:</strong> ${data.ip_address || 'N/A'}</div>
                <div class="detail-row"><strong>Status:</strong> <span class="status-badge status-${data.status}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></div>
                ${data.notes ? `<div class="detail-row"><strong>Notes:</strong><br><div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">${data.notes}</div></div>` : ''}
            `;
            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').style.display = 'block';
        }
        
        function updateStatus(data) {
            document.getElementById('update_id').value = data.id;
            document.getElementById('update_status').value = data.status;
            document.getElementById('update_notes').value = data.notes || '';
            document.getElementById('updateModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
