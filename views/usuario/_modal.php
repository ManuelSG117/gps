<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\Usuario $model */
/** @var string $action */
?>
<?php Pjax::begin(['id' => 'create-usuario-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'create-usuario-form',
    'action' => $action === 'update' ? ['usuario/update', 'id' => $model->id] : ['usuario/create'],
    'method' => 'post',
    'enableClientValidation' => true,
    'options' => ['data-pjax' => false],
]); ?>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'username')->textInput([
                'maxlength' => true,
                'required' => true,
                'readonly' => $action === 'view',
            ]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'names')->textInput([
                'maxlength' => true,
                'required' => true,
                'readonly' => $action === 'view',
            ]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'correo_electronico')->textInput([
                'maxlength' => true,
                'type' => 'email',
                'required' => false,
                'readonly' => $action === 'view',
            ]) ?>
        </div>
        <div class="col-md-12">
            <?php if ($action === 'view'): ?>
                <?= $form->field($model, 'password')->passwordInput([
                    'value' => '********',
                    'readonly' => true,
                    'disabled' => true,
                ])->label('Contraseña') ?>
            <?php elseif ($action === 'update' && !Yii::$app->user->can('admin_users')): ?>
                <?= $form->field($model, 'password')->passwordInput([
                    'maxlength' => true,
                    'readonly' => true,
                    'disabled' => true,
                    'value' => '********',
                ])->label('Contraseña (solo editable por el administrador usuarios)') ?>
            <?php else: ?>
                <?= $form->field($model, 'password')->passwordInput([
                    'maxlength' => true,
                    'required' => $action === 'create',
                ]) ?>
            <?php endif; ?>
        </div>
        <?php if ($action === 'update'): ?>
        <div class="col-md-12">
            <?= $form->field($model, 'activo')->dropDownList([
                1 => 'Activo',
                0 => 'Inactivo',
            ], [
                'prompt' => 'Seleccione estatus',
            ]) ?>
        </div>
        <?php elseif ($action === 'view'): ?>
        <div class="col-md-12">
            <?= $form->field($model, 'activo')->textInput([
                'readonly' => true,
                'value' => $model->activo ? 'Activo' : 'Inactivo',
            ])->label('Estatus') ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($action !== 'view'): ?>
    <div class="d-flex justify-content-end mt-4">
        <?= Html::submitButton($action === 'update' ? 'Actualizar' : 'Guardar', ['class' => 'btn btn-success']) ?>
    </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?> 