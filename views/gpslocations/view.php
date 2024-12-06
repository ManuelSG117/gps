<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Gpslocations $model */

$this->title = $model->GPSLocationID;
$this->params['breadcrumbs'][] = ['label' => 'Gpslocations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="gpslocations-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'GPSLocationID' => $model->GPSLocationID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'GPSLocationID' => $model->GPSLocationID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'GPSLocationID',
            'lastUpdate',
            'latitude',
            'longitude',
            'phoneNumber',
            'userName',
            'sessionID',
            'speed',
            'direction',
            'distance',
            'gpsTime',
            'locationMethod',
            'accuracy',
            'extraInfo',
            'eventType',
        ],
    ]) ?>

</div>
