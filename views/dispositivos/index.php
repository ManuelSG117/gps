<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models\Dispositivos;
use app\models\DispositivosSearch;
use kartik\file\FileInput; 

/** @var yii\web\View $this */
/** @var app\models\DispositivosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Dispositivos';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/dispositivos.js',['depends' => [\yii\web\JqueryAsset::class]]
);

?>
<link href="/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	<link href="/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">

<div class="dispositivos-index">

    <h1><?= Html::encode($this->title) ?></h1>

   
    <p>
        <?= Html::button('Crear Dispositivo <animated-icons src="https://animatedicons.co/get-icon?name=Wireless&style=minimalistic&token=8dff41c2-b1dd-4146-a4e9-3a487f0e0f22" trigger="loop" attributes=\'{"variationThumbColour":"#536DFE","variationName":"Two Tone","variationNumber":2,"numberOfGroups":2,"backgroundIsGroup":false,"strokeWidth":1,"defaultColours":{"group-1":"#000000","group-2":"#536DFE","background":"#FFFFFF"}}\' height="30" width="30"></animated-icons>', [
            'class' => 'btn btn-sm btn-success btn-index',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#exampleModalCenter',
        ]) ?>
    </p>

    <?php Pjax::begin(['id' => 'dispositivos-grid', 'timeout' => 10000]); ?>
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'responsive' => true,  // Hacer la tabla responsive
    'hover' => true,  // Efecto hover sobre las filas
    
    'rowOptions' => function ($model, $index, $widget, $grid) {
        return ['data-id' => $model->id];
    },

    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombre',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'nombre'], 
        ], 
        [
            'attribute' => 'imei',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'imei'], 
        ],
        [
            'attribute' => 'placa',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'placa'], 
        ],
       
        [
            'class' => ActionColumn::className(),
            'template' => '{view} {update} {delete}',
            'contentOptions' => ['class' => 'action-column'], // Añadir esta línea
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a(
                        '<i class="fa fa-pencil-alt"></i></span>',
                        '#',
                        [
                            'title' => 'Actualizar',
                            'class' => 'btn btn-primary light btn-sharp ajax-update',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['dispositivos/update', 'id' => $model->id]),
                        ]
                    );
                },

                'view' => function ($url, $model, $key) {
                    return Html::a(
                        '<i class="fa fa-eye"></i></span>',
                        '#',
                        [
                            'title' => 'Ver',
                            'class' => 'btn btn-info light btn-sharp ajax-view',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['dispositivos/view', 'id' => $model->id]),
                        ]
                    );
                },

              'delete' => function ($url, $model, $key) {
             return Html::a(
        '<i class="fa fa-trash"></i></span>',
        '#',
        [
            'title' => 'Eliminar',
            'class' => 'btn btn-danger light btn-sharp ajax-delete',
            'data-id' => $model->id,
            'data-url' => $url,
        ]
    );
},
            ],
        ],
    ],
]); ?>
    <?php Pjax::end(); ?>

</div>

<!-- Modal -->
<div class="modal fade " id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Crear Dispositivo</h5>
                <script src="https://animatedicons.co/scripts/embed-animated-icons.js"></script>
                <script src="https://animatedicons.co/scripts/embed-animated-icons.js"></script>
                <animated-icons
                src="https://animatedicons.co/get-icon?name=Wireless&style=minimalistic&token=8dff41c2-b1dd-4146-a4e9-3a487f0e0f22"
                trigger="loop"
                attributes='{"variationThumbColour":"#536DFE","variationName":"Two Tone","variationNumber":2,"numberOfGroups":2,"backgroundIsGroup":false,"strokeWidth":1,"defaultColours":{"group-1":"#000000","group-2":"#536DFE","background":"#FFFFFF"}}'
                height="35"
                width="35"
                ></animated-icons>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', ['model' => $model, 'action' => 'create']) ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/vendor/sweetalert2/sweetalert2.min.js"></script>
