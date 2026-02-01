<?php
session_start();
header('Content-Type: text/plain');

if (isset($_POST['otp'])) {
    $inputOtp = trim($_POST['otp']);
    
    if (isset($_SESSION['otp'])) {
        $sessionOtp = (string)$_SESSION['otp'];
        
        if ($sessionOtp === $inputOtp) {
            echo 'success';
        } else {
            echo 'fail';
        }
    } else {
        echo 'fail';
    }
} else {
    echo 'fail';
}
?>