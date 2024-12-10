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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="/vendor/pickadate/themes/default.css">
<link rel="stylesheet" href="/vendor/pickadate/themes/default.date.css">
<div class="main-content">
    <button class="floating-button" id="floatingButton" onclick="toggleButtonContainer()">+</button>
    <div class="sidebar"><h4>GPS</h4>
        <input type="text" id="gpsSearch" class="minimal-input gps-search" placeholder="Buscar" onkeyup="filterGpsList()">
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
        <button class="minimal-button small-button" onclick="startAnimation()">Iniciar Ruta</button>
        <button class="reset-button" onclick="resetMap()">
            <img src="https://img.icons8.com/?size=100&id=VG3PB5IAD9Oy&format=png&color=000000" alt="Reset" style="width: 20px; height: 20px;">
        </button>
        </div>
    
    <br>
    <div id="map"></div>
    <div id="loadingScreen" class="loading-screen" style="display: none;">
        <div class="spinner"></div>
    </div>
</div>


<style>

.gps-search {
        width: 330px; /* Ajusta el ancho según tus necesidades */
        margin-left:-15px;
        box-sizing: border-box; /* Asegura que el padding y el borde se incluyan en el ancho total */
    }

    .small-input {
        width: 108px; /* Ajusta el tamaño según tus necesidades */
        margin: 10px;
    }
    .small-button {
        width: 108px; 
        height: 45px;
        margin: 10px;
        border: none;
        background-color: #222b40;
        color: white;
        border-radius: 5px;
        transition: background-color 0.3s;
        white-space: nowrap; 
        text-align: left; 
        padding-left: 6px; 
    }
    .large-select {
        width: 258px;
        height: 45px;
        margin: 19.5px;   
        margin-left:2px;

    }
  
    .reset-button {
        width: 40px;
        height: 40px;
        margin: 10px;
        border: none;
        background-color: #f0f0f0;
        float: right;
        border-radius: 5px;
        transition: transform 0.5s ease-in-out; 
    }
    .reset-button:hover {
        transform: rotate(360deg); 
    }
    .reset-button img {
        width: 100%;
        height: 100%;
    }
</style>  
     <!-- Required vendors -->
     <script src="/vendor/global/global.min.js"></script>
	<script src="/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    
    <!-- pickdate -->
    <script src="/vendor/pickadate/picker.js"></script>
    <script src="/vendor/pickadate/picker.time.js"></script>
    <script src="/vendor/pickadate/picker.date.js"></script>

    <!-- Pickdate -->
    <script src="/js/plugins-init/pickadate-init.js"></script>
   <script src="/js/custom.min.js"></script>
	<script src="/js/deznav-init.js"></script>
	