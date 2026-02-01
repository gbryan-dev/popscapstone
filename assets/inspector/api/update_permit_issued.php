<?php
include '../../../db_conn.php';

if (isset($_POST['appID'])) {
    // Sanitize inputs
    $appID = isset($_POST['appID']) ? intval($_POST['appID']) : 0;
    $currentTime = isset($_POST['current_time']) ? mysqli_real_escape_string($conn, trim($_POST['current_time'])) : '';
    $validUntil = isset($_POST['valid_until']) ? mysqli_real_escape_string($conn, trim($_POST['valid_until'])) : '';

    if ($appID > 0 && $currentTime && $validUntil) {
        // Prepare query
        $sql = "UPDATE applications 
                SET status = 'Permit Issued',
                    approval_date = '$currentTime',
                    valid_until = '$validUntil'
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
