<?php

use app\models\Usuario;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\UsuarioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/js/usuario.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<div class="usuario-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button('Crear Usuario', [
            'class' => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#usuarioModal',
        ]) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(['id' => 'usuario-grid', 'timeout' => 10000]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            
            'username',
            'names',
            'correo_electronico',
            //para el estatus, si es 1 es activo, si es 0 es inactivo con un badge
            [
                'attribute' => 'activo',
                'value' => function ($model) {
                    return $model->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                },
                'format' => 'raw',
            ],
            
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Usuario $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-eye"></i>', '#', [
                            'title' => 'Ver',
                            'class' => 'btn btn-info light btn-sharp ajax-view-usuario',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['usuario/view', 'id' => $model->id]),
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-pencil-alt"></i>', '#', [
                            'title' => 'Actualizar',
                            'class' => 'btn btn-primary light btn-sharp ajax-update-usuario',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['usuario/update', 'id' => $model->id]),
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-trash"></i>', '#', [
                            'title' => 'Eliminar',
                            'class' => 'btn btn-danger light btn-sharp ajax-delete-usuario',
                            'data-id' => $model->id,
                            'data-url' => $url,
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

    <?php // Modal para crear/editar usuario ?>
    <div class="modal fade" id="usuarioModal" tabindex="-1" role="dialog" aria-labelledby="usuarioModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usuarioModalTitle">Crear Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="usuarioModalBody">
                    <?= $this->render('_modal', ['model' => new \app\models\Usuario(), 'action' => 'create']) ?>
                </div>
            </div>
        </div>
    </div>

</div>
