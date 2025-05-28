<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\PolizaSeguro;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguroSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\PolizaSeguro $model */

$this->title = 'Poliza Seguros';
$this->params['breadcrumbs'][] = $this->title;

// Registrar el archivo JS
$this->registerJsFile('@web/js/poliza-seguro.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Agregar estilos para la línea de tiempo
$this->registerCss("
    .timeline-container {
        margin-top: 20px;
    }
    .widget-timeline .timeline {
        list-style: none;
        position: relative;
        padding: 0;
        margin: 0;
    }
    .widget-timeline .timeline > li {
        position: relative;
        margin-bottom: 20px;
        padding-left: 30px;
    }
    .widget-timeline .timeline > li:last-child {
        margin-bottom: 0;
    }
    .widget-timeline .timeline .timeline-badge {
        position: absolute;
        left: 0;
        top: 0;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #ccc;
    }
    .widget-timeline .timeline .timeline-badge.success {
        background-color: #28a745;
    }
    .widget-timeline .timeline .timeline-badge.danger {
        background-color: #dc3545;
    }
    .widget-timeline .timeline .timeline-badge.warning {
        background-color: #ffc107;
    }
    .widget-timeline .timeline .timeline-badge.primary {
        background-color: #007bff;
    }
    .widget-timeline .timeline .timeline-badge.dark {
        background-color: #343a40;
    }
    .widget-timeline .timeline .timeline-panel {
        padding: 10px;
        border-radius: 5px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
");
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Flatpickr CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<div class="poliza-seguro-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Update the button that opens the modal -->
    <p>
        <?= Html::button('Crear Póliza de Seguro', [
            'class' => 'btn btn-sm btn-success btn-index', 
            'data-bs-toggle' => 'modal', 
            'data-bs-target' => '#polizaModal'
        ]) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?php Pjax::begin(['id' => 'poliza-grid']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'aseguradora',
            'no_poliza',
            'fecha_compra',
            'fecha_vencimiento',
            [
                'attribute' => 'estado',
                'format' => 'raw',
                'value' => function ($model) {
                    $estadoNombre = PolizaSeguro::getNombreEstado($model->estado);
                    $estadoClase = PolizaSeguro::getClaseEstado($model->estado);
                    return '<span class="badge bg-' . $estadoClase . '">' . $estadoNombre . '</span>';
                },
                'filter' => [
                    PolizaSeguro::ESTADO_ACTIVA => 'Activa',
                    PolizaSeguro::ESTADO_VENCIDA => 'Vencida',
                    PolizaSeguro::ESTADO_CANCELADA => 'Cancelada',
                    PolizaSeguro::ESTADO_SUSPENDIDA => 'Suspendida',
                    PolizaSeguro::ESTADO_RENOVADA => 'Renovada',
                ],
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {cambiar-estado} {delete}',
                'contentOptions' => ['class' => 'action-column'], // Added this line for consistent styling
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-eye"></i></span>',
                            '#',
                            [
                                'title' => 'Ver',
                                'class' => 'btn btn-info light btn-sharp ajax-view',
                                'data-id' => $model->id,
                                'data-url' => Url::to(['view', 'id' => $model->id]),
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-pencil-alt"></i></span>',
                            '#',
                            [
                                'title' => 'Actualizar',
                                'class' => 'btn btn-primary light btn-sharp ajax-update',
                                'data-id' => $model->id,
                                'data-url' => Url::to(['update', 'id' => $model->id]),
                            ]
                        );
                    },
                    'cambiar-estado' => function ($url, $model, $key) {
                        return Html::button(
                            '<i class="fa fa-exchange-alt"></i></span>',
                            [
                                'title' => 'Cambiar Estado',
                                'class' => 'btn btn-warning light btn-sharp',
                                'onclick' => "mostrarModalCambioEstado({$model->id})",
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

<?= $this->render('_modal', ['model' => $model]) ?>
<?= $this->render('_modal_estado') ?>

<script>
// Initialize Flatpickr when the modal is shown
$(document).on('shown.bs.modal', '#polizaModal', function () {
    flatpickr('input[type="date"]', {
        dateFormat: "Y-m-d",
        locale: "es",
        allowInput: true,
        altInput: true,
        altFormat: "d/m/Y",
        disableMobile: true
    });
});
</script>