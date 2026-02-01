<?php
session_start();
include('../db_conn.php');
$client_id = $_SESSION['client_id'];
$role_id = $_SESSION['role_id'];

// Fetch shared data
$sql_client = "SELECT email FROM clients_acc WHERE client_id = ?";
$stmt = $conn->prepare($sql_client);
$stmt->bind_param("s", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

if ($role_id == 3) {
    // Retailer
    $sql = "SELECT * FROM retailers_info WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();
    ?>
    <form id="profile-form" style="padding-top: 30px;">
      <input type="hidden" name="role" value="3">
      <div class="row">
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Full Name</div>
          <input type="text" class="form-control" name="full_name" value="<?= $info['full_name'] ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Phone</div>
          <input type="text" class="form-control" name="phone" value="<?= $info['phone'] ?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Gender</div>
          <input type="text" class="form-control" name="gender" value="<?= $info['gender'] ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Birthday</div>
          <input type="date" class="form-control" name="bdate" value="<?= $info['bdate'] ?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col-12 mb-3">
          <div style="text-align:left;">Address</div>
          <textarea class="form-control" name="address" required><?= $info['address'] ?></textarea>
        </div>
      </div>
    </form>
    <?php
} elseif ($role_id == 4) {
    // Manufacturer
    $sql = "SELECT * FROM manufacturers_info WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();
    ?>
    <form id="profile-form" style="padding-top:30px">
      <input type="hidden" name="role" value="4">
      <div class="row">
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Company Name</div>
          <input type="text" class="form-control" name="company_name" value="<?= $info['company_name'] ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Dealer Name</div>
          <input type="text" class="form-control" name="dealer_name" value="<?= $info['dealer_name'] ?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Contact Number</div>
          <input type="text" class="form-control" name="contact_number" value="<?= $info['contact_number'] ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <div style="text-align:left;">Company Website</div>
          <input type="url" class="form-control" name="company_website" value="<?= $info['company_website'] ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-12 mb-3">
          <div style="text-align:left;">Company Address</div>
          <textarea class="form-control" name="company_address" required><?= $info['company_address'] ?></textarea>
        </div>
      </div>
    </form>
    <?php
} else {
    echo "Invalid role.";
}
?>

<!-- Change Password Section -->
<div style="margin-top: 40px; border-top: 2px solid #0f2c5a; padding-top: 30px;">
  <h5 style="color: #0f2c5a; margin-bottom: 20px;">Change Password</h5>
  
  <form id="changePasswordForm" method="post" autocomplete="off">
    <fieldset>
      
      <!-- Step 1: Send OTP -->
      <div id="otpRequestSection">
        <p style="font-size: 14px; color: #666;">
          Click the button below to receive an OTP code to your registered email: <strong><?= $client['email'] ?></strong>
        </p>
        <input type="hidden" name="email" value="<?= $client['email'] ?>">
        <button type="button" class="btn" id="sendOtpBtn" style="background: #0f2c5a; color: white; padding: 10px 30px;">
          Send OTP Code
        </button>
      </div>

      <!-- Step 2: Enter OTP -->
      <div id="otpVerifySection" style="display: none;">
        <span style="display: block; text-align: left; font-size: .765em; margin-bottom: 10px;">Enter OTP Code</span>
        <div class="dtr-form-field" style="display:flex; justify-content: space-between; margin-bottom: 20px;">
          <input id="otp1_change" type="number" min="0" max="9" style="width:23%; padding-top: 15px; text-align: center; font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp2_change')">
          <input id="otp2_change" type="number" min="0" max="9" style="width:23%; padding-top: 15px; text-align: center; font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp3_change')">
          <input id="otp3_change" type="number" min="0" max="9" style="width:23%; padding-top: 15px; text-align: center; font-size: 30px;" required oninput="enforceOneDigitAndMove(this, 'otp4_change')">
          <input id="otp4_change" type="number" min="0" max="9" style="width:23%; padding-top: 15px; text-align: center; font-size: 30px;" required>
        </div>
      </div>

      <!-- Step 3: Enter New Password -->
      <div id="newPasswordSection" style="display: none;">
        <div class="dtr-form-field" style="margin-top: 10px; position: relative;">
          <span class="dtr-form-subtext">New Password</span>
          <input id="newPasswordInput_profile" name="password" type="password" placeholder="Enter your new password" required style="padding-right: 40px; width: 100%; box-sizing: border-box;">
          
          <div id="togglePassword_profile" style="position: absolute; right: 15px; top: 47px; width: 24px; height: 24px; cursor: pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#0f2c5a">
              <path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/>
            </svg>
          </div>
        </div>

        <button type="submit" class="btn" id="changePasswordBtn" style="background: #0f2c5a; color: white; padding: 10px 30px; margin-top: 20px;">
          Change Password
        </button>
      </div>

      <div id="change_password_result" style="margin-top: 10px; color: #E75151"></div>

    </fieldset>
  </form>
</div>

<script>
// Enforce one digit and move to next input
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

// Send OTP
$('#sendOtpBtn').on('click', function() {
  const $button = $(this);
  $button.prop('disabled', true).text('Sending OTP...');
  
  const email = $('input[name="email"]').val();
  
  $.ajax({
    url: 'send_change_password_otp.php',
    method: 'POST',
    data: { email: email },
    success: function(response) {
      $('#change_password_result').html(response);
      
      if (response.includes('An OTP has been sent')) {
        $('#otpRequestSection').hide();
        $('#otpVerifySection').show();
        
        // Disable OTP inputs initially
        $('#otp1_change, #otp2_change, #otp3_change, #otp4_change').prop('disabled', false).val('');
      }
      
      $button.prop('disabled', false).text('Send OTP Code');
    },
    error: function() {
      $('#change_password_result').html('An error occurred. Please try again.');
      $button.prop('disabled', false).text('Send OTP Code');
    }
  });
});

// Verify OTP automatically when 4 digits entered
setInterval(() => {
  const otp1 = $('#otp1_change').val() || '';
  const otp2 = $('#otp2_change').val() || '';
  const otp3 = $('#otp3_change').val() || '';
  const otp4 = $('#otp4_change').val() || '';
  const fullOtp = otp1 + otp2 + otp3 + otp4;

  if (fullOtp.length === 4) {
    $.ajax({
      url: 'check_otp_response.php',
      method: 'POST',
      data: { otp: fullOtp },
      success: function(result) {
          console.log(result)
        if (result.trim() === 'success') {
          $('#otpVerifySection').hide();
          $('#newPasswordSection').show();
          $('#change_password_result').html('');
          
          // Disable OTP fields
          $('#otp1_change, #otp2_change, #otp3_change, #otp4_change').prop('disabled', true);
        }
      }
    });
  }
}, 100);

// Submit new password
$('#changePasswordForm').on('submit', function(e) {
  e.preventDefault();
  
  const $button = $('#changePasswordBtn');
  $button.prop('disabled', true).text('Changing password...');
  
  const newPassword = $('#newPasswordInput_profile').val();
  const otp1 = $('#otp1_change').val();
  const otp2 = $('#otp2_change').val();
  const otp3 = $('#otp3_change').val();
  const otp4 = $('#otp4_change').val();
  const otptyped = otp1 + otp2 + otp3 + otp4;
  
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
    url: 'changepass.php',
    method: 'POST',
    data: {
      password: newPassword,
      otptyped: otptyped,
      currentTime: currentTime
    },
    success: function(response) {
      $('#change_password_result').html(response);
      
      if (response.includes('Password changed successfully')) {
        $('#change_password_result').css('color', 'green');
        
        // Reset form after 2 seconds
        setTimeout(() => {
          $('#changePasswordForm')[0].reset();
          $('#otpRequestSection').show();
          $('#otpVerifySection').hide();
          $('#newPasswordSection').hide();
          $('#change_password_result').html('');
          $('#otp1_change, #otp2_change, #otp3_change, #otp4_change').prop('disabled', false).val('');
        }, 2000);
      } else {
        $('#change_password_result').css('color', '#E75151');
      }
      
      $button.prop('disabled', false).text('Change Password');
    },
    error: function() {
      $('#change_password_result').html('An error occurred. Please try again.').css('color', '#E75151');
      $button.prop('disabled', false).text('Change Password');
    }
  });
});

// Password toggle functionality
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

$('#togglePassword_profile').on('click', function() {
  const passwordInput = $('#newPasswordInput_profile');
  const isPassword = passwordInput.attr('type') === 'password';
  
  passwordInput.attr('type', isPassword ? 'text' : 'password');
  $(this).html(isPassword ? closeEyeSVG : openEyeSVG);
});
</script>