<?php
$ref_id = htmlspecialchars($_POST['ref_id']);
include '../../../db_conn.php';

$sql = "SELECT rl.log_id, rl.feedback_note, rl.created_at
        FROM review_logs rl
        JOIN applications p ON rl.application_id = p.application_id
        WHERE p.ref_id = ?
        ORDER BY rl.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ref_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-lg-4 col-xl-12 col-md-4" style="margin-bottom: 5px; padding:0px; position:relative;">
                <div class="card file photo" style="position:relative;">
                    <button 
                        type="button" 
                        class="openDeleteModal" 
                        data-id="' . $row['log_id'] . '" 
                        style="position:absolute; top:10px; right:10px; background:none; border:none; cursor:pointer; color:red;">
                        Delete
                    </button>
                    <div class="card-body file-info">
                        <p>' . htmlspecialchars($row['feedback_note']) . '</p>
                        <small style="color:grey">' . htmlspecialchars($row['created_at']) . '</small>
                    </div>
                </div>
              </div>';
    }
} else {
    echo '<div style="color:gray"><i class="fas fa-exclamation-triangle" style="color:red"></i> No data found.</div>';
}

$stmt->close();
$conn->close();
?>
