<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Gpslocations $model */

$this->title = 'Update Gpslocations: ' . $model->GPSLocationID;
$this->params['breadcrumbs'][] = ['label' => 'Gpslocations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->GPSLocationID, 'url' => ['view', 'GPSLocationID' => $model->GPSLocationID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="gpslocations-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
