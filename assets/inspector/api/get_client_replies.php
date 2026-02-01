<?php
session_start();
include '../../../db_conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;

if (empty($log_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing log_id']);
    exit;
}

// Fetch the remark_replies column
$sql = "SELECT remark_replies FROM review_logs WHERE log_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $log_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Remark not found']);
    exit;
}

$row = $result->fetch_assoc();
$remark_replies_json = $row['remark_replies'];

// Parse the JSON replies
$replies = [];
if (!empty($remark_replies_json)) {
    $decoded = json_decode($remark_replies_json, true);
    if (is_array($decoded)) {
        $replies = $decoded;
    }
}

echo json_encode([
    'success' => true,
    'replies' => $replies
]);

$stmt->close();
$conn->close();
?>