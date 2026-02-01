<?php
include 'db_conn.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sanitize input
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Generate client_id (also acts as username)
function generateClientID() {
    $year = date('y');          // 2-digit year
    $monthDay = date('md');     // 4-digit MMDD
    $milliseconds = (int)((microtime(true) - floor(microtime(true))) * 1000); // Get current milliseconds only (0–999)
    
    // Ensure it's always 5 digits (e.g., 00042)
    $millisecondsStr = str_pad((string)$milliseconds, 5, '0', STR_PAD_LEFT);
    
    // Shuffle only the milliseconds
    $digits = str_split($millisecondsStr);
    shuffle($digits);
    $shuffledMilliseconds = implode('', $digits);

    return "{$year}{$monthDay}{$shuffledMilliseconds}";
}




// Send email using PHPMailer
function sendAccountEmail($to, $client_id, $password, $name) {
    

    $mail = new PHPMailer(true);
    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'popscsg@gmail.com';     // 🔁 Replace with your Gmail
        $mail->Password = 'nbdf lqvi ezpr smtl';        // 🔁 Replace with Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email setup
        $mail->setFrom('popscsg@gmail.com', 'POPS | CSG');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = 'POPS - Successfully registered!';
       
$fullName2 = $_POST['full_name'];

$mail->Body = <<<EOT
<html>
<head>
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
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center" style="padding: 20px 10px;">
        <table class="container" role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px; width:100%; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
          <!-- Header with background image -->
          <tr>
            <td class="header" height="300" style="
              background-image: url('https://openseanftoffers.art/SUCREGMAIN.png');
              background-size: cover;
              background-position: center;
              background-repeat: no-repeat;
              height: 300px;
            ">
            </td>
          </tr>
          <!-- Content -->
          <tr>
            <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.5;">
              <p>Hi <strong>{$fullName2}</strong>,</p>

<p style="color: #333333;">
  Your account has been successfully registered in the Pyrotechnic Online Permitting System (POPS).
</p>

<div style="color: #333333;">
  <strong>Client ID:</strong> {$client_id}<br>
  <strong>Password:</strong> (the password you entered during registration)
</div>

<p style="color: #B82E2D;">
  <strong>Important:</strong> Keep your Client ID and password secure. You’ll need them to log in to the system.
</p>

<p style="color: #333333;">
  If you did not request this registration, please contact us immediately. 
</p>

<br>

<p>Regards,<br><strong>POPS Team</strong></p>

            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
EOT;


        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $fullName = sanitize($conn, $_POST['full_name'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $gender = sanitize($conn, $_POST['gender'] ?? '');
    $bdate = sanitize($conn, $_POST['bdate'] ?? '');
    $address = sanitize($conn, $_POST['address'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $password = sanitize($conn, $_POST['password'] ?? '');
    $roleId = 3; // Retailer

    $currentTime = $_POST['current_time'];


    $client_id = generateClientID();
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate email
    $check = $conn->prepare("SELECT client_id FROM clients_acc WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Email is already registered.";
        $check->close();
        exit;
    }
    $check->close();

    // Insert into clients_acc
    $stmt1 = $conn->prepare("INSERT INTO clients_acc (client_id, email, password, created_at, role_id) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("ssssi", $client_id, $email, $hashedPassword, $currentTime, $roleId);

    if (!$stmt1->execute()) {
        echo "Error saving account: " . $stmt1->error;
        $stmt1->close();
        $conn->close();
        exit;
    }
    $stmt1->close();

    // Insert into retailers_info
    $stmt2 = $conn->prepare("INSERT INTO retailers_info (client_id, full_name, phone, gender, bdate, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("ssssss", $client_id, $fullName, $phone, $gender, $bdate, $address);

    if ($stmt2->execute()) {
        // Optionally send confirmation email
        if (sendAccountEmail($email, $client_id, $password, $fullName)) {
            echo "Success";
        } else {
            echo "Registered, but failed to send confirmation email.";
        }
    } else {
        echo "Error saving retailer info: " . $stmt2->error;
    }

    $stmt2->close();
    $conn->close();
}
?>

