<?php

use app\models\Geocerca;
use app\models\Vehiculos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Vehiculos[] $vehiculos */
/** @var app\models\Geocerca[] $geocercas */
/** @var app\models\VehiculoGeocerca[] $asignaciones */

$this->title = 'Asignación de Geocercas a Vehículos';
$this->params['breadcrumbs'][] = $this->title;

// Preparar datos de geocercas para JavaScript
$geofencesJson = \yii\helpers\Json::encode(array_map(function($geofence) {
    return [
        'id' => $geofence->id,
        'name' => $geofence->name,
        'description' => $geofence->description,
        'coordinates' => $geofence->coordinates
    ];
}, $geocercas));

// Preparar datos de vehículos para JavaScript
$vehiculosJson = \yii\helpers\Json::encode(array_map(function($vehiculo) {
    return [
        'id' => $vehiculo->id,
        'modelo' => $vehiculo->modelo_auto,
        'marca' => $vehiculo->marca_auto,
        'placa' => $vehiculo->placa
    ];
}, $vehiculos));

// Registrar archivos JS necesarios
$this->registerJsFile('@web/js/geocerca.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/vehiculo-geocerca.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<script>
var geofencesData = <?= $geofencesJson ?>;
var vehiculosData = <?= $vehiculosJson ?>;
</script>

<style>
/* Estilos para elementos resaltados */
.search-highlight {
    background-color: rgba(255, 255, 0, 0.2) !important;
}

.active-highlight {
    background-color: rgba(0, 123, 255, 0.2) !important;
    border-left: 4px solid #007bff !important;
}

/* Estilos para elementos seleccionados */
.selected-item {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selected-item .remove-item {
    cursor: pointer;
    color: #dc3545;
}

.selected-item .remove-item:hover {
    color: #bd2130;
}

/* Estilos para la vista previa de asignación */
.preview-container {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
}

.preview-section {
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.preview-section:last-child {
    border-bottom: none;
}

/* Estilos para asignación rápida */
.asignar-rapido {
    margin-right: 5px;
    transition: all 0.3s ease;
}

.asignar-rapido:hover {
    transform: scale(1.1);
}

.quick-assign-container {
    border: 1px solid #eee;
    border-radius: 5px;
    padding: 10px;
    margin-top: 10px;
}

.quick-assign-checkbox {
    cursor: pointer;
}

/* Indicador de asignación */
.has-assignments {
    position: relative;
}

.has-assignments:after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 8px;
    background-color: #28a745;
    border-radius: 50%;
}

/* Mejoras para la visualización en dispositivos móviles */
@media (max-width: 768px) {
    .section {
        margin-bottom: 15px;
    }
    
    #map {
        height: 300px !important;
    }
    
    .btn-group {
        display: flex;
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        flex: 1 0 auto;
        margin-bottom: 5px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE&libraries=drawing"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="vehiculo-geocerca-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Utilice el botón <i class="fas fa-link"></i> para asignaciones rápidas o haga doble clic en un elemento para ver todas sus asignaciones.
    </div>
    
    <div class="row">
        <!-- Controles para expandir/minimizar secciones -->
        <div class="col-12 mb-3">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary toggle-section" data-section="map-section">
                    <i class="fas fa-map"></i> Mapa
                </button>
                <button type="button" class="btn btn-outline-primary toggle-section" data-section="vehiculos-section">
                    <i class="fas fa-car"></i> Vehículos
                </button>
                <button type="button" class="btn btn-outline-primary toggle-section" data-section="geocercas-section">
                    <i class="fas fa-draw-polygon"></i> Geocercas
                </button>
            </div>
            <button type="button" class="btn btn-outline-success expand-all">
                <i class="fas fa-expand"></i> Expandir Todo
            </button>
            <button type="button" class="btn btn-outline-warning collapse-all">
                <i class="fas fa-compress"></i> Minimizar Todo
            </button>
        </div>
    </div>
    
    <div class="row">
        <!-- Sección del Mapa -->
        <div class="col-md-12 section" id="map-section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-map"></i> Mapa de Geocercas y Vehículos</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary minimize-section" data-section="map-section">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary maximize-section" data-section="map-section">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body section-content" id="map-section-content">
                    <div id="map" style="height: 500px;"></div>
                    <div id="info" style="display: none;"></div>
                </div>
            </div>
        </div>
        
        <!-- Sección de Vehículos -->
        <div class="col-md-6 section mt-3" id="vehiculos-section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-car"></i> Vehículos</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary minimize-section" data-section="vehiculos-section">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary maximize-section" data-section="vehiculos-section">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body section-content" id="vehiculos-section-content">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchVehiculos" placeholder="Buscar vehículos...">
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="asignarGeocercasBtn">
                            <i class="fas fa-link"></i> Asignar Geocercas
                        </button>
                        <small class="text-muted ml-2">Seleccione vehículos y haga clic para asignar geocercas</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllVehiculos">
                                    </th>
                                    <th>Modelo</th>
                                    <th>Marca</th>
                                    <th>Placa</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="vehiculosList">
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr class="vehiculo-item" data-id="<?= $vehiculo->id ?>">
                                    <td>
                                        <input type="checkbox" class="vehiculo-checkbox" data-id="<?= $vehiculo->id ?>">
                                    </td>
                                    <td><?= Html::encode($vehiculo->modelo_auto) ?></td>
                                    <td><?= Html::encode($vehiculo->marca_auto) ?></td>
                                    <td><?= Html::encode($vehiculo->placa) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info ver-geocercas" data-id="<?= $vehiculo->id ?>">
                                            <i class="fas fa-eye"></i> Ver Geocercas
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección de Geocercas -->
        <div class="col-md-6 section mt-3" id="geocercas-section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-draw-polygon"></i> Geocercas</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary minimize-section" data-section="geocercas-section">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary maximize-section" data-section="geocercas-section">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body section-content" id="geocercas-section-content">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchGeocercas" placeholder="Buscar geocercas...">
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="asignarVehiculosBtn">
                            <i class="fas fa-link"></i> Asignar Vehículos
                        </button>
                        <small class="text-muted ml-2">Seleccione geocercas y haga clic para asignar vehículos</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllGeocercas">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="geocercasList">
                                <?php foreach ($geocercas as $geocerca): ?>
                                <tr class="geocerca-item" data-id="<?= $geocerca->id ?>">
                                    <td>
                                        <input type="checkbox" class="geocerca-checkbox" data-id="<?= $geocerca->id ?>">
                                    </td>
                                    <td><?= Html::encode($geocerca->name) ?></td>
                                    <td><?= Html::encode($geocerca->description) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info ver-vehiculos" data-id="<?= $geocerca->id ?>">
                                            <i class="fas fa-eye"></i> Ver Vehículos
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar geocercas a vehículos -->
<div class="modal fade" id="asignarGeocercasModal" tabindex="-1" aria-labelledby="asignarGeocercasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarGeocercasModalLabel">Asignar Geocercas a Vehículos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Vehículos Seleccionados:</label>
                    <div id="vehiculosSeleccionados" class="border p-2 rounded mb-3" style="min-height: 50px;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Seleccionar Geocercas:</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchGeocercasModal" placeholder="Buscar geocercas...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllGeocercasModal">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody id="geocercasListModal">
                                <?php foreach ($geocercas as $geocerca): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="geocerca-modal-checkbox" data-id="<?= $geocerca->id ?>">
                                    </td>
                                    <td><?= Html::encode($geocerca->name) ?></td>
                                    <td><?= Html::encode($geocerca->description) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="eliminarAsignacionesExistentesGeocercas">
                    <label class="form-check-label" for="eliminarAsignacionesExistentesGeocercas">
                        Eliminar asignaciones existentes
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="previewAsignacionGeocercas">Vista Previa</button>
                <button type="button" class="btn btn-primary" id="guardarAsignacionGeocercas">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar vehículos a geocercas -->
<div class="modal fade" id="asignarVehiculosModal" tabindex="-1" aria-labelledby="asignarVehiculosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarVehiculosModalLabel">Asignar Vehículos a Geocercas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Geocercas Seleccionadas:</label>
                    <div id="geocercasSeleccionadas" class="border p-2 rounded mb-3" style="min-height: 50px;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Seleccionar Vehículos:</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchVehiculosModal" placeholder="Buscar vehículos...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllVehiculosModal">
                                    </th>
                                    <th>Modelo</th>
                                    <th>Marca</th>
                                    <th>Placa</th>
                                </tr>
                            </thead>
                            <tbody id="vehiculosListModal">
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="vehiculo-modal-checkbox" data-id="<?= $vehiculo->id ?>">
                                    </td>
                                    <td><?= Html::encode($vehiculo->modelo_auto) ?></td>
                                    <td><?= Html::encode($vehiculo->marca_auto) ?></td>
                                    <td><?= Html::encode($vehiculo->placa) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="eliminarAsignacionesExistentesVehiculos">
                    <label class="form-check-label" for="eliminarAsignacionesExistentesVehiculos">
                        Eliminar asignaciones existentes
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="previewAsignacionVehiculos">Vista Previa</button>
                <button type="button" class="btn btn-primary" id="guardarAsignacionVehiculos">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver geocercas de un vehículo -->
<div class="modal fade" id="verGeocercasModal" tabindex="-1" aria-labelledby="verGeocercasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verGeocercasModalLabel">Geocercas Asignadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="geocercasAsignadas" class="list-group">
                    <!-- Aquí se cargarán dinámicamente las geocercas asignadas -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver vehículos de una geocerca -->
<div class="modal fade" id="verVehiculosModal" tabindex="-1" aria-labelledby="verVehiculosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verVehiculosModalLabel">Vehículos Asignados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="vehiculosAsignados" class="list-group">
                    <!-- Aquí se cargarán dinámicamente los vehículos asignados -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para las secciones expandibles */
.section {
    transition: all 0.3s ease;
}

.section-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.section.minimized .section-content {
    height: 0;
    padding: 0;
    overflow: hidden;
}

.section.maximized {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}

/* Estilos para el mapa */
#map {
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Estilos para las tablas */
.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

/* Estilos para los elementos seleccionados */
.selected-item {
    display: inline-block;
    background-color: #e9ecef;
    padding: 5px 10px;
    margin: 3px;
    border-radius: 4px;
}

.selected-item .remove-item {
    margin-left: 5px;
    cursor: pointer;
    color: #dc3545;
}

/* Estilos para los botones de acción */
.btn-group .btn {
    margin-right: 5px;
}

/* Estilos para los checkboxes */
input[type="checkbox"] {
    cursor: pointer;
}
</style>