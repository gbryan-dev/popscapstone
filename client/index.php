<?php
session_start();
include '../db_conn.php'; // adjust path if needed
include '../env.php';

if (!isset($_SESSION['client_id'])) {
    echo '<script>window.location.href = "../";</script>';
    exit;
}

$client_id = $_SESSION['client_id'];
$role_id = $_SESSION['role_id'];
$full_name = null;

// Try to get from retailers_info
$stmt = $conn->prepare("
    SELECT full_name FROM retailers_info WHERE client_id = ?
");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($full_name);
    $stmt->fetch();
} else {
    $stmt->close();
    // Try manufacturers_info using dealer_name
    $stmt = $conn->prepare("
        SELECT dealer_name FROM manufacturers_info WHERE client_id = ?
    ");
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($full_name);
        $stmt->fetch();
    } else {
        $full_name = "User"; // fallback
    }
}
$stmt->close();
$conn->close();
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    // Define initMap globally BEFORE the Maps API loads
    function initMap() {
        // This will be called by Google Maps API
        console.log('Google Maps API loaded successfully');
    }
    window.initMap = initMap;
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap&loading=async" async defer></script>
   
<!-- FAVICON FILES -->
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="../assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="../assets/images/logo.png" rel="shortcut icon">

<!-- CSS FILES -->
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/fonts/iconfonts.css">
<link rel="stylesheet" href="../assets/css/plugins.css">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/responsive.css">
<link rel="stylesheet" href="../assets/css/color.css">
<link rel="stylesheet" href="../assets/js/aos/aos-master/dist/aos.css">


<!-- Bootstrap JS & Popper.js -->

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<script>
   // Get your input field(s)
    const inputFields = document.querySelectorAll('input[type="text"], input[type="search"]');
    
    inputFields.forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });
    });

  /**
   * Get custom marker icon configuration
   * @param {string} markerType - 'map1' or 'map2'
   * @returns {Object} Google Maps marker icon configuration
   */
  function getMarkerIcon(markerType) {
    let markerIcon;
    
    if (markerType === 'map2') {
      // Fireworks Display Marker (Green)
      markerIcon = {
        path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M 0,-30 L 0,-25 M 0,-30 L 3,-27 M 0,-30 L -3,-27 M 0,-30 L 4,-29 M 0,-30 L -4,-29 M 0,-30 L 3,-32 M 0,-30 L -3,-32 M 0,-30 L 1,-34 M 0,-30 L -1,-34",
        fillColor: "#06BA54",
        fillOpacity: 1,
        strokeColor: "#ffffff",
        strokeWeight: 1.5,
        scale: 1.5,
        anchor: new google.maps.Point(0, 0),
      };
    } else {
      // Store/Selling Firecrackers Marker (Red) - default
      markerIcon = {
        path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -6,-27 L -6,-33 L 6,-33 L 6,-27 M -5,-27 L -5,-24 L 5,-24 L 5,-27 M -3,-29 L -3,-25 L -1,-25 L -1,-29 M 1,-29 L 1,-25 L 3,-25 L 3,-29",
        fillColor: "#D52941",
        fillOpacity: 1,
        strokeColor: "#ffffff",
        strokeWeight: 1.5,
        scale: 1.5,
        anchor: new google.maps.Point(0, 0),
      };
    }
    
    return markerIcon;
  }

  function initReusableMap(options) {
    const {
      mapId,
      infoId,
      searchInputId,
      latId,
      lngId,
      addressId,
      proceedBtnId,
      centerLat = 8.48,
      centerLng = 124.65,
      regionSuffix = ', Northern Mindanao, Philippines',
      maxRetries = 5,
      markerType = 'map1' // 'map1' or 'map2'
    } = options;

    let marker;
    let map;
    let geocoder;
    let searchTimeout;
    let mapLoadAttempts = 0;

    function initialize() {
      try {
        // Show loading message
        document.getElementById(infoId).innerHTML = '<strong>Loading map...</strong>';

        // Check if Google Maps API is loaded
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
          mapLoadAttempts++;
          if (mapLoadAttempts < maxRetries) {
            console.log('Google Maps not loaded yet. Retry attempt:', mapLoadAttempts);
            setTimeout(initialize, 1000); // Retry after 1 second
            return;
          } else {
            document.getElementById(infoId).innerHTML = '<strong style="color: red;">Failed to load map. Please refresh the page.</strong>';
            return;
          }
        }

        const center = { lat: centerLat, lng: centerLng };
        map = new google.maps.Map(document.getElementById(mapId), {
          center: center,
          zoom: 8,
        });

        geocoder = new google.maps.Geocoder();

        // Clear loading message once map is ready
        google.maps.event.addListenerOnce(map, 'idle', function() {
          document.getElementById(infoId).innerHTML = '<strong>Click on the map to select a address or search above</strong>';
          mapLoadAttempts = 0; // Reset attempts on success
        });

        map.addListener('click', function(event) {
          if (marker) {
            marker.setMap(null);
          }

          marker = new google.maps.Marker({
            position: event.latLng,
            map: map,
            title: "Selected Location",
            icon: getMarkerIcon(markerType),
          });

          const lat = event.latLng.lat();
          const lng = event.latLng.lng();

          document.getElementById(infoId).innerHTML = '<strong>Getting address...</strong>';

          geocoder.geocode({ location: event.latLng }, function(results, status) {
            let address = "Address not found";
            if (status === 'OK' && results[0]) {
              address = results[0].formatted_address;
            }

            document.getElementById(infoId).innerHTML = `
              <strong>Latitude:</strong> ${lat}<br>
              <strong>Longitude:</strong> ${lng}<br>
              <strong>Address:</strong> ${address}
            `;
            document.getElementById(latId).value = lat || '';
            document.getElementById(lngId).value = lng || '';
            document.getElementById(addressId).value = address || '';
            document.getElementById(proceedBtnId).style.display = 'block';
          });
        });
      } catch (error) {
        console.error('Error initializing map:', error);
        mapLoadAttempts++;
        if (mapLoadAttempts < maxRetries) {
          setTimeout(initialize, 1000);
        } else {
          document.getElementById(infoId).innerHTML = '<strong style="color: red;">Failed to load map. Please refresh the page.</strong>';
        }
      }
    }

    function searchLocation() {
      const searchInput = document.getElementById(searchInputId).value.trim();

      if (!searchInput) {
        return;
      }

      if (!geocoder) {
        document.getElementById(infoId).innerHTML = '<strong style="color: orange;">Map is still loading. Please wait...</strong>';
        return;
      }

      document.getElementById(infoId).innerHTML = '<strong>Searching...</strong>';

      const searchQuery = searchInput + regionSuffix;

      geocoder.geocode({ address: searchQuery }, function(results, status) {
        if (status === 'OK' && results[0]) {
          const lat = results[0].geometry.location.lat();
          const lng = results[0].geometry.location.lng();

          map.setCenter(results[0].geometry.location);
          map.setZoom(15);

          if (marker) {
            marker.setMap(null);
          }

          marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map,
            title: "Searched Location",
            icon: getMarkerIcon(markerType),
          });

          const address = results[0].formatted_address;

          document.getElementById(infoId).innerHTML = `
            <strong>Latitude:</strong> ${lat}<br>
            <strong>Longitude:</strong> ${lng}<br>
            <strong>Address:</strong> ${address}
          `;
          document.getElementById(latId).value = lat || '';
          document.getElementById(lngId).value = lng || '';
          document.getElementById(addressId).value = address || '';
          document.getElementById(proceedBtnId).style.display = 'block';
        } else {
          document.getElementById(infoId).innerHTML = '<strong style="color: orange;">Location not found. Try a different search.</strong>';
        }
      });
    }

    function handleSearchInput() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
        searchLocation();
      }, 800);
    }

    // Attach search handler if search input exists
    const searchInputElement = document.getElementById(searchInputId);
    if (searchInputElement) {
      searchInputElement.addEventListener('input', handleSearchInput);
    }

    // Start initialization
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initialize);
    } else {
      initialize();
    }

    // Return methods for external control if needed
    return {
      search: searchLocation,
      getMap: () => map
    };
  }
</script>


<style>
  /* Notifications Container */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 20px;
    max-height: 600px;
    overflow-y: auto;
    width: 100%;
}

/* Individual Notification Item */
.notification-item {
    display: flex;
    gap: 15px;
    padding: 18px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    cursor: default;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-unread {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-left: 4px solid #667eea;
}

.notification-read {
    background: #f8f9fa;
    border-left: 4px solid #dee2e6;
    opacity: 0.85;
}

.notification-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Notification Icon */
.notification-icon-wrapper {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.notification-icon {
    color: white !important;
    font-size: 24px;
}

/* Notification Content */
.notification-content {
    flex: 1;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.notification-message {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
    font-weight: 500;
}

.notification-time {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notification-time i {
    font-size: 12px;
}

/* No Notifications State */
.no-notifications {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.no-notifications i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

/* Scrollbar Styling */
.notifications-list::-webkit-scrollbar {
    width: 8px;
}

.notifications-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.notifications-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.notifications-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive Design */
@media (max-width: 768px) {
    .notification-item {
        padding: 15px;
    }
    
    .notification-icon-wrapper {
        width: 40px;
        height: 40px;
    }
    
    .notification-icon {
        font-size: 20px;
    }
    
    .notification-message {
        font-size: 13px;
    }
    
    .notifications-list {
        padding: 10px;
    }
}

  @keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
  20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.reupload-response-error {
  animation: shake 0.5s;
  background-color: #fee;
  padding: 15px;
  border-radius: 6px;
  border-left: 4px solid #dc3545;
  margin-top: 10px;
}

/* Add to your style section */
.modal-backdrop {
  z-index: 100000 !important;
}

.modal-backdrop + .modal-backdrop {
  display: none !important;
}

#remarksModal {
  z-index: 100001 !important;
}
  .custom-select-wrapper {
    position: relative;
    width: 100%;
    max-width: 400px;
    margin: 0 auto 20px;
  }

  .custom-select {
    width: 100%;
    font-size: 16px;
    font-weight: 500;
    color: #0f2c5a;
    background-color: white;
    border: 2px solid #0f2c5a;
    border-radius: 8px;
    cursor: pointer;
    appearance: none;
    transition: all 0.3s ease;
  }

  .custom-select:hover {
    background-color: #f8f9fa;
    border-color: #ED5553;
  }

  .custom-select:focus {
    outline: none;
    border-color: #ED5553;
    box-shadow: 0 0 0 3px rgba(237, 85, 83, 0.1);
  }

  .actionbtn {
    background-color: #0f2c5a;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    font-size: 12px;
  }

  .actionbtn:hover {
    background-color: #ED5553;
  }

  .content-div {
    display: none;
  }
  table th {
    font-weight: 500;
    letter-spacing: .5px;
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

.map {
        height: 500px;
        width: 100%;
        max-width: 800px;
        border: 2px solid #ccc;
        border-radius: 4px;
      }
      .info {
        margin-top: 10px;
        padding: 10px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        width: 100%;
        max-width: 800px;
        border-radius: 4px;
      }

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
      border-top: 10px solid rgba(255, 88, 88, 0.9);
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      padding: 20px;
      margin:auto;
      overflow-y: auto;
      margin-top: 70px;
      padding-bottom: 50px;
      display: none;
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


.permitreqbox {
    margin: auto;
    width: 300px;
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    background-color: white;
    padding: 20px;
    padding-top: 10px;
    padding-bottom: 30px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
    margin-bottom: 30px;
    margin-top: 10px;
    text-align: left;
}





  .file-upload-box {
    border: 1px dashed #aaa;
    padding: 50px 10px;
    cursor: pointer;
    text-align: center;
    background-color: #f9f9f9;
    margin-bottom: 10px;
    width: 100%;
    height: auto;
    border-radius: 10px;
    font-family: 'Inter';
  }
.uploaded-files {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  max-height: 150px;
  overflow-y: hidden; 
  transition: overflow-y 0.3s ease; /* Optional: smooth transition for overflow */
}

.permitreqbox:hover .uploaded-files {
  overflow-y: auto; /* Show the scrollbar when hovering */
}



  .file-chip {
    background-color: #e0e0e0;
    padding: 5px 10px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    font-size: 14px;
  }

  .remove-file {
    margin-left: 10px;
    margin-top: 0px;
    text-align: center;
    cursor: pointer;
    font-size: 20px;
    border-radius: 100%;
    
    text-align: center;
    font-weight: bold;
    color: red; 
    background-color: #19181a;
  }

  input[type="file"] {
    display: none;
  }


.permitreqbox h6 { font-family: 'Inter' !important;
    font-weight:500 }

    
    
    
    
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


.docs-refs-hover:hover {
  background-color: #123d7a;
  color:white;
}



.action-tile {  
        background-color: #F9FAFB;
        transition: background-color 0.3s, color 0.3s;
        cursor: pointer;
        color: black;
        border: 2px solid transparent;
    }

    .action-tile:hover {

        background-color: #ED5553;
        color: white;
        border: 2px solid red;
    }


.content-div {
      display: none;
    }

.btn-group button {
  background-color: transparent;
        transition: background-color 0.3s, color 0.3s;
        cursor: pointer;
        color: black;
        border: 1px;
        padding:10px;
}

.btn-group button:hover { background:#0f2c5a; color:white; }

.btn-group button.active {
       background-color: #0f2c5a;
        color: white;
        outline: none;
    }

    

    .actionbtn { background-color: #D1120E;
        color: white;
        border: 2px solid transparent;
        cursor: pointer;
        padding:5px 10px; }

    .actionbtn:hover { background: #0f2c5a; border:2px solid red;  }


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

    .chatpopsytext { margin-bottom:10px; width:100% }
    .paymentaccountList { flex-direction:column; }

  }


  </style>


</head>
<body>
    <canvas id="canvas"></canvas>



 <!-- open overlay --> 
<div class="overlay2" id="overlayCon"  onclick="handleOverlayClick(event)">

    <div class="vbox-close" style="color: rgba(255, 88, 88, 0.9); background-color: rgb(22, 22, 23);">×</div>

     <div class="modal2" id="Modal_Apply_Permit" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

<?php if ($role_id == 4): ?>
   <div id="PermitTypeChoices">
  <p style="font-family: 'Inter'; font-size: 1.2rem; font-weight: 500;">
    What Type of Permit Would You Like to Apply For?
  </p>

  <div class="row selection-container justify-content-between" style="margin-top:50px">



    <div class="col-12 col-md-6 mb-4" onclick="openModal('FireworksPermitDiv');document.getElementById('DIVFORM_fireworks_payment').style.display = 'none';document.getElementById('DIVFORM_fireworks_permit_application').style.display = 'block'">
      <div class="selection-box p-3 action-tile text-center d-flex flex-column align-items-center h-100">
        <div class="action-icon-box mb-2">
          <i class="fas fa-fire" style="color:#0f2c5a;"></i>
        </div>
        <p>Special Permit for Fireworks Display</p>
      </div>
    </div>




    <div class="col-12 col-md-6 mb-4" onclick="openModal('TransportPermitDiv');document.getElementById('TransportPermit_section_payment').style.display = 'none';document.getElementById('TransportPermit_section_application').style.display = 'block'">
      <div class="selection-box p-3 action-tile text-center d-flex flex-column align-items-center h-100">
        <div class="action-icon-box mb-2">
          <i class="fas fa-truck-moving" style="color:#0f2c5a;"></i>
        </div>
        <p>Permit to Transport Firecrackers and Pyrotechnic Devices</p>
      </div>
    </div>

  </div>
</div>




<?php else: ?>    
<form id="retailer_apply_permit" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
<!-- /#DIVFOR_RETAILER -->
    <div id="DIVFOR_RETAILER" >
      <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Permit to sell firecrackers and pyrotechnics devices</h4>
      <p>Permitting Requirements</p>
      <div class="container" style="margin-top: 30px;">
        <div class="row">
          <div class="dtr-form dtr-form-styled" style="width: 100%;display: flex;flex-wrap: wrap;">
      
      <div class="permitreqbox">
        <h6>Letter Request</h6>
        <label class="file-upload-box">
          Click to upload file
          <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
          <input type="file" name="letter_request[]" multiple required  onchange="displayFiles(this, 'letter-request-files', 'responsefrom_DIVFOR_RETAILER')">
        </label>
        <div id="letter-request-files" class="uploaded-files"></div>
      </div>

      <div class="permitreqbox">
        <h6>Distributor's Certificate</h6>
        <label class="file-upload-box">
          Click to upload file
          <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
          <input type="file" name="distributors_certificate[]" multiple required  onchange="displayFiles(this, 'distributor-certificate-files', 'responsefrom_DIVFOR_RETAILER')">
        </label>
        <div id="distributor-certificate-files" class="uploaded-files"></div>
      </div>

      <div class="permitreqbox">
        <h6>Photocopy of manufacturer or dealer's license</h6>
        <label class="file-upload-box">
          Click to upload file
          <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
          <input type="file" name="photocopy_license[]" multiple required  onchange="displayFiles(this, 'license-files', 'responsefrom_DIVFOR_RETAILER')">
        </label>
        <div id="license-files" class="uploaded-files"></div>
      </div>

      <div class="permitreqbox">
        <h6>Barangay, Police and Court clearance</h6>
        <label class="file-upload-box">
          Click to upload file
          <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
          <input type="file" name="barangay_police_court_clearance[]" multiple required  onchange="displayFiles(this, 'clearance-files', 'responsefrom_DIVFOR_RETAILER')">
        </label>
        <div id="clearance-files" class="uploaded-files"></div>
      </div>

      <div class="permitreqbox">
        <h6>Certificate of Participation in fireworks Safety Training Course</h6>
        <label class="file-upload-box">
          Click to upload file
          <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
          <input type="file" name="fireworks_training_certificate[]" multiple required  onchange="displayFiles(this, 'training-certificate-files', 'responsefrom_DIVFOR_RETAILER')">
        </label>
        <div id="training-certificate-files" class="uploaded-files"></div>
      </div>

          </div>
        </div>


          <p id="responsefrom_DIVFOR_RETAILER"  style="margin-top: 20px;"></p>


         <button type="button" id="proceedBtn" onclick="validateUploads('#DIVFOR_RETAILER', 'responsefrom_DIVFOR_RETAILER', 'MAPMAP', 'DIVFOR_RETAILER')" style="border: 2px solid #FE0002;padding: 5px 30px;margin-bottom: 30px;">Continue</button>
      </div>
    </div>
<!-- /#DIVFOR_RETAILER -->

<!-- /#MAPMAP -->
<div id="MAPMAP" style="display:none;">
  <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">
    Pin your business address
  </h4>

  <div class="container" style="margin-top: 10px;">
    <div class="row">
      <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">

        <div class="permitreqbox" style="width: 700px;max-width: 85vw;padding-top: 20px;">
         
      <input 
        type="text" 
        id="mapSearchInput" 
        inputmode="search" 
        enterkeyhint="done"
        placeholder="Type barangay or city name (e.g., Carmen, Iligan, Valencia)..." 
        style="padding: 15px 20px 10px 20px;"
        oninput="handleSearchInput()"
      />

      <input type="hidden" name="maplatitude" id="maplatitude">
      <input type="hidden" name="maplongitude" id="maplongitude">
      <input type="hidden" name="mapaddress" id="mapaddress">

    <div id="map" class="map"></div>
    <div id="info" class="info">Click on the map to select a business address or type in the search bar above.</div>


       
      </div> <!-- /.dtr-form -->
    </div> <!-- /.row -->
        <p id="responsefrom_MAPMAP"  style="margin-top: 20px;"></p>


        <div class="flex" style="display: flex; flex-direction: row; justify-content: center; margin-bottom: 30px; width: 100%;">
          <div style="margin: 10px; padding-top: 15px;  padding-right: 15px; cursor: pointer; background: transparent; color: black;" onclick="
            document.getElementById('MAPMAP').style.display='none';
            document.getElementById('DIVFOR_RETAILER').style.display='block';
            const overlay = document.getElementById('overlayCon');
            requestAnimationFrame(() => {
              overlay.scrollTo({ top: 0, behavior: 'smooth' });
            });
          ">Back</div>

           <button type="button" id="proceedBtnfromMAPMAP" onclick="validateUploads('#MAPMAP', 'responsefrom_MAPMAP', 'DIVFOR_RETAILERPAYMENT', 'MAPMAP')" style="border: 2px solid #FE0002;padding: 5px 30px;margin-bottom: 30px;display: none;">Proceed to Payment</button>
         </div>



  </div> 
</div> 
</div>
<script>
  initReusableMap({
  mapId: 'map',
  infoId: 'info',
  searchInputId: 'mapSearchInput',  
  latId: 'maplatitude',
  lngId: 'maplongitude',
  addressId: 'mapaddress',
  proceedBtnId: 'proceedBtnfromMAPMAP',
  centerLat: 8.48, 
  centerLng: 124.65, 
  regionSuffix: ', Northern Mindanao, Philippines', 
  maxRetries: 5,
  markerType: 'map1'
});
</script>
<!-- /#MAPMAP -->

<!-- /#DIVFOR_RETAILERPAYMENT -->
<div id="DIVFOR_RETAILERPAYMENT" style="display:none;">
  <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">
    Proof of Payment
  </h4>

  <div class="container" style="margin-top: 10px;">
    <div class="row">
      <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">

        <div class="permitreqbox" style="width: 700px;max-width: 85vw;padding-top: 20px;">
           <p>
Please make your payment using one of the official payment platforms listed below. Use the correct name and number provided. Once your payment is complete, take a clear screenshot of the receipt and upload it below. This will help us verify your transaction quickly. Thank you!
</p>

<ul class="paymentaccountList" style="display: flex; list-style: none;">
  <li style="padding: 10px; background: #F9FAFB; margin: 10px;width: 100%;">
    <strong>GCash</strong><br>
    Juan Dela Cruz<br>
    0917 123 4567<br>
    Amount: P500.00 
  </li>
  <li style="padding: 10px; background: #F9FAFB; margin: 10px;width: 100%;">
    <strong>Maya</strong><br>
    Maria Santos<br>
    0928 765 4321<br>
    Amount: P500.00 
  </li>
  <li style="padding: 10px; background: #F9FAFB; margin: 10px;width: 100%;">
    <strong>Bank Transfer - BDO</strong><br>
    Pedro Reyes<br>
    0045 6789 1234<br>
    Amount: P500.00 
  </li>
</ul>



          <label class="file-upload-box">
            Click to upload file
            <span style="font-size: 12px;font-weight: lighter;"><br>(Upload receipt for payment)</span>
            <input
              type="file"
              name="proof_of_payment[]"
              accept="image/*"
              required
              onchange="displayFilesIMAGESonly(this, 'proof-of-payment-files', 'responsefrom_DIVFOR_RETAILERPAYMENT')"
            >
          </label>
          <div id="proof-of-payment-files" class="uploaded-files"></div>
        </div>

      </div> <!-- /.dtr-form -->
    </div> <!-- /.row -->

     


           <p id="responsefrom_DIVFOR_RETAILERPAYMENT" style="margin-top: 20px;"></p>

          <div class="flex" id="apply_for_permit_btn" style="display: flex;flex-direction: row; justify-content: center;margin-bottom: 30px;width: 100%">
            <div style="margin: 10px; padding-top: 10px; cursor: pointer; background: transparent; color: black;" onclick="
            
  document.getElementById('MAPMAP').style.display='block';
  document.getElementById('DIVFOR_RETAILERPAYMENT').style.display='none';
  document.getElementById('DIVFOR_RETAILER').style.display='none';
  const overlay = document.getElementById('overlayCon');
  requestAnimationFrame(() => {
    overlay.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
">Back</div>

            <button type="submit" style="border: 2px solid #FE0002;padding: 5px 20px;margin: 10px;">Apply For Permit</button>
          </div>

  </div> 
</div> 
<!-- /#DIVFOR_RETAILERPAYMENT -->          
</form>
<?php endif; ?>


<script>

$(document).ready(function() {
    // When the form is submitted
    $('#retailer_apply_permit').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this); 

        // Change the submit button text to "Processing..."
        $('#apply_for_permit_btn button').text('Processing..').prop('disabled', true);

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

        $.ajax({
            url: 'retailer_apply_permit.php', // The PHP script to handle form submission
            type: 'POST',
            data: formData,
            contentType: false, // Let jQuery handle the content type
            processData: false, // Prevent jQuery from transforming the data into a query string
            success: function(response) {
                if(response == 'Failed'){
                  window.location.reload();
                } else {
                 openModal('Modal_Submitted_Permit');
                 celebrateConfetti('Modal_Submitted_Permit_confetti');
                 


                 $('#retailer_apply_permit')[0].reset();
                 document.getElementById('letter-request-files').innerHTML = '';
                 document.getElementById('distributor-certificate-files').innerHTML = '';
                 document.getElementById('license-files').innerHTML = '';
                 document.getElementById('clearance-files').innerHTML = '';
                 document.getElementById('training-certificate-files').innerHTML = '';
                 document.getElementById('proof-of-payment-files').innerHTML = '';

                }


                document.getElementById('DIVFOR_RETAILER').style.display='block';
                document.getElementById('MAPMAP').style.display='none';
                document.getElementById('DIVFOR_RETAILERPAYMENT').style.display='none';
                


                $('#apply_for_permit_btn button').text('Apply For Permit').prop('disabled', false);
                // You can perform further actions here after the successful submission
            },
            error: function(xhr, status, error) {
                // Handle errors
                alert('Error: ' + error);
                // Re-enable the button and reset text if there's an error
                $('#apply_for_permit_btn button').text('Apply For Permit').prop('disabled', false);
            }
        });
    });
});





  let lastCount = 0;

setInterval(() => {
  const container = document.getElementById("proof-of-payment-files");
  const button = document.getElementById("apply_for_permit_btn");

  if (!container || !button) return; 

  const currentCount = container.children.length;

  if (currentCount >= 1) {
    button.style.display = 'flex';
  } else {
    button.style.display = 'none';
  }

  lastCount = currentCount;
}, 100);
</script>
</div>
























 <div class="modal2" id="FireworksPermitDiv" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

<form id="form_fireworks_permit_application" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
  <!-- /#DIVFORM_fireworks_permit_application -->
  <div id="DIVFORM_fireworks_permit_application">
    <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Special Permit for Fireworks Display</h4>
    <p>Permitting Requirements</p>
    <div class="container" style="margin-top: 30px;">
      <div class="row">
        <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">
          
          <div class="permitreqbox" style="margin: 10px;">
            <h6>Fireworks Display Operator</h6>
            <label class="file-upload-box">
              Click to upload file
              <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
              <input type="file" name="letter_request[]" multiple required 
                onchange="displayFiles(this, 'fireworks_letter_request_files', 'response_fireworks_application')">
            </label>
            <div id="fireworks_letter_request_files" class="uploaded-files"></div>
          </div>

          <div class="permitreqbox" style="margin: 10px;">
            <h6>Dealer's License</h6>
            <label class="file-upload-box">
              Click to upload file
              <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
              <input type="file" name="contract_copy[]" multiple required 
                onchange="displayFiles(this, 'fireworks_contract_copy_files', 'response_fireworks_application')">
            </label>
            <div id="fireworks_contract_copy_files" class="uploaded-files"></div>
          </div>

          <div class="permitreqbox" style="margin: 10px;">
            <h6>Manufacturer's License</h6>
            <label class="file-upload-box">
              Click to upload file
              <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
              <input type="file" name="license_copy[]" multiple required 
                onchange="displayFiles(this, 'fireworks_license_copy_files', 'response_fireworks_application')">
            </label>
            <div id="fireworks_license_copy_files" class="uploaded-files"></div>
          </div>

        </div>
      </div>

      <p id="response_fireworks_application"  style="margin-top: 20px;"></p>

      <button type="button" id="btn_fireworks_payment_proceed" onclick="validateUploads('#DIVFORM_fireworks_permit_application', 'response_fireworks_application', 'MAPMAP2', 'DIVFORM_fireworks_permit_application')" style="border: 2px solid #FE0002;padding: 5px 30px;margin-bottom: 30px;">
       Continue
      </button>
    </div>
  </div>
  <!-- /#DIVFORM_fireworks_permit_application -->




<!-- /#MAPMAP -->
<div id="MAPMAP2" style="display:none;">
  <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">
    Pin your firework display address
  </h4>

  <div class="container" style="margin-top: 10px;">
    <div class="row">
      <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">

        <div class="permitreqbox" style="width: 700px;max-width: 85vw;padding-top: 20px;">
         
      <input 
        type="text" 
        id="mapSearchInput2" 
        inputmode="search" 
        enterkeyhint="done"
        placeholder="Type barangay or city name (e.g., Carmen, Iligan, Valencia)..." 
        style="padding: 15px 20px 10px 20px;"
        oninput="handleSearchInput()"
      />

      <input type="hidden" name="maplatitude" id="maplatitude2">
      <input type="hidden" name="maplongitude" id="maplongitude2">
      <input type="hidden" name="mapaddress" id="mapaddress2">

    <div id="map2" class="map"></div>
    <div id="info2" class="info">Click on the map to select a business address or type in the search bar above.</div>


       
      </div> <!-- /.dtr-form -->
    </div> <!-- /.row -->
        <p id="responsefrom_MAPMAP2"  style="margin-top: 20px;"></p>


        <div class="flex" style="display: flex; flex-direction: row; justify-content: center; margin-bottom: 30px; width: 100%;">
          <div style="margin: 10px; padding-top: 15px;  padding-right: 15px; cursor: pointer; background: transparent; color: black;" onclick="
            document.getElementById('MAPMAP2').style.display='none';
            document.getElementById('DIVFORM_fireworks_permit_application').style.display='block';
            const overlay = document.getElementById('overlayCon');
            requestAnimationFrame(() => {
              overlay.scrollTo({ top: 0, behavior: 'smooth' });
            });
          ">Back</div>

           <button type="button" id="proceedBtnfromMAPMAP2" onclick="validateUploads('#MAPMAP2', 'responsefrom_MAPMAP2', 'DIVFORM_fireworks_payment', 'MAPMAP2')" style="border: 2px solid #FE0002;padding: 5px 30px;margin-bottom: 30px;display: none;">Proceed to Payment</button>
         </div>



  </div> 
</div> 
</div>
<script>
  initReusableMap({
  mapId: 'map2',
  infoId: 'info2',
  searchInputId: 'mapSearchInput2',  
  latId: 'maplatitude2',
  lngId: 'maplongitude2',
  addressId: 'mapaddress2',
  proceedBtnId: 'proceedBtnfromMAPMAP2',
  centerLat: 8.48, 
  centerLng: 124.65, 
  regionSuffix: ', Northern Mindanao, Philippines', 
  maxRetries: 5,
  markerType: 'map2'
});
</script>
<!-- /#MAPMAP -->

  <!-- /#DIVFORM_fireworks_payment -->
  <div id="DIVFORM_fireworks_payment" style="display:none;">
    <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Proof of Payment</h4>
    <div class="container" style="margin-top: 10px;">
      <div class="row">
        <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">

          <div class="permitreqbox" style="width: 700px; max-width: 85vw; padding-top: 20px;">
            <p>Please make your payment using one of the official payment platforms listed below. Use the correct name and number provided. Once your payment is complete, take a clear screenshot of the receipt and upload it below. This will help us verify your transaction quickly. Thank you!</p>

            <ul class="paymentaccountList" style="display: flex; list-style: none;">
              <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                <strong>GCash</strong><br>Juan Dela Cruz<br>0917 123 4567<br>Amount: P500.00 
              </li>
              <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                <strong>Maya</strong><br>Maria Santos<br>0928 765 4321<br>Amount: P500.00 
              </li>
              <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                <strong>Bank Transfer - BDO</strong><br>Pedro Reyes<br>0045 6789 1234<br>Amount: P500.00 
              </li>
            </ul>

            <label class="file-upload-box">
              Click to upload file
              <span style="font-size: 12px;font-weight: lighter;"><br>(Upload receipt for payment)</span>
              <input type="file" name="proof_of_payment[]" accept="image/*" required
                onchange="displayFilesIMAGESonly(this, 'fireworks_proof_of_payment_files', 'response_fireworks_payment')">
            </label>
            <div id="fireworks_proof_of_payment_files" class="uploaded-files"></div>
          </div>

        </div>
      </div>

      <p id="response_fireworks_payment"  style="margin-top: 20px;"></p>

      <div class="flex" id="btn_fireworks_apply_wrapper" style="display: flex;flex-direction: row; justify-content: center; margin-bottom: 30px; width: 100%;">
        <div style="margin: 10px; padding-top: 10px; cursor: pointer; background: transparent; color: black;" onclick="
          document.getElementById('DIVFORM_fireworks_payment').style.display='none';
          document.getElementById('DIVFORM_fireworks_permit_application').style.display='block';
          const overlay = document.getElementById('overlayCon');
          requestAnimationFrame(() => {
            overlay.scrollTo({ top: 0, behavior: 'smooth' });
          });">
          Back
        </div>

        <button type="submit" style="border: 2px solid #FE0002; padding: 5px 20px; margin: 10px;">Apply For Permit</button>
      </div>

    </div>
  </div>
  <!-- /#DIVFORM_fireworks_payment -->
</form>



<script>

$(document).ready(function() {
    // When the form is submitted
    $('#form_fireworks_permit_application').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this); 

        // Change the submit button text to "Processing..."
        $('#btn_fireworks_apply_wrapper button').text('Processing..').prop('disabled', true);

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

        $.ajax({
            url: 'manufacturer_apply_fw_permit.php', // The PHP script to handle form submission
            type: 'POST',
            data: formData,
            contentType: false, // Let jQuery handle the content type
            processData: false, // Prevent jQuery from transforming the data into a query string
            success: function(response) {
                if(response == 'Failed'){
                  window.location.reload();
                } else {
                 openModal('Modal_Submitted_Permit');
                 celebrateConfetti('Modal_Submitted_Permit_confetti');


                 $('#form_fireworks_permit_application')[0].reset();
                 document.getElementById('fireworks_letter_request_files').innerHTML = '';
                 document.getElementById('fireworks_contract_copy_files').innerHTML = '';
                 document.getElementById('fireworks_license_copy_files').innerHTML = '';
                 document.getElementById('fireworks_proof_of_payment_files').innerHTML = '';

                }
                $('#btn_fireworks_apply_wrapper button').text('Apply For Permit').prop('disabled', false);
                // You can perform further actions here after the successful submission


                document.getElementById('DIVFORM_fireworks_permit_application').style.display='block';
                document.getElementById('MAPMAP2').style.display='none';
                document.getElementById('DIVFORM_fireworks_payment').style.display='none';
            },
            error: function(xhr, status, error) {
                // Handle errors
                alert('Error: ' + error);
                // Re-enable the button and reset text if there's an error
                $('#btn_fireworks_apply_wrapper button').text('Apply For Permit').prop('disabled', false);
            }
        });
    });
});





 

setInterval(() => {
   let lastCount = 0;
  const container = document.getElementById("fireworks_proof_of_payment_files");
  const button = document.getElementById("btn_fireworks_apply_wrapper");

  if (!container || !button) return; 

  const currentCount = container.children.length;

  if (currentCount >= 1) {
    button.style.display = 'flex';
  } else {
    button.style.display = 'none';
  }

  lastCount = currentCount;
}, 100);


</script>
</div>











<div class="modal2" id="TransportPermitDiv" style="flex-direction: column; justify-content: center; align-items: center; text-align: center; padding-top: 50px; border-left: transparent; border-right: transparent; border-bottom: transparent;" onclick="event.stopPropagation()">

<form id="TransportPermit_form" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
    
    <!-- Application Section -->
    <div id="TransportPermit_section_application">
      <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Permit to Transport Firecrackers and Pyrotechnic Devices</h4>
      <p>Permitting Requirements</p>
      <div class="container" style="margin-top: 30px;">
        <div class="row">
          <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">
            
            <div class="permitreqbox" style="margin: 10px;">
              <h6>Letter Request</h6>
              <label class="file-upload-box">
                Click to upload file
                <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
                <input type="file" name="letter_request[]" multiple required onchange="displayFiles(this, 'TransportPermit_letter_request_files', 'TransportPermit_response_application')">
              </label>
              <div id="TransportPermit_letter_request_files" class="uploaded-files"></div>
            </div>

            <div class="permitreqbox" style="margin: 10px;">
              <h6>Certified photocopy of Dealer's License</h6>
              <label class="file-upload-box">
                Click to upload file
                <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
                <input type="file" name="dealers_license[]" multiple required onchange="displayFiles(this, 'TransportPermit_dealers_license_files', 'TransportPermit_response_application')">
              </label>
              <div id="TransportPermit_dealers_license_files" class="uploaded-files"></div>
            </div>

            <div class="permitreqbox" style="margin: 10px;">
              <h6>Proof of purchase or official receipt</h6>
              <label class="file-upload-box">
                Click to upload file
                <span style="font-size: 12px;font-weight: lighter;"><br>(Select multiple)</span>
                <input type="file" name="proof_of_purchased[]" multiple required onchange="displayFiles(this, 'TransportPermit_proof_of_purchased_files', 'TransportPermit_response_application')">
              </label>
              <div id="TransportPermit_proof_of_purchased_files" class="uploaded-files"></div>
            </div>

          </div>
        </div>

        <p id="TransportPermit_response_application" style="margin-top: 20px;"></p>

        <button type="button" id="TransportPermit_btn_proceed" onclick="validateUploads('#TransportPermit_section_application', 'TransportPermit_response_application', 'TransportPermit_section_payment', 'TransportPermit_section_application')" style="border: 2px solid #FE0002;padding: 5px 30px;margin-bottom: 30px;">
          Proceed to Payment
        </button>
      </div>
    </div>






    <!-- Payment Section -->
    <div id="TransportPermit_section_payment" style="display:none;">
      <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Proof of Payment</h4>
      <div class="container" style="margin-top: 10px;">
        <div class="row">
          <div class="dtr-form dtr-form-styled" style="width: 100%; display: flex; flex-wrap: wrap;">

            <div class="permitreqbox" style="width: 700px; max-width: 85vw; padding-top: 20px;">
              <p>Please make your payment using one of the official payment platforms listed below. Use the correct name and number provided. Once your payment is complete, take a clear screenshot of the receipt and upload it below. This will help us verify your transaction quickly. Thank you!</p>

              <ul class="paymentaccountList" style="display: flex; list-style: none;">
                <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                  <strong>GCash</strong><br>Juan Dela Cruz<br>0917 123 4567<br>Amount: P500.00 
                </li>
                <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                  <strong>Maya</strong><br>Maria Santos<br>0928 765 4321<br>Amount: P500.00 
                </li>
                <li style="padding: 10px; background: #F9FAFB; margin: 10px; width: 100%;">
                  <strong>Bank Transfer - BDO</strong><br>Pedro Reyes<br>0045 6789 1234<br>Amount: P500.00 
                </li>
              </ul>

              <label class="file-upload-box">
                Click to upload file
                <span style="font-size: 12px;font-weight: lighter;"><br>(Upload receipt for payment)</span>
                <input type="file" name="proof_of_payment[]" accept="image/*"  required onchange="displayFilesIMAGESonly(this, 'TransportPermit_proof_of_payment_files', 'TransportPermit_response_payment')">
              </label>
              <div id="TransportPermit_proof_of_payment_files" class="uploaded-files"></div>
            </div>

          </div>
        </div>

        <p id="TransportPermit_response_payment"  style="margin-top: 20px;"></p>

        <div class="flex" id="TransportPermit_btn_wrapper" style="display: flex; flex-direction: row; justify-content: center; margin-bottom: 30px; width: 100%;">
          <div style="margin: 10px; padding-top: 10px; cursor: pointer; background: transparent; color: black;" onclick="
            document.getElementById('TransportPermit_section_payment').style.display='none';
            document.getElementById('TransportPermit_section_application').style.display='block';
            const overlay = document.getElementById('overlayCon');
            requestAnimationFrame(() => {
              overlay.scrollTo({ top: 0, behavior: 'smooth' });
            });
          ">Back</div>

          <button type="submit" style="border: 2px solid #FE0002; padding: 5px 20px; margin: 10px;">Apply For Permit</button>
        </div>

      </div>
    </div>
  </form>




<script>

$(document).ready(function() {
    // When the form is submitted
    $('#TransportPermit_form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this); 

        // Change the submit button text to "Processing..."
        $('#TransportPermit_btn_wrapper button').text('Processing..').prop('disabled', true);

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

        $.ajax({
            url: 'manufacturer_apply_transport_permit.php', // The PHP script to handle form submission
            type: 'POST',
            data: formData,
            contentType: false, // Let jQuery handle the content type
            processData: false, // Prevent jQuery from transforming the data into a query string
            success: function(response) {
                if(response == 'Failed'){
                  window.location.reload();
                } else {
                 openModal('Modal_Submitted_Permit');
                 celebrateConfetti('Modal_Submitted_Permit_confetti');

                 $('#TransportPermit_form')[0].reset();
                 document.getElementById('TransportPermit_letter_request_files').innerHTML = '';
                 document.getElementById('TransportPermit_dealers_license_files').innerHTML = '';
                 document.getElementById('TransportPermit_proof_of_purchased_files').innerHTML = '';
                 document.getElementById('TransportPermit_proof_of_payment_files').innerHTML = '';

                }
                $('#TransportPermit_btn_wrapper button').text('Apply For Permit').prop('disabled', false);
                // You can perform further actions here after the successful submission
            },
            error: function(xhr, status, error) {
                // Handle errors
                alert('Error: ' + error);
                // Re-enable the button and reset text if there's an error
                $('#TransportPermit_btn_wrapper button').text('Apply For Permit').prop('disabled', false);
            }
        });
    });
});





 

setInterval(() => {
   let lastCount = 0;
  const container = document.getElementById("TransportPermit_proof_of_payment_files");
  const button = document.getElementById("TransportPermit_btn_wrapper");

  if (!container || !button) return; 

  const currentCount = container.children.length;

  if (currentCount >= 1) {
    button.style.display = 'flex';
  } else {
    button.style.display = 'none';
  }

  lastCount = currentCount;
}, 100);


</script>
</div>

































<div class="modal2" id="Modal_Submitted_Permit" style="
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

  
 
  <div class="boxcon" style="width: 500px;" id="Modal_Submitted_Permit_confetti"> 
      <div><img src="../assets/images/logo.png" style="width:200px;height:200px;"></div>
  <h4 style="margin-top: 15px;margin-bottom: 0px;color:#0f2c5a"  >Permit Submitted!</h4>
 
  <p style="color:red;">CSG | Philippine National Police</p>

<p style="text-align: left;">Your application has been successfully submitted. Please wait for review and approval by the authorities.</p>
  <p style="text-align: left;">You will be notified once your application status is updated. Kindly check your dashboard regularly for updates and notifications.</p>
  <p style="text-align: left;" >Thank you for using the Pyrotechnic Online Permitting System (POPS). We appreciate your cooperation.</p>


      
                    </div>
    


    </div>
  </div>
</div>

</div>




<div class="modal2" id="Reply_to_Remarks_Done" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

  <div class="container mt-4">
    <div class="row">
      <div class="dtr-form dtr-form-styled" style="width:100%">

        <div class="boxcon" style="width: 500px;" id="Reply_to_Remarks_Done_confetti"> 
          <div>
            <img src="../assets/images/logo.png" style="width:200px;height:200px;">
          </div>

          <!-- Singular header for single remark -->
          <h4 style="margin-top: 15px; margin-bottom: 0px; color:#0f2c5a;">
            Remark Replied!
          </h4>

          <p style="color:red;">CSG | Philippine National Police</p>

          <!-- Singular message -->
          <p style="text-align: left;">
            Your reply to the remark has been successfully submitted.
          </p>

          <p style="text-align: left;">
            You will be notified once any further updates or actions are required. Please check your dashboard regularly for updates and notifications.
          </p>

          <p style="text-align: left;">
            Thank you for addressing the remark. We appreciate your cooperation with the Pyrotechnic Online Permitting System (POPS).
          </p>

        </div>

      </div>
    </div>
  </div>

</div>















<div class="modal2" id="Modal_View_Permit" style="display:flex; flex-direction:column">
  <h4 style="margin-top: 10px; margin-bottom: 20px; color: #0f2c5a;text-align:center">My Permit Applications</h4>

  <div class="" style="width: 100%; padding: 0px;">
    
    <!-- Custom Select Dropdown -->
    <div class="custom-select-wrapper">
      <select class="custom-select" id="permitTypeSelect" onchange="handleSelectChange(this)">
        <option value="pending_permits">Pending</option>
        <option value="underreview_permits">Under Review</option>
        <option value="endorsed_to_director">Endorsed To Director</option>
        <option value="issued_permits">Permit Issued</option>
        <option value="rejected_permits">Rejected</option>
        <option value="replied_permits">Others</option>

      </select>
    </div>

    <div style="width: 100%; overflow-x: auto; margin: auto;">
      
      <div id="pending_permits" class="content-div" style="display: block;">
        <p style="color: gray;">Loading pending permits...</p>
      </div>

      <div id="underreview_permits" class="content-div">
        <p style="color: gray;">Loading permits under review...</p>
      </div>
      <div id="endorsed_to_director" class="content-div">
        <p style="color: gray;">Loading endorsed to director review...</p>
      </div>

      <div id="issued_permits" class="content-div">
        <p style="color: gray;">Loading issued permits...</p>
      </div>

      <div id="rejected_permits" class="content-div">
        <p style="color: gray;">Loading rejected permits...</p>
      </div>

      <div id="replied_permits" class="content-div">
        <p style="color: gray;">Loading rejected permits...</p>
      </div>

    </div>
  </div>
</div>

<!-- Remarks Modal -->
<!-- Enhanced Remarks Modal -->
<div class="modal fade" id="remarksModal" tabindex="-1" aria-labelledby="remarksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:#0f2c5a">
        <p class="modal-title" id="remarksModalLabel" style="color:white; margin:0;">
          <i class="fas fa-comments"></i> Remarks & Document Re-upload
        </p>
        <div style="cursor: pointer; text-align: center; font-weight: bold; height: 30px; width: 30px; border-radius: 100%; background-color: red; font-size: 14px; color: white; padding-top: 3px;" onclick="closethismodal('remarksModal')">
          <i class="fas fa-times"></i>
        </div>
      </div>
      <div class="modal-body" id="remarksContent" style="max-height: 70vh; overflow-y: auto; padding: 20px;">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading remarks...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.remark-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.remark-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 2px solid #0f2c5a;
}

.remark-date {
  font-size: 12px;
  color: #666;
  font-style: italic;
}

.remark-feedback {
  background-color: white;
  padding: 15px;
  border-radius: 6px;
  margin-bottom: 15px;
  border-left: 4px solid #ED5553;
}

.reupload-section {
  background-color: #fff3cd;
  padding: 15px;
  border-radius: 6px;
  border: 1px solid #ffc107;
  margin-top: 10px;
}

.reupload-title {
  font-weight: 600;
  color: #856404;
  margin-bottom: 10px;
  font-size: 14px;
}

.document-reupload-box {
  background-color: white;
  border: 2px dashed #0f2c5a;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
}

.document-reupload-box h6 {
  color: #0f2c5a;
  font-weight: 500;
  margin-bottom: 10px;
  font-size: 14px;
}

.upload-btn-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 15px;
}

.submit-reupload-btn {
  background-color: #0f2c5a;
  color: white;
  border: 2px solid #ED5553;
  padding: 10px 30px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
}

.submit-reupload-btn:hover {
  background-color: #ED5553;
  border-color: #0f2c5a;
}

.submit-reupload-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  border-color: #999;
}

.no-remarks {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.no-remarks i {
  font-size: 3rem;
  margin-bottom: 15px;
  color: #0f2c5a;
}
</style>

<script>
function viewRemarks(ref_id) {
  console.log('viewRemarks called with ref_id:', ref_id);
  
  const remarksModalElement = document.getElementById('remarksModal');
  const remarksModal = new bootstrap.Modal(remarksModalElement, {
    backdrop: true,
    keyboard: true
  });
  
  window.remarksModal = remarksModal;
  
  // Show loading state
  document.getElementById('remarksContent').innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading remarks...</p>
    </div>
  `;
  
  remarksModal.show();
  
  // Add listener to clean up when modal is hidden
  remarksModalElement.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
      backdrop.remove();
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }, { once: true });
  
  // Fetch remarks
  fetch(`fetch_remarks.php?ref_id=${encodeURIComponent(ref_id)}`)
    .then(response => {
      console.log('Response status:', response.status);
      return response.text();
    })
    .then(text => {
      console.log('Raw response:', text);
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('JSON parse error:', e);
        throw new Error('Invalid JSON response: ' + text);
      }
    })
    .then(data => {
      console.log('Parsed data:', data);
      
      if (data.error) {
        document.getElementById('remarksContent').innerHTML = `
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${data.error}
          </div>
        `;
        return;
      }
      
      if (!data.remarks || data.remarks.length === 0) {
        document.getElementById('remarksContent').innerHTML = `
          <div class="no-remarks">
            <i class="fas fa-info-circle"></i>
            <p style="margin:0;">No remarks found for this application.</p>
          </div>
        `;
        return;
      }
      
      // Render remarks
      let html = '';
      data.remarks.forEach((remark, index) => {
        html += `
          <div class="remark-card">
            <div class="remark-header">
              <strong style="color: #0f2c5a;">
                <i class="fas fa-comment-dots"></i> Remark #${index + 1}
              </strong>
              <span class="remark-date">
                <i class="far fa-clock"></i> ${remark.created_at || 'N/A'}
              </span>
            </div>
            
            <div class="remark-feedback">
              <p style="margin-bottom: 0;">
                ${remark.feedback_note || 'No remarks provided'}
              </p>
            </div>
        `;
        
        // Display existing replies if any
        if (remark.replies && remark.replies.length > 0) {
          html += `
            <div class="replies-section" style="margin-top: 15px; padding-left: 20px; border-left: 3px solid #0f2c5a;">
              <strong style="font-size: 13px; color: #0f2c5a;">
                <i class="fas fa-reply"></i> Your Replies:
              </strong>
          `;
          
          remark.replies.forEach(reply => {
            html += `
              <div class="reply-item" style="background: #e8f4f8; padding: 10px; margin-top: 8px; border-radius: 5px;">
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                  <i class="far fa-clock"></i> ${reply.created_at}
                </div>
                <p style="margin: 0; font-size: 14px; color: #333;">
                  ${reply.reply_text}
                </p>
              </div>
            `;
          });
          
          html += `</div>`;
        }
        
        // Check if there are NO selected documents (text-only remark)
        const hasNoDocuments = !remark.selected_documents || remark.selected_documents.length === 0;
        
        // If NO documents required, show TEXT REPLY FORM
        if (hasNoDocuments) {
          html += `
            <div class="reply-form-section" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
              <form class="remark-reply-form" data-log-id="${remark.log_id}" data-app-id="${data.application_id}">
                <label style="font-size: 13px; font-weight: 600; color: #0f2c5a; margin-bottom: 8px;">
                  <i class="fas fa-reply"></i> Reply to this remark:
                </label>
                <textarea 
                  class="form-control reply-textarea" 
                  name="reply_text" 
                  maxlength="500" 
                  rows="3" 
                  placeholder="Type your reply here (max 500 characters)..."
                  style="font-size: 14px; resize: vertical; min-height: 80px;"
                  oninput="updateReplyCharCount(this)"
                ></textarea>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                  <small class="char-count" style="color: #666;">500 characters remaining</small>
                  <button 
                    type="submit" 
                    class="btn btn-primary btn-sm"
                    style="background: #0f2c5a; border: none; padding: 6px 20px;">
                    <i class="fas fa-paper-plane"></i> Send Reply
                  </button>
                </div>
                <div class="reply-response" style="margin-top: 10px;"></div>
              </form>
            </div>
          `;
        }
        
        // If there ARE selected documents that need re-upload
        if (remark.selected_documents && remark.selected_documents.length > 0) {
          html += `
            <div class="reupload-section">
              <div class="reupload-title">
                <i class="fas fa-exclamation-triangle"></i> 
                Documents Required for Re-upload:
              </div>
              
              <form id="reupload-form-${remark.log_id}" class="reupload-form" data-log-id="${remark.log_id}" data-app-id="${data.application_id}">
          `;
          
          remark.selected_documents.forEach(docField => {
            const sanitizedFieldName = docField.replace(/[^a-zA-Z0-9]/g, '_');
            html += `
              <div class="document-reupload-box">
                <h6>
                  <i class="fas fa-file-upload"></i> ${docField}
                  <span style="color: red; font-weight: bold;"> *</span>
                </h6>
                <label class="file-upload-box" style="cursor: pointer;">
                  Click to upload file(s)
                  <span style="font-size: 12px; font-weight: lighter;">
                    <br>(Select multiple if needed)
                  </span>
                  <input 
                    type="file" 
                    name="${sanitizedFieldName}[]" 
                    multiple 
                    required
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.bmp,.webp,.jfif"
                    onchange="displayFiles(this, 'reupload-files-${remark.log_id}-${sanitizedFieldName}', 'reupload-response-${remark.log_id}')"
                  >
                </label>
                <div id="reupload-files-${remark.log_id}-${sanitizedFieldName}" class="uploaded-files"></div>
              </div>
            `;
          });
          
          html += `
                <p id="reupload-response-${remark.log_id}" style="margin-top: 10px;"></p>
                
                <div class="upload-btn-wrapper">
                  <button type="submit" class="submit-reupload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Submit Re-uploaded Documents
                  </button>
                </div>
              </form>
            </div>
          `;
        }
        
        html += `</div>`; // Close remark-card
      });
      
      document.getElementById('remarksContent').innerHTML = html;
      
      // Attach form submission handlers
      attachReuploadHandlers();
      attachReplyHandlers();
    })
    .catch(error => {
      console.error('Error fetching remarks:', error);
      document.getElementById('remarksContent').innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i> 
          Error loading remarks: ${error.message}
          <br><small>Please check the console for details.</small>
        </div>
      `;
    });
}

// Update character count for reply textarea
function updateReplyCharCount(textarea) {
  const remaining = textarea.maxLength - textarea.value.length;
  const charCount = textarea.closest('.reply-form-section').querySelector('.char-count');
  if (charCount) {
    charCount.textContent = remaining + ' characters remaining';
    charCount.style.color = remaining < 50 ? '#dc3545' : '#666';
  }
}

// Attach reply form handlers
function attachReplyHandlers() {
  const replyForms = document.querySelectorAll('.remark-reply-form');
  
  replyForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const logId = this.dataset.logId;
      const appId = this.dataset.appId;
      const textarea = this.querySelector('.reply-textarea');
      const submitBtn = this.querySelector('button[type="submit"]');
      const responseEl = this.querySelector('.reply-response');
      
      const replyText = textarea.value.trim();
      
      if (!replyText) {
        responseEl.style.color = 'red';
        responseEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Please enter a reply';
        return;
      }
      
      // Disable button
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
      
      const formData = new FormData();
      formData.append('log_id', logId);
      formData.append('application_id', appId);
      formData.append('reply_text', replyText);
      
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
      
      fetch('submit_remark_reply.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          responseEl.style.color = 'green';
          closethismodal('remarksModal');
            openModal('Reply_to_Remarks_Done');
            celebrateConfetti('Reply_to_Remarks_Done_confetti');
          
          // Clear textarea
          textarea.value = '';
          updateReplyCharCount(textarea);
          
        } else {
          responseEl.style.color = 'red';
          responseEl.innerHTML = '<i class="fas fa-times-circle"></i> Error: ' + (data.error || 'Failed to submit reply');
        }
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Reply';
      })
      .catch(error => {
        console.error('Reply error:', error);
        responseEl.style.color = 'red';
        responseEl.innerHTML = '<i class="fas fa-times-circle"></i> Connection error. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Reply';
      });
    });
  });
}







function attachReuploadHandlers() {
  const forms = document.querySelectorAll('.reupload-form');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const logId = this.dataset.logId;
      const appId = this.dataset.appId;
      const submitBtn = this.querySelector('.submit-reupload-btn');
      const responseEl = document.getElementById(`reupload-response-${logId}`);
      
      // VALIDATION: Check if all required documents have been uploaded
      const documentBoxes = this.querySelectorAll('.document-reupload-box');
      let missingDocuments = [];
      
      documentBoxes.forEach(box => {
        const docName = box.querySelector('h6').textContent.replace('📁 ', '').trim();
        const uploadedFilesContainer = box.querySelector('.uploaded-files');
        
        if (!uploadedFilesContainer || uploadedFilesContainer.children.length === 0) {
          missingDocuments.push(docName);
        }
      });
      
      // If there are missing documents, show error and prevent submission
      if (missingDocuments.length > 0) {
        responseEl.style.color = 'red';
        responseEl.innerHTML = `
          <i class="fas fa-exclamation-triangle"></i> 
          <strong>Missing Required Documents:</strong><br>
          Please upload files for: ${missingDocuments.join(', ')}
        `;
        
        // Scroll to the error message
        responseEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Shake animation for visual feedback
        responseEl.style.animation = 'shake 0.5s';
        setTimeout(() => {
          responseEl.style.animation = '';
        }, 500);
        
        return; // Stop form submission
      }
      
      // Clear any previous error messages
      responseEl.textContent = '';
      
      // Disable button
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
      
      const formData = new FormData(this);
      formData.append('log_id', logId);
      formData.append('application_id', appId);
      
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
      
      fetch('submit_document_reupload.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          responseEl.style.color = 'green';
          responseEl.innerHTML = '<i class="fas fa-check-circle"></i> Documents uploaded successfully! Your application will be reviewed again.';
          
          // Reset form after 2 seconds
          closethismodal('remarksModal')
           openModal('Reply_to_Remarks_Done');
                 celebrateConfetti('Reply_to_Remarks_Done_confetti');


                 
          // setTimeout(() => {
          //   form.reset();
          //   form.querySelectorAll('.uploaded-files').forEach(el => el.innerHTML = '');
            
          //   // Close modal and refresh
          //   setTimeout(() => {
          //     closethismodal('remarksModal');
          //     location.reload();
          //   }, 1000);
          // }, 10000);
        } else {
          responseEl.style.color = 'red';
          responseEl.innerHTML = '<i class="fas fa-times-circle"></i> Error: ' + (data.errors ? data.errors.join(', ') : data.error || 'Upload failed');
        }
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Submit Re-uploaded Documents';
      })
      .catch(error => {
        console.error('Upload error:', error);
        responseEl.style.color = 'red';
        responseEl.innerHTML = '<i class="fas fa-times-circle"></i> Upload failed. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Submit Re-uploaded Documents';
      });
    });
  });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
  function handleSelectChange(select) {
    const divId = select.value;
    const divs = document.querySelectorAll('.content-div');
    divs.forEach(div => div.style.display = 'none');
    document.getElementById(divId).style.display = 'block';
  }

  function renderTable(type, data, extra = {}) {
    if (data.length === 0) {
      return '<div style="color:gray;margin-top:15px;text-align:center"><i class="fas fa-exclamation-triangle"></i> No data found.</div>';
    }
    
    
    let html = '';
    const roleId = 4; // Replace with actual role_id from PHP

    switch(type) {
      case 'pending':
        html = `
        <table class="table table-bordered table-striped" style="font-size:12px;">
          <thead style="background-color:#ED5553; color:white;">
            <tr>
              <th>Reference ID</th>
              ${roleId == 4 ? '<th>Permit For</th>' : ''}
              <th>Apply Date</th>
            </tr>
          </thead>
          <tbody>
            ${data.map(app => `
              <tr>
                <td>${app.ref_id}</td>
                ${roleId == 4 ? `<td>${app.permit_for}</td>` : ''}
                <td>${app.apply_date}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>`;
        break;
        
        case 'endorsed_to_director':
        html = `
        <table class="table table-bordered table-striped" style="font-size:12px;">
          <thead style="background-color:#ED5553; color:white;">
            <tr>
              <th>Reference ID</th>
              ${roleId == 4 ? '<th>Permit For</th>' : ''}
              <th>Apply Date</th>
            </tr>
          </thead>
          <tbody>
            ${data.map(app => `
              <tr>
                <td>${app.ref_id}</td>
                ${roleId == 4 ? `<td>${app.permit_for}</td>` : ''}
                <td>${app.apply_date}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>`;
        break;
        
        
        

      case 'replied':
        html = `
        <table class="table table-bordered table-striped" style="font-size:12px;">
          <thead style="background-color:#ED5553; color:white;">
            <tr>
              <th>Reference ID</th>
              ${roleId == 4 ? '<th>Permit For</th>' : ''}
              <th>Apply Date</th>
            </tr>
          </thead>
          <tbody>
            ${data.map(app => `
              <tr>
                <td>${app.ref_id}</td>
                ${roleId == 4 ? `<td>${app.permit_for}</td>` : ''}
                <td>${app.apply_date}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>`;
        break;

      case 'issued':
        html = `
        <table class="table table-bordered table-striped" style="font-size:12px;">
          <thead style="background-color:#ED5553; color:white;">
            <tr>
              <th>Reference ID</th>
              ${roleId == 4 ? '<th>Permit For</th>' : ''}
              <th>Apply Date</th>
              <th>Approval Date</th>
              <th>Valid Until</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            ${data.map(app => `
              <tr>
                <td>${app.ref_id}</td>
                ${roleId == 4 ? `<td>${app.permit_for}</td>` : ''}
                <td>${app.apply_date}</td>
                <td>${app.approval_date}</td>
                <td>${app.valid_until}</td>
                <td><div style="font-weight:400;font-size:12px !important" class="actionbtn badge bg-success text-white" onclick="viewmypermit('${app.ref_id}')">View&nbsp;Permit</div></td>
              </tr>
            `).join('')}
          </tbody>
        </table>`;
        break;

case 'underreview':
    html = `
    <div style="max-height:500px; overflow-y:auto;">
    <table class="table table-bordered table-striped" style="font-size:12px;">
      <thead style="background-color:#ED5553; color:white; position: sticky; top: 0; z-index: 1;">
        <tr>
          <th>Reference ID</th>
          ${roleId == 4 ? '<th>Permit For</th>' : ''}
          <th>Apply Date</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        ${data.map(app => {
          const pending = Number(app.pending_remarks || 0);
          return `
            <tr>
              <td>${app.ref_id}</td>
              ${roleId == 4 ? `<td>${app.permit_for || '-'}</td>` : ''}
              <td>${app.apply_date || '-'}</td>
              <td>
                ${pending > 0 ? `
                  <div class="actionbtn position-relative" onclick="viewRemarks('${app.ref_id}')">
                    View&nbsp;Remarks
                    <span class="position-absolute start-100 translate-middle badge rounded-pill bg-warning" style="top:0px;font-size:1em;border:2px solid white">
                      ${pending}
                    </span>
                  </div>
                ` : `
                  <span class="text-muted" style="font-size:0.9em; padding: 6px 12px;">No remarks
                  </span>
                `}
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
    </div>`;
    break;

      case 'rejected':
        html = `
        <table class="table table-bordered table-striped" style="font-size:12px;">
          <thead style="background-color:#ED5553; color:white;">
            <tr>
              <th>Reference ID</th>
              ${roleId == 4 ? '<th>Permit For</th>' : ''}
              <th>Apply Date</th>
              <th>Rejection Date</th>
              <th>Reason&nbsp;Of&nbsp;Rejection</th>
            </tr>
          </thead>
          <tbody>
            ${data.map(app => `
              <tr>
                <td>${app.ref_id}</td>
                ${roleId == 4 ? `<td>${app.permit_for}</td>` : ''}
                <td>${app.apply_date}</td>
                <td>${app.rejection_date || 'N/A'}</td>
                <td>${app.reason_of_rejection || 'N/A'}</td>
                
              </tr>
            `).join('')}
          </tbody>
        </table>`;
        break;
    }
    return html;
  }

function viewmypermit(ref_id) {
  window.open(`view?ref=${ref_id}`, '_blank', 'noopener,noreferrer');
}

function closethismodal(modalId) {
    const modalElement = document.getElementById(modalId);
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    
    if (modalInstance) {
        modalInstance.hide();
    }
    
    // Force remove ALL backdrops after a small delay
    setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.remove();
        });
        
        // Reset body state
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }, 100);
}

function updatePermitContainer(type, containerId) {
    fetch(`fetch_permits.php?type=${type}`)
        .then(res => res.json())
        .then(res => {
            if (res.error) {
                document.getElementById(containerId).innerHTML = 
                    `<div class="alert alert-danger">${res.error}</div>`;
                return;
            }
            // No extra needed anymore for underreview
            document.getElementById(containerId).innerHTML = 
                renderTable(res.type, res.data);
        })
        .catch(() => {
            document.getElementById(containerId).innerHTML = 
                `<div class="alert alert-danger">Error loading data.</div>`;
        });
}
function startAutoRefresh() {
    // Initial loads
    updatePermitContainer('pending', 'pending_permits');
    updatePermitContainer('underreview', 'underreview_permits');
    updatePermitContainer('endorsed_to_director', 'endorsed_to_director');
    updatePermitContainer('issued', 'issued_permits');
    updatePermitContainer('rejected', 'rejected_permits');
    updatePermitContainer('replied', 'replied_permits');

    // Only one interval — refresh current tab only
    setInterval(() => {
        const activeDiv = document.querySelector('.content-div[style*="block"]');
        if (!activeDiv) return;
        
        const id = activeDiv.id;                    // e.g. "underreview_permits"
        const type = id.replace('_permits', '');    // "underreview"
        
        updatePermitContainer(type, id);
    }, 5000);
}

startAutoRefresh()
</script>





<div class="modal2" id="Modal_Notifications" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

    <h4 style="margin-top: 10px; margin-bottom: 30px; color: #0f2c5a;">
        <i class="fas fa-bell"></i> All Notifications
    </h4>
    
    <div id="notifications-container" style="width: 100%; max-width: 800px; min-height: 300px;">
        <div class="text-center" style="padding: 40px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p style="margin-top: 15px; color: #666;">Loading notifications...</p>
        </div>
    </div>

</div>


<div class="modal2" id="Modal_View_Payments" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #0f2c5a;">My Payment Receipts</h4>


<div id="payment-receipts-container" style="display: flex; flex-wrap: wrap; justify-content: center;">

</div>


<script>



// Fetch notification count
async function getNotificationCount() {
    try {
        const response = await fetch('get_notifcount.php');
        const data = await response.json();
        
        // You can also update a badge or UI element
        if (data.success && data.count > 0) {
            updateNotificationBadge(data.count);
        }
        
        return data;
    } catch (error) {
        console.error('Error fetching notification count:', error);
        return { success: false, count: 0 };
    }
}

// Optional: Update UI badge
function updateNotificationBadge(count) {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

// Call the function
getNotificationCount();

// Optional: Auto-refresh every 30 seconds
setInterval(getNotificationCount, 3000);
</script>

</div>








<div class="modal2" id="Modal_Documents" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
  padding-bottom: 50px;
" onclick="event.stopPropagation()">

  <h4 style="margin-top: 10px; margin-bottom: 10px; color: #0f2c5a;">View Documents</h4>
  <p class="text-center mb-4" id="doc_found">Click on a Reference ID below to view the related documents.</p>

  <div id="refIdContainer" style="display:flex;width: 100%;flex-wrap: wrap; justify-content: center;">
  
  </div>

<div style="color:gray" id="doc_no_found" style="display: none;"><i class="fas fa-exclamation-triangle"></i> No data found.</div>

  <!-- Styling -->
  <style>
    .docu_card {
        margin: 10px;
        width: 200px;
    }
    .docu_card-body {
        padding-top: 10px;
        text-align: center;
    }
    .docu_modal-body img {
        max-width: 100%;
        max-height: 200px;
    }
    .docu_card-title { font-size:12px }
  </style>

  <!-- Modal for Documents -->
  <div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true" style="margin:auto;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header" style="background:#0f2c5a">
          <p class="modal-title" id="documentsModalLabel" style="color:white;">Documents</p>
          <div style="cursor: pointer;text-align: center; font-weight:bold; height:30px;width: 30px;border-radius: 100%; background-color: red; font-size: 14px;color:white;padding-top: 1px;" onclick="closethismodal('documentsModal')">
            <i class="fas fa-close"></i>
          </div>
        </div>
        <div class="docu_modal-body" id="documentsContainer" style="max-height: 400px; overflow-y:auto; padding-top:10px;display: flex;flex-wrap: wrap;justify-content: center;">
          <!-- Documents will be displayed here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JavaScript -->
  <script>
    function closethismodal(divId) {
      const modalInstance = window[divId];
      if (modalInstance) {
        modalInstance.hide();
      } else {
        console.warn(`Modal with ID '${divId}' is not initialized.`);
      }
    }


    // Fetch and display Ref IDs every second
  
  </script>
</div>



<div class="modal2" id="Modal_Profile" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
" onclick="event.stopPropagation()">

    <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;">Profile Information</h4>

    <div id="profile-form-container" style="width:100%; max-width:600px; ">
    <!-- Form will be loaded here by JS -->
  </div>
  <div id="profile_message" class="mt-3"></div>


<script>
  $(document).ready(function () {
  // Fetch profile on page load
  $.ajax({
    url: 'get_profile.php',
    method: 'GET',
    dataType: 'html',
    success: function (data) {
      $('#profile-form-container').html(data);
    }
  });

  // Delegate form submit handler (event delegation for dynamically loaded content)
  $(document).on('submit', '#profile-form', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: 'update_profile.php',
      method: 'POST',
      data: formData,
      success: function (response) {
        alert(response);
        window.location.reload();
      },
      error: function () {
        alert(response)
      }
    });
  });
});
</script>
</div>



<div class="modal2" id="Modal_Logout" style="
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding-top: 50px;
  border-left: transparent;
  border-right: transparent;
  border-bottom: transparent;
  width: 500px;
  border-radius: 10px;
" onclick="event.stopPropagation()">

  
    <h4 style="margin-top: 10px; margin-bottom: 0px; color: #0f2c5a;font-family: 'Inter';">Logout Confirmation</h4>

    <div class="container mt-4" style="margin-bottom: 50px;">
      <div class="row">
        <div class="dtr-form dtr-form-styled" style="width: 100%;">
          <p style="font-family: 'Inter';">Are you sure you want to log out? You will need to log in again to access your account.</p>

          <div class="flex">
            <button style="background:transparent;color:black;" onclick="document.getElementById('overlayCon').style.display='none'">No, cancel</button>
            <button style="border: 2px solid #FE0002;padding: 5px 30px;" onclick="window.location.href='logout.php'">Yes, logout</button>
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
            <img src="../assets/images/logo.png" style="height: 250px;width: 250px;">
        </div>
    </div>
    <!-- preloader ends --> 
    
    <!-- Small Devices Header 
============================================= -->
    <div class="dtr-responsive-header fixed-top on-scroll" style="background:white;">
        <div class="container"> 
            
            <!-- small devices logo --> 
            <div style="display:flex">
               <img src="../assets/images/logo.png"  style="height: 80px;width:80px" alt="logo" >
                <h4 style="color:black;margin-top: auto;margin-left: 10px;color: #04005E;">POPS</h4>
            </div>
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
    <header id="dtr-header-global" class="fixed-top trans-header on-scroll"  data-aos="fade-down"  style="background:#F9FAFB;height: 100px;padding: 0px;padding-top: 10px;">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between"> 
                
                <!-- header left starts -->
                <div class="dtr-header-left" style="display:flex;"> 
                    
                    <!-- logo --> 
                    <div class="logo-default dtr-scroll-link"><img src="../assets/images/logo.png"  style="height: 80px;width:80px" alt="logo"></div> 
                    
                    <!-- logo on scroll --> 
                    <div class="logo-alt dtr-scroll-link"><img src="../assets/images/logo.png"  style="height: 80px;width:80px" alt="logo"></div> 
                    <!-- logo on scroll ends --> 

                    <h4 style="margin-top:auto;margin-left: 10px;color:#04005E">POPS</h4>
                    
                </div>
                <!-- header left ends --> 
                
                <!-- menu starts-->
               <div class="dtr-header-right ml-auto">
    <div class="main-navigation dtr-menu-light">
        <ul class="sf-menu dtr-scrollspy dtr-nav light-nav-on-load dark-nav-on-scroll">
            <li onclick="openModal('Modal_Logout')"> <button class="dtr-btn btn-red dtr-btn-right-icon dtr-scroll-link chatpopsytext" style="padding: 5px 10px;">Logout</button></li>

            



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
        

        
    
        
        <!-- about section starts
================================================== -->
        <section id="about" class="dtr-section  dtr-section-with-bg" style="background-image: url('../assets/images/bg2.png');height: 100%;max-height:100%;margin-top: 80px;padding-top: 100px;padding-bottom: 250px;">
    <!-- blue overlay -->
    <div class="container dtr-overlayContent text-center text-white" style="padding-top:50px">
        <!-- Logo at the top -->
        <div class="mb-4"  data-aos="zoom-in" data-aos-delay="1000"> 
            <img src="../assets/images/logo.png" alt="Logo" class="img-fluid" style="height: 200px;width: 200px;">
        </div>

        <!-- Welcome message -->
        <h2 class="mb-2" style="color:white;text-shadow: 2px 2px #B82E2D;"   data-aos="fade-right" data-aos-delay="1200">Welcome, <?= htmlspecialchars($full_name) ?></h2>
        <p class="lead mb-5"  data-aos="zoom-in" data-aos-delay="1200">What would you like to do?</p>

<div class="row justify-content-center text-center" >
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;"     data-aos="fade-up" data-aos-delay="1300" onclick="openModal('Modal_Apply_Permit')">
        <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
            <div class="action-icon-box mb-2">
                <i class="fas fa-file-alt"></i>
            </div>
            Apply permit
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;"     data-aos="fade-up" data-aos-delay="1500" onclick="openModal('Modal_View_Permit')">
        <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
            <div class="action-icon-box mb-2">
                <i class="fas fa-eye"></i>
            </div>
            View permits
        </div>
    </div>
   <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;" data-aos="fade-up" data-aos-delay="1700" onclick="openModal('Modal_Notifications')">
    <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
        <div class="action-icon-box mb-2" style="position: relative;">
            <i class="fas fa-bell"></i>
            <div id="notification-badge" class="badge" style="
                display: none;
                position: absolute;
                top: -8px;
                right: -8px;
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                padding: 4px 7px;
                font-size: 11px;
                font-weight: bold;
                min-width: 20px;
                height: 20px;
                line-height: 12px;
                text-align: center;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            ">0</div>
        </div>
        Notifications
    </div>
</div>
    
   <!--  <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;"     data-aos="fade-up" data-aos-delay="2100" onclick="openModal('Modal_Documents')">
        <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
            <div class="action-icon-box mb-2">
                <i class="fas fa-folder-open"></i>
            </div>
            Documents
        </div>
    </div> -->
   
     <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;"     data-aos="fade-up" data-aos-delay="2300" onclick="openModal('Modal_Profile')">
        <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
            <div class="action-icon-box mb-2">
                <i class="fas fa-user"></i>
            </div>
            My profile
        </div>
    </div>
   
   <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" style="min-width: 120px;"     data-aos="fade-up" data-aos-delay="2500" onclick="openModal('Modal_Logout')">
        <div class="p-3 rounded shadow-sm action-tile text-center d-flex flex-column align-items-center">
            <div class="action-icon-box mb-2">
               <i class="fa-solid fa-power-off bold-icon"></i>

            </div>
          Logout
        </div>
    </div>
   
</div>


        <!-- End of boxes row -->
    </div>
</section>

<style>.bold-icon {
  font-weight: 900;
  text-shadow: 0.5px 0 0 currentColor;
}
</style>
        <!-- about section ends
================================================== --> 
    
        
        
       
    
      <footer id="dtr-footer"  data-aos="fade-up"  > 
    <!--== footer main starts ==-->
    <div class="dtr-footer-main" style="background: red">
        <div class="container"> 
            
            <!--== row starts ==-->
            <div class="row justify-content-center"> 
                
                <!-- column 1 starts -->
                <div class="col-12 col-md-4 small-device-space"> 
                    <img src="../assets/images/logo.png" style="height: 80px;width:80px" alt="POPS Logo">
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
                
               
                
                <!-- column 3 starts -->
                <div class="col-12 col-md-4 small-device-space">
                    <h4>Contact Info</h4>
                    <div class="spacer-30"></div>
                    <ul class="dtr-contact-widget">
                        <li><i class="icon-phone-call"></i> (088) 850 0736</li>
                        <li><i class="icon-envelope1"></i><a href="mailto:support@pops.ph">support@pops.ph</a></li>
                        <li><i class="icon-map-pin1"></i> Camp Alagar, Cagayan De Oro City, 9000 Misamis Oriental</li>
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
        this.speed = Math.random() * 4 + 1;
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



<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <script>
      // Add this inside the viewRemarks function, after attachReuploadHandlers()
function updateSubmitButtonState(formId) {
  const form = document.getElementById(formId);
  if (!form) return;
  
  const submitBtn = form.querySelector('.submit-reupload-btn');
  const documentBoxes = form.querySelectorAll('.document-reupload-box');
  let uploadedCount = 0;
  let totalRequired = documentBoxes.length;
  
  documentBoxes.forEach(box => {
    const uploadedFilesContainer = box.querySelector('.uploaded-files');
    if (uploadedFilesContainer && uploadedFilesContainer.children.length > 0) {
      uploadedCount++;
    }
  });
  
  // Update button text with progress
  if (uploadedCount < totalRequired) {
    submitBtn.innerHTML = `
      <i class="fas fa-cloud-upload-alt"></i> 
      Submit Documents (${uploadedCount}/${totalRequired} uploaded)
    `;
    submitBtn.style.opacity = '0.6';
  } else {
    submitBtn.innerHTML = `
      <i class="fas fa-cloud-upload-alt"></i> 
      Submit Re-uploaded Documents
    `;
    submitBtn.style.opacity = '1';
  }
}

// Monitor file uploads
document.querySelectorAll('.reupload-form').forEach(form => {
  const formId = form.id;
  
  // Check every second
  setInterval(() => {
    updateSubmitButtonState(formId);
  }, 500);
});
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


</script>


<script>








 function validateUploads(containerSelector, responseElementId, nextSectionId, currentSectionId) {
  const container = document.querySelector(containerSelector);
  if (!container) {
    console.error(`Container ${containerSelector} not found`);
    return;
  }

  const boxes = container.querySelectorAll('.permitreqbox');
  let missing = [];

  boxes.forEach(box => {
    const h6 = box.querySelector('h6')?.innerText.trim();
    const uploaded = box.querySelector('.uploaded-files');
    if (!uploaded || uploaded.children.length === 0) {
      if (h6) missing.push(h6);
    }
  });

  const responseEl = document.getElementById(responseElementId);

  if (missing.length > 0) {
    const message = `⚠️ Please upload required files for: ${missing.join(', ')}`;
    if (responseEl) {
      responseEl.style.color = 'red';
      responseEl.innerText = message;
    }
  } else {
    const overlay = document.getElementById('overlayCon');
    if (overlay) {
      requestAnimationFrame(() => {
        overlay.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }

    document.getElementById(nextSectionId)?.style.setProperty('display', 'block');
    document.getElementById(currentSectionId)?.style.setProperty('display', 'none');
  }
}



function displayFilesIMAGESonly(inputElement, fileContainerId, responsealert) {
    const allowedExtensions = /\.(jpg|jpeg|png|gif|jfif|bmp|webp|tiff|heic)$/i;
    const responseEl = document.getElementById(responsealert);

    const files = inputElement.files;
    const container = document.getElementById(fileContainerId);

    container.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (files.length > 5) {
            responseEl.style.color = 'red';
            responseEl.innerText = "❌ You can only upload up to 5 files!";
            alert('❌ You can only upload up to 5 files!')
            return; 
        }

        if (!allowedExtensions.test(file.name)) {
            responseEl.style.color = 'red';
            responseEl.innerText = `❌ Invalid file types: ${file.name}`;
            alert(`❌ Invalid file types: ${file.name}`)
            continue;
        }

        const fileDiv = document.createElement('div');
        fileDiv.classList.add('file-chip');

        const fileName = document.createElement('span');
        fileName.textContent = file.name.length > 7 
            ? file.name.slice(0, 7) + '...' 
            : file.name;

        const deleteBtn = document.createElement('div');
        deleteBtn.textContent = 'x';
        deleteBtn.classList.add('remove-file');
        deleteBtn.style.height = '30px';
        deleteBtn.style.width = '30px';
        
        // FIXED: Prevent event propagation and backdrop issues
        deleteBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            removeFile(fileDiv, file, inputElement);
        };

        fileDiv.appendChild(fileName);
        fileDiv.appendChild(deleteBtn);

        responseEl.style.color = '';
        responseEl.innerText = ``;

        container.appendChild(fileDiv);
    }
}

function displayFiles(inputElement, fileContainerId, responsealert) {
    const allowedExtensions = /\.(jpg|jpeg|png|gif|bmp|webp|tiff|heic|pdf)$/i;
    const responseEl = document.getElementById(responsealert);

    const files = inputElement.files;
    const container = document.getElementById(fileContainerId);

    container.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (files.length > 5) {
            responseEl.style.color = 'red';
            responseEl.innerText = "❌ You can only upload up to 5 files!";
            alert('❌ You can only upload up to 5 files!')
            return; 
        }

        if (!allowedExtensions.test(file.name)) {
            responseEl.style.color = 'red';
            responseEl.innerText = `❌ Invalid file types: ${file.name}`;
            alert(`❌ Invalid file types: ${file.name}`)
            continue;
        }

        const fileDiv = document.createElement('div');
        fileDiv.classList.add('file-chip');

        const fileName = document.createElement('span');
        fileName.textContent = file.name.length > 7 
            ? file.name.slice(0, 7) + '...' 
            : file.name;

        const deleteBtn = document.createElement('div');
        deleteBtn.textContent = 'x';
        deleteBtn.classList.add('remove-file');
        deleteBtn.style.height = '30px';
        deleteBtn.style.width = '30px';
        
        // FIXED: Prevent event propagation and backdrop issues
        deleteBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            removeFile(fileDiv, file, inputElement);
        };

        fileDiv.appendChild(fileName);
        fileDiv.appendChild(deleteBtn);

        responseEl.style.color = '';
        responseEl.innerText = ``;

        container.appendChild(fileDiv);
    }
}



function removeFile(fileDiv, file, inputElement) {
    // Remove the file from the displayed list
    fileDiv.remove();
    
    // Clean up any lingering backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });
    
    // Ensure body is not locked
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}




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




let notificationsScrollPosition = 0;

// Function to load notifications while preserving scroll position
function loadNotifications() {
    // Get the notifications list container (if it exists)
    const existingList = document.querySelector('.notifications-list');
    
    // Save current scroll position before refresh
    if (existingList) {
        notificationsScrollPosition = existingList.scrollTop;
    }
    
    fetch('get_notifs.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            const container = document.getElementById('notifications-container');
            if (container) {
                container.innerHTML = data;
                
                // Restore scroll position after content is loaded
                setTimeout(() => {
                    const newList = document.querySelector('.notifications-list');
                    if (newList && notificationsScrollPosition > 0) {
                        newList.scrollTop = notificationsScrollPosition;
                    }
                }, 50); // Small delay to ensure DOM is updated
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const container = document.getElementById('notifications-container');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger text-center" style="margin: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading notifications. Please try again.
                    </div>
                `;
            }
        });
}

// Track scroll position in real-time
function attachScrollListener() {
    const notificationsList = document.querySelector('.notifications-list');
    if (notificationsList) {
        notificationsList.addEventListener('scroll', function() {
            notificationsScrollPosition = this.scrollTop;
        });
    }
}

// Load notifications immediately when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    
    // Attach scroll listener after initial load
    setTimeout(attachScrollListener, 100);
});

// Refresh notifications every 5 seconds
setInterval(function() {
    loadNotifications();
    
    // Re-attach scroll listener after each refresh
    setTimeout(attachScrollListener, 100);
}, 15000);

// Modified openModal function to handle notifications
const originalOpenModal = window.openModal;
window.openModal = function(targetModalId) {
    // Call original openModal function
    if (typeof originalOpenModal === 'function') {
        originalOpenModal(targetModalId);
    } else {
        // Fallback if openModal doesn't exist yet
        const modals = document.querySelectorAll('.modal2');
        modals.forEach(modal => modal.style.display = 'none');
        
        const target = document.getElementById(targetModalId);
        if (target) {
            target.style.display = targetModalId === 'modalTerms' ? 'block' : 'flex';
        }
        
        const overlay = document.getElementById('overlayCon');
        if (overlay) {
            overlay.style.display = 'block';
            requestAnimationFrame(() => {
                overlay.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    }
    
    // If opening notifications modal, refresh immediately and reset scroll
    if (targetModalId === 'Modal_Notifications') {
        notificationsScrollPosition = 0; // Reset scroll when opening modal
        loadNotifications();
        setTimeout(attachScrollListener, 100);
    }
};


  // Global constants
  const overlay = document.getElementById('overlayCon');
  overlay.style.display = 'none';
  const modals = document.querySelectorAll('.modal2');
  modals.forEach(modal => modal.style.display = 'none');

  // Show one modal, hide all others
  function openModal(targetModalId) {
    // Hide all modals no matter what
      if (targetModalId === 'Modal_Notifications') {
        markNotificationsAsRead();
    }
    
    modals.forEach(modal => {
      modal.style.display = 'none';
    });

    // Show the target modal (if it exists)
    const target = document.getElementById(targetModalId);
    if (target) {
      // Use 'block' for Terms, 'flex' for others
      target.style.display = targetModalId === 'modalTerms' ? 'block' : 'flex';
    }

    // Always show overlay
    overlay.style.display = 'block';

    // Scroll overlay to top

    requestAnimationFrame(() => {
      overlay.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Specific helper functions (optional for convenience)
  async function markNotificationsAsRead() {
    try {
        const response = await fetch('mark_notifread.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Notifications marked as read:', data.updated_count);
            
            // Update the badge to hide it
            const badge = document.getElementById('notification-badge');
            if (badge) {
                badge.style.display = 'none';
                badge.textContent = '0';
            }
        } else {
            console.error('Failed to mark notifications as read:', data.error);
        }
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
}


  // Close all modals when clicking outside
// Close all modals when clicking outside
function handleOverlayClick(event) {
  // Don't close if clicking inside a Bootstrap modal
  if (event.target.closest('.modal-dialog')) {
    return;
  }
  
  const clickedOutside = Array.from(modals).every(modal => !modal.contains(event.target));
  if (clickedOutside) {
    overlay.style.display = 'none';
    modals.forEach(modal => modal.style.display = 'none');
  }
}
  // Attach event listener once DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    overlay.addEventListener('click', handleOverlayClick);
  });








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
  
  
  
  // Version 2: Attach to EVERY input/textarea immediately + mutation observer
document.addEventListener('DOMContentLoaded', () => {
    const block = e => {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    };

    // Initial elements
    document.querySelectorAll('input, textarea').forEach(el => {
        el.addEventListener('keydown', block);
    });

    // Watch for dynamically added inputs
    const observer = new MutationObserver(() => {
        document.querySelectorAll('input:not([data-enter-blocked]), textarea:not([data-enter-blocked])')
            .forEach(el => {
                el.addEventListener('keydown', block);
                el.dataset.enterBlocked = 'true';
            });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

});



// Add this script at the end of your file, before closing </body>
setInterval(() => {
    // Check if there are any visible Bootstrap modals
    const visibleModals = document.querySelectorAll('.modal.show');
    
    // If no modals are visible, remove all backdrops
  
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    
}, 500);
</script>


<!-- JS FILES --> 
<script src="../assets/js/aos/aos-master/dist/aos.js"></script>
<script>AOS.init({
    duration: 1000,
    once: true 
  });</script>
<script src="../assets/js/jquery.min.js"></script> 
<script src="../assets/js/bootstrap.min.js"></script> 
<script src="../assets/js/plugins.js"></script> 
<script src="../assets/js/custom.js"></script>
</body>
</html>