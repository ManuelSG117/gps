<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Geocerca $model */

$this->title = 'Create Geocerca';
$this->params['breadcrumbs'][] = ['label' => 'Geocercas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="geocerca-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
