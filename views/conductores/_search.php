<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ConductoresSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conductores-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombres') ?>

    <?= $form->field($model, 'apellido_p') ?>

    <?= $form->field($model, 'apellido_m') ?>

    <?= $form->field($model, 'no_licencia') ?>

    <?php // echo $form->field($model, 'estado') ?>

    <?php // echo $form->field($model, 'municipio') ?>

    <?php // echo $form->field($model, 'colonia') ?>

    <?php // echo $form->field($model, 'calle') ?>

    <?php // echo $form->field($model, 'num_ext') ?>

    <?php // echo $form->field($model, 'num_int') ?>

    <?php // echo $form->field($model, 'cp') ?>

    <?php // echo $form->field($model, 'telefono') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'tipo_sangre') ?>

    <?php // echo $form->field($model, 'fecha_nacimiento') ?>

    <?php // echo $form->field($model, 'nombres_contacto') ?>

    <?php // echo $form->field($model, 'apellido_p_contacto') ?>

    <?php // echo $form->field($model, 'apellido_m_contacto') ?>

    <?php // echo $form->field($model, 'parentesco') ?>

    <?php // echo $form->field($model, 'telefono_contacto') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
