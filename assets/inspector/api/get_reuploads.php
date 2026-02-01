<?php
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;

    if ($log_id > 0 && $application_id > 0) {
        $sql = "SELECT 
                    reupload_id,
                    field_name,
                    file_name,
                    file_extension,
                    file_size,
                    uploaded_at,
                    status
                FROM document_reuploads 
                WHERE log_id = ? AND application_id = ?
                ORDER BY uploaded_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $log_id, $application_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reuploads = [];
        
        function formatBytes($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
            $power = min($power, count($units) - 1);
            $formatted = $bytes / pow(1024, $power);
            return round($formatted, $precision) . ' ' . $units[$power];
        }

        while ($row = $result->fetch_assoc()) {
            $reuploads[] = [
                'reupload_id' => $row['reupload_id'],
                'field_name' => $row['field_name'],
                'file_name' => $row['file_name'],
                'file_extension' => $row['file_extension'],
                'file_size' => formatBytes($row['file_size']),
                'uploaded_at' => $row['uploaded_at'],
                'status' => $row['status']
            ];
        }

        echo json_encode([
            'success' => true,
            'reuploads' => $reuploads
        ]);

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>