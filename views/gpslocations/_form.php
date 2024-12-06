<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Gpslocations $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="gpslocations-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lastUpdate')->textInput() ?>

    <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phoneNumber')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sessionID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'speed')->textInput() ?>

    <?= $form->field($model, 'direction')->textInput() ?>

    <?= $form->field($model, 'distance')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gpsTime')->textInput() ?>

    <?= $form->field($model, 'locationMethod')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'accuracy')->textInput() ?>

    <?= $form->field($model, 'extraInfo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'eventType')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
