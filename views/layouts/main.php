<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use app\assets\AppAsset;
AppAsset::register($this);
$this->registerCssFile('@web/css/style.css', [ 'depends' => [\app\assets\AppAsset::className()]]);



$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);


?>
  
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<link rel="icon" type="/image/png" sizes="16x16" href="./images/favicon.png">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link href="/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="/vendor/swiper/css/swiper-bundle.min.css" rel="stylesheet">
	<link href="/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="/vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
	<link href="/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
     <!-- Dashboard 1 -->
	 <!-- <script src="/js/dashboard/dashboard-2.js"></script> -->

<!-- <script src="/js/plugins-init/datatables.init.js"></script> -->

    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
   
	<script src="/js/custom.js"></script>
	<script src="/vendor/global/global.min.js"></script>
    
    

<?php $this->beginBody() ?>
</head>

<body>
<?php
if (!Yii::$app->user->isGuest) {
?>

<div id="main-wrapper">
  



     <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
		<img src="/images/capasu-blanco.png" alt="Capasu Logo" class="brand-title" width="150" height="24"/>

            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line">
						<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.7468 5.58925C11.0722 5.26381 11.0722 4.73617 10.7468 4.41073C10.4213 4.0853 9.89369 4.0853 9.56826 4.41073L4.56826 9.41073C4.25277 9.72622 4.24174 10.2342 4.54322 10.5631L9.12655 15.5631C9.43754 15.9024 9.96468 15.9253 10.3039 15.6143C10.6432 15.3033 10.6661 14.7762 10.3551 14.4369L6.31096 10.0251L10.7468 5.58925Z" fill="#452B90"/>
							<path opacity="0.3" d="M16.5801 5.58924C16.9056 5.26381 16.9056 4.73617 16.5801 4.41073C16.2547 4.0853 15.727 4.0853 15.4016 4.41073L10.4016 9.41073C10.0861 9.72622 10.0751 10.2342 10.3766 10.5631L14.9599 15.5631C15.2709 15.9024 15.798 15.9253 16.1373 15.6143C16.4766 15.3033 16.4995 14.7762 16.1885 14.4369L12.1443 10.0251L16.5801 5.58924Z" fill="#452B90"/>
						</svg>
					</span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->


		<!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
						<div class="header-left">
							<div class="dashboard_bar">
                                CAPASU
                            </div>
						</div>
                        <div class="header-right d-flex align-items-center">
						
							<ul class="navbar-nav">
								<li class="nav-item dropdown notification_dropdown">
									<a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
										<svg id="icon-light" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="svg-main-icon">
											<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
												<rect x="0" y="0" width="24" height="24"/>
												<path d="M12,15 C10.3431458,15 9,13.6568542 9,12 C9,10.3431458 10.3431458,9 12,9 C13.6568542,9 15,10.3431458 15,12 C15,13.6568542 13.6568542,15 12,15 Z" fill="#000000" fill-rule="nonzero"/>
												<path d="M19.5,10.5 L21,10.5 C21.8284271,10.5 22.5,11.1715729 22.5,12 C22.5,12.8284271 21.8284271,13.5 21,13.5 L19.5,13.5 C18.6715729,13.5 18,12.8284271 18,12 C18,11.1715729 18.6715729,10.5 19.5,10.5 Z M16.0606602,5.87132034 L17.1213203,4.81066017 C17.7071068,4.22487373 18.6568542,4.22487373 19.2426407,4.81066017 C19.8284271,5.39644661 19.8284271,6.34619408 19.2426407,6.93198052 L18.1819805,7.99264069 C17.5961941,8.57842712 16.6464466,8.57842712 16.0606602,7.99264069 C15.4748737,7.40685425 15.4748737,6.45710678 16.0606602,5.87132034 Z M16.0606602,18.1819805 C15.4748737,17.5961941 15.4748737,16.6464466 16.0606602,16.0606602 C16.6464466,15.4748737 17.5961941,15.4748737 18.1819805,16.0606602 L19.2426407,17.1213203 C19.8284271,17.7071068 19.8284271,18.6568542 19.2426407,19.2426407 C18.6568542,19.8284271 17.7071068,19.8284271 17.1213203,19.2426407 L16.0606602,18.1819805 Z M3,10.5 L4.5,10.5 C5.32842712,10.5 6,11.1715729 6,12 C6,12.8284271 5.32842712,13.5 4.5,13.5 L3,13.5 C2.17157288,13.5 1.5,12.8284271 1.5,12 C1.5,11.1715729 2.17157288,10.5 3,10.5 Z M12,1.5 C12.8284271,1.5 13.5,2.17157288 13.5,3 L13.5,4.5 C13.5,5.32842712 12.8284271,6 12,6 C11.1715729,6 10.5,5.32842712 10.5,4.5 L10.5,3 C10.5,2.17157288 11.1715729,1.5 12,1.5 Z M12,18 C12.8284271,18 13.5,18.6715729 13.5,19.5 L13.5,21 C13.5,21.8284271 12.8284271,22.5 12,22.5 C11.1715729,22.5 10.5,21.8284271 10.5,21 L10.5,19.5 C10.5,18.6715729 11.1715729,18 12,18 Z M4.81066017,4.81066017 C5.39644661,4.22487373 6.34619408,4.22487373 6.93198052,4.81066017 L7.99264069,5.87132034 C8.57842712,6.45710678 8.57842712,7.40685425 7.99264069,7.99264069 C7.40685425,8.57842712 6.45710678,8.57842712 5.87132034,7.99264069 L4.81066017,6.93198052 C4.22487373,6.34619408 4.22487373,5.39644661 4.81066017,4.81066017 Z M4.81066017,19.2426407 C4.22487373,18.6568542 4.22487373,17.7071068 4.81066017,17.1213203 L5.87132034,16.0606602 C6.45710678,15.4748737 7.40685425,15.4748737 7.99264069,16.0606602 C8.57842712,16.6464466 8.57842712,17.5961941 7.99264069,18.1819805 L6.93198052,19.2426407 C6.34619408,19.8284271 5.39644661,19.8284271 4.81066017,19.2426407 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
											</g>
										</svg>
										<svg id="icon-dark" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="svg-main-icon">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<rect x="0" y="0" width="24" height="24"/>
											<path d="M12.0700837,4.0003006 C11.3895108,5.17692613 11,6.54297551 11,8 C11,12.3948932 14.5439081,15.9620623 18.9299163,15.9996994 C17.5467214,18.3910707 14.9612535,20 12,20 C7.581722,20 4,16.418278 4,12 C4,7.581722 7.581722,4 12,4 C12.0233848,4 12.0467462,4.00010034 12.0700837,4.0003006 Z" fill="#000000"/>
										</g>
										</svg>	
									</a>
								</li>
								<li class="nav-item dropdown notification_dropdown">
									<a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 12H19C19.8284 12 20.5 12.6716 20.5 13.5C20.5 14.3284 19.8284 15 19 15H6C5.17157 15 4.5 14.3284 4.5 13.5C4.5 12.6716 5.17157 12 6 12H7.5L8.05827 6.97553C8.30975 4.71226 10.2228 3 12.5 3C14.7772 3 16.6903 4.71226 16.9417 6.97553L17.5 12Z" fill="#222B40"/>
											<path opacity="0.3" d="M14.5 18C14.5 16.8954 13.6046 16 12.5 16C11.3954 16 10.5 16.8954 10.5 18C10.5 19.1046 11.3954 20 12.5 20C13.6046 20 14.5 19.1046 14.5 18Z" fill="#222B40"/>
										</svg>
										<span class="badge bg-danger" id="notification-badge" style="position:absolute;top:8px;right:8px;display:none;">0</span>
									</a>
									<div class="dropdown-menu dropdown-menu-end" style="min-width:350px;max-width:400px;">
										<div style="display:flex;justify-content:flex-end;align-items:center;">
											<button id="mark-all-read-btn" class="btn btn-link btn-sm" style="color:#452B90;font-weight:bold;">Marcar todas como leídas</button>
										</div>
										<div id="DZ_W_Notification1" class="widget-media dz-scroll p-2" style="height:380px;max-height:380px;overflow-y:auto;">
											<!-- Notificaciones AJAX -->
										</div>
										<a class="all-notification" href="/notificaciones/index">Ver todas las notificaciones <i class="ti-arrow-end"></i></a>
									</div>
								</li>
								<li class="nav-item dropdown notification_dropdown">

								</li>
								<li class="nav-item dropdown notification_dropdown">

								</li>
								<li class="nav-item dropdown notification_dropdown">
								
									<div class="dropdown-menu dropdown-menu-end">
										<div id="DZ_W_TimeLine02" class="widget-timeline dz-scroll style-1 p-3 height370">
											<ul class="timeline">
												<li>
									</div>
								</li>
								<li class="nav-item ps-3">
									<div class="dropdown header-profile2">
										<a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-expanded="false">
											<div class="header-info2 d-flex align-items-center">
												<div class="header-media">
													<img src="/images/user.jpg" alt="">
												</div>
											</div>
										</a>
										<div class="dropdown-menu dropdown-menu-end">
											<div class="card border-0 mb-0">
												<div class="card-header py-2">
													<div class="products">
														<img src="/images/user.jpg" class="avatar avatar-md" alt="">
														<div>
															<?php
															if (!Yii::$app->user->isGuest) {
																$usuario = Yii::$app->user->identity;
																?>
																<h6><?= htmlspecialchars($usuario->username) ?></h6>
																<span><?= htmlspecialchars($usuario->correo_electronico) ?></span>
																<?php 
															} else { ?>
																<h6>Invitado</h6>
															<?php } ?>
														</div>	
													</div>
												</div>
												<div class="card-body px-0 py-2">
													<a href="app-profile-1.html" class="dropdown-item ai-icon ">
														<svg  width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 15.3462C8.11714 15.3462 4.81429 15.931 4.81429 18.2729C4.81429 20.6148 8.09619 21.2205 11.9848 21.2205C15.8524 21.2205 19.1543 20.6348 19.1543 18.2938C19.1543 15.9529 15.8733 15.3462 11.9848 15.3462Z" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 12.0059C14.5229 12.0059 16.58 9.94779 16.58 7.40969C16.58 4.8716 14.5229 2.81445 11.9848 2.81445C9.44667 2.81445 7.38857 4.8716 7.38857 7.40969C7.38 9.93922 9.42381 11.9973 11.9524 12.0059H11.9848Z" stroke="var(--primary)" stroke-width="1.42857" stroke-linecap="round" stroke-linejoin="round"/>
														</svg>

														<span class="ms-2">Profile </span>
													</a>
													<a href="my-project.html" class="dropdown-item ai-icon ">
														<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>

													
														<span class="ms-2">Notification </span>
													</a>
												</div>
												<div class="card-footer px-0 py-2">
													<a href="javascript:void(0);" class="dropdown-item ai-icon ">
														<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path fill-rule="evenodd" clip-rule="evenodd" d="M20.8066 7.62355L20.1842 6.54346C19.6576 5.62954 18.4907 5.31426 17.5755 5.83866V5.83866C17.1399 6.09528 16.6201 6.16809 16.1307 6.04103C15.6413 5.91396 15.2226 5.59746 14.9668 5.16131C14.8023 4.88409 14.7139 4.56833 14.7105 4.24598V4.24598C14.7254 3.72916 14.5304 3.22834 14.17 2.85761C13.8096 2.48688 13.3145 2.2778 12.7975 2.27802H11.5435C11.0369 2.27801 10.5513 2.47985 10.194 2.83888C9.83666 3.19791 9.63714 3.68453 9.63958 4.19106V4.19106C9.62457 5.23686 8.77245 6.07675 7.72654 6.07664C7.40418 6.07329 7.08843 5.98488 6.8112 5.82035V5.82035C5.89603 5.29595 4.72908 5.61123 4.20251 6.52516L3.53432 7.62355C3.00838 8.53633 3.31937 9.70255 4.22997 10.2322V10.2322C4.82187 10.574 5.1865 11.2055 5.1865 11.889C5.1865 12.5725 4.82187 13.204 4.22997 13.5457V13.5457C3.32053 14.0719 3.0092 15.2353 3.53432 16.1453V16.1453L4.16589 17.2345C4.41262 17.6797 4.82657 18.0082 5.31616 18.1474C5.80575 18.2865 6.33061 18.2248 6.77459 17.976V17.976C7.21105 17.7213 7.73116 17.6515 8.21931 17.7821C8.70746 17.9128 9.12321 18.233 9.37413 18.6716C9.53867 18.9488 9.62708 19.2646 9.63043 19.5869V19.5869C9.63043 20.6435 10.4869 21.5 11.5435 21.5H12.7975C13.8505 21.5 14.7055 20.6491 14.7105 19.5961V19.5961C14.7081 19.088 14.9088 18.6 15.2681 18.2407C15.6274 17.8814 16.1154 17.6806 16.6236 17.6831C16.9451 17.6917 17.2596 17.7797 17.5389 17.9393V17.9393C18.4517 18.4653 19.6179 18.1543 20.1476 17.2437V17.2437L20.8066 16.1453C21.0617 15.7074 21.1317 15.1859 21.0012 14.6963C20.8706 14.2067 20.5502 13.7893 20.111 13.5366V13.5366C19.6717 13.2839 19.3514 12.8665 19.2208 12.3769C19.0902 11.8872 19.1602 11.3658 19.4153 10.9279C19.5812 10.6383 19.8213 10.3981 20.111 10.2322V10.2322C21.0161 9.70283 21.3264 8.54343 20.8066 7.63271V7.63271V7.62355Z" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
															<circle cx="12.175" cy="11.889" r="2.63616" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
															</svg>

														<span class="ms-2">Settings </span>
													</a>
													<a href="/index.php/site/logout" class="dropdown-item ai-icon">
														<svg class="logout-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
														<span class="ms-2 text-danger">Logout </span>
													</a>
												</div>
											</div>
											
										</div>
									</div>
								</li>
							</ul>
						</div>
                    </div>
				</nav>
			</div>
		</div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

    <!--**********************************
            Sidebar start
        ***********************************-->

        <div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					<li class="menu-title">Capasu</li>
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.13478 20.7733V17.7156C9.13478 16.9351 9.77217 16.3023 10.5584 16.3023H13.4326C13.8102 16.3023 14.1723 16.4512 14.4393 16.7163C14.7063 16.9813 14.8563 17.3408 14.8563 17.7156V20.7733C14.8539 21.0978 14.9821 21.4099 15.2124 21.6402C15.4427 21.8705 15.756 22 16.0829 22H18.0438C18.9596 22.0024 19.8388 21.6428 20.4872 21.0008C21.1356 20.3588 21.5 19.487 21.5 18.5778V9.86686C21.5 9.13246 21.1721 8.43584 20.6046 7.96467L13.934 2.67587C12.7737 1.74856 11.1111 1.7785 9.98539 2.74698L3.46701 7.96467C2.87274 8.42195 2.51755 9.12064 2.5 9.86686V18.5689C2.5 20.4639 4.04738 22 5.95617 22H7.87229C8.55123 22 9.103 21.4562 9.10792 20.7822L9.13478 20.7733Z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Inicio</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/gpslocations/index">Ubicación en tiempo real</a></li>
					</ul>
					</li>
					
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 3H21V21H3V3Z" stroke="#90959F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M9 17V13" stroke="#90959F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M13 17V9" stroke="#90959F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M17 17V5" stroke="#90959F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>    
						<span class="nav-text">Reportes</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/gpsreport/index">Reporte Ruta</a></li>
						<li><a href="/gpsreport/report-stops">Reporte Paradas</a></li>
					</ul>
					</li>
				
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V8H20V18ZM4 6V6.01H20V6H4Z" fill="#90959F"/>
								<path d="M12 12C13.1 12 14 11.1 14 10C14 8.9 13.1 8 12 8C10.9 8 10 8.9 10 10C10 11.1 10.9 12 12 12ZM12 14C10.34 14 8 14.67 8 16V17H16V16C16 14.67 13.66 14 12 14Z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Conductores</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/conductores/index">Ver Conductores</a></li>
						<li><a href="/conductores/create">Crear Conductor</a></li>
					</ul>
					</li>
					
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Vehículos</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/vehiculos/index">Ver Vehículos</a></li>
						<li><a href="/vehiculos/create">Crear Vehículo</a></li>
					</ul>
					</li>
					
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Dispositivos</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/dispositivos/index">Ver Dispositivos</a></li>
						<li><a href="/dispositivos/create">Crear Dispositivo</a></li>
					</ul>
					</li>

					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Geocerca</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/geocerca/index">Ver Geozona</a></li>
						<li><a href="/vehiculo-geocerca/index">Asignar Geozona</a></li>
					</ul>
					</li>
					
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Pólizas de Seguro</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/poliza-seguro/index">Ver Pólizas</a></li>
						<li><a href="/poliza-seguro/create">Crear Póliza</a></li>
					</ul>
					</li>
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Mantenimiento Vehicular</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/reparacion-vehiculo/index">Ver servicios</a></li>
						<li><a href="/reparacion-vehiculo/index">Crear servicio</a></li>
					</ul>
					</li>
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="#90959F"/>
							</svg>
						</div>    
						<span class="nav-text">Usuarios</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="/usuarios/index">Ver Usuarios</a></li>
						<li><a href="/usuarios/create">Crear Usuario</a></li>
					</ul>
					</li>
				</ul>

				<div class="copyright">
					<p>Capasu © <span class="current-year">2025</span> Todos los derechos reservados</p>
					
				</div>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->


<!--**********************************
        Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <?= $content ?>
        </div>
    </div>
</div>
<!--**********************************
        Content body end
***********************************-->


        

<div class="footer">
            <div class="copyright">
                <p>Copyright © Developed by <a href="https://www.capasu.gob.mx/" target="_blank">Capasu</a> <span class="current-year">2024</span></p>
            </div>
        </div>

</div>
<?php } else { ?>
    <?= $content ?>
<?php } ?>
<?php $this->endBody() ?>



</body>
</html>
<?php $this->endPage() ?>



    <!-- Required vendors -->
	<script src="/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/metismenu"></script>

	<script>
		jQuery(document).ready(function(){
			setTimeout(function(){
				dzSettingsOptions.version = 'light';
				new dzSettings(dzSettingsOptions);

				setCookie('version','light',365);
			},)
		});
	</script>

<!-- Notificaciones JS -->
<!-- <script src="/js/notificaciones.js"></script> -->

<!-- SweetAlert2 para alertas globales -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- <script>
let gpsQueueAlertActive = false;
function checkGpsQueueAlert() {
    // Si el usuario eligió no volver a mostrar, no mostrar la alerta
    if (localStorage.getItem('gpsQueueAlertDismissed') === 'true') {
        return;
    }
    $.get('/gpslocations/queue-alert', function(data) {
        if (data.alert && !gpsQueueAlertActive) {
            gpsQueueAlertActive = true;
            Swal.fire({
                icon: 'warning',
                title: '¡Alerta de procesamiento GPS!',
                text: 'El sistema está experimentando una acumulación inusual de datos GPS. Por favor, contacte a soporte si el problema persiste.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                showDenyButton: true,
                denyButtonText: 'No volver a mostrar',
                didClose: () => { gpsQueueAlertActive = false; }
            }).then((result) => {
                if (result.isDenied) {
                    localStorage.setItem('gpsQueueAlertDismissed', 'true');
                }
            });
        } else if (!data.alert && gpsQueueAlertActive) {
            gpsQueueAlertActive = false;
            Swal.close();
        }
    });
}
setInterval(checkGpsQueueAlert, 30000); // cada 30 segundos
checkGpsQueueAlert();
</script> -->

<style>
.deznav .metismenu  {
    text-align: center !important; /* Centra el texto horizontalmente */
    justify-content: center !important; /* Centra el contenido horizontalmente */
    align-items: center !important; /* Centra el contenido verticalmente */
}
.brand-title {
    margin-top: 25px;
    margin-left: 3.9375rem !important;
}

#notification-badge {
    position: relative;
    top: -8px !important;
    right: -8px !important;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
    background: #e74c3c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

</style>
