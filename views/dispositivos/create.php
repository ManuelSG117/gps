<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Dispositivos $model */

$this->title = 'Create Dispositivos';
$this->params['breadcrumbs'][] = ['label' => 'Dispositivos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dispositivos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
