<?php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
$application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
$reply_text = isset($_POST['reply_text']) ? trim($_POST['reply_text']) : '';
$current_time = isset($_POST['current_time']) ? $_POST['current_time'] : '';
$client_id = $_SESSION['client_id'];

// Validation
if (empty($log_id) || empty($application_id) || empty($reply_text)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (strlen($reply_text) > 500) {
    echo json_encode(['success' => false, 'error' => 'Reply text too long (max 500 characters)']);
    exit;
}

// Verify the application belongs to this client
$verify_sql = "SELECT client_id FROM applications WHERE application_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("i", $application_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Application not found']);
    exit;
}

$app_data = $verify_result->fetch_assoc();
if ($app_data['client_id'] !== $client_id) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get existing replies
$get_sql = "SELECT remark_replies FROM review_logs WHERE log_id = ?";
$get_stmt = $conn->prepare($get_sql);
$get_stmt->bind_param("i", $log_id);
$get_stmt->execute();
$get_result = $get_stmt->get_result();

if ($get_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Remark not found']);
    exit;
}

$log_data = $get_result->fetch_assoc();
$existing_replies = $log_data['remark_replies'];

// Parse existing replies (stored as JSON array)
$replies_array = [];
if (!empty($existing_replies)) {
    $decoded = json_decode($existing_replies, true);
    if (is_array($decoded)) {
        $replies_array = $decoded;
    }
}

// Add new reply
$new_reply = [
    'reply_text' => $reply_text,
    'created_at' => $current_time,
    'client_id' => $client_id
];

$replies_array[] = $new_reply;

// Convert back to JSON
$updated_replies_json = json_encode($replies_array, JSON_UNESCAPED_UNICODE);

// Update the review_logs table
$update_sql = "UPDATE review_logs SET remark_replies = ? WHERE log_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $updated_replies_json, $log_id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reply submitted successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to submit reply: ' . $conn->error]);
}
// Update review_logs
$stmt = $conn->prepare("UPDATE review_logs SET isdone = 'yes' WHERE log_id = ?");
$stmt->bind_param("i", $log_id);
$stmt->execute();
$stmt->close();

// Update applications
$stmt = $conn->prepare("UPDATE applications SET status = 'Replied' WHERE application_id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$stmt->close();

$update_stmt->close();
$get_stmt->close();
$verify_stmt->close();
$conn->close();
?>