<?php
include '../../../db_conn.php';
if (isset($_POST['appID'])) {
    // Sanitize inputs
    $appID = isset($_POST['appID']) ? intval($_POST['appID']) : 0;
    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
    
    if ($appID > 0) {
        // First, fetch the ref_id from the database
        $query = "SELECT ref_id FROM applications WHERE application_id = $appID";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $ref_id = $row['ref_id'];
            
            // Update the application status
            $sql = "UPDATE applications 
                    SET status = 'Endorsed To Director'
                    WHERE application_id = $appID";
                    
            if (mysqli_query($conn, $sql)) {
                // Send notification with the correct ref_id
                require_once 'add_notification.php';
                $message = "Your application {$ref_id} has been endorsed to the Director for review.";
                addNotification($client_id, $message, $ref_id);
                
                echo "Successfully endorsed to director!";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            echo "Application not found.";
        }
    } else {
        echo "Missing or invalid input.";
    }
}
?>