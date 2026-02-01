<?php
session_start();
include '../db_conn.php';

$client_id = $_SESSION['client_id'] ?? null;
if (!$client_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'pending':
        $status_condition = "status = 'Pending'";
        $columns = ['ref_id', 'permit_for', 'apply_date', 'status'];
        break;

    case 'endorsed_to_director':
        $status_condition = "status = 'Endorsed To Director'";
        $columns = ['ref_id', 'permit_for', 'apply_date', 'status'];
        break;

    case 'issued':
        $status_condition = "status = 'Permit Issued'";
        $columns = ['ref_id', 'permit_for', 'apply_date', 'approval_date', 'valid_until', 'status'];
        break;

    case 'underreview':
        // ── IMPORTANT: per-application pending remarks count + priority to those with pending remarks ───────
        $query = "
            SELECT 
                a.ref_id, 
                a.permit_for, 
                a.apply_date, 
                a.status, 
                a.application_id,
                COUNT(rl.log_id) AS pending_remarks
            FROM applications a
            LEFT JOIN review_logs rl 
                ON rl.application_id = a.application_id 
                AND rl.isdone = 'no'
            WHERE a.client_id = ?
              AND a.status NOT IN ('Pending', 'Permit Issued', 'Rejected')
            GROUP BY a.application_id
            ORDER BY 
                pending_remarks DESC,          -- ← applications with pending remarks (isdone='no') come first
                a.application_id DESC          -- then newest first among same count
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        break;

    case 'rejected':
        $status_condition = "status = 'Rejected'";
        $columns = ['ref_id', 'permit_for', 'apply_date', 'rejection_date', 'status', 'reason_of_rejection'];
        break;

    case 'replied':
        $status_condition = "status = 'Replied'";
        $columns = ['ref_id', 'permit_for', 'apply_date', 'status'];
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type']);
        exit;
}

// For all other cases (not underreview)
if ($type !== 'underreview') {
    $select_cols = implode(", ", $columns);
    $query = "SELECT $select_cols FROM applications 
              WHERE client_id = ? AND $status_condition 
              ORDER BY application_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'type' => $type,
    'data' => $data
]);
?>