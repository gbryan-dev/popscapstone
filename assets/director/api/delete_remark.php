<?php
include '../../../db_conn.php';

if (isset($_POST['log_id'])) {
    $log_id = intval($_POST['log_id']);

    $sql = "DELETE FROM review_logs WHERE log_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $log_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}
$conn->close();
?>
