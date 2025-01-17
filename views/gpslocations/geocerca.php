<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Geocerca';
$this->params['breadcrumbs'][] = $this->title;
?>

<div id="map" style="height: 500px;"></div>
<button id="saveGeofence" class="btn btn-success">Guardar Geocerca</button>

<?php
$this->registerJsFile('@web/js/geocerca.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>