<?php
/**
 * Authentication Functions
 * Akal University Attendance Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Authenticate User
 * 
 * @param string $email User email
 * @param string $password User password
 * @return array|false User data array or false on failure
 */
function authenticateUser($email, $password) {
    $conn = getDBConnection();
    
    $sql = "SELECT u.userid, u.name, u.email, u.password, u.role, u.department_id, u.is_active,
                   d.department_name, d.department_code
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.department_id
            WHERE u.email = ? AND u.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from user data
            unset($user['password']);
            
            $stmt->close();
            closeDBConnection($conn);
            return $user;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return false;
}

/**
 * Create User Session
 * 
 * @param array $user User data
 */
function createUserSession($user) {
    $_SESSION['userid'] = $user['userid'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['department_id'] = $user['department_id'];
    $_SESSION['department_name'] = $user['department_name'] ?? null;
    $_SESSION['department_code'] = $user['department_code'] ?? null;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Check if User is Logged In
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if User has Required Role
 * 
 * @param string|array $allowedRoles Single role or array of allowed roles
 * @return bool True if user has required role, false otherwise
 */
function hasRole($allowedRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($allowedRoles)) {
        return in_array($_SESSION['role'], $allowedRoles);
    }
    
    return $_SESSION['role'] === $allowedRoles;
}

/**
 * Require Login
 * Redirects to login page if user is not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Require Role
 * Redirects to appropriate dashboard if user doesn't have required role
 * 
 * @param string|array $allowedRoles Single role or array of allowed roles
 */
function requireRole($allowedRoles) {
    requireLogin();
    
    if (!hasRole($allowedRoles)) {
        // Redirect to user's appropriate dashboard
        redirectToDashboard();
        exit();
    }
}

/**
 * Redirect to Dashboard based on User Role
 */
function redirectToDashboard() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    
    $role = $_SESSION['role'];
    
    switch ($role) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'hod':
            header("Location: ../hod/dashboard.php");
            break;
        case 'staff':
            header("Location: ../staff/dashboard.php");
            break;
        case 'student':
            header("Location: ../student/dashboard.php");
            break;
        default:
            header("Location: ../login.php");
            break;
    }
    exit();
}

/**
 * Logout User
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: login.php");
    exit();
}

/**
 * Get Current User ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['userid'] ?? null;
}

/**
 * Get Current User Role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get Current User Department ID
 * 
 * @return int|null Department ID or null if not set
 */
function getCurrentUserDepartmentId() {
    return $_SESSION['department_id'] ?? null;
}

/**
 * Get Current User Name
 * 
 * @return string|null User name or null if not logged in
 */
function getCurrentUserName() {
    return $_SESSION['name'] ?? null;
}

/**
 * Check Session Timeout
 * 
 * @return bool True if session is valid, false if timed out
 */
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $loginTime = $_SESSION['login_time'] ?? 0;
    $currentTime = time();
    
    if (($currentTime - $loginTime) > SESSION_LIFETIME) {
        logoutUser();
        return false;
    }
    
    return true;
}
?>
