<?php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$client_id = $_SESSION['client_id'];

// Update all unread notifications to read
$query = "
    UPDATE notifications
    SET is_read = 'yes'
    WHERE client_id = ? AND is_read = 'no'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $client_id);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    echo json_encode([
        'success' => true,
        'message' => 'Notifications marked as read',
        'updated_count' => $affected_rows
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update notifications'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>