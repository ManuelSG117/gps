<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
$this->title = 'Capasu GPS';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/swiper/css/swiper-bundle.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link class="main-css" href="css/style.css" rel="stylesheet">
</head>
<body>
    <div id="main-wrapper">
        <div class="nav-header">
            <a href="index.html" class="brand-logo">
                <!-- SVG Logo -->
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line">
                        <!-- SVG Hamburger -->
                    </span>
                </div>
            </div>
        </div>
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="dashboard_bar">Dashboard</div>
                        </div>
                        <div class="header-right d-flex align-items-center">
                            <div class="input-group search-area">
                                <input type="text" class="form-control" placeholder="Search here...">
                                <span class="input-group-text"><a href="javascript:void(0)">
                                    <!-- SVG Search -->
                                </a></span>
                            </div>
                            <ul class="navbar-nav">
                                <!-- Navbar Items -->
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="deznav">
            <div class="deznav-scroll">
                <ul class="metismenu" id="menu">
                    <li class="menu-title">Capasu</li>
                    <li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
                        <div class="menu-icon">
                            <!-- SVG Icon -->
                        </div>    
                        <span class="nav-text">Dashboard</span>
                        </a>
                        <ul aria-expanded="false">
                            <!-- Submenu Items -->
                        </ul>
                    </li>    
                </ul>
                <div class="copyright">
                    <p>Capasu © <span class="current-year">2024</span> All Rights Reserved</p>
                    <p>Made with <span class="heart"></span> by Capasu</p>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-3 respo col-xxl-4 col-lg-5">
                        <div class="row">
                            <!-- Content -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="copyright">
                <p>Copyright © Developed by <a href="https://www.capasu.gob.mx/" target="_blank">Capasu</a> <span class="current-year">2024</span></p>
            </div>
        </div>
    </div>
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/chart-js/chart.bundle.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/apexchart/apexchart.js"></script>
    <script src="js/dashboard/dashboard-1.js"></script>
    <script src="vendor/draggable/draggable.js"></script>
    <script src="vendor/swiper/js/swiper-bundle.min.js"></script>
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/js/dataTables.buttons.min.js"></script>
    <script src="vendor/datatables/js/buttons.html5.min.js"></script>
    <script src="vendor/datatables/js/jszip.min.js"></script>
    <script src="js/plugins-init/datatables.init.js"></script>
    <script src="vendor/bootstrap-datetimepicker/js/moment.js"></script>
    <script src="vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script src="vendor/jqvmap/js/jquery.vmap.min.js"></script>
    <script src="vendor/jqvmap/js/jquery.vmap.world.js"></script>
    <script src="vendor/jqvmap/js/jquery.vmap.usa.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/deznav-init.js"></script>
    <script>
        jQuery(document).ready(function(){
            setTimeout(function(){
                dzSettingsOptions.version = 'light';
                new dzSettings(dzSettingsOptions);
                setCookie('version','light');
            },1500)
        });
    </script>
</body>
</html>