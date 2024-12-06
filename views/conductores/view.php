<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conductores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="conductores-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'nombres',
            'apellido_p',
            'apellido_m',
            'no_licencia',
            'estado',
            'municipio',
            'colonia',
            'calle',
            'num_ext',
            'num_int',
            'cp',
            'telefono',
            'email:email',
            'tipo_sangre',
            'fecha_nacimiento',
            'nombres_contacto',
            'apellido_p_contacto',
            'apellido_m_contacto',
            'parentesco',
            'telefono_contacto',
        ],
    ]) ?>

</div>
