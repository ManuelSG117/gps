<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<link href="/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="/vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
<script src="/js/reportes_ruta.js"></script>

<?php Pjax::begin(['id' => 'gps-report-pjax', 'timeout' => 10000]); ?>
<div class="gps-report-form">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['gpsreport/index'],
        'options' => ['data-pjax' => true]
    ]); ?>

    <div class="container-fluid">
        <div class="row align-items-end">
            <!-- Filtro Periodo -->
            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="filter">Periodo:</label>
                    <?= Html::dropDownList('filter', Yii::$app->request->get('filter', 'today'), [
                        'today' => 'Hoy',
                        'yesterday' => 'Ayer',
                        'current_week' => 'Semana Actual',
                        'last_week' => 'Semana Anterior',
                        'current_month' => 'Mes Actual',
                        'last_month' => 'Mes Anterior',
                        'custom' => 'Personalizado',
                    ], [
                        'class' => 'form-control',
                        'id' => 'filter',
                    ]) ?>
                </div>
            </div>

            <!-- Filtro Dispositivo -->
            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="gps">Dispositivo:</label>
                    <?= Html::dropDownList('gps', Yii::$app->request->get('gps', null), GpsLocations::find()->select(['phoneNumber'])->indexBy('phoneNumber')->column(), [
                        'class' => 'form-control',
                        'id' => 'gps',
                    ]) ?>
                </div>
            </div>

            <!-- Botón Mostrar -->
            <div class="col-lg-2 col-md-4 col-12">
                <?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary w-100']) ?>
            </div>

            <!-- Exportar a Excel -->
            <div class="col-lg-2 col-md-4 col-12">
                <?= Html::a('Export to Excel', '#', [
                    'class' => 'btn btn-success w-100',
                    'onclick' => 'return confirmExport();'
                ]) ?>
            </div>

            <!-- Rango de Fechas (se muestra solo cuando se selecciona 'Personalizado') -->
            <div class="col-lg-4 col-md-12 col-12 custom-dates" style="display: none;">
                <div class="row">
                    <div class="col-6">
                        <?= Html::input('text', 'startDate', Yii::$app->request->get('startDate', null), [
                            'class' => 'form-control',
                            'id' => 'startDate',
                            'placeholder' => 'Desde:',
                        ]) ?>
                    </div>
                    <div class="col-6">
                        <?= Html::input('text', 'endDate', Yii::$app->request->get('endDate', null), [
                            'class' => 'form-control',
                            'id' => 'endDate',
                            'placeholder' => 'Hasta:',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>

    <?php ActiveForm::end(); ?>
</div>

<div class="custom-card-container" <?= empty($locations) ? 'style="display: none;"' : '' ?>>
    <div class="custom-card">
        <div class="custom-card-header">
            <h4 class="custom-card-title">Reporte Ruta</h4>
        </div>
        <div class="custom-card-body">
            <div class="table-responsive route-table">
                <table id="projects-tbl" class="table table-striped table-bordered compact-table">
                    <thead>
                        <tr class="table-primary">
                            <th>Fecha</th>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th>Velocidad</th>
                            <th>Dirección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Pagination setup
                        $pageSize = 10;
                        $totalCount = count($locations);
                        $pageCount = ceil($totalCount / $pageSize);
                        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $currentPage = max(1, min($currentPage, $pageCount));
                        $offset = ($currentPage - 1) * $pageSize;
                        
                        // Get current page items
                        $currentItems = array_slice($locations, $offset, $pageSize);
                        
                        foreach ($currentItems as $location): 
                        ?>
                            <tr>
                                <td><?= $location->lastUpdate ?></td>
                                <td><?= $location->latitude ?></td>
                                <td><?= $location->longitude ?></td>
                                <td><span class="badge badge-sm  badge-primary"><?= $location->speed ?> km/h</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                        onclick="showAddress(<?= $location->latitude ?>, <?= $location->longitude ?>, this)">
                                        <i class="fa fa-map-marker"></i> Mostrar calle
                                    </button>
                                    <div class="mt-2 address-result"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalCount > 0): ?>
                <div class="pagination-container mt-3">
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $currentPage - 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &laquo;
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $i, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $currentPage + 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center text-muted">
                        Mostrando <?= min($offset + 1, $totalCount) ?>-<?= min($offset + $pageSize, $totalCount) ?> de <?= $totalCount ?> registros
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (empty($locations)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay información de ubicación disponible para el período y dispositivo seleccionados.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        });
    </script>
<?php endif; ?>

<!-- After the table card div, add the map container -->
<div class=" mt-4" <?= empty($locations) ? 'style="display: none;"' : '' ?>>
    <div class="card-header">
        <h4 class="card-title">Mapa de Ruta</h4>
    </div>
    <div class="card-body">
        <div id="map" style="height: 500px; width: 100%; position: relative;"></div>
    </div>
</div>
<?php Pjax::end(); ?>

<!-- Configurar las URLs para la exportación -->
<script>
    // Configurar las URLs para la exportación
    exportUrlWithChart = '<?= Url::to(['gpsreport/download-report', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => true]) ?>';
    exportUrlWithoutChart = '<?= Url::to(['gpsreport/download-report', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => false]) ?>';
</script>

<!-- Add Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<style>
    #map {
        height: 500px !important;
        /* Altura fija */
        width: 100% !important;
        /* Ancho completo */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1;
        position: relative;
        /* Asegurar posicionamiento correcto */
    }

    .legend {
        line-height: 18px;
        color: #555;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    .legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.7;
    }


  

</style>