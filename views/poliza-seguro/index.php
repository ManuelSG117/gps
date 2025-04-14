<?php

use app\models\PolizaSeguro;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguroSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\PolizaSeguro $model */

$this->title = 'Pólizas de Seguro';
$this->params['breadcrumbs'][] = $this->title;

// Register JS file
$this->registerJsFile('@web/js/poliza-seguro.js', ['depends' => [\yii\web\JqueryAsset::class]]);
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
        <?= Html::button('<i class="fa fa-plus"></i> Crear Póliza de Seguro', [
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
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-eye"></i>', 'javascript:void(0);', [
                            'class' => 'ajax-view',
                            'data-url' => Url::to(['view', 'id' => $model->id]),
                            'title' => 'Ver',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-pencil"></i>', 'javascript:void(0);', [
                            'class' => 'ajax-update',
                            'data-url' => Url::to(['update', 'id' => $model->id]),
                            'title' => 'Actualizar',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-trash"></i>', 'javascript:void(0);', [
                            'class' => 'ajax-delete',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['delete', 'id' => $model->id]),
                            'title' => 'Eliminar',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>

<?= $this->render('_modal', ['model' => $model]) ?>

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