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
        
    </head>
    <body>
        
        
            
        <canvas id="canvas"></canvas>
        <?php include('partials/sidebar.php')?>
        <?php include('partials/header.php')?>


        <div class="lime-container" >
            <div class="lime-body">
                <div class="container">
                    
                   

                    <div class="row">
                      

<div class="col-md-12">
    <div class="card">
        <div class="card-body">
           


<div style="display: flex; width: 100%; flex-direction: column; align-items: center;text-align: center;">
    <h5 class="card-title" style="margin: auto;font-size: 23px;">Issued Application Permits</h5>
    <p style="font-size:1px">&nbsp;</p>

</div>
            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference&nbsp;ID</th>
                            <th scope="col">Permit&nbsp;For</th>
                            <th scope="col">Client&nbsp;Name</th>
                            <th scope="col">Apply&nbsp;Date</th>
                            <th scope="col">Approval&nbsp;Date</th>
                            <th scope="col">Valid&nbsp;Until</th>
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
function loadApplications(status = 'Permit Issued') {
    fetch(`api/get_applications.php?status=${encodeURIComponent(status)}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('get_applications').innerHTML = data;
        })
        .catch(error => console.error('Error loading applications:', error));
}

// Initial load
loadApplications('Permit Issued');

// Auto-refresh every second
setInterval(() => loadApplications('Permit Issued'), 1000);
</script>

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