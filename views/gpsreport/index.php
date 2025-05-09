<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;
use yii\widgets\Pjax;

// Obtener solo los IMEIs registrados
$registeredImeis = (new \yii\db\Query())
    ->select(['imei'])
    ->from('dispositivos')
    ->column();

// Obtener solo los phoneNumber de ubicaciones que estén registrados
$registeredPhones = GpsLocations::find()
    ->select(['phoneNumber'])
    ->where(['phoneNumber' => $registeredImeis])
    ->groupBy('phoneNumber')
    ->indexBy('phoneNumber')
    ->column();

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
                    <?= Html::dropDownList('gps', Yii::$app->request->get('gps', 'all'), 
                        array_merge(
                            ['all' => 'Todos los dispositivos'], 
                            $registeredPhones
                        ), [
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
                <?= Html::a('Exportar', '#', [
                    'class' => 'btn btn-success w-100',
                    'onclick' => 'return confirmExport();'
                ]) ?>
            </div>

            <!-- Rango de Fechas (se muestra solo cuando se selecciona 'Personalizado') -->
            <div class="col-lg-4 col-md-12 col-12 custom-dates" style="display: none;">
                <div class="row">
                    <div class="col-6 date-field-container">
                        <?= Html::input('text', 'startDate', Yii::$app->request->get('startDate', null), [
                            'class' => 'form-control',
                            'id' => 'startDate',
                            'placeholder' => 'Desde:',
                        ]) ?>
                    </div>
                    <div class="col-6 date-field-container">
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
                            <!-- Primera página -->
                            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &laquo;&laquo;
                                </a>
                            </li>
                            
                            <!-- Página anterior -->
                            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $currentPage - 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &laquo;
                                </a>
                            </li>
                            
                            <?php
                            // Mostrar solo 5 páginas a la vez
                            $maxPagesToShow = 5;
                            $startPage = max(1, min($currentPage - floor($maxPagesToShow / 2), $pageCount - $maxPagesToShow + 1));
                            $startPage = max(1, $startPage); // Asegurar que no sea menor que 1
                            $endPage = min($startPage + $maxPagesToShow - 1, $pageCount);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $i, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Página siguiente -->
                            <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $currentPage + 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &raquo;
                                </a>
                            </li>
                            
                            <!-- Última página -->
                            <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= Url::to(['gpsreport/index', 'page' => $pageCount, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                    &raquo;&raquo;
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

<!-- After the table card div, add the map container -->
<div class="custom card-container" <?= empty($locations) ? 'style="display: none;"' : '' ?>>
    <div class="custom-card-header">
        <h4 class="custom-card-title">Mapa de Ruta</h4>
    </div>
    <div class="custom-card-body">
        <div id="map" style="height: 500px; width: 100%; position: relative;"></div>
    </div>
</div>
<br>
<!-- Panel de estadísticas de la ruta -->
<div id="route-stats-cards" class="mb-3" style="display:none;">
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/2e2e2e2e-2e2e-2e2e-2e2e-2e2e2e2e2e2e/route1.lottie" background="transparent" speed="1" style="width: 120px; height: 120px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Distancia Total Recorrida</p>
                    <p class="card__description stat-value" id="stat-distance">-</p>
                    <p class="card__description">Suma de todos los tramos recorridos en la ruta seleccionada.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/3b3b3b3b-3b3b-3b3b-3b3b-3b3b3b3b3b3b/route2.lottie" background="transparent" speed="1" style="width: 120px; height: 120px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Velocidad Promedio</p>
                    <p class="card__description stat-value" id="stat-avg-speed">-</p>
                    <p class="card__description">Promedio de velocidad durante toda la ruta.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-3">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/4c4c4c4c-4c4c-4c4c-4c4c-4c4c4c4c4c4c/route3.lottie" background="transparent" speed="1" style="width: 120px; height: 120px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Duración Total</p>
                    <p class="card__description stat-value" id="stat-duration">-</p>
                    <p class="card__description">Tiempo transcurrido desde el primer hasta el último punto de la ruta.</p>
                </div>
            </div>
        </div>
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

    /* Estilos para el panel de estadísticas */
    #route-stats-cards {
        box-shadow: 0 0 10px rgba(0,0,0,0.08);
        border-radius: 8px;
        background: #fff;
        margin-bottom: 20px;
    }
    #route-stats-cards .cards {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px 10px;
        margin-bottom: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    #route-stats-cards .card__title {
        font-size: 0.95rem;
        color: #888;
        display: block;
        margin-bottom: 4px;
    }
    #route-stats-cards .card__description {
        font-size: 1.4rem;
        font-weight: bold;
        color: #007bff;
    }
    /* Leyenda de velocidades */
    .legend-speed {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 0.95rem;
    }
    .legend-speed .legend-color {
        display: inline-block;
        width: 18px;
        height: 8px;
        margin-right: 6px;
        border-radius: 2px;
    }
    /* Panel de controles de animación */
    #route-anim-controls {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.10);
        padding: 10px 18px 10px 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #e3e6f0;
    }
    #route-anim-controls button {
        min-width: 32px;
        min-height: 32px;
        border-radius: 6px;
        border: none;
        background: #f4f6fb;
        color: #007bff;
        transition: background 0.2s;
    }
    #route-anim-controls button:hover {
        background: #e3e6f0;
    }
    #route-anim-controls label {
        font-weight: 500;
        color: #555;
        margin-bottom: 0;
    }
    #route-anim-controls input[type=range] {
        accent-color: #007bff;
        height: 4px;
        margin: 0 6px;
        width: 90px;
    }
    #route-anim-controls .progress {
        background: #e9ecef;
        border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        margin-left: 10px;
        margin-right: 10px;
    }
    #route-anim-controls .progress-bar {
        background: linear-gradient(90deg, #36b3ff 0%, #007bff 100%);
        border-radius: 4px;
        transition: width 0.2s;
    }
</style>