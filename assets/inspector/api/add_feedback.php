<?php
// api/add_feedback.php
include '../../../db_conn.php';

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    echo "error: Database connection failed";
    exit;
}

// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "error: Invalid request method";
    exit;
}

// Get POST data

$client_id = isset($_POST['client_id']) ? trim($_POST['client_id']) : '';
$ref_id = isset($_POST['ref_id']) ? trim($_POST['ref_id']) : '';
$remarks_note = isset($_POST['remarks_note']) ? trim($_POST['remarks_note']) : '';
$current_time = isset($_POST['current_time']) ? trim($_POST['current_time']) : '';
$selected_documents = isset($_POST['selected_documents']) ? trim($_POST['selected_documents']) : '';


require_once 'add_notification.php';
$message = "There are remarks for {$ref_id}. Please review them. Additional documents may be required if indicated.";
addNotification($client_id, $message, $ref_id);

// Validate required fields
if (empty($ref_id)) {
    echo "error: Missing ref_id";
    exit;
}

if (empty($remarks_note)) {
    echo "error: Missing remarks note";
    exit;
}

// Get application_id from ref_id
$sql_get_app = "SELECT application_id FROM applications WHERE ref_id = ?";
$stmt = $conn->prepare($sql_get_app);

if (!$stmt) {
    echo "error: Database prepare error";
    exit;
}

$stmt->bind_param("s", $ref_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "error: Application not found";
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$application_id = $row['application_id'];
$stmt->close();

// Insert the feedback/remark
$sql_insert = "INSERT INTO review_logs (application_id, feedback_note, selected_documents, created_at) 
              VALUES (?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    echo "error: Database prepare error";
    $conn->close();
    exit;
}

$stmt_insert->bind_param("isss", $application_id, $remarks_note, $selected_documents, $current_time);

if ($stmt_insert->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt_insert->error;
}

$stmt_insert->close();

$conn->close();
?>