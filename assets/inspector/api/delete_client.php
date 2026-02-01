<?php
include '../../../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $client_id = mysqli_real_escape_string($conn, $_POST['client_id']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get all application IDs for this client
        $appSql = "SELECT application_id FROM applications WHERE client_id = '$client_id'";
        $appResult = mysqli_query($conn, $appSql);
        $applicationIds = [];
        
        while ($row = mysqli_fetch_assoc($appResult)) {
            $applicationIds[] = $row['application_id'];
        }
        
        // Delete related records for each application
        if (!empty($applicationIds)) {
            $appIdList = implode(',', $applicationIds);
            
            // Delete document reuploads
            mysqli_query($conn, "DELETE FROM document_reuploads WHERE application_id IN ($appIdList)");
            
            // Delete review logs
            mysqli_query($conn, "DELETE FROM review_logs WHERE application_id IN ($appIdList)");
            
            // Delete documents
            mysqli_query($conn, "DELETE FROM documents WHERE application_id IN ($appIdList)");
            
            // Delete special permits
            mysqli_query($conn, "DELETE FROM special_permit_display_fireworks WHERE application_id IN ($appIdList)");
            mysqli_query($conn, "DELETE FROM permit_sell_firecrackers WHERE application_id IN ($appIdList)");
            mysqli_query($conn, "DELETE FROM permit_transport_pyrotechnics WHERE application_id IN ($appIdList)");
        }
        
        // Delete applications
        mysqli_query($conn, "DELETE FROM applications WHERE client_id = '$client_id'");
        
        // Delete notifications
        mysqli_query($conn, "DELETE FROM notifications WHERE client_id = '$client_id'");
        
        // Delete client info tables
        mysqli_query($conn, "DELETE FROM manufacturers_info WHERE client_id = '$client_id'");
        mysqli_query($conn, "DELETE FROM retailers_info WHERE client_id = '$client_id'");
        
        // Finally, delete the client account
        $deleteSql = "DELETE FROM clients_acc WHERE client_id = '$client_id'";
        
        if (mysqli_query($conn, $deleteSql)) {
            // Commit transaction
            mysqli_commit($conn);
            echo "success";
        } else {
            throw new Exception(mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
    
    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>