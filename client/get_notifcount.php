<?php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'count' => 0]);
    exit;
}

$client_id = $_SESSION['client_id'];

// Query to count unread notifications
$query = "
    SELECT COUNT(*) as unread_count
    FROM notifications
    WHERE client_id = ? AND is_read = 'no'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'count' => (int)$row['unread_count']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'count' => 0
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>