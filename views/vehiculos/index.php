<?php

use app\models\Vehiculos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\VehiculosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Vehiculos $model */

$this->title = 'Vehículos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/js/vehiculos.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://animatedicons.co/scripts/embed-animated-icons.js"></script>

<div class="vehiculos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <button type="button" class="btn btn-sm btn-success btn-index" data-toggle="modal" data-target="#exampleModalCenter">
            Crear Vehículo
            <animated-icons
                src="https://animatedicons.co/get-icon?name=Car&style=minimalistic&token=8467715e-7688-4324-a72a-baa5aa8ec02e"
                trigger="loop"
                attributes='{"variationThumbColour":"#536DFE","variationName":"Two Tone","variationNumber":2,"numberOfGroups":2,"backgroundIsGroup":false,"strokeWidth":1,"defaultColours":{"group-1":"#000000","group-2":"#536DFE","background":"#FFFFFF"}}'
                height="35"
                width="35"
            ></animated-icons>
        </button>
    </p>

    <?php Pjax::begin(['id' => 'vehiculos-grid']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'modelo_auto',
            'marca_auto',
            // 'placa',
            // 'no_serie',
            // 'ano_auto',
            // 'color_auto',
            [
                'attribute' => 'estatus',
                'value' => function ($model) {
                    return $model->estatus ? 'Activo' : 'Inactivo';
                },
                'filter' => [1 => 'Activo', 0 => 'Inactivo'],
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', '#', [
                            'class' => 'btn btn-info light btn-sharp ajax-view',
                            'title' => 'Ver',
                            'data-url' => Url::to(['view', 'id' => $model->id]),
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-pencil-alt"></i>', '#', [
                            'class' => 'btn btn-primary light btn-sharp ajax-update',
                            'title' => 'Actualizar',
                            'data-url' => Url::to(['update', 'id' => $model->id]),
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', '#', [
                            'class' => 'btn btn-danger light btn-sharp ajax-delete',
                            'title' => 'Eliminar',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['delete', 'id' => $model->id]),
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Crear Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
