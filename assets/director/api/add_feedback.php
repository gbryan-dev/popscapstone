<?php
if (!isset($_POST['ref_id']) || !isset($_POST['remarks_note'])) {
    echo "<script>
        window.close(); 
        window.location.href = 'about:blank'; 
    </script>";
    exit;
}

$ref_id = htmlspecialchars($_POST['ref_id']);
$currentTime = htmlspecialchars($_POST['current_time']);

$remarks_note = htmlspecialchars($_POST['remarks_note']);

include '../../../db_conn.php';

// Get application_id using ref_id
$sql = "SELECT application_id FROM applications WHERE ref_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ref_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Invalid reference ID";
    exit;
}

$permit = $result->fetch_assoc();
$application_id = $permit['application_id'];

// Insert feedback into review_logs
$insert_sql = "INSERT INTO review_logs (application_id, feedback_note, created_at) VALUES (?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iss", $application_id, $remarks_note, $currentTime);

if ($insert_stmt->execute()) {
    echo "Feedback submitted successfully";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$insert_stmt->close();
$conn->close();
?>
