<?php

use app\models\ReparacionVehiculo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Reparacion Vehiculos';
$this->params['breadcrumbs'][] = $this->title;



$this->registerJsFile('@web/js/reparacion-vehiculo.js', [
    'depends' => [\yii\web\JqueryAsset::class]
]);

?>
<div class="reparacion-vehiculo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reparacionModal">
            Crear Reparación
        </button>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(['id' => 'reparaciones-grid']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'vehiculo_id',
            'fecha',
            'tipo_servicio',
            'descripcion:ntext',
            //'costo',
            //'tecnico',
            //'notas:ntext',
            //'estatus',
            //'estado_servicio',
            //'motivo_pausa:ntext',
            //'requisitos_reanudar:ntext',
            //'fecha_finalizacion',
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', '#', [
                            'class' => 'btn btn-sm btn-info ajax-view',
                            'title' => 'Ver',
                            'data-url' => Url::to(['view', 'id' => $model->id]),
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-pencil-alt"></i>', '#', [
                            'class' => 'btn btn-sm btn-primary ajax-update',
                            'title' => 'Actualizar',
                            'data-url' => Url::to(['update', 'id' => $model->id]),
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', '#', [
                            'class' => 'btn btn-sm btn-danger ajax-delete',
                            'title' => 'Eliminar',
                            'data-url' => Url::to(['delete', 'id' => $model->id]),
                        ]);
                    },
                ],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>

<!-- Modal de Reparación -->
<div class="modal fade" id="reparacionModal" tabindex="-1" aria-labelledby="reparacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reparacionModalLabel">Nueva Reparación de Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', [
                'model' => new ReparacionVehiculo(),
            ]) ?>
        </div>
    </div>
</div>
