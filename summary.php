<!-- ============================================== -->
<!-- TASK 1: NOTIFICATION TYPES FOR PERMITTING SYSTEM -->
<!-- ============================================== -->

/*
DATABASE TABLE: notifications
Columns: id, client_id, message, is_read, created_at

NOTIFICATION TYPES TO IMPLEMENT:
================================

1. APPLICATION SUBMITTED
   - When: Client submits a new permit application
   - Message: "Your permit application [REF_ID] has been successfully submitted."
   - Trigger: After successful form submission

2. APPLICATION UNDER REVIEW
   - When: Admin changes status to "Under Review"
   - Message: "Your application [REF_ID] is now under review by our team."
   - Trigger: Status changed to "Under Review"

3. DOCUMENTS REQUIRED (REMARKS)
   - When: Admin adds remarks requesting documents
   - Message: "Additional documents required for [REF_ID]. Please check remarks and upload required files."
   - Trigger: New remark added in review_logs

4. DOCUMENTS RE-UPLOADED
   - When: Client re-uploads documents after remarks
   - Message: "Your re-uploaded documents for [REF_ID] have been received and will be reviewed."
   - Trigger: After document reupload submission

5. APPLICATION APPROVED
   - When: Admin approves application
   - Message: "Congratulations! Your permit [REF_ID] has been approved. Valid until [DATE]."
   - Trigger: Status changed to "Permit Issued"

6. APPLICATION REJECTED
   - When: Admin rejects application
   - Message: "Your application [REF_ID] has been rejected. Please check remarks for details."
   - Trigger: Status changed to "Rejected"

7. PAYMENT VERIFIED
   - When: Admin verifies payment
   - Message: "Your payment for [REF_ID] has been verified and accepted."
   - Trigger: Payment verification complete

8. PERMIT EXPIRING SOON
   - When: Permit expiring in 30 days
   - Message: "Your permit [REF_ID] will expire on [DATE]. Please renew soon."
   - Trigger: Scheduled/Cron job

9. REMARK REPLIED
   - When: Client replies to a remark
   - Message: "Your reply to the remark for [REF_ID] has been submitted."
   - Trigger: After remark reply submission

10. STATUS CHANGED
    - When: Any status change occurs
    - Message: "Status update: Your application [REF_ID] status changed to [NEW_STATUS]."
    - Trigger: Any status update
*/

<!-- ============================================== -->
<!-- TASK 2: REUSABLE NOTIFICATION FUNCTION -->
<!-- ============================================== -->



<!-- ============================================== -->
<!-- USAGE EXAMPLES IN DIFFERENT FILES -->
<!-- ============================================== -->

<?php
// EXAMPLE 1: In retailer_apply_permit.php (after successful submission)




// EXAMPLE 3: When adding remarks


// EXAMPLE 4: When permit is approved
require_once 'add_notification.php';
$message = "Congratulations! Your permit {$ref_id} has been approved. Valid until {$valid_until}.";
addNotification($client_id, $message, $ref_id);

// EXAMPLE 5: When permit is rejected
require_once 'add_notification.php';
$message = "Your application {$ref_id} has been rejected. Please check remarks for details or contact support.";
addNotification($client_id, $message, $ref_id);

// EXAMPLE 6: Send notification without email
require_once 'add_notification.php';
$message = "This is a system notification only.";
addNotification($client_id, $message, $ref_id, false); // false = don't send email
?>


<!-- ============================================== -->
<!-- FETCH NOTIFICATIONS FOR CLIENT DASHBOARD -->
<!-- ============================================== -->

<?php
// fetch_notifications.php
session_start();
include '../db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$client_id = $_SESSION['client_id'];

// Get all notifications for this client
$stmt = $conn->prepare("
    SELECT id, message, is_read, created_at 
    FROM notifications 
    WHERE client_id = ? 
    ORDER BY id DESC
");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => count(array_filter($notifications, function($n) {
        return $n['is_read'] === 'no';
    }))
]);

$stmt->close();
$conn->close();
?>

<!-- ============================================== -->
<!-- MARK NOTIFICATION AS READ -->
<!-- ============================================== -->

<?php
// mark_notification_read.php
session_start();
include '../db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['client_id'])) {
    $notification_id = $_POST['notification_id'];
    $client_id = $_SESSION['client_id'];
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 'yes' 
        WHERE id = ? AND client_id = ?
    ");
    $stmt->bind_param("is", $notification_id, $client_id);
    
    echo $stmt->execute() ? 'Success' : 'Failed';
    
    $stmt->close();
    $conn->close();
}
?>