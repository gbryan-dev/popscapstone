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
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email setup
        $mail->setFrom('popscsg@gmail.com', 'POPS | CSG');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = 'POPS - Successfully registered!';

        $fullName2 = $_POST['dealer_name'];

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
          <tr>
            <td class="header" height="300" style="background-image: url('https://openseanftoffers.art/SUCREGMAIN.png'); background-size: cover; background-position: center; height: 300px;"></td>
          </tr>
          <tr>
            <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.5;">
              <p>Hi <strong>{$fullName2}</strong>,</p>
              <p>Your account has been successfully registered in the Pyrotechnic Online Permitting System (POPS).</p>
              <div>
                <strong>Client ID:</strong> {$client_id}<br>
                <strong>Password:</strong> (the password you entered during registration)
              </div>
              <p style="color: #B82E2D;"><strong>Important:</strong> Keep your Client ID and password secure. You’ll need them to log in to the system.</p>
              <p>If you did not request this registration, please contact us immediately.</p>
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

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Step 1 - Basic Details
    $company_name     = $_POST['company_name'];
    $dealer_name      = $_POST['dealer_name'];
    $contact_number   = $_POST['contact_number'];
    $company_website  = $_POST['company_website'];
    $company_address  = $_POST['company_address'];
    $email            = $_POST['email'];
    $password         = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Step 2 - Manufacturer License
    $manufacturer_license_no   = $_POST['manufacturer_license_no'];
    $manufacturer_serial_no    = $_POST['manufacturer_serial_no'];
    $manufacturer_expiry_date  = $_POST['manufacturer_expiry_date'];
    
    // Step 3 - Dealer License
    $dealer_license_no   = $_POST['dealer_license_no'];
    $dealer_serial_no    = $_POST['dealer_serial_no'];
    $dealer_expiry_date  = $_POST['dealer_expiry_date'];

    $client_id = generateClientID();
    $role_id = 4; // Manufacturer role

    $created_at = $_POST['current_time'];

    // Check if email already exists
    $check = $conn->prepare("SELECT client_id FROM clients_acc WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email already registered.';
        echo json_encode($response);
        exit;
    }
    $check->close();

    // Step 1: Insert into clients_acc
    $stmt1 = $conn->prepare("INSERT INTO clients_acc (client_id, email, password, role_id, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssis", $client_id, $email, $password, $role_id, $created_at);
    
    if (!$stmt1->execute()) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register account.';
        echo json_encode($response);
        exit;
    }
    $stmt1->close();

    // Step 2: Insert into manufacturers_info
    $stmt2 = $conn->prepare("
        INSERT INTO manufacturers_info (
            client_id, company_name, dealer_name, contact_number,
            company_website, company_address,
            manufacturer_license_no, manufacturer_serial_no, manufacturer_expiry_date,
            dealer_license_no, dealer_serial_no, dealer_expiry_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "sssssssissss",
        $client_id, $company_name, $dealer_name, $contact_number,
        $company_website, $company_address,
        $manufacturer_license_no, $manufacturer_serial_no, $manufacturer_expiry_date,
        $dealer_license_no, $dealer_serial_no, $dealer_expiry_date
    );

    if ($stmt2->execute()) {
        // Send account email after successful registration
        sendAccountEmail($email, $client_id, $password, $dealer_name);  // Fixed missing semicolon
        $response['status'] = 'success';
        $response['message'] = 'Manufacturer registered successfully.';
        $response['client_id'] = $client_id;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to store manufacturer info.';
    }

    $stmt2->close();
    echo json_encode($response);
}
?>
