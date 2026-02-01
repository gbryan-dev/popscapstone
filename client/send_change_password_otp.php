<?php
session_start();
include '../db_conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $client_id = $_SESSION['client_id'];
    $full_name = null;
    
    // Verify email belongs to logged-in user
    $stmt = $conn->prepare("SELECT email FROM clients_acc WHERE client_id = ? AND email = ?");
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
    
    // Try to find the full_name from retailers_info
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
        // Try to find the full_name from manufacturers_info (dealer_name used as full name)
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
            $stmt->close();
        } else {
            $stmt->close();
            echo "User information not found.";
            $conn->close();
            exit();
        }
    }
    
    // Generate OTP
    $otp = rand(1000, 9999);
    
    // Save OTP to session
    $_SESSION['change_password_otp'] = $otp;
    $_SESSION['change_password_email'] = $email;
    $_SESSION['otp_timestamp'] = time(); // Add timestamp for expiration
    
    // Send OTP via email
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



        $mail->Subject = 'Your OTP Code for Password Change';
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
      <p style="color: #333">Your OTP code for password change is: <strong style="font-size: 24px; color: #0f2c5a;">{$otp}</strong><br><br>
      <span style="color: #B82E2D;">This code will expire in 10 minutes. If you did not request this password change, please secure your account immediately.</span></p>
      <p style="margin-bottom:0;">Regards,<br>POPS Team</p>
    </div>
  </div>
</body>
</html>
EOT;
        
        $mail->send();
        echo "An OTP has been sent to your email address.";
    } catch (Exception $e) {
        echo "Failed to send OTP. Mailer Error: " . $mail->ErrorInfo;
    }
    
    $conn->close();
}
?>