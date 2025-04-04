<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguro $model */

$this->title = 'Create Poliza Seguro';
$this->params['breadcrumbs'][] = ['label' => 'Poliza Seguros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="poliza-seguro-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
