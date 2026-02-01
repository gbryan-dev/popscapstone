<?php
// Include database connection file
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $owner = trim($_POST['owner'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $application_type = trim($_POST['application_type'] ?? '');
    $validity_license = $_POST['validity_license'] ?? '';
    
    // Validate inputs
    if (empty($id) || empty($owner) || empty($license_number) || empty($application_type) || empty($validity_license)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Check for duplicate owner (excluding current record)
    $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE owner = ? AND id != ?");
    $stmt->bind_param("si", $owner, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This owner name already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Check for duplicate license number (excluding current record)
    $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE license_number = ? AND id != ?");
    $stmt->bind_param("si", $license_number, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This license number already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
  
    // Generate new QR code value based on updated information
    $qr_code_value = generateUniqueQRCode($conn, $owner, $license_number, $application_type, $id);
    
    // Update in database
    $stmt = $conn->prepare("UPDATE qr_docs SET qr_code_value = ?, owner = ?, license_number = ?, application_type = ?, validity_license = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $qr_code_value, $owner, $license_number, $application_type, $validity_license, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'QR Code updated successfully',
                'qr_code_value' => $qr_code_value
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or QR code not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating QR code: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();

// Function to generate unique QR code value
function generateUniqueQRCode($conn, $owner, $license_number, $application_type, $exclude_id = null) {
    do {
        // Create a unique code based on timestamp, owner, license number, and random string
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $qr_code_value = strtoupper(substr($owner, 0, 3) . '-' . $license_number . '-' . $random);
        
        // Check if this code already exists (excluding current record if updating)
        if ($exclude_id) {
            $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE qr_code_value = ? AND id != ?");
            $stmt->bind_param("si", $qr_code_value, $exclude_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE qr_code_value = ?");
            $stmt->bind_param("s", $qr_code_value);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
    } while ($exists);
    
    return $qr_code_value;
}
?>