<?php
session_start();
include '../../../db_conn.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['ref_id'])) {
        $ref_id = htmlspecialchars($_POST['ref_id']);
        
        // Start transaction for data integrity
        $conn->begin_transaction();
        
        try {
            // Step 1: Get application_id
            $sql_get_app = "SELECT application_id FROM applications WHERE ref_id = ?";
            $stmt_get_app = $conn->prepare($sql_get_app);
            $stmt_get_app->bind_param("s", $ref_id);
            $stmt_get_app->execute();
            $result_app = $stmt_get_app->get_result();
            
            if ($result_app->num_rows === 0) {
                throw new Exception("Application not found");
            }
            
            $app_data = $result_app->fetch_assoc();
            $application_id = $app_data['application_id'];
            $stmt_get_app->close();
            
            // Step 2: Get all document file names for physical deletion
            $sql_get_docs = "SELECT file_name FROM documents WHERE application_id = ?";
            $stmt_get_docs = $conn->prepare($sql_get_docs);
            $stmt_get_docs->bind_param("i", $application_id);
            $stmt_get_docs->execute();
            $result_docs = $stmt_get_docs->get_result();
            
            $document_files = [];
            while ($doc = $result_docs->fetch_assoc()) {
                $document_files[] = $doc['file_name'];
            }
            $stmt_get_docs->close();
            
            // Step 3: Get all reupload file names for physical deletion
            $sql_get_reuploads = "SELECT file_name FROM document_reuploads WHERE application_id = ?";
            $stmt_get_reuploads = $conn->prepare($sql_get_reuploads);
            $stmt_get_reuploads->bind_param("i", $application_id);
            $stmt_get_reuploads->execute();
            $result_reuploads = $stmt_get_reuploads->get_result();
            
            $reupload_files = [];
            while ($reup = $result_reuploads->fetch_assoc()) {
                $reupload_files[] = $reup['file_name'];
            }
            $stmt_get_reuploads->close();
            
            // Step 4: Delete from document_reuploads (child of review_logs)
            $sql_del_reuploads = "DELETE dr FROM document_reuploads dr 
                                  INNER JOIN review_logs rl ON dr.log_id = rl.log_id 
                                  WHERE rl.application_id = ?";
            $stmt_del_reuploads = $conn->prepare($sql_del_reuploads);
            $stmt_del_reuploads->bind_param("i", $application_id);
            $stmt_del_reuploads->execute();
            $stmt_del_reuploads->close();
            
            // Step 5: Delete from review_logs
            $sql_del_logs = "DELETE FROM review_logs WHERE application_id = ?";
            $stmt_del_logs = $conn->prepare($sql_del_logs);
            $stmt_del_logs->bind_param("i", $application_id);
            $stmt_del_logs->execute();
            $stmt_del_logs->close();
            
            // Step 6: Delete from documents
            $sql_del_docs = "DELETE FROM documents WHERE application_id = ?";
            $stmt_del_docs = $conn->prepare($sql_del_docs);
            $stmt_del_docs->bind_param("i", $application_id);
            $stmt_del_docs->execute();
            $stmt_del_docs->close();
            
            // Step 7: Delete from special_permit_display_fireworks
            $sql_del_fireworks = "DELETE FROM special_permit_display_fireworks WHERE application_id = ?";
            $stmt_del_fireworks = $conn->prepare($sql_del_fireworks);
            $stmt_del_fireworks->bind_param("i", $application_id);
            $stmt_del_fireworks->execute();
            $stmt_del_fireworks->close();
            
            // Step 8: Delete from permit_sell_firecrackers
            $sql_del_sell = "DELETE FROM permit_sell_firecrackers WHERE application_id = ?";
            $stmt_del_sell = $conn->prepare($sql_del_sell);
            $stmt_del_sell->bind_param("i", $application_id);
            $stmt_del_sell->execute();
            $stmt_del_sell->close();
            
            // Step 9: Delete from permit_transport_pyrotechnics
            $sql_del_transport = "DELETE FROM permit_transport_pyrotechnics WHERE application_id = ?";
            $stmt_del_transport = $conn->prepare($sql_del_transport);
            $stmt_del_transport->bind_param("i", $application_id);
            $stmt_del_transport->execute();
            $stmt_del_transport->close();
            
            // Step 10: Delete related notifications
            $sql_del_notifs = "DELETE FROM notifications WHERE message LIKE ?";
            $stmt_del_notifs = $conn->prepare($sql_del_notifs);
            $ref_id_pattern = "%$ref_id%";
            $stmt_del_notifs->bind_param("s", $ref_id_pattern);
            $stmt_del_notifs->execute();
            $stmt_del_notifs->close();
            
            // Step 11: Finally, delete from applications
            $sql_del_app = "DELETE FROM applications WHERE application_id = ?";
            $stmt_del_app = $conn->prepare($sql_del_app);
            $stmt_del_app->bind_param("i", $application_id);
            $stmt_del_app->execute();
            $stmt_del_app->close();
            
            // Commit transaction
            $conn->commit();
            
            // Step 12: Delete physical files AFTER successful database deletion
            $upload_path = '../../client/uploads/';
            
            // Delete document files
            foreach ($document_files as $file_name) {
                $file_path = $upload_path . $file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete reupload files
            foreach ($reupload_files as $file_name) {
                $file_path = $upload_path . $file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            echo "success";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
        
    } else {
        echo "Missing ref_id parameter";
    }
} else {
    echo "Invalid request method";
}

$conn->close();
?>