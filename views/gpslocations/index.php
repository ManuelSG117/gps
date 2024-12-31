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
    <div class="sidebar"><h4>GPS</h4>
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
        <button class="minimal-button small-button" onclick="startAnimation()">Iniciar Ruta</button>
        <button class="reset-button" onclick="resetMap()">
            <img src="https://img.icons8.com/?size=100&id=VG3PB5IAD9Oy&format=png&color=000000" alt="Reset" style="width: 20px; height: 20px;">
        </button>
    </div>
    <br>
    <div id="map"></div>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinners">
            <div class="spinners"></div>
            <p>Cargando ruta...</p>
        </div>
    </div>
</div>

<style>
/* Contenedor superpuesto para bloquear interacciones */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.5); /* Fondo semitransparente */
    z-index: 1000; /* Asegurar que esté encima de todo */
    display: none; /* Oculto por defecto */
    pointer-events: none; /* Bloquea clics */
}

/* Habilitar el bloqueo cuando se muestra el spinner */
.loading-overlay.active {
    display: block;
    pointer-events: all; /* Bloquea clics mientras está activo */
}

/* Spinner de carga */
.loading-spinners {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1001; /* Encima del overlay */
    background: rgba(255, 255, 255, 0.8);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinners {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #000;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.loading-spinnes p {
    margin-top: 10px;
    font-size: 16px;
    color: #333;
}

.gps-search {
    width: 330px; /* Ajusta el ancho según tus necesidades */
    margin-left: -15px;
    box-sizing: border-box; /* Asegura que el padding y el borde se incluyan en el ancho total */
}

.search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.clear-search {
    position: absolute;
    right: 10px;
    cursor: pointer;
    font-size: 20px;
    color: #aaa;
}

.clear-search:hover {
    color: #000;
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
    margin-left: 2px;
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

.options-menu {
    position: absolute;
    top: 0;
    left: 100%; /* Position to the right of the button */
    margin-left: 10px; /* Add some space between the button and the menu */
    background-color: white;
    border: 1px solid #ccc;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    z-index: 1002; /* Ensure it is above other elements */
    width: 150px;
}

.options-menu div {
    padding: 10px;
    cursor: pointer;
}

.options-menu div:hover {
    background-color: #f0f0f0;
}
</style>

<script>
function clearSearch() {
    document.getElementById('gpsSearch').value = '';
    filterGpsList();
}

// document.addEventListener('DOMContentLoaded', function() {
//     toastr.info('This is an info toast', 'Info', {
//         positionClass: 'toast-top-right',
//         closeButton: true
//     });
//     toastr.warning('This is a warning toast', 'Warning', {
//         positionClass: 'toast-top-right',
//         closeButton: true
//     });
// });
</script>

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