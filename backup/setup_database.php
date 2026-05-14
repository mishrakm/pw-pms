<?php
/**
 * Database Setup Script for PlusWealth PMS Contact Form
 * This script creates the database table for storing contact form submissions
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your server
 * 2. Run it once by accessing it in your browser: http://yourdomain.com/setup_database.php
 * 3. After successful setup, DELETE this file for security
 */

// Database configuration
$db_host = 'localhost';
$db_name = 'pluswoir_pms';
$db_user = 'pluswoir_pms';
$db_pass = 'Pm$plusW3alth';

echo "<h2>PlusWealth PMS Database Setup</h2>";
echo "<p>Starting database setup...</p>";

try {
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✓ Connected to MySQL server successfully</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Database '$db_name' is ready</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($db_name);
    
    // Create contact_submissions table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `contact_submissions` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NOT NULL,
        `city` VARCHAR(255) NOT NULL,
        `ticket_size` VARCHAR(100) NOT NULL,
        `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT DEFAULT NULL,
        `status` ENUM('new', 'contacted', 'converted', 'closed') DEFAULT 'new',
        `notes` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_email` (`email`),
        INDEX `idx_submitted_at` (`submitted_at`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "<p style='color: green;'>✓ Table 'contact_submissions' created successfully</p>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Create downloads table
    $create_downloads_table = "CREATE TABLE IF NOT EXISTS `downloads` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `filename` VARCHAR(255) NOT NULL,
        `filepath` VARCHAR(500) NOT NULL,
        `file_size` INT(11) DEFAULT NULL,
        `file_type` VARCHAR(100) DEFAULT NULL,
        `upload_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `downloads_count` INT(11) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        INDEX `idx_is_active` (`is_active`),
        INDEX `idx_upload_date` (`upload_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_downloads_table) === TRUE) {
        echo "<p style='color: green;'>✓ Table 'downloads' created successfully</p>";
    } else {
        throw new Exception("Error creating downloads table: " . $conn->error);
    }
    
    // Create admin_settings table
    $create_admin_table = "CREATE TABLE IF NOT EXISTS `admin_settings` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` TEXT NOT NULL,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_admin_table) === TRUE) {
        echo "<p style='color: green;'>✓ Table 'admin_settings' created successfully</p>";
        
        // Insert default password
        $default_password = password_hash('pluswealth2025', PASSWORD_DEFAULT);
        $insert_password = $conn->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES ('admin_password', ?) ON DUPLICATE KEY UPDATE setting_value = setting_value");
        $insert_password->bind_param("s", $default_password);
        $insert_password->execute();
        $insert_password->close();
        echo "<p style='color: green;'>✓ Default admin password set (pluswealth2025)</p>";
    } else {
        throw new Exception("Error creating admin_settings table: " . $conn->error);
    }
    
    // Create admin_users table
    $create_users_table = "CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'portal_manager') DEFAULT 'portal_manager',
        `full_name` VARCHAR(255) DEFAULT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_login` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_username` (`username`),
        INDEX `idx_role` (`role`),
        INDEX `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_users_table) === TRUE) {
        echo "<p style='color: green;'>✓ Table 'admin_users' created successfully</p>";
        
        // Insert default admin user
        $admin_password = password_hash('admin2025', PASSWORD_DEFAULT);
        $insert_admin = $conn->prepare("INSERT INTO admin_users (username, password, role, full_name, email) VALUES (?, ?, 'admin', 'Administrator', 'admin@pluswealth.net') ON DUPLICATE KEY UPDATE username = username");
        $username = 'admin';
        $insert_admin->bind_param("ss", $username, $admin_password);
        $insert_admin->execute();
        $insert_admin->close();
        echo "<p style='color: green;'>✓ Default admin user created (username: admin, password: admin2025)</p>";
        
        // Insert default portal manager user
        $manager_password = password_hash('manager2025', PASSWORD_DEFAULT);
        $insert_manager = $conn->prepare("INSERT INTO admin_users (username, password, role, full_name, email) VALUES (?, ?, 'portal_manager', 'Portal Manager', 'manager@pluswealth.net') ON DUPLICATE KEY UPDATE username = username");
        $manager_username = 'manager';
        $insert_manager->bind_param("ss", $manager_username, $manager_password);
        $insert_manager->execute();
        $insert_manager->close();
        echo "<p style='color: green;'>✓ Default portal manager user created (username: manager, password: manager2025)</p>";
    } else {
        throw new Exception("Error creating admin_users table: " . $conn->error);
    }
    
    // Verify table structure
    $verify = $conn->query("DESCRIBE contact_submissions");
    if ($verify) {
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $verify->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Setup Complete!</h3>";
    echo "<p><strong>IMPORTANT:</strong> Delete this file (setup_database.php) immediately for security reasons.</p>";
    echo "<p>Your contact form is now ready to store submissions in the database.</p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database credentials and try again.</p>";
}
?>
