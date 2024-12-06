<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\GpslocationsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="gpslocations-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'GPSLocationID') ?>

    <?= $form->field($model, 'lastUpdate') ?>

    <?= $form->field($model, 'latitude') ?>

    <?= $form->field($model, 'longitude') ?>

    <?= $form->field($model, 'phoneNumber') ?>

    <?php // echo $form->field($model, 'userName') ?>

    <?php // echo $form->field($model, 'sessionID') ?>

    <?php // echo $form->field($model, 'speed') ?>

    <?php // echo $form->field($model, 'direction') ?>

    <?php // echo $form->field($model, 'distance') ?>

    <?php // echo $form->field($model, 'gpsTime') ?>

    <?php // echo $form->field($model, 'locationMethod') ?>

    <?php // echo $form->field($model, 'accuracy') ?>

    <?php // echo $form->field($model, 'extraInfo') ?>

    <?php // echo $form->field($model, 'eventType') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
