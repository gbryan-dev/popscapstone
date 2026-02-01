<?php
session_start();
include 'db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp'])) {
        echo "Session expired or data missing.";
        exit;
    }

    $email = $_SESSION['otp_email'];
    $sessionOtp = $_SESSION['otp'];
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

    // Update password in clients_acc
    $stmt = $conn->prepare("UPDATE clients_acc SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        $stmt->close();

        // Try to get full_name from retailers_info
        $full_name = null;
$client_id = null;

// Try retailers_info
$stmt = $conn->prepare("
    SELECT r.full_name, c.client_id
    FROM clients_acc c
    JOIN retailers_info r ON c.client_id = r.client_id
    WHERE c.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($full_name, $client_id);
    $stmt->fetch();
} else {
    $stmt->close();

    // Try manufacturers_info
    $stmt = $conn->prepare("
        SELECT m.dealer_name, c.client_id
        FROM clients_acc c
        JOIN manufacturers_info m ON c.client_id = m.client_id
        WHERE c.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($full_name, $client_id);
        $stmt->fetch();
    } else {
        $full_name = "User";
        $client_id = null;
    }
}
$stmt->close();


        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'popscsg@gmail.com';
            $mail->Password = 'nbdf lqvi ezpr smtl'; // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('popscsg@gmail.com', 'POPS | CSG');
            $mail->addAddress($email, $full_name);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Has Been Changed';

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
      
      <span style="color: #333">This is a confirmation that your password has been <strong>successfully changed</strong>.</span>
      <div style="color: #333333;"><br>
        <strong>Client ID:</strong> {$client_id}
        </div>
      <p style="color: #B82E2D;">If you didn't request this, please secure your account immediately by contacting us.</p>
      <p style="margin-bottom:0;">Regards,<br>POPS Team</p>
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
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);

        echo "Password changed successfully. A confirmation email has been sent.";
    } else {
        echo "Failed to update password. Please try again.";
    }

    $conn->close();
}
?>
