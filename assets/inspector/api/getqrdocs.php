<?php
// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

include '../../../db_conn.php';
header('Content-Type: application/json');

// Check if connection exists
if (!isset($conn)) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'data' => []
    ]);
    exit;
}

// Fetch all QR codes from database
$sql = "SELECT * FROM qr_docs ORDER BY id DESC";
$result = $conn->query($sql);

if ($result) {
    $qr_codes = [];
    
    while ($row = $result->fetch_assoc()) {
        $qr_codes[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $qr_codes
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching QR codes: ' . $conn->error,
        'data' => []
    ]);
}

$conn->close();
?>