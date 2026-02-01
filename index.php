<?php 
session_start();

if (isset($_SESSION['client_id'])) {
    echo '<script>window.location.href = "client/";</script>';
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>POPS - Pyrotechnic Online Permitting System | CSG</title>
<meta name="author" content="CSG - Civil Security Group">
<meta name="description" content="POPS is a streamlined online system designed to assist LGUs and constituents in managing permit processing efficiently, transparently, and digitally.">
<meta name="keywords" content="POPS, permitting, online processing, LGU, digital permits, CSG, governance, public service">



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- FAVICON FILES -->
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="assets/images/logo.png" rel="shortcut icon">


<!-- CSS FILES -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/fonts/iconfonts.css">
<link rel="stylesheet" href="assets/css/plugins.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/responsive.css">
<link rel="stylesheet" href="assets/css/color.css">
<link rel="stylesheet" href="assets/js/aos/aos-master/dist/aos.css">

<style>

.action-icon-box {
    width: 40px;
    height: 40px;
    background-color: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #333;
}


.selection-container {
    display: flex;
    gap: 0px;
    margin-top: 20px;
    flex-wrap: wrap;
  }

  .selection-box {
    flex: 1 1 45%;
    text-align: center;
    cursor: pointer;
    padding: 20px;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s;
  }

  .selection-box img {
    width: 100%;
    max-width: 200px;
    height: auto;
    margin-bottom: 10px;
  } 

  .dtr-btn2 { border:2px solid transparent !important; }
  .dtr-btn2:hover {  
        border: 2px solid #FF0000 !important;}

  .dtr-btn3 { border:2px solid transparent !important; }
  .dtr-btn3:hover {  
        border: 2px solid #0f2c5a !important;}


  .selection-box:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
     background-color: #ED5553;
        color: white;
        border: 2px solid #0f2c5a;
  }

  /* Responsive: stack on small screens */
  @media (max-width: 768px) {
    .selection-box {
      flex: 1 1 100%;
    }
  }


 .dtr-form-field{
  margin-top: 0px !important;
  margin-bottom:10px !important;
 }
/* .dtr-form-column{
  margin-top: 5px !important;
  margin-bottom: 0px !important;
 }*/

  canvas {
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      pointer-events: none; /* allow clicks through canvas */
      background: transparent;
      display: block;
      z-index: 9999;
    }

  * { overflow-x:none }
    /* Fullscreen overlay */
    .overlay2 {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 99999;
      overflow-y: auto;
      padding-bottom: 50px;
    }

    /* Centered modal */
    .modal2 {
      width: 1000px;
      max-width: 95%;
      background-color: #f9fafb;
      border: 10px solid rgba(255, 88, 88, 0.9);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      padding: 20px;
      margin:auto;
      overflow-y: auto;
      margin-top: 70px;
    }

    /* Zigzag layout container */
    .pops-zigzag-container {
      display: flex;
      width: 100%;
      flex-wrap: wrap;
      justify-content: space-evenly;
      margin-bottom: 1.5rem;
    }


    .pops-zigzag-content {
      margin-top: 10px;
      margin-bottom: 10px;
      width: 45%;
      max-width: 800px;
      background-color: #f9f9f9;
      padding: 1rem;
      border-radius: 6px;
    }

    .overlay2 button {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 16px;
      background-color: rgba(255, 88, 88, .9);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .overlay2 button:hover {
      background-color: rgba(200, 50, 50, .9);
    }

    /* Remove border on hover and focus for <select> */
select:hover,
select:focus {
  border: none;
  outline: none;
}

/* Remove border on hover and focus for <input type="date"> */
input[type="date"]:hover,
input[type="date"]:focus {
  border: none;
  outline: none;
}



    .boxcon {
    margin: auto;
    width: 700px;
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    background-color: white;
    padding: 20px;
    padding-top: 30px;
    padding-bottom: 30px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
    margin-top: 30px;
    margin-bottom: 50px;
}


    #overlayCon { display:none; }
    #modalTerms { display:none;}
    #modalRegister { display:none; }
    #modalLogin { display:none; }
    #modalForgotPass { display:none; }
    #modalRegSuccess { display:none; }
    #modalChangePassSuccess { display:none; }
    
    
    
    
    
    select {
  -webkit-appearance: none;  /* Chrome, Safari, Opera */
  -moz-appearance: none;     /* Firefox */
  appearance: none;          /* Standard syntax */
  background: transparent;   /* Optional: Remove the default background */
  padding-right: 20px;       /* Optional: Ensure there's enough space for the text */
}
 input[type="number"] {
      -webkit-appearance: none;
      -moz-appearance: textfield;
      appearance: none;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }





    @media (max-width: 768px) {
      .pops-zigzag-container {
        flex-direction: column;
        align-items: center;
        text-align: left !important;
      }

      .pops-zigzag-content {
        width: 100%;
      }
    }

    @media (max-width: 576px) {
    .text-decoration-none {
      font-size: 13px !important;
       margin-top:3px !important;

    }
    .remember-label {
        margin-top:3px !important;
      font-size: 13px !important;
      margin-left: 20px !important;
    }

    .dtr-pl-50 {
        padding-left: 0px !important;
    }
  }


  </style>


</head>
<body>
  <canvas id="canvas"></canvas>


 <!-- open overlay --> 
<div class="overlay2" id="overlayCon"  onclick="handleOverlayClick(event)">

    <div class="vbox-close" style="color: rgba(255, 88, 88, 0.9); background-color: rgb(22, 22, 23);">×</div>

      <div class="modal2" id="modalRegister" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
"  onclick="event.stopPropagation()">
  <div><img src="assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 10px;margin-bottom: 0px;color:#0f2c5a">Civil Security Group</h4>
  <p style="color:red;">Philippine National Police</p>
 
  <div class="boxcon"> 
 
  
  <div class="container mt-4">
    <div class="row">
          





<div class="dtr-form dtr-form-styled" style="width: 100%;" id="RegisterIntro">
  <p>Which best describes your business?</p>

  <div class="row selection-container justify-content-between">
    
    <!-- Retailer Container -->
    <div class="col-12 col-md-6 mb-4" onclick="openModal('RegRetailerDiv')">
      <div class="selection-box p-3 action-tile text-center d-flex flex-column align-items-center h-100">
        <div class="action-icon-box mb-2">
          <i class="fas fa-store" style="color:#0f2c5a"></i>
        </div>
        <p>Retailer</p>
      </div>
    </div>

    <!-- Manufacturer Container -->
    <div class="col-12 col-md-6 mb-4" onclick="openModal('RegManufacturerDiv');forceShowStep1()">
      <div class="selection-box p-3 action-tile text-center d-flex flex-column align-items-center h-100">
        <div class="action-icon-box mb-2">
          <i class="fas fa-industry" style="color: #0f2c5a"></i>
        </div>
        <p>Manufacturer</p>
      </div>
    </div>

  </div>
</div>




















      <div class="dtr-form dtr-form-styled" style="width: 100%;display: none;" id="RegRetailerDiv">

    <form id="registerRetailer" method="post" autocomplete="off" novalidate>
        <fieldset>

            <h4 style="margin-top: -30px;margin-bottom: 10px;color:#0f2c5a">Registration</h4>
            <!-- Full Name and Contact Number -->
            <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-top: 30px;">
                <div class="dtr-form-column">
                    <div class="dtr-form-field">
                        <span class="dtr-form-subtext">Full Name</span>
                        <input name="full_name" type="text" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                </div>

                <div class="dtr-form-column">
                    <div class="dtr-form-field">
                        <span class="dtr-form-subtext">Contact Number</span>
                        <input name="phone" type="number" placeholder="e.g. 09123456789" required>
                    </div>
                </div>
            </div>

            <!-- Gender and Birthdate -->
            <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-bottom: 0;">
                <div class="dtr-form-column">
                    <div class="dtr-form-field" style="display: flex; flex-direction: column; position: relative; width: 100%; padding: 15px 20px 8px; margin-bottom: 4px; border: 2px solid #e7eaf6; border-radius: 8px; font-size: 17px;">
                        <span class="dtr-form-subtext" for="gender">Gender</span>
                        <select name="gender" id="gender" required style="padding: 0; padding-left:2px; border: none; margin-top: 16px;" required>
                            <option selected hidden>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="dtr-form-column">
                    <div class="dtr-form-field"  style="display:flex;flex-direction: column;position: relative; display: block; width: 100%; padding: 15px 20px; padding-bottom: 8px; margin: 0 0 4px 0; border-width: 2px; border-style: solid; border-radius: 8px; font-size: 17px; font-weight: normal; line-height: 25px !important; vertical-align: top; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;border-color:#e7eaf6">
                        <span class="dtr-form-subtext" for="bdate">Birthdate</span>
                        <input name="bdate" id="bdate" type="date" style="width:100%;border: none;margin-top:14px;padding:0px"  required>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="dtr-form-field">
                <span class="dtr-form-subtext" for="address">Home Address</span>
                <textarea name="address" id="address" rows="3" placeholder="Enter your home address" required></textarea>
            </div>

            <!-- Email and Password -->
            <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-top: 15px;">
                <div class="dtr-form-column">
                    <div class="dtr-form-field">
                        <span class="dtr-form-subtext" for="email">Email address</span>
                        <input name="email" id="email" type="email" placeholder="e.g. juan@gmail.com" required oninput="document.getElementById('reg_result').innerHTML = '';">
                    </div>
                </div>

                <div class="dtr-form-column">
                    <div class="dtr-form-field" style="position: relative;">
                        <span class="dtr-form-subtext" for="password">Password</span>
                        <input id="retailer_password" name="password" type="password" placeholder="Enter your password" required style="padding-right: 40px; width: 100%; box-sizing: border-box;">

                        <div id="retailer_togglePassword" style="position: absolute; right: 15px; top: 30px; width: 24px; height: 24px; cursor: pointer;">
                            <!-- Initial eye icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
                                <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            

            <div class="d-flex justify-content-between mt-3">
        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a" onclick="openModal('RegisterIntro')">
          <i class="bi bi-arrow-left-short"></i> Back
        </button>

        <button type="submit" class="dtr-btn dtr-btn3"  style="">
          Sign Up <i class="bi bi-send-check"></i>
        </button>
      </div>


            <!-- Result Message -->
            <div id="reg_result" style="margin-top: 10px; color: #E75151"></div>

            <!-- Login Link -->
            <p style="color: #0f2c5a; margin-top: 15px; font-size: 14px;">
                Already have an account?
                <span style="color: #E75151; cursor: pointer;" onclick="openLogin()">Login here</span>.
            </p>

        </fieldset>
    </form>
    <script>
 
</script>
</div>
























 <div class="dtr-form dtr-form-styled" style="width: 100%;display: none;" id="RegManufacturerDiv">
          <form id="registerManufacturer" method="post" autocomplete="off" novalidate>
  <fieldset>

    <!-- STEP 1 -->
    <h4 style="margin-top: -30px;margin-bottom: 10px;color:#0f2c5a">Registration</h4>
    <div id="step1" data-step="1" style="margin-top:15px">
      <p>Company & Dealer Information</p>

      <div class="dtr-form-row dtr-form-row-2col clearfix" >
        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Company Name</span>
            <input name="company_name" type="text" placeholder="e.g. ABC Manufacturing Inc." required>
          </div>
        </div>

        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Dealer's Name</span>
            <input name="dealer_name" type="text" placeholder="e.g. Juan Dela Cruz" required>
          </div>
        </div>
      </div>

      <div class="dtr-form-row dtr-form-row-2col clearfix">
        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Contact Number</span>
            <input name="contact_number" type="number" placeholder="e.g. 09123456789" required>
          </div>
        </div>

        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Website (optional)</span>
            <input name="company_website" type="url" placeholder="e.g. manufacturer.com">
          </div>
        </div>
      </div>


      <div class="dtr-form-field">
        <span class="dtr-form-subtext">Company Address</span>
        <textarea name="company_address" rows="3" placeholder="Enter company address" required></textarea>
      </div>

      <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-top:15px">
        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Email Address</span>
            <input name="email" type="email" placeholder="e.g. juan@gmail.com" required oninput="document.getElementById('manufacturer_result').innerHTML = '';">
          </div>
        </div>

        <div class="dtr-form-column">
                    <div class="dtr-form-field" style="position: relative;">
                        <span class="dtr-form-subtext" for="password">Password</span>
                        <input id="manufacturer_password" name="password" type="password" placeholder="Enter your password" required style="padding-right: 40px; width: 100%; box-sizing: border-box;">

                        <div id="manufacturer_togglePassword" style="position: absolute; right: 15px; top: 30px; width: 24px; height: 24px; cursor: pointer;">
                            <!-- Initial eye icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
                                <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z" />
                            </svg>
                        </div>
                    </div>
                </div>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a"  onclick="openModal('RegisterIntro')">
          <i class="bi bi-arrow-left-short" ></i> Back
        </button>

        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a" data-next="step2">
          Continue <i class="bi bi-arrow-right-short"></i>
        </button>
      </div>
    </div>
    <!-- END STEP 1 -->


    <!-- STEP 2 -->
    <div id="step2" data-step="2" style="display:none;margin-top:15px">
      <p>Manufacturer's License</p>

      <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-top:10px">
        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">License No.</span>
            <input name="manufacturer_license_no" type="number" placeholder="e.g. 513264" required>
          </div>
        </div>

        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Serial No.</span>
            <input name="manufacturer_serial_no" type="number" placeholder="e.g. 4665155" required>
          </div>
        </div>
      </div>

      <div class="dtr-form-field" style="display:flex;flex-direction: column;position: relative; display: block; width: 100%; padding: 15px 20px; padding-bottom: 8px; margin: 0 0 4px 0; border-width: 2px; border-style: solid; border-radius: 8px; font-size: 17px; font-weight: normal; line-height: 25px !important; vertical-align: top; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;border-color:#e7eaf6">
        <div class="dtr-form-subtext">Expiry Date</div>
        <input name="manufacturer_expiry_date" type="date" style="width:100%;border: none;margin-top:14px;padding:0px" required>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a" data-back="step1">
          <i class="bi bi-arrow-left-short" ></i> Back
        </button>
        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a" data-next="step3">
          Continue <i class="bi bi-arrow-right-short"></i>
        </button>
      </div>
    </div>
    <!-- END STEP 2 -->


    <!-- STEP 3 -->
    <div id="step3" data-step="3" style="display:none;margin-top:15px">
      <p>Dealer's License</p>

      <div class="dtr-form-row dtr-form-row-2col clearfix" style="margin-top:10px">
        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">License No.</span>
            <input name="dealer_license_no" type="number" placeholder="e.g. 513264" required>
          </div>
        </div>

        <div class="dtr-form-column">
          <div class="dtr-form-field">
            <span class="dtr-form-subtext">Serial No.</span>
            <input name="dealer_serial_no" type="number" placeholder="e.g. 4665155" required>
          </div>
        </div>
      </div>

      <div class="dtr-form-field" style="display:flex;flex-direction: column;position: relative; display: block; width: 100%; padding: 15px 20px; padding-bottom: 8px; margin: 0 0 4px 0; border-width: 2px; border-style: solid; border-radius: 8px; font-size: 17px; font-weight: normal; line-height: 25px !important; vertical-align: top; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;border-color:#e7eaf6">
        <div class="dtr-form-subtext">Expiry Date</div>
        <input name="dealer_expiry_date" type="date" style="width:100%;border: none;margin-top:14px;padding:0px" required>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <button type="button" class="dtr-btn dtr-btn2"  style="background: #0f2c5a" data-back="step2">
          <i class="bi bi-arrow-left-short" ></i> Back
        </button>
        <button type="submit" class="dtr-btn btn-sm dtr-btn3">
          Sign Up <i class="bi bi-send-check"></i>
        </button>
      </div>
    </div>
    <!-- END STEP 3 -->


    <div id="manufacturer_result" style="margin-top: 10px; color: #E75151"></div>
  </fieldset>
</form>

<script>
  (() => {

    const form = document.getElementById('registerManufacturer');

    // Show only the step indicated by id, hide others
    function showStep(stepId) {
      const steps = form.querySelectorAll('[data-step]');
      steps.forEach(step => {
        step.style.display = step.id === stepId ? 'block' : 'none';
      });
    }

    // Validate required inputs in the current step
    function validateStep(stepElement) {
      const requiredFields = stepElement.querySelectorAll('[required]');
      for (const field of requiredFields) {
        if (!field.checkValidity()) {
          field.reportValidity();
          return false;
        }
      }
      return true;
    }

    // Event delegation for buttons with data-next and data-back
    form.addEventListener('click', e => {
      if (e.target.matches('button[data-next], button[data-next] *')) {
        // Find closest button element in case icon or child clicked
        const btn = e.target.closest('button[data-next]');
        const nextStepId = btn.getAttribute('data-next');
        const currentStep = btn.closest('[data-step]');
        if (!validateStep(currentStep)) return;
        showStep(nextStepId);
      }
      else if (e.target.matches('button[data-back], button[data-back] *')) {
        const btn = e.target.closest('button[data-back]');
        const backStepId = btn.getAttribute('data-back');
        showStep(backStepId);
      }
    });

    function forceShowStep1() {
  showStep('step1');
}
    // Initialize first step visibility
    showStep('step1');

















document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerManufacturer');
    const submitButton = form.querySelector('button[type="submit"]'); // Assuming your button is inside the form

    // Save the initial button text and icon
    const initialButtonText = submitButton.textContent;  // Save text part of the button
    const initialButtonHTML = submitButton.innerHTML;   // Save the entire inner HTML including the icon

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Flag to track validation status
        let isValid = true;
        const formData = new FormData(form);
        const requiredFields = form.querySelectorAll('[required]'); // Select all required fields

        // Loop through required fields and check if any is empty
        requiredFields.forEach(field => {
            if (!formData.get(field.name)) {
                isValid = false;
                // Trigger the browser's built-in validation message for required fields
                field.reportValidity(); // This triggers the default "Please fill out this field" message
            }
        });

        // If validation fails, stop form submission
        if (!isValid) {
            submitButton.disabled = false;
            submitButton.innerHTML = initialButtonHTML; // Restore the initial button text and icon
            return; // Exit early if not valid
        }

        // Change button text and disable it for processing
        submitButton.disabled = true;
        submitButton.innerHTML = `Processing..`;  // Change to processing state with a spinner icon

        // If the form is valid, proceed with submitting
        const email = formData.get('email');  // Assuming your email field has a name="email" attribute

        const options = { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long',
            timeZone: 'Asia/Manila' 
        };

        const currentTime = new Intl.DateTimeFormat('en-PH', options).format(new Date());
        formData.append('current_time', currentTime);

        // Perform AJAX request to submit the form data
        fetch('register_manufacturer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayRegSuccess();
                celebrateConfetti('modalRegSuccess_confetti');

                // Display the email in the reg_email element
                // $('#reg_email').text(email);
                form.reset();
                document.getElementById('manufacturer_result').textContent = '';

                
            } else {
                document.getElementById('manufacturer_result').textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during registration. Please try again.');
        })
        .finally(() => {
            // Enable the button and restore the original button text and icon after processing
            submitButton.disabled = false;
            submitButton.innerHTML = initialButtonHTML; // Restore the initial button text and icon
        });
    });
});







  })();



  
</script>
           
 </div>
    


    </div>
  </div>
</div>

</div>










  <div class="modal2" id="modalTerms" onclick="event.stopPropagation()">
    <h2>Terms of Agreement</h2>
    <p>This notice is specific to the contents of the <strong>Pyrotechnic Online Permitting System (POPS)</strong> Website, which is owned and operated by the Civil Security Group (CSG) of the Philippine National Police. It is provided as a service to individuals and businesses applying for licenses and permits related to pyrotechnic materials (“Services”). By accessing or using the POPS Website or any part of it, you agree to be legally bound by the following Terms of Use.</p>

    <!-- Zigzag Terms Sections -->
    <div class="pops-zigzag-container">
      <div class="pops-zigzag-content">
  <h4>1. Any falsification of documents is subject to legal sanctions and automatic disapproval.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>2. Applicants must provide accurate and truthful information in the application form.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>3. All supporting documents submitted must be valid and verifiable.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>4. POPS reserves the right to reject incomplete or incorrect applications.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>5. Applications are subject to evaluation by authorized personnel only.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>6. The client acknowledges that submission does not guarantee permit approval.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>7. clients shall not share their login credentials to unauthorized individuals.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>8. POPS shall maintain confidentiality of the client’s submitted data.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>9. Processing time may vary depending on the completeness and accuracy of submitted requirements.</h4>
</div>

<div class="pops-zigzag-content">
  <h4>10. Notifications and status updates will be sent through the registered email address.</h4>
</div>

     

    </div>

   

    <p class="text-center">By clicking button below, you acknowledge that you have read, understood, and agree to these terms.</p>

    <div style="display: flex; justify-content: center; margin-top: 20px;">
         <button style="background:transparent;color:Black" onclick="document.getElementById('overlayCon').style.display='none'">
   No, Thanks
  </button>
  <button onclick="openRegister();openModal('RegisterIntro')">
    Yes, I Accept
  </button>
</div>

  </div>







<div class="modal2" id="modalLogin" style="
  flex-direction: column;
  justify-content: center;

  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
"  onclick="event.stopPropagation()">
  <div><img src="assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 10px;margin-bottom: 0px;color:#0f2c5a">Civil Security Group</h4>
  <p style="color:red;">Philippine National Police</p>
 
  <div class="boxcon" style="width: 500px;"> 
  <h4 style="margin-top: 10px;margin-bottom: 0px;color:#0f2c5a">Sign in to your account</h4>
  
  <div class="container mt-4">
    <div class="row">
        
 <div class="dtr-form dtr-form-styled" style="width:100%">
                        <form id="loginForm" method="post" autocomplete="off">
                            <fieldset>

  

   
        
         <div class="dtr-form-field">
            <div class="dtr-form-field">
                <span class="dtr-form-subtext">Email</span>
                <input required name="emailuser" type="email" placeholder="Enter your Email"  oninput="document.getElementById('loginForm_result').innerHTML = '';">
            </div>
        </div>

        <!-- Password Field -->
  <div class="dtr-form-field" style="margin-top:10px; position: relative;">
  <span class="dtr-form-subtext">Password</span>
  <input id="login_password" required name="password" type="password" placeholder="Enter your password"  style="padding-right: 40px; width: 100%; box-sizing: border-box;" oninput="document.getElementById('loginForm_result').innerHTML = '';" >

  <div id="login_togglePassword" style="position: absolute; right: 15px; top: 30px; width: 24px; height: 24px; cursor: pointer;">
    <!-- Open eye SVG initially -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
      <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>
    </svg>
  </div>
</div>





       <div class="d-flex justify-content-between align-items-center mb-3">
  <div class="form-check" style="display:flex;padding-top: 10px;padding-left: 10px;">
    <input class="form-check-input " name="remember" type="checkbox" id="rememberMe" style="margin: auto; width: 1em; height: 1em;">

    <label class="form-check-label remember-label" for="rememberMe" style="margin-left: 25px; font-size:15px;color:#0F2C5A;font-weight: lighter;">
      Remember me
    </label>
  </div>
  
  <p style="font-size: 15px;color: #0F2C5A;cursor: pointer;padding-top: 10px;padding-right: 10px;" class="text-decoration-none" onclick="openForgotPass()">Forgot Password?</p>
</div>


   
    <!-- Submit Button -->
    <div class="text-center">
        <button class="dtr-btn  w-100" type="submit" style="background: #0f2c5a">
           Sign In
        </button>
    </div>


    <div id="loginForm_result" style="margin-top:10px;color:#E75151"></div>



    <p style="color:#0f2c5a;margin-top: 15px;font-size: 14px;">Don't have an account? <span style="color:#E75151;cursor: pointer;" onclick="openTerms()">Register here</span>.</p>


</fieldset>

                        </form>
                    </div>
    
<script>
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    



    let allValid = true;
    const formElements = this.querySelectorAll('input[required]');

    // Loop through all required input fields
    formElements.forEach(function(input) {
        if (!input.checkValidity()) {
            allValid = false;
            input.reportValidity();
            return false; // Stop the loop if a field is invalid
        }
    });

    // Proceed if all fields are valid
    if (allValid) {
        const formData = new FormData(this);

        fetch('login_client.php', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Network response was not OK");
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'client/';
            } else {
                document.getElementById('loginForm_result').textContent = data.message;
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
            document.getElementById('loginForm_result').textContent = 'Something went wrong. Please try again.';
        });
    } else {

    const emailuser = document.querySelector('input[name="emailuser"]');
    const password = document.querySelector('input[name="password"]');

    if (!emailuser.value.trim()) {
        document.querySelector('input[name="emailuser"]').focus();
        return; 
    }

    if (!password.value.trim()) {
        document.querySelector('input[name="password"]').focus();
        return; 
    }


      
     
    }
});

// Remember Me: auto-fill Email
window.addEventListener('DOMContentLoaded', () => {
    const rememberemailuser = getCookie('remember_emailuser');
    if (rememberemailuser) {
        document.querySelector('input[name="emailuser"]').value = rememberemailuser;
        document.getElementById('rememberMe').checked = true;
    }
});

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

</script>

    </div>
  </div>
</div>

</div>

























<div class="modal2" id="modalForgotPass" style="
  flex-direction: column;
  justify-content: center;

  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
"  onclick="event.stopPropagation()">
  <div><img src="assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 10px;margin-bottom: 0px;color:#0f2c5a">Civil Security Group</h4>
  <p style="color:red;">Philippine National Police</p>
 
  <div class="boxcon" style="width: 500px;"> 
  <h4 style="margin-top: 10px;margin-bottom: 0px;color:#0f2c5a">Password Recovery</h4>
  
  <div class="container mt-4">
    <div class="row">
        
 <div class="dtr-form dtr-form-styled" style="width:100%">
                  
<form method="post" autocomplete="off" id="emailSection">
  <fieldset>

    <!-- Email Section -->

    <div class="dtr-form-field">
      <span class="dtr-form-subtext">Email</span>
      <input name="email" id="passrec_email" type="email" placeholder="Enter your email" required>
    </div>

    <div class="text-center">
      <button class="dtr-btn w-100" type="submit" style="background: #0f2c5a">
        Send OTP Code
      </button>
    </div>
 

  

  </fieldset>
</form>






      
<div id="otpSection" style="display: none;">
  <fieldset>



  <!-- OTP Section -->
  <div >
    <span style="display: block; text-align: left;font-size: .765em;">Enter OTP Code</span>
    <div class="dtr-form-field" style="display:flex;justify-content: space-between;">
    <input id="otp1" type="number" min="0" max="9" style="width:23%;padding-top: 15px; text-align: center;font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp2')">
<input id="otp2" type="number" min="0" max="9" style="width:23%;padding-top: 15px; text-align: center;font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp3')">
<input id="otp3" type="number" min="0" max="9" style="width:23%;padding-top: 15px; text-align: center;font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp4')">
<input id="otp4" type="number" min="0" max="9" style="width:23%;padding-top: 15px; text-align: center;font-size: 30px;" required>

    </div>



    <div id="newpassdiv" style="display: none;">
      <form id="passwordForm">
            <div class="dtr-form-field" style="margin-top:10px;position: relative;">
              <span class="dtr-form-subtext">New Password</span>
              <input id="newPasswordInput" name="password" type="password" placeholder="Enter your new password" required>

                <div id="togglePassword2" style="position: absolute; right: 15px; top: 30px; width: 24px; height: 24px; cursor: pointer;">
    <!-- Open eye SVG initially -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
      <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>
    </svg>
  </div>

            </div>

            <div class="text-center">
              <button class="dtr-btn w-100" type="submit" style="background: #0f2c5a" id="changepassbtn">
                Change Password
              </button>
            </div>
          </form>
    </div>

  </div>


  


  </fieldset>
</div>

  <div id="passrec_result" style="margin-top:10px;color:#E75151"></div>



<script>



let otpChecked = false; // Add flag to prevent multiple checks

setInterval(() => {
  const emailSection = document.getElementById("emailSection");
  const otpSection = document.getElementById("otpSection");
  const newpassdiv = document.getElementById("newpassdiv");

  const otp1 = document.getElementById('otp1').value || '';
  const otp2 = document.getElementById('otp2').value || '';
  const otp3 = document.getElementById('otp3').value || '';
  const otp4 = document.getElementById('otp4').value || '';
  const fullOtp = otp1 + otp2 + otp3 + otp4;

  // Only check if we have 4 digits AND haven't checked yet
  if (fullOtp.length === 4 && !otpChecked) {
    otpChecked = true; // Set flag immediately to prevent duplicate requests
    
    fetch('check_otp_response.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'otp=' + encodeURIComponent(fullOtp)
    })
    .then(response => response.text())
    .then(result => {
        console.log(result)
      if (result.trim() === 'success') {
        emailSection.style.display = "none";
        otpSection.style.display = "block";
        newpassdiv.style.display = "block";

        document.getElementById("passrec_result").innerHTML = '';

        // Disable OTP fields
        document.getElementById('otp1').disabled = true;
        document.getElementById('otp2').disabled = true;
        document.getElementById('otp3').disabled = true;
        document.getElementById('otp4').disabled = true;
      } else {
        // Reset flag if verification failed so user can try again
        otpChecked = false;
        document.getElementById("passrec_result").innerHTML = 'Invalid OTP code. Please try again.';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      otpChecked = false; // Reset on error
      document.getElementById("passrec_result").innerHTML = 'An error occurred. Please try again.';
    });
  }
  
  // Reset the flag if user deletes a digit (OTP is no longer 4 digits)
  if (fullOtp.length < 4) {
    otpChecked = false;
  }
}, 100);




    function enforceOneDigitAndMove(input, nextInputId) {
    // Enforce only one digit
    if (input.value.length > 1) {
      input.value = input.value.slice(0, 1);  // Keep only the first digit
    }

    // Move to the next input if the current input has a value
    if (input.value.length === 1) {
      const nextInput = document.getElementById(nextInputId);
      if (nextInput) {
        nextInput.focus();  // Move focus to the next input field
      }
    }
  }


document.addEventListener("DOMContentLoaded", function () {




   const passwordForm = document.getElementById('passwordForm');

        passwordForm.addEventListener('submit', function(e) {

          e.preventDefault();

          const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true).text('Changing your password..');

          const newPassword = document.getElementById('newPasswordInput').value;
          const otp1 = document.getElementById('otp1').value;
    const otp2 = document.getElementById('otp2').value;
    const otp3 = document.getElementById('otp3').value;
    const otp4 = document.getElementById('otp4').value;

    const otptyped = otp1 + otp2 + otp3 + otp4;


          fetch('changepass.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'password=' + encodeURIComponent(newPassword) + '&otptyped=' + encodeURIComponent(otptyped)
    
          })
          .then(response => response.text())
          .then(response => {

            $button.prop('disabled', false).text('Change Password');

            console.log("Server response:", response);
            if (response.includes("Password changed successfully.")) {
                          displayChangePassSuccess();
                          celebrateConfetti('modalChangePassSuccess_confetti');
            const emailSection = document.getElementById("emailSection");
  const otpSection = document.getElementById("otpSection");
  const newpassdiv = document.getElementById("newpassdiv");
  emailSection.style.display = "none";
        otpSection.style.display = "none";
        newpassdiv.style.display = "none";
            } else {
               document.getElementById("passrec_result").innerHTML = response;
            }

           



          });
        });


 

    const form = document.getElementById("emailSection");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true).text('Sending OTP Code..');

        document.getElementById('otp1').disabled = false;
        document.getElementById('otp2').disabled = false;
        document.getElementById('otp3').disabled = false;
        document.getElementById('otp4').disabled = false;
        document.getElementById('otp1').value = '';
        document.getElementById('otp2').value = '';
        document.getElementById('otp3').value = '';
        document.getElementById('otp4').value = '';
        document.getElementById('newPasswordInput').value = '';

        


        fetch("passwordrecovery_process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("passrec_result").innerHTML = data;
            $button.prop('disabled', false).text('Send OTP Code');

            if (data.includes("An OTP has been sent")) {
                const emailSection = document.getElementById("emailSection");
                const otpSection = document.getElementById("otpSection");

                if (emailSection) emailSection.style.display = "none";
                if (otpSection) otpSection.style.display = "block";

            }


        })
        .catch(error => {
            document.getElementById("passrec_result").innerHTML = "An error occurred. Please try again.";
            $button.prop('disabled', false).text('Send OTP Code');
            console.error("Error:", error);
        });
    });
});
</script>



                    </div>
    


    </div>
  </div>
</div>

</div>



















<div class="modal2" id="modalChangePassSuccess" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
"  onclick="event.stopPropagation()">
  
  
  <div class="container mt-4">
    <div class="row">
        
 <div class="dtr-form dtr-form-styled" style="width:100%">

  
 
  <div class="boxcon" style="width: 500px;" id="modalChangePassSuccess_confetti"> 
      <div><img src="assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 15px;margin-bottom: 0px;color:#0f2c5a">Password Changed Successfully!</h4>
  <p style="color:red;">CSG | Philippine National Police</p>



                    <p style="text-align: left;">
  Your password has been updated successfully. Please use your new password the next time you log in.
</p>
<p style="text-align: left;">
  For your security, make sure to keep your password confidential and avoid sharing it with others. Remember to update your password regularly to protect your account.
</p>
<p style="text-align: left;">Thank you for using the Pyrotechnic Online Permitting System (POPS). We appreciate your cooperation.</p>

      
                    </div>
    


    </div>
  </div>
</div>

</div>
















<div class="modal2" id="modalRegSuccess" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
"  onclick="event.stopPropagation()">
  
  
  <div class="container mt-4">
    <div class="row">
        
 <div class="dtr-form dtr-form-styled" style="width:100%">

  
 
  <div class="boxcon" style="width: 500px;" id="modalRegSuccess_confetti"> 
      <div>

        <img src="assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 15px;margin-bottom: 0px;color:#0f2c5a">Successfully Registered!</h4>
  <p style="color:red;">CSG | Philippine National Police</p>



                   <p style="text-align: left;">
  Your account has been successfully registered.

</p>
<p style="text-align: left;">
  Keep your Email and credentials safe.
</p>
<p style="text-align: left;">Thank you for using the Pyrotechnic Online Permitting System (POPS). We appreciate your cooperation.</p>


      
                    </div>
    


    </div>
  </div>
</div>

</div>



</div>
  <!-- close overlay --> 



<div id="dtr-wrapper" class="clearfix"> 
    
    <!-- preloader starts -->
    <div class="dtr-preloader " style="background-color: rgba(255, 88, 88, .9) !important;">
        <div class="dtr-preloader-inner">
            <img src="assets/images/logo.png" style="height: 250px;width: 250px;">
        </div>
    </div>
    <!-- preloader ends --> 
    
    <!-- Small Devices Header 
============================================= -->
    <div class="dtr-responsive-header fixed-top">
        <div class="container"> 
            
            <!-- small devices logo --> 
            <a href="index.html"><img src="assets/images/logo.png"  style="height: 80px;width:80px" alt="logo" ></a> 
            <!-- small devices logo ends --> 
            
            <!-- menu button -->
            <button id="dtr-menu-button" class="dtr-hamburger" type="button" style="margin-right: 10px !important;"><span class="dtr-hamburger-lines-wrapper"><span class="dtr-hamburger-lines"></span></span></button>
        </div>
        <div class="dtr-responsive-header-menu"></div>
    </div>
    <!-- Small Devices Header ends 
============================================= --> 
    
    <!-- Header 
============================================= -->
    <header id="dtr-header-global" class="fixed-top trans-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between"> 
                
                <!-- header left starts -->
                <div class="dtr-header-left"> 
                    
                    <!-- logo --> 
                    <a class="logo-default dtr-scroll-link" href="#home"><img src="assets/images/logo.png"  style="height: 80px;width:80px" alt="logo"></a> 
                    
                    <!-- logo on scroll --> 
                    <a class="logo-alt dtr-scroll-link" href="#home"><img src="assets/images/logo.png"  style="height: 80px;width:80px" alt="logo"></a> 
                    <!-- logo on scroll ends --> 
                    
                </div>
                <!-- header left ends --> 
                
                <!-- menu starts-->
               <div class="dtr-header-right ml-auto">
    <div class="main-navigation dtr-menu-light">
        <ul class="sf-menu dtr-scrollspy dtr-nav light-nav-on-load dark-nav-on-scroll" style="overflow-y: auto;">
            <li><a class="nav-link" href="#home">Home</a></li>
            <li><a class="nav-link" href="#about">About Us</a></li>
            <li><a class="nav-link" href="#howitworks">How It Works?</a></li>
            <li><a class="nav-link" href="#contact">Contact</a></li>
            <li  onclick="openTerms()"><a class="nav-link" style="cursor: pointer;" onclick="openTerms()">Register</a></li>
            <li  onclick="openLogin()"><a class="nav-link"  style="cursor: pointer;"  onclick="openLogin()">Login</a></li>

            



        </ul>
    </div>
</div>

                <!-- menu ends --> 
                
                <!-- header button starts --> 
              
                <!-- header button ends --> 
                
            </div>
        </div>
    </header>
    <!-- header ends
================================================== --> 
    
    <!-- == main content area starts == -->
    <div id="dtr-main-content"> 
        
        <!-- hero section starts
================================================== -->
        <section id="home" class="dtr-section dtr-section-with-bg dtr-hero-section-top-padding bg-blue" style="background-image: url(assets/images/bg.png);"> 
            
            <!-- wrapping div for background bottom curve stripes image - easy to change color - no need to edit image - refer help doc -->
            <div class="dtr-bottom-shape-img" style="background-image: url(assets/images/hero-bottom.svg);">
                <div class="container"> 
                    
                    <!--===== row 1 starts =====-->
                    <div class="row dtr-pb-100"> 
                        
                        <!-- column 1 starts -->
                        <div class="col-12 text-center"> 

                            <!-- intro text --><br>
                            <h2 class="color-white" data-aos="fade-up">Pyrotechnic Online Permitting System</h2>
                            <p class="color-white-muted"  data-aos="fade-up" data-aos-delay="500">Streamlining Fireworks Permits Under the Philippine National Police – Civil Security Group</p>

                             <div style="display: flex;justify-content: center;" class=" dtr-mt-40">
                            <button class="dtr-btn btn-red dtr-scroll-link"  data-aos="fade-in-up" data-aos-delay="1000" onclick="openTerms()">Register</button> 
                            <button class="dtr-btn  dtr-scroll-link"   data-aos="fade-in-up" data-aos-delay="1000" style="background: transparent;border: none;color: white;" onclick="openLogin()">Login</button> 
                            </div>
                           
                            
                           
                            
                        </div>
                        <!-- column 1 ends --> 
                        
                    </div>
                    <!--===== row 1 ends =====--> 
                    
                </div>
            </div>
        </section>
        <!-- hero section ends
================================================== --> 
        
        <!-- features section starts
================================================== -->
        <section id="features" class="dtr-section dtr-pt-150 dtr-pb-100" style="background: #f9fafb">
  <div class="container"> 
    
    <!-- heading starts -->
    <div class="dtr-styled-heading text-center">
      <h2 data-aos="fade-up" data-aos-delay="1200">For Firecracker Vendors and Applicants</h2>
      <p data-aos="fade-out"  data-aos-delay="1200">POPS makes the permit process faster, safer, and more accessible for legal firecracker vendors.</p>
    </div>
    <!-- heading ends --> 
    
    <!--== row starts ==-->
    <div class="row dtr-pt-10"> 
      
      <!-- column 1 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up"> 
        
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Online registration and application process</h4>
        <p>Apply for permits directly on the website without visiting the office. All processes are digitized.</p>
      </div>
      <!-- column 1 ends --> 
      
      <!-- column 2 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up" data-aos-delay="500"> 
        
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Submit documents anytime, anywhere</h4>
        <p>Upload necessary requirements online securely from any device and location.</p>
      </div>
      <!-- column 2 ends --> 
      
      <!-- column 3 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up" data-aos-delay="1000"> 
        
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Track application status in real-time</h4>
        <p>Monitor the progress of your application with live updates and status tracking.</p>
      </div>
      <!-- column 3 ends --> 

    </div>

    <!--== second row starts ==-->
    <div class="row dtr-pt-30">
      
      <!-- column 4 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up" data-aos-delay="1200"> 
       
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Safe and secure document handling</h4>
        <p>Uploaded documents are encrypted and validated to ensure authenticity and security.</p>
      </div>
      <!-- column 4 ends --> 

      <!-- column 5 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up" data-aos-delay="1400"> 
        
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Chat support with POPSY chatbot</h4>
        <p>Get real-time help and answers to your questions 24/7 using the built-in AI assistant.</p>
      </div>
      <!-- column 5 ends --> 

      <!-- column 6 starts -->
      <div class="col-12 col-md-4 dtr-img-feature"  data-aos="fade-up" data-aos-delay="1600"> 
        
        <h4 class="dtr-img-feature-heading"><span style="color:#14EA0E">✔</span> Faster approvals with digital verification</h4>
        <p>The system automatically checks for document authenticity to reduce processing delays.</p>
      </div>
      <!-- column 6 ends --> 

    </div>
    <!--== second row ends ==-->

  </div>
</section>

        <!-- features section ends
================================================== --> 
        
    
        
        <!-- about section starts
================================================== -->
        <section id="about" class="dtr-section dtr-pt-100 dtr-pb-150 dtr-section-with-bg" style="background-image: url(assets/images/bg.png);">
    <!-- blue overlay -->
    <div class="dtr-overlay dtr-overlay-red"></div>
    <div class="container dtr-overlayContent">
        
        <!-- row starts -->
        <div class="row">
            
            <!-- column 1 starts -->
            <div class="col-12 col-md-6 col-lg-5"  style="z-index: 1;" data-aos="fade-right">
                <h2 class="color-white">Pyrotechnic Online Permitting System (POPS)</h2>
                <p class="color-white-muted">
                    The <strong>Pyrotechnic Online Permitting System (POPS)</strong> is a web-based system developed to streamline the application and registration process for firecracker and pyrotechnic vendors and applicants. It enables clients to register, submit requirements, and track their permit status online — offering convenience, efficiency, and transparency. <br><br>
                    This system operates <strong>under the supervision of the Civil Security Group (CSG)</strong> and aims to enhance public safety compliance through digital transformation.
                </p>

                <!--== accordion starts ==-->
                <div class="dtr-mt-30 color-white">
                    <div class="dtr-accordion accordion" id="accord-index1">

                        <!-- accordion tab 1 -->
                        <div class="card">
                            <div class="card-header" id="accord-index1-heading1">
                                <h4>
                                    <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse1" aria-expanded="false" aria-controls="accord-index1-collapse1">
                                        How to get started
                                    </button>
                                </h4>
                            </div>
                            <div id="accord-index1-collapse1" class="collapse" aria-labelledby="accord-index1-heading1" data-parent="#accord-index1">
                                <div class="card-body">
                                    Click on the “Register” button on the homepage, fill out the registration form, and upload the required documents. Once submitted, your application will be reviewed and tracked through your dashboard.
                                </div>
                            </div>
                        </div>

                        <!-- accordion tab 2 -->
                        <div class="card">
                            <div class="card-header" id="accord-index1-heading2">
                                <h4>
                                    <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse2" aria-expanded="false" aria-controls="accord-index1-collapse2">
                                        What does POPS provide?
                                    </button>
                                </h4>
                            </div>
                            <div id="accord-index1-collapse2" class="collapse" aria-labelledby="accord-index1-heading2" data-parent="#accord-index1">
                                <div class="card-body">
                                    POPS offers a full suite of permitting services including online application, secure document upload, real-time status tracking, and automated notifications. It also provides LGUs with centralized monitoring and reporting tools.
                                </div>
                            </div>
                        </div>

                        <!-- accordion tab 3 -->
                        <div class="card">
                            <div class="card-header" id="accord-index1-heading3">
                                <h4>
                                    <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse3" aria-expanded="false" aria-controls="accord-index1-collapse3">
                                        Is support available?
                                    </button>
                                </h4>
                            </div>
                            <div id="accord-index1-collapse3" class="collapse" aria-labelledby="accord-index1-heading3" data-parent="#accord-index1">
                                <div class="card-body">
                                    Yes. LGU personnel are trained to support applicants throughout the process. Additionally, the system includes a help section, FAQs, and contact options for technical or permit-related questions.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--== accordion ends ==-->

                <p class="color-white-muted dtr-mt-40">
                    “With POPS, we ensure a safer celebration season by empowering local governments and the public through technology.”
                </p>

                <!-- author box -->
                <div class="dtr-author-box dtr-mt-20">
                    <img src="assets/images/logo.png" width="60" height="60" alt="image" class="dtr-author-img border-red">
                    <div class="dtr-author-content">
                        <h6 class="color-white">Municipal LGU Administrator</h6>
                        <p class="color-white-muted">POPS Project Lead</p>
                    </div>
                </div>
                <!-- author box ends -->

            </div>
            <!-- column 1 ends -->

            <!-- column 2 -->
            <div class="col-12 col-md-6 col-lg-6 offset-lg-1 small-device-space"  style="z-index: 1;"  data-aos="fade-out">
                <img src="assets/images/logo.png" alt="image">
            </div>
            <!-- column 2 ends -->

        </div>
        <!-- row ends -->

    </div>
</section>

        <!-- about section ends
================================================== --> 
    
        
        <!-- service boxes section starts
================================================== -->
     <section id="howitworks" class="dtr-pt-100 dtr-pb-70" style="background: #f9fafb">
    <div class="container">

        <!-- heading starts -->
        <div class="dtr-styled-heading text-center">
            <h2 data-aos="fade-right">How It Works?</h2>
            <p data-aos="fade-out">The Pyrotechnic Online Permitting System (POPS) simplifies the process of applying, verifying, and approving pyrotechnic-related permits under the supervision of CSG.</p>
        </div>
        <!-- heading ends -->

        <!--== row starts ==-->
        <div class="row justify-content-center">

            <!-- box 1 starts -->
            <div class="col-12 col-sm-6 col-lg-4" data-aos="fade-up"  data-aos-delay="500">
                <div class="dtr-servicebox">
                    <span class="dtr-servicebox-number">1</span>
                    <span class="dtr-servicebox-img color-blue"><i class="icon-user" style="color: rgba(255, 88, 88, .9);"></i></span>
                    <h4>Client Registration</h4>
                    <p>Applicants must register and create an account using their valid credentials through the POPS portal.</p>
                </div>
            </div>
            <!-- box 1 ends -->

            <!-- box 2 starts -->
            <div class="col-12 col-sm-6 col-lg-4"  data-aos="fade-up"  data-aos-delay="1000">
                <div class="dtr-servicebox">
                    <span class="dtr-servicebox-number">2</span>
                    <span class="dtr-servicebox-img color-blue"><i class="icon-upload" style="color: rgba(255, 88, 88, .9);"></i></span>
                    <h4>Online Application</h4>
                    <p>Applicants fill out and submit the required forms and documents online depending on the type of permit.</p>
                </div>
            </div>
            <!-- box 2 ends -->

            <!-- box 3 starts -->
            <div class="col-12 col-sm-6 col-lg-4"  data-aos="fade-up"  data-aos-delay="1200">
                <div class="dtr-servicebox">
                    <span class="dtr-servicebox-number">3</span>
                    <span class="dtr-servicebox-img color-blue"><i class="icon-search" style="color: rgba(255, 88, 88, .9);"></i></span>
                    <h4>Document Review</h4>
                    <p>CSG personnel review the submissions for completeness, accuracy, and compliance with regulatory requirements.</p>
                </div>
            </div>
            <!-- box 3 ends -->

            <!-- box 4 starts -->
            <div class="col-12 col-sm-6 col-lg-4"  data-aos="fade-up"  data-aos-delay="1400">
                <div class="dtr-servicebox">
                    <span class="dtr-servicebox-number">4</span>
                    <span class="dtr-servicebox-img color-blue"><i class="icon-check" style="color: rgba(255, 88, 88, .9);"></i></span>
                    <h4>Approval Process</h4>
                    <p>Once verified and approved, the system updates the status and issues the permit electronically.</p>
                </div>
            </div>
            <!-- box 4 ends -->

            <!-- box 5 starts -->
            <div class="col-12 col-sm-6 col-lg-4"  data-aos="fade-up"  data-aos-delay="1600">
                <div class="dtr-servicebox">
                    <span class="dtr-servicebox-number">5</span>
                    <span class="dtr-servicebox-img color-blue"><i class="icon-envelope" style="color: rgba(255, 88, 88, .9);"></i></span>
                    <h4>Notification & Tracking</h4>
                    <p>Clients receive updates via email and can track the status of their applications through the system dashboard.</p>
                </div>
            </div>
            <!-- box 5 ends -->

        </div>
        <!--== row ends ==-->

    </div>
</section>

        <!-- service boxes section ends
================================================== --> 
  
        
        <!-- blog section starts
================================================== -->
       <section id="FAQs" class="dtr-section dtr-py-100" style="background:#f9fafb">
    <div class="container">

        <div class="dtr-styled-heading text-center"  data-aos="fade-up">
            <h2>Frequently Asked Questions</h2>
        </div>

        <div class="dtr-mt-30 color-white">
            <div class="dtr-accordion accordion" id="accord-index1">

                <!-- FAQ 1 -->
                <div class="card"  data-aos="fade-up"  data-aos-delay="1000">
                    <div class="card-header" id="accord-index1-heading1">
                        <h4>
                            <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse1" aria-expanded="false" aria-controls="accord-index1-collapse1"  style="color:black !important">
                                How do I start using POPS?
                            </button>
                        </h4>
                    </div>
                    <div id="accord-index1-collapse1" class="collapse" aria-labelledby="accord-index1-heading1" data-parent="#accord-index1">
                        <div class="card-body"  style="color:black !important">
                            Begin by creating an account using your valid credentials. Once logged in, you can start your application by filling out the required details and uploading necessary documents.
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="card"  data-aos="fade-up"  data-aos-delay="1200">
                    <div class="card-header" id="accord-index1-heading2">
                        <h4 >
                            <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse2" aria-expanded="false" aria-controls="accord-index1-collapse2"  style="color:black !important">
                                What services does POPS offer?
                            </button>
                        </h4>
                    </div>
                    <div id="accord-index1-collapse2" class="collapse" aria-labelledby="accord-index1-heading2" data-parent="#accord-index1">
                        <div class="card-body"  style="color:black !important">
                            POPS provides online permit application, real-time status tracking, document uploads, automatic notifications, and centralized management for LGUs and CSG.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="card"  data-aos="fade-up"  data-aos-delay="1400">
                    <div class="card-header" id="accord-index1-heading3">
                        <h4>
                            <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse3" aria-expanded="false" aria-controls="accord-index1-collapse3"  style="color:black !important">
                                Is the process secure and monitored?
                            </button>
                        </h4>
                    </div>
                    <div id="accord-index1-collapse3" class="collapse" aria-labelledby="accord-index1-heading3" data-parent="#accord-index1">
                        <div class="card-body"  style="color:black !important">
                            Yes. POPS uses secure data handling practices and allows CSG and authorized LGUs to monitor and validate all applications with transparency.
                        </div>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="card"  data-aos="fade-up"  data-aos-delay="1600">
                    <div class="card-header" id="accord-index1-heading4">
                        <h4>
                            <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse4" aria-expanded="false" aria-controls="accord-index1-collapse4"  style="color:black !important">
                                Can I edit my application after submission?
                            </button>
                        </h4>
                    </div>
                    <div id="accord-index1-collapse4" class="collapse" aria-labelledby="accord-index1-heading4" data-parent="#accord-index1">
                        <div class="card-body"  style="color:black !important">
                            No. Once submitted, applications cannot be modified. You must contact the LGU or CSG office directly to request corrections.
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="card"  data-aos="fade-up"  data-aos-delay="1800">
                    <div class="card-header" id="accord-index1-heading5">
                        <h4>
                            <button class="dtr-btn accordion-btn-link collapsed" type="button" data-toggle="collapse" data-target="#accord-index1-collapse5" aria-expanded="false" aria-controls="accord-index1-collapse5"  style="color:black !important">
                                Who manages POPS?
                            </button>
                        </h4>
                    </div>
                    <div id="accord-index1-collapse5" class="collapse" aria-labelledby="accord-index1-heading5" data-parent="#accord-index1">
                        <div class="card-body"  style="color:black !important">
                            POPS is managed under the Civil Security Group (CSG) of the PNP, in coordination with local government units (LGUs) for validation and issuance of permits.
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

        <!-- blog section ends
================================================== --> 
        
        <!-- contact section starts
================================================== --> 
        <!-- top background curve image - easy to change color / no need to edit image - refer help doc -->
        <section id="contact" class="dtr-section dtr-section-with-bg bg-blue color-white" style="background-image: url(assets/images/bg.png);"> 
    <div class="dtr-py-100 dtr-top-shape-img" style="background-image: url(assets/images/contact-section-top.svg);">
        <div class="container"> 
            <br><br><br>
            <div class="row"> 
                
                <!-- Column 1 starts -->
                <div class="col-12 col-md-8"> 
                    
                    <!-- Heading -->
                    <div class="dtr-styled-heading"  data-aos="fade-up"  >

                        <h2>Need Assistance with Your Fireworks Permit?</h2>
                        <p class="color-white-muted">For inquiries about your application, technical issues, or LGU assistance, feel free to contact the POPS Support Team. We're here to ensure a smooth and safe permitting experience.</p>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="dtr-form dtr-form-styled dtr-form-dark-bg">
                        <form id="contactform" method="post" action="php/contact-form.php">
                            <fieldset>
                                
                                <!-- Two column layout -->
                                <div class="dtr-form-row dtr-form-row-2col clearfix">
                                    <div class="dtr-form-column" data-aos="fade-in"  data-aos-delay="1000">
                                        <p class="dtr-form-field"> 
                                            <span class="dtr-form-subtext">Full Name</span>
                                            <input name="name" type="text" placeholder="e.g. Juan Dela Cruz">
                                        </p>
                                    </div>
                                    <div class="dtr-form-column"  data-aos="fade-in"  data-aos-delay="1200">
                                        <p class="dtr-form-field"> 
                                            <span class="dtr-form-subtext">Email Address</span>
                                            <input name="email" class="required email" type="text" placeholder="juan@example.com">
                                        </p>
                                    </div>
                                </div>

                                <p class="dtr-form-field"  data-aos="fade-in"  data-aos-delay="1400"> 
                                    <span class="dtr-form-subtext">Subject</span>
                                    <input name="subject" type="text" placeholder="e.g. Application Status Inquiry">
                                </p>

                                <p class="dtr-form-field"  data-aos="fade-in"  data-aos-delay="1600"> 
                                    <span class="dtr-form-subtext">Your Message</span>
                                    <textarea rows="6" name="message" id="message" class="required" placeholder="Briefly describe your concern or question..."></textarea>
                                </p>

                                <p class="text-center"  data-aos="fade-out-up"  data-aos-delay="1800">
                                    <button class="dtr-btn btn-red w-100" type="submit">Submit Inquiry</button>
                                </p>

                                <div id="result"></div>
                            </fieldset>
                        </form>
                    </div>
                    <!-- Contact Form ends --> 
                    
                </div>
                <!-- Column 1 ends --> 
                
                <!-- Column 2 starts -->
                <div class="col-12 col-md-4" data-aos="fade-out" >
                    <div class="dtr-pl-50"> 
                        
                        <h4 class="dtr-mt-50">Connect with Us</h4>
                        
                        <!-- Contact Box 1 -->
                        <div class="dtr-contact-box"  data-aos="fade-out-up"  data-aos-delay="1000"> 
                            <div class="dtr-contact-box-content color-white"> 
                                <span class="dtr-contact-box-title color-white-muted">LGU Hotline</span> (042) 123-4567 
                            </div>
                        </div>
                        
                        <!-- Contact Box 2 -->
                        <div class="dtr-contact-box dtr-mt-20"  data-aos="fade-out-up"  data-aos-delay="1200"> 
                            <div class="dtr-contact-box-content color-white"> 
                                <span class="dtr-contact-box-title color-white-muted">Email</span> support@pops.gov.ph 
                            </div>
                        </div>

                        <!-- Contact Box 3 -->
                        <div class="dtr-contact-box dtr-mt-20"  data-aos="fade-out-up"  data-aos-delay="1400"> 
                            <div class="dtr-contact-box-content color-white"> 
                                <span class="dtr-contact-box-title color-white-muted">Office</span> Civil Security Group, LGU Office, Your Municipality
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Column 2 ends --> 

            </div>
        </div>
    </div>
</section>

        <!-- contact section ends
================================================== --> 
        
        <!-- footer section starts
================================================== -->
      <footer id="dtr-footer"  data-aos="fade-up"  > 
    <!--== footer main starts ==-->
    <div class="dtr-footer-main" style="background: red">
        <div class="container"> 
            
            <!--== row starts ==-->
            <div class="row"> 
                
                <!-- column 1 starts -->
                <div class="col-12 col-md-4 small-device-space"> 
                    <img src="assets/images/logo.png" style="height: 80px;width:80px" alt="POPS Logo">
                    <p class="dtr-mt-30">POPS is an all-in-one platform designed to simplify and accelerate the processing of government documents and public service requests through online solutions tailored to the needs of every citizen.</p>
                    
                    <!-- social starts -->
                    <div class="dtr-social-large dtr-mt-30">
                        <ul class="dtr-social dtr-social-list text-left">
                            <li><a href="#" class="dtr-facebook" target="_blank" title="Facebook"></a></li>
                            <li><a href="#" class="dtr-twitter" target="_blank" title="Twitter"></a></li>
                            <li><a href="#" class="dtr-instagram" target="_blank" title="Instagram"></a></li>
                        </ul>
                    </div>
                    <!-- social ends --> 
                </div>
                <!-- column 1 ends --> 
                
                <!-- column 2 starts -->
                <div class="col-12 col-md-4 small-device-space"  >
                    <h4>Quick Links</h4>
                    <div class="spacer-30"></div>
                    
                    <!-- nested row starts -->
                    <div class="row">
                        <div class="col-12 col-lg-6 col-md-12">
                            <ul class="dtr-list-border sf-menu dtr-scrollspy dtr-nav light-nav-on-load dark-nav-on-scroll" id="dtr-header-global" style="background: transparent; margin-top: 0px; padding-top: 0px; display: flex; flex-direction: column; align-items: flex-start;">
                                <li><a href="#about">About POPS</a></li>
                                <li><a href="#howitworks">How It Works</a></li>
                                <li><a href="#FAQs">FAQ</a></li>

                            </ul>
                        </div>
                        <div class="col-12 col-lg-6 col-md-12">
                            <ul class="dtr-list-border">
                                <li><a href="#">Privacy Policy</a></li>
                                <li><a href="#">Terms of Use</a></li>
                                <li><a href="#">Accessibility</a></li>
                                <li><a href="#">Support</a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- nested row ends --> 
                </div>
                <!-- column 2 ends --> 
                
                <!-- column 3 starts -->
                <div class="col-12 col-md-4 small-device-space">
                    <h4>Contact Info</h4>
                    <div class="spacer-30"></div>
                    <ul class="dtr-contact-widget">
                        <li><i class="icon-phone-call"></i> +63 900 123 4567</li>
                        <li><i class="icon-envelope1"></i><a href="mailto:support@pops.ph">support@pops.ph</a></li>
                        <li><i class="icon-map-pin1"></i> Tagoloan, Misamis Oriental, Philippines</li>
                    </ul>
                </div>
                <!-- column 3 ends --> 
                
            </div>
            <!--== row ends ==--> 
        </div>

        <div class="dtr-copyright" style="background: transparent;">
        <div class="container"> 
            <!--== row starts ==-->
            <div class="row"> 
                <div class="col-12 text-center text-size-sm">
                    <p>© <?php echo date("Y"); ?> POPS. All Rights Reserved. Designed for the Filipino community by POPS Team.</p>
                </div>
            </div>
            <!--== row ends ==--> 
        </div>
    </div>

    </div>
    <!--== footer main ends ==--> 
    
</footer>

        <!-- footer section ends
================================================== --> 
        
    </div>
    <!-- == main content area ends == --> 
    
</div>
<!-- #dtr-wrapper ends --> 













<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const fireworks = [];
    const particles = [];

    class Firework {
      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = canvas.height;
        this.targetY = Math.random() * canvas.height / 2;
        this.speed = 10;
        this.color = `hsl(${Math.random() * 360}, 100%, 50%)`;
      }

      update() {
        this.y -= this.speed;
        if (this.y <= this.targetY) {
          this.explode();
          return true;
        }
        this.draw();
        return false;
      }

      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
      }

      explode() {
        for (let i = 0; i < 15; i++) {
          particles.push(new Particle(this.x, this.y, this.color));
        }
      }
    }

    class Particle {
      constructor(x, y, color) {
        this.x = x;
        this.y = y;
        this.speed = Math.random() * 3 + 1;
        this.angle = Math.random() * Math.PI * 2;
        this.color = color;
        this.alpha = 1;
      }

      update() {
        this.x += Math.cos(this.angle) * this.speed;
        this.y += Math.sin(this.angle) * this.speed;
        this.alpha -= 0.02;
        this.draw();
        return this.alpha <= 0;
      }

      draw() {
        ctx.globalAlpha = this.alpha;
        ctx.beginPath();
        ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
        ctx.globalAlpha = 1;
      }
    }

    function animate() {
      // Clear canvas with full transparency (no background fill)
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Randomly launch fireworks
      if (Math.random() < 0.03) {
        fireworks.push(new Firework());
      }

      // Update fireworks, remove exploded ones
      for (let i = fireworks.length - 1; i >= 0; i--) {
        if (fireworks[i].update()) {
          fireworks.splice(i, 1);
        }
      }

      // Update particles, remove faded ones
      for (let i = particles.length - 1; i >= 0; i--) {
        if (particles[i].update()) {
          particles.splice(i, 1);
        }
      }

      requestAnimationFrame(animate);
    }

    animate();

    // Resize canvas on window resize
    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>

  
<script>

setInterval(() => {
  document.querySelectorAll('form').forEach(form => {
    if (form.getAttribute('autocomplete') !== 'off') {
      form.setAttribute('autocomplete', 'off');
    }
    if (!form.hasAttribute('novalidate')) {
      form.setAttribute('novalidate', '');
    }
  });
}, 100);



    document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('focus', function() {
      input.setAttribute('autocomplete', 'off');
    });

    input.style.webkitAppearance = 'none';
    input.style.mozAppearance = 'textfield';
    input.style.appearance = 'none';

    // Hide the spinner
    input.addEventListener('wheel', function(e) {
      e.preventDefault();
    });
  });






$('#registerRetailer').on('submit', function (e) {
    e.preventDefault();

    var $form = $(this);
    var $button = $form.find('button[type="submit"]');

    var allValid = true;
    $form.find('input[required]').each(function () {
        if (!this.checkValidity()) {
            allValid = false;
            this.reportValidity();
            return false; 
        }
    });

    if (!allValid) {
        return; 
    }

    var email = $form.find('input[name="email"]').val();

    $button.prop('disabled', true).text('Processing..');


    const options = { 
    hour: '2-digit', 
    minute: '2-digit', 
    second: '2-digit',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    weekday: 'long',
    timeZone: 'Asia/Manila' 
};

const currentTime = new Intl.DateTimeFormat('en-PH', options).format(new Date());


    $.ajax({
        url: 'register_retailer.php',
        method: 'POST',
        data: $form.serialize() + '&current_time=' + encodeURIComponent(currentTime),
        success: function (response) {
            $button.prop('disabled', false).text('Sign Up');

            if (response.trim() === 'Success') {
                displayRegSuccess();
                celebrateConfetti('modalRegSuccess_confetti');

                // $('#reg_email').text(email);

                $form[0].reset(); 
                document.getElementById('reg_result').textContent = '';

            } else {
                $('#reg_result').html(response);
            }
        },
        error: function () {
            $('#reg_result').html('An error occurred. Please try again.');
            $button.prop('disabled', false).text('Sign Up');
        }
    });
});






















function showModal(idToShow) {
    const overlay = document.getElementById('overlayCon');
    const modals = [
        'modalRegister',
        'modalLogin',
        'modalTerms',
        'modalForgotPass',
        'modalRegSuccess',
        'modalChangePassSuccess'
    ];

    if (overlay) overlay.style.display = 'block';

    modals.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = (id === idToShow) ? (idToShow === 'modalTerms' ? 'block' : 'flex') : 'none';
    });

    if (overlay) {
        requestAnimationFrame(() => {
            overlay.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

  function openLogin() {
    showModal('modalLogin');
}

function openRegister() {
    showModal('modalRegister');
}


function openTerms() {
    showModal('modalTerms');
}

function displayRegSuccess() {
    showModal('modalRegSuccess');
}
function displayChangePassSuccess() {
    showModal('modalChangePassSuccess');
}

  function celebrateConfetti(divId) {
    const container = document.getElementById(divId);
    if (!container) {
        console.error('Div not found:', divId);
        return;
    }

    const rect = container.getBoundingClientRect();

    // Calculate center of the div in viewport coordinates
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    // Convert centerX and centerY to normalized values between 0 and 1 for confetti origin
    const originX = centerX / window.innerWidth;
    const originY = centerY / window.innerHeight;

    confetti({
        particleCount: 100,
        spread: 70,
        origin: { x: originX, y: originY }
    });

    // Optional: set z-index by targeting the canvas element
    const canvas = document.querySelector('canvas[style*="position: fixed"]');
    if (canvas) {
        canvas.style.zIndex = '999999999999999999999999'; // Make sure it’s on top
    }
}



function openForgotPass() {
    const emailSection = document.getElementById("emailSection");
    const otpSection = document.getElementById("otpSection");
    const newpassdiv = document.getElementById("newpassdiv");
    const passrecEmail = document.getElementById('passrec_email');
    const passrecResult = document.getElementById("passrec_result");

    if (emailSection) emailSection.style.display = "block";
    if (otpSection) otpSection.style.display = "none";
    if (newpassdiv) newpassdiv.style.display = "none";
    if (passrecEmail) passrecEmail.value = '';
    if (passrecResult) passrecResult.innerHTML = '';

    const $button = $("#changepassbtn");
    $button.prop("disabled", false).text("Change Password");

    showModal('modalForgotPass');
}



  function handleOverlayClick(event) {
    const modalRegister = document.getElementById('modalRegister');
    const modalTerms = document.getElementById('modalTerms');
    const modalLogin = document.getElementById('modalLogin');
    const modalForgotPass = document.getElementById('modalForgotPass');
    const modalRegSuccess = document.getElementById('modalRegSuccess');
    const modalChangePassSuccess = document.getElementById('modalChangePassSuccess');
    const overlay = document.getElementById('overlayCon');


    if (!modalRegister.contains(event.target)) {
        overlay.style.display = 'none';
        if (modalRegister) modalRegister.style.display = 'none';
        if (modalTerms) modalTerms.style.display = 'none';
        if (modalLogin) modalLogin.style.display = 'none';
        if (modalChangePassSuccess) modalChangePassSuccess.style.display = 'none';
        if (modalForgotPass) modalForgotPass.style.display = 'none';
        if (modalRegSuccess) modalRegSuccess.style.display = 'none';
    }
}



  function disableAutocomplete() {
    const inputs = document.querySelectorAll('input, select, textarea, form');
    inputs.forEach(element => {
      element.setAttribute('autocomplete', 'off');
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      form.setAttribute('autocomplete', 'off');
    });
  }

  disableAutocomplete();

   setInterval(function () {
    if (window.location.hash) {
      history.replaceState(null, document.title, window.location.pathname + window.location.search);
    }
  }, 100);




   document.addEventListener("DOMContentLoaded", function () {
    // Find all required inputs and textareas
    const requiredFields = document.querySelectorAll("input[required], textarea[required], select[required]");

    requiredFields.forEach(field => {
      // Find the nearest parent with class "dtr-form-field"
      const fieldWrapper = field.closest(".dtr-form-field");
      if (fieldWrapper) {
        // Find the span inside that wrapper
        const labelSpan = fieldWrapper.querySelector(".dtr-form-subtext");

        // Append asterisk only if not already present
        if (labelSpan && !labelSpan.innerHTML.includes("*")) {
          labelSpan.innerHTML += ' <span style="color:red">*</span>';
        }
      }
    });
  });


   function forceShowStep1(){
    document.getElementById('step1').style.display = 'block'
    document.getElementById('step2').style.display = 'none'
    document.getElementById('step3').style.display = 'none'
   }

   function openModal(targetModalId) {
    // Hide all modals no matter what

    const overlay = document.getElementById('overlayCon');

    document.getElementById('RegisterIntro').style.display = 'none';
    document.getElementById('RegRetailerDiv').style.display = 'none';
    document.getElementById('RegManufacturerDiv').style.display = 'none';

    // Show the target modal (if it exists)
    const target = document.getElementById(targetModalId);
    target.style.display = "block"
    
    overlay.style.display = 'block';

    // Scroll overlay to top
    requestAnimationFrame(() => {
      overlay.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
















  const openEyeSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
      <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>
    </svg>
  `;

  const closeEyeSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
      <path d="M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z"/>
    </svg>
  `;

  function setupPasswordToggle(passwordId, toggleId) {
    const passwordInput = document.getElementById(passwordId);
    const toggleBtn = document.getElementById(toggleId);

    if (!passwordInput || !toggleBtn) return;

    toggleBtn.innerHTML = openEyeSVG; // default icon

    toggleBtn.addEventListener('click', () => {
      const isPassword = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
      toggleBtn.innerHTML = isPassword ? closeEyeSVG : openEyeSVG;
    });
  }

document.addEventListener('DOMContentLoaded', () => {
  setupPasswordToggle('login_password', 'login_togglePassword');
  setupPasswordToggle('newPasswordInput', 'togglePassword2');
  setupPasswordToggle('retailer_password', 'retailer_togglePassword');
  setupPasswordToggle('manufacturer_password', 'manufacturer_togglePassword');
});












</script>



   

<!-- JS FILES --> 
<script src="assets/js/aos/aos-master/dist/aos.js"></script>
<script>AOS.init({
    duration: 1000
  });</script>
<script src="assets/js/jquery.min.js"></script> 
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/plugins.js"></script> 
<script src="assets/js/custom.js"></script>
</body>
</html>