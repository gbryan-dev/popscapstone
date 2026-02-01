<?php
session_start();
include '../db_conn.php';


$client_id = $_SESSION['client_id'];

$sql = "
    SELECT ref_id
    FROM applications
    WHERE client_id = ?
    GROUP BY ref_id
    ORDER BY MAX(application_id) DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$ref_ids = [];
while ($row = $result->fetch_assoc()) {
    $ref_ids[] = $row['ref_id'];
}

header('Content-Type: application/json');
echo json_encode($ref_ids);
?>
