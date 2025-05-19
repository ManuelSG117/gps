<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="reparacion-vehiculo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'vehiculo_id') ?>

    <?= $form->field($model, 'fecha') ?>

    <?= $form->field($model, 'tipo_servicio') ?>

    <?= $form->field($model, 'descripcion') ?>

    <?php // echo $form->field($model, 'costo') ?>

    <?php // echo $form->field($model, 'tecnico') ?>

    <?php // echo $form->field($model, 'notas') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'estado_servicio') ?>

    <?php // echo $form->field($model, 'motivo_pausa') ?>

    <?php // echo $form->field($model, 'requisitos_reanudar') ?>

    <?php // echo $form->field($model, 'fecha_finalizacion') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
