<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    @session_start(); 
}


include 'db_conn.php';

// Get ref from URL parameter
if (!isset($_GET['ref']) || empty(trim($_GET['ref']))) {
    die("Error: Reference ID is required");
}

$ref = trim($_GET['ref']);



$refParam = $ref !== '' ? '?ref=' . urlencode($ref) : '';

// Check if admin is logged in
if (!isset($_SESSION['logged_admin'])) {

    $redirectUrl = "hey" . $refParam;

    // Use JavaScript redirect if headers already sent
    if (headers_sent()) {
        echo '<script>window.location.href="' . $redirectUrl . '";</script>';
        exit();
    } else {
        header("Location: " . $redirectUrl);
        exit();
    }
}


// First, get the application_id from the special_permit_display_fireworks table using the ref
$ref_sql = "
    SELECT spdf.application_id 
    FROM special_permit_display_fireworks spdf
    LEFT JOIN applications app ON spdf.application_id = app.application_id
    WHERE app.ref_id = ?
    LIMIT 1
";

$ref_stmt = $conn->prepare($ref_sql);
$ref_stmt->bind_param("s", $ref);
$ref_stmt->execute();
$ref_result = $ref_stmt->get_result();

if ($ref_result->num_rows === 0) {
   echo "<script>window.location.href='notfound';</script>";
}

$ref_row = $ref_result->fetch_assoc();
$application_id = (int)$ref_row['application_id'];
$ref_stmt->close();

// Now use the application_id to get all the details
$sql = "
    SELECT 
        spdf.*,
        app.client_id,
        app.ref_id,
        app.permit_for,
        app.apply_date,
        app.approval_date,
        app.status,
        mf.company_name,
        mf.dealer_name,
        mf.contact_number,
        mf.company_website,
        mf.company_address,
        mf.manufacturer_license_no,
        mf.manufacturer_serial_no,
        mf.manufacturer_expiry_date,
        mf.dealer_license_no,
        mf.dealer_serial_no,
        mf.dealer_expiry_date
    FROM special_permit_display_fireworks spdf
    LEFT JOIN applications app ON spdf.application_id = app.application_id
    LEFT JOIN manufacturers_info mf ON app.client_id = mf.client_id
    WHERE spdf.application_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No fireworks display permit found for Application ID: " . htmlspecialchars($application_id));
}

$row = $result->fetch_assoc();

// ==================== NOW EXTRACT ALL DATA SAFELY ====================

// Fireworks Display Details
$display_datetime     = $row['display_datetime'];
$date                 = new DateTime($display_datetime);
$display_date         = $date->format('F d, Y');
$display_time         = $date->format('h:i A');
$display_location     = $row['display_location'] ?? 'Not specified';
$display_purpose      = $row['display_purpose'] ?? 'Not specified';
$pyro_technician      = $row['pyro_technician'] ?? 'Not specified';
$fdo_license_number   = $row['fdo_licence_number']; // note: British spelling in DB
$control_number       = $row['control_number'] ?? '';
$status       = $row['status'] ?? '';
$ref_id       = $row['ref_id'] ?? '';
$partner_police_station = $row['partner_police_station'] ?? 'Not specified';
$reference_number     = $row['receipt_reference_number'] ?? '';
$amount_paid          = number_format($row['amount_paid'] ?? 0, 2);
$pay_date             = $row['pay_date'] && $row['pay_date'] !== '0000-00-00' 
                        ? date("F d, Y", strtotime($row['pay_date'])) 
                        : 'Not paid';

// Split control number into individual digits: cn1, cn2, ..., cn20
$cn1 = $cn2 = $cn3 = $cn4 = $cn5 = $cn6 = $cn7 = $cn8 = $cn9 = $cn10 =
$cn11 = $cn12 = $cn13 = $cn14 = $cn15 = $cn16 = $cn17 = $cn18 = $cn19 = $cn20 = '';

$control_chars = str_split(strtoupper($control_number));
foreach ($control_chars as $i => $char) {
    if ($i < 20) {
        ${'cn' . ($i + 1)} = $char;
    }
}

// Application Details
$apply_date_raw = $row['apply_date'] ?? '';
$apply_date_clean = preg_replace('/^[A-Za-z]+, | at .*$/', '', $apply_date_raw);
$apply_date = $apply_date_clean ? date("F d, Y", strtotime($apply_date_clean)) : 'Unknown';

$approval_date = $row['approval_date'] 
    ? date("F d, Y", strtotime($row['approval_date'])) 
    : 'Pending';

// Manufacturer / Company Info
$company_name         = $row['company_name'] ?? 'N/A';
$dealer_name          = $row['dealer_name'] ?? 'N/A';
$contact_number       = $row['contact_number'] ?? 'N/A';
$company_address      = $row['company_address'] ?? 'N/A';

// All data is now ready — include your HTML/PDF template here
// include 'fireworks_permit_template.php';

$stmt->close();
$conn->close();
?>

<html><head>
<title>Special Permit for Fireworks Display</title>
<!-- FAVICON FILES -->
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="assets/images/logo.png" rel="shortcut icon">


   <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>


body {padding:0; margin:0; text-align:center;  background-image: url('assets/images/bg2.png');
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;}
.page {margin:5px 0; }
.page svg {background-color:#fff; margin-top: 20px;}
#svg2 {   position:relative; }

#successendorsed { display:none; }

#successrejected { display:none; }

.qr-container {
            position: absolute;
            left: 0px;
            bottom: 0px;
            margin: auto;
            padding: 30px;
            width: 100px;
            border-radius: 15px;
            min-height: 280px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #qrcode {
            display: inline-block;
        }

        #qrcode img {
            border-radius: 10px;
        }

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

   .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Modal container with your original styles */
        .modal2 {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 550px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-left: transparent;
            border-right: transparent;
            border-bottom: transparent;
            padding:20px;
        }
</style>
</head>
<body>



  
   <?php if ($status == 'Permit Issued'): ?>
          <div style="color:#19E915;margin-top: 30px;">This permit is issued by the director.</div>
      <?php endif; ?>  

        <?php if ($status == 'Rejected'): ?>
          <div style="color:red;margin-top: 30px;">This permit is rejected by the director.</div>
      <?php endif; ?>  
  
   <?php if ($status == 'Endorsed To Director'): ?>

          <div style="color:#19E915;margin-top: 30px;">This permit is endorsed to the director.</div>
      <?php endif; ?>                                



  <input 
                type="hidden" 
                id="qrcodeinput" 
                autocomplete="off"
            >





<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>

    let status = "<?php echo $status; ?>";
    let ref_id = "<?php echo $ref_id; ?>"
    if (status == 'Permit Issued'){
    document.getElementById('qrcodeinput').value = `https://popscsg.xyz/view?ref=${ref_id}`;
    }






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



<div class="modal-overlay" id="successendorsed" onclick="hideModal()">
        <div class="modal2" onclick="event.stopPropagation()">
            <div class="boxcon" id="successendorsed_confetti">
                <div>
                    <img src="../../assets/images/logo.png"  style="width:200px;height:200px;">
                    <h4 style="margin-top: 15px;margin-bottom: 0px;color:#0f2c5a">Successfully Approved!</h4>
                    <p  style="color:red;">CSG | Philippine National Police</p>
                    <p>
                        This permit has been successfully approved by the Director. The approval process has been completed.
                    </p>
                   
                    
                    <div class="countdown-container">
                        <div class="countdown-text">This page will auto-redirect in:</div>
                        <div class="countdown-timer" id="countdown">10</div>
                    </div>
                </div>
            </div>
        </div>
    </div>




<div class="modal-overlay" id="successrejected" onclick="hideModal()">
        <div class="modal2" onclick="event.stopPropagation()">
            <div class="boxcon">
                <div>
                    <img src="../../assets/images/logo.png"  style="width:200px;height:200px;">
                    <h4 style="margin-top: 15px;margin-bottom: 0px;color:#0f2c5a">Successfully Rejected!</h4>
                    <p  style="color:red;">CSG | Philippine National Police</p>
                    <p>
                        This permit has been successfully rejected by the director. The rejected process has been completed.
                    </p>
                   
                    
                    <div class="countdown-container">
                        <div class="countdown-text">This page will auto-redirect in:</div>
                        <div class="countdown-timer" id="countdown">10</div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    


<div class="page">


<!-- Created with Inkscape (http://www.inkscape.org/) -->
<div >


          
          <div id="temp-qr" style="display:none;"></div>

     




<svg id="svg2" version="1.1" width="793.76001" height="1122.5601" viewBox="0 0 793.76001 1122.5601" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">
 <g id="qrcode-group" transform="translate(50, 852)">
            <foreignObject width="110" height="110">
            </foreignObject>
        </g>
  <defs id="defs6">
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath16">
      <path d="M 108.75,237.37 H 504.7 v 324 H 108.75 Z" clip-rule="evenodd" id="path14"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath28">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path26"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath36">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path34"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath48">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path46"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath60">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path58"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath68">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path66"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath80">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path78"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath88">
      <path d="m 442.66,61.464 h 133.1 v 21.36 h -133.1 z" clip-rule="evenodd" id="path86"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath96">
      <path d="m 353.95,115.82 h 72.75 v 93.75 h -72.75 z" clip-rule="evenodd" id="path94"></path>
    </clipPath>
    <mask maskUnits="userSpaceOnUse" x="0" y="0" width="1" height="1" id="mask100">
      <image width="1" height="1" style="image-rendering:optimizeSpeed" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGEAAAB9CAAAAACXeVKfAAAAAXNCSVQI5gpbmQAAAEhJREFUaIHtzUENAAAIBKDT/p01hQ83KEBNblUfB4nBYDAYDAaDwWAwGAwGg8FgMBgMBoPBYDAYDAaDwWAwGAwGg8FgMBgMfy1pUQH4fuPs7wAAAABJRU5ErkJggg==" id="image102"></image>
    </mask>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath112">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path110"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath120">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path118"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath128">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path126"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath136">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path134"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath144">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path142"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath152">
      <path d="M 8.871e-6,0 H 595.32001 V 841.92 H 8.871e-6 Z" clip-rule="evenodd" id="path150"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath160">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path158"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath168">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path166"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath176">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path174"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath184">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path182"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath192">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path190"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath200">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path198"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath208">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path206"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath216">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path214"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath224">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path222"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath232">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path230"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath244">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path242"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath252">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path250"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath264">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path262"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath272">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path270"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath284">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path282"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath294">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path292"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath306">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path304"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath314">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path312"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath326">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path324"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath338">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path336"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath350">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path348"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath358">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path356"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath370">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path368"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath378">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path376"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath390">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path388"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath402">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path400"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath410">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path408"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath422">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path420"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath434">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path432"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath442">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path440"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath454">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path452"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath466">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path464"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath478">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path476"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath486">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path484"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath498">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path496"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath510">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path508"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath522">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path520"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath534">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path532"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath544">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path542"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath554">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path552"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath564">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path562"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath574">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path572"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath584">
      <path d="m 30.6,59.424 h 233.45 v 54.72 H 30.6 Z" clip-rule="evenodd" id="path582"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath596">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path594"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath604">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path602"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath612">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path610"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath624">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path622"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath632">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path630"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath644">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path642"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath652">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path650"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath664">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path662"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath672">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path670"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath684">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path682"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath692">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path690"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath704">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path702"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath716">
      <path d="M 101.3,638.86 H 476.97 V 755.74 H 101.3 Z" clip-rule="evenodd" id="path714"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath724">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path722"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath732">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path730"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath740">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path738"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath752">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path750"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath760">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path758"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath768">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path766"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath776">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path774"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath784">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path782"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath792">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path790"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath800">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path798"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath808">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path806"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath816">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path814"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath824">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path822"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath832">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path830"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath840">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path838"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath848">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path846"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath856">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path854"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath864">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path862"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath876">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path874"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath888">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path886"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath896">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path894"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath908">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path906"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath920">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path918"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath932">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path930"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath944">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path942"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath952">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path950"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath960">
      <path d="m 40.32,528.07 h 75.504 v 13.8 H 40.32 Z" clip-rule="evenodd" id="path958"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath968">
      <path d="m 40.32,528.07 h 75.504 v 13.8 H 40.32 Z" clip-rule="evenodd" id="path966"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath980">
      <path d="m 40.32,528.07 h 75.504 v 13.8 H 40.32 Z" clip-rule="evenodd" id="path978"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath988">
      <path d="m 116.3,528.07 h 10.68 v 13.8 H 116.3 Z" clip-rule="evenodd" id="path986"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath996">
      <path d="m 116.3,528.07 h 10.68 v 13.8 H 116.3 Z" clip-rule="evenodd" id="path994"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1004">
      <path d="m 127.46,528.07 h 9.72 v 13.8 h -9.72 z" clip-rule="evenodd" id="path1002"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1012">
      <path d="m 127.46,528.07 h 9.72 v 13.8 h -9.72 z" clip-rule="evenodd" id="path1010"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1020">
      <path d="m 139.34,528.07 h 29.52 v 13.8 h -29.52 z" clip-rule="evenodd" id="path1018"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1028">
      <path d="m 139.34,528.07 h 29.52 v 13.8 h -29.52 z" clip-rule="evenodd" id="path1026"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1040">
      <path d="m 139.34,528.07 h 29.52 v 13.8 h -29.52 z" clip-rule="evenodd" id="path1038"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1052">
      <path d="m 139.34,528.07 h 29.52 v 13.8 h -29.52 z" clip-rule="evenodd" id="path1050"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1060">
      <path d="m 169.34,528.07 h 19.104 v 13.8 H 169.34 Z" clip-rule="evenodd" id="path1058"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1068">
      <path d="m 169.34,528.07 h 19.104 v 13.8 H 169.34 Z" clip-rule="evenodd" id="path1066"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1080">
      <path d="m 169.34,528.07 h 19.104 v 13.8 H 169.34 Z" clip-rule="evenodd" id="path1078"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1088">
      <path d="m 188.93,528.07 h 18.96 v 13.8 h -18.96 z" clip-rule="evenodd" id="path1086"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1096">
      <path d="m 188.93,528.07 h 18.96 v 13.8 h -18.96 z" clip-rule="evenodd" id="path1094"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1108">
      <path d="m 188.93,528.07 h 18.96 v 13.8 h -18.96 z" clip-rule="evenodd" id="path1106"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1116">
      <path d="m 208.37,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1114"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1124">
      <path d="m 208.37,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1122"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1136">
      <path d="m 208.37,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1134"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1144">
      <path d="m 223.25,528.07 h 16.92 v 13.8 h -16.92 z" clip-rule="evenodd" id="path1142"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1152">
      <path d="m 223.25,528.07 h 16.92 v 13.8 h -16.92 z" clip-rule="evenodd" id="path1150"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1164">
      <path d="m 223.25,528.07 h 16.92 v 13.8 h -16.92 z" clip-rule="evenodd" id="path1162"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1172">
      <path d="m 240.65,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1170"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1180">
      <path d="m 240.65,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1178"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1192">
      <path d="m 240.65,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1190"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1200">
      <path d="m 258.17,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1198"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1208">
      <path d="m 258.17,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1206"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1220">
      <path d="m 258.17,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1218"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1228">
      <path d="m 275.69,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1226"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1236">
      <path d="m 275.69,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1234"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1248">
      <path d="m 275.69,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1246"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1256">
      <path d="m 293.21,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1254"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1264">
      <path d="m 293.21,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1262"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1276">
      <path d="m 293.21,528.07 h 14.28 v 13.8 h -14.28 z" clip-rule="evenodd" id="path1274"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1284">
      <path d="m 307.97,528.07 h 17.064 v 13.8 H 307.97 Z" clip-rule="evenodd" id="path1282"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1292">
      <path d="m 307.97,528.07 h 17.064 v 13.8 H 307.97 Z" clip-rule="evenodd" id="path1290"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1304">
      <path d="m 307.97,528.07 h 17.064 v 13.8 H 307.97 Z" clip-rule="evenodd" id="path1302"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1312">
      <path d="m 325.51,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1310"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1320">
      <path d="m 325.51,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1318"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1332">
      <path d="m 325.51,528.07 h 17.04 v 13.8 h -17.04 z" clip-rule="evenodd" id="path1330"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1340">
      <path d="m 343.03,528.07 h 16.08 v 13.8 h -16.08 z" clip-rule="evenodd" id="path1338"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1348">
      <path d="m 343.03,528.07 h 16.08 v 13.8 h -16.08 z" clip-rule="evenodd" id="path1346"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1360">
      <path d="m 343.03,528.07 h 16.08 v 13.8 h -16.08 z" clip-rule="evenodd" id="path1358"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1562">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1560"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1570">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1568"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1578">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1576"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1586">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1584"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1598">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1596"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1610">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1608"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1622">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1620"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1634">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1632"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1642">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1640"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1654">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1652"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1662">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1660"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1674">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1672"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1686">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1684"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1698">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1696"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1710">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1708"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1722">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1720"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1734">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1732"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1746">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1744"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1758">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1756"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1766">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1764"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1778">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1776"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1790">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1788"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1798">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1796"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1810">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1808"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1822">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1820"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1834">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1832"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1842">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1840"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1854">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1852"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1866">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1864"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1878">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1876"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1890">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1888"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1898">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1896"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1910">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1908"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1922">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1920"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1930">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1928"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1942">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1940"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1954">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1952"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1966">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1964"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1978">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1976"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath1990">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path1988"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2002">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2000"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2014">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2012"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2026">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2024"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2038">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2036"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2050">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2048"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2062">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2060"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2074">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2072"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2082">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2080"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2090">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2088"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2102">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2100"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2110">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2108"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2118">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2116"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2130">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2128"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2138">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2136"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2150">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2148"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2162">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2160"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2172">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2170"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2184">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2182"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2192">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2190"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2204">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2202"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2212">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2210"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2226">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2224"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2238">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2236"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2246">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2244"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2258">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2256"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2272">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2270"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2284">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2282"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2294">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2292"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2302">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2300"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2310">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2308"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2318">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2316"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2326">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2324"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2338">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2336"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2346">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2344"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2354">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2352"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2362">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2360"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2370">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2368"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2382">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2380"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2390">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2388"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2398">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2396"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2410">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2408"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2418">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2416"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2426">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2424"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2438">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2436"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2450">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2448"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2458">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2456"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2466">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2464"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2474">
      <path d="M 32.64,104.42 H 567 V 639.57 H 32.64 Z" clip-rule="evenodd" id="path2472"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2486">
      <path d="m 484.3,719.5 h 80.424 v 75.624 H 484.3 Z" clip-rule="evenodd" id="path2484"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2494">
      <path d="m 484.3,719.5 h 80.424 v 75.624 H 484.3 Z" clip-rule="evenodd" id="path2492"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2506">
      <path d="M 405.07,787.2 H 574.8 v 17.28 H 405.07 Z" clip-rule="evenodd" id="path2504"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2514">
      <path d="M 405.07,787.2 H 574.8 v 17.28 H 405.07 Z" clip-rule="evenodd" id="path2512"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2526">
      <path d="M 405.07,787.2 H 574.8 v 17.28 H 405.07 Z" clip-rule="evenodd" id="path2524"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2534">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2532"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2542">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2540"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2550">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2548"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2562">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2560"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2574">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2572"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2586">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2584"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2594">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2592"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2606">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2604"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2614">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2612"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2622">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2620"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2630">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2628"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2638">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2636"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2646">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2644"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2654">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2652"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2662">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2660"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2670">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2668"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2678">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2676"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2686">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2684"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2698">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2696"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2706">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2704"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2718">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2716"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2726">
      <path d="M 27.6,786.96 H 190.37 V 814.2 H 27.6 Z" clip-rule="evenodd" id="path2724"></path>
    </clipPath>
    <clipPath clipPathUnits="userSpaceOnUse" id="clipPath2950">
      <path d="m 463.5,667.07 h 94.15 v 93.75 H 463.5 Z" clip-rule="evenodd" id="path2948"></path>
    </clipPath>
    <mask maskUnits="userSpaceOnUse" x="0" y="0" width="1" height="1" id="mask2954">
      <image width="1" height="1" style="image-rendering:optimizeSpeed" preserveAspectRatio="none" xlink:href="data:image/png;base64,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" id="image2956"></image>
    </mask>
  </defs>
  <g id="g8" transform="matrix(1.3333333,0,0,-1.3333333,0,1122.56)">
    <g id="g10">
      <g id="g12" clip-path="url(#clipPath16)">
        <g id="g18" transform="matrix(395.95,0,0,324,108.75,237.37)">
          <image width="1" height="1" style="image-rendering:optimizeSpeed" preserveAspectRatio="none" transform="matrix(1,0,0,-1,0,1)" xlink:href="data:image/png;base64,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" id="image20"></image>
        </g>
      </g>
    </g>
    <g id="g22">
      <g id="g24" clip-path="url(#clipPath28)">
        <g id="g30">
          <g id="g32" clip-path="url(#clipPath36)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,449.74,77.184)" style="font-variant:normal;font-weight:bold;font-size:6px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#333333;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text40"><tspan x="0 3.96 8.2919998 12.24 13.908 18.24 22.572001 26.52 30.851999 32.52 35.874001 39.228001 40.896 42.582001 46.296001 50.256001 54.924 56.610001 58.278 62.25 65.603996 69.318001 70.986 74.711998 78.066002 79.734001 83.099998 86.454002 88.122002 93.054001 96.767998 102.048 104.082 105.75" y="0" id="tspan38">PNP RCSU 10, FEO, Explosive Mgmt. </tspan></text>
          </g>
        </g>
        <g id="g42">
          <g id="g44" clip-path="url(#clipPath48)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,449.74,70.224)" style="font-variant:normal;font-weight:bold;font-size:6px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#333333;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text52"><tspan x="0 3.96 7.3140001 10.668 12.702 14.37 18.096001 21.695999" y="0" id="tspan50">Section </tspan></text>
          </g>
        </g>
        <g id="g54">
          <g id="g56" clip-path="url(#clipPath60)"></g>
        </g>
        <g id="g62">
          <g id="g64" clip-path="url(#clipPath68)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,449.74,63.384)" style="font-variant:normal;font-weight:normal;font-size:6px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#333333;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text72"><tspan x="0 3.3540001 4.6859999 7.6859999 11.022 14.388 17.388 20.742001 22.41 25.775999 29.129999 32.484001 34.152 38.124001 41.363998 43.397999 48.431999 49.764 51.431999 53.099998 57.071999 60.425999 63.425999 65.094002 66.426003 69.779999" y="0" id="tspan70">License and Permit Section</tspan></text>
          </g>
        </g>
        <g id="g74">
          <g id="g76" clip-path="url(#clipPath80)"></g>
        </g>
        <g id="g82">
          <g id="g84" clip-path="url(#clipPath88)"></g>
        </g>
      </g>
    </g>
    <g id="g90">
      <g id="g92" clip-path="url(#clipPath96)">
        <g id="g98" transform="matrix(72.75,0,0,93.75,353.95,115.82)">
          <!-- <image width="1" height="1" style="image-rendering:optimizeSpeed" preserveAspectRatio="none" transform="matrix(1,0,0,-1,0,1)" xlink:href="data:image/png;base64,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" mask="url(#mask100)" id="image104"></image> -->
        </g>
      </g>
    </g>
    <g id="g106">
      <g id="g108" clip-path="url(#clipPath112)"></g>
    </g>
    <g id="g114">
      <g id="g116" clip-path="url(#clipPath120)"></g>
    </g>
    <g id="g122">
      <g id="g124" clip-path="url(#clipPath128)"></g>
    </g>
    <g id="g130">
      <g id="g132" clip-path="url(#clipPath136)"></g>
    </g>
    <g id="g138">
      <g id="g140" clip-path="url(#clipPath144)"></g>
    </g>
    <g id="g146">
      <g id="g148" clip-path="url(#clipPath152)"></g>
    </g>
    <g id="g154">
      <g id="g156" clip-path="url(#clipPath160)">
        <g id="g162">
          <g id="g164" clip-path="url(#clipPath168)"></g>
        </g>
        <g id="g170">
          <g id="g172" clip-path="url(#clipPath176)"></g>
        </g>
        <g id="g178">
          <g id="g180" clip-path="url(#clipPath184)"></g>
        </g>
        <g id="g186">
          <g id="g188" clip-path="url(#clipPath192)"></g>
        </g>
        <g id="g194">
          <g id="g196" clip-path="url(#clipPath200)"></g>
        </g>
        <g id="g202">
          <g id="g204" clip-path="url(#clipPath208)"></g>
        </g>
        <g id="g210">
          <g id="g212" clip-path="url(#clipPath216)"></g>
        </g>
        <g id="g218">
          <g id="g220" clip-path="url(#clipPath224)"></g>
        </g>
        <g id="g226">
          <g id="g228" clip-path="url(#clipPath232)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,37.8,96.504)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text236"><tspan x="0 6.0029998 12.006 18.504 21.006001 27.504" y="0" id="tspan234">SBR NO</tspan></text>
          </g>
        </g>
        <g id="g238">
          <g id="g240" clip-path="url(#clipPath244)"></g>
        </g>
        <g id="g246">
          <g id="g248" clip-path="url(#clipPath252)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,73.824,96.504)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text256"><tspan x="0 2.52" y="0" id="tspan254">: </tspan></text>
          </g>
        </g>
        <g id="g258">
          <g id="g260" clip-path="url(#clipPath264)"></g>
        </g>
        <g id="g266">
          <g id="g268" clip-path="url(#clipPath272)">
            <text xml:space="preserve"
      transform="matrix(1,0,0,-1,81.384,96.504)"
      style="font-variant:normal;
             font-weight:bold;
             font-size:8.04px;
             font-family:Arial;
             -inkscape-font-specification:Arial-BoldMT;
             writing-mode:lr-tb;
             fill:#000000;
             fill-opacity:1;
             fill-rule:nonzero;
             stroke:none;
             text-decoration: underline;
             text-anchor: start;"
      id="text276"><?php echo $reference_number; ?></text>

          </g>
        </g>
        <g id="g278">
          <g id="g280" clip-path="url(#clipPath284)"></g>
        </g>
        <g id="g288">
          <g id="g290" clip-path="url(#clipPath294)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,37.8,86.184)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text298"><tspan x="0 6.0029998 13.554 20.511 27.009001 33.471001 38.970001 41.499001 44.000999 50.004002 55.053001" y="0" id="tspan296">AMOUNT: Php</tspan></text>
          </g>
        </g>
        <g id="g300">
          <g id="g302" clip-path="url(#clipPath306)"></g>
        </g>
        <g id="g308">
          <g id="g310" clip-path="url(#clipPath314)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,100.46,86.184)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text318"><tspan x="0" y="0" id="tspan316"><?php echo $amount_paid; ?></tspan></text>
          </g>
        </g>
       
       
        <g id="g344">
          <g id="g346" clip-path="url(#clipPath350)"></g>
        </g>
        <g id="g352">
          <g id="g354" clip-path="url(#clipPath358)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,37.8,74.904)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text362"><tspan x="0 6.4980001 12.501 18 24.003 39.096001" y="0" id="tspan360">DATE :</tspan></text>
          </g>
        </g>
        <g id="g364">
          <g id="g366" clip-path="url(#clipPath370)"></g>
        </g>
        <g id="g372">
          <g id="g374" clip-path="url(#clipPath378)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,81.864,74.904)" style="font-variant:normal;font-weight:normal;font-size:9px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text382"><tspan x="0 4.5599999" y="0" id="tspan380"><?php echo $pay_date; ?></tspan></text>
          </g>
        </g>
        
        <g id="g396">
          <g id="g398" clip-path="url(#clipPath402)"></g>
        </g>
        
        
        <g id="g428">
          <g id="g430" clip-path="url(#clipPath434)"></g>
        </g>
       
        
       
        <g id="g472">
          <g id="g474" clip-path="url(#clipPath478)"></g>
        </g>
       
        <g id="g492">
          <g id="g494" clip-path="url(#clipPath498)"></g>
        </g>
        <path d="m 135.5,72.744 h 49.704 v 1.08 H 135.5 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path500"></path>
        <g id="g502"></g>
       
        <g id="g516">
          <g id="g518" clip-path="url(#clipPath522)"></g>
        </g>
        <path d="m 37.8,61.224 h 23.304 v 1.08 H 37.8 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path524"></path>
        <g id="g526"></g>
        <g id="g528">
          <g id="g530" clip-path="url(#clipPath534)"></g>
        </g>
        <g id="g536"></g>
        <g id="g538">
          <g id="g540" clip-path="url(#clipPath544)"></g>
        </g>
        <g id="g546"></g>
        <g id="g548">
          <g id="g550" clip-path="url(#clipPath554)"></g>
        </g>
        <g id="g556"></g>
        <g id="g558">
          <g id="g560" clip-path="url(#clipPath564)"></g>
        </g>
        <g id="g566"></g>
        <g id="g568">
          <g id="g570" clip-path="url(#clipPath574)"></g>
        </g>
        <g id="g576"></g>
        <g id="g578">
          <g id="g580" clip-path="url(#clipPath584)"></g>
        </g>
        <g id="g586"></g>
      </g>
    </g>
    <path d="m 101.25,635.22 h 375.7 v 124.2 h -375.7 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path588"></path>
    <g id="g590">
      <g id="g592" clip-path="url(#clipPath596)">
        <g id="g598">
          <g id="g600" clip-path="url(#clipPath604)"></g>
        </g>
        <g id="g606">
          <g id="g608" clip-path="url(#clipPath612)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,218.45,730.78)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text616"><tspan x="0 8.6639996 15.336 22.068001 28.775999 35.484001 38.147999 40.764 46.764 50.099998 56.723999 60.060001 63.431999 66.767998 73.391998 80.099998 83.435997 91.379997 98.087997 100.752 103.368 106.032 112.704 119.436 121.956 128.664 135.37199" y="0" id="tspan614">Republic of the Philippines</tspan></text>
          </g>
        </g>
        <g id="g618">
          <g id="g620" clip-path="url(#clipPath624)"></g>
        </g>
        <g id="g626">
          <g id="g628" clip-path="url(#clipPath632)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,194.45,716.98)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text636"><tspan x="0 8.6639996 16.667999 24 27.336 36.708 45.372002 53.375999 60.096001 63.431999 71.375999 80.711998 87.444 90.779999 99.444 107.472 110.808 119.352 128.688 138.68401 148.632 151.968 160.02 168.048 171.384 180.756" y="0" id="tspan634">NATIONAL POLICE COMMISSION</tspan></text>
          </g>
        </g>
        <g id="g638">
          <g id="g640" clip-path="url(#clipPath644)"></g>
        </g>
        <g id="g646">
          <g id="g648" clip-path="url(#clipPath652)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,124.7,703.18)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text656"><tspan x="0 8.0279999 16.691999 20.028 27.360001 30.695999 38.736 46.764 50.099998 58.764 66.683998 70.019997 78.683998 87.348 94.751999 98.087997 107.46 115.98 124.644 131.94 135.276 143.328 152.664 159.996 163.356 172.02 180.024 183.384 186.72 195.384 198.72 206.664 210 217.332 220.668 228.73199 236.65199 245.31599 253.93201 262.59601 265.93201 273.35999 281.388 284.724 294.09601 302.76001 312.09601 320.76001" y="0" id="tspan654">PHILIPPINE NATIONAL POLICE, CIVIL SECURITY GROUP</tspan></text>
          </g>
        </g>
        <g id="g658">
          <g id="g660" clip-path="url(#clipPath664)"></g>
        </g>
        <g id="g666">
          <g id="g668" clip-path="url(#clipPath672)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,166.94,687.58)" style="font-variant:normal;font-weight:bold;font-size:14.04px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text676"><tspan x="0 10.179 19.54368 30.340441 34.285679 45.082439 55.26144 65.342163 73.864441 77.711403 87.890404 91.83564 101.07396 104.92092 113.54148 117.38844 126.75312 136.1178 146.18448 156.2652 166.4442 170.29115 178.91171 188.15004 192.09528 202.17599 212.25671 216.20197 224.72424 228.66948 236.34937" y="0" id="tspan674">REGIONAL CIVIL SECURITY UNIT 10</tspan></text>
          </g>
        </g>
        <g id="g678">
          <g id="g680" clip-path="url(#clipPath684)"></g>
        </g>
        <g id="g686">
          <g id="g688" clip-path="url(#clipPath692)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,161.42,673.18)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text696"><tspan x="0 8.6639996 15.336 25.427999 32.136002 35.375999 42.084 48.792 52.032001 55.368" y="0" id="tspan694">Camp 1Lt. </tspan></text>
          </g>
        </g>
        <g id="g698">
          <g id="g700" clip-path="url(#clipPath704)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,220.25,673.18)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text708"><tspan x="0 8.0279999 10.692 16.691999 23.268 29.976 33.312 40.043999 43.284 51.312 53.976002 60.551998 67.260002 73.968002 77.963997 81.300003 84.636002 93.300003 99.972 106.608 113.316 119.316 125.916 132.62399 135.96001 142.584 149.29201 152.62801 162 165.996 172.668 175.92 184.584 187.2 190.536" y="0" id="tspan706">Vicente Alagar, Cagayan de Oro City</tspan></text>
          </g>
        </g>
        <g id="g710">
          <g id="g712" clip-path="url(#clipPath716)"></g>
        </g>
      </g>
    </g>
    <g id="g718">
      <g id="g720" clip-path="url(#clipPath724)">
        <g id="g726">
          <g id="g728" clip-path="url(#clipPath732)"></g>
        </g>
        <g id="g734">
          <g id="g736" clip-path="url(#clipPath740)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,121.58,606.1)" style="font-variant:normal;font-weight:bold;font-size:15.96px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text744"><tspan x="0 10.64532 21.33852 31.983841 43.506962 47.94384 59.466961 69.218521 73.655403 84.30072 95.00988 106.533 119.95536 124.39224 134.2236 138.66048 148.41203 160.74911 172.27225 176.82085 186.5724 191.00928 202.53239 213.28944 228.29184 240.75661 252.27972 263.80283 274.44815 278.88504 290.40817 294.84503 305.53824 316.18356 326.04684 337.56995" y="0" id="tspan742">SPECIAL PERMIT FOR FIREWORKS DISPLAY</tspan></text>
          </g>
        </g>
        <g id="g746">
          <g id="g748" clip-path="url(#clipPath752)"></g>
        </g>
        <g id="g754">
          <g id="g756" clip-path="url(#clipPath760)"></g>
        </g>
        <g id="g762">
          <g id="g764" clip-path="url(#clipPath768)"></g>
        </g>
        <g id="g770">
          <g id="g772" clip-path="url(#clipPath776)"></g>
        </g>
        <g id="g778">
          <g id="g780" clip-path="url(#clipPath784)"></g>
        </g>
        <g id="g786">
          <g id="g788" clip-path="url(#clipPath792)"></g>
        </g>
        <g id="g794">
          <g id="g796" clip-path="url(#clipPath800)"></g>
        </g>
        <g id="g802">
          <g id="g804" clip-path="url(#clipPath808)"></g>
        </g>
        <g id="g810">
          <g id="g812" clip-path="url(#clipPath816)"></g>
        </g>
        <g id="g818">
          <g id="g820" clip-path="url(#clipPath824)"></g>
        </g>
        <g id="g826">
          <g id="g828" clip-path="url(#clipPath832)"></g>
        </g>
        <g id="g834">
          <g id="g836" clip-path="url(#clipPath840)"></g>
        </g>
        <g id="g842">
          <g id="g844" clip-path="url(#clipPath848)"></g>
        </g>
        <g id="g850">
          <g id="g852" clip-path="url(#clipPath856)"></g>
        </g>

        



        <g id="g926">
          <g id="g928" clip-path="url(#clipPath932)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,481.06,559.27)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text936"><tspan x="0" y="0" id="tspan934">.</tspan></text>
          </g>
        </g>
        <g id="g938">
          <g id="g940" clip-path="url(#clipPath944)"></g>
        </g>
        <g id="g946">
          <g id="g948" clip-path="url(#clipPath952)"></g>
        </g>
        <g id="g954">
          <g id="g956" clip-path="url(#clipPath960)">
            <g id="g962">
              <g id="g964" clip-path="url(#clipPath968)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,50.04,530.59)" style="font-style:italic;font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-ItalicMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text972"><tspan x="0 8.6639996 15.336 22.068001 25.403999 29.4 36.096001 38.759998 42.096001 50.759998 57.335999 60.576" y="0" id="tspan970">Control No. </tspan></text>
              </g>
            </g>
            <g id="g974">
              <g id="g976" clip-path="url(#clipPath980)"></g>
            </g>
          </g>
        </g>
        <g id="g982">
          <g id="g984" clip-path="url(#clipPath988)">
            <g id="g990">
              <g id="g992" clip-path="url(#clipPath996)"></g>
            </g>
          </g>
        </g>
        <g id="g998">
          <g id="g1000" clip-path="url(#clipPath1004)">
            <g id="g1006">
              <g id="g1008" clip-path="url(#clipPath1012)"></g>
            </g>
          </g>
        </g>
        <g id="g1014">
          <g id="g1016" clip-path="url(#clipPath1020)">
            <g id="g1022">
              <g id="g1024" clip-path="url(#clipPath1028)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,143.66,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1032"><tspan x="0 8.04" y="0" id="tspan1030"><?php echo $GLOBALS['cn1'];?><?php echo $GLOBALS['cn2'];?></tspan></text>
              </g>
            </g>
            <g id="g1034">
              <g id="g1036" clip-path="url(#clipPath1040)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,159.74,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1044"><tspan x="0" y="0" id="tspan1042"><?php echo $GLOBALS['cn3'];?></tspan></text>
              </g>
            </g>
            <g id="g1046">
              <g id="g1048" clip-path="url(#clipPath1052)"></g>
            </g>
          </g>
        </g>
        <g id="g1054">
          <g id="g1056" clip-path="url(#clipPath1060)">
            <g id="g1062">
              <g id="g1064" clip-path="url(#clipPath1068)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,174.5,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1072"><tspan x="0" y="0" id="tspan1070"><?php echo $GLOBALS['cn4'];?></tspan></text>
              </g>
            </g>
            <g id="g1074">
              <g id="g1076" clip-path="url(#clipPath1080)"></g>
            </g>
          </g>
        </g>
        <g id="g1082">
          <g id="g1084" clip-path="url(#clipPath1088)">
            <g id="g1090">
              <g id="g1092" clip-path="url(#clipPath1096)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,194.09,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1100"><tspan x="0" y="0" id="tspan1098"><?php echo $GLOBALS['cn5'];?></tspan></text>
              </g>
            </g>
            <g id="g1102">
              <g id="g1104" clip-path="url(#clipPath1108)"></g>
            </g>
          </g>
        </g>
        <g id="g1110">
          <g id="g1112" clip-path="url(#clipPath1116)">
            <g id="g1118">
              <g id="g1120" clip-path="url(#clipPath1124)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,213.53,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1128"><tspan x="0" y="0" id="tspan1126"><?php echo $GLOBALS['cn6'];?></tspan></text>
              </g>
            </g>
            <g id="g1130">
              <g id="g1132" clip-path="url(#clipPath1136)"></g>
            </g>
          </g>
        </g>
        <g id="g1138">
          <g id="g1140" clip-path="url(#clipPath1144)">
            <g id="g1146">
              <g id="g1148" clip-path="url(#clipPath1152)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,228.29,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1156"><tspan x="0" y="0" id="tspan1154"><?php echo $GLOBALS['cn7'];?></tspan></text>
              </g>
            </g>
            <g id="g1158">
              <g id="g1160" clip-path="url(#clipPath1164)"></g>
            </g>
          </g>
        </g>
        <g id="g1166">
          <g id="g1168" clip-path="url(#clipPath1172)">
            <g id="g1174">
              <g id="g1176" clip-path="url(#clipPath1180)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,245.81,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1184"><tspan x="0" y="0" id="tspan1182"><?php echo $GLOBALS['cn8'];?></tspan></text>
              </g>
            </g>
            <g id="g1186">
              <g id="g1188" clip-path="url(#clipPath1192)"></g>
            </g>
          </g>
        </g>
        <g id="g1194">
          <g id="g1196" clip-path="url(#clipPath1200)">
            <g id="g1202">
              <g id="g1204" clip-path="url(#clipPath1208)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,263.33,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1212"><tspan x="0" y="0" id="tspan1210"><?php echo $GLOBALS['cn9'];?></tspan></text>
              </g>
            </g>
            <g id="g1214">
              <g id="g1216" clip-path="url(#clipPath1220)"></g>
            </g>
          </g>
        </g>
        <g id="g1222">
          <g id="g1224" clip-path="url(#clipPath1228)">
            <g id="g1230">
              <g id="g1232" clip-path="url(#clipPath1236)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,280.85,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1240"><tspan x="0" y="0" id="tspan1238"><?php echo $GLOBALS['cn10'];?></tspan></text>
              </g>
            </g>
            <g id="g1242">
              <g id="g1244" clip-path="url(#clipPath1248)"></g>
            </g>
          </g>
        </g>
        <g id="g1250">
          <g id="g1252" clip-path="url(#clipPath1256)">
            <g id="g1258">
              <g id="g1260" clip-path="url(#clipPath1264)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,298.37,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1268"><tspan x="0" y="0" id="tspan1266"><?php echo $GLOBALS['cn11'];?></tspan></text>
              </g>
            </g>
            <g id="g1270">
              <g id="g1272" clip-path="url(#clipPath1276)"></g>
            </g>
          </g>
        </g>
        <g id="g1278">
          <g id="g1280" clip-path="url(#clipPath1284)">
            <g id="g1286">
              <g id="g1288" clip-path="url(#clipPath1292)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,313.15,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1296"><tspan x="0" y="0" id="tspan1294"><?php echo $GLOBALS['cn12'];?></tspan></text>
              </g>
            </g>
            <g id="g1298">
              <g id="g1300" clip-path="url(#clipPath1304)"></g>
            </g>
          </g>
        </g>
        <g id="g1306">
          <g id="g1308" clip-path="url(#clipPath1312)">
            <g id="g1314">
              <g id="g1316" clip-path="url(#clipPath1320)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,330.67,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1324"><tspan x="0" y="0" id="tspan1322"><?php echo $GLOBALS['cn13'];?></tspan></text>
              </g>
            </g>
            <g id="g1326">
              <g id="g1328" clip-path="url(#clipPath1332)"></g>
            </g>
          </g>
        </g>
        <g id="g1334">
          <g id="g1336" clip-path="url(#clipPath1340)">
            <g id="g1342">
              <g id="g1344" clip-path="url(#clipPath1348)">
                <text xml:space="preserve" transform="matrix(1,0,0,-1,348.19,530.59)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text1352"><tspan x="0" y="0" id="tspan1350"><?php echo $GLOBALS['cn14'];?></tspan></text>
              </g>
            </g>
            <g id="g1354">
              <g id="g1356" clip-path="url(#clipPath1360)"></g>
            </g>
          </g>
        </g>
        <path d="m 39.84,541.75 h 0.48 v 2.28 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1362"></path>
        <path d="m 39.84,543.55 h 0.48 v 0.48001 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1364"></path>
        <path d="m 40.32,543.55 h 75.504 v 0.48001 H 40.32 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1366"></path>
        <path d="m 115.82,541.75 h 0.48 v 1.8 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1368"></path>
        <path d="m 115.82,543.55 h 0.48 v 0.48001 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1370"></path>
        <path d="m 116.3,543.55 h 10.68 v 0.48001 H 116.3 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1372"></path>
        <path d="m 126.98,541.75 h 0.48 v 1.8 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1374"></path>
        <path d="m 126.98,543.55 h 0.48 v 0.48001 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1376"></path>
        <path d="m 127.46,543.55 h 9.72 v 0.48001 h -9.72 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1378"></path>
        <path d="m 137.18,541.75 h 2.16 v 2.28 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1380"></path>
        <path d="m 137.18,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1382"></path>
        <path d="m 139.34,541.87 h 29.52 v 2.16 h -29.52 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1384"></path>
        <path d="m 168.86,541.75 h 0.48 v 0.12 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1386"></path>
        <path d="m 168.86,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1388"></path>
        <path d="m 171.02,541.87 h 17.424 v 2.16 H 171.02 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1390"></path>
        <path d="m 188.45,541.75 h 0.48001 v 0.12 H 188.45 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1392"></path>
        <path d="m 188.45,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1394"></path>
        <path d="m 190.61,541.87 h 17.28 v 2.16 h -17.28 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1396"></path>
        <path d="m 207.89,541.75 h 0.48 v 0.12 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1398"></path>
        <path d="m 207.89,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1400"></path>
        <path d="m 210.05,541.87 h 12.72 v 2.16 h -12.72 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1402"></path>
        <path d="m 222.77,541.75 h 0.48 v 0.12 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1404"></path>
        <path d="m 222.77,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1406"></path>
        <path d="m 224.93,541.87 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1408"></path>
        <path d="m 240.29,541.75 h 0.48 v 0.12 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1410"></path>
        <path d="m 240.29,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1412"></path>
        <path d="m 242.45,541.87 h 15.24 v 2.16 h -15.24 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1414"></path>
        <path d="m 257.69,541.75 h 0.48001 v 0.12 H 257.69 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1416"></path>
        <path d="m 257.69,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1418"></path>
        <path d="m 259.85,541.87 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1420"></path>
        <path d="m 275.21,541.75 h 0.47998 v 0.12 H 275.21 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1422"></path>
        <path d="m 275.21,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1424"></path>
        <path d="m 277.37,541.87 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1426"></path>
        <path d="m 292.73,541.75 h 0.48001 v 0.12 H 292.73 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1428"></path>
        <path d="m 292.73,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1430"></path>
        <path d="m 294.89,541.87 h 12.6 v 2.16 h -12.6 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1432"></path>
        <path d="m 307.49,541.75 h 0.48001 v 0.12 H 307.49 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1434"></path>
        <path d="m 307.49,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1436"></path>
        <path d="m 309.65,541.87 h 15.384 v 2.16 H 309.65 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1438"></path>
        <path d="m 325.03,541.75 h 0.47998 v 0.12 H 325.03 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1440"></path>
        <path d="m 325.03,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1442"></path>
        <path d="m 327.19,541.87 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1444"></path>
        <path d="m 342.55,541.75 h 0.48001 v 0.12 H 342.55 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1446"></path>
        <path d="m 342.55,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1448"></path>
        <path d="m 344.71,541.87 h 14.52 v 2.16 h -14.52 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1450"></path>
        <path d="m 359.23,541.75 h 2.16 v 2.28 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1452"></path>
        <path d="m 359.23,541.87 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1454"></path>
        <path d="m 39.84,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1456"></path>
        <path d="m 39.84,527.47 h 0.48 v 0.47998 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1458"></path>
        <path d="m 39.84,527.47 h 0.48 v 0.47998 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1460"></path>
        <path d="m 40.32,527.47 h 75.504 v 0.47998 H 40.32 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1462"></path>
        <path d="m 115.82,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1464"></path>
        <path d="m 115.82,527.47 h 0.48 v 0.47998 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1466"></path>
        <path d="m 116.3,527.47 h 10.68 v 0.47998 H 116.3 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1468"></path>
        <path d="m 126.98,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1470"></path>
        <path d="m 126.98,527.47 h 0.48 v 0.47998 h -0.48 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1472"></path>
        <path d="m 127.46,527.47 h 9.72 v 0.47998 h -9.72 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1474"></path>
        <path d="m 137.18,527.95 h 2.16 v 13.8 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1476"></path>
        <path d="m 137.18,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1478"></path>
        <path d="m 137.18,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1480"></path>
        <path d="m 139.34,525.79 h 29.52 v 2.16 h -29.52 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1482"></path>
        <path d="m 168.86,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1484"></path>
        <path d="m 168.86,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1486"></path>
        <path d="m 171.02,525.79 h 17.424 v 2.16 H 171.02 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1488"></path>
        <path d="m 188.45,527.95 h 0.48001 v 13.8 H 188.45 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1490"></path>
        <path d="m 188.45,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1492"></path>
        <path d="m 190.61,525.79 h 17.28 v 2.16 h -17.28 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1494"></path>
        <path d="m 207.89,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1496"></path>
        <path d="m 207.89,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1498"></path>
        <path d="m 210.05,525.79 h 12.72 v 2.16 h -12.72 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1500"></path>
        <path d="m 222.77,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1502"></path>
        <path d="m 222.77,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1504"></path>
        <path d="m 224.93,525.79 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1506"></path>
        <path d="m 240.29,527.95 h 0.48 v 13.8 h -0.48 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1508"></path>
        <path d="m 240.29,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1510"></path>
        <path d="m 242.45,525.79 h 15.24 v 2.16 h -15.24 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1512"></path>
        <path d="m 257.69,527.95 h 0.48001 v 13.8 H 257.69 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1514"></path>
        <path d="m 257.69,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1516"></path>
        <path d="m 259.85,525.79 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1518"></path>
        <path d="m 275.21,527.95 h 0.47998 v 13.8 H 275.21 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1520"></path>
        <path d="m 275.21,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1522"></path>
        <path d="m 277.37,525.79 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1524"></path>
        <path d="m 292.73,527.95 h 0.48001 v 13.8 H 292.73 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1526"></path>
        <path d="m 292.73,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1528"></path>
        <path d="m 294.89,525.79 h 12.6 v 2.16 h -12.6 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1530"></path>
        <path d="m 307.49,527.95 h 0.48001 v 13.8 H 307.49 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1532"></path>
        <path d="m 307.49,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1534"></path>
        <path d="m 309.65,525.79 h 15.384 v 2.16 H 309.65 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1536"></path>
        <path d="m 325.03,527.95 h 0.47998 v 13.8 H 325.03 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1538"></path>
        <path d="m 325.03,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1540"></path>
        <path d="m 327.19,525.79 h 15.36 v 2.16 h -15.36 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1542"></path>
        <path d="m 342.55,527.95 h 0.48001 v 13.8 H 342.55 Z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1544"></path>
        <path d="m 342.55,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1546"></path>
        <path d="m 344.71,525.79 h 14.52 v 2.16 h -14.52 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1548"></path>
        <path d="m 359.23,527.95 h 2.16 v 13.8 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1550"></path>
        <path d="m 359.23,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1552"></path>
        <path d="m 359.23,525.79 h 2.16 v 2.16 h -2.16 z" style="fill:#333333;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path1554"></path>
        <g id="g1556">
          <g id="g1558" clip-path="url(#clipPath1562)"></g>
        </g>
        <g id="g1564">
          <g id="g1566" clip-path="url(#clipPath1570)"></g>
        </g>
        <g id="g1572">
          <g id="g1574" clip-path="url(#clipPath1578)"></g>
        </g>






































        <g>
  <g clip-path="url(#clipPath1586)">
    <text xml:space="preserve"
          transform="matrix(1,0,0,-1,50.00,489.19)"
          style="font-variant:normal;font-weight:normal;font-size:12px;
                 font-family:Arial;-inkscape-font-specification:ArialMT;
                 writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none"
          id="text1599">
    </text>
  </g>
</g>




<g id="g2220">
          <g id="g2222" clip-path="url(#clipPath2226)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,86.064,252.17)" style="font-variant:normal;font-weight:light;font-size:11.04px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2230"><tspan x="0 7.31952 11.02896 17.1672 20.843519 23.97888 30.724319" y="0" id="tspan2228"></tspan></text>
          </g>
        </g>

<g id="g858">
          <g id="g860" clip-path="url(#clipPath864)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,409.63,559.27)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text868"><tspan x="0 6" y="0" id="tspan866"></tspan></text>
          </g>
        </g>




    <script>
        const input = document.getElementById('qrcodeinput');
        const qrcodeGroup = document.getElementById('qrcode-group');
        const tempQR = document.getElementById('temp-qr');
        
        input.addEventListener('input', function() {
            const text = this.value.trim();
            
            // Clear previous QR code
            qrcodeGroup.innerHTML = '';
            tempQR.innerHTML = '';
            
            if (text) {
                // Generate QR code in temporary container
                const qrcode = new QRCode(tempQR, {
                    text: text,
                    width: 120,
                    height: 120,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                // Wait for QR code to generate
                setTimeout(() => {
                    const canvas = tempQR.querySelector('canvas');
                    if (canvas) {
                        // Convert canvas to data URL
                        const dataURL = canvas.toDataURL('image/png');
                        
                        // Create SVG image element
                        const svgNS = 'http://www.w3.org/2000/svg';
                        const svgImage = document.createElementNS(svgNS, 'image');
                        svgImage.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', dataURL);
                        svgImage.setAttribute('width', '120');
                        svgImage.setAttribute('height', '120');
                        svgImage.setAttribute('x', '0');
                        svgImage.setAttribute('y', '0');
                        
                        qrcodeGroup.appendChild(svgImage);
                    }
                }, 100);
            } else {
               
            }
        });

          function runthisqronce(){
            const text = input.value.trim();
            
            // Clear previous QR code
            qrcodeGroup.innerHTML = '';
            tempQR.innerHTML = '';
            
            if (text) {
                // Generate QR code in temporary container
                const qrcode = new QRCode(tempQR, {
                    text: text,
                    width: 120,
                    height: 120,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                // Wait for QR code to generate
                setTimeout(() => {
                    const canvas = tempQR.querySelector('canvas');
                    if (canvas) {
                        // Convert canvas to data URL
                        const dataURL = canvas.toDataURL('image/png');
                        
                        // Create SVG image element
                        const svgNS = 'http://www.w3.org/2000/svg';
                        const svgImage = document.createElementNS(svgNS, 'image');
                        svgImage.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', dataURL);
                        svgImage.setAttribute('width', '120');
                        svgImage.setAttribute('height', '120');
                        svgImage.setAttribute('x', '0');
                        svgImage.setAttribute('y', '0');
                        
                        qrcodeGroup.appendChild(svgImage);
                    }
                }, 100);
            } else {
               
            }
            }
            runthisqronce();


function renderSvgTextLineWrap(elementId, parts, maxWidth = 480, lineHeight = 25) {
    const svgText = document.getElementById(elementId);
    if (!svgText) return;

    const svgNS = "http://www.w3.org/2000/svg";

    const tempText = document.createElementNS(svgNS, "text");
    tempText.setAttribute("style", svgText.getAttribute("style"));
    document.querySelector("svg").appendChild(tempText);

    let dy = 0;
    let line = [];
    let lineWidth = 0;

    function flushLine() {
        if (line.length > 0) {
            let tspan = document.createElementNS(svgNS, "tspan");
            tspan.setAttribute("x", 0);
            tspan.setAttribute("dy", dy === 0 ? 0 : lineHeight);

            line.forEach(segment => {
                let segTspan = document.createElementNS(svgNS, "tspan");
                if (segment.bold) segTspan.setAttribute("font-weight", "bold");
                segTspan.textContent = segment.text;
                tspan.appendChild(segTspan);
            });

            svgText.appendChild(tspan);
            dy++;
            line = [];
            lineWidth = 0;
        }
    }

    parts.forEach(part => {
        let words = part.text.split(" ");
        words.forEach((word) => {
            let testLine = line.map(seg => seg.text).join(" ") + (line.length ? " " : "") + word;
            tempText.textContent = testLine;
            let length = tempText.getComputedTextLength();

            if (length > maxWidth && line.length) {
                flushLine();
            }
            line.push({ text: (line.length ? " " : "") + word, bold: part.bold });
        });
    });

    flushLine();
    tempText.remove();
}




const fireworksPermitStatement = [
  { text: "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Authority is hereby granted to", bold: false },
  { text: "<?php echo $dealer_name; ?>", bold: true },
  { text: "of", bold: false },
  { text: "<?php echo $company_name; ?>", bold: true },
  { text: "with office address at" },
  { text: "<?php echo $company_address; ?>", bold: true },
  { text: "to display fireworks on <?php echo $display_date; ?> from <?php echo $display_time; ?>" },
  { text: "onwards in connection with the celebration of " },
  { text: "“<?php echo $display_purpose; ?>”", bold: true },
  { text: "held at", bold: false },
  { text: "<?php echo $display_location; ?>", bold: true },
  { text: "which will be handled by", bold: false },
  { text: "<?php echo $dealer_name; ?>,", bold: true },
  { text: "a licensed Pyro Technician with", bold: false },
  { text: "FDO License No. <?php echo $fdo_license_number; ?>.", bold: true },
];

renderSvgTextLineWrap("text1599", fireworksPermitStatement);


const partner_police_station = [
  { text: "<?php echo $partner_police_station; ?>", bold: true },
  { text: "or assignment of security personnel and Bureau of Fire Protection for deployment of appropriate firefighting equipment.", bold: false },
]

renderSvgTextLineWrap("text2230", partner_police_station, 455,20);


const apply_date = [
  { text: "<?php echo $apply_date; ?>", bold: false },
]
 


renderSvgTextLineWrap("text868", apply_date, 455,20);

</script>





       

























































        <g id="g2068">
          <g id="g2070" clip-path="url(#clipPath2074)"></g>
        </g>
        <g id="g2076">
          <g id="g2078" clip-path="url(#clipPath2082)"></g>
        </g>
        <g id="g2084">
          <g id="g2086" clip-path="url(#clipPath2090)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,72.504,346.97)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2094"><tspan x="0 6.74544 12.83952 15.2352 20.755199 23.868481 30.00672 36.111839 39.225121 45.363361 51.468479 55.177921 57.573601 63.093601 69.088318 72.201599 74.597282 80.735519 86.840637 89.953918 92.349602 97.869598 100.98288 106.50288 112.64112 118.6248 121.02048 127.15872 132.67873 135.74783 138.78384 141.89713 148.03535 151.01616 154.12944 160.26768 166.3728 169.37569 172.48895 178.6272 181.01184 183.40752 189.54576 197.45039 199.84608 205.98431 212.08945 215.20271 220.72272 226.86096 232.96608 239.10432 241.48895 244.60223 246.99792 253.13615 259.24127 264.76129" y="0" id="tspan2092">This authorization is subject to the following conditions:</tspan></text>
          </g>
        </g>
        <g id="g2096">
          <g id="g2098" clip-path="url(#clipPath2102)"></g>
        </g>
        <g id="g2104">
          <g id="g2106" clip-path="url(#clipPath2110)"></g>
        </g>
        <g id="g2112">
          <g id="g2114" clip-path="url(#clipPath2118)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,72.504,309.05)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2122"><tspan x="0 6.1382399 9.2073603 15.35664 22.102079 28.196159 34.3344 37.315201 40.428478 46.566719 52.671841 55.06752 60.587521 63.700802 69.839043 72.223679 74.619362 80.757599 88.662239 94.800484 100.9056 103.30128 109.43952 112.50864 115.65504 118.05072 121.64976 127.788 135.69264 141.83089 145.5072 151.02721 156.5472 159.68256 167.59824 169.99393 173.10719 179.10191 182.21519 184.61089 187.00656 189.40224 195.54048 204.7368 207.18768 213.28175 219.42 222.48912 224.94 231.04512 237.18336 240.16415 246.3024 249.37152 252.51791 258.65616 264.03265 267.14594 270.25919 275.77921 281.77393 287.91217 290.29681 292.69247 295.80576 301.944 308.04913 311.16241 317.30063 322.82065 328.92575 334.92047" y="0" id="tspan2120">1. That only allowable fireworks with illuminating effect shall be used.</tspan></text>
          </g>
        </g>
        <g id="g2124">
          <g id="g2126" clip-path="url(#clipPath2130)"></g>
        </g>
        <g id="g2132">
          <g id="g2134" clip-path="url(#clipPath2138)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,72.504,290.09)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2142"><tspan x="0 6.1382399 9.2073603" y="0" id="tspan2140">2. </tspan></text>
          </g>
        </g>
        <g id="g2144">
          <g id="g2146" clip-path="url(#clipPath2150)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,87.864,290.09)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2154"><tspan x="0 6.74544 12.83952 18.97776 21.958561 25.07184 28.18512 34.32336 40.307041 43.420319 46.5336 48.929279 52.638721 58.776958 66.681602 72.676323 76.385757 81.905762 87.304321 90.417603 93.530884 99.525597 102.52848 108.66672 114.77184 117.88512 124.02336 129.54337 135.64848 141.78671 144.85583 150.37584 156.51408 162.65231 165.048 167.44368 170.55696 176.69521 182.67888 185.79216 191.9304 198.03552 201.63457 207.15456 213.2928 219.39792 224.79648 230.93472 237.03984 240.15312 243.15601 246.86543 253.00368 262.10065 265.21393 267.60959 270.00528 275.52527 281.66351 287.76865 293.28864 299.42688 305.53201 308.64529 314.78351 320.88864 327.02689 329.41153 335.54977 339.12671 342.12961 351.35904 357.49728 363.60239 369.74063 372.80975 378.948 384.3576 387.47089 393.60913 397.28543 403.31329 407.02271 410.13599 416.27423 422.37936 424.77505 430.17361" y="0" id="tspan2152">That the fireworks to be used shall be purchased from licensed dealer/manufacturer only.</tspan></text>
          </g>
        </g>
        <g id="g2156">
          <g id="g2158" clip-path="url(#clipPath2162)"></g>
        </g>
        <g id="g2164"></g>
        <g id="g2166">
          <g id="g2168" clip-path="url(#clipPath2172)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,72.504,271.13)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2176"><tspan x="0 6.1382399 9.2073603 13.54608 20.291519 26.385599 32.380322 35.383202 39.688801 45.827042 51.932159 55.045441 61.040161 64.749603 70.744324 75.049919 78.1632 84.301437 90.285118 94.590721 100.72896 106.10544 109.21872 115.35696 121.34064 123.73632 128.04192 131.1552 133.55087 137.26031 143.39856 151.30321 157.44144 161.0184 166.53841 172.0584 176.364 182.50224 184.88689 190.40688 196.54512 198.92976 205.06799 210.44447 213.55775 217.74191 220.85519 226.99344 232.97713 237.28271 243.27744 246.98688 253.12512 259.23026 265.36847 267.75311 273.27313 279.41135 282.98831 287.29391 292.81393 298.95215 305.05728 307.45297 309.84863 314.15424 319.67422 325.81247 331.79617 335.50562 341.64383 344.02847 350.16672 356.27185 359.38513 365.37985 369.68542 377.60114 379.9968 383.11008 389.1048 393.4104 396.52368 402.66193" y="0" id="tspan2174">3. That before the actual fireworks display, the organizer shall coordinate with the</tspan></text>
          </g>
        </g>
        <g id="g2178">
          <g id="g2180" clip-path="url(#clipPath2184)"></g>
        </g>
       
        
        <g id="g2218"></g>
        
        <g id="g2232">
          <g id="g2234" clip-path="url(#clipPath2238)"></g>
        </g>

        

       
        <g id="g2264"></g>
       
        <g id="g2278">
          <g id="g2280" clip-path="url(#clipPath2284)"></g>
        </g>
        <g id="g2286"></g>
        <g id="g2288">
          <g id="g2290" clip-path="url(#clipPath2294)"></g>
        </g>
        <g id="g2296">
          <g id="g2298" clip-path="url(#clipPath2302)"></g>
        </g>
        <g id="g2304">
          <g id="g2306" clip-path="url(#clipPath2310)"></g>
        </g>
        <g id="g2312">
          <g id="g2314" clip-path="url(#clipPath2318)"></g>
        </g>
        <g id="g2320">
          <g id="g2322" clip-path="url(#clipPath2326)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,332.47,175.22)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2330"><tspan x="0 7.3319998 16.667999 25.332001 28.559999 35.987999 44.652 52.655998 56.015999 64.68 68.015999 76.68 84.683998 93.216003 100.548 109.884 118.548 121.884 125.244 133.908 141.912 151.272" y="0" id="tspan2328">FOR THE DIRECTOR, CSG:</tspan></text>
          </g>
        </g>
        <g id="g2332">
          <g id="g2334" clip-path="url(#clipPath2338)"></g>
        </g>
        <g id="g2340">
          <g id="g2342" clip-path="url(#clipPath2346)"></g>
        </g>
        <g id="g2348">
          <g id="g2350" clip-path="url(#clipPath2354)"></g>
        </g>
        <g id="g2356">
          <g id="g2358" clip-path="url(#clipPath2362)"></g>
        </g>
        <g id="g2364">
          <g id="g2366" clip-path="url(#clipPath2370)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,333.55,133.82)" style="font-variant:normal;font-weight:bold;font-size:12px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2374"><tspan x="0 9.3360004 16.667999 20.028 28.056 36.084 44.748001 48.084 56.112 59.352001 62.688 71.351997 80.015999 88.019997 96.683998 104.016 112.02" y="0" id="tspan2372">OLIVER S. NAVALES</tspan></text>
          </g>
        </g>
        <g id="g2376">
          <g id="g2378" clip-path="url(#clipPath2382)"></g>
        </g>
        <g id="g2384">
          <g id="g2386" clip-path="url(#clipPath2390)"></g>
        </g>
        <g id="g2392">
          <g id="g2394" clip-path="url(#clipPath2398)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,333.55,120.98)" style="font-variant:normal;font-weight:normal;font-size:11.04px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2402"><tspan x="0 7.31952 13.45776 15.8424 18.238079 23.75808 29.89632 32.965439 40.936321 47.041439 49.437119 55.575359 61.680481 67.818718" y="0" id="tspan2400">Police Colonel</tspan></text>
          </g>
        </g>
        <g id="g2404">
          <g id="g2406" clip-path="url(#clipPath2410)"></g>
        </g>
        <g id="g2412">
          <g id="g2414" clip-path="url(#clipPath2418)"></g>
        </g>
        <g id="g2420">
          <g id="g2422" clip-path="url(#clipPath2426)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,333.55,107.42)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2430"><tspan x="0" y="0" id="tspan2428">C</tspan></text>
          </g>
        </g>
        <g id="g2432">
          <g id="g2434" clip-path="url(#clipPath2438)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,342.19,107.42)" style="font-variant:normal;font-weight:normal;font-size:12px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2442"><tspan x="0 3.336 6.7080002 15.372 23.988001 32.015999 40.68 44.015999 50.723999" y="0" id="tspan2440">, RCSU 10</tspan></text>
          </g>
        </g>
        <g id="g2444">
          <g id="g2446" clip-path="url(#clipPath2450)"></g>
        </g>
        <g id="g2452">
          <g id="g2454" clip-path="url(#clipPath2458)"></g>
        </g>
        <g id="g2460">
          <g id="g2462" clip-path="url(#clipPath2466)"></g>
        </g>
        <g id="g2468">
          <g id="g2470" clip-path="url(#clipPath2474)"></g>
        </g>
      </g>
    </g>
    <path d="M 483.9,715.52 H 565 v 83.55 h -81.1 z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2476"></path>
    <path d="M 483.9,715.52 H 565 v 83.55 h -81.1 z" style="fill:none;stroke:#ffffff;stroke-width:0.75;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" id="path2478"></path>
    <g id="g2480">
      <g id="g2482" clip-path="url(#clipPath2486)">
        <g id="g2488">
          <g id="g2490" clip-path="url(#clipPath2494)"></g>
        </g>
      </g>
    </g>
    <path d="m 404.6,783.17 h 170.5 v 25.3 H 404.6 Z" style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2496"></path>
    <path d="m 404.6,783.17 h 170.5 v 25.3 H 404.6 Z" style="fill:none;stroke:#ffffff;stroke-width:0.75;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" id="path2498"></path>
    <g id="g2500">
      <g id="g2502" clip-path="url(#clipPath2506)">
        <g id="g2508">
          <g id="g2510" clip-path="url(#clipPath2514)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,412.27,791.28)" style="font-variant:normal;font-weight:bold;font-size:14.04px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#ff0000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2518"><tspan x="0 10.92312 21.003839 24.94908 35.8722 39.70512 49.884121 59.838482 68.459038 72.306 82.485001 93.408119 102.64644" y="0" id="tspan2516">ORIGINAL COPY</tspan></text>
          </g>
        </g>
        <g id="g2520">
          <g id="g2522" clip-path="url(#clipPath2526)"></g>
        </g>
      </g>
    </g>
    <g id="g2528">
      <g id="g2530" clip-path="url(#clipPath2534)">
        <g id="g2536">
          <g id="g2538" clip-path="url(#clipPath2542)"></g>
        </g>
        <g id="g2544">
          <g id="g2546" clip-path="url(#clipPath2550)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,34.8,799.56)" style="font-variant:normal;font-weight:normal;font-size:6.96px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2554"><tspan x="0 4.6701598 9.6952801 14.38632 16.321199 21.346319 26.016479" y="0" id="tspan2552">PNP CSG</tspan></text>
          </g>
        </g>
        <g id="g2556">
          <g id="g2558" clip-path="url(#clipPath2562)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,66.264,799.56)" style="font-variant:normal;font-weight:normal;font-size:6.96px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2566"><tspan x="0" y="0" id="tspan2564">-</tspan></text>
          </g>
        </g>
        <g id="g2568">
          <g id="g2570" clip-path="url(#clipPath2574)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,68.544,799.56)" style="font-variant:normal;font-weight:normal;font-size:6.96px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2578"><tspan x="0 5.0251198 10.07112 14.74128 19.89168 21.826559 25.65456 29.60784 31.542721 33.44976 37.764961 43.17984 48.316319 54.079201 56.01408 59.49408 63.433441 65.716316 67.261436 71.131203 74.708641 76.643517 80.471519 84.424797 88.378082" y="0" id="tspan2576">RCSU 10, FORM series 2002</tspan></text>
          </g>
        </g>
        <g id="g2580">
          <g id="g2582" clip-path="url(#clipPath2586)"></g>
        </g>
        <g id="g2588">
          <g id="g2590" clip-path="url(#clipPath2594)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,34.8,791.52)" style="font-variant:normal;font-weight:normal;font-size:6.96px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2598"><tspan x="0 4.6701598 8.1501598 11.99208 13.5372 17.40696 20.886959 22.432079 26.023439 29.86536 31.80024 35.628239 37.173359 40.778641 44.62056 48.573841 52.053841 55.89576" y="0" id="tspan2596">Explosive License </tspan></text>
          </g>
        </g>
        <g id="g2600">
          <g id="g2602" clip-path="url(#clipPath2606)"></g>
        </g>
        <g id="g2608">
          <g id="g2610" clip-path="url(#clipPath2614)"></g>
        </g>
        <g id="g2616">
          <g id="g2618" clip-path="url(#clipPath2622)"></g>
        </g>
        <g id="g2624">
          <g id="g2626" clip-path="url(#clipPath2630)"></g>
        </g>
        <g id="g2632">
          <g id="g2634" clip-path="url(#clipPath2638)"></g>
        </g>
        <g id="g2640">
          <g id="g2642" clip-path="url(#clipPath2646)"></g>
        </g>
        <g id="g2648">
          <g id="g2650" clip-path="url(#clipPath2654)"></g>
        </g>
        <g id="g2656">
          <g id="g2658" clip-path="url(#clipPath2662)"></g>
        </g>
        <g id="g2664">
          <g id="g2666" clip-path="url(#clipPath2670)"></g>
        </g>
        <g id="g2672">
          <g id="g2674" clip-path="url(#clipPath2678)"></g>
        </g>
        <g id="g2680">
          <g id="g2682" clip-path="url(#clipPath2686)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,34.8,724.54)" style="font-variant:normal;font-weight:bold;font-size:6px;font-family:Arial;-inkscape-font-specification:Arial-BoldMT;writing-mode:lr-tb;fill:#333333;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2690"><tspan x="0 3.96 7.3140001 10.668 12.702 14.37 18.096001 21.695999" y="0" id="tspan2688">Section </tspan></text>
          </g>
        </g>
        <g id="g2692">
          <g id="g2694" clip-path="url(#clipPath2698)"></g>
        </g>
        <g id="g2700">
          <g id="g2702" clip-path="url(#clipPath2706)">
            <text xml:space="preserve" transform="matrix(1,0,0,-1,34.8,717.7)" style="font-variant:normal;font-weight:normal;font-size:6px;font-family:Arial;-inkscape-font-specification:ArialMT;writing-mode:lr-tb;fill:#333333;fill-opacity:1;fill-rule:nonzero;stroke:none" id="text2710"><tspan x="0 3.3540001 4.6859999 7.6859999 11.022 14.388 17.388 20.742001 22.41 25.775999 29.129999 32.484001 34.152 38.124001 41.363998 43.397999 48.431999 49.764 51.431999 53.099998 57.071999 60.425999 63.425999 65.094002 66.426003 69.779999" y="0" id="tspan2708">License and Permit Section</tspan></text>
          </g>
        </g>
        <g id="g2712">
          <g id="g2714" clip-path="url(#clipPath2718)"></g>
        </g>
        <g id="g2720">
          <g id="g2722" clip-path="url(#clipPath2726)"></g>
        </g>
      </g>
    </g>
   

    <g id="g2944">
      <g id="g2946" clip-path="url(#clipPath2950)">
        <g id="g2952" transform="matrix(94.15,0,0,93.75,463.5,667.07)">
          <image width="1" height="1" preserveAspectRatio="none" transform="matrix(1,0,0,-1,0,1)" xlink:href="data:image/png;base64,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" mask="url(#mask2954)" id="image2958"></image>
        </g>
      </g>
    </g>
    <path d="m 24,812.04 h 0.72 v 5.88 H 24 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2960"></path>
    <path d="m 24,817.2 h 5.88 v 0.72 H 24 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2962"></path>
    <path d="m 24.72,812.04 h 0.72 v 5.16 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2964"></path>
    <path d="m 24.72,816.48 h 5.16 v 0.72 h -5.16 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2966"></path>
    <path d="m 25.44,812.04 h 3 v 4.44 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2968"></path>
    <path d="m 25.44,813.48 h 4.44 v 3 h -4.44 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2970"></path>
    <path d="m 28.44,812.04 h 0.72 v 1.44 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2972"></path>
    <path d="m 28.44,812.76 h 1.44 v 0.72 h -1.44 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2974"></path>
    <path d="m 29.16,812.04 h 0.72 v 0.72 h -0.72 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2976"></path>
    <path d="m 29.16,812.04 h 0.72 v 0.72 h -0.72 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2978"></path>
    <path d="m 29.88,817.2 h 535.68 v 0.72 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2980"></path>
    <path d="m 29.88,816.48 h 535.68 v 0.72 H 29.88 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2982"></path>
    <path d="m 29.88,813.48 h 535.68 v 3 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2984"></path>
    <path d="m 29.88,812.76 h 535.68 v 0.72 H 29.88 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2986"></path>
    <path d="m 29.88,812.04 h 535.68 v 0.72 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2988"></path>
    <path d="m 570.72,812.04 h 0.71997 v 5.88 H 570.72 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2990"></path>
    <path d="m 565.56,817.2 h 5.88 v 0.72 h -5.88 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2992"></path>
    <path d="m 570,812.04 h 0.72003 v 5.16 H 570 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2994"></path>
    <path d="m 565.56,816.48 h 5.16 v 0.72 h -5.16 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2996"></path>
    <path d="m 567,812.04 h 3 v 4.44 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path2998"></path>
    <path d="M 565.56,813.48 H 570 v 3 h -4.44 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3000"></path>
    <path d="m 566.28,812.04 h 0.71997 v 1.44 H 566.28 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3002"></path>
    <path d="M 565.56,812.76 H 567 v 0.72 h -1.44 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3004"></path>
    <path d="m 565.56,812.04 h 0.72003 v 0.72 H 565.56 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3006"></path>
    <path d="m 565.56,812.04 h 0.72003 v 0.72 H 565.56 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3008"></path>
    <path d="m 24,29.76 h 0.72 V 812.04 H 24 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3010"></path>
    <path d="m 24.72,29.76 h 0.72 v 782.28 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3012"></path>
    <path d="m 25.44,29.76 h 3 v 782.28 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3014"></path>
    <path d="m 28.44,29.76 h 0.72 v 782.28 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3016"></path>
    <path d="m 29.16,29.76 h 0.72 v 782.28 h -0.72 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3018"></path>
    <path d="m 570.72,29.76 h 0.71997 V 812.04 H 570.72 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3020"></path>
    <path d="m 570,29.76 h 0.72003 V 812.04 H 570 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3022"></path>
    <path d="m 567,29.76 h 3 v 782.28 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3024"></path>
    <path d="m 566.28,29.76 h 0.71997 V 812.04 H 566.28 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3026"></path>
    <path d="m 565.56,29.76 h 0.72003 V 812.04 H 565.56 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3028"></path>
    <path d="m 24,23.88 h 0.72 v 5.8799 H 24 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3030"></path>
    <path d="m 24,23.88 h 5.88 v 0.71997 H 24 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3032"></path>
    <path d="m 24.72,24.6 h 0.72 v 5.16 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3034"></path>
    <path d="m 24.72,24.6 h 5.16 v 0.71997 h -5.16 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3036"></path>
    <path d="m 25.44,25.32 h 3 v 4.44 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3038"></path>
    <path d="m 25.44,25.32 h 4.44 v 3 h -4.44 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3040"></path>
    <path d="m 28.44,28.32 h 0.72 v 1.44 h -0.72 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3042"></path>
    <path d="m 28.44,28.32 h 1.44 v 0.72003 h -1.44 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3044"></path>
    <path d="m 29.16,29.04 h 0.72 v 0.71997 h -0.72 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3046"></path>
    <path d="m 29.16,29.04 h 0.72 v 0.71997 h -0.72 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3048"></path>
    <path d="m 29.88,23.88 h 535.68 v 0.71997 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3050"></path>
    <path d="m 29.88,24.6 h 535.68 v 0.71997 H 29.88 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3052"></path>
    <path d="m 29.88,25.32 h 535.68 v 3 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3054"></path>
    <path d="m 29.88,28.32 h 535.68 v 0.72003 H 29.88 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3056"></path>
    <path d="m 29.88,29.04 h 535.68 v 0.71997 H 29.88 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3058"></path>
    <path d="m 570.72,23.88 h 0.71997 v 5.8799 H 570.72 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3060"></path>
    <path d="m 565.56,23.88 h 5.88 v 0.71997 h -5.88 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3062"></path>
    <path d="m 570,24.6 h 0.72003 v 5.16 H 570 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3064"></path>
    <path d="m 565.56,24.6 h 5.16 v 0.71997 h -5.16 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3066"></path>
    <path d="m 567,25.32 h 3 v 4.44 h -3 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3068"></path>
    <path d="M 565.56,25.32 H 570 v 3 h -4.44 z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3070"></path>
    <path d="m 566.28,28.32 h 0.71997 v 1.44 H 566.28 Z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3072"></path>
    <path d="M 565.56,28.32 H 567 v 0.72003 h -1.44 z" style="fill:#262626;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3074"></path>
    <path d="m 565.56,29.04 h 0.72003 v 0.71997 H 565.56 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3076"></path>
    <path d="m 565.56,29.04 h 0.72003 v 0.71997 H 565.56 Z" style="fill:#000000;fill-opacity:1;fill-rule:evenodd;stroke:none" id="path3078"></path>
  </g>
</svg>

</div>


</div>


<script>
//            function backtostatus(status) {
//     let backstatus = '';

//     // Normalize status to ensure case-insensitive comparison
//     switch (status.toLowerCase()) {
//         case 'pending':
//             backstatus = 'pending_apps';
//             break;
//         case 'under review':
//             backstatus = 'underreview_apps';
//             break;
//         case 'drafting permit':
//             backstatus = 'draftingpermit_apps';
//             break;
//         case 'endorsed to director':
//             backstatus = 'endorsedtodirector_apps';
//             break;
//         case 'for final approval':
//             backstatus = 'forfinalapproval_apps';
//             break;
//         case 'permit issued':
//             backstatus = 'permitissued_apps';
//             break;
//         case 'rejected':
//             backstatus = 'rejected_apps';
//             break;
//         default:
//             alert('Unknown status: ' + status);
//             return;
//     }

//         window.location.href = backstatus;

// }



   // Countdown and auto-refresh functionality
let timeLeft = 10;
let countdownInterval = null;
const countdownElement = document.getElementById('countdown');

function startCountdown() {
    // Only start countdown if the modal is visible
   
    
    countdownInterval = setInterval(() => {
        timeLeft--;
        if (countdownElement) {
            countdownElement.textContent = timeLeft;
        }
        
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            location.href = 'all';
        }
    }, 1000);
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


  
function showModal(idToShow) {
    const overlay = document.getElementById('overlayCon');
    const modals = [
        'successendorsed', 'successrejected'
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








// function downloadPdf() {
//     const element = document.getElementById('svg2');
//     const options = {
//         margin:       0, // no margins at all
//         filename:     'document.pdf',
//         image:        { type: 'jpeg', quality: 0.98 },
//         html2canvas:  { scale: 1 },
//         jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
//     };
//     html2pdf().set(options).from(element).save();
// }
</script>



</body></html>