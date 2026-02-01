<?php
session_start();
include '../db_conn.php';

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    echo '<div class="alert alert-warning text-center" style="margin: 20px;">
            <i class="fas fa-lock"></i>
            Please log in to view notifications
          </div>';
    exit;
}

$client_id = $_SESSION['client_id'];

// IMPORTANT: Make sure ref_id is selected
$sql = "SELECT id, message, is_read, created_at, ref_id
        FROM notifications
        WHERE client_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo '<div class="notifications-list">';
  
    while ($row = $result->fetch_assoc()) {
        $id         = htmlspecialchars($row['id']);
        $message    = htmlspecialchars($row['message']);
        $is_read    = $row['is_read'];
        $created_at = htmlspecialchars($row['created_at']);
        $ref_id     = isset($row['ref_id']) ? htmlspecialchars($row['ref_id']) : '';

        // Clean and format the date
        $date = preg_replace('/^[^,]+,\s*/', '', $created_at); // remove weekday
        $date = preg_replace('/:\d{2}\s*(AM|PM)/i', ' $1', $date); // remove seconds
        $formattedDate = trim($date);
       
        // Convert month to 3 letters + dot (your existing logic)
        $formattedDate = preg_replace_callback('/^[A-Za-z]+/', function($matches) {
            $month = $matches[0];
            $shortMonths = [
                'January' => 'January', 'February' => 'February', 'March' => 'March', 'April' => 'April',
                'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August',
                'September' => 'September', 'October' => 'October', 'November' => 'November', 'December'=> 'December'
            ];
            return $shortMonths[$month] ?? substr($month, 0, 3) . '.';
        }, $formattedDate);
       
        // ────────────────────────────────────────────────
        // Determine styling + click behavior
        // ────────────────────────────────────────────────
        $iconColor   = '#667eea';
        $bgGradient  = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $borderLeft  = '4px solid #dee2e6';
        $isClickable = false;
        $cursorStyle = 'default';
        $clickAttr   = '';

        $lowerMessage = strtolower($message);

        if (stripos($message, 'rejected') !== false) {
            $iconColor  = '#dc3545';
            $bgGradient = 'linear-gradient(135deg, #dc3545 0%, #dc3545 100%)';
            $borderLeft = '4px solid #dc3545';
        } 
        elseif (stripos($message, 'approved') !== false || stripos($message, 'issued') !== false) {
            $iconColor  = '#28a745';
            $bgGradient = 'linear-gradient(135deg, #28a745 0%, #28a745 100%)';
            $borderLeft = '4px solid #28a745';
            
            // ─── NEW: make "approved" notifications clickable ───────
            if (stripos($message, 'has been approved') !== false && !empty($ref_id)) {
                $isClickable = true;
                $cursorStyle = 'pointer';
                $clickAttr   = 'onclick="viewmypermit(\'' . $ref_id . '\')"';
            }
        } 
        elseif (stripos($message, 'under review') !== false ||
                stripos($message, 'successfully submitted') !== false ||
                stripos($message, 'has been endorsed') !== false) {
            $iconColor  = '#007bff';
            $bgGradient = 'linear-gradient(135deg, #007bff 0%, #007bff 100%)';
            $borderLeft = '4px solid #007bff';
        } 
        elseif (stripos($message, 'review them') !== false) {
            $iconColor  = '#ffc107';
            $bgGradient = 'linear-gradient(135deg, #ffc107 0%, #ffc107 100%)';
            $borderLeft = '4px solid #ffc107';
            $isClickable = true;
            $cursorStyle = 'pointer';
            $clickAttr   = 'onclick="viewRemarks(\'' . $ref_id . '\')"';
        }

        // Build class & data attributes
        $extraClass = $is_read ? 'read' : 'unread';

        echo '
        <div class="notification-item ' . $extraClass . '"
             data-notif-id="' . $id . '"
             style="border-left: ' . $borderLeft . '; border-right: ' . $borderLeft . '; cursor: ' . $cursorStyle . ';"
             ' . $clickAttr . '>
         
            <div class="notification-content">
                <p class="notification-message">' . $message . '</p>
                <small class="notification-time">
                    <i class="fas fa-clock"></i>
                    ' . $formattedDate . '
                </small>
            </div>
        </div>';
    }
  
    echo '</div>';
} else {
    echo '<div class="no-notifications">
            <i class="fas fa-bell-slash"></i>
            <p style="color: #999; margin-top: 15px; font-size: 16px;">No notifications yet</p>
            <small style="color: #bbb;">You\'ll be notified about your permit applications here</small>
          </div>';
}

$stmt->close();
$conn->close();
?>