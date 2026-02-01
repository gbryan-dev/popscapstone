<?php
session_start();
include '../../../db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../PHPMailer/src/Exception.php';
require '../../../PHPMailer/src/PHPMailer.php';
require '../../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_otp_email']) || !isset($_SESSION['admin_otp'])) {
        echo "Session expired or data missing.";
        exit;
    }

    $email = $_SESSION['admin_otp_email'];
    $sessionOtp = $_SESSION['admin_otp'];
    $newPassword = trim($_POST['password']);
    $enteredOtp = isset($_POST['otptyped']) ? trim($_POST['otptyped']) : '';

    if (empty($newPassword)) {
        echo "Password cannot be empty.";
        exit;
    }

    if (empty($enteredOtp)) {
        echo "OTP is required.";
        exit;
    }

    if ($enteredOtp != $sessionOtp) {
        echo "Invalid OTP.";
        exit;
    }

    // Hash the password securely
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in officials_acc
    $stmt = $conn->prepare("UPDATE officials_acc SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        $stmt->close();

        // Get admin details
        $username = null;
        $admin_id = null;

        $stmt = $conn->prepare("SELECT id, username FROM officials_acc WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $username);
            $stmt->fetch();
        } else {
            $username = "Admin";
            $admin_id = null;
        }
        $stmt->close();

        // Send confirmation email
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
        $mail->addAddress($email, $username);
        $mail->addReplyTo('popsy@popscsg.xyz', 'POPS Support');
        
        $mail->isHTML(true);
            
            
            $mail->Subject = 'Account - Password Has Been Changed';

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
      <p style="margin-top:0;">Hi <strong>{$username}</strong>,</p>
      
      <span style="color: #333">This is a confirmation that your director password has been <strong>successfully changed</strong>.</span>
      <div style="color: #333333;"><br>
        <strong>Admin ID:</strong> {$admin_id}<br>
        <strong>Username:</strong> {$username}
      </div>
      <p style="color: #B82E2D;">If you didn't request this, please secure your account immediately by resetting your password.</p>
      <p style="margin-bottom:0;">Regards,<br>POPS Admin Team</p>
    </div>

  </div>
</body>
</html>
EOT;

            $mail->send();
        } catch (Exception $e) {
            // Optionally log email sending error
        }

        // Clear session
        unset($_SESSION['admin_otp']);
        unset($_SESSION['admin_otp_email']);

        echo "Password changed successfully. A confirmation email has been sent.";
    } else {
        echo "Failed to update password. Please try again.";
    }

    $conn->close();
}
?>