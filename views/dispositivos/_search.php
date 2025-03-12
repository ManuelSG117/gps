<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DispositivosSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="dispositivos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombre') ?>

    <?= $form->field($model, 'imei') ?>

    <?= $form->field($model, 'num_tel') ?>

    <?= $form->field($model, 'marca') ?>

    <?php // echo $form->field($model, 'modelo') ?>

    <?php // echo $form->field($model, 'cat_dispositivo') ?>

    <?php // echo $form->field($model, 'modelo_auto') ?>

    <?php // echo $form->field($model, 'marca_auto') ?>

    <?php // echo $form->field($model, 'placa') ?>

    <?php // echo $form->field($model, 'no_serie') ?>

    <?php // echo $form->field($model, 'color_auto') ?>

    <?php // echo $form->field($model, 'ano_auto') ?>

    <?php // echo $form->field($model, 'velocidad_max') ?>

    <?php // echo $form->field($model, 'sensor_temp') ?>

    <?php // echo $form->field($model, 'tipo_motor') ?>

    <?php // echo $form->field($model, 'km_litro') ?>

    <?php // echo $form->field($model, 'aseguradora') ?>

    <?php // echo $form->field($model, 'no_poliza') ?>

    <?php // echo $form->field($model, 'fecha_vencimiento') ?>

    <?php // echo $form->field($model, 'fecha_compra') ?>

    <?php // echo $form->field($model, 'direccion') ?>

    <?php // echo $form->field($model, 'departamento') ?>

    <?php // echo $form->field($model, 'conductor_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
