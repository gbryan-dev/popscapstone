<?php
session_start();
include '../db_conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $otptyped = $_POST['otptyped'];
    $currentTime = $_POST['currentTime'];
    
    
    // Verify OTP was verified in previous step
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        echo "Please verify OTP first.";
        exit();
    }
    
    // Double-check OTP matches
    if (!isset($_SESSION['change_password_otp']) || $otptyped != $_SESSION['change_password_otp']) {
        echo "Invalid OTP.";
        exit();
    }
    
    // Verify email and client_id match
    if (!isset($_SESSION['change_password_email']) || !isset($_SESSION['client_id'])) {
        echo "Session expired. Please try again.";
        exit();
    }
    
    $email = $_SESSION['change_password_email'];
    $client_id = $_SESSION['client_id'];
    
    // Verify the email belongs to the logged-in user
    $stmt = $conn->prepare("SELECT client_id FROM clients_acc WHERE client_id = ? AND email = ?");
    $stmt->bind_param("ss", $client_id, $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        echo "Invalid request.";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Get full_name for email
    $full_name = null;
    $stmt = $conn->prepare("
        SELECT r.full_name 
        FROM clients_acc c
        JOIN retailers_info r ON c.client_id = r.client_id
        WHERE c.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($full_name);
        $stmt->fetch();
        $stmt->close();
    } else {
        $stmt->close();
        $stmt = $conn->prepare("
            SELECT m.dealer_name 
            FROM clients_acc c
            JOIN manufacturers_info m ON c.client_id = m.client_id
            WHERE c.email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($full_name);
            $stmt->fetch();
        }
        $stmt->close();
    }
    
    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password in database
    $stmt = $conn->prepare("UPDATE clients_acc SET password = ? WHERE email = ? AND client_id = ?");
    $stmt->bind_param("sss", $hashed_password, $email, $client_id);
    
    if ($stmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.popscsg.xyz';
            $mail->SMTPAuth = true;
            $mail->Username = 'popsy@popscsg.xyz';
            $mail->Password = 'popsy@popscsg.xyz'; // Replace with actual password
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            $mail->setFrom('popsy@popscsg.xyz', 'POPS | CSG');
            $mail->addAddress($email, $full_name);
            $mail->addReplyTo('popsy@popscsg.xyz', 'POPS Support');
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Changed Successfully';
            
            
            $mail->Body = <<<EOT
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
      <p style="color: #333">Your password has been successfully changed on <strong>{$currentTime}</strong>.</p>
      <p style="color: #B82E2D;"><strong>If you did not make this change, please contact our support team immediately to secure your account.</strong></p>
      <p style="margin-bottom:0;">Regards,<br>POPS Team</p>
    </div>
  </div>
</body>
</html>
EOT;
            
            $mail->send();
        } catch (Exception $e) {
            // Log error but don't fail the password change
            error_log("Failed to send password change confirmation: " . $mail->ErrorInfo);
        }
        
        // Clear OTP session variables
        unset($_SESSION['change_password_otp']);
        unset($_SESSION['change_password_email']);
        unset($_SESSION['otp_verified']);
        unset($_SESSION['otp_timestamp']);
        
        echo "Password changed successfully.";
    } else {
        echo "Failed to change password. Please try again.";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>