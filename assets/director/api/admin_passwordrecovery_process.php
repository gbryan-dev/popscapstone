<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../../db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../PHPMailer/src/Exception.php';
require '../../../PHPMailer/src/PHPMailer.php';
require '../../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $username = null;
        $admin_id = null;
        
        // Check if email exists in officials_acc table
        $stmt = $conn->prepare("SELECT id, username FROM officials_acc WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $username);
            $stmt->fetch();
            $stmt->close();
        } else {
            $stmt->close();
            echo "The email you entered is not registered as an director account.";
            $conn->close();
            exit();
        }
        
        // Generate OTP
        $otp = rand(1000, 9999);
        
        // Save OTP to session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_otp'] = $otp;
        $_SESSION['admin_otp_email'] = $email;
        
        // Send OTP via email using PHPMailer
        $mail = new PHPMailer(true);
        
        // Server settings - Using your cPanel SMTP settings
        $mail->isSMTP();
        $mail->Host = 'mail.popscsg.xyz';
        $mail->SMTPAuth = true;
        $mail->Username = 'popsy@popscsg.xyz'; // Your email from cPanel
        $mail->Password = 'popsy@popscsg.xyz'; // Replace with popsy@popscsg.xyz password
        
        // Use SSL on port 465 (as recommended by your cPanel)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Optional: Enable debug (comment out after testing)
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';
        
        // Email content
        $mail->setFrom('popsy@popscsg.xyz', 'POPS | CSG');
        $mail->addAddress($email, $username);
        $mail->addReplyTo('popsy@popscsg.xyz', 'POPS Support');
        
        $mail->isHTML(true);
        $mail->Subject = 'Recovery - OTP Code for Password Reset';
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
      <p style="color: #333">Your OTP code for director password reset is: <strong>{$otp}</strong><br><br>
      <span style="color: #B82E2D;">Please use this code to complete your password reset. If you did not request this, please secure your account immediately.</span></p>
      <p style="margin-bottom:0;">Regards,<br>POPS Team</p>
    </div>
  </div>
</body>
</html>
EOT;
        
        $mail->send();
        echo "An OTP has been sent to your director email address.";
        
    } catch (Exception $e) {
        echo "Failed to send OTP. Mailer Error: " . $mail->ErrorInfo;
    }
    
    $conn->close();
}
?>