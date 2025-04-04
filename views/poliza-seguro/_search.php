<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguroSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="poliza-seguro-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'aseguradora') ?>

    <?= $form->field($model, 'no_poliza') ?>

    <?= $form->field($model, 'fecha_compra') ?>

    <?= $form->field($model, 'fecha_vencimiento') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
