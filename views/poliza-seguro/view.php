<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguro $model */

$this->title = 'Póliza: ' . $model->no_poliza;
$this->params['breadcrumbs'][] = ['label' => 'Poliza Seguros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Registrar el archivo JS
$this->registerJsFile('@web/js/poliza-seguro.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Obtener el nombre y clase del estado actual
$estadoNombre = app\models\PolizaSeguro::getNombreEstado($model->estado);
$estadoClase = app\models\PolizaSeguro::getClaseEstado($model->estado);
?>
<div class="poliza-seguro-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que quieres eliminar esta póliza?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::button('Cambiar Estado', [
            'class' => 'btn btn-warning',
            'onclick' => "mostrarModalCambioEstado({$model->id})"
        ]) ?>
    </p>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <h5>Estado Actual: <span class="badge bg-<?= $estadoClase ?>"><?= $estadoNombre ?></span></h5>
            </div>
            
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'aseguradora',
                    'no_poliza',
                    'fecha_compra',
                    'fecha_vencimiento',
                ],
            ]) ?>
        </div>
    </div>
    
    <?php if (!empty($historial)): ?>
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Historial de Estados</h5>
        </div>
        <div class="card-body">
            <div id="historial-estados-timeline" class="timeline-container">
                <div class="widget-timeline">
                    <ul class="timeline">
                        <?php foreach ($historial as $item): ?>
                            <li>
                                <div class="timeline-badge <?= $item['clase_estado'] ?>"></div>
                                <div class="timeline-panel">
                                    <div class="media">
                                        <div class="media-body">
                                            <h6 class="mb-1"><?= $item['estado_nuevo_nombre'] ?></h6>
                                            <small class="d-block"><?= Yii::$app->formatter->asDatetime($item['fecha_cambio']) ?></small>
                                            <?php if (!empty($item['comentario'])): ?>
                                                <p class="mb-0 mt-2"><?= $item['comentario'] ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($item['motivo'])): ?>
                                                <div class="mt-2"><strong>Motivo:</strong> <?= $item['motivo'] ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['estado_anterior'])): ?>
                                                <small class="text-muted">Cambio desde: <?= $item['estado_anterior_nombre'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
