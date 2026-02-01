<?php
include '../../../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ref_id'], $_POST['client_id'])) {
    $ref_id    = trim($_POST['ref_id']);
    $client_id = trim($_POST['client_id']);
    
    if (!empty($ref_id) && !empty($client_id)) {
        // Update application status
        if ($stmt = $conn->prepare("UPDATE applications SET status = 'Under Review' WHERE ref_id = ?")) {
            $stmt->bind_param("s", $ref_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Add notification
        require_once 'add_notification.php';
        $message = "Your application {$ref_id} is now under review by our team. You will be notified of any updates.";
        addNotification($client_id, $message, $ref_id);
        
        // Redirect to view application
        echo '
            <form id="redirectForm" method="POST" action="../view_application.php">
                <input type="hidden" name="ref_id" value="' . htmlspecialchars($ref_id, ENT_QUOTES, 'UTF-8') . '">
            </form>
            <script>
                document.getElementById("redirectForm").submit();
            </script>
        ';
    }
}
?>