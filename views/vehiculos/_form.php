<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Vehiculos $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehiculos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'modelo_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marca_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_serie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ano_adquisicion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ano_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'km_recorridos')->textInput() ?>

    <?= $form->field($model, 'velocidad_max')->textInput() ?>

    <?= $form->field($model, 'km_litro')->textInput() ?>

    <?= $form->field($model, 'color_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipo_motor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado_llantas')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado_vehiculo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado_motor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estatus')->textInput() ?>

    <?= $form->field($model, 'conductor_id')->textInput() ?>

    <?= $form->field($model, 'dispositivo_id')->textInput() ?>

    <?= $form->field($model, 'poliza_id')->textInput() ?>

    <?= $form->field($model, 'direccion_id')->textInput() ?>

    <?= $form->field($model, 'departamento_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
