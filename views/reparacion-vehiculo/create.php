<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculo $model */

$this->title = 'Create Reparacion Vehiculo';
$this->params['breadcrumbs'][] = ['label' => 'Reparacion Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reparacion-vehiculo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
