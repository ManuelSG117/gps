<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Dispositivos $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Dispositivos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="dispositivos-view">

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
            'nombre',
            'imei',
            'num_tel',
            'marca',
            'modelo',
            'cat_dispositivo',
            'modelo_auto',
            'marca_auto',
            'placa',
            'no_serie',
            'color_auto',
            'ano_auto',
            'velocidad_max',
            'sensor_temp',
            'tipo_motor',
            'km_litro',
            'aseguradora:ntext',
            'no_poliza',
            'fecha_vencimiento',
            'fecha_compra',
            'direccion',
            'departamento',
            'conductor_id',
        ],
    ]) ?>

</div>
