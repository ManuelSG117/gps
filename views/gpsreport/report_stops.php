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

$this->registerJsFile('@web/js/stops.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/js/stops-chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
<script src="/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="/js/plugins-init/datatables.init.js"></script>

<div class="gps-report-form" style="margin-top: -60px;">
    <?php Pjax::begin(['id' => 'gps-report-pjax', 'timeout' => 5000]); ?>
    <?php $form = ActiveForm::begin([
        'id' => 'gps-report-form',
        'options' => ['data-pjax' => true],
        'method' => 'get',
        'action' => ['gpsreport/report-stops'],
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
                    <?= Html::dropDownList('gps', Yii::$app->request->get('gps', null), $registeredPhones, [
                        'class' => 'form-control',
                        'id' => 'gps',
                    ]) ?>
                </div>
            </div>

            <!-- Botón Mostrar -->
            <div class="col-lg-2 col-md-4 col-12">
            <?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary w-100', 'id' =>'showinfo']) ?>
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
    <?php ActiveForm::end(); ?>

    <br>

    <div class="custom-card-container" <?= empty($stops) ? 'style="display: none;"' : '' ?>>
        <div class="custom-card">
            <div class="custom-card-header">
                <h4 class="custom-card-title">Reporte Paradas Dispositivo</h4>
            </div>
            <div class="custom-card-body">
                <div class="table-responsive active-projects">
                    <table id="projects-tbls" class="table table-striped table-bordered compact-table">
                        <thead>
                            <tr class="table-primary">
                                <th>Inicio de parada</th>
                                <th>Fin de parada</th>
                                <th>Duración </th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($stops)): ?>
                                <?php 
                                // Pagination setup
                                $pageSize = 10;
                                $totalCount = count($stops);
                                $pageCount = ceil($totalCount / $pageSize);
                                $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $currentPage = max(1, min($currentPage, $pageCount));
                                $offset = ($currentPage - 1) * $pageSize;
                                
                                // Get current page items
                                $currentItems = array_slice($stops, $offset, $pageSize);
                                
                                foreach ($currentItems as $stop): 
                                ?>
                                    <tr>
                                        <td><?= $stop['start_time'] ?></td>
                                        <td><?= isset($stop['end_time']) ? $stop['end_time'] : 'En curso' ?></td>
                                        <td><span class="badge badge-sm  badge-primary">
                                            <?php
                                            if (isset($stop['duration'])) {
                                                $durationInSeconds = $stop['duration'];
                                                if ($durationInSeconds >= 3600) {
                                                    $hours = floor($durationInSeconds / 3600);
                                                    $minutes = floor(($durationInSeconds % 3600) / 60);
                                                    $seconds = $durationInSeconds % 60;
                                                    echo sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                                                } else {
                                                    $minutes = floor($durationInSeconds / 60);
                                                    $seconds = $durationInSeconds % 60;
                                                    echo sprintf('%d minutos, %d segundos', $minutes, $seconds);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                        </td>
                                        <td>
                                            
                                            <a href="https://www.google.com/maps?q=<?= $stop['latitude'] ?>,<?= $stop['longitude'] ?>" target="_blank">
                                                Ver en mapa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if (!empty($stops) && $totalCount > $pageSize): ?>
                    <div class="pagination-container mt-3">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <!-- Primera página -->
                                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                        &laquo;&laquo;
                                    </a>
                                </li>
                                
                                <!-- Página anterior -->
                                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $currentPage - 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
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
                                        <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $i, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Página siguiente -->
                                <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $currentPage + 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                        &raquo;
                                    </a>
                                </li>
                                
                                <!-- Última página -->
                                <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $pageCount, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
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
    
    <!-- Map container for stops and route -->
    <div class="custom-card mt-4" <?= empty($stops) ? 'style="display: none;"' : '' ?>>
        <div class="custom-card-header">
            <h4 class="custom-card-title">Mapa de Paradas y Ruta</h4>
        </div>
        <div class="custom-card-body">
            <div id="stops-map" style="height: 500px; width: 100%; position: relative;"></div>
        </div>
    </div>
    
    <!-- Add Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
    <script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
    
    <script>
    // Initialize map when data is available
    $(document).ready(function() {
        if (<?= !empty($stops) ? 'true' : 'false' ?>) {
            initStopsMap();
        }
    });
    
    // Function to initialize the map with stops and route
    function initStopsMap() {
        // Create map
        var map = L.map('stops-map').setView([0, 0], 13);
        
        // Add Google Maps layer
        var googleStreets = L.gridLayer.googleMutant({
            type: 'roadmap'
        }).addTo(map);
        
        // Create markers for stops
        var stopMarkers = [];
        var stopCoordinates = [];
        var bounds = L.latLngBounds();
        
        <?php if (!empty($stops)): ?>
            <?php foreach ($stops as $index => $stop): ?>
                var lat = <?= $stop['latitude'] ?>;
                var lng = <?= $stop['longitude'] ?>;
                var stopTime = "<?= $stop['start_time'] ?>";
                var endTime = "<?= isset($stop['end_time']) ? $stop['end_time'] : 'En curso' ?>";
                var duration = "<?php
                    if (isset($stop['duration'])) {
                        $durationInSeconds = $stop['duration'];
                        if ($durationInSeconds >= 3600) {
                            $hours = floor($durationInSeconds / 3600);
                            $minutes = floor(($durationInSeconds % 3600) / 60);
                            $seconds = $durationInSeconds % 60;
                            echo sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                        } else {
                            $minutes = floor($durationInSeconds / 60);
                            $seconds = $durationInSeconds % 60;
                            echo sprintf('%d minutos, %d segundos', $minutes, $seconds);
                        }
                    } else {
                        echo 'N/A';
                    }
                ?>";
                
                // Use standard marker with red color instead of custom icon
                var marker = L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map);
                
                marker.bindPopup(
                    "<strong>Parada #" + (<?= $index ?> + 1) + "</strong><br>" +
                    "Inicio: " + stopTime + "<br>" +
                    "Fin: " + endTime + "<br>" +
                    "Duración: " + duration + "<br>" +
                    "<a href='https://www.google.com/maps?q=" + lat + "," + lng + "' target='_blank'>Ver en Google Maps</a>"
                );
                
                stopMarkers.push(marker);
                stopCoordinates.push([lat, lng]);
                bounds.extend([lat, lng]);
            <?php endforeach; ?>
            
            // Create a normal polyline (not dashed) to connect the stops
            var routeLine = L.polyline(stopCoordinates, {
                color: 'blue',
                weight: 3,
                opacity: 0.7,
                lineJoin: 'round'
            }).addTo(map);
            
            // Fit map to bounds
            if (stopCoordinates.length > 0) {
                map.fitBounds(bounds, {padding: [50, 50]});
            }
            
            // Add legend
            var legend = L.control({position: 'bottomright'});
            legend.onAdd = function(map) {
                var div = L.DomUtil.create('div', 'info legend');
                div.innerHTML = 
                    '<img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png" height="20"> Paradas<br>' +
                    '<i style="background: blue; height: 2px; width: 30px; display: inline-block;"></i> Ruta';
                return div;
            };
            legend.addTo(map);
        <?php endif; ?>
    }
    </script>
    <br>
    <br>
 <!-- Render the cards partial view -->
<?= $this->render('_stops_cards', [
    'stops' => $stops,
    'stopsPerDay' => $stopsPerDay
]) ?>

<br> <br>
<!-- El script para controlar la visibilidad de las tarjetas ha sido eliminado ya que ahora siempre se muestran -->
<script>
// Initialize chart when show button is clicked
$('#showinfo').on('click', function () {
    setTimeout(function () {
        // Pass the PHP data to the JavaScript function
        initStopsChart(
            <?= json_encode(array_keys($stopsPerDay)) ?>,
            <?= json_encode(array_values($stopsPerDay)) ?>
        );
    }, 500);
});
</script>

<?php Pjax::end(); ?>

</div>
    
<script>
$(document).ready(function() {
   // console.log("Documento listo");

    $(document).on('pjax:complete', function() {
    //    console.log("Evento pjax:complete detectado, recargando stops.js...");
    $.getScript('http://localhost:8080/js/stops.js?' + new Date().getTime(), function() {
   //     console.log("stops.js recargado y ejecutado correctamente.");
        if (typeof initStops === 'function') {
            initStops();
          // console.log("initStops() ejecutado tras Pjax.");
        } else {
   //         console.log("La función initStops() no está definida.");
        }
    }).fail(function() {
   //     console.log("Error al recargar stops.js");
    });
    
 
});
});
</script>

<script>
$(document).ready(function () {
    let cardContainer = $(".custom-card-container");
    cardContainer.hide();

    $('#gps-report-form').on('submit', function (event) {
        event.preventDefault();
        $.pjax.submit(event, '#gps-report-pjax', {timeout: 5000});
    });

    function updateCardsVisibility() {
        if (typeof stops !== 'undefined' && stops.length > 0) {
            cardContainer.fadeIn(500);
            $("#cards-container").fadeIn(500);
            $("#loading-animation").hide();
        } else {
            cardContainer.hide();
            $("#cards-container").hide();
            $("#loading-animation").show();
        }
    }

    $(document).off('pjax:complete').on('pjax:complete', function () {
        setTimeout(updateCardsVisibility, 100);
    });

    // Ejecutar la verificación inicial
    updateCardsVisibility();
});
</script>

</script>
<script>$(document).on('pjax:end', function() {
 //   console.log('Consulta PJAX completada');

    var filaCount = $('#tabla-reportes tbody tr').length;
    //console.log('Número de filas en la tabla: ' + filaCount);

    if (filaCount > 1) {
 //     console.log('Más de una fila, mostrando el toggle');
        $('#toggle-button').show();  // Asegúrate de que el toggle esté visible
        
    } else {
 //       console.log('Una o menos filas, ocultando el toggle');
        $('#toggle-button').hide();  // Ocultar el toggle si no hay más de una fila
    }

    
});

</script>

<style>
    /* Map container styles */
    #stops-map {
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1;
        position: relative;
    }
    
    /* Legend styles */
    .info.legend {
        background: white;
        padding: 6px 8px;
        border-radius: 4px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    
    /* Stop marker styles */
    .stop-marker-icon {
        background: none;
        border: none;
    }
</style>

