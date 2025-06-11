<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

// Agregar enlace a Bootstrap Icons
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css');

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login d-flex align-items-center justify-content-center min-vh-100 ">
    <div class="login-container bg-white p-5 rounded shadow-lg" style="max-width: 400px; width: 100%;">
        <h1 class="text-center mb-4 text-primary"><?= Html::encode($this->title) ?></h1>

        <p class="text-center text-muted mb-4">Por favor, ingrese sus credenciales para acceder:</p>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control form-control-lg mb-3'],
                'errorOptions' => ['class' => 'invalid-feedback'],
            ],
        ]); ?>

        <div class="form-group position-relative mb-3">
            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Ingrese su usuario',
                'class' => 'form-control form-control-lg ps-4',
            ])->label(false) ?>
            <i class="bi bi-person position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
        </div>

        <div class="form-group position-relative mb-4">
            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Ingrese su contraseña',
                'class' => 'form-control form-control-lg ps-4',
            ])->label(false) ?>
            <i class="bi bi-lock position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
        </div>

        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"form-check mb-3\">{input} {label}</div>\n{error}",
            'class' => 'form-check-input',
            'labelOptions' => ['class' => 'form-check-label'],
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton('Iniciar Sesión', [
                'class' => 'btn btn-primary btn-lg w-100 mb-3',
                'name' => 'login-button'
            ]) ?>
        </div>

            <?php ActiveForm::end(); ?>

          

        </div>
    </div>
</div>
