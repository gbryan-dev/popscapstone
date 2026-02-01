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


        <div class="lime-container">
            <div class="lime-body">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="page-title">
                                <nav aria-label="breadcrumb">
                                  <ol class="breadcrumb breadcrumb-separator-1">
                                    <li class="breadcrumb-item"><a href="#">UI Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Charts</li>
                                  </ol>
                                </nav>
                                <h3>Apex</h3>
                                <p class="page-desc">ApexCharts is an open-source modern charting library that helps developers to create beautiful and interactive visualizations for web pages.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Line Chart</h5>
                                    <div id="apex1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Area Chart</h5>
                                    <div id="apex2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Column Chart</h5>
                                    <div id="apex3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Bar Chart</h5>
                                    <div id="apex4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Candlestick Chart</h5>
                                    <div id="apex5"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Scatter Chart</h5>
                                    <div id="apex6"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Mixed Chart</h5>
                                    <div id="apex7"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                   
                    
                </div>
            </div>
                  <?php include('partials/footer.php')?>
        </div>
        
        
        <!-- Javascripts -->
        <script src="assets/plugins/jquery/jquery-3.1.0.min.js"></script>
        <script src="assets/plugins/bootstrap/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/plugins/apexcharts/dist/apexcharts.min.js"></script>
        <script src="assets/plugins/chartjs/chart.min.js"></script>
        <script src="assets/js/lime.min.js"></script>
        <script src="assets/js/pages/charts.js"></script>
    </body>
</html>