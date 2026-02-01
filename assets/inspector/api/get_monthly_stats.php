<?php
// get_monthly_stats.php
include '../../../db_conn.php';

header('Content-Type: application/json');

// Automatically get current year
$currentYear = date('Y');  // 2025 right now → will auto-update every new year

$clients   = array_fill(0, 12, 0);
$issued    = array_fill(0, 12, 0);
$rejected  = array_fill(0, 12, 0);  // We'll count long-pending as "stuck/rejected"

// Helper: Convert your text date like "Thursday, August 14, 2025 at 10:21:26 AM" → MySQL date
function parseAppDate($dateString) {
    $clean = substr($dateString, 0, strpos($dateString, ' at ')); // Remove time part
    return date('Y-m-d', strtotime($clean));
}

// 1. New Clients per month (retailers + manufacturers)
$query = "
    SELECT MONTH(STR_TO_DATE(SUBSTRING_INDEX(created_at, ' at ', 1), '%W, %M %e, %Y')) AS m,
           COUNT(*) AS cnt
    FROM clients_acc
    WHERE YEAR(STR_TO_DATE(SUBSTRING_INDEX(created_at, ' at ', 1), '%W, %M %e, %Y')) = ?
      AND role_id IN (3,4)
    GROUP BY m
    ORDER BY m";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $currentYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $clients[$row['m'] - 1] = (int)$row['cnt'];
}

// 2. Permits Issued per month
$query = "
    SELECT MONTH(STR_TO_DATE(SUBSTRING_INDEX(approval_date, ' at ', 1), '%W, %M %e, %Y')) AS m,
           COUNT(*) AS cnt
    FROM applications
    WHERE status = 'Permit Issued'
      AND approval_date IS NOT NULL
      AND YEAR(STR_TO_DATE(SUBSTRING_INDEX(approval_date, ' at ', 1), '%W, %M %e, %Y')) = ?
    GROUP BY m
    ORDER BY m";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $currentYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $issued[$row['m'] - 1] = (int)$row['cnt'];
}

// 3. Stuck / Likely Rejected (applications older than 60 days and still not issued)
$query = "
    SELECT MONTH(STR_TO_DATE(SUBSTRING_INDEX(apply_date, ' at ', 1), '%W, %M %e, %Y')) AS m,
           COUNT(*) AS cnt
    FROM applications
    WHERE status = 'Rejected'
      AND apply_date IS NOT NULL
      AND YEAR(STR_TO_DATE(SUBSTRING_INDEX(apply_date, ' at ', 1), '%W, %M %e, %Y')) = ?
    GROUP BY m
    ORDER BY m";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $currentYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $rejected[$row['m'] - 1] = (int)$row['cnt'];
}

// Output
echo json_encode([
    'year'       => $currentYear,
    'categories' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    'clients'    => array_values($clients),
    'issued'     => array_values($issued),
    'rejected'   => array_values($rejected)
]);

?>