<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\VehiculosSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehiculos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'modelo_auto') ?>

    <?= $form->field($model, 'marca_auto') ?>

    <?= $form->field($model, 'placa') ?>

    <?= $form->field($model, 'no_serie') ?>

    <?php // echo $form->field($model, 'ano_adquisicion') ?>

    <?php // echo $form->field($model, 'ano_auto') ?>

    <?php // echo $form->field($model, 'km_recorridos') ?>

    <?php // echo $form->field($model, 'velocidad_max') ?>

    <?php // echo $form->field($model, 'km_litro') ?>

    <?php // echo $form->field($model, 'color_auto') ?>

    <?php // echo $form->field($model, 'tipo_motor') ?>

    <?php // echo $form->field($model, 'estado_llantas') ?>

    <?php // echo $form->field($model, 'estado_vehiculo') ?>

    <?php // echo $form->field($model, 'estado_motor') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'conductor_id') ?>

    <?php // echo $form->field($model, 'dispositivo_id') ?>

    <?php // echo $form->field($model, 'poliza_id') ?>

    <?php // echo $form->field($model, 'direccion_id') ?>

    <?php // echo $form->field($model, 'departamento_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
