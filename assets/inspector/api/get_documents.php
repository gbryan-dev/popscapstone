<?php
// NO SPACE BEFORE THIS LINE
header('Content-Type: application/json; charset=utf-8');

// Prevent any output buffering issues
ob_clean();

include '../../../db_conn.php';

// Log function for debugging
function logDebug($message) {
    error_log('[GET_DOCS] ' . $message);
}

try {
    // Check connection
    if (!isset($conn)) {
        throw new Exception('Database connection not established');
    }
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Check method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Invalid request method: ' . $_SERVER["REQUEST_METHOD"]);
    }
    
    // Get ref_id
    $ref_id = isset($_POST['ref_id']) ? trim($_POST['ref_id']) : '';
    
    if (empty($ref_id)) {
        throw new Exception('Missing ref_id parameter');
    }
    
    logDebug('Received ref_id: ' . $ref_id);
    
    // Get application_id
    $sql = "SELECT application_id FROM applications WHERE ref_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $ref_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        
        echo json_encode([
            'success' => false,
            'message' => 'Application not found',
            'ref_id' => $ref_id
        ]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $application_id = $row['application_id'];
    $stmt->close();
    
    logDebug('Found application_id: ' . $application_id);
    
    // Get distinct documents
    $sql = "SELECT DISTINCT field_name FROM documents WHERE application_id = ? ORDER BY field_name ASC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare docs failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $application_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute docs failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $documents = [];
    while ($doc = $result->fetch_assoc()) {
        $fieldName = trim($doc['field_name']);
        if (!empty($fieldName)) {
            $documents[] = $fieldName;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    logDebug('Found ' . count($documents) . ' documents');
    
    // Success response
    echo json_encode([
        'success' => true,
        'documents' => $documents,
        'application_id' => $application_id,
        'count' => count($documents)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    logDebug('Error: ' . $e->getMessage());
    
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => true
    ]);
}
exit;
?>