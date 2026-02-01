<?php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$ref_id = isset($_GET['ref_id']) ? $_GET['ref_id'] : '';

if (empty($ref_id)) {
    echo json_encode(['error' => 'Missing ref_id']);
    exit;
}

$client_id = $_SESSION['client_id'];

// Get application_id and verify ownership
$app_sql = "SELECT application_id, client_id FROM applications WHERE ref_id = ?";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("s", $ref_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();

if ($app_result->num_rows === 0) {
    echo json_encode(['error' => 'Application not found']);
    exit;
}

$app_data = $app_result->fetch_assoc();
$application_id = $app_data['application_id'];

if ($app_data['client_id'] !== $client_id) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch remarks with replies
$sql = "SELECT log_id, feedback_note, selected_documents, created_at, isdone, remark_replies 
        FROM review_logs 
        WHERE application_id = ? AND isdone = 'no'
        ORDER BY log_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

$remarks = [];
while ($row = $result->fetch_assoc()) {
    $docs = [];
    if (!empty($row['selected_documents'])) {
        $docs = array_map('trim', explode(',', $row['selected_documents']));
    }
    
    // Parse replies from JSON
    $replies = [];
    if (!empty($row['remark_replies'])) {
        $decoded = json_decode($row['remark_replies'], true);
        if (is_array($decoded)) {
            $replies = $decoded;
        }
    }
    
    $remarks[] = [
        'log_id' => $row['log_id'],
        'feedback_note' => $row['feedback_note'],
        'selected_documents' => $docs,
        'created_at' => $row['created_at'],
        'isdone' => $row['isdone'],
        'replies' => $replies
    ];
}

echo json_encode([
    'success' => true,
    'remarks' => $remarks,
    'application_id' => $application_id
]);

$stmt->close();
$app_stmt->close();
$conn->close();
?>