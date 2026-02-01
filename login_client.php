<?php
session_start();
include 'db_conn.php';

// Production-safe error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailuser = $_POST['emailuser'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($emailuser) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing credentials']);
        exit;
    }

    $stmt = $conn->prepare("SELECT client_id, password, role_id FROM clients_acc WHERE email = ?");
    $stmt->bind_param("s", $emailuser);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_client_id, $db_password, $db_role);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['client_id'] = $db_client_id;
            $_SESSION['role_id'] = $db_role;

            // Handle "Remember Me"
            if (!empty($_POST['remember'])) {
                setcookie('remember_client_id', $db_client_id, time() + (86400 * 30), "/"); // 30 days
            } else {
                setcookie('remember_client_id', '', time() - 3600, "/"); // Delete cookie
            }

            echo json_encode(['status' => 'success']);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Email or Password is incorrect.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>