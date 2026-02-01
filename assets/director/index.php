<?php
require_once 'partials/auth.php'; 
include '../../db_conn.php';

// Initialize variables
$total_all_applications = 0;
$total_issued = 0;
$total_clients = 0;

// SQL to get count of applications that are not 'Permit Issued'
$sql_not_issued = "SELECT COUNT(*) as total FROM applications";
$result_not_issued = mysqli_query($conn, $sql_not_issued);
if ($result_not_issued && mysqli_num_rows($result_not_issued) > 0) {
    $row = mysqli_fetch_assoc($result_not_issued);
    $total_all_applications = $row['total'];
}

// SQL to get count of applications that are 'Permit Issued'
$sql_issued = "SELECT COUNT(*) as total FROM applications WHERE status = 'Permit Issued'";
$result_issued = mysqli_query($conn, $sql_issued);
if ($result_issued && mysqli_num_rows($result_issued) > 0) {
    $row = mysqli_fetch_assoc($result_issued);
    $total_issued = $row['total'];
}

// SQL to get total number of clients
$sql_clients = "SELECT COUNT(*) as total FROM clients_acc";
$result_clients = mysqli_query($conn, $sql_clients);
if ($result_clients && mysqli_num_rows($result_clients) > 0) {
    $row = mysqli_fetch_assoc($result_clients);
    $total_clients = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
   <?php include('partials/head.php')?>
   <style>
       
     @media (max-width: 480px) {
  .p0inmobile {
    padding: 0 !important;
  }

  .p25inmobile {
    padding: 10px !important;
    padding-bottom: 0px !important;
  }

  
}

   </style>

    </head>
    <body>
        
        
            
        <canvas id="canvas"></canvas>
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>


        <div class="lime-container" >
            <div class="lime-body">
                <div class="container">


                   
                    
                   


                    <div class="row">
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body p0inmobile">
                                    <p class="card-title p25inmobile"></p>
                                    <div id="apex3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h2 class="float-right"><?php echo $total_all_applications; ?></h2>
                                    <h5 class="card-title">Total Permits</h5>
                                    
                                    <p>All permits processed.</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar " role="progressbar" style="width: 100%;background: #D52941;" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h2 class="float-right"><?php echo $total_issued; ?></h2>
                                    <h5 class="card-title">Permit Issued</h5>
                                    
                                    <p>Approved permits.</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h2 class="float-right"><?php echo $total_clients; ?></h2>
                                    <h5 class="card-title">All Clients</h5>
                                    
                                    <p>Retailers and manufacturers.</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 100%;background: #0C0D50;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                          <div class="col-md-3" style="display:none;">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h2 class="float-right"><?php echo $total_clients; ?></h2>
                                    <h5 class="card-title">Total Earnings</h5>
                                    
                                    <p>Retailers and manufacturers.</p>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                      

<div class="col-md-8">
    <div class="card">
        <div class="card-body">
            <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
    <h5 class="card-title" style="margin: 0;">Permits Endorsed to Director</h5>
    <span class="seemorebtn" onclick="window.location.href='all'">See More</span>
</div>
            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Permit&nbsp;For</th>
                            <th scope="col">Client&nbsp;Name</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="get_applications">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
function loadApplications() {
    fetch(`api/pendingapps_limit6.php`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('get_applications').innerHTML = data;
        })
        .catch(error => console.error('Error loading applications:', error));
}

// Initial load
loadApplications();

// Auto-refresh every second
setInterval(() => loadApplications(), 1000);
</script>

</div>




                        <?php
include '../../db_conn.php';

// Get latest 5 clients and their names from retailers_info or manufacturers_info
$sql_clients = "
    SELECT c.client_id, c.created_at,
           r.full_name,
           m.dealer_name,
           m.company_name
    FROM clients_acc c
    LEFT JOIN retailers_info r ON c.client_id = r.client_id
    LEFT JOIN manufacturers_info m ON c.client_id = m.client_id
    ORDER BY c.created_at DESC
    LIMIT 5
";

$result_clients = mysqli_query($conn, $sql_clients);

function getInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    for ($i = 0; $i < min(2, count($words)); $i++) {
        $initials .= strtoupper(mb_substr($words[$i], 0, 1));
    }
    return $initials;
}
?>





<div class="col-md-4">
    <div class="card">
        <div class="card-body">
           
            <div style="display: flex; width: 100%; padding:0px;justify-content: space-between; align-items: center;">
    <h5 class="card-title">Latest Clients</h5>
    <span class="seemorebtn" style="margin-top:-15px">See More</span>
</div>
           

            <div class="social-media-list" style="margin-top:0px">
    <?php
    if ($result_clients && mysqli_num_rows($result_clients) > 0) {
        $seen_names = []; // Track displayed full_names
        
        while ($client = mysqli_fetch_assoc($result_clients)) {
            // Determine display name (priority: full_name > dealer_name > company_name > client_id)
            $name = $client['full_name'] ?: ($client['dealer_name'] ?: ($client['company_name'] ?: $client['client_id']));
            $name = trim($name);

            // Skip if this name was already displayed (case-insensitive recommended)
            $name_key = strtolower($name);
            if (isset($seen_names[$name_key])) {
                continue; // Skip duplicate
            }
            $seen_names[$name_key] = true;

            $initials = getInitials($name);
            ?>
            <div class="social-media-item" style="display:flex; align-items:center;">
                <div class="social-icon google" style="background: #D52941 !important;">
                    <span style="font-size: 16px"><?php echo htmlspecialchars($initials); ?></span>
                </div>
                <div class="social-text">
                    <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($name); ?></p>
                                 <span style="font-size: 0.9em; color: #666;">Created : <?php echo $client['created_at']; ?> </span>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p>No clients found.</p>";
    }
    ?>
</div>
        </div>
    </div>
</div>

                    </div>
                  
                </div>
            </div>
                  <?php include('partials/footer.php')?>
        </div>
        
        
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