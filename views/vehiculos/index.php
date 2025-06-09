<?php

use app\models\Vehiculos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\models\VehiculosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Vehiculos $model */

$this->title = 'Vehículos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/js/vehiculos.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="vehiculos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <button type="button" class="btn btn-sm btn-success btn-index" data-toggle="modal" data-target="#exampleModalCenter">
            Crear Vehículo
        </button>
    </p>

    <?php Pjax::begin(['id' => 'vehiculos-grid']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'modelo_auto',
            [
                'attribute' => 'identificador',
                'value' => function ($model) {
                    return $model->identificador;
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'identificador',
                    'data' => \yii\helpers\ArrayHelper::map(app\models\Vehiculos::find()->select('identificador')->distinct()->where(['not', ['identificador' => null]])->orderBy('identificador')->asArray()->all(), 'identificador', 'identificador'),
                    'options' => [
                        'placeholder' => 'Selecciona identificador(es)',
                        'multiple' => true,
                        'value' => (array)$searchModel->identificador,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'filterInputOptions' => [
                    'id' => 'identificador-filter',
                ],
            ],
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
                'template' => '{view} {update} {delete} {geofence-log}',
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
                    'geofence-log' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-route"></i>', '#', [
                            'class' => 'btn btn-warning light btn-sharp geofence-log-btn',
                            'title' => 'Ver entradas/salidas de geocercas',
                            'data-id' => $model->id,
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

<!-- Modal para mostrar logs de geocercas -->
<div class="modal fade" id="geofenceLogModal" tabindex="-1" role="dialog" aria-labelledby="geofenceLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="geofenceLogModalLabel">Entradas y Salidas de Geocercas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="geofenceFilterSelect" class="form-label">Filtrar por Geocerca:</label>
                    <select id="geofenceFilterSelect" class="form-select">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="geofenceLogTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Evento</th>
                                <th>Geocerca</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llenarán los logs -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
