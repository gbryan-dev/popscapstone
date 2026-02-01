<?php
// api/get_documents_display.php
header('Content-Type: application/json');
include '../../../db_conn.php';

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $power = min($power, count($units) - 1);
    $formatted = $bytes / pow(1024, $power);
    return round($formatted, $precision) . ' ' . $units[$power];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['ref_id'])) {
        $ref_id = htmlspecialchars($_POST['ref_id']);

        $sql = "SELECT application_id FROM applications WHERE ref_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ref_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $permit = $result->fetch_assoc();
            $application_id = $permit['application_id'];

            $sql_docs = "SELECT * FROM documents WHERE application_id = ? ORDER BY id ASC";
            $stmt_docs = $conn->prepare($sql_docs);
            $stmt_docs->bind_param("i", $application_id);
            $stmt_docs->execute();
            $documents = $stmt_docs->get_result();

            $docs_array = [];

            while ($doc = $documents->fetch_assoc()) {
                $rawSize = isset($doc['file_size']) ? $doc['file_size'] : 0;
                
                $docs_array[] = [
                    'id' => $doc['id'],
                    'file_name' => htmlspecialchars($doc['file_name']),
                    'field_name' => htmlspecialchars($doc['field_name']),
                    'file_extension' => htmlspecialchars($doc['file_extension']),
                    'file_size' => $rawSize,
                    'file_size_formatted' => formatBytes($rawSize)
                ];
            }

            echo json_encode([
                'success' => true,
                'documents' => $docs_array
            ]);

        } else {
            echo json_encode([
                'success' => true,
                'documents' => []
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Reference ID is required'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>