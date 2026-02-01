<?php
include '../db_conn.php';

if (isset($_GET['ref_id'])) {
    $ref_id = $_GET['ref_id'];

    // Fetch application IDs based on ref_id
    $sql = "SELECT application_id FROM applications WHERE ref_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ref_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $application_ids = [];
    while ($row = $result->fetch_assoc()) {
        $application_ids[] = $row['application_id'];
    }

    // Fetch documents based on application IDs
    if (count($application_ids) > 0) {
        $placeholders = str_repeat('?,', count($application_ids) - 1) . '?';
        $sql = "SELECT * FROM documents WHERE application_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($application_ids)), ...$application_ids);
        $stmt->execute();
        $documents = $stmt->get_result();

        $docs = [];
        while ($doc = $documents->fetch_assoc()) {
            $docs[] = $doc;
        }

        // Return documents as JSON
        echo json_encode($docs);
    } else {
        echo json_encode([]);
    }
}
?>
