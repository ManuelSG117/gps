<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguro $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="poliza-seguro-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'aseguradora')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_poliza')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_compra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_vencimiento')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
