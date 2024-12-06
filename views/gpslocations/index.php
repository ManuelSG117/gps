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

<div class="main-content">
    <button class="floating-button" id="floatingButton" onclick="toggleButtonContainer()">+</button>
    <div class="sidebar"><h4>GPS</h4>
        <input type="text" id="gpsSearch" class="minimal-input" placeholder="Buscar" onkeyup="filterGpsList()">
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
        <input type="text" id="startDate" class="datepicker-default form-control picker__input picker__input--active picker__input--target" placeholder="Fecha Inicio">
        <input type="text" id="endDate" class="minimal-input flatpickr small-input" placeholder="Fecha Fin">
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
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.flatpickr', {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            onChange: function(selectedDates, dateStr, instance) {
                instance.close();
            }
        });
    });
</script>

<style>
    .small-input {
        width: 108px; /* Ajusta el tamaño según tus necesidades */
        margin: 10px;
    }
    .small-button {
        width: 108px; 
        height: 47px;
        margin: 10px;
        border: none;
        background-color: #222b40;
        color: white;
        border-radius: 5px;
        transition: background-color 0.3s;
        white-space: nowrap; /* Asegura que el texto no se divida en varias líneas */
        text-align: left; /* Alinea el texto a la izquierda */
        padding-left: 6px; /* Añade un poco de padding a la izquierda */
    }
    .large-select {
        width: 240px; /* Ajusta el tamaño según tus necesidades */
        margin: 10px;   

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
        transform: rotate(0deg); 
    }
    .reset-button img {
        width: 100%;
        height: 100%;
    }
</style>