<?php
/**
 * Database Configuration File
 * Centralized database connection settings for PlusWealth PMS
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'pluswoir_pms');
define('DB_USER', 'pluswoir_pms');
define('DB_PASS', 'Pm$plusW3alth');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return mysqli
 */
function get_db_connection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Database connection failed");
        }
        
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

/**
 * Close database connection
 */
function close_db_connection() {
    global $conn;
    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
        $conn = null;
    }
}
?>
