<?php
// Include database connection file
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    
    // Validate input
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        exit;
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM qr_docs WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'QR Code deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'QR code not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting QR code: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>