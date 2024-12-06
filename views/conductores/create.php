<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */

$this->title = 'Create Conductores';
$this->params['breadcrumbs'][] = ['label' => 'Conductores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conductores-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
