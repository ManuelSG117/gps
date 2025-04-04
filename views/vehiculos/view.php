<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Vehiculos $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="vehiculos-view">

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
            'modelo_auto',
            'marca_auto',
            'placa',
            'no_serie',
            'ano_adquisicion',
            'ano_auto',
            'km_recorridos',
            'velocidad_max',
            'km_litro',
            'color_auto',
            'tipo_motor',
            'estado_llantas',
            'estado_vehiculo',
            'estado_motor',
            'estatus',
            'conductor_id',
            'dispositivo_id',
            'poliza_id',
            'direccion_id',
            'departamento_id',
        ],
    ]) ?>

</div>
