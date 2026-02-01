<?php 
session_start();

if (isset($_SESSION['official_id'])) {
    echo '<script>window.location.href = "admin/";</script>';
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <?php include('partials/head.php')?>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
}

.login-container {
    max-width: 450px;
    width: 100%;
}

.login-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    padding: 50px 40px;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-header {
    text-align: center;
    margin-bottom: 40px;
}

.login-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #D52941 0%, #D52941 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(213, 41, 65, 0.3);
}

.login-logo i {
    font-size: 40px;
    color: white;
}

.login-header h2 {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 28px;
}

.login-header p {
    color: #718096;
    font-size: 14px;
}

.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 10px;
    font-size: 14px;
    display: block;
}

.input-group-custom {
    position: relative;
    margin-bottom: 25px;
}

.input-group-custom i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 20px;
}

.form-control {
    width: 100%;
    border-radius: 12px;
    padding: 14px 15px 14px 45px;
    border: 2px solid #e2e8f0;
    font-size: 15px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #D52941;
    box-shadow: 0 0 0 4px rgba(213, 41, 65, 0.1);
    outline: none;
}

.password-toggle {
    position: relative;
}

.password-toggle .toggle-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #a0aec0;
    width:50px;
    user-select: none;
    z-index: 10;
}

.password-toggle .toggle-icon:hover {
    color: #D52941;
}

.btn-login {
    background: linear-gradient(135deg, #D52941 0%, #D52941 100%);
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-weight: 600;
    font-size: 16px;
    width: 100%;
    color: white;
    margin-top: 10px;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(213, 41, 65, 0.4);
    cursor: pointer;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(213, 41, 65, 0.5);
    background: linear-gradient(135deg, #D52941 0%, #D52941 100%);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-message {
    background: #fff5f5;
    border: 1px solid #fc8181;
    border-radius: 10px;
    padding: 12px 15px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.error-message i {
    color: #e53e3e;
    margin-right: 10px;
    font-size: 20px;
}

.error-message span {
    color: #c53030;
    font-size: 14px;
    font-weight: 500;
}

.success-message {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 10px;
    padding: 12px 15px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
}

.success-message i {
    color: #22c55e;
    margin-right: 10px;
    font-size: 20px;
}

.success-message span {
    color: #16a34a;
    font-size: 14px;
    font-weight: 500;
}

.login-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #e2e8f0;
}

.login-footer p {
    color: #718096;
    font-size: 13px;
    margin: 5px 0;
}

.login-footer a {
    color: #D52941;
    text-decoration: none;
    font-weight: 600;
}

.login-footer a:hover {
    text-decoration: underline;
}

.otp-container {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 25px;
}

.otp-input {
    width: 23%;
    padding: 15px 0;
    text-align: center;
    font-size: 28px;
    font-weight: 600;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s;
}

.otp-input:focus {
    border-color: #D52941;
    box-shadow: 0 0 0 4px rgba(213, 41, 65, 0.1);
    outline: none;
}

.otp-input::-webkit-inner-spin-button,
.otp-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.otp-label {
    text-align: center;
    font-size: 14px;
    color: #718096;
    margin-bottom: 15px;
}

input[type="number"] {
    -moz-appearance: textfield;
}

@media (max-width: 480px) {
    .login-card {
        padding: 40px 30px;
    }
    
    .login-header h2 {
        font-size: 24px;
    }
    
    .otp-input {
        font-size: 24px;
        padding: 12px 0;
    }
}
</style>
</head>

<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Password Recovery</h2>
            <p>Reset your director account password</p>
        </div>

        <!-- Error/Success Messages -->
        <div id="admin_passrec_result"></div>

        <!-- Email Section -->
        <form id="emailSection" autocomplete="off">
            <label class="form-label">Email Address</label>
            <div class="input-group-custom">
                <i class="fas fa-envelope"></i>
                <input type="email" class="form-control" name="email" id="admin_passrec_email" 
                       placeholder="Enter your director email" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-paper-plane"></i> Send OTP Code
            </button>
        </form>

        <!-- OTP Section -->
        <div id="otpSection" style="display: none;">
            <div class="otp-label">Enter the 4-digit OTP sent to your email</div>
            <div class="otp-container">
                <input type="number" class="otp-input" id="otp1" min="0" max="9" 
                       oninput="enforceOneDigitAndMove(this, 'otp2')" required>
                <input type="number" class="otp-input" id="otp2" min="0" max="9" 
                       oninput="enforceOneDigitAndMove(this, 'otp3')" required>
                <input type="number" class="otp-input" id="otp3" min="0" max="9" 
                       oninput="enforceOneDigitAndMove(this, 'otp4')" required>
                <input type="number" class="otp-input" id="otp4" min="0" max="9" required>
            </div>

            <!-- New Password Section -->
            <div id="newpassdiv" style="display: none;">
                <form id="passwordForm">
                    <label class="form-label">New Password</label>
                    <div class="input-group-custom password-toggle">
                        <input type="password" class="form-control" id="newPasswordInput" name="password" 
                               placeholder="Enter your new password" required>
                        <i class="fas fa-eye toggle-icon" id="togglePassword2"></i>
                    </div>

                    <button type="submit" class="btn-login" id="changepassbtn">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

        <div class="login-footer" style="display:flex;width: 100%;justify-content: space-between;">
            <p><a href=""></a></p>
            <p><a href="login"><i class="fas fa-sign-in-alt"></i> Admin Login</a></p>
        </div>
    </div>
</div>

<script>
setInterval(() => {
    const emailSection = document.getElementById("emailSection");
    const otpSection = document.getElementById("otpSection");
    const newpassdiv = document.getElementById("newpassdiv");

    const otp1 = document.getElementById('otp1').value || '';
    const otp2 = document.getElementById('otp2').value || '';
    const otp3 = document.getElementById('otp3').value || '';
    const otp4 = document.getElementById('otp4').value || '';
    const fullOtp = otp1 + otp2 + otp3 + otp4;

    if (fullOtp.length === 4) {
        fetch('api/admin_check_otp_response.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'otp=' + encodeURIComponent(fullOtp)
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                emailSection.style.display = "none";
                otpSection.style.display = "block";
                newpassdiv.style.display = "block";
                
                const resultDiv = document.getElementById("admin_passrec_result");
                resultDiv.innerHTML = '<div class="success-message"><i class="fas fa-check-circle"></i><span>OTP verified! Please enter your new password.</span></div>';

                // Disable OTP fields
                document.getElementById('otp1').disabled = true;
                document.getElementById('otp2').disabled = true;
                document.getElementById('otp3').disabled = true;
                document.getElementById('otp4').disabled = true;
            }
        });
    }
}, 100); 

function enforceOneDigitAndMove(input, nextInputId) {
    if (input.value.length > 1) {
        input.value = input.value.slice(0, 1);
    }
    if (input.value.length === 1) {
        const nextInput = document.getElementById(nextInputId);
        if (nextInput) {
            nextInput.focus();
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const passwordForm = document.getElementById('passwordForm');

    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Changing password...');

        const newPassword = document.getElementById('newPasswordInput').value;
        const otp1 = document.getElementById('otp1').value;
        const otp2 = document.getElementById('otp2').value;
        const otp3 = document.getElementById('otp3').value;
        const otp4 = document.getElementById('otp4').value;
        const otptyped = otp1 + otp2 + otp3 + otp4;

        fetch('api/admin_changepass.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'password=' + encodeURIComponent(newPassword) + '&otptyped=' + encodeURIComponent(otptyped)
        })
        .then(response => response.text())
        .then(response => {
            $button.prop('disabled', false).html('<i class="fas fa-key"></i> Change Password');
            console.log("Server response:", response);
            
            if (response.includes("Password changed successfully.")) {
                const resultDiv = document.getElementById("admin_passrec_result");
                resultDiv.innerHTML = '<div class="success-message"><i class="fas fa-check-circle"></i><span>Password changed successfully! Redirecting to login...</span></div>';
                
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                const resultDiv = document.getElementById("admin_passrec_result");
                resultDiv.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i><span>' + response + '</span></div>';
            }
        });
    });

    const form = document.getElementById("emailSection");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending OTP...');

        document.getElementById('otp1').disabled = false;
        document.getElementById('otp2').disabled = false;
        document.getElementById('otp3').disabled = false;
        document.getElementById('otp4').disabled = false;
        document.getElementById('otp1').value = '';
        document.getElementById('otp2').value = '';
        document.getElementById('otp3').value = '';
        document.getElementById('otp4').value = '';
        
        const passwordInput = document.getElementById('newPasswordInput');
        if (passwordInput) {
            passwordInput.value = '';
        }

        fetch("api/admin_passwordrecovery_process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const resultDiv = document.getElementById("admin_passrec_result");
            
            if (data.includes("An OTP has been sent")) {
                resultDiv.innerHTML = '<div class="success-message"><i class="fas fa-check-circle"></i><span>' + data + '</span></div>';
                
                const emailSection = document.getElementById("emailSection");
                const otpSection = document.getElementById("otpSection");

                if (emailSection) emailSection.style.display = "none";
                if (otpSection) otpSection.style.display = "block";
                
                document.getElementById('otp1').focus();
            } else {
                resultDiv.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i><span>' + data + '</span></div>';
            }
            
            $button.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send OTP Code');
        })
        .catch(error => {
            const resultDiv = document.getElementById("admin_passrec_result");
            resultDiv.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i><span>An error occurred. Please try again.</span></div>';
            $button.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send OTP Code');
            console.error("Error:", error);
        });
    });
});

// Password Toggle
document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword2');
    const passwordInput = document.getElementById('newPasswordInput');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
});
</script>

<script src="assets/js/jquery.min.js"></script> 
</body>
</html>