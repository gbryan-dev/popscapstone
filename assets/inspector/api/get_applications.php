<?php
include '../../../db_conn.php';

// Get status from query parameter, default to 'Pending'
$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

$sql = "
    SELECT p.ref_id, p.application_id, p.permit_for, p.valid_until, p.status, p.rejection_date, p.approval_date, p.reason_of_rejection, p.apply_date,
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
        $refId = htmlspecialchars($row['ref_id']);
        $application_id = htmlspecialchars($row['application_id']);
        $permitFor = htmlspecialchars($row['permit_for']);
        $clientNameSafe = htmlspecialchars($clientName ?? 'N/A');
        $reasonOfRejection = htmlspecialchars($row['reason_of_rejection'] ?? 'N/A');
        
        $valid_until = '';

if (!empty($row['valid_until'])) {
    $date = DateTime::createFromFormat('F d, Y', $row['valid_until']);
    if ($date) {
        $valid_until = $date->format('M. d, Y'); // Jan. 20, 2029
    }
}


        $approval_date = '';
        if (!empty($row['approval_date'])) {
            $date = preg_replace('/^[^,]+,\s*/', '', $row['approval_date']); // remove weekday
            $date = preg_replace('/:\d{2}\s*(AM|PM)/i', ' $1', $date); // remove seconds
            $approval_date = trim($date);
            
            // Convert month to 3 letters + dot
            $approval_date = preg_replace_callback('/^[A-Za-z]+/', function($matches) {
                $month = $matches[0];
                $shortMonths = [
                    'January'   => 'Jan.', 'February' => 'Feb.', 'March'    => 'Mar.', 'April'   => 'Apr.',
                    'May'       => 'May',  'June'     => 'Jun.', 'July'     => 'Jul.', 'August'  => 'Aug.',
                    'September' => 'Sep.', 'October'  => 'Oct.', 'November' => 'Nov.', 'December'=> 'Dec.'
                ];
                return $shortMonths[$month] ?? substr($month, 0, 3) . '.';
            }, $approval_date);
        }

        $rejection_date = '';
        if (!empty($row['rejection_date'])) {
            $date = preg_replace('/^[^,]+,\s*/', '', $row['rejection_date']); // remove weekday
            $date = preg_replace('/:\d{2}\s*(AM|PM)/i', ' $1', $date); // remove seconds
            $rejection_date = trim($date);
            
            // Convert month to 3 letters + dot
            $rejection_date = preg_replace_callback('/^[A-Za-z]+/', function($matches) {
                $month = $matches[0];
                $shortMonths = [
                    'January'   => 'Jan.', 'February' => 'Feb.', 'March'    => 'Mar.', 'April'   => 'Apr.',
                    'May'       => 'May',  'June'     => 'Jun.', 'July'     => 'Jul.', 'August'  => 'Aug.',
                    'September' => 'Sep.', 'October'  => 'Oct.', 'November' => 'Nov.', 'December'=> 'Dec.'
                ];
                return $shortMonths[$month] ?? substr($month, 0, 3) . '.';
            }, $rejection_date);
        }

        $applyDate = '';
        if (!empty($row['apply_date'])) {
            $date = preg_replace('/^[^,]+,\s*/', '', $row['apply_date']); // remove weekday
            $date = preg_replace('/:\d{2}\s*(AM|PM)/i', ' $1', $date); // remove seconds
            $applyDate = trim($date);
            
            // Convert month to 3 letters + dot
            $applyDate = preg_replace_callback('/^[A-Za-z]+/', function($matches) {
                $month = $matches[0];
                $shortMonths = [
                    'January'   => 'Jan.', 'February' => 'Feb.', 'March'    => 'Mar.', 'April'   => 'Apr.',
                    'May'       => 'May',  'June'     => 'Jun.', 'July'     => 'Jul.', 'August'  => 'Aug.',
                    'September' => 'Sep.', 'October'  => 'Oct.', 'November' => 'Nov.', 'December'=> 'Dec.'
                ];
                return $shortMonths[$month] ?? substr($month, 0, 3) . '.';
            }, $applyDate);
        }
        
        // Determine number of columns based on status
        if ($status === 'Rejected') {
            // For Rejected: Ref ID, Permit For, Client Name, Apply Date, Reason of Rejection, Action
            echo '<tr>
                <td>' . $refId . '</td>
                <td>' . $permitFor . '</td>
                <td>' . $clientNameSafe . '</td>
                <td>' . ($rejection_date ?: 'N/A') . '</td>
                <td>
                    <div style="max-width: 300px; word-wrap: break-word;">
                        ' . ($reasonOfRejection !== 'N/A' ? $reasonOfRejection : '<em style="color: #999;">No reason provided</em>') . '
                    </div>
                </td>
                <td>
                    <form method="POST" action="view_application">
                        <input type="hidden" name="ref_id" value="' . $refId . '">
                        <button type="submit" class="btn btn-primary btn-sm" style="background: #D52941 !important">View</button>
                    </form>
                </td>
            </tr>';
        } else if ($status === 'Permit Issued') {
            // For Rejected: Ref ID, Permit For, Client Name, Apply Date, Reason of Rejection, Action
            echo '<tr>
                <td>' . $refId . '</td>
                <td>' . $permitFor . '</td>
                <td>' . $clientNameSafe . '</td>
                <td>' . ($applyDate ?: 'N/A') . '</td>
                <td>' . ($approval_date ?: 'N/A') . '</td>
                <td>' . ($valid_until ?: 'N/A') . '</td>
                
                <td>
                    <form method="POST" action="spdf">
                        <input type="hidden" name="application_id" value="' . $application_id . '">
                        <button type="submit" class="btn btn-primary  bg-success btn-sm" >View&nbsp;Permit</button>
                    </form>
                </td>
            </tr>';
        } else {
            // For other statuses: Ref ID, Permit For, Client Name, Action
            echo '<tr>
                <td>' . $refId . '</td>
                <td>' . $permitFor . '</td>
                <td>' . $clientNameSafe . '</td>
                <td>
                    <form method="POST" action="' . ($status === 'Pending' || $status === 'Replied' ? 'api/underreview_and_view' : 'view_application') . '">
                        <input type="hidden" name="ref_id" value="' . $refId . '">
                        <button type="submit" class="btn btn-primary btn-sm" style="background: #D52941 !important">View</button>
                    </form>
                </td>
            </tr>';
        }
    }
} else {
    // Determine colspan based on status
    $colspan = ($status === 'Rejected') ? '10' : '10';
    echo '<tr><td colspan="' . $colspan . '" class="text-center" style="color: #999;">No permits found.</td></tr>';
}

$stmt->close();
$conn->close();
?>