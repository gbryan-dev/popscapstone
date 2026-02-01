<?php
require_once 'env.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Map Centered on Philippines Region 10 with Pin Selection</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 20px;
      }
      #searchContainer {
        margin-bottom: 15px;
      }
      #searchInput {
        width: 100%;
        max-width: 500px;
        padding: 12px;
        font-size: 16px;
        border: 2px solid #ccc;
        border-radius: 4px;
      }
      #searchInput:focus {
        outline: none;
        border-color: #06BA54;
      }
      #map {
        height: 500px;
        width: 100%;
        max-width: 800px;
        border: 2px solid #ccc;
        border-radius: 4px;
      }
      #info {
        margin-top: 10px;
        padding: 10px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        width: 100%;
        max-width: 800px;
        border-radius: 4px;
      }
    </style>
    <script>
      let marker;
      let map;
      let geocoder;
      let searchTimeout;
      
      function initMap() {
        const region10Center = { lat: 8.48, lng: 124.65 };
        map = new google.maps.Map(document.getElementById("map"), {
          center: region10Center,
          zoom: 8,
        });
        
        geocoder = new google.maps.Geocoder();
        
        map.addListener('click', function(event) {
          if (marker) {
            marker.setMap(null);
          }
          
          marker = new google.maps.Marker({
            position: event.latLng,
            map: map,
            title: "Selected Location",
            icon: {
              path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M 0,-30 L 0,-25 M 0,-30 L 3,-27 M 0,-30 L -3,-27 M 0,-30 L 4,-29 M 0,-30 L -4,-29 M 0,-30 L 3,-32 M 0,-30 L -3,-32 M 0,-30 L 1,-34 M 0,-30 L -1,-34",
              fillColor: "#06BA54",
              fillOpacity: 1,
              strokeColor: "#ffffff",
              strokeWeight: 1.5,
              scale: 1.3,
              anchor: new google.maps.Point(0, 0),
            },
          });
          
          const lat = event.latLng.lat();
          const lng = event.latLng.lng();
          
          geocoder.geocode({ location: event.latLng }, function(results, status) {
            let address = "Address not found";
            if (status === 'OK' && results[0]) {
              address = results[0].formatted_address;
            }
            
            document.getElementById('info').innerHTML = `
              <strong>Latitude:</strong> ${lat}<br>
              <strong>Longitude:</strong> ${lng}<br>
              <strong>Address:</strong> ${address}
            `;
          });
        });
      }
      
      function searchLocation() {
        const searchInput = document.getElementById('searchInput').value.trim();
        
        if (!searchInput) {
          return;
        }
        
        const searchQuery = searchInput + ', Northern Mindanao, Philippines';
        
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
              icon: {
                path: "M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M 0,-30 L 0,-25 M 0,-30 L 3,-27 M 0,-30 L -3,-27 M 0,-30 L 4,-29 M 0,-30 L -4,-29 M 0,-30 L 3,-32 M 0,-30 L -3,-32 M 0,-30 L 1,-34 M 0,-30 L -1,-34",
                fillColor: "#06BA54",
                fillOpacity: 1,
                strokeColor: "#ffffff",
                strokeWeight: 1.5,
                scale: 1.3,
                anchor: new google.maps.Point(0, 0),
              },
            });
            
            document.getElementById('info').innerHTML = `
              <strong>Latitude:</strong> ${lat}<br>
              <strong>Longitude:</strong> ${lng}<br>
              <strong>Address:</strong> ${results[0].formatted_address}
            `;
          }
        });
      }
      
      function handleSearchInput() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          searchLocation();
        }, 800);
      }
    </script>
  </head>
  <body>
    <h2>Region 10 (Northern Mindanao) Location Finder</h2>
    <div id="searchContainer">
      <input 
        type="text" 
        id="searchInput" 
        placeholder="Type barangay or city name (e.g., Carmen, Iligan, Valencia)..." 
        oninput="handleSearchInput()"
      />
    </div>
    <div id="map"></div>
    <div id="info">Click on the map to select a location or type in the search bar above.</div>
  </body>
</html>