<!-- "api/check_dataandqr.php" -->

<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON response header
header('Content-Type: application/json');

// Handle CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include database connection file
include '../../../db_conn.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. POST required.');
    }

    // Get and decode JSON request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log the received data for debugging
    error_log("Received data: " . print_r($data, true));
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['qr_code'])) {
        throw new Exception('Missing required field: qr_code');
    }

    $qrCode = trim($data['qr_code']);
    $tableName = 'qr_docs';

    // Log what we're searching for
    error_log("Searching for QR code: " . $qrCode);

    // Sanitize table name (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
        throw new Exception('Invalid table name format');
    }

    // Check if connection exists
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . (isset($conn) ? $conn->connect_error : 'Connection object not found'));
    }

    // Check if table exists first
    $tableCheck = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception("Table '$tableName' does not exist in the database");
    }

    // Query database for QR code
    $query = "SELECT * FROM `$tableName` WHERE qr_code_value = ?";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Bind the QR code parameter
    $stmt->bind_param('s', $qrCode);
    
    // Execute query
    if (!$stmt->execute()) {
        throw new Exception('Execution failed: ' . $stmt->error);
    }

    // Get result
    $result = $stmt->get_result();
    $found = $result->num_rows > 0;

    // Log the result
    error_log("QR code found: " . ($found ? 'YES' : 'NO'));

    // Fetch the record if found (for metadata if needed)
    $record = null;
    if ($found) {
        $record = $result->fetch_assoc();
        error_log("Record found: " . print_r($record, true));
    }

    // Return success response
    $response = [
        'success' => true,
        'found' => $found,
        'qr_code' => $qrCode,
        'message' => $found ? 'QR code found in database' : 'QR code not found in database'
    ];

    error_log("Response: " . json_encode($response));
    
    echo json_encode($response);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error
    error_log("Error in check_qr.php: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'found' => false,
        'error' => $e->getMessage()
    ]);
}
?>