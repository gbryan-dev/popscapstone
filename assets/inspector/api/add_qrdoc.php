<?php
// Include database connection file
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner = trim($_POST['owner'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $application_type = trim($_POST['application_type'] ?? '');
    $validity_license = $_POST['validity_license'] ?? '';
    
    // Validate inputs
    if (empty($owner) || empty($license_number) || empty($application_type) || empty($validity_license)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Check for duplicate owner
    $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE owner = ?");
    $stmt->bind_param("s", $owner);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This owner name already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Check for duplicate license number
    $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE license_number = ?");
    $stmt->bind_param("s", $license_number);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This license number already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
   
    
    // Generate unique QR code value
    $qr_code_value = generateUniqueQRCode($conn, $owner, $license_number, $application_type);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO qr_docs (qr_code_value, owner, license_number, application_type, validity_license) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $qr_code_value, $owner, $license_number, $application_type, $validity_license);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'QR Code created successfully',
            'qr_code_value' => $qr_code_value
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating QR code: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();

// Function to generate unique QR code value
function generateUniqueQRCode($conn, $owner, $license_number, $application_type) {
    do {
        // Create a unique code based on timestamp, owner, license number, and random string
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $qr_code_value = strtoupper(substr($owner, 0, 3) . '-' . $license_number . '-' . $random);
        
        // Check if this code already exists
        $stmt = $conn->prepare("SELECT id FROM qr_docs WHERE qr_code_value = ?");
        $stmt->bind_param("s", $qr_code_value);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
    } while ($exists);
    
    return $qr_code_value;
}
?>