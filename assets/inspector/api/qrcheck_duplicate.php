<?php
// Include database connection file
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    $exclude_id = $_POST['exclude_id'] ?? '';
    
    // Validate inputs
    if (empty($field) || empty($value)) {
        echo json_encode(['success' => false, 'exists' => false]);
        exit;
    }
    
    // Allowed fields to check
    $allowed_fields = ['owner', 'license_number'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'exists' => false]);
        exit;
    }
    
    // Check for duplicate
    if (!empty($exclude_id)) {
        // For edit mode - exclude current record
        $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE $field = ? AND id != ?");
        $stmt->bind_param("si", $value, $exclude_id);
    } else {
        // For add mode - check all records
        $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE $field = ?");
        $stmt->bind_param("s", $value);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    
    echo json_encode([
        'success' => true,
        'exists' => $exists
    ]);
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'exists' => false]);
}

$conn->close();
?>