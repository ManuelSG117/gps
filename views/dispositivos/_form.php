<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Dispositivos $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="dispositivos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imei')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'num_tel')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marca')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'modelo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cat_dispositivo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'modelo_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marca_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_serie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ano_auto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'velocidad_max')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sensor_temp')->textInput() ?>

    <?= $form->field($model, 'tipo_motor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'km_litro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'aseguradora')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'no_poliza')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_vencimiento')->textInput() ?>

    <?= $form->field($model, 'fecha_compra')->textInput() ?>

    <?= $form->field($model, 'direccion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'departamento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'conductor_id')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
