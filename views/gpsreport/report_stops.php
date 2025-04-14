<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;
use yii\widgets\Pjax;

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
                    <?= Html::dropDownList('gps', Yii::$app->request->get('gps', null), GpsLocations::find()->select(['phoneNumber'])->indexBy('phoneNumber')->column(), [
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

    <div class="custom-card-container">
        <div class="custom-card">
            <div class="custom-card-header">
                <h4 class="custom-card-title">Reporte Paradas Dispositivo</h4>
            </div>
            <div class="custom-card-body">
                <div class="table-responsive active-projects">
                    <table id="projects-tbl" class="table table-striped table-bordered compact-table">
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
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No hay datos disponibles.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if (!empty($stops) && $totalCount > $pageSize): ?>
                    <div class="pagination-container mt-3">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $currentPage - 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                        &laquo;
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $i, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= ($currentPage >= $pageCount) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= Url::to(['gpsreport/report-stops', 'page' => $currentPage + 1, 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate')]) ?>">
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
    
    
    
   <!-- Switch para mostrar/ocultar tarjetas --> 
   <div id="toggle-container">
    <input type="checkbox" id="label-check" class="label-check" />
    <label class="hamburger-label" for="label-check">
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="line3"></div>
        <label></label
    ></label>
    </div>
    <br>
 <!-- Render the cards partial view -->
<?= $this->render('_stops_cards', [
    'stops' => $stops,
    'stopsPerDay' => $stopsPerDay
]) ?>

<br> <br>
<script>
document.getElementById('label-check').addEventListener('change', function() {
    let cardsContainer = document.getElementById("cards-container");

    if (this.checked) {
        cardsContainer.style.display = "block"; 
        requestAnimationFrame(() => {
            cardsContainer.classList.add("show");
        });
    } else {
        cardsContainer.classList.remove("show");
        setTimeout(() => {
            if (!cardsContainer.classList.contains("show")) {
                cardsContainer.style.display = "none";  
            }
        }, 500);
    }
});
</script>
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
    let toggleContainer = $("#toggle-container");

    // Ocultar la card y el toggle al inicio
    cardContainer.hide();
    toggleContainer.hide();

    // Manejar el envío del formulario sin recargar la página
    $('#gps-report-form').on('submit', function (event) {
        event.preventDefault(); // Evita la recarga
        $.pjax.submit(event, '#gps-report-pjax', {timeout: 5000});
    });

    // Detectar la actualización completa de PJAX
    $(document).off('pjax:complete').on('pjax:complete', function () {
        // Esperar un momento para asegurarse de que el DOM se haya actualizado
        setTimeout(function () {
            let newTableBody = $("#projects-tbl tbody");
            let numRows = newTableBody.find("tr").length;

            if (numRows > 0 && !newTableBody.find("tr td[colspan]").length) {
                cardContainer.fadeIn(500);
                toggleContainer.fadeIn(500);
            } else {
                cardContainer.hide();
                toggleContainer.hide();
            }
        }, 100); // Ajusta el tiempo de espera según sea necesario
    });
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
    .custom-card {
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        background-color: #fff;
        position: relative;
    }

    .custom-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
    }

    .custom-card-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .custom-card-body {
        padding: 20px;
        position: relative;
    }

    .compact-table {
        font-size: 14px;
    }

    .compact-table th, .compact-table td {
        padding: 10px 15px;
    }
    
    /* Map container styles */
    #stops-map {
        border-radius: 4px;
        overflow: hidden;
    }
    
    /* Legend styles */
    .info.legend {
        background: white;
        padding: 6px 8px;
        border-radius: 4px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
</style>

