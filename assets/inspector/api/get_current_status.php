<?php
session_start();
include '../../../db_conn.php';


header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ref_id = isset($_POST['ref_id']) ? htmlspecialchars($_POST['ref_id']) : '';
    
    if (empty($ref_id)) {
        echo json_encode(['success' => false, 'message' => 'Reference ID is required']);
        exit;
    }
    
    // Get current status from database
    $sql = "SELECT status, application_id FROM applications WHERE ref_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ref_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'status' => $row['status'],
            'application_id' => $row['application_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>