<?php

use yii\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\GpslocationsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Gpslocations';
?>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>

<div id="map" style="width: 100%; height: 400px;"></div>

<script>
function initMap() {
    var mapOptions = {
        center: new google.maps.LatLng(19.432608, -99.133209), // Coordenadas de ejemplo (Ciudad de México)
        zoom: 10,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map"), mapOptions);
}

google.maps.event.addDomListener(window, 'load', initMap);
</script>
