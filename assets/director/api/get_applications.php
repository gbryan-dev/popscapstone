<?php
include '../../../db_conn.php';

// Get status from query parameter, default to 'Pending'
$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

$sql = "
    SELECT p.ref_id, p.permit_for, p.status,
           m.dealer_name,
           r.full_name
    FROM applications p
    LEFT JOIN manufacturers_info m ON p.client_id = m.client_id
    LEFT JOIN retailers_info r ON p.client_id = r.client_id
    WHERE p.status = ?
    ORDER BY p.application_id DESC
";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientName = $row['dealer_name'] ? $row['dealer_name'] : $row['full_name'];

        echo '<tr>
            <td>' . htmlspecialchars($row['ref_id']) . '</td>
            <td>' . htmlspecialchars($row['permit_for']) . '</td>
            <td>' . htmlspecialchars($clientName ?? 'N/A') . '</td>
            <td>
                <form method="POST" action="api/underreview_and_view">
                    <input type="hidden" name="ref_id" value="' . htmlspecialchars($row['ref_id']) . '">
                    <button type="submit" class="btn btn-primary btn-sm" style="background: #D52941 !important">View</button>
                </form>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="3">No permits found.</td></tr>';
}

$stmt->close();
$conn->close();
?>
