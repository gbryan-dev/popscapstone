<?php
include '../../../db_conn.php';
$sql_latest = "
    SELECT 
        p.ref_id, 
        p.permit_for, 
        p.status, 
        m.dealer_name, 
        r.full_name
    FROM applications p
    LEFT JOIN manufacturers_info m ON p.client_id = m.client_id
    LEFT JOIN retailers_info r ON p.client_id = r.client_id
    WHERE p.status = 'Endorsed To Director'
    ORDER BY p.application_id DESC
    LIMIT 6
";
$result = mysqli_query($conn, $sql_latest);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clientName = $row['dealer_name'] ? $row['dealer_name'] : $row['full_name'];
        $status = $row['status'];
        $refId = $row['ref_id'];
        
        echo '<tr>
                <td>' . htmlspecialchars($row['permit_for']) . '</td>
                <td>' . htmlspecialchars($clientName ?? 'N/A') . '</td>
                <td>';
        
        if ($status == 'Pending') {
            echo '<form method="POST" action="api/underreview_and_view">
                    <input type="hidden" name="ref_id" value="' . htmlspecialchars($refId) . '">
                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                </form>';
        }
        if ($status != 'Pending') {
            echo '<form method="POST" action="view_application">
                    <input type="hidden" name="ref_id" value="' . htmlspecialchars($refId) . '">
                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                </form>';
        }
        
        echo '</td></tr>';
    }
} else {
    echo '<tr><td colspan="3">No permits found.</td></tr>'; 
}
?>