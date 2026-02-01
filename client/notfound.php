<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>POPS - Pyrotechnic Online Permitting System | CSG</title>
<meta name="author" content="CSG - Civil Security Group">
<meta name="description" content="POPS is a streamlined online system designed to assist LGUs and constituents in managing permit processing efficiently, transparently, and digitally.">
<meta name="keywords" content="POPS, permitting, online processing, LGU, digital permits, CSG, governance, public service">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- FAVICON FILES -->
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="../assets/images/logo.png" rel="shortcut icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel=
    "stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter';
        }

        .bg-image {
            background-image: url('../assets/images/bg2.png');
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.1), rgba(30, 30, 60, 0.1));
        }

        .content {
            position: relative;
            z-index: 2;
            color: white;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .error-code {
            font-size: 150px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 1s;
        }

        .error-message {
            font-size: 32px;
            margin-bottom: 15px;
            animation: fadeInUp 1s;
        }

        .error-description {
          margin: auto;
          text-align: center;
            font-size: 18px;
            max-width: 400px;
            width: 100%;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1.2s;
        }

        .btn-home {
            padding: 12px 40px;
            font-size: 16px;
            border-radius: 50px;
            background: linear-gradient(135deg, #D52941 0%, #764ba2 100%);
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 1.4s;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.5);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 100px;
            }
            .error-message {
                font-size: 24px;
            }
            .error-description {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-image">
        <div class="overlay"></div>
        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h1 class="error-code" style="color:#D52941">404</h1>
                        <h2 class="error-message">Page Not Found</h2>
                        <p class="error-description">
                            Oops! The page you are looking for might have been removed,
                            had its name changed, or is temporarily unavailable.
                        </p>
                        <a href="index" class="btn btn-primary btn-home">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>