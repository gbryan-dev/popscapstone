<?php
include '../../../db_conn.php';

if (isset($_POST['appID'])) {
    // Sanitize inputs
    $appID = isset($_POST['appID']) ? intval($_POST['appID']) : 0;
    
    if ($appID > 0) {
        $sql = "UPDATE applications 
                SET status = 'Rejected'
                WHERE application_id = $appID";
        
        if (mysqli_query($conn, $sql)) {
            echo "Successfully endorsed to director!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Missing or invalid input.";
    }
}
?>