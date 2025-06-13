<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

// Obtener dispositivos registrados (igual que en index.php)
$registeredImeis = (new \yii\db\Query())
    ->select(['imei'])
    ->from('dispositivos')
    ->column();
$registeredPhones = (new \yii\db\Query())
    ->select(['gps.phoneNumber', 'v.identificador'])
    ->from('gpslocations gps')
    ->innerJoin('dispositivos d', 'gps.phoneNumber = d.imei')
    ->innerJoin('vehiculos v', 'd.id = v.dispositivo_id')
    ->where(['gps.phoneNumber' => $registeredImeis])
    ->groupBy('gps.phoneNumber')
    ->all();
$formattedPhones = [];
foreach ($registeredPhones as $phone) {
    $formattedPhones[$phone['phoneNumber']] = $phone['identificador'];
}
$deviceOptions = ['all' => 'Todos los dispositivos'] + $formattedPhones;
$defaultGps = Yii::$app->request->get('gps', array_key_first($formattedPhones));

// Inyectar los datos para JS
if (isset(
    $locations
)) {
    echo '<script>var locations = ' . json_encode($locations) . ';</script>';
}
if (isset($stops)) {
    echo '<script>var stops = ' . json_encode($stops) . ';</script>';
}

?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
<script src="/js/leaflet.movingRotatedMarker.js"></script>
<script src="/js/combined_report.js"></script>
<script type="module" src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs"></script>
<script src="https://rawcdn.githack.com/bbecquet/Leaflet.RotatedMarker/master/leaflet.rotatedMarker.js"></script>

<div class="gps-report-form">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['gpsreport/combined-report'],
        'options' => ['data-pjax' => true]
    ]); ?>
    <div class="container-fluid">
        <div class="row align-items-end">
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
            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="gps">Dispositivo:</label>
                    <?= Html::dropDownList('gps', $defaultGps, $deviceOptions, [
                        'class' => 'form-control',
                        'id' => 'gps',
                    ]) ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-12">
                <div class="form-group">
                    <label for="minStopDuration">Min. duración parada (min):</label>
                    <input type="number" class="form-control" id="minStopDuration" name="minStopDuration"
                           min="1" step="1"
                           value="<?= Html::encode(Yii::$app->request->get('minStopDuration', 3)) ?>">
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 custom-dates" style="display: none;">
                <!-- Aquí irá el input de rango generado por JS -->
            </div>
            <div class="col-lg-2 col-md-4 col-12">
                <?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary w-100']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php Pjax::begin(['id' => 'combined-map-pjax', 'timeout' => 10000]); ?>
    <?php if (isset($locations) && !empty($locations)): ?>
        <div id="cards-stats-wrapper">
            <div class="custom card-container">
                <div class="custom-card-header">
                    <h4 class="custom-card-title">Mapa de Ruta y Paradas</h4>
                </div>
                <div class="custom-card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="showStopsOnMap">
                        <label class="form-check-label" for="showStopsOnMap">Mostrar paradas en el mapa</label>
                    </div>
                    <div id="combined-map" style="height: 500px; width: 100%; position: relative;"></div>
                    <div class="form-check form-switch mb-3 mt-3">
                        <input class="form-check-input" type="checkbox" id="toggleCardsAlwaysOpen">
                        <label class="form-check-label" for="toggleCardsAlwaysOpen">Mostrar siempre la información de las tarjetas sin animación</label>
                    </div>
                </div>
            </div>
            <br>
            <!-- Cards de estadísticas de ruta -->
            <?php echo $this->render('_route_stats_cards', ['locations' => $locations]); ?>
            <!-- Cards de paradas y tiempo promedio de parada -->
            <div id="stops-stats-cards" class="mb-3" style="display:none;">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-12 mb-3">
                        <div class="cards">
                            <dotlottie-player src="https://lottie.host/06c57bb2-0963-4084-bcc9-05e6e5156d90/4U8QhsebpY.lottie" background="transparent" speed="1" style="width: 170px; height: 120px" loop autoplay></dotlottie-player>
                            <div class="card__content">
                                <p class="card__title">Total de Paradas</p>
                                <p class="card__description stat-value" id="stat-total-stops">-</p>
                                <p class="card__description">Cantidad de paradas detectadas en la ruta.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12 mb-3">
                        <div class="cards">
                            <dotlottie-player src="https://lottie.host/ed84083f-f28a-4a85-829b-b9c3e6d57d3d/9jQnEiULKi.lottie" background="transparent" speed="1" style="width: 170px; height: 120px" loop autoplay></dotlottie-player>
                            <div class="card__content">
                                <p class="card__title">Tiempo Promedio de Parada</p>
                                <p class="card__description stat-value" id="stat-avg-stop-duration">-</p>
                                <p class="card__description">Promedio de tiempo detenido por parada.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Selecciona un filtro y presiona "Mostrar" para ver el mapa.</div>
    <?php endif; ?>
<?php Pjax::end(); ?>


<style>
#combined-map {
    height: 500px !important;
    width: 100% !important;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1;
    position: relative;
}

.custom-dates .flatpickr-input {
    width: 100%;
}
</style> 