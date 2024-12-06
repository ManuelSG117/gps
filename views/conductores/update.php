<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */

$this->title = 'Update Conductores: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conductores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conductores-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
