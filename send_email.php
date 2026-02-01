<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Dummy values (used in the email)
$client_id = generateClientID();
$plainPassword = str_replace("-", "", $client_id);
$recipientName = 'GBRY';
$recipientEmail = 'bryangalamgam@gmail.com';

function generateClientID() {
    $year = date('y');
    $monthDay = date('md');
    $milliseconds = substr((string)round(microtime(true) * 1000), -7);
    return "{$year}-{$monthDay}-{$milliseconds}";
}

function sendAccountEmail($to, $client_id, $password, $name) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'popscsg@gmail.com'; // ⚠️ Replace with your email
        $mail->Password = 'nbdf lqvi ezpr smtl'; // ⚠️ Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email settings
        $mail->setFrom('popscsg@gmail.com', 'POPS | CSG');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = 'POPS - Successfully Registered!';

        // HTML template body
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
      <p style="margin-top:0;">Hi <strong>{$name}</strong>,</p>
      <p>Your account has been successfully registered.</p>
      <p><strong>Client ID:</strong> {$client_id}<br><strong>Temporary Password:</strong> {$password}
      <br>Please log in and <strong>change your password immediately</strong> for your security.</p>
      <p style="margin-bottom:0;">Regards,<br>POPS Team</p>
    </div>
    
  </div>
</body>
</html>
EOT;




        $mail->send();
        echo "Email successfully sent to {$to}";
    } catch (Exception $e) {
        echo "Email failed to send. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Send the email
sendAccountEmail($recipientEmail, $client_id, $plainPassword, $recipientName);
?>
