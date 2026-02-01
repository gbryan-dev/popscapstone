<?php
session_start();
include '../../../db_conn.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_director'])) {
    echo "unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "invalid_request";
    exit();
}

$admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'] ?? '';

// Validate inputs
if (empty($admin_id) || empty($username) || empty($email)) {
    echo "missing_fields";
    exit();
}

// Check if username already exists for another admin
$checkSql = "SELECT id FROM officials_acc WHERE username = '$username' AND id != '$admin_id' AND role_id = 2";
$checkResult = mysqli_query($conn, $checkSql);
if (mysqli_num_rows($checkResult) > 0) {
    echo "username_exists";
    exit();
}

// Check if email already exists for another admin
$checkEmailSql = "SELECT id FROM officials_acc WHERE email = '$email' AND id != '$admin_id' AND role_id = 2";
$checkEmailResult = mysqli_query($conn, $checkEmailSql);
if (mysqli_num_rows($checkEmailResult) > 0) {
    echo "email_exists";
    exit();
}

// Start building the update query
if (!empty($password)) {
    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $updateSql = "UPDATE officials_acc SET 
                  username = '$username',
                  email = '$email',
                  password = '$hashed_password'
                  WHERE id = '$admin_id' AND role_id = 2";
} else {
    // Update without changing password
    $updateSql = "UPDATE officials_acc SET 
                  username = '$username',
                  email = '$email'
                  WHERE id = '$admin_id' AND role_id = 2";
}

// Execute the query
if (mysqli_query($conn, $updateSql)) {
    // Update session if current user is editing their own account
    
    echo "success";
} else {
    echo "database_error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>