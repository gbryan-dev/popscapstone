<?php
/**
 * add_notification.php
 * 
 * Reusable function to add notifications with email sending
 * Just include this file and call addNotification() anywhere in your code
 * 
 * Usage:
 * require_once 'add_notification.php';
 * addNotification($client_id, $message, $ref_id);
 */

include '../db_conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/**
 * Add notification to database and send email
 * 
 * @param string $client_id - The client ID
 * @param string $message - Notification message
 * @param string $ref_id - Reference ID (optional, for email subject)
 * @param bool $send_email - Whether to send email (default: true)
 * @return bool - Success status
 */
function addNotification($client_id, $message, $ref_id = '', $send_email = true) {
    global $conn;
    
    // Get current time in Manila timezone
    $datetime = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $created_at = $datetime->format('l, F j, Y \a\t h:i:s A');
    
    // Insert notification into database
    $stmt = $conn->prepare("INSERT INTO notifications (client_id, message, is_read, created_at, ref_id) VALUES (?, ?, 'no', ?, ?)");
    $stmt->bind_param("ssss", $client_id, $message, $created_at, $ref_id);
    
    if (!$stmt->execute()) {
        error_log("Failed to insert notification: " . $stmt->error);
        $stmt->close();
        return false;
    }
    $stmt->close();
    
    // Send email if enabled
    if ($send_email) {
        sendNotificationEmail($client_id, $message, $ref_id, $created_at);
    }
    
    return true;
}

/**
 * Send notification email using PHPMailer
 * 
 * @param string $client_id
 * @param string $message
 * @param string $ref_id
 * @param string $created_at
 */
function sendNotificationEmail($client_id, $message, $ref_id, $created_at) {
    global $conn;
    
    // Get client email and name
    $email = null;
    $full_name = null;
    
    // Try retailers_info first
    $stmt = $conn->prepare("
        SELECT c.email, r.full_name 
        FROM clients_acc c
        LEFT JOIN retailers_info r ON c.client_id = r.client_id
        WHERE c.client_id = ?
    ");
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $full_name = $row['full_name'];
    }
    $stmt->close();
    
    // If not found, try manufacturers_info
    if (empty($full_name)) {
        $stmt = $conn->prepare("
            SELECT c.email, m.dealer_name 
            FROM clients_acc c
            LEFT JOIN manufacturers_info m ON c.client_id = m.client_id
            WHERE c.client_id = ?
        ");
        $stmt->bind_param("s", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $full_name = $row['dealer_name'];
        }
        $stmt->close();
    }
    
    if (empty($email)) {
        error_log("Email not found for client_id: " . $client_id);
        return false;
    }
    
    // Prepare email subject
    $subject = empty($ref_id) 
        ? 'POPS Notification - Important Update' 
        : "POPS Notification - Application {$ref_id}";
    
    
    $mail = new PHPMailer(true);
    
    try {


      


         $mail->isSMTP();
        $mail->Host = 'mail.popscsg.xyz';
        $mail->SMTPAuth = true;
        $mail->Username = 'popsy@popscsg.xyz'; // Your email from cPanel
        $mail->Password = 'popsy@popscsg.xyz'; // Replace with popsy@popscsg.xyz password
        
        // Use SSL on port 465 (as recommended by your cPanel)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Email content
        $mail->setFrom('popsy@popscsg.xyz', 'POPS | CSG');
        $mail->addAddress($email, $full_name);
        $mail->addReplyTo('popsy@popscsg.xyz', 'POPS Support');
        
        $mail->isHTML(true);









        $mail->Subject = $subject;
        $mail->Body = getEmailTemplate($full_name, $message, $created_at);
        
        $mail->send();








        return true;
    } catch (Exception $e) {
        error_log("Email send failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Get email template
 */
function getEmailTemplate($full_name, $message, $created_at) {
    return <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    @media only screen and (max-width: 620px) {
      .container {
        width: 100% !important;
        padding: 10px !important;
      }
      .header {
        height: 150px !important;
      }
    }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
  <div class="container" style="max-width:600px; width:100%; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;">
    <div class="header" style="
      background-image: url('https://popscsg.xyz/forgmail.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 300px;">
    </div>
    <div style="padding: 30px; color: #333; font-size: 16px; line-height: 1.5;">
      <p style="margin-top:0;">Hi <strong>{$full_name}</strong>,</p>
      <p style="color: #333">{$message}</p>
      <p style="color: #666; font-size: 14px; margin-top: 20px;">
        <em>Notification sent on: {$created_at}</em>
      </p>
      <p style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0f2c5a;">
        Please log in to your POPS account to view full details and take necessary actions.
      </p>
      <p style="margin-bottom:0; margin-top: 30px;">Regards,<br><strong>POPS Team</strong><br>Civil Security Group | Philippine National Police</p>
    </div>
  </div>
</body>
</html>
EOT;
}

// Example usage in your code:
// require_once 'add_notification.php';
// addNotification($client_id, "Your application RET25120803502 has been approved!", "RET25120803502");
?>