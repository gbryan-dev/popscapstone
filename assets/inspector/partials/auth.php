<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    @session_start(); 
}

// Check if admin is logged in
if (!isset($_SESSION['logged_admin'])) {
    // Use JavaScript redirect if headers already sent
    if (headers_sent()) {
        echo '<script>window.location.href="login";</script>';
        exit();
    } else {
        header("Location: login");
        exit();
    }
}

// Include database connection
include '../../db_conn.php';

// Optional: Store admin info in variables for easy access
$admin_id = $_SESSION['logged_admin']['id'];
$admin_username = $_SESSION['logged_admin']['username'];
$admin_email = $_SESSION['logged_admin']['email'];
$admin_role_id = $_SESSION['logged_admin']['role_id'];

// Verify admin still exists with same username
$stmt = $conn->prepare("SELECT username FROM officials_acc WHERE id = ? AND role_id = 2");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();
$stmt->close();

// If admin not found or username changed, logout
if (!$current_admin || $current_admin['username'] !== $admin_username) {
    // Clear session
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    
    // Redirect to login
    if (headers_sent()) {
        echo '<script>window.location.href="login";</script>';
        exit();
    } else {
        header("Location: login");
        exit();
    }
}

?>