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
<link href="/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="/vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

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

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive active-projects">
                <div class="tbl-caption">
                    <h4 class="heading mb-0">Reporte Paradas Dispositivo</h4>
                </div>
                <table id="projects-tbl" class="table">
                    <thead>
                        <tr>
                            <th>Inicio de parada</th>
                            <th>Fin de parada</th>
                            <th>Duración (minutos)</th>
                            <th>Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stops)): ?>
                            <?php foreach ($stops as $stop): ?>
                                <tr>
                                    <td><?= $stop['start_time'] ?></td>
                                    <td><?= isset($stop['end_time']) ? $stop['end_time'] : 'En curso' ?></td>
                                    <td>
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
                                    </td>                                    <td>
                                        <a href="https://www.google.com/maps?q=<?= $stop['latitude'] ?>,<?= $stop['longitude'] ?>" target="_blank">
                                            Ver en mapa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <td colspan="4" class="text-center"><strong>No hay paradas registradas.</strong></td>

                            <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php Pjax::end(); ?>

    </div>

    <?php if (!empty($stops)): ?>

    
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
                    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
                    <dotlottie-player src="https://lottie.host/ed2373fa-ca39-42e0-8da8-dbdabc4769b4/AuM0umjYJQ.lottie" background="transparent" speed="2" style="width: 100px; height: 76px" loop autoplay></dotlottie-player>
                     </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div id="stops-chart" style="height: 200px;"></div>
        </div>
    </div>
</div>
<br>
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
<?php endif; ?>


<style>
#icon-container {
text-align: center; 
justify-content: center;
margin-left:45%;
}

#cards-container {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

#cards-container.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}


.label-check {
  display: none;
}

.hamburger-label {
  width: 24.5px;
  height: 20.3px;
  display: block;
  cursor: pointer;
  transition: 0.3s;
}

.hamburger-label div {
  width: 24.5px;
  height: 2.1px;
  background-color: #fff;
  position: absolute;
}

.line1 {
  transition: all 0.3s;
}

.line2 {
  margin: 6.3px 0 0 0;
  transition: 0.3s;
}

.line3 {
  margin: 12.6px 0 0 0;
  transition: 0.3s;
}

#label-check:checked + .hamburger-label .line1 {
  transform: rotate(35deg) scaleX(0.55) translate(13.65px, -1.575px);
  border-radius: 17.5px 17.5px 17.5px 0;
}

#label-check:checked + .hamburger-label .line3 {
  transform: rotate(-35deg) scaleX(0.55) translate(13.65px, 1.575px);
  border-radius: 0 17.5px 17.5px 17.5px;
}

#label-check:checked + .hamburger-label .line2 {
  border-top-right-radius: 17.5px;
  border-bottom-right-radius: 17.5px;
  width: 15.75px;
}

#label-check:checked + .hamburger-label {
  transform: rotate(90deg);
}

</style>


 <!-- Required vendors -->
 <script src="/vendor/global/global.min.js"></script>
    
    
    <!-- Dashboard 1 -->
    <script src="/js/dashboard/dashboard-2.js"></script>

    <script src="/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/datatables/js/dataTables.buttons.min.js"></script>
    <script src="/vendor/datatables/js/buttons.html5.min.js"></script>
    <script src="/js/plugins-init/datatables.init.js"></script>

    <style>

    .table-responsive {
    height: calc(100vh - 400px) !important;  /* Ajusta el 200px según el espacio superior e inferior que necesites */
    overflow-y: auto !important;
    }

    .card-body {
        padding: 0 !important;
    }

    #projects-tbl {
        width: 100% !important;
        table-layout: fixed !important;  /* Opcional, para que las columnas tengan el mismo tamaño */
        overflow-x: hidden !important;
    }
        
       .cards {
      position: relative;
      height: 200px;
      background: linear-gradient(to bottom, #1e3c72, #2a5298); /* Degradado de azul */
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      perspective: 1000px;
      box-shadow: 0 0 0 5px rgba(90, 90, 90, 0.84);
      transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      animation: breathing 3s ease-in-out infinite;
    }
    
    @keyframes breathing {
      0% {
        box-shadow: 0 0 0 5px rgba(179, 172, 172, 0.84);
      }
      50% {
        box-shadow: 0 0 0 5px rgba(172, 168, 168, 0.42);
      }
      100% {
        box-shadow: 0 0 0 5px rgba(90, 90, 90, 0.84);
      }
    }

    .cards svg {
    width: 48px;
    fill: #333;
    transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .cards:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 16px rgba(146, 144, 144, 0.77);
    }

    .card__content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 20px;
    box-sizing: border-box;
    background-color: #f2f2f2;
    transform: rotateX(-90deg);
    transform-origin: bottom;
    transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .cards:hover .card__content {
    transform: rotateX(0deg);
    }

    .card__title {
    margin: 0;
    font-size: 24px;
    text-align: center;
    color: #444;
    font-weight: 700;
    }

    .cards:hover svg {
    scale: 0;
    }

    .card__description {
    margin: 10px 0 0;
    font-size: 14px;
    text-align: center;
    color: #777;
    line-height: 1.4;
    }


</style>
    
<script>
    function confirmExport() {
    Swal.fire({
        title: '¿Incluir gráfica?',
        text: "¿Deseas incluir la gráfica en el reporte?",
        icon: 'question',
        showCancelButton: true,
        showCloseButton: true, // Mostrar botón de cierre
        confirmButtonText: 'Sí, incluir',
        cancelButtonText: 'No, solo datos'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir con includeChart=true
            window.location.href = '<?= Url::to(['gpsreport/download-report-stops', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => true]) ?>';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Redirigir con includeChart=false
            window.location.href = '<?= Url::to(['gpsreport/download-report-stops', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => false]) ?>';
        }
        // No hacer nada si se cierra el diálogo con la "X" o fuera del modal
    });
    return false; // Prevenir la acción por defecto del enlace
}
$('#showinfo').on('click', function () {
        setTimeout(function () {
            Highcharts.chart('stops-chart', {
                chart: {
                    type: 'line'
                },
                title: {
                    text: 'Número de Paradas por Día'
                },
                xAxis: {
                    categories: <?= json_encode(array_keys($stopsPerDay)) ?>
                },
                yAxis: {
                    title: {
                        text: 'Número de Paradas'
                    }
                },
                series: [{
                    name: 'Paradas',
                    data: <?= json_encode(array_values($stopsPerDay)) ?>
                }]
            });
        }, 2000); // Esperar 1 segundo para asegurar que el contenido se haya cargado
    });


    $(document).on('pjax:end', function() {
    flatpickr('#startDate', { enableTime: false, dateFormat: 'Y-m-d', locale: 'es' });
    flatpickr('#endDate', { enableTime: false, dateFormat: 'Y-m-d', locale: 'es' });
});


</script>