<?php
session_start();
include '../../../db_conn.php';

if (!isset($_POST['ref_id'])) {
    exit('No ref_id provided');
}

$ref_id = htmlspecialchars($_POST['ref_id']);

// Get application_id
$app_sql = "SELECT application_id FROM applications WHERE ref_id = ?";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("s", $ref_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();

if ($app_result->num_rows === 0) {
    exit('Application not found');
}

$app_data = $app_result->fetch_assoc();
$application_id = $app_data['application_id'];

// Fetch all remarks with replies
$sql = "SELECT log_id, feedback_note, selected_documents, created_at, isdone, remark_replies
        FROM review_logs
        WHERE application_id = ?
        ORDER BY log_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div style="text-align: center; padding: 20px; color: #666;">
            <i class="material-icons" style="font-size: 48px;">comment</i>
            <p>No remarks yet</p>
          </div>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    $log_id = $row['log_id'];
    $feedback = htmlspecialchars($row['feedback_note']);
    $docs = $row['selected_documents'];
    $created_at = htmlspecialchars($row['created_at']);
    $isdone = $row['isdone'];
   
    // Parse replies from JSON
    $replies = [];
    if (!empty($row['remark_replies'])) {
        $decoded = json_decode($row['remark_replies'], true);
        if (is_array($decoded)) {
            $replies = $decoded;
        }
    }
   
    // Card styling based on status
    $cardClass = 'border-warning';
    $statusBadge = '<span class="badge bg-warning" style="font-weight:normal;color:black">Pending</span>';
   
    if ($isdone === 'yes') {
        $cardClass = 'border-success';
        // Changed/added here: small "Complied" badge
        $statusBadge = '<span class="badge bg-success" style="font-size: 0.75em;color:white;font-weight:normal">Responded</span>';
    }
   
    echo '<div class="card mb-3 ' . $cardClass . ' viewRemarkCard"
               style="cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
               data-log-id="' . $log_id . '"
               data-application-id="' . $application_id . '"
               data-date="' . $created_at . '"
               data-message="' . $feedback . '"
               data-documents="' . htmlspecialchars($docs) . '">';
   
    echo '<div class="card-body">';
   
    // Header with date, delete button, and status badges
    echo '<div class="d-flex justify-content-between align-items-start mb-2">';
    echo '<small class="text-muted" style="width:50%"><i class="material-icons" style="font-size: 14px; vertical-align: middle;">schedule</i> ' . $created_at . '</small>';
    echo '<div class="d-flex align-items-center gap-2">';
    echo $statusBadge;  // ← shows Complied badge when isdone = yes
  echo '<button class="btn btn-sm btn-outline-danger"
        onclick="event.stopPropagation(); openDeleteModal(' . $log_id . ');"
        style="font-size:12px; display:flex; align-items:center; gap:4px; white-space:nowrap;">
        <i class="material-icons" style="font-size:14px;">delete</i>
        Delete
      </button>';

    echo '</div>';
    echo '</div>';
   
    // Feedback message
    echo '<p class="mb-2" style="font-size: 14px;">' . nl2br($feedback) . '</p>';
   
    // Documents requested
    if (!empty($docs)) {
        $docArray = array_map('trim', explode(',', $docs));
        echo '<div class="mb-2">';
        echo '<small class="text-muted" style="font-weight: 600;">Documents Requested:</small><br>';
        echo '<div class="d-flex flex-wrap gap-2" style="margin-top: 5px;">';
        foreach ($docArray as $doc) {
            echo '<span class="badge bg-warning" style="font-size: 11px;">' . htmlspecialchars($doc) . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }
   
    // Client replies section
    if (!empty($replies)) {
        echo '<div class="mt-3 pt-3" style="border-top: 1px solid #dee2e6;">';
        echo '<strong style="font-size: 13px; color: #0f2c5a;">
                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">reply</i>
                Client Replies
              </strong>';
       
        foreach ($replies as $reply) {
            $replyText = htmlspecialchars($reply['reply_text']);
            $replyDate = isset($reply['created_at']) ? htmlspecialchars($reply['created_at']) : 'N/A';
           
            echo '<div class="mt-2 p-2" style="background: #e8f4f8; border-left: 3px solid #0f2c5a; border-radius: 4px;">';
            echo '<small class="text-muted" style="font-size: 11px;">
                    <i class="material-icons" style="font-size: 12px; vertical-align: middle;">schedule</i> ' . $replyDate . '
                  </small>';
            echo '<p class="mb-0 mt-1" style="
    font-size: 13px;
    color: #333;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
">' . nl2br($replyText) . '</p>';
            echo '</div>';
        }
       
        echo '</div>';
    }
   
    echo '</div>'; // card-body
    echo '</div>'; // card
}

$stmt->close();
$app_stmt->close();
$conn->close();
?>