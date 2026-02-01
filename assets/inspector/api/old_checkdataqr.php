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

// Normalization function to handle special characters and spacing
// Normalization function to handle special characters and spacing
function normalizeText($text) {
    if (!$text) return '';
    
    // Replace multiple spaces with single space
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Normalize all dash types to regular hyphen - ENHANCED VERSION
    $text = str_replace(['–', '—', '−', '‐', '‑', '−', '‒', '―'], '-', $text);
    
    // Remove ALL non-breaking spaces and replace with regular space
    $text = str_replace(["\xC2\xA0", "\u{00A0}", chr(160)], ' ', $text);
    
    // Standardize comma spacing
    $text = preg_replace('/,\s*/', ', ', $text);
    
    // Trim and lowercase
    return strtolower(trim($text));
}
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
    
    // Get data from request (already normalized from frontend)
    $qrCode = isset($data['qr_code']) ? trim($data['qr_code']) : '';
    $owner = isset($data['owner']) ? $data['owner'] : '';
    $licenseNumber = isset($data['license_number']) ? $data['license_number'] : '';
    $applicationType = isset($data['application_type']) ? $data['application_type'] : '';
    $validityLicense = isset($data['validity_license']) ? $data['validity_license'] : '';
    
    // Check if we have at least QR code or one other field
    if (empty($qrCode) && empty($owner) && empty($licenseNumber) && empty($applicationType)) {
        throw new Exception('At least one field is required (qr_code, owner, license_number, or application_type)');
    }
    
    $tableName = 'qr_docs';
    
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
    
    // First, try to find record by QR code only (most unique identifier)
    // Then verify other fields for match percentage
    $searchByQR = !empty($qrCode);
    
    if ($searchByQR) {
        // Search by QR code first
        $query = "SELECT * FROM `$tableName` WHERE TRIM(qr_code_value) = ? LIMIT 1";
        
        error_log("Searching by QR: " . $qrCode);
        
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $qrCodeTrimmed = trim($qrCode);
        $stmt->bind_param('s', $qrCodeTrimmed);
    } else {
        // Build dynamic query based on available data
        $conditions = [];
        $params = [];
        $types = '';
        
        if (!empty($owner)) {
            $conditions[] = "LOWER(TRIM(owner)) = ?";
            $params[] = $owner;
            $types .= 's';
        }
        
        if (!empty($licenseNumber)) {
            $conditions[] = "LOWER(TRIM(license_number)) = ?";
            $params[] = $licenseNumber;
            $types .= 's';
        }
        
        if (!empty($applicationType)) {
            $conditions[] = "LOWER(TRIM(application_type)) = ?";
            $params[] = $applicationType;
            $types .= 's';
        }
        
        if (!empty($validityLicense)) {
            $conditions[] = "LOWER(TRIM(validity_license)) = ?";
            $params[] = $validityLicense;
            $types .= 's';
        }
        
        if (empty($conditions)) {
            throw new Exception('No search criteria provided');
        }
        
        // Query database
        $query = "SELECT * FROM `$tableName` WHERE " . implode(" OR ", $conditions) . " LIMIT 1";
        
        error_log("Query: " . $query);
        error_log("Params: " . print_r($params, true));
        
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        // Bind parameters dynamically
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    }
    
    // Execute query
    if (!$stmt->execute()) {
        throw new Exception('Execution failed: ' . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    $found = $result->num_rows > 0;
    
    // Prepare detailed response
    $response = [
        'success' => true,
        'found' => $found,
        'searched_fields' => []
    ];
    
    if ($found) {
        $record = $result->fetch_assoc();
        
        // Calculate match details with normalization
        $matchCount = 0;
        $totalFields = 0;
        $fieldMatches = [];
        
        if (!empty($qrCode)) {
            $totalFields++;
            $matches = (strcasecmp(trim($record['qr_code_value']), trim($qrCode)) === 0);
            $matchCount += $matches ? 1 : 0;
            $fieldMatches['qr_code'] = [
                'searched' => $qrCode,
                'found' => $record['qr_code_value'],
                'matches' => $matches
            ];
        }
        
        if (!empty($owner)) {
            $totalFields++;
            // Normalize both values before comparison
            $dbOwner = normalizeText($record['owner']);
            $searchOwner = $owner; // Already normalized from frontend
            $matches = ($dbOwner === $searchOwner);
            $matchCount += $matches ? 1 : 0;
            $fieldMatches['owner'] = [
                'searched' => $owner,
                'found' => $record['owner'],
                'matches' => $matches
            ];
            error_log("Owner comparison - DB: '$dbOwner' vs Search: '$searchOwner' = " . ($matches ? 'MATCH' : 'NO MATCH'));
        }
        
        if (!empty($licenseNumber)) {
            $totalFields++;
            // Normalize both values before comparison
            $dbLicense = normalizeText($record['license_number']);
            $searchLicense = $licenseNumber; // Already normalized from frontend
            $matches = ($dbLicense === $searchLicense);
            $matchCount += $matches ? 1 : 0;
            $fieldMatches['license_number'] = [
                'searched' => $licenseNumber,
                'found' => $record['license_number'],
                'matches' => $matches
            ];
            error_log("License comparison - DB: '$dbLicense' vs Search: '$searchLicense' = " . ($matches ? 'MATCH' : 'NO MATCH'));
        }
        
        if (!empty($applicationType)) {
            $totalFields++;
            // Normalize both values before comparison
            $dbAppType = normalizeText($record['application_type']);
            $searchAppType = $applicationType; // Already normalized from frontend
            $matches = ($dbAppType === $searchAppType);
            $matchCount += $matches ? 1 : 0;
            $fieldMatches['application_type'] = [
                'searched' => $applicationType,
                'found' => $record['application_type'],
                'matches' => $matches
            ];
            error_log("App Type comparison - DB: '$dbAppType' vs Search: '$searchAppType' = " . ($matches ? 'MATCH' : 'NO MATCH'));
        }
        
        if (!empty($validityLicense)) {
            $totalFields++;
            // Normalize both values before comparison
            $dbValidity = normalizeText($record['validity_license']);
            $searchValidity = $validityLicense; // Already normalized from frontend
            $matches = ($dbValidity === $searchValidity);
            $matchCount += $matches ? 1 : 0;
            $fieldMatches['validity_license'] = [
                'searched' => $validityLicense,
                'found' => $record['validity_license'],
                'matches' => $matches
            ];
            error_log("Validity comparison - DB: '$dbValidity' vs Search: '$searchValidity' = " . ($matches ? 'MATCH' : 'NO MATCH'));
        }
        
        $matchPercentage = $totalFields > 0 ? ($matchCount / $totalFields) * 100 : 0;
        
        $response['record'] = $record;
        $response['match_details'] = [
            'match_count' => $matchCount,
            'total_fields' => $totalFields,
            'match_percentage' => round($matchPercentage, 2),
            'field_matches' => $fieldMatches
        ];
        
        if ($matchPercentage == 100) {
            $response['message'] = '✅ Perfect match! All fields verified successfully.';
        } else if ($matchPercentage >= 80) {
            $response['message'] = '⚠️ Partial match. ' . $matchCount . ' out of ' . $totalFields . ' fields match (' . round($matchPercentage) . '%)';
        } else {
            $response['message'] = '❌ Poor match. Only ' . $matchCount . ' out of ' . $totalFields . ' fields match (' . round($matchPercentage) . '%)';
        }
        
    } else {
        $response['message'] = '❌ Document not found in database';
        
        // Show what was searched
        if (!empty($qrCode)) $response['searched_fields']['qr_code'] = $qrCode;
        if (!empty($owner)) $response['searched_fields']['owner'] = $owner;
        if (!empty($licenseNumber)) $response['searched_fields']['license_number'] = $licenseNumber;
        if (!empty($applicationType)) $response['searched_fields']['application_type'] = $applicationType;
        if (!empty($validityLicense)) $response['searched_fields']['validity_license'] = $validityLicense;
    }
    
    error_log("Response: " . json_encode($response));
    
    echo json_encode($response);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in check_dataandqr.php: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'found' => false,
        'error' => $e->getMessage()
    ]);
}
?>