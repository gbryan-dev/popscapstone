<?php
include '../../../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ref_id'])) {
    $ref_id = $_POST['ref_id'];

    // Update status
    if ($stmt = $conn->prepare("UPDATE applications SET status = 'Under Review' WHERE ref_id = ?")) {
        $stmt->bind_param("s", $ref_id);
        $stmt->execute();
        $stmt->close();
    }

    // Output a form that auto-submits to view_application.php with POST
    echo '<form id="redirectForm" method="POST" action="../view_application">
            <input type="hidden" name="ref_id" value="' . htmlspecialchars($ref_id) . '">
          </form>
          <script>document.getElementById("redirectForm").submit();</script>';
    exit;
}

// If accessed directly, go back to index
header('Location: index.php');
exit;
