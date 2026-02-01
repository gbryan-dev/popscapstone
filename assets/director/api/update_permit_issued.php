<?php
include '../../../db_conn.php';

if (isset($_POST['appID'])) {
    // Sanitize inputs
    $appID = isset($_POST['appID']) ? intval($_POST['appID']) : 0;
    $client_id = isset($_POST['client_id']) ? mysqli_real_escape_string($conn, trim($_POST['client_id'])) : '';
    $ref_id = isset($_POST['ref_id']) ? mysqli_real_escape_string($conn, trim($_POST['ref_id'])) : '';
    $currentTime = isset($_POST['current_time']) ? mysqli_real_escape_string($conn, trim($_POST['current_time'])) : '';
    $validUntil = isset($_POST['valid_until']) ? mysqli_real_escape_string($conn, trim($_POST['valid_until'])) : '';
    
    // Validate required fields
    if (!$appID || !$client_id || !$ref_id || !$currentTime || !$validUntil) {
        echo "Missing or invalid input.";
        exit;
    }
    
    // Use prepared statement for security
    $sql = "UPDATE applications 
            SET status = 'Permit Issued',
                approval_date = ?,
                valid_until = ?
            WHERE application_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssi", $currentTime, $validUntil, $appID);
        
        if ($stmt->execute()) {
            // Send notification after successful update
            require_once 'add_notification.php';
            $message = "Congratulations! Your permit {$ref_id} has been approved. Valid until {$validUntil}.";
            addNotification($client_id, $message, $ref_id);
            
            echo "Successfully endorsed to director!";
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>