<?php
require_once 'partials/auth.php';
include '../../db_conn.php'; 

include '../../env.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['ref_id'])) {
        $ref_id = htmlspecialchars($_POST['ref_id']);

        // First query: fetch permit details
        $sql_permit = "SELECT status, application_id, ref_id, permit_for, apply_date, client_id FROM applications WHERE ref_id = ?";
        $stmt_permit = $conn->prepare($sql_permit);
        $stmt_permit->bind_param("s", $ref_id);
        $stmt_permit->execute();
        $result_permit = $stmt_permit->get_result();

        if ($result_permit->num_rows > 0) {
            $permit = $result_permit->fetch_assoc();


            $status = $permit['status'];
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
    .card.file.photo {
        transition: border 0.3s ease;
        border: 2px solid transparent; /* default no visible border */
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

    max-height: 600px;overflow-y: auto;
    }
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
$sql_permit = "SELECT application_id FROM applications WHERE ref_id = ?";
$stmt_permit = $conn->prepare($sql_permit);
$stmt_permit->bind_param("s", $ref_id);
$stmt_permit->execute();
$result_permit = $stmt_permit->get_result();

$locations = [];

if ($result_permit->num_rows > 0) {
    $permit = $result_permit->fetch_assoc();
    $baseonthis_application_id = $permit['application_id'];
    
    // Fetch all applications with location data using prepared statement
    $query = "SELECT 
        app.application_id, 
        app.permit_for, 
        app.maplatitude, 
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
                                    
                                    <!-- Legend -->
                                    
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
              console.log('Google Maps not loaded yet. Retry attempt:', mapLoadAttempts);
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
          console.log('Map is still loading...');
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

$sql = "SELECT application_id FROM applications WHERE ref_id = ?";
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

    $sql_docs = "SELECT * FROM documents WHERE application_id = ?";
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $application_id);
    $stmt_docs->execute();
    $documents = $stmt_docs->get_result();

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    while ($doc = $documents->fetch_assoc()) {
        $fileName = htmlspecialchars($doc['file_name']);
        $fieldName = htmlspecialchars($doc['field_name']);
        $fileExtension = strtolower(htmlspecialchars($doc['file_extension']));
        $rawSize = isset($doc['file_size']) ? $doc['file_size'] : 0; // Just in case
        $fileSize = formatBytes($rawSize);
        $filePath = "../../client/uploads/" . $fileName; // actual uploaded file path

        // Check if extension is image type
        if (in_array($fileExtension, $imageExtensions) && file_exists($filePath)) {
            $displayPath = $filePath;
        } else {
            // fallback to notimage.png
            $displayPath = "../../client/notimage.png";
        }

        echo '
        <div class="col-lg-4 col-xl-6 col-md-4" style="margin-bottom: 30px;" onclick="window.open(\'' . $filePath . '\', \'_blank\')">
            <div class="card file photo">
                <div class="card-header file-icon">
                    <img src="' . $displayPath . '" alt="Document" style="width:100%; height:auto; max-height:400px;" />
                </div>
                <div class="card-body file-info">
                    <p>' . $fieldName . '</p>
                    <span class="file-size">' . $fileSize . '</span><br>
                </div>
            </div>
        </div>';
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
                            
                            <div class="card">
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
                <input type="text" class="form-control" name="display_location" placeholder="Enter location" required>
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

        <button type="submit" class="btn btn-primary bg-success" style="float:right;">Save Permit</button>
    </form>
   <script>
    $(document).ready(function() {
        $('#DraftSpecialPermit').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            let appid = "<?php echo $application_id; ?>";
            var formData = $(this).serialize(); // Serialize form data for submission

            $.ajax({
                url: 'api/draft_specialpermit.php', // Create a PHP script to handle form data and database insertion
                type: 'POST',
                data: formData,
                success: function(response) {
                    
            
                        // Create a form to submit to spdf.php using POST
                        var form = $('<form>', {
                            'action': 'spdf',
                            'method': 'POST'
                        });

                        // Add the application_id as a hidden input field
                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': 'application_id',
                            'value': appid // PHP echo for dynamic value
                        }));

                        // Append the form to the body and submit it
                        form.appendTo('body').submit();
                   

                },
                error: function(xhr, status, error) {
                    // Handle any errors
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
    <form action="submit_form.php" method="POST" style="padding:0px">
        <!-- Date of application -->
        <div class="mb-3">
            <label for="application_date" class="form-label">Date of Application</label>
            <input type="text" class="form-control" id="application_date" name="application_date" readonly
                value="<?php echo htmlspecialchars($application_date_display); ?>">


        </div>

        <!-- Company name -->
        <div class="mb-3">
            <label for="company_name" class="form-label">Company Name</label>
            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter company name" readonly value="<?php echo $company_name; ?>">
        </div>

        <!-- Fireworks display location -->
        <div class="mb-3">
            <label for="display_location" class="form-label">Fireworks Display Location</label>
            <input type="text" class="form-control" id="display_location" name="display_location" placeholder="Enter location" required>
        </div>

        <!-- Display date and time -->
        <div class="mb-3">
            <label for="display_schedule" class="form-label">Display Date and Time</label>
            <input type="datetime-local" class="form-control" id="display_schedule" name="display_schedule" required>
        </div>

        <!-- Payment reference number -->
        <div class="mb-3">
            <label for="payment_ref" class="form-label">Payment Reference Number</label>
            <input type="number" class="form-control" id="payment_ref" name="payment_ref" placeholder="Enter payment reference number" required>
        </div>

        <!-- Submit button -->
        <button type="submit"  style="float:right;margin-top: 15px;" class="btn btn-primary">Continue</button>
    </form>
</div>
                                            </div>

                                        <?php elseif ($permit_for == 'Permit To Sell Firecrackers And Pyrotechnics Devices'): ?>
                                            <div>
                                                <h4>Drafting Permit</h4>
                                                <p>For Retailers Permit</p>
                                            </div>
                                        <?php endif; ?>

                                        
                                    </div>





                                    <div class="remarksdivcon" id="remarksdivcon">
                                        <button type="button" onclick="hidefeedbackcon()" class="btn btn-primary" style="position:absolute;right: 10px;top: 10px;display: flex;">
                                        <div style="padding-top: 1px;padding-left: 3px;">Close</div>
                                        </button>
                                        
                                        <br>
                                        <div class="col-md-12 mb-3" style="padding:0px">
                                            <form id="feedbackForm">
                                                <label>Add Feedback</label>
                                                <textarea name="remarks_note" class="form-control" 
                                            style="min-height: 100px;max-height: 100px;" 
                                            maxlength="200" required
                                            oninput="updateCharCount(this)"></textarea>
                                                <small id="charCount">200 characters remaining</small>
                                                <button type="submit" class="btn btn-primary" style="float:right;margin-top: 10px;">Submit</button>
                                            </form>
                                        </div>
                                        <br>
                                        <br>    
                                        <hr>
                                        <label>All Feedbacks</label>
                                        <div id="remarksContainer" style="overflow-y:auto;height: 300px;padding-bottom: 100px;"></div>
                                        <div class="modal fade" id="deleteRemarkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="background: rgba(0, 0, 0, 0.5);">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h5 class="modal-title" id="exampleModalCenterTitle" style="color:white;">Confirm Deletion</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" >
                                                        <i class="material-icons" style="color:white;">close</i>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="padding-top:20px">
        Are you sure you want to delete this remark? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button  type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete it</button>
      </div>
                                            </div>
                                        </div>
                                    </div>



                                        <script>

function updateCharCount(el) {
    let remaining = el.maxLength - el.value.length;
    document.getElementById('charCount').textContent = remaining + " characters remaining";
}
                                            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id']); ?>');
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

    fetch('api/add_feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        this.reset(); // clear form
    })
    .catch(error => console.error('Error:', error));
});

function fetchRemarks() {
    let formData = new FormData();
    formData.append('ref_id', '<?php echo htmlspecialchars($_POST['ref_id']); ?>');

    fetch('api/fetch_remarks.php', { // your PHP file path
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('remarksContainer').innerHTML = data;
    })
    .catch(error => console.error('Error fetching data:', error));
}

fetchRemarks();
setInterval(fetchRemarks, 1000);



let deleteLogId = null;

document.addEventListener("click", function(e) {
    if (e.target.closest(".openDeleteModal")) {
        deleteLogId = e.target.closest(".openDeleteModal").getAttribute("data-id");
        let modal = new bootstrap.Modal(document.getElementById('deleteRemarkModal'));
        window.deleteRemarkModal = deleteRemarkModal;
        modal.show();
    }
});

document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
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



            fetchRemarks(); // refresh remarks
        } else {
            alert("Failed to delete remark.");
        }
    })
    .catch(err => console.error('Error deleting remark:', err));
});


</script>
                                    </div>





                                    
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
        </div>'; // close row
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
            </div>'; // close row
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
                <?php if ($status != 'Permit Issued' && $status != 'Drafting Permit' && $status != 'Endorsed To Director' && $status != 'Rejected'): ?>
    <button type="button" id="draftingpermitbtn" onclick="showdraftingcon()" class="btn btn-primary" style="display:flex;">
        <i style="font-size: 12px;padding-top: 5px;" class="material-icons">edit</i> 
        <div style="padding-top: 1px;padding-left: 3px;">Edit Permit</div>
    </button>
<?php else: ?>
      <button type="button" id="draftingpermitbtn" onclick="showdraftingcon()" class="btn btn-primary" style="display:flex;">
        <i style="font-size: 12px;padding-top: 5px;" class="material-icons">edit</i> 
        <div style="padding-top: 1px;padding-left: 3px;">Edit Permit</div>
    </button>
<?php endif; ?>


    </div>

    <!-- Action buttons on the right -->
    <div>





        <form id="viewpermitbtn" action="spdf" method="POST" style="display:inline;">
        <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application_id); ?>">
        <button type="submit" class="btn btn-primary bg-success" style="display:flex;">
            <div style="padding-top: 1px;padding-left: 3px;">View Permit</div>
        </button>
    </form>
       


<!-- <form id="viewpermitbtn" action="spdf" method="POST" style="display:inline;">
        <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application_id); ?>">
        <button type="submit" class="btn btn-primary bg-success" style="display:flex;">
            <div style="padding-top: 1px;padding-left: 3px;">View Permit</div>
        </button>
    </form> -->
       
    </div>
</div>



                                    
                                </div>
                                <button type="button" id="showfeedbacksbtn" onclick="showfeedbackcon()" style="border-radius: 0px;display:none;" class="btn btn-primary" >
        <i style="font-size: 12px;" class="material-icons">comment</i> Feedbacks
        </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
                <?php include('partials/footer.php')?>
        </div>
        
            
        <!-- Javascripts -->

        <script>
            function showfeedbackcon() {
                document.getElementById('showfeedbacksbtn').style.display='none';
                document.getElementById('remarksdivcon').style.height = '100%';
                document.getElementById('remarksdivcon').style.padding = '25px';
            }
            function hidefeedbackcon() {
                document.getElementById('showfeedbacksbtn').style.display='block';
                document.getElementById('remarksdivcon').style.height = '0px';
                document.getElementById('remarksdivcon').style.padding = '0px';
            }

            function showdraftingcon() {
                document.getElementById('draftingdivcon').style.height = '100%';
                document.getElementById('draftingdivcon').style.padding = '15px';
            }

            function hidedraftingcon() {
                document.getElementById('draftingdivcon').style.height = '0px';
                document.getElementById('draftingdivcon').style.padding = '0px';
            }


           function backtostatus(status) {
    let backstatus = '';

    // Normalize status to ensure case-insensitive comparison
    switch (status.toLowerCase()) {
        case 'all':
            backstatus = 'all';
            break;
        case 'under review':
            backstatus = 'underreview_apps';
            break;
        case 'drafting permit':
            backstatus = 'draftingpermit_apps';
            break;
        case 'endorsed to director':
            backstatus = 'endorsedtodirector_apps';
            break;
        case 'for final approval':
            backstatus = 'forfinalapproval_apps';
            break;
        case 'permit issued':
            backstatus = 'permitissued_apps';
            break;
        case 'rejected':
            backstatus = 'rejected_apps';
            break;
        default:
            alert('Unknown status: ' + status);
            return;
    }

        window.location.href = backstatus;

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