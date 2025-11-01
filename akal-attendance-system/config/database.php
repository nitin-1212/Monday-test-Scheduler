<?php
/**
 * Database Configuration File
 * Akal University Attendance Management System
 * 
 * IMPORTANT: Update these credentials according to your MySQL setup
 * Change DB_TIMEZONE to match your server timezone (default: Asia/Kolkata)
 */

// Database Configuration Constants
define('DB_HOST', 'localhost');           // Database host (usually 'localhost')
define('DB_USERNAME', 'root');            // Database username (change this)
define('DB_PASSWORD', '');                // Database password (change this)
define('DB_NAME', 'akal_attendance_db');  // Database name
define('DB_CHARSET', 'utf8mb4');          // Character set

// Timezone Configuration
define('DB_TIMEZONE', 'Asia/Kolkata');    // Server timezone (CHANGE THIS if needed)
date_default_timezone_set(DB_TIMEZONE);

// Application Configuration
define('APP_NAME', 'Akal University Attendance System');
define('APP_URL', 'http://localhost/akal-attendance-system');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 8);     // 8 hours in seconds

// Attendance Marking Time Restrictions
define('ATTENDANCE_START_HOUR', 9);       // 09:00 AM
define('ATTENDANCE_END_HOUR', 17);        // 05:00 PM (17:00)

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get Database Connection
 * 
 * @return mysqli Database connection object
 * @throws Exception if connection fails
 */
function getDBConnection() {
    try {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        if (!$conn->set_charset(DB_CHARSET)) {
            throw new Exception("Error setting charset: " . $conn->error);
        }
        
        // Set timezone for MySQL connection
        $conn->query("SET time_zone = '+05:30'"); // IST timezone
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed. Please check your configuration.");
    }
}

/**
 * Close Database Connection
 * 
 * @param mysqli $conn Database connection object
 */
function closeDBConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

/**
 * Execute Prepared Statement
 * 
 * @param mysqli $conn Database connection
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi" for string, string, int)
 * @param array $params Parameters array
 * @return mysqli_stmt|false Prepared statement or false on failure
 */
function executePreparedStatement($conn, $sql, $types = "", $params = []) {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

/**
 * Sanitize Input
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if current time is within attendance marking hours
 * 
 * @return bool True if within allowed hours, false otherwise
 */
function isWithinAttendanceHours() {
    $currentHour = (int)date('H');
    return ($currentHour >= ATTENDANCE_START_HOUR && $currentHour < ATTENDANCE_END_HOUR);
}

/**
 * Check if date is today
 * 
 * @param string $date Date to check (Y-m-d format)
 * @return bool True if date is today, false otherwise
 */
function isToday($date) {
    return $date === date('Y-m-d');
}
?>
