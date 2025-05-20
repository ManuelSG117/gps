<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="reparacion-vehiculo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vehiculo_id')->textInput() ?>

    <?= $form->field($model, 'fecha')->textInput() ?>

    <?= $form->field($model, 'tipo_servicio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'costo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tecnico')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notas')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estatus')->textInput() ?>

    <?= $form->field($model, 'estado_servicio')->textInput() ?>

    <?= $form->field($model, 'motivo_pausa')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'requisitos_reanudar')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fecha_finalizacion')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
