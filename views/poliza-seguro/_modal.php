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
                <!-- Pestañas de navegación -->
                <ul class="nav nav-tabs mb-3" id="polizaModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos-content" type="button" role="tab" aria-controls="datos-content" aria-selected="true">Datos de la Póliza</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial-content" type="button" role="tab" aria-controls="historial-content" aria-selected="false">Historial de Estados</button>
                    </li>
                </ul>
                
                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="polizaModalTabsContent">
                    <!-- Pestaña de Datos -->
                    <div class="tab-pane fade show active" id="datos-content" role="tabpanel" aria-labelledby="datos-tab">
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
                    
                    <!-- Pestaña de Historial -->
                    <div class="tab-pane fade" id="historial-content" role="tabpanel" aria-labelledby="historial-tab">
                        <div class="historial-container">
                            <div class="timeline-container">
                                <!-- El historial se cargará dinámicamente aquí -->
                                <p class="text-muted text-center py-3">El historial se cargará al visualizar una póliza existente.</p>
                            </div>
                        </div>
                    </div>
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
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    text-align: center;
    line-height: 24px;
    cursor: pointer;
    color: #dc3545;
}

.image-preview .remove-image:hover {
    background-color: rgba(255, 255, 255, 0.9);
}

/* Estilos para el timeline */
.timeline-container {
    padding: 15px 0;
}

.widget-timeline .timeline {
    list-style: none;
    position: relative;
    padding: 0;
    margin: 0;
}

.widget-timeline .timeline > li {
    position: relative;
    margin-bottom: 20px;
    padding-left: 30px;
}

.widget-timeline .timeline > li:last-child {
    margin-bottom: 0;
}

.widget-timeline .timeline-badge {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #ccc;
}

.widget-timeline .timeline-badge.primary {
    background-color: #007bff;
}

.widget-timeline .timeline-badge.success {
    background-color: #28a745;
}

.widget-timeline .timeline-badge.warning {
    background-color: #ffc107;
}

.widget-timeline .timeline-badge.danger {
    background-color: #dc3545;
}

.widget-timeline .timeline-badge.info {
    background-color: #17a2b8;
}

.widget-timeline .timeline-badge.secondary {
    background-color: #6c757d;
}

.widget-timeline .timeline-panel {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    background-color: #fff;
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