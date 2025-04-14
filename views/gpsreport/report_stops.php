<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->registerJsFile('@web/js/stops.js', ['depends' => [\yii\web\JqueryAsset::class]]);
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
 <!-- Contenedor de tarjetas con ID -->
<div id="cards-container">
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/ed84083f-f28a-4a85-829b-b9c3e6d57d3d/9jQnEiULKi.lottie" background="transparent" speed="1" style="width: 350px; height: 250px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Duración Total de Paradas</p>
                    <p class="card__description">
                        <?php
                        $totalDuration = array_sum(array_column($stops, 'duration'));
                        if ($totalDuration >= 3600) {
                            $hours = floor($totalDuration / 3600);
                            $minutes = floor(($totalDuration % 3600) / 60);
                            $seconds = $totalDuration % 60;
                            echo sprintf('%d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                        } else {
                            $minutes = floor($totalDuration / 60);
                            $seconds = $totalDuration % 60;
                            echo sprintf('%d minutos, %d segundos', $minutes, $seconds);
                        }
                        ?>
                    </p>
                    <p class="card__description">
                    <?php
                    $averageDuration = count($stops) > 0 ? ($totalDuration / count($stops)) : 0;
                    if ($averageDuration >= 3600) {
                        $hours = floor($averageDuration / 3600);
                        $minutes = floor(($averageDuration % 3600) / 60);
                        $seconds = $averageDuration % 60;
                        echo sprintf('Promedio de tiempo detenido: %d horas, %d minutos, %d segundos', $hours, $minutes, $seconds);
                    } else {
                        $minutes = floor($averageDuration / 60);
                        $seconds = $averageDuration % 60;
                        echo sprintf('Promedio de tiempo detenido: %d minutos, %d segundos', $minutes, $seconds);
                    }
                    ?>
                </p>
                    <div id="icon-container">
                    <dotlottie-player src="https://lottie.host/a9b6f0ca-e88b-4420-8575-7202b711f122/ohDvpoaorb.lottie" background="transparent" speed="1" style="width: 80px; height: 80px" loop autoplay></dotlottie-player>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <div class="cards">
                <dotlottie-player src="https://lottie.host/06c57bb2-0963-4084-bcc9-05e6e5156d90/4U8QhsebpY.lottie" background="transparent" speed="1" style="width: 170px; height: 300px" loop autoplay></dotlottie-player>
                <div class="card__content">
                    <p class="card__title">Total de Paradas Registradas</p>
                    <p class="card__description">Total de paradas: <?= count($stops) ?></p>
                    <p class="card__description">
                        <?php
                        $totalDays = count($stopsPerDay);
                        $averageStops = $totalDays > 0 ? count($stops) / $totalDays : 0;
                        echo sprintf('Promedio de paradas registradas por día: %.2f', $averageStops);
                        ?>
                    </p>
                    <div id="icon-container">
                    <dotlottie-player src="https://lottie.host/ed2373fa-ca39-42e0-8da8-dbdabc4769b4/AuM0umjYJQ.lottie" background="transparent" speed="2" style="width: 100px; height: 76px" loop autoplay></dotlottie-player>
                     </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
    <!-- Contenedor del gráfico con animación de carga superpuesta -->
    <div id="chart-wrapper">
    <!-- Div donde se renderiza la gráfica -->
    <div id="stops-chart" style="height: 200px;"></div>
    
    <!-- Animación de carga (overlay) -->
    <div id="loading-animation" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
         display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.8);">
        <dotlottie-player src="https://lottie.host/e0f85e03-ec5a-4435-a7ee-30dc93809080/92ftoDoc5w.lottie" background=" linear-gradient(to bottom, #1e3c72, #2a5298);" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
    </div>
</div>
        </div>
    </div>
</div>
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
// Inicialización del gráfico y ocultación de la animación al cargar
$('#showinfo').on('click', function () {
    setTimeout(function () {
      Highcharts.chart('stops-chart', {
        chart: {
          type: 'line',
          events: {
            load: function () {
              // Oculta la animación de carga cuando el gráfico se ha renderizado
              document.getElementById('loading-animation').style.display = 'none';
            }
          }
        },
        title: {
          text: 'Número de Paradas por Día'
        },
        xAxis: {
          categories: <?= json_encode(array_keys($stopsPerDay)) ?>
        },
        yAxis: {
          title: {
            text: '# Paradas'
          }
        },
        series: [{
          name: 'Paradas',
          data: <?= json_encode(array_values($stopsPerDay)) ?>,
          dataLabels: {
            enabled: true,
          },
          enableMouseTracking: true
        }]
      });
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

