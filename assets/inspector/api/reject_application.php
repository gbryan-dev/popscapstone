<?php
session_start();
include '../../../db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['ref_id'])) {
        
        $ref_id = htmlspecialchars($_POST['ref_id']);
        $application_id = htmlspecialchars($_POST['application_id']);
        $current_time = htmlspecialchars($_POST['current_time']);
        $val_reason_of_rejection = htmlspecialchars($_POST['val_reason_of_rejection']);
        
        // Update application status to Rejected and save rejection reason
        $sql = "UPDATE applications SET status = 'Rejected', rejection_date = ?, reason_of_rejection = ? WHERE ref_id = ? AND application_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $current_time, $val_reason_of_rejection, $ref_id, $application_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Close the first statement before creating the second one
                $stmt->close();
                
                // Get client_id and permit_for for notification
                $sql_client = "SELECT client_id, permit_for FROM applications WHERE ref_id = ?";
                $stmt_client = $conn->prepare($sql_client);
                $stmt_client->bind_param("s", $ref_id);
                $stmt_client->execute();
                $result_client = $stmt_client->get_result();
                
                if ($result_client->num_rows > 0) {
                    $client = $result_client->fetch_assoc();
                    $client_id = $client['client_id'];
                    $permit_for = $client['permit_for'];
                    
                    // Close the second statement
                    $stmt_client->close();
                    
                    // Insert notification
                    require_once 'add_notification.php';
                    $message = "Your application $ref_id for '$permit_for' has been rejected. Reason: $val_reason_of_rejection";
                    addNotification($client_id, $message, $ref_id);
                } else {
                    $stmt_client->close();
                }
                
                echo "success";
            } else {
                $stmt->close();
                echo "Error: No rows updated. Please check if ref_id and application_id are correct.";
            }
        } else {
            echo "Error: Failed to update application status - " . $stmt->error;
            $stmt->close();
        }
        
    } else {
        echo "Missing required parameters (ref_id, application_id, current_time, or rejection reason)";
    }
} else {
    echo "Invalid request method";
}

$conn->close();
?>