<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
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
                            <label class="control-label">Imágenes de la Póliza (Frente y Reverso)</label>
                            <?= FileInput::widget([
                                'name' => 'poliza_images[]',
                                'options' => [
                                    'multiple' => true,
                                    'accept' => 'image/*',
                                ],
                                'pluginOptions' => [
                                    'initialPreview' => [],
                                    'initialPreviewAsData' => true,
                                    'initialPreviewConfig' => [],
                                    'overwriteInitial' => false,
                                    'maxFileCount' => 2,
                                    'showCaption' => true,
                                    'showRemove' => true,
                                    'showUpload' => false,
                                    'browseClass' => 'btn btn-primary',
                                    'browseIcon' => '<i class="fa fa-camera"></i> ',
                                    'browseLabel' => 'Seleccionar Imágenes',
                                    'msgFilesTooMany' => 'Solo puede subir un máximo de {n} imágenes',
                                ]
                            ]); ?>
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2 imágenes.</small>
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