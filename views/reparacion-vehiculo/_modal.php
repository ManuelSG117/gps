<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Vehiculos;

/** @var yii\web\View $this */
/** @var app\models\ReparacionVehiculo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>


<?php $form = ActiveForm::begin([
    'id' => 'create-reparacion-form',
    'action' => ['reparacion-vehiculo/create'],
    'method' => 'post',
    'enableAjaxValidation' => false,
    'options' => ['data-pjax' => false],
]); ?>

<div class="modal-body">
    <!-- Indicadores de pasos -->
    <div class="step-indicators mb-4">
        <div class="d-flex justify-content-between">
            <div class="step-indicator active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Información Básica</div>
            </div>
            <div class="step-indicator" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Detalles del Servicio</div>
            </div>
            <div class="step-indicator" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">Información Técnica</div>
            </div>
        </div>
    </div>

    <!-- Paso 1: Información Básica -->
    <div id="step-content-1" class="step-content" data-step="1">
        <h5 class="text-center text-primary mb-4">Información Básica</h5>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'vehiculo_id')->dropDownList(
                    ArrayHelper::map(Vehiculos::find()->all(), 'id', function($model) {
                        return $model->marca_auto . ' ' . $model->modelo_auto . ' (' . $model->placa . ')';
                    }),
                    ['prompt' => 'Seleccione un vehículo', 'required' => true]
                ) ?>
            </div>          
              <div class="col-md-6">
                <?= $form->field($model, 'fecha')->textInput([
                    'class' => 'form-control flatpickr',
                    'placeholder' => '  ...',
                    'autocomplete' => 'off'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'tipo_servicio')->dropDownList([
                    'Mantenimiento Preventivo' => 'Mantenimiento Preventivo',
                    'Mantenimiento Correctivo' => 'Mantenimiento Correctivo',
                    'Reparación' => 'Reparación',
                    'Revisión' => 'Revisión',
                    'Otro' => 'Otro'
                ], ['prompt' => 'Seleccione tipo de servicio']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'estado_servicio')->dropDownList([
                    1 => 'Pendiente',
                    2 => 'En Proceso',
                    3 => 'Pausado',
                    4 => 'Completado'
                ], ['prompt' => 'Seleccione estado']) ?>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Paso 2: Detalles del Servicio -->
    <div id="step-content-2" class="step-content" data-step="2" style="display:none;">
        <h5 class="text-center text-primary mb-4">Detalles del Servicio</h5>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'descripcion')->textarea(['rows' => 4]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'costo')->textInput(['type' => 'number', 'step' => '0.01']) ?>
            </div>
            <div class="col-md-6">                <?= $form->field($model, 'fecha_finalizacion')->textInput([
                    'class' => 'form-control flatpickr',
                    'placeholder' => 'Fecha estimada de finalización...',
                    'autocomplete' => 'off'
                ]) ?>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-warning prev-step"><i class="fas fa-arrow-left"></i> Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Paso 3: Información Técnica -->
    <div id="step-content-3" class="step-content" data-step="3" style="display:none;">
        <h5 class="text-center text-primary mb-4">Información Técnica</h5>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'tecnico')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'notas')->textarea(['rows' => 3]) ?>
            </div>
        </div>

        <div class="row pause-fields" style="display: none;">
            <div class="col-md-6">
                <?= $form->field($model, 'motivo_pausa')->textarea(['rows' => 3]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'requisitos_reanudar')->textarea(['rows' => 3]) ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="view-mode-gallery" style="display: none;">
                    <h6 class="mb-3">Imágenes del Servicio</h6>
                    <div class="image-gallery d-flex flex-wrap gap-3"></div>
                </div>
                
                <div class="edit-mode-upload">
                    <label class="form-label">Imágenes del Servicio</label>
                    <div class="image-upload-container">
                        <div class="image-preview-container d-flex flex-wrap gap-2 mb-2"></div>
                        <div class="upload-controls">
                            <input type="file" id="imagen-servicio" name="imagenes[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">Puede seleccionar múltiples imágenes. Formatos permitidos: JPG, PNG</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-warning prev-step"><i class="fas fa-arrow-left"></i> Atrás</button>
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<style>
.step-indicators {
    margin-bottom: 20px;
}

.step-indicator {
    text-align: center;
    flex: 1;
    padding: 10px;
    position: relative;
}

.step-indicator:not(:last-child):after {
    content: '';
    position: absolute;
    top: 30px;
    left: 50%;
    width: 100%;
    height: 2px;
    background-color: #ddd;
    z-index: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ddd;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    position: relative;
    z-index: 2;
}

.step-indicator.active .step-number {
    background-color: #007bff;
}

.step-title {
    font-size: 14px;
    color: #666;
}

.step-indicator.active .step-title {
    color: #007bff;
}

/* Estilos para la carga de imágenes */
.image-upload-container {
    border: 2px dashed #ddd;
    padding: 15px;
    border-radius: 5px;
    background-color: #f8f9fa;
}

.image-preview-container {
    min-height: 100px;
}

.image-preview {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    padding: 5px;
    cursor: pointer;
    color: #dc3545;
    border: none;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview .remove-image:hover {
    background: rgba(255, 255, 255, 1);
    color: #bd2130;
}

/* Estilos para la galería de imágenes en modo vista */
.image-gallery {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

.gallery-item {
    position: relative;
    width: 200px;
    height: 200px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.05);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Estilos para el lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 1050;
    padding: 40px;
}

.lightbox img {
    max-width: 90%;
    max-height: 90vh;
    margin: auto;
    display: block;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 30px;
    cursor: pointer;
    background: none;
    border: none;
    padding: 10px;
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 24px;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    padding: 20px 15px;
    border-radius: 5px;
}

.lightbox-prev {
    left: 20px;
}

.lightbox-next {
    right: 20px;
}
</style>
