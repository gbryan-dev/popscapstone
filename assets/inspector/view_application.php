<?php
require_once 'partials/auth.php';
include '../../db_conn.php'; 

include '../../env.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['ref_id'])) {
        $ref_id = htmlspecialchars($_POST['ref_id']);

        // First query: fetch permit details
        $sql_permit = "SELECT status, application_id, ref_id, reason_of_rejection, permit_for, apply_date, client_id FROM applications WHERE ref_id = ?";
        $stmt_permit = $conn->prepare($sql_permit);
        $stmt_permit->bind_param("s", $ref_id);
        $stmt_permit->execute();
        $result_permit = $stmt_permit->get_result();

        if ($result_permit->num_rows > 0) {
            $permit = $result_permit->fetch_assoc();

    
            $status = $permit['status'];
            $reason_of_rejection = $permit['reason_of_rejection'];
            
            $application_id = $permit['application_id'];
            $permit_for = $permit['permit_for'];
            $apply_date = $permit['apply_date'];
            $application_date_display = preg_replace('/\s+at\s+.*/i', '', $apply_date);

            $client_id = $permit['client_id'];

            // Second query: fetch manufacturer info based on client_id
            $sql_manu = "SELECT company_name FROM manufacturers_info WHERE client_id = ?";
            $stmt_manu = $conn->prepare($sql_manu);
            $stmt_manu->bind_param("s", $client_id);
            $stmt_manu->execute();
            $result_manu = $stmt_manu->get_result();

            if ($result_manu->num_rows > 0) {
                $manufacturer = $result_manu->fetch_assoc();
                $company_name = $manufacturer['company_name'];
            } else {
                $company_name = null;
            }

        } else {
            echo "<script>window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
      <head>
           <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap&libraries=places" async defer></script>
    <?php include('partials/head.php')?>
<style>

    .results-container {
    margin-top: 8px;
}

.qr-result-badge {
    padding: 4px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    display: block;
    margin-top: 8px;
}

.qr-result-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.qr-result-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.qr-result-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.qr-result-info {
    background-color: transparent;
    color: #0c5460;
    border: 1px solid transparent;
}

.qr-scanning {
    text-align: center;
    padding: 8px;
    color: #667eea;
    font-size: 12px;
}

    .card.file.photo {
        transition: border 0.3s ease;
        border: 2px solid transparent;
    }

    .card.file.photo:hover {
        border: 2px solid red;
        cursor: pointer;
         box-shadow: 0px 0px 10px red;
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

    #map {
                height: 500px;
                width: 100%;
            }

            .search { width:60%;}
              .legend {
                display: flex;
                justify-content: center;
                align-items: center;
                align-content: center;
                flex-wrap: wrap;
                gap:10px;
                padding: 10px;
                margin: 10px;
            }
            
            .legend-item {
                display: flex;
                align-items: center;
                margin: 5px 0;
                gap:10px;
                flex-wrap: nowrap;

            }   

            .legendicon {
                width: 40px;
                height: 40px;
            }

            .legendicon img {
                margin: auto;
                width:100%; height: 100%;
            }

            @media (max-width: 480px) {
                .search { width:80%;}
                .legend { transform:scale(0.9); }
            }
            

    @media (max-width: 768px) {
    .docsmob {
        max-height: 600px;
        overflow-y: auto;
    }
}

/* Reupload documents styling */
.reupload-section {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 15px;
    margin-top: 15px;
    border-radius: 5px;
}

.reupload-item {
    padding: 10px;
    margin: 8px 0;
    background: white;
    border-radius: 4px;
    border-left: 3px solid #ffc107;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reupload-item.approved {
    border-left-color: #28a745;
}

.reupload-item.rejected {
    border-left-color: #dc3545;
}

.reupload-field-name {
    font-weight: 600;
    color: #333;
    font-size: 13px;
}

.reupload-file-info {
    font-size: 11px;
    color: #666;
    margin-top: 3px;
}

.reupload-status {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.reupload-status.pending {
    background: #fff3cd;
    color: #856404;
}

.reupload-status.approved {
    background: #d4edda;
    color: #155724;
}

.reupload-status.rejected {
    background: #f8d7da;
    color: #721c24;
}

.download-btn {
    padding: 4px 8px;
    margin-left: 10px;
    font-size: 11px;
    cursor: pointer;
}


 input[type="checkbox"]:checked {
            background-color: #19E915;
            border-color: #19E915;
        }


</style>

        
    </head>
    <body>
        
        
        <canvas id="canvas"></canvas>
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>

        <div class="lime-container">
            <div class="lime-body">
                <div style="width: 90%;max-width: 1000px;margin: auto;">

    <?php if ($permit_for != 'Transport Firecrackers and Pyrotechnic Devices'): ?>

                        <?php
include '../../db_conn.php';

$ref_id = htmlspecialchars($_POST['ref_id']);

// First query: fetch permit details
$sql_permit = "SELECT application_id, reason_of_rejection FROM applications WHERE ref_id = ?";
$stmt_permit = $conn->prepare($sql_permit);
$stmt_permit->bind_param("s", $ref_id);
$stmt_permit->execute();
$result_permit = $stmt_permit->get_result();

$locations = [];

if ($result_permit->num_rows > 0) {
    $permit = $result_permit->fetch_assoc();
    $baseonthis_application_id = $permit['application_id'];
    $reason_of_rejection = $permit['reason_of_rejection'];
    
    
    // Fetch all applications with location data using prepared statement
    $query = "SELECT 
        app.application_id, 
        app.permit_for, 
        app.maplatitude,
        app.mapaddress,
        app.maplongitude,
        df.display_datetime,
        df.display_purpose,
        df.pyro_technician, 
        app.status
    FROM applications app
    LEFT JOIN special_permit_display_fireworks df 
        ON app.application_id = df.application_id
    LEFT JOIN permit_sell_firecrackers sf 
        ON app.application_id = sf.application_id
    WHERE app.permit_for IN (
        'Special Permit for Fireworks Display',
        'Permit To Sell Firecrackers And Pyrotechnics Devices'
    ) AND app.application_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $baseonthis_application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
    }
    
    $stmt->close();
}

$stmt_permit->close();
?>
    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body" >
                                    <!-- Title + Search -->
                                    <div style="display: flex; width: 100%; flex-direction: column; align-items: center; text-align: center;">
                                        <?php if ($permit_for == 'Special Permit for Fireworks Display'): ?>
                                            <h5 class="card-title" style="margin: auto; font-size: 23px;">Firework Display Address</h5>
                                       
                                         <?php endif; ?>

                                         <?php if ($permit_for == 'Permit To Sell Firecrackers And Pyrotechnics Devices'): ?>
                                            <h5 class="card-title" style="margin: auto; font-size: 23px;">Store Address</h5>
                                            <?php endif; ?>

                                        <div class="form-inline search"style="display: none;" >
                                            <input class="form-control" id="searchInput" style="width:100%; text-align: center;"
                                                   type="search" placeholder="Type barangay or city name (e.g., Carmen, Iligan, Valencia)..." aria-label="Search">
                                        </div>
                                    </div>
                                    
                                </div>
                                <div id="map"></div>

                                <div class="legend" style="display: none;">
                                        <div class="legend-item">
                                            
                                            <div class="legendicon">
                                            <img src="mapfirework2.png" >
                                            </div>
                                            <span>Fireworks Display</span>
                                        </div>
                                        <div class="legend-item">

                                            <div class="legendicon" >
                                            <img src="mapstore2.png">
                                            </div>
                                            <span>Selling Firecrackers</span>
                                        </div>
                                    </div>

<script>
      let map;
      let geocoder;
      let searchTimeout;
      let mapLoadAttempts = 0;
      const maxRetries = 5;
      let markers = [];
      
      // Location data from PHP
      const locations = <?php echo json_encode($locations); ?>;
      
      function initMap() {
        try {
          // Check if Google Maps API is loaded
          if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            mapLoadAttempts++;
            if (mapLoadAttempts < maxRetries) {
              setTimeout(initMap, 1000);
              return;
            } else {
              console.error('Failed to load Google Maps');
              return;
            }
          }
          let lat = parseFloat(locations[0].maplatitude);
          let lng = parseFloat(locations[0].maplongitude);

          
          const region10Center = { lat: lat, lng: lng };
          map = new google.maps.Map(document.getElementById("map"), {
            center: region10Center,
            zoom: 13,
          });
          
          geocoder = new google.maps.Geocoder();
          
          google.maps.event.addListenerOnce(map, 'idle', function() {
            mapLoadAttempts = 0;
            // Add markers after map is loaded
            addMarkers();
          });
          
          // Add event listener to search input
          document.getElementById('searchInput').addEventListener('input', handleSearchInput);
          document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              searchLocation();
            }
          });
          
        } catch (error) {
          console.error('Error initializing map:', error);
          mapLoadAttempts++;
          if (mapLoadAttempts < maxRetries) {
            setTimeout(initMap, 1000);
          }
        }
      }
      
      function addMarkers() {
        locations.forEach(function(location) {
          const lat = parseFloat(location.maplatitude);
          const lng = parseFloat(location.maplongitude);
          
          if (isNaN(lat) || isNaN(lng)) {
            console.warn('Invalid coordinates for application:', location.application_id);
            return;
          }
          
          const position = { lat: lat, lng: lng };
          let markerIcon;
            

          // Determine marker icon based on permit_for
          if (location.permit_for == 'Special Permit for Fireworks Display') {
             markerIcon = {
              path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M 0,-30 L 0,-25 M 0,-30 L 3,-27 M 0,-30 L -3,-27 M 0,-30 L 4,-29 M 0,-30 L -4,-29 M 0,-30 L 3,-32 M 0,-30 L -3,-32 M 0,-30 L 1,-34 M 0,-30 L -1,-34",
              fillColor: "#06BA54",
              fillOpacity: 1,
              strokeColor: "#ffffff",
              strokeWeight: 1.5,
              scale: 1.5,
              anchor: new google.maps.Point(0, 0),
            };
            
          } else if (location.permit_for == 'Permit To Sell Firecrackers And Pyrotechnics Devices') {
          markerIcon = {
  path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -6,-27 L -6,-33 L 6,-33 L 6,-27 M -5,-27 L -5,-24 L 5,-24 L 5,-27 M -3,-29 L -3,-25 L -1,-25 L -1,-29 M 1,-29 L 1,-25 L 3,-25 L 3,-29",
  fillColor: "#D52941",
  fillOpacity: 1,
  strokeColor: "#ffffff",
  strokeWeight: 1.5,
  scale: 1.5,
  anchor: new google.maps.Point(0, 0),
};
          } else {
            // Default marker for other permit types
            markerIcon = {
              path: google.maps.SymbolPath.CIRCLE,
              fillColor: "#4285F4",
              fillOpacity: 1,
              strokeColor: "#ffffff",
              strokeWeight: 2,
              scale: 8,
            };
          }
          
          const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: location.permit_for,
            icon: markerIcon,
          });
          
          // Create info window

          const infoWindow = new google.maps.InfoWindow({
            content: `
              <div style="padding: 10px;">
                <h6 style="margin: 0 0 10px 0; font-weight: bold;">${location.permit_for}</h6>
                </div>
            `
          });

          
const datetime = location.display_datetime;
const formatted_datetime = new Date(datetime).toLocaleString("en-US", {
  month: "short",
  day: "2-digit",
  year: "numeric",
  hour: "2-digit",
  minute: "2-digit",
  hour12: true
}).replace(",", "").replace(/^(\w{3})/, "$1.");


           const infoWindow2 = new google.maps.InfoWindow({
            content: `
              <div style="padding: 10px;">
                <h6 style="margin: 0 0 10px 0; font-weight: bold;">${location.permit_for}</h6>
                <p style="margin: 5px 0;"><strong>Date of Fireworks Display:</strong> ${formatted_datetime}</p>
                <p style="margin: 5px 0;"><strong>Purpose of Fireworks Display:</strong> ${location.display_purpose}</p>
                <p style="margin: 5px 0;"><strong>Licensed Pyro Technician:</strong> ${location.pyro_technician}</p>
              </div>
            `
          });


          
          marker.addListener('click', function() {
            // Close all other info windows
            markers.forEach(m => {
                if (m.infoWindow) {
                m.infoWindow.close();
              }

              if (m.infoWindow2) {
                m.infoWindow2.close();
              }
            });
            if (location.permit_for == 'Special Permit for Fireworks Display'){
                 infoWindow2.open(map, marker);
            } 

            if (location.permit_for == 'Permit To Sell Firecrackers And Pyrotechnics Devices'){
                 infoWindow.open(map, marker);
            } 
           
          });
          
          markers.push({ marker: marker, infoWindow: infoWindow2 });
        });
      }
      
      function searchLocation() {
        const searchInput = document.getElementById('searchInput').value.trim();
        
        if (!searchInput) {
          return;
        }
        
        if (!geocoder) {
          return;
        }
        
        const searchQuery = searchInput + ', Northern Mindanao, Philippines';
        
        geocoder.geocode({ address: searchQuery }, function(results, status) {
          if (status === 'OK' && results[0]) {
            map.setCenter(results[0].geometry.location);
            map.setZoom(15);
          }
        });
      }
      
      function handleSearchInput() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          searchLocation();
        }, 800);
      }
      
      // Start initialization when page loads
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
      } else {
        initMap();
      }
</script>
                            </div>
                        </div>
                    </div>
                      <?php endif; ?>

                    <div class="row">
                        
                        <div class="col-lg-6" style="margin-bottom: 30px;">
                            <div class="card card-transparent file-list recent-files">
                                <h5 class="card-title" style="color:white;">Documents </h5>
                                <div class="card-body" style="padding:0px">
                                    <div class="row docsmob" >
                                      <?php
include "../../db_conn.php";

$ref_id = htmlspecialchars($_POST['ref_id']);

$sql = "SELECT application_id, reason_of_rejection FROM applications WHERE ref_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ref_id);
$stmt->execute();
$result = $stmt->get_result();

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $power = min($power, count($units) - 1);
    $formatted = $bytes / pow(1024, $power);
    return round($formatted, $precision) . ' ' . $units[$power];
}

if ($result->num_rows > 0) {
    $permit = $result->fetch_assoc();
    $application_id = $permit['application_id'];
    $reason_of_rejection = $permit['reason_of_rejection'];

    $sql_docs = "SELECT * FROM documents WHERE application_id = ?";
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $application_id);
    $stmt_docs->execute();
    $documents = $stmt_docs->get_result();

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif'];

    while ($doc = $documents->fetch_assoc()) {
        $fileName = htmlspecialchars($doc['file_name']);
        $fieldName = htmlspecialchars($doc['field_name']);
        $fileExtension = strtolower(htmlspecialchars($doc['file_extension']));
        $rawSize = isset($doc['file_size']) ? $doc['file_size'] : 0;
        $fileSize = formatBytes($rawSize);
        $filePath = "../../client/uploads/" . $fileName;

        if (in_array($fileExtension, $imageExtensions) && file_exists($filePath)) {
            $displayPath = $filePath;
        } else {
            $displayPath = "../../client/notimage.png";
        }

        // Generate unique ID for each card
        $uniqueId = 'doc_' . $doc['id'];

       if ($fieldName == 'Proof Of Payment') {
    // Version WITHOUT QR code scanning
    echo '
        <div onclick="window.open(\'' . $filePath . '\', \'_blank\')" class="col-lg-4 col-xl-6 col-md-4" style="margin-bottom: 30px;">
            <div class="card file photo">
                <div class="card-header file-icon" style="cursor: pointer;">
                    <img src="' . $displayPath . '" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                </div>
                <div class="card-body file-info">
                    <p><strong>' . $fieldName . '</strong></p>
                    <span class="file-size" style="display:none">' . $fileSize . '</span>
                </div>
            </div>
        </div>';
} else {
    // Original version WITH QR code scanning
    echo '
        <div onclick="window.open(\'' . $filePath . '\', \'_blank\')" class="col-lg-4 col-xl-6 col-md-4" style="margin-bottom: 30px;" data-qr-scan="true" data-file-path="' . $filePath . '" data-file-ext="' . $fileExtension . '" data-container-id="' . $uniqueId . '">
            <div class="card file photo">
                <div class="card-header file-icon" style="cursor: pointer;">
                    <img src="' . $displayPath . '" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                </div>
                <div class="card-body file-info">
                    <p><strong>' . $fieldName . '</strong></p>
                    <div class="results-container" id="' . $uniqueId . '">
                        <div class="qr-scanning">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden"></span>
                            </div>
                            Scanning QR code...
                        </div>
                    </div>
                    <span class="file-size" style="display:none">' . $fileSize . '</span>
                </div>
            </div>
        </div>';
}
    }
} else {
    echo "<p style='color:white;'>No documents found for the provided reference ID.</p>";
}
?>

                                        
                                        
                                    </div>
                                </div>
                            </div>
                            
                         
                        </div>  


                        
                        <div class="col-lg-6 col-md-12">
                            
                            <div class="card" style="z-index: 1;">
                                <div class="card-body" style="position:relative;">

                                    <div class="remarksdivcon" id="draftingdivcon" style="overflow-y: auto;">
                                        <button type="button" onclick="hidedraftingcon()" class="btn btn-primary" style="position:absolute;right: 10px;top: 10px;display: flex;">
                                        <div style="padding-top: 1px;padding-left: 3px;">Close</div>
                                        </button>
                                        

                                        <?php if ($permit_for == 'Special Permit for Fireworks Display'): ?>
                                                
                                                <div class="container mt-4">
    <h3 class="mb-4">Drafting Permit</h3>
    <form id="DraftSpecialPermit"  method="POST">
         <input type="hidden" class="form-control" name="application_id" value="<?php echo $application_id;?>">
        

        <div class="row">
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Date of Fireworks Display</label>
                <input type="datetime-local" class="form-control" name="display_datetime" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Location of Fireworks Display</label>
                <input type="text" class="form-control" name="display_location" placeholder="Enter location" required value="<?php echo $locations[0]['mapaddress']; ?>">
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Purpose of Fireworks Display</label>
                <input type="text" class="form-control" name="display_purpose" placeholder="e.g. Fiesta Celebration">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Licensed Pyro Technician</label>
                <input type="text" class="form-control" name="pyro_technician" placeholder="Enter technician's name">
            </div>
        </div>

        <div class="row">
            
            
             <div class="col-md-6 mb-3">
                <label class="form-label">FDO License Number</label>
                <input type="text" class="form-control" name="fdo_license_number" placeholder="Enter FDO license number">
            </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Partner Police Station</label>
                <input type="text" class="form-control" name="partner_police_station" placeholder="Enter police station name">
            </div>
        </div>

        <div class="row">
           
          
            <div class="col-md-6 mb-3">
                <label class="form-label">Receipt Reference Number</label>
                <input type="text" class="form-control" name="reference_number" placeholder="Enter reference number">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Amount Paid</label>
                <input type="number" class="form-control" name="amount_paid" placeholder="Enter amount">
            </div>
        </div>

        <div class="row">
            
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Paid Date</label>
                <input type="date" class="form-control" name="pay_date">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="float:right;">Draft Permit</button>
    </form>
   <script>
    $(document).ready(function() {
        $('#DraftSpecialPermit').on('submit', function(e) {
            e.preventDefault();
            let appid = "<?php echo $application_id; ?>";
            var formData = $(this).serialize();

            $.ajax({
                url: 'api/draft_specialpermit.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var form = $('<form>', {
                        'action': 'spdf',
                        'method': 'POST'
                    });

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'application_id',
                        'value': appid
                    }));

                    form.appendTo('body').submit();
                },
                error: function(xhr, status, error) {
                    alert('Something went wrong! Please try again.');
                }
            });
        });
    });
</script>
</div>

                                        <?php elseif ($permit_for == 'Transport Firecrackers and Pyrotechnic Devices'): ?>
                                            <div>
                                               
                                                <div class="container mt-5">
    <h3 class="mb-4">Drafting Permit</h3>
    <div class="qr-result-warning" style="padding:10px">
  Note: We are unable to draft the permit at this time because our client has not yet provided the required permit information/documents.
</div>
    <form action="submit_form.php" method="POST" style="padding:0px;display:none">
        <div class="mb-3">
            <label for="application_date" class="form-label">Date of Application</label>
            <input type="text" class="form-control" id="application_date" name="application_date" readonly
                value="<?php echo htmlspecialchars($application_date_display); ?>">
        </div>

        <div class="mb-3">
            <label for="company_name" class="form-label">Company Name</label>
            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter company name" readonly value="<?php echo $company_name; ?>">
        </div>

        <div class="mb-3">
            <label for="display_location" class="form-label">Fireworks Display Location</label>
            <input type="text" class="form-control" id="display_location" name="display_location" placeholder="Enter location" required>
        </div>

        <div class="mb-3">
            <label for="display_schedule" class="form-label">Display Date and Time</label>
            <input type="datetime-local" class="form-control" id="display_schedule" name="display_schedule" required>
        </div>

        <div class="mb-3">
            <label for="payment_ref" class="form-label">Payment Reference Number</label>
            <input type="number" class="form-control" id="payment_ref" name="payment_ref" placeholder="Enter payment reference number" required>
        </div>

        <button type="submit"  style="float:right;margin-top: 15px;" class="btn btn-primary">Continue</button>
    </form>
</div>
                                            </div>

                                        <?php elseif ($permit_for == 'Permit To Sell Firecrackers And Pyrotechnics Devices'): ?>
                                            <div style="padding-top:20px">
                                                <h4>Drafting Permit</h4>
                                                 <div class="qr-result-warning" style="padding:10px">
  Note: We are unable to draft the permit at this time because our client has not yet provided the required permit information/documents.
</div>
                                            </div>
                                        <?php endif; ?>

                                        
                                    </div>

                                    <div>
                                    <div class="remarksdivcon" id="remarksdivcon" style="overflow-y:auto;">
    <button type="button" onclick="hidefeedbackcon()" class="btn btn-primary" style="position:absolute;right: 10px;top: 10px;display: flex;">
        <div style="padding-top: 1px;padding-left: 3px;">Close</div>
    </button>
  


  <?php if ($status != 'Rejected' && $status != 'Permit Issued'): ?> 

    <div class="col-md-12" style="padding:0px;margin-bottom: 10px;">
        <form id="feedbackForm">
            <label>Add Remarks</label>
            <textarea name="remarks_note" class="form-control" 
                style="min-height: 100px;max-height: 100px;" 
                maxlength="200" required
                oninput="updateCharCount(this)"></textarea>
            <small id="charCount">200 characters remaining</small>
            
            <!-- Document Selection Section -->
            <div class="mt-3">
                <label class="form-label">
                    <strong>Select documents to re-upload (optional):</strong>
                </label>
                <div id="documentCheckboxes" class="p-3"
                    style="background-color: #f8f9fa; border-radius: 5px; max-height: 250px; overflow:hidden; min-height: 80px; border: 1px solid #dee2e6;">
                    
                    <div class="text-center text-muted" style="padding: 20px;">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden"></span>
                        </div>
                        <div style="margin-top: 10px; font-size: 13px;">Loading documents...</div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="float:right;margin-top: 10px;">
                Submit
            </button>
        </form>
    </div>
    <br><br><hr>

<?php elseif ($status == 'Rejected'): ?> 

    <div class="form-group" style="margin-bottom: 15px;">
        <label style="font-weight: 600; margin-bottom: 8px;">
            <i class="material-icons" style="vertical-align: middle; font-size: 18px; color: #dc3545;">
                edit_note
            </i>
            Reason for Rejection
        </label>
        <textarea 
            class="form-control" 
            rows="4" 
            maxlength="500"
            readonly 
            style="resize: vertical; min-height: 100px;"><?php echo htmlspecialchars($reason_of_rejection); ?></textarea>
  
    </div>

    <hr>

<?php else: ?>
    <!-- intentionally left empty -->
<?php endif; ?>


     <label>All Remarks</label>
    <div id="remarksContainer" style="overflow-y:auto; max-height: 300px;"></div>
    
    <!-- Delete Remark Modal -->
    <div class="modal fade" id="deleteRemarkModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteModalTitle" style="color:white;">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="material-icons" style="color:white;">close</i>
                    </button>
                </div>
                <div class="modal-body" style="padding-top:20px">
                    Are you sure you want to delete this remark? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete it</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Reject Application Modal -->
<!-- Reject Application Modal -->
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="rejectModalTitle" style="color:white;">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">cancel</i>
                    Confirm Rejection
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="material-icons" style="color:white;">close</i>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <p style="font-size: 15px; margin-bottom: 15px;">Are you sure you want to reject this application?</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p style="margin: 5px 0;"><strong>Reference ID:</strong> <?php echo htmlspecialchars($ref_id); ?></p>
                    <p style="margin: 5px 0;"><strong>Permit Type:</strong> <?php echo htmlspecialchars($permit_for); ?></p>
                </div>

                <!-- Reason for Rejection -->
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="reason_of_rejection" style="font-weight: 600; margin-bottom: 8px;">
                        <i class="material-icons" style="vertical-align: middle; font-size: 18px; color: #dc3545;">edit_note</i>
                        Reason for Rejection <span style="color: #dc3545;">*</span>
                    </label>
                    <textarea 
                        id="reason_of_rejection" 
                        class="form-control" 
                        rows="4" 
                        maxlength="500"
                        placeholder="Please provide a detailed reason for rejecting this application..."
                        required
                        style="resize: vertical; min-height: 100px;"
                        oninput="updateRejectCharCount(this)"></textarea>
                    <small id="rejectCharCount" style="color: #6c757d; font-size: 12px;">500 characters remaining</small>
                </div>
                
                <div class="alert alert-warning" style="margin-bottom: 0;">
                    <i class="material-icons" style="vertical-align: middle; font-size: 18px;">warning</i>
                    <strong>Warning:</strong> This action will:
                    <ul style="margin: 10px 0 0 20px; padding-left: 0;">
                        <li>Set the application status to "Rejected"</li>
                        <li>Notify the client with your rejection reason</li>
                        <li>Save the rejection reason in the system</li>
                    </ul>
                    <strong style="color: #856404;">This action cannot be undone!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                    Cancel
                </button>
                <button type="button" id="confirmRejectBtn" class="btn btn-danger">
                    Yes, Reject Application
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- View Remark Details Modal (ENHANCED WITH REUPLOADS) -->
    <div class="modal fade" id="viewRemarkModal" tabindex="-1" role="dialog" aria-labelledby="viewRemarkModalTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="margin-top:70px">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #0f2c5a 0%, #0f2c5a 100%);">
                    <h5 class="modal-title" id="viewRemarkModalTitle" style="color:white;">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">comment</i>
                        Remark Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="material-icons" style="color:white;">close</i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 25px; max-height: 600px; overflow-y: auto;">
                    <!-- Date/Time -->
                    <div class="mb-4">
                        <label class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">schedule</i>
                            Date & Time
                        </label>
                        <div id="modalRemarkDate" style="font-size: 14px; color: #333; margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        </div>
                    </div>

                    <!-- Remark Message -->
                    <div class="mb-4">
                        <label class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">message</i>
                            Remark Message
                        </label>
                        <div id="modalRemarkMessage" style="font-size: 15px; line-height: 1.6; color: #333; margin-top: 5px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px; white-space: pre-wrap;">
                        </div>
                    </div>

                    <div id="modalClientRepliesSection" style="display: none; margin-top: 20px;">
    <label class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">reply</i>
        Client Replies
    </label>
    <div id="modalClientReplies" style="margin-top: 10px;">
        <!-- Client replies will be loaded here -->
    </div>
</div>

                    <!-- Documents Requested for Re-upload -->
                    <div id="modalDocumentsSection" style="display: none;">
                        <label class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">folder_open</i>
                            Documents Requested for Re-upload
                        </label>
                        <div id="modalRemarkDocuments" style="margin-top: 10px;">
                        </div>
                    </div>

                    <!-- Reuploaded Documents Section (NEW) -->
                    <div id="modalReuploadsSection" style="display: none; margin-top: 20px;">
                        <label class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">cloud_upload</i>
                            Reuploaded Documents
                        </label>
                        <div id="modalReuploads" style="margin-top: 10px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


// ============================================================================
// MAIN SCANNER FUNCTION
// ============================================================================
function scanDocument(filePath, fileExtension, containerId) {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif'];
    
    if (imageExtensions.includes(fileExtension.toLowerCase())) {
        scanImageFile(filePath, containerId);
    } else if (fileExtension.toLowerCase() === 'pdf') {
        scanPdfFile(filePath, containerId);
    } else {
        showResult(containerId, 'info', 'ℹ️ File type not supported');
    }
}


// Function to load and display documents with QR scanning
function loadDocumentsDisplay() {
    const container = document.querySelector('.docsmob');
    
    if (!container) {
        console.error('Documents container not found!');
        return;
    }
    
    let formData = new FormData();
    formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id'] ?? ''); ?>');

    fetch('api/get_documents_display.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.documents && data.documents.length > 0) {
            let html = '';
            
            data.documents.forEach((doc) => {
                const fileName = doc.file_name;
                const fieldName = doc.field_name;
                const fileExtension = doc.file_extension.toLowerCase();
                const fileSize = doc.file_size_formatted;
                const filePath = '../../client/uploads/' + fileName;
                const uniqueId = 'doc_' + doc.id;
                
                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif'];
                const displayPath = imageExtensions.includes(fileExtension) ? filePath : '../../client/notimage.png';
                
                if (fieldName === 'Proof Of Payment') {
                    // Version WITHOUT QR code scanning
                    html += `
                        <div onclick="window.open('${filePath}', '_blank')" class="col-lg-4 col-xl-6 col-md-4 fade-in-item" style="margin-bottom: 30px;">
                            <div class="card file photo">
                                <div class="card-header file-icon" style="cursor: pointer;">
                                    <img src="${displayPath}" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                                </div>
                                <div class="card-body file-info">
                                    <p><strong>${fieldName}</strong></p>
                                    <span class="file-size" style="display:none">${fileSize}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Original version WITH QR code scanning
                    html += `
                        <div onclick="window.open('${filePath}', '_blank')" class="col-lg-4 col-xl-6 col-md-4 fade-in-item" style="margin-bottom: 30px;" data-qr-scan="true" data-file-path="${filePath}" data-file-ext="${fileExtension}" data-container-id="${uniqueId}">
                            <div class="card file photo">
                                <div class="card-header file-icon" style="cursor: pointer;">
                                    <img src="${displayPath}" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                                </div>
                                <div class="card-body file-info">
                                    <p><strong>${fieldName}</strong></p>
                                    <div class="results-container" id="${uniqueId}">
                                        <div class="qr-scanning">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden"></span>
                                            </div>
                                            Scanning QR code...
                                        </div>
                                    </div>
                                    <span class="file-size" style="display:none">${fileSize}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            // Add timestamp at the end
            html += `
                <div class="col-12">
                    <div style="text-align: right; font-size: 11px; color: #999; padding: 10px;">
                        <i class="material-icons" style="font-size: 12px; vertical-align: middle;">refresh</i> 
                        Last updated: ${new Date().toLocaleTimeString()}
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
            
            // After updating DOM, scan documents for QR codes
            setTimeout(() => {
                const documentsToScan = container.querySelectorAll('[data-qr-scan="true"]');
                documentsToScan.forEach(function(docElement) {
                    const filePath = docElement.getAttribute('data-file-path');
                    const fileExtension = docElement.getAttribute('data-file-ext');
                    const containerId = docElement.getAttribute('data-container-id');
                    
                    scanDocument(filePath, fileExtension, containerId);
                });
            }, 500);
            
        } else if (data.success && (!data.documents || data.documents.length === 0)) {
            container.innerHTML = `
                <div class="col-12">
                    <p style='color:white; text-align: center; padding: 40px;'>No documents found for the provided reference ID.</p>
                </div>
            `;
        } else {
            console.error('Error loading documents:', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error fetching documents:', error);
        // Don't replace content on error to avoid disrupting user
    });
}

// Auto-refresh documents display every 15 seconds
setInterval(function() {
    loadDocumentsDisplay();
    console.log('Auto-refreshed documents display at:', new Date().toLocaleTimeString());
}, 60000);

// Initial load when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure scanDocument function is available
    setTimeout(() => {
        loadDocumentsDisplay();
    }, 1000);
});


function loadDocumentCheckboxes() {
    const container = document.getElementById('documentCheckboxes');
    
    if (!container) {
        console.error('documentCheckboxes container not found!');
        return;
    }
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center text-muted" style="padding: 20px;">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden"></span>
            </div>
            <div style="margin-top: 10px; font-size: 13px;">Loading documents...</div>
        </div>
    `;
    
    let formData = new FormData();
    formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id'] ?? ''); ?>');

    fetch('api/get_documents.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        text = text.trim();
        
        if (!text.startsWith('{')) {
            console.error('Response is not JSON. First 100 chars:', text.substring(0, 100));
            throw new Error('Invalid JSON response');
        }
        
        const data = JSON.parse(text);
        
        if (data.success && data.documents && data.documents.length > 0) {
            let html = '<div style="max-height: 250px; overflow-y: auto;">';
            
            data.documents.forEach((doc, index) => {
                const safeDoc = doc.replace(/[<>'"]/g, '');
                
                html += `
                    <div class="form-check" style="margin-bottom: 12px; padding-left: 0;">
                        <label class="form-check-label" style="display: flex; align-items: center; cursor: pointer; padding: 10px; border-radius: 5px; transition: background-color 0.2s; width: 100%;" 
                               onmouseover="this.style.backgroundColor='#e9ecef'" 
                               onmouseout="this.style.backgroundColor='transparent'">
                            <input class="form-check-input document-checkbox" 
                                   type="checkbox" 
                                   value="${safeDoc}" 
                                   id="doc_${index}" 
                                   name="selected_documents[]" 
                                   style="width: 18px; height: 18px; margin: 0 12px 0 0; cursor: pointer; flex-shrink: 0;">
                            <span style="font-size: 14px; font-weight: 500; color: #495057; flex: 1;">
                                <i class="material-icons" style="font-size: 18px; vertical-align: middle; margin-right: 5px; color: #667eea;">description</i>
                                ${safeDoc}
                            </span>
                        </label>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            
        } else if (data.success && (!data.documents || data.documents.length === 0)) {
            container.innerHTML = `
                <div class="text-center text-muted" style="padding: 20px;">
                    <i class="material-icons" style="font-size: 36px; color: #ccc;">folder_off</i>
                    <p style="margin-top: 10px; font-size: 13px;">No documents available</p>
                </div>
            `;
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error loading documents:', error);
        container.innerHTML = `
            <div class="text-center" style="padding: 20px;">
                <i class="material-icons" style="font-size: 36px; color: #dc3545;">error_outline</i>
                <p class="text-danger mb-0" style="margin-top: 10px; font-size: 13px;">Error: ${error.message}</p>
                <button onclick="loadDocumentCheckboxes()" class="btn btn-sm btn-primary mt-2">Retry</button>
            </div>
        `;
    });
}

// Load and display reuploaded documents for a specific log
function loadReuploadsForLog(logId, applicationId) {
    const formData = new FormData();
    formData.append('log_id', logId);
    formData.append('application_id', applicationId);

    return fetch('api/get_reuploads.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log(data)
        if (data.success && data.reuploads && data.reuploads.length > 0) {
            return data.reuploads;
        }
        return [];
    })
    .catch(error => {
        console.error('Error loading reuploads:', error);
        return [];
    });
}

// Load and display feedback con
function showfeedbackcon() {
    document.getElementById('showfeedbacksbtn').style.display='none';
    document.getElementById('remarksdivcon').style.height = '100%';
    document.getElementById('remarksdivcon').style.padding = '25px';
    
    setTimeout(function() {
        loadDocumentCheckboxes();
    }, 100);
}

function hidefeedbackcon() {
    document.getElementById('showfeedbacksbtn').style.display='block';
    document.getElementById('remarksdivcon').style.height = '0px';
    document.getElementById('remarksdivcon').style.padding = '0px';
}

// Auto-refresh documents every 15 seconds - runs continuously
setInterval(function() {
    console.log('Auto-refreshed documents at:', new Date().toLocaleTimeString());
}, 60000);

// Initial load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDocumentCheckboxes();
});

function updateCharCount(el) {
    let remaining = el.maxLength - el.value.length;
    document.getElementById('charCount').textContent = remaining + " characters remaining";
}

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Form submission handler
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const remarksTextarea = document.querySelector('textarea[name="remarks_note"]');
            if (!remarksTextarea || !remarksTextarea.value.trim()) {
                alert('Please enter a remark before submitting.');
                return;
            }

            let formData = new FormData();
            formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id'] ?? ''); ?>');
            formData.append('remarks_note', remarksTextarea.value);
            
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
            
            let client_id = '<?php echo $client_id; ?>';
            formData.append('client_id', client_id);
            
            // Collect selected documents
            const selectedDocs = [];
            document.querySelectorAll('.document-checkbox:checked').forEach(checkbox => {
                selectedDocs.push(checkbox.value);
            });
            
            formData.append('selected_documents', selectedDocs.join(', '));


            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            fetch('api/add_feedback.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                if (data.trim() === 'success' || data.includes('success')) {
                    // Clear form
                    remarksTextarea.value = '';
                    
                    // Uncheck all checkboxes
                    document.querySelectorAll('.document-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    updateCharCount(remarksTextarea);
                    fetchRemarks();
                    
                } else {
                    alert('Error adding remark: ' + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                alert('Connection error. Please try again.');
            });
        });
    }
    
    // Start fetching remarks
    fetchRemarks();
    setInterval(fetchRemarks, 3000);
});

function fetchRemarks() {
    let formData = new FormData();
    formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id'] ?? ''); ?>');

    fetch('api/fetch_remarks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const container = document.getElementById('remarksContainer');
        if (container) {
            container.innerHTML = data;
        }
    })
    .catch(error => console.error('Error fetching data:', error));
}

let deleteLogId = null;

function openDeleteModal(log_id){
        deleteLogId = log_id
        let modal = new bootstrap.Modal(document.getElementById('deleteRemarkModal'));
        window.deleteRemarkModal = modal;
        modal.show();
}

// Handle click events for both delete and view
// Load and display reuploaded documents for a specific log
function loadReuploadsForLog(logId, applicationId) {
    const formData = new FormData();
    formData.append('log_id', logId);
    formData.append('application_id', applicationId);

    return fetch('api/get_reuploads.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.reuploads && data.reuploads.length > 0) {
            return data.reuploads;
        }
        return [];
    })
    .catch(error => {
        console.error('Error loading reuploads:', error);
        return [];
    });
}

// Handle click events for viewing remark details
// Add this to inspector/view_application.php - Update the view remarks modal click handler

document.addEventListener("click", function(e) {
    if (e.target.closest(".viewRemarkCard")) {
        const card = e.target.closest(".viewRemarkCard");
        const date = card.getAttribute("data-date");
        const message = card.getAttribute("data-message");
        const documents = card.getAttribute("data-documents");
        const logId = card.getAttribute("data-log-id");
        const applicationId = card.getAttribute("data-application-id");

        // Set date and message
        document.getElementById('modalRemarkDate').textContent = date;
        document.getElementById('modalRemarkMessage').textContent = message;
        
        // Handle requested documents
        const docSection = document.getElementById('modalDocumentsSection');
        const docContainer = document.getElementById('modalRemarkDocuments');
        
        if (documents && documents.trim() !== '') {
            docSection.style.display = 'block';
            const docsArray = documents.split(',').map(d => d.trim()).filter(d => d !== '');
            
            let docHtml = '<div class="d-flex flex-wrap" style="gap: 10px;">';
            docsArray.forEach(doc => {
                docHtml += `
                    <div style="padding: 10px 15px; background: #e3f2fd; border-left: 3px solid #2196f3; border-radius: 5px; display: inline-flex; align-items: center;">
                        <i class="material-icons" style="font-size: 18px; color: #2196f3; margin-right: 8px;">description</i>
                        <span style="font-size: 13px; font-weight: 500; color: #1976d2;">${doc}</span>
                    </div>
                `;
            });
            docHtml += '</div>';
            docContainer.innerHTML = docHtml;
        } else {
            docSection.style.display = 'none';
        }
        
        // Fetch and display client replies from remark_replies column
        if (logId && applicationId) {
            const repliesSection = document.getElementById('modalClientRepliesSection');
            const repliesContainer = document.getElementById('modalClientReplies');
            
            // Show loading
            repliesSection.style.display = 'block';
            repliesContainer.innerHTML = `
                <div class="text-center text-muted" style="padding: 20px;">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <div style="margin-top: 10px; font-size: 13px;">Loading client replies...</div>
                </div>
            `;
            
            // Fetch replies
            fetch('api/get_client_replies.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'log_id=' + encodeURIComponent(logId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.replies && data.replies.length > 0) {
                    repliesSection.style.display = 'block';
                    
                    let repliesHtml = '<div style="max-height: 300px; overflow-y: auto;">';
                    
                    data.replies.forEach((reply, index) => {
                        const replyText = reply.reply_text || 'No reply text';
                        const replyDate = reply.created_at || 'N/A';
                        
                        repliesHtml += `
                            <div style="background: #e8f4f8; padding: 15px; margin-bottom: 12px; border-left: 4px solid #0f2c5a; border-radius: 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="font-size: 13px; color: #0f2c5a;">
                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">reply</i> 
                                        Reply 
                                    </strong>
                                    <small style="color: #666; font-size: 11px;">
                                        <i class="material-icons" style="font-size: 12px; ">schedule</i> 
                                        ${replyDate}
                                    </small>
                                </div>
                                <p style="margin: 0; font-size: 14px; color: #333; ">
                                    ${replyText}
                                </p>
                            </div>
                        `;
                    });
                    
                    repliesHtml += '</div>';
                    repliesContainer.innerHTML = repliesHtml;
                } else {
                    repliesSection.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading client replies:', error);
                repliesSection.style.display = 'none';
            });
        }
        
        // LOAD REUPLOADED DOCUMENTS with QR scanning
        if (logId && applicationId) {
            const reuploadsSection = document.getElementById('modalReuploadsSection');
            const uploadsContainer = document.getElementById('modalReuploads');
            
            // Show loading state
            reuploadsSection.style.display = 'block';
            uploadsContainer.innerHTML = `
                <div class="text-center text-muted" style="padding: 20px;">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <div style="margin-top: 10px; font-size: 13px;">Loading reuploaded documents...</div>
                </div>
            `;
            
            loadReuploadsForLog(logId, applicationId).then(reuploads => {
                if (reuploads && reuploads.length > 0) {
                    reuploadsSection.style.display = 'block';
                    
                    let uploadsHtml = '<div class="row docsmob">';
                    
                    reuploads.forEach((reupload, index) => {
                        const fieldDisplay = reupload.field_name.replace(/_/g, ' ');
                        const statusClass = reupload.status.toLowerCase();
                        const statusIcon = reupload.status === 'Approved' ? '✅' : 
                                          reupload.status === 'Rejected' ? '❌' : '⏳';
                        const uniqueId = 'reupload_qr_' + reupload.reupload_id + '_' + index;
                        let filePath = '../../client/uploads/' + reupload.file_name;
                        let filePath2 = '../../client/uploads/' + reupload.file_name;
                        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif'];
                        const isImage = imageExtensions.includes(reupload.file_extension.toLowerCase());

                        if (!isImage) {
                            filePath2 = '../../client/notimage.png';
                        }

                        if (fieldDisplay == 'Proof Of Payment'){
                            uploadsHtml += `
                                <div class="col-lg-4 col-xl-6 col-md-4" style="margin-bottom: 30px;" onclick="window.open('${filePath}', '_blank')">
                                    <div style="flex: 1;">
                                        <div class="card file photo">
                                            <div class="card-header file-icon" style="cursor: pointer;">
                                                <img src="${filePath2}" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                                            </div>
                                        </div>
                                        <div class="reupload-field-name" style="margin-top:-20px">
                                            ${fieldDisplay}
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            uploadsHtml += `
                                <div class="col-lg-4 col-xl-6 col-md-4" style="margin-bottom: 30px;" onclick="window.open('${filePath}', '_blank')">
                                    <div style="flex: 1;">
                                        <div class="card file photo">
                                            <div class="card-header file-icon" style="cursor: pointer;">
                                                <img src="${filePath2}" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                                            </div>
                                        </div>
                                        <div class="reupload-field-name" style="margin-top:-20px">
                                            ${fieldDisplay}
                                        </div>
                                        <div class="results-container" id="${uniqueId}" style="margin-top: 10px;">
                                            <div class="qr-scanning">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                    <span class="visually-hidden"></span>
                                                </div>
                                                Scanning QR code...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    
                    uploadsHtml += '</div>';
                    uploadsContainer.innerHTML = uploadsHtml;
                    
                    // Now scan each reuploaded document for QR codes
                    setTimeout(() => {
                        reuploads.forEach((reupload, index) => {
                            const uniqueId = 'reupload_qr_' + reupload.reupload_id + '_' + index;
                            const filePath = '../../client/uploads/' + reupload.file_name;
                            const fileExtension = reupload.file_extension.toLowerCase();
                            
                            scanDocument(filePath, fileExtension, uniqueId);
                        });
                    }, 300);
                } else {
                    reuploadsSection.style.display = 'none';
                }
            });
        }
        
        // Show the modal
        let modal = new bootstrap.Modal(document.getElementById('viewRemarkModal'));
        modal.show();
    }
});

// Helper function to show result HTML in modal containers
function showResultHtmlInModal(containerId, type, htmlContent) {
    const resultsContainer = document.getElementById(containerId);
    
    if (!resultsContainer) {
        console.error('Container not found:', containerId);
        return;
    }
    
    let className;
    
    switch(type) {
        case 'success':
            className = 'qr-result-success';
            break;
        case 'error':
            className = 'qr-result-error';
            break;
        case 'warning':
            className = 'qr-result-warning';
            break;
        default:
            className = 'qr-result-info';
    }
    
    resultsContainer.innerHTML = `<div class="qr-result-badge ${className}" style="font-size: 11px;">${htmlContent}</div>`;
}

// Helper function to show simple result text in modal containers
function showResultInModal(containerId, type, message) {
    const resultsContainer = document.getElementById(containerId);
    
    if (!resultsContainer) {
        console.error('Container not found:', containerId);
        return;
    }
    
    let className;
    
    switch(type) {
        case 'success':
            className = 'qr-result-success';
            break;
        case 'error':
            className = 'qr-result-error';
            break;
        case 'warning':
            className = 'qr-result-warning';
            break;
        default:
            className = 'qr-result-info';
    }
    
    resultsContainer.innerHTML = `<div class="qr-result-badge ${className}" style="font-size: 11px;">${message}</div>`;
}


// Open reject modal
function openRejectModal() {
    let modal = new bootstrap.Modal(document.getElementById('rejectApplicationModal'));
    window.rejectApplicationModal = modal;
    modal.show();
}

// Handle reject confirmation
// Character count function for rejection reason
function updateRejectCharCount(textarea) {
    const remaining = textarea.maxLength - textarea.value.length;
    document.getElementById('rejectCharCount').textContent = remaining + " characters remaining";
}

// Handle reject confirmation
document.getElementById("confirmRejectBtn")?.addEventListener("click", function() {
    const refId = '<?php echo htmlspecialchars($ref_id); ?>';
    const reasonTextarea = document.getElementById('reason_of_rejection');
    
    // Validate reason is not empty
    if (!reasonTextarea || !reasonTextarea.value.trim()) {
        alert('Please provide a reason for rejection before proceeding.');
        reasonTextarea?.focus();
        return;
    }
    
    const val_reason_of_rejection = reasonTextarea.value.trim();
    
    // Check minimum length (optional - adjust as needed)
    if (val_reason_of_rejection.length < 10) {
        alert('Please provide a more detailed reason (at least 10 characters).');
        reasonTextarea.focus();
        return;
    }
    
    // Disable button to prevent double clicks
    const originalHTML = this.innerHTML;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Rejecting...';
    
    // Generate current time in JavaScript
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
    
    let formData = new FormData();
formData.append('ref_id', refId);
formData.append('application_id', '<?php echo $application_id; ?>');
formData.append('current_time', currentTime);
formData.append('val_reason_of_rejection', val_reason_of_rejection);


    
    fetch('api/reject_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() == "success") {
           
            // Close modal
            const cancelBtn = document.querySelector("#rejectApplicationModal .btn.btn-secondary");
            if (cancelBtn) cancelBtn.click();
            
            // Show success message
            // Redirect back to applications list
            // backtostatus('rejected');


            window.location.reload();
        } else {
            console.log("Failed to reject application: " + data);
            // Re-enable button
            this.disabled = false;
            this.innerHTML = originalHTML;
        }
    })
    .catch(err => {
        console.error('Error rejecting application:', err);
        alert("An error occurred while rejecting the application.");
        // Re-enable button
        this.disabled = false;
        this.innerHTML = originalHTML;
    });
});

// Reset the textarea when modal is closed
document.getElementById('rejectApplicationModal')?.addEventListener('hidden.bs.modal', function () {
    const reasonTextarea = document.getElementById('reason_of_rejection');
    if (reasonTextarea) {
        reasonTextarea.value = '';
        updateRejectCharCount(reasonTextarea);
    }
});


document.getElementById("confirmDeleteBtn")?.addEventListener("click", function() {
    if (!deleteLogId) return;

    let formData = new FormData();
    formData.append('log_id', deleteLogId);

    fetch('api/delete_remark.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === "success") {
            const cancelBtn = document.querySelector("#deleteRemarkModal .btn.btn-secondary");
            if (cancelBtn) cancelBtn.click();
            fetchRemarks();
        } else {
            alert("Failed to delete remark.");
        }
    })
    .catch(err => console.error('Error deleting remark:', err));
});

function showdraftingcon() {
    const elem = document.getElementById('draftingdivcon');
    if (elem) {
        elem.style.height = '100%';
        elem.style.padding = '15px';
    }
}

function hidedraftingcon() {
    const elem = document.getElementById('draftingdivcon');
    if (elem) {
        elem.style.height = '0px';
        elem.style.padding = '0px';
    }
}

function backtostatus(status) {
    let backstatus = '';
    
    switch (status.toLowerCase()) {
        case 'all':
            backstatus = 'all';
            break;
        case 'under review':
            backstatus = 'underreview';
            break;
        case 'drafting permit':
            backstatus = 'draftingpermit';
            break;
        case 'endorsed to director':
            backstatus = 'endorsed';
            break;
        case 'for final approval':
            backstatus = 'forfinalapproval';
            break;
        case 'permit issued':
            backstatus = 'issued';
            break;
        case 'rejected':
            backstatus = 'rejected';
            break;
        default:
            alert('Unknown status: ' + status);
            return;
    }
    
    window.location.href = backstatus;
}

loadDocumentCheckboxes()
</script>

<style>
.form-check-input:checked {
    background-color: #19E915;
    border-color: #19E915;
}

 input[type="checkbox"]:checked {
            background-color: #19E915;
            border-color: #19E915;
        }


.badge.bg-warning {
    padding: 5px 10px;
    border-radius: 12px;
}

.gap-2 {
    gap: 0.5rem !important;
}

.viewRemarkCard {
    cursor: pointer;
    transition: all 0.3s ease;
}

.viewRemarkCard:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

.modal-lg {
    max-width: 700px;
}

#modalRemarkMessage {
    min-height: 80px;
}

.form-check-input {
    flex-shrink: 0;
}

.form-check {
    padding-left: 0 !important;
}
</style>
                                    </div>


 <?php if ($status != 'Permit Issued' && $status != 'Rejected'): ?>
    <button type="button" onclick="openRejectModal()" class="btn btn-danger" style="position:absolute;right: 10px;top: 10px;display: flex;">
    <i style="font-size: 15px;padding-top: 3px;" class="material-icons">close</i> 
    <div style="padding-top: 1px;padding-left: 3px;">Reject</div>
</button>
<?php else: ?>
      <img src="rejectedstamp.png" style="position:absolute;right: 10px;top: 10px;display: flex;height:70px;width: 70px;">
<?php endif; ?>
                                    
                                    









                                    <?php
                                     echo "<h4>" . htmlspecialchars($ref_id) . "</h4>";
                                     echo "<h6>" . htmlspecialchars($permit_for) . "</h6>";
                                     echo "<h6>" . $apply_date . "</h6>";
                                    ?>
                                   <hr>
                                    <?php
include '../../db_conn.php';

$ref_id = htmlspecialchars($_POST['ref_id']);

// Step 1: Get permit & client_id
$sql_permit = "SELECT * FROM applications WHERE ref_id = ?";
$stmt = $conn->prepare($sql_permit);
$stmt->bind_param("s", $ref_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $permit = $result->fetch_assoc();
    $client_id = $permit['client_id'];

    // Step 2: Get client email from clients_acc
    $sql_client = "SELECT email FROM clients_acc WHERE client_id = ?";
    $stmt = $conn->prepare($sql_client);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $client_result = $stmt->get_result();
    $client_email = ($client_result->num_rows > 0) ? $client_result->fetch_assoc()['email'] : 'N/A';

    echo '<div class="row">
            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($client_email) . '" readonly>
            </div>';

    // Step 3: Manufacturer info
    $sql_manu = "SELECT * FROM manufacturers_info WHERE client_id = ?";
    $stmt = $conn->prepare($sql_manu);
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $manu_result = $stmt->get_result();

    if ($manu_result->num_rows > 0) {
        $info = $manu_result->fetch_assoc();

        echo '
            <div class="col-md-6 mb-3">
                <label>Company Name</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['company_name']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Dealer Name</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['dealer_name']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Contact Number</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['contact_number']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Company Website</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['company_website']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Company Address</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['company_address']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Manufacturer License No</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['manufacturer_license_no']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Manufacturer Serial No</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['manufacturer_serial_no']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Manufacturer Expiry Date</label>
                <input type="text" class="form-control" value="' . date("F j, Y", strtotime($info['manufacturer_expiry_date'])) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Dealer License No</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['dealer_license_no']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Dealer Serial No</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['dealer_serial_no']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Dealer Expiry Date</label>
                <input type="text" class="form-control" value="' . date("F j, Y", strtotime($info['dealer_expiry_date'])) . '" readonly>
            </div>
        </div>';
    } else {
        // Step 4: Retailer info
        $sql_retailer = "SELECT * FROM retailers_info WHERE client_id = ?";
        $stmt = $conn->prepare($sql_retailer);
        $stmt->bind_param("s", $client_id);
        $stmt->execute();
        $ret_result = $stmt->get_result();

        if ($ret_result->num_rows > 0) {
            $info = $ret_result->fetch_assoc();

            function calculateAge($birthdate) {
                $birthDate = new DateTime($birthdate);
                $today = new DateTime('today');
                $diff = $birthDate->diff($today);

                if ($diff->y > 0) {
                    return $diff->y . ($diff->y === 1 ? " year old" : " years old");
                } elseif ($diff->m > 0) {
                    return $diff->m . ($diff->m === 1 ? " month old" : " months old");
                } else {
                    return $diff->d . ($diff->d === 1 ? " day old" : " days old");
                }
            }

            echo '
            <div class="col-md-6 mb-3">
                <label>Full Name</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['full_name']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Phone</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['phone']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Gender</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['gender']) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Birthdate</label>
                <input type="text" class="form-control" value="' . date("F j, Y", strtotime($info['bdate'])) . '" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Age</label>
                <input type="text" class="form-control" value="' . calculateAge($info['bdate']) . '" readonly>
            </div>

            <div class="col-md-12 mb-3">
                <label>Address</label>
                <input type="text" class="form-control" value="' . htmlspecialchars($info['address']) . '" readonly>
            </div>
            </div>';
        } else {
            echo '</div><div class="alert alert-warning">No client info found.</div>';
        }
    }
} else {
    echo '<div class="alert alert-danger">Permit not found.</div>';
}
?>

                                    
                                    <br>
                                    <div class="d-flex justify-content-between">
    <!-- Back button on the left -->
    <div>
        <button type="button" class="btn btn-primary" onclick="backtostatus('all')" style="display:flex;">
            <i style="font-size: 12px;padding-top: 4px;" class="material-icons">arrow_back</i> 
            <div style="padding-top: 1px;padding-left: 3px;">All Apps</div>
        </button>
    </div>

    <!-- Action buttons on the right -->
    <div>


  <?php if ($status !== 'Rejected'): ?>

    <?php if (
        $status != 'Permit Issued' &&
        $status != 'Drafting Permit' &&
        $status != 'Endorsed To Director'
    ): ?>
        <button type="button" id="draftingpermitbtn" onclick="showdraftingcon()" class="btn btn-primary" style="display:flex;">
            <i style="font-size: 12px; padding-top: 5px;" class="material-icons">edit</i> 
            <div style="padding-top: 1px; padding-left: 3px;">Drafting Permit</div>
        </button>
    <?php else: ?>
        <form id="viewpermitbtn" action="spdf" method="POST" style="display:inline;">
            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application_id); ?>">
            <button type="submit" class="btn btn-primary bg-success" style="display:flex;">
                <div style="padding-top: 1px; padding-left: 3px;">View Permit</div>
            </button>
        </form>
    <?php endif; ?>

<?php else: ?>

<?php endif; ?>





    </div>
</div>
                                    
                                </div>
                                <button type="button" id="showfeedbacksbtn" onclick="showfeedbackcon()" style="border-radius: 0px;" class="btn btn-primary" >
        <i style="font-size: 12px;" class="material-icons">comment</i> Remarks
        </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
                <?php include('partials/footer.php')?>
        </div>

      <!-- PDF.js and jsQR Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.1.1/dist/tesseract.min.js"></script>
<script>

function checkAndUpdateStatusTwoStep(refId) {
    $.ajax({
        url: 'api/get_current_status.php',
        method: 'POST',
        data: { ref_id: refId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const currentStatus = response.status;
                if (currentStatus === 'Replied') {
                    console.log('')
                    updateStatusToUnderReview(refId);
                } else {
                    console.log('')
                }
            } else {
                console.error('Error getting status:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error (get_current_status):', error);
        }
    });
}

function updateStatusToUnderReview(refId) {
    $.ajax({
        url: 'api/auto_update_status.php',
        method: 'POST',
        data: { ref_id: refId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.updated === true) {
                console.log('')
            } else {
                console.log('')
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error (auto_update_status):', error);
        }
    });
}

let refId = '<?php echo $ref_id; ?>';
checkAndUpdateStatusTwoStep(refId);
setInterval(function() {
    checkAndUpdateStatusTwoStep(refId);
}, 5000);

// ============================================================================
// TAMPERING DETECTION SYSTEM - FIELD DEFINITIONS
// ============================================================================
const tamperFields = [
    { key: 'OWNER', start: 'OWNER:', stop: 'LICENSE' },
    { key: 'LICENSE NUMBER', start: 'LICENSE NUMBER:', stop: 'APPLICATION' },
    { key: 'APPLICATION TYPE', start: 'APPLICATION TYPE:', stop: 'VALIDITY' },
    { key: 'VALIDITY LICENSE', start: 'VALIDITY LICENSE:', stop: 'This LTO' }
];

// Normalize text function
function normalize(text) {
    if (!text) return '';
    return text
        .replace(/[\u2013\u2014]/g, '-')
        .replace(/\s+/g, ' ')
        .trim();
}

// Extract field from text
function extractField(text, start, stop) {
    const s = text.indexOf(start);
    if (s === -1) return '';
    const from = s + start.length;
    const e = stop ? text.indexOf(stop, from) : text.length;
    return normalize(text.substring(from, e !== -1 ? e : undefined));
}

// ============================================================================
// DOCUMENT INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const documentsToScan = document.querySelectorAll('[data-qr-scan="true"]');
        
        documentsToScan.forEach(function(docElement) {
            const filePath = docElement.getAttribute('data-file-path');
            const fileExtension = docElement.getAttribute('data-file-ext');
            const containerId = docElement.getAttribute('data-container-id');
            
            scanDocument(filePath, fileExtension, containerId);
        });
    }, 500);
});

// ============================================================================
// SCAN IMAGE FILES (WITH TAMPERING DETECTION)
// ============================================================================
function scanImageFile(filePath, containerId) {
    showResult(containerId, 'info', '⏳ Loading image...');
    
    const img = new Image();
    img.crossOrigin = 'anonymous';
    
    img.onload = function() {
        try {
            // Create canvas with original image dimensions
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            
            showResult(containerId, 'info', '⏳ Scanning for QR code...');
            
            // STEP 1: Try multiple QR detection strategies
            let qrCodeValue = null;
            
            // Strategy 1: Direct scan at original size
            let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            let code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert"
            });
            
            if (code) {
                qrCodeValue = code.data;
                console.log('QR found (Strategy 1 - Original):', qrCodeValue);
            }
            
            // Strategy 2: Try with inverted colors
            if (!qrCodeValue) {
                code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "attemptBoth"
                });
                if (code) {
                    qrCodeValue = code.data;
                    console.log('QR found (Strategy 2 - Inverted):', qrCodeValue);
                }
            }
            
            // Strategy 3: Try with scaled-up image (2x)
            if (!qrCodeValue) {
                const scaledCanvas = document.createElement('canvas');
                scaledCanvas.width = img.width * 2;
                scaledCanvas.height = img.height * 2;
                const scaledCtx = scaledCanvas.getContext('2d');
                scaledCtx.drawImage(img, 0, 0, scaledCanvas.width, scaledCanvas.height);
                
                imageData = scaledCtx.getImageData(0, 0, scaledCanvas.width, scaledCanvas.height);
                code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "attemptBoth"
                });
                
                if (code) {
                    qrCodeValue = code.data;
                    console.log('QR found (Strategy 3 - 2x scale):', qrCodeValue);
                }
            }
            
            // Strategy 4: Try with scaled-down image (0.5x)
            if (!qrCodeValue) {
                const smallCanvas = document.createElement('canvas');
                smallCanvas.width = img.width * 0.5;
                smallCanvas.height = img.height * 0.5;
                const smallCtx = smallCanvas.getContext('2d');
                smallCtx.drawImage(img, 0, 0, smallCanvas.width, smallCanvas.height);
                
                imageData = smallCtx.getImageData(0, 0, smallCanvas.width, smallCanvas.height);
                code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "attemptBoth"
                });
                
                if (code) {
                    qrCodeValue = code.data;
                    console.log('QR found (Strategy 4 - 0.5x scale):', qrCodeValue);
                }
            }
            
            // Strategy 5: Try with contrast enhancement
            if (!qrCodeValue) {
                const contrastCanvas = document.createElement('canvas');
                contrastCanvas.width = img.width;
                contrastCanvas.height = img.height;
                const contrastCtx = contrastCanvas.getContext('2d');
                contrastCtx.drawImage(img, 0, 0);
                
                // Enhance contrast
                imageData = contrastCtx.getImageData(0, 0, contrastCanvas.width, contrastCanvas.height);
                const data = imageData.data;
                const contrast = 50; // Contrast factor
                const factor = (259 * (contrast + 255)) / (255 * (259 - contrast));
                
                for (let i = 0; i < data.length; i += 4) {
                    data[i] = factor * (data[i] - 128) + 128;     // Red
                    data[i + 1] = factor * (data[i + 1] - 128) + 128; // Green
                    data[i + 2] = factor * (data[i + 2] - 128) + 128; // Blue
                }
                
                contrastCtx.putImageData(imageData, 0, 0);
                imageData = contrastCtx.getImageData(0, 0, contrastCanvas.width, contrastCanvas.height);
                
                code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "attemptBoth"
                });
                
                if (code) {
                    qrCodeValue = code.data;
                    console.log('QR found (Strategy 5 - Enhanced contrast):', qrCodeValue);
                }
            }
            
            // Check if QR code was found
            if (!qrCodeValue) {
                showResult(containerId, 'warning', '⚠️ No QR code detected');
                return;
            }
            
            console.log('Final QR Code Value:', qrCodeValue);
            
            // STEP 2: Extract visible text using Tesseract
            showResult(containerId, 'info', '⏳ Extracting text...');
            
            Tesseract.recognize(
                canvas,
                'eng',
                {
                    logger: function(m) {
                        if (m.status === 'recognizing text') {
                            const progress = Math.round(m.progress * 100);
                            showResult(containerId, 'info', `⏳ Extracting text... ${progress}%`);
                        }
                    }
                }
            ).then(function(result) {
                const visibleText = normalize(result.data.text);
                
                // For images, there's no hidden layer, so both are the same
                const hiddenText = '';
                
                console.log('Image - Visible Text:', visibleText);
                
                // STEP 3: Process tampering detection
                processTamperingDetection(qrCodeValue, visibleText, hiddenText, containerId);
                
            }).catch(function(error) {
                console.error('OCR error:', error);
                showResult(containerId, 'error', '❌ OCR failed');
            });
            
        } catch (error) {
            console.error('Image scanning error:', error);
            showResult(containerId, 'error', '❌ Scanning error: ' + error.message);
        }
    };
    
    img.onerror = function() {
        showResult(containerId, 'error', '❌ Could not load image');
    };
    
    // Add timestamp to prevent caching issues
    img.src = filePath + '?t=' + new Date().getTime();
}


function preprocessImageForQR(canvas, ctx) {
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    
    // Convert to grayscale and apply threshold
    for (let i = 0; i < data.length; i += 4) {
        const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        const threshold = gray > 128 ? 255 : 0;
        
        data[i] = threshold;     // Red
        data[i + 1] = threshold; // Green
        data[i + 2] = threshold; // Blue
    }
    
    ctx.putImageData(imageData, 0, 0);
    return ctx.getImageData(0, 0, canvas.width, canvas.height);
}


// ============================================================================
// SCAN PDF FILES (WITH TAMPERING DETECTION)
// ============================================================================
async function scanPdfFile(filePath, containerId) {
    try {
        const response = await fetch(filePath);
        
        if (!response.ok) {
            throw new Error('Failed to load PDF');
        }
        
        const arrayBuffer = await response.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: new Uint8Array(arrayBuffer) }).promise;
        
        let qrCodeValue = null;
        let visibleText = '';
        let hiddenText = '';
        
        // Process first page only
        const page = await pdf.getPage(1);
        
        // GET HIDDEN TEXT (PDF text layer)
        const textContent = await page.getTextContent();
        hiddenText = normalize(textContent.items.map(i => i.str).join(' '));
        
        // Render page to canvas
        const viewport = page.getViewport({ scale: 2.8 });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        
        // STEP 1: Scan for QR code
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        qrCodeValue = code ? code.data : null;
        
        if (!qrCodeValue) {
            showResult(containerId, 'warning', 'No QR code attached');
            return;
        }
        
        // STEP 2: Get VISIBLE text using OCR
        showResult(containerId, 'info', '⏳ Extracting visible text...');
        
        const dataUrl = canvas.toDataURL('image/png');
        const ocrResult = await Tesseract.recognize(dataUrl, 'eng', {
            logger: function(m) {
                if (m.status === 'recognizing text') {
                    const progress = Math.round(m.progress * 100);
                    showResult(containerId, 'info', `⏳ Extracting text... ${progress}%`);
                }
            }
        });
        
        visibleText = normalize(ocrResult.data.text);
        
        console.log('PDF - Hidden Text:', hiddenText);
        console.log('PDF - Visible Text:', visibleText);
        
        // STEP 3: Process tampering detection
        processTamperingDetection(qrCodeValue, visibleText, hiddenText, containerId);
        
    } catch (error) {
        console.error('PDF scanning error:', error);
        showResult(containerId, 'error', '❌ PDF scan error');
    }
}

// ============================================================================
// PROCESS TAMPERING DETECTION
// ============================================================================
function processTamperingDetection(qrCodeValue, visibleText, hiddenText, containerId) {
    const isImage = hiddenText.trim() === '';  // ← key detection

    let tamperedFields = [];
    let extractedVisible = {};
    let extractedHidden = {};
    let finalValues = {};   // ← what we will actually send to DB / show as main value

    tamperFields.forEach(f => {
        const v = extractField(visibleText, f.start, f.stop);
        const h = extractField(hiddenText, f.start, f.stop);

        extractedVisible[f.key] = v;
        extractedHidden[f.key] = h;

        if (isImage) {
            // For images: only visible text exists → no tampering possible
            finalValues[f.key] = v;
        } else {
            // For PDFs: prefer hidden (original) unless tampered
            const isTampered = v && h && normalize(v) !== normalize(h);
            if (isTampered) {
                tamperedFields.push(f.key);
                finalValues[f.key] = v;           // send fake/visible to DB? or h? → your business rule
                // Most systems send visible here so fraud is visible in backend logs
            } else {
                finalValues[f.key] = h || v;
            }
        }
    });

    console.log('Final values to send:', finalValues);
    console.log('Tampered fields:', tamperedFields);

    // Show results (tampering only meaningful for PDFs)
    displayTamperingResults(containerId, isImage ? [] : tamperedFields, extractedVisible, extractedHidden, hiddenText);

    // Prepare data for DB check
    const dataToSend = {
        qr_code: qrCodeValue,
        owner: finalValues['OWNER'],
        license_number: finalValues['LICENSE NUMBER'],
        application_type: finalValues['APPLICATION TYPE'],
        validity_license: finalValues['VALIDITY LICENSE']
    };

    checkDocumentInDatabase(dataToSend, containerId);
}

// ============================================================================
// DISPLAY TAMPERING RESULTS
// ============================================================================
function displayTamperingResults(containerId, tamperedFields, extractedVisible, extractedHidden, hiddenText = '') {
    const resultsContainer = document.getElementById(containerId);
    if (!resultsContainer) return;

    const isImage = hiddenText.trim() === '';

    let html = '';

    if (!isImage && tamperedFields.length > 0) {
        html += `<div class="qr-result-badge qr-result-error" style="font-size:11px;font-weight:bold;margin-bottom:8px;">
            ⚠️ TAMPERING DETECTED
        </div>`;

        tamperFields.forEach(field => {
            const v = extractedVisible[field.key];
            const h = extractedHidden[field.key];
            const tampered = tamperedFields.includes(field.key);

            if (tampered) {
                html += `<div style="margin:6px 0;padding:6px;background:#fff3cd;border-left:3px solid #ffc107;border-radius:3px;">
                    <div style="font-size:10px;font-weight:bold;color:#856404;">${field.key}</div>
                    <div style="font-size:9px;color:#155724;"><strong>ORIGINAL:</strong> ${h || '(not found)'}</div>
                    <div style="font-size:9px;color:#721c24;"><strong>VISIBLE:</strong> ${v || '(not found)'}</div>
                </div>`;
            } else if (v || h) {
                html += `<div style="margin:4px 0;padding:4px;background:#e6fffa;border-left:3px solid #48bb78;border-radius:3px;">
                    <div style="font-size:10px;font-weight:bold;color:#2d7a5f;">${field.key}</div>
                    <div style="font-size:9px;color:#2d7a5f;">✓ ${h || v}</div>
                </div>`;
            }
        });
    } else {
        // Clean document OR image (no tampering info)
        const isProbablyRealPDF = !isImage && hiddenText.trim().length > 40;

        html += `<div class="qr-result-badge ${isProbablyRealPDF ? 'qr-result-success' : 'qr-result-info'}" style="font-size:11px;font-weight:bold;margin-bottom:8px;">`;

        if (isImage) {
            html += `Image-based no digital layer`;
        } else if (isProbablyRealPDF) {
            html += `✅ DOCUMENT IS AUTHENTIC`;
        } else {
            html += `⚠️ No reliable hidden text layer`;
        }

        html += `</div>`;

        // Show the best available values
        tamperFields.forEach(field => {
            const value = isImage 
                ? extractedVisible[field.key] 
                : (extractedHidden[field.key] || extractedVisible[field.key]);

            if (value) {
                html += `<div style="margin:4px 0;padding:4px;background:#e6fffa;border-left:3px solid #48bb78;border-radius:3px;">
                    <div style="font-size:10px;font-weight:bold;color:#2d7a5f;">${field.key}</div>
                    <div style="font-size:9px;color:#2d7a5f;">✓ ${value}</div>
                </div>`;
            }
        });
    }

    html += `<div id="${containerId}_db_verify" style="margin-top:8px;"></div>`;
    resultsContainer.innerHTML = html;
}
// ============================================================================
// CHECK DOCUMENT IN DATABASE (OPTIONAL VERIFICATION)
// ============================================================================
function checkDocumentInDatabase(dataToSend, containerId) {
    // Normalize data before sending
    const requestData = {
        qr_code: dataToSend.qr_code,
        owner: dataToSend.owner ? normalizeText(dataToSend.owner) : null,
        license_number: dataToSend.license_number ? normalizeText(dataToSend.license_number) : null,
        application_type: dataToSend.application_type ? normalizeText(dataToSend.application_type) : null,
        validity_license: dataToSend.validity_license ? normalizeText(dataToSend.validity_license) : null
    };
    
    // Get the database verification container
    const dbVerifyContainer = document.getElementById(containerId + '_db_verify');
    
    if (!dbVerifyContainer) {
        console.error('Database verify container not found');
        return;
    }
    
    // Show loading
    dbVerifyContainer.innerHTML = `<div style="font-size: 10px; color: #667eea; text-align: center; padding: 6px;">
        <div class="spinner-border spinner-border-sm" role="status" style="width: 12px; height: 12px;">
            <span class="visually-hidden"></span>
        </div>
        Verifying with database...
    </div>`;
    
    fetch('api/check_dataandqr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network error');
        }
        return response.json();
    })
    .then(data => {
        console.log('Database verification:', data);
        
        let resultHtml = '';
        
        if (data.success && data.found) {
            // Document found in database
            if (data.match_details && data.match_details.field_matches) {
                const fieldMatches = data.match_details.field_matches;
                const percentage = data.match_details.match_percentage;
                
                // Title with percentage
                resultHtml += `<div style="font-size: 10px; font-weight: bold; color: #0f2c5a; margin-bottom: 6px; border-bottom: 1px solid #dee2e6; padding-bottom: 4px;">
                    📊 Database Verification
                </div>`;
                
                resultHtml += '<div style="font-size: 10px; margin-top: 5px; text-align: left;">';
                
                // QR Code
                if (fieldMatches.qr_code) {
                    resultHtml += fieldMatches.qr_code.matches 
                        ? '✅ QR Code: Match<br>' 
                        : '❌ QR Code: No match<br>';
                } else {
                    resultHtml += '⚪ QR Code: Not scanned<br>';
                }
                
                // Owner
                if (fieldMatches.owner) {
                    resultHtml += fieldMatches.owner.matches 
                        ? '✅ Owner: Match<br>' 
                        : '❌ Owner: No match<br>';
                } else {
                    resultHtml += '⚪ Owner: Not scanned<br>';
                }
                
                // License Number
                if (fieldMatches.license_number) {
                    resultHtml += fieldMatches.license_number.matches 
                        ? '✅ License: Match<br>' 
                        : '❌ License: No match<br>';
                } else {
                    resultHtml += '⚪ License: Not scanned<br>';
                }
                
                // Application Type
                if (fieldMatches.application_type) {
                    resultHtml += fieldMatches.application_type.matches 
                        ? '✅ App Type: Match<br>' 
                        : '❌ App Type: No match<br>';
                } else {
                    resultHtml += '⚪ App Type: Not scanned<br>';
                }
                
                // Validity License
                if (fieldMatches.validity_license) {
                    resultHtml += fieldMatches.validity_license.matches 
                        ? '✅ Validity: Match<br>' 
                        : '❌ Validity: No match<br>';
                } else {
                    resultHtml += '⚪ Validity: Not scanned<br>';
                }
                
                resultHtml += '</div>';
                
                // Wrap in appropriate color based on percentage
                let bgColor = '#d4edda';
                let borderColor = '#c3e6cb';
                if (percentage < 100 && percentage >= 80) {
                    bgColor = '#fff3cd';
                    borderColor = '#ffeaa7';
                } else if (percentage < 80) {
                    bgColor = '#f8d7da';
                    borderColor = '#f5c6cb';
                }
                
                dbVerifyContainer.innerHTML = `<div style="background: ${bgColor}; border: 1px solid ${borderColor}; border-radius: 4px; padding: 6px; margin-top: 6px;">
                    ${resultHtml}
                </div>`;
                
            } else {
                dbVerifyContainer.innerHTML = `<div class="qr-result-badge qr-result-success" style="font-size: 10px; margin-top: 6px;">
                    ✅ Document found in database
                </div>`;
            }
        } else if (data.success && !data.found) {
            // Document NOT found in database
            resultHtml += `<div style="font-size: 10px; font-weight: bold; color: #721c24; margin-bottom: 6px; border-bottom: 1px solid #dee2e6; padding-bottom: 4px;">
                📊 Database Verification
            </div>`;
            
            resultHtml += '<strong style="font-size: 10px;">❌ QR Code: Not in database</strong><br>';
            resultHtml += '<div style="font-size: 10px; margin-top: 5px; text-align: left;">';
            
            resultHtml += requestData.qr_code 
                ? '' 
                : '⚪ QR Code: Not scanned<br>';
            
            resultHtml += requestData.owner 
                ? '' 
                : '⚪ Owner: Not scanned<br>';
            
            resultHtml += requestData.license_number 
                ? '' 
                : '⚪ License: Not scanned<br>';
            
            resultHtml += requestData.application_type 
                ? '' 
                : '⚪ App Type: Not scanned<br>';
            
            resultHtml += requestData.validity_license 
                ? '' 
                : '⚪ Validity: Not scanned<br>';
            
            resultHtml += '</div>';
            
            dbVerifyContainer.innerHTML = `<div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 6px; margin-top: 6px;">
                ${resultHtml}
            </div>`;
        } else {
            dbVerifyContainer.innerHTML = `<div class="qr-result-badge qr-result-error" style="font-size: 10px; margin-top: 6px;">
                ❌ Verification failed
            </div>`;
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        dbVerifyContainer.innerHTML = `<div class="qr-result-badge qr-result-error" style="font-size: 10px; margin-top: 6px;">
            ❌ Connection error
        </div>`;
    });
}

// ============================================================================
// NORMALIZE TEXT FOR DATABASE
// ============================================================================
function normalizeText(text) {
    if (!text) return '';
    
    text = text.replace(/\s+/g, ' ');
    text = text.replace(/[–—−‐‑‒―]/g, '-');
    text = text.replace(/\u00A0/g, ' ');
    text = text.replace(/,\s*/g, ', ');
    
    return text.trim().toLowerCase();
}

// ============================================================================
// DISPLAY RESULT HELPERS
// ============================================================================
function showResult(containerId, type, message) {
    const resultsContainer = document.getElementById(containerId);
    
    if (!resultsContainer) {
        console.error('Container not found:', containerId);
        return;
    }
    
    let className;
    
    switch(type) {
        case 'success':
            className = 'qr-result-success';
            break;
        case 'error':
            className = 'qr-result-error';
            break;
        case 'warning':
            className = 'qr-result-warning';
            break;
        default:
            className = '';
    }
    
    resultsContainer.innerHTML = `<div class="qr-result-badge ${className}">${message}</div>`;
}

function showResultHtml(containerId, type, htmlContent) {
    const resultsContainer = document.getElementById(containerId);
    
    if (!resultsContainer) {
        console.error('Container not found:', containerId);
        return;
    }
    
    let className;
    
    switch(type) {
        case 'success':
            className = 'qr-result-success';
            break;
        case 'error':
            className = 'qr-result-error';
            break;
        case 'warning':
            className = 'qr-result-warning';
            break;
        default:
            className = 'qr-result-info';
    }
    
    resultsContainer.innerHTML = `<div class="qr-result-badge ${className}">${htmlContent}</div>`;
}
</script>
        <script src="assets/plugins/jquery/jquery-3.1.0.min.js"></script>
        <script src="assets/plugins/bootstrap/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/js/lime.min.js"></script>
        <script src="assets/js/fireworks_anim.js"></script>
    </body>
</html>