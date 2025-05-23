<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\PolizaSeguro $model */
/** @var yii\widgets\ActiveForm $form */

?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<!-- Modal -->
<div class="modal fade" id="polizaModal" tabindex="-1" role="dialog" aria-labelledby="polizaModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="polizaModalTitle">Crear Póliza de Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="poliza-seguro-form">
                    <?php $form = ActiveForm::begin([
                        'id' => 'create-poliza-form',
                        'action' => ['create'],
                        'enableAjaxValidation' => false,
                        'enableClientValidation' => true,
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'aseguradora')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'no_poliza')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha_compra')->textInput([
                                'class' => 'form-control flatpickr-date',
                                'placeholder' => 'Seleccione fecha de compra'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha_vencimiento')->textInput([
                                'class' => 'form-control flatpickr-date',
                                'placeholder' => 'Seleccione fecha de vencimiento'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Imágenes de la Póliza (Frente y Reverso)</label>
                            <div class="image-upload-container">
                                <div class="image-preview-container d-flex flex-wrap gap-2 mb-2"></div>
                                <div class="upload-controls">
                                    <input type="file" id="imagen-poliza" name="poliza_images[]" class="form-control" accept="image/*" multiple>
                                    <small class="text-muted">Puede seleccionar múltiples imágenes. Formatos permitidos: JPG, PNG. Máximo 2 imágenes.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btn-save-poliza-footer"><i class="fa fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<style>
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
</style>

<script>
// Initialize Flatpickr immediately when this file is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Function to initialize Flatpickr
    function initFlatpickr() {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            locale: "es",
            allowInput: true,
            altInput: true,
            altFormat: "d/m/Y",
            disableMobile: true
        });
    }
    
    // Initialize when modal is shown
    $(document).on('shown.bs.modal', '#polizaModal', function() {
        initFlatpickr();
    });
    
    // Also try to initialize immediately in case the modal is already open
    initFlatpickr();
});
</script>