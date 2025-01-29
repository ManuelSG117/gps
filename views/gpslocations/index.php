<?php

use yii\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\GpslocationsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->registerJsFile('@web/js/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title = 'Gpslocations';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
<link rel="stylesheet" href="/vendor/pickadate/themes/default.css">
<link rel="stylesheet" href="/vendor/pickadate/themes/default.date.css">
<link rel="stylesheet" href="/vendor/toastr/css/toastr.min.css">

<div class="main-content">
    <button class="floating-button" id="floatingButton" onclick="toggleButtonContainer()">+</button>
    <div class="sidebar" id="sidebar">   <button class="minimize-sidebar" onclick="toggleSidebar()">-</button>
        <h4>GPS</h4>
        <div class="search-container">
            <input type="text" id="gpsSearch" class="minimal-input gps-search" placeholder="Buscar" onkeyup="filterGpsList()">
            <span class="clear-search" onclick="clearSearch()">×</span>
        </div>
        <div class="gps-titles">
            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
            <span>Nombre</span>
            <span>Status</span>
            <span>Velocidad</span>
            <span>Opciones</span>
        </div>
        <div id="gpsList" class="gps-list"></div>
    </div>
    <div class="button-container" id="buttonContainer">
        <label for="startDate">Fecha Inicio:</label>
        <input name="datepicker" class="datepicker-default form-control" id="startDate">
        <label for="endDate">Fecha Fin:</label>
        <input name="datepicker" class="datepicker-default form-control" id="endDate">
        <select id="gpsSelector" class="minimal-select large-select"></select>
        <button class="minimal-button small-button" onclick="loadRoute()">Cargar Ruta</button>
        <button id="startRouteButton" class="minimal-button small-button hidden" onclick="startAnimation()">Iniciar Ruta</button>
        <button class="reset-button" onclick="resetMap()">
            <img src="https://img.icons8.com/?size=100&id=VG3PB5IAD9Oy&format=png&color=000000" alt="Reset" style="width: 20px; height: 20px;">
        </button>
        <button id="pauseResumeButton" class="pause-button hidden" onclick="toggleAnimation()">
        <i id="playPauseIcon" class="fas fa-play"></i>
    </button>
    
    </div>
    <br>
    <div id="map"></div>
    <button class="circular-button" id="maximizeButton" onclick="toggleSidebar()" style="display: none;">☰</button>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinners">
            <div class="spinners"></div>
            <p>Cargando ruta...</p>
        </div>
    </div>
</div>


<!-- Required vendors -->
<script src="/vendor/global/global.min.js"></script>
<script src="/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>

<!-- pickdate -->
<script src="/vendor/pickadate/picker.js"></script>
<script src="/vendor/pickadate/picker.time.js"></script>
<script src="/vendor/pickadate/picker.date.js"></script>

<!-- Pickdate -->
<script src="/js/plugins-init/pickadate-init.js"></script>

<script src="/vendor/toastr/js/toastr.min.js"></script>

<!-- All init script -->
<script src="/js/plugins-init/toastr-init.js"></script>