<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Vehiculos $model */

$this->title = 'Create Vehiculos';
$this->params['breadcrumbs'][] = ['label' => 'Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vehiculos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
