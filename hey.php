<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start(); 
}


// Get ref from URL
$ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

// If already logged in, redirect to view?ref=
if (isset($_SESSION['logged_admin'])) {

    if (!empty($ref)) {
        header("Location: view?ref=" . urlencode($ref));
    } else {
        header("Location: view?ref=" . urlencode($ref));
    }

    exit();
}

// Database connection
include 'db_conn.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';
    
    if (!empty($input_username) && !empty($input_password)) {
        // Query to check admin credentials
        $stmt = $conn->prepare("SELECT * FROM officials_acc WHERE username = ? AND role_id = 2");
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if ($admin && password_verify($input_password, $admin['password'])) {
            // Login successful
            $_SESSION['logged_admin'] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'role_id' => $admin['role_id']
            ];
             header("Location: view?ref=" . urlencode($ref));
            exit();
        } else {
            $error_message = 'Incorrect username or password';
        }
        $stmt->close();
    } else {
        $error_message = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>POPS - Pyrotechnic Online Permitting System | CSG</title>
<meta name="author" content="CSG - Civil Security Group">
<meta name="description" content="POPS is a streamlined online system designed to assist LGUs and constituents in managing permit processing efficiently, transparently, and digitally.">
<meta name="keywords" content="POPS, permitting, online processing, LGU, digital applications, CSG, governance, public service">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- FAVICON FILES -->
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="assets/images/logo.png" rel="shortcut icon">

        <!-- Styles -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        
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
              background-image: url('assets/images/bg2.png');
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover; /* optional, makes image cover the whole screen */
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
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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
            border-radius: 12px;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e2e8f0;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #D52941;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle .toggle-icon {
            position: absolute;
            right: 15px;
            width: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
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
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #D52941 0%, #D52941 100%);
        }
        
        .btn-login:active {
            transform: translateY(0);
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
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-footer p {
            color: #718096;
            font-size: 13px;
            margin: 0;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="material-icons">admin_panel_settings</i>
                </div>
               <h2>Welcome! Permit First, Fireworks Later 🎇</h2>
                <p>Pyrotechnic Online Permitting System</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="material-icons">error</i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($timeout_message)): ?>
                <div class="error-message" style="background: #fffbeb; border-color: #f59e0b;">
                    <i class="material-icons" style="color: #d97706;">schedule</i>
                    <span style="color: #92400e;"><?php echo htmlspecialchars($timeout_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group-custom">
                        <i class="material-icons">person</i>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group-custom password-toggle">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required
                               autocomplete="current-password">
                        <i class="material-icons toggle-icon" onclick="togglePassword()">visibility_off</i>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 20px;">login</i>
                    Sign In
                </button>
            </form>

          
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.textContent = 'visibility';
            } else {
                passwordField.type = 'password';
                toggleIcon.textContent = 'visibility_off';
            }
        }

        // Focus on username field on load
        window.onload = function() {
            document.getElementById('username').focus();
        };

        // Allow Enter key to submit
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>