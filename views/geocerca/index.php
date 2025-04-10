<?php

use app\models\Geocerca;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\GeocercaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->registerJsFile('@web/js/geocerca.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title = 'Geocercas';
$this->params['breadcrumbs'][] = $this->title;
$geofences = Geocerca::find()->all();
$geofencesJson = \yii\helpers\Json::encode(array_map(function($geofence) {
    return [
        'id' => $geofence->id,
        'name' => $geofence->name,
        'description' => $geofence->description,
        'coordinates' => $geofence->coordinates
    ];
}, $geofences));
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script>
var geofencesData = <?= $geofencesJson ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE&libraries=drawing"></script>

<div class="geocerca-index">

    <!-- <p>
        <button id="save-polygon-changes" class="btn btn-warning" style="display: none;">Guardar Cambios</button>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    

    <div id="map"></div>
    
    <div id="info"></div>

    <div class="sidebar">
        <input type="text" class="search-box" placeholder="Buscar geofence..." id="searchGeofence">
        <div class="gps-titles">
            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
            <span>Nombre</span>
        </div>
        <div class="geofence-list" id="geofenceList">
            
        </div>
    </div>

    <!-- Floating Button -->
    <button id="floating-button" class="floating-button">+</button>

    <div class="modal fade" id="geofenceModal" tabindex="-1" aria-labelledby="geofenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="geofenceModalLabel">Nueva Geofence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="geofenceForm">
                        <input type="hidden" id="geofenceId">
                        <div class="mb-3">
                            <label for="geofenceName" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="geofenceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="geofenceDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="geofenceDescription" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="saveGeofenceData">Guardar</button>
                </div>
            </div>
        </div>
    </div>

</div>



<style>
    /* Add a new class for the geofence list items */
    .geofence-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        background-color: #fff;
        transition: all 0.3s ease;
        margin-top: 5px;
    }
    
    .geofence-item:hover {
        background-color: #f5f5f5;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .geofence-item input[type="checkbox"] {
        margin-right: 15px;
        transform: scale(1.5);
        accent-color: #222b40;
        flex: 0 0 20px;
    }
    
    .geofence-item label {
        flex: 1;
        font-size: 12px;
        color: #333;
        font-weight: 500;
        cursor: pointer;
    }
    
    /* Add styles for the geofence list container */
    .geofence-list {
        max-height: calc(100% - 80px);
        overflow-y: auto;
        padding-right: 5px;
    }

      #save-geofence {
    margin: 10px;
    padding: 10px;

    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 5px;   
}

#save-geofence:hover {
    background-color: #45a049;
    animation: pulse 1s infinite;

}
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.modal-content {
    border-radius: 10px;
}

.modal-header {
    background-color: #f8f9fa;
    border-radius: 10px 10px 0 0;
}

.modal-footer {
    background-color: #f8f9fa;
    border-radius: 0 0 10px 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
}

.delete-section {
    transition: opacity 0.3s ease;
}

.btn-outline-danger {
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
    transform: scale(1.05);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

#saveGeofenceData {
    border: none;
}




.geofence-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.geofence-item:hover .geofence-actions {
    opacity: 1;
}


.geofence-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}



@keyframes slideOut {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(-100%);
        opacity: 0;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

</style>

