<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculo $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Reparacion Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="reparacion-vehiculo-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'vehiculo_id',
            'fecha',
            'tipo_servicio',
            'descripcion:ntext',
            'costo',
            'tecnico',
            'notas:ntext',
            'estatus',
            'estado_servicio',
            'motivo_pausa:ntext',
            'requisitos_reanudar:ntext',
            'fecha_finalizacion',
        ],
    ]) ?>

</div>
