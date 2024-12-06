<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conductores-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombres')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'apellido_p')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'apellido_m')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_licencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'municipio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'colonia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'calle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'num_ext')->textInput() ?>

    <?= $form->field($model, 'num_int')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cp')->textInput() ?>

    <?= $form->field($model, 'telefono')->textInput() ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipo_sangre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_nacimiento')->textInput() ?>

    <?= $form->field($model, 'nombres_contacto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'apellido_p_contacto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'apellido_m_contacto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parentesco')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telefono_contacto')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
