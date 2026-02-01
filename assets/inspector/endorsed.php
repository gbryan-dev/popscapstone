<?php require_once 'partials/auth.php'; ?>
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
    <h5 class="card-title" style="margin: auto;font-size: 23px;">Endorsed to Director Application Permits</h5>
    <p style="font-size:1px">&nbsp;</p>

</div>

            <div class="table-responsive" style="margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Reference&nbsp;ID</th>
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
function loadApplications(status = 'Endorsed to Director') {
    fetch(`api/get_applications.php?status=${encodeURIComponent(status)}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('get_applications').innerHTML = data;
        })
        .catch(error => console.error('Error loading applications:', error));
}

// Initial load
loadApplications('Endorsed to Director');

// Auto-refresh every second
setInterval(() => loadApplications('Endorsed to Director'), 1000);
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