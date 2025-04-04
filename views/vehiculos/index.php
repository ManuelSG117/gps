<?php

use app\models\Vehiculos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\VehiculosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Vehiculos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vehiculos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Vehiculos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'modelo_auto',
            'marca_auto',
            'placa',
            'no_serie',
            //'ano_adquisicion',
            //'ano_auto',
            //'km_recorridos',
            //'velocidad_max',
            //'km_litro',
            //'color_auto',
            //'tipo_motor',
            //'estado_llantas',
            //'estado_vehiculo',
            //'estado_motor',
            //'estatus',
            //'conductor_id',
            //'dispositivo_id',
            //'poliza_id',
            //'direccion_id',
            //'departamento_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Vehiculos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
