<?php
session_start();

if (isset($_POST['otp'])) {
    $inputOtp = $_POST['otp'];
    
    if (isset($_SESSION['admin_otp']) && $_SESSION['admin_otp'] == $inputOtp) {
        echo 'success';
    } else {
        echo 'fail';
    }
}
?>