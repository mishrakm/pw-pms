<?php
/**
 * File Download Handler
 * Tracks downloads and serves files securely
 */
require_once __DIR__ . '/includes/db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid download request');
}

$id = intval($_GET['id']);

try {
    $conn = get_db_connection();
    
    // Get file details
    $stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        die('File not found');
    }
    
    $file = $result->fetch_assoc();
    $stmt->close();
    
    // Check if file exists
    //$filepath = __DIR__ . '/' . $file['filepath'];
    $filepath =  '/uploads/' . $file['filepath'];
    if (!file_exists($filepath)) {
        http_response_code(404);
        die('File not found on server');
    }
    
    // Increment download counter
    $update = $conn->prepare("UPDATE downloads SET downloads_count = downloads_count + 1 WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
    $update->close();
    
    // Serve the file
    header('Content-Type: ' . ($file['file_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    readfile($filepath);
    exit;
    
} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    http_response_code(500);
    die('Error processing download');
}
?>
