<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Dispositivos $model */
/** @var yii\widgets\ActiveForm $form */
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php Pjax::begin(['id' => 'create-dispositivos-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'create-dispositivos-form',
    'action' => ['dispositivos/create'],
    'method' => 'post',
    'enableClientValidation' => true,
    'options' => ['enctype' => 'multipart/form-data', 'data-pjax' => false],
]); ?>

<div class="modal-body">
    <!-- Step Indicators -->
    <div class="step-indicators mb-4">
        <div class="d-flex justify-content-between">
            <div class="step-indicator active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Información del Dispositivo</div>
            </div>
            <div class="step-indicator" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Imágenes</div>
            </div>
        </div>
    </div>

    <!-- Step 1: Device Information -->
    <div id="step-content-1" class="step-content" data-step="1">
        <h5 class="text-center text-primary mb-4">Información del dispositivo</h5>
        
        <?= $form->field($model, 'nombre')->textInput(['maxlength' => true, 'required' => true]) ?>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'imei')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'num_tel')->textInput([
                    'maxlength' => 10,
                    'pattern' => '\d{10}',
                    'title' => 'El teléfono debe contener 10 dígitos',
                    'inputmode' => 'numeric',
                    'type' => 'tel',
                    'required' => true,
                    'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');"
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'marca')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'modelo')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'cat_dispositivo')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    
    <!-- Step 2: Device Images -->
    <div id="step-content-2" class="step-content" data-step="2" style="display:none;">
        <h5 class="text-center text-primary mb-4">Imágenes del Dispositivo</h5>
        
        <div class="row mt-3">
            <div class="col-12">
                <label class="control-label">Imágenes del Dispositivo</label>
                <?= FileInput::widget([
                    'name' => 'vehicle_images[]',
                    'options' => [
                        'multiple' => true,
                        'accept' => 'image/*',
                        'class' => 'file-input'
                    ],
                    'pluginOptions' => [
                        'initialPreview' => [],
                        'initialPreviewAsData' => true,
                        'initialPreviewConfig' => [],
                        'overwriteInitial' => false,
                        'maxFileCount' => 4,
                        'showCaption' => true,
                        'showRemove' => true,
                        'showUpload' => false,
                        'browseClass' => 'btn btn-primary',
                        'browseIcon' => '<i class="fas fa-camera"></i> ',
                        'browseLabel' => 'Seleccionar Imágenes',
                        'msgFilesTooMany' => 'Solo puede subir un máximo de {n} imágenes',
                        'fileActionSettings' => [
                            'showRemove' => true,
                            'showUpload' => false,
                            'showZoom' => true,
                            'showDrag' => true,
                            'showDownload' => false
                        ]
                    ]
                ]); ?>
                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 4 imágenes.</small>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-warning prev-step me-3"><i class="fas fa-arrow-left"></i> Atrás</button>
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>

<script>
    $(document).ready(function() {
        let currentStep = 1;
        const totalSteps = 2;
        
        function validateStep(stepNumber) {
            let isValid = true;
            
            // Get all required fields in the current step
            $(`#step-content-${stepNumber} [required]`).each(function() {
                // Make sure the field is visible and enabled before validation
                if($(this).is(':visible') && !$(this).prop('disabled')) {
                    if($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                }
            });
            
            if(!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos requeridos',
                    text: 'Por favor complete todos los campos obligatorios antes de continuar.'
                });
            }
            
            return isValid;
        }
        
        function updateStep(step) {
            $('.step-indicator').removeClass('active');
            $(`.step-indicator[data-step="${step}"]`).addClass('active');
            
            $('.step-content').hide();
            $(`#step-content-${step}`).show();
        }
        
        $('.next-step').click(function() {
            if (currentStep < totalSteps) {
                if(validateStep(currentStep)) {
                    currentStep++;
                    updateStep(currentStep);
                }
            }
        });
        
        $('.prev-step').click(function() {
            if (currentStep > 1) {
                currentStep--;
                updateStep(currentStep);
            }
        });
        
        // Initialize Flatpickr when the modal is shown
        $(document).on('shown.bs.modal', '#exampleModalCenter', function() {
            flatpickr(".flatpickr-date", {
                dateFormat: "Y-m-d",
                locale: "es",
                allowInput: true,
                altInput: true,
                altFormat: "d/m/Y",
                disableMobile: true
            });
        });
    });
</script>

<style>
.step-indicators {
    margin-bottom: 20px;
}
.step-indicator {
    text-align: center;
    position: relative;
    flex: 1;
}
.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 5px;
    font-weight: bold;
}
.step-indicator.active .step-number {
    background-color: #007bff;
    color: white;
}
.step-title {
    font-size: 12px;
    color: #6c757d;
}
.step-indicator.active .step-title {
    color: #007bff;
    font-weight: bold;
}
.step-content {
    transition: opacity 0.3s;
}
.btn {
    margin-top: 10px;
    padding: 10px;
}
.tooltip-wrapper {
    display: inline-block;
    width: 100%;
}
.tooltip-inner {
    max-width: none;
    white-space: pre-wrap;
}
.tooltip{
    margin-top: -10px !important;
}
</style>

    