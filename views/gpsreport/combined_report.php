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
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
<script src="/js/combined_report.js"></script>

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
                <?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary w-100']) ?>
            </div>
            <div class="col-lg-4 col-md-12 col-12 custom-dates" style="display: none;">
                <!-- Aquí solo irá el input de rango generado por JS -->
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<br>
<?php Pjax::begin(['id' => 'combined-map-pjax', 'timeout' => 10000]); ?>
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
    </div>
</div>
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
</style> 