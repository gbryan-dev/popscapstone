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
    
    // Check current status
    $sql_check = "SELECT status, application_id FROM applications WHERE ref_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $ref_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_status = $row['status'];
        $application_id = $row['application_id'];
        
        // If status is "Replied", update to "Under Review"
        if ($current_status === 'Replied') {
            $sql_update = "UPDATE applications SET status = 'Under Review' WHERE ref_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $ref_id);
            
            if ($stmt_update->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Status updated to Under Review',
                    'old_status' => $current_status,
                    'new_status' => 'Under Review',
                    'updated' => true
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            $stmt_update->close();
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Status is not Replied',
                'current_status' => $current_status,
                'updated' => false
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
    }
    
    $stmt_check->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>