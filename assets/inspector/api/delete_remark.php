<?php
include '../../../db_conn.php';

if (isset($_POST['log_id'])) {
    $log_id = intval($_POST['log_id']);

    try {
        // Delete from document_reuploads first
        $stmt1 = $conn->prepare("DELETE FROM document_reuploads WHERE log_id = ?");
        $stmt1->bind_param("i", $log_id);
        $stmt1->execute();
        $stmt1->close();

        // Delete from review_logs
        $stmt2 = $conn->prepare("DELETE FROM review_logs WHERE log_id = ?");
        $stmt2->bind_param("i", $log_id);
        $stmt2->execute();
        $stmt2->close();

        echo "success";
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
} else {
    echo "error: log_id not set";
}

$conn->close();
?>
