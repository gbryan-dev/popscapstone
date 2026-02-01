<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    
    // Check if OTP exists in session
    if (!isset($_SESSION['change_password_otp'])) {
        echo 'expired';
        exit();
    }
    
    // Check OTP expiration (10 minutes)
    if (isset($_SESSION['otp_timestamp'])) {
        $elapsed_time = time() - $_SESSION['otp_timestamp'];
        if ($elapsed_time > 600) { // 600 seconds = 10 minutes
            unset($_SESSION['change_password_otp']);
            unset($_SESSION['change_password_email']);
            unset($_SESSION['otp_timestamp']);
            echo 'expired';
            exit();
        }
    }
    
    // Verify OTP
    if ($otp == $_SESSION['change_password_otp']) {
        // Mark OTP as verified
        $_SESSION['otp_verified'] = true;
        echo 'success';
    } else {
        echo 'invalid';
    }
} else {
    echo 'invalid_request';
}
?>