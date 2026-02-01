<?php
include '../../../db_conn.php';
$sql_latest = "
    SELECT 
        p.ref_id, 
        p.client_id,
        p.permit_for, 
        p.status, 
        m.dealer_name, 
        r.full_name
    FROM applications p
    LEFT JOIN manufacturers_info m ON p.client_id = m.client_id
    LEFT JOIN retailers_info r ON p.client_id = r.client_id
    WHERE p.status = 'Pending' OR p.status = 'Replied'
    ORDER BY p.application_id DESC
    LIMIT 6
";
$result = mysqli_query($conn, $sql_latest);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status = $row['status']; 
        switch ($status) {
            case 'Pending':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-warning text-white">Pending</span>';
                break;
            case 'Replied':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">Replied to Remark</span>';
                break;
            case 'Under Review':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-info text-white">Under Review</span>';
                break;
            case 'Drafting Permit':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">Drafting Permit</span>';
                break;
            case 'Permit Issued':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-success text-white">Permit Issued</span>';
                break;
            case 'Rejected':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-danger text-white">Rejected</span>';
                break;
            case 'Expired':
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-danger text-white">Expired</span>';
                break;
            default:
                $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">' . htmlspecialchars($status) . '</span>';
        }
        
        $clientName = $row['dealer_name'] ? $row['dealer_name'] : $row['full_name'];
        
        echo '<tr>
                <td>' . htmlspecialchars($row['permit_for']) . '</td>
                <td>' . htmlspecialchars($clientName ?? 'N/A') . '</td>
                <td style="white-space:nowrap;">
                    <div style="color:inherit; text-decoration:none;">
                        ' . $badge . '
                    </div>
                </td>
                <td>
                    <form method="POST" action="api/underreview_and_view">
                        <input type="hidden" name="ref_id" value="' . htmlspecialchars($row['ref_id']) . '">
                        <input type="hidden" name="client_id" value="' . htmlspecialchars($row['client_id']) . '">
                        <button type="submit" class="btn btn-primary btn-sm">View</button>
                    </form>
                </td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="4">No permits found.</td></tr>'; 
}
?>