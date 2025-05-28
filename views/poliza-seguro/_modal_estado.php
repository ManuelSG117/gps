<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\PolizaSeguro;

/** @var yii\web\View $this */

?>
<!-- Modal para cambio de estado -->
<div class="modal fade" id="cambioEstadoModal" tabindex="-1" role="dialog" aria-labelledby="cambioEstadoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambioEstadoModalTitle">Cambiar Estado de Póliza</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-cambio-estado" enctype="multipart/form-data">
                    <input type="hidden" id="poliza-id" name="poliza_id">
                    
                    <div class="mb-3">
                        <label for="nuevo-estado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevo-estado" name="estado" required>
                            <option value="">Seleccione un estado</option>
                            <option value="<?= PolizaSeguro::ESTADO_ACTIVA ?>">Activa</option>
                            <option value="<?= PolizaSeguro::ESTADO_VENCIDA ?>">Vencida</option>
                            <option value="<?= PolizaSeguro::ESTADO_CANCELADA ?>">Cancelada</option>
                            <option value="<?= PolizaSeguro::ESTADO_SUSPENDIDA ?>">Suspendida</option>
                            <option value="<?= PolizaSeguro::ESTADO_RENOVADA ?>">Renovada</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo del cambio</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="">Seleccione un motivo</option>
                            <option value="Vencimiento">Vencimiento</option>
                            <option value="Solicitud del cliente">Solicitud del cliente</option>
                            <option value="Renovación">Renovación</option>
                            <option value="Falta de pago">Falta de pago</option>
                            <option value="Cambio de aseguradora">Cambio de aseguradora</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                    </div>
                    
                    <!-- Nuevo campo para subir imágenes -->
                    <div class="mb-3">
                        <label for="estado-poliza-images" class="form-label">Imágenes de la póliza (opcional)</label>
                        <input type="file" class="form-control" id="estado-poliza-images" name="poliza_images[]" multiple accept="image/*">
                        <div class="form-text">Puede seleccionar hasta 2 imágenes (frente y reverso de la póliza).</div>
                        <div class="image-preview-container mt-2 d-flex flex-wrap"></div>
                    </div>
                </form>
                
                <!-- Contenedor para el historial de estados -->
                <div id="historial-estados-container" class="mt-4 d-none">
                    <h6>Historial de Estados</h6>
                    <div class="widget-timeline">
                        <ul class="timeline" id="historial-estados-lista">
                            <!-- El historial se cargará dinámicamente aquí -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-estado">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Script para previsualizar imágenes -->
<script>
$(document).ready(function() {
    // Previsualización de imágenes al seleccionarlas
    $(document).on('change', '#estado-poliza-images', function() {
        const previewContainer = $(this).siblings('.image-preview-container');
        previewContainer.empty();
        
        if (this.files && this.files.length > 0) {
            for (let i = 0; i < Math.min(this.files.length, 2); i++) {
                const file = this.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview me-2 mb-2';
                    previewDiv.innerHTML = `<img src="${e.target.result}" alt="Vista previa" style="max-width: 150px; max-height: 150px;">`;
                    previewContainer.append(previewDiv);
                }
                
                reader.readAsDataURL(file);
            }
        }
    });
});
</script>