<?php
include '../../../db_conn.php';

$sql = "
    SELECT p.ref_id, p.permit_for, p.application_id, p.status, p.apply_date,
           COALESCE(m.dealer_name, r.full_name) AS client_name
    FROM applications p
    LEFT JOIN manufacturers_info m ON p.client_id = m.client_id
    LEFT JOIN retailers_info r ON p.client_id = r.client_id
    WHERE status = 'Permit Issued' OR status = 'Endorsed To Director' OR status = 'Rejected'
    ORDER BY p.application_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $refId      = htmlspecialchars($row['ref_id']);
        $application_id      = htmlspecialchars($row['application_id']);
        $status     = htmlspecialchars($row['status']);
        $permitFor  = htmlspecialchars($row['permit_for']);
        $clientName = htmlspecialchars($row['client_name'] ?? 'N/A');

        // Clean date
        $date = preg_replace('/^[^,]+,\s*/', '', $row['apply_date']); // remove weekday
        $date = preg_replace('/:\d{2}\s*(AM|PM)/i', ' $1', $date);     // remove seconds
        $formattedDate = trim($date); // e.g., "November 28, 2025 at 07:31 PM"

        // Convert month to 3 letters + dot (Nov., Dec., Jan., etc.)
        $formattedDate = preg_replace_callback('/^[A-Za-z]+/', function($matches) {
            $month = $matches[0];
            $shortMonths = [
                'January'   => 'Jan.', 'February' => 'Feb.', 'March'    => 'Mar.', 'April'   => 'Apr.',
                'May'       => 'May.', 'June'     => 'Jun.', 'July'     => 'Jul.', 'August'  => 'Aug.',
                'September' => 'Sep.', 'October'  => 'Oct.', 'November' => 'Nov.', 'December'=> 'Dec.'
            ];
            return $shortMonths[$month] ?? substr($month, 0, 3) . '.';
        }, $formattedDate);

        // Safe for HTML
        $formattedDate = htmlspecialchars($formattedDate);

        // Badge class & color based on status
       

           switch ($status) {
    case 'Pending':
        $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-warning text-white">Pending</span>';
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
        $badge = '<span style="font-weight:400;font-size:12px !important" class="badge bg-primary text-white">' . $status . '</span>';
}

        echo '<tr>
            <td style="white-space:nowrap;">
                <div style="color:inherit; text-decoration:none;">
                    ' . $badge . '
                </div>
            </td>
            <td>' . $refId . '</td>
            <td>' . $permitFor . '</td>
            <td>' . $formattedDate . '</td>
            <td>' . $clientName . '</td>
            <td style="display:flex;gap:5px">';

        // Only display View button if status is 'Pending'
        if ($status == 'Pending') {
            echo '<form method="POST" action="api/underreview_and_view">
                    <input type="hidden" name="ref_id" value="' . $refId . '">
                    <button type="submit" class="btn bg-success btn-primary btn-sm">View</button>
                </form>';
        }


             if ($status != 'Pending') {

          echo '<form id="viewpermitbtn" action="spdf" method="POST" style="display:inline;">
          <input type="hidden" name="application_id" value="' . $application_id . '">
        <button type="submit" class="btn btn-primary  btn-sm bg-success" style="display:flex;">
            <div style="padding-top: 1px;padding-left: 3px;">View</div>
        </button>
    </form>';
     }

        echo '<form method="POST" action="api/delete_application.php">
                    <input type="hidden" name="ref_id" value="' . $refId . '">
                    <button type="submit" class="btn btn-danger btn-sm" 
                            onclick="return confirm(\'Are you sure you want to delete this application?\');">Delete</button>
                </form>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center">No permits found.</td></tr>';
}

$stmt->close();
$conn->close();
?>