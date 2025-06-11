<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Usuario $model */

$this->title = 'Mi Perfil: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="usuario-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'username',
                    'names',
                    'correo_electronico',
                    [
                        'attribute' => 'activo',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->activo === 1) {
                                return '<span class="badge badge-success">Activo</span>';
                            } else {
                                return '<span class="badge badge-danger">Inactivo</span>';
                            }
                        },
                    ],
                    'token_acceso',
                ],
            ]) ?>
        </div>
    </div>

</div>
```