<?php
require_once 'partials/auth.php';
include '../../env.php';
include '../../db_conn.php';

// Fetch all applications with location data
$query = "SELECT 
    app.ref_id,
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
)
";
$result = mysqli_query($conn, $query);

$locations = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('partials/head.php')?>
        
        <style>
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
            
          
            
        </style>
        
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap&libraries=places" async defer></script>
    </head>
    <body>
        <canvas id="canvas"></canvas>
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>
        <div class="lime-container">
            <div class="lime-body">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Title + Search -->
                                    <div style="display: flex; width: 100%; flex-direction: column; align-items: center; text-align: center;">
                                        <h5 class="card-title" style="margin: auto; font-size: 23px;">Activity Map</h5>
                                        <div class="form-inline search" style="margin-top: 10px;">
                                            <input class="form-control" id="searchInput" style="width:100%; text-align: center;"
                                                   type="search" placeholder="Type barangay or city name (e.g., Carmen, Iligan, Valencia)..." aria-label="Search">
                                        </div>
                                    </div>
                                    
                                    <!-- Legend -->
                                    
                                </div>
                                <div id="map"></div>

                                <div class="legend">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partials/footer.php')?>
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
          
          const region10Center = { lat: 8.48, lng: 124.65 };
          map = new google.maps.Map(document.getElementById("map"), {
            center: region10Center,
            zoom: 10,
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
          if (location.permit_for === 'Special Permit for Fireworks Display') {
             markerIcon = {
              path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M 0,-30 L 0,-25 M 0,-30 L 3,-27 M 0,-30 L -3,-27 M 0,-30 L 4,-29 M 0,-30 L -4,-29 M 0,-30 L 3,-32 M 0,-30 L -3,-32 M 0,-30 L 1,-34 M 0,-30 L -1,-34",
              fillColor: "#06BA54",
              fillOpacity: 1,
              strokeColor: "#ffffff",
              strokeWeight: 1.5,
              scale: 1.5,
              anchor: new google.maps.Point(0, 0),
            };
            
          } else if (location.permit_for === 'Permit To Sell Firecrackers And Pyrotechnics Devices') {
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
      <form action="view_application.php" method="POST" style="margin-top: 10px;">
        <input type="hidden" name="ref_id" value="${location.ref_id}">
        <button type="submit" style="padding: 5px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
          View Permit
        </button>
      </form>
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

        <!-- Javascripts -->
        <script src="assets/plugins/bootstrap/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/plugins/chartjs/chart.min.js"></script>
        <script src="assets/plugins/apexcharts/dist/apexcharts.min.js"></script>
        <script src="assets/js/lime.min.js"></script>
        <script src="assets/js/pages/dashboard.js"></script>
        <script src="assets/js/fireworks_anim.js"></script>
        <script src="assets/js/pages/charts.js"></script>
    </body>
</html>