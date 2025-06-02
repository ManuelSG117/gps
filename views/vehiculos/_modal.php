<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\Conductores;
use kartik\file\FileInput;
use app\models\Dispositivos;
use app\models\PolizaSeguro;


/** @var yii\web\View $this */
/** @var app\models\Vehiculos $model */
/** @var yii\widgets\ActiveForm $form */
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php Pjax::begin(['id' => 'create-vehiculos-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
    'id' => 'create-vehiculos-form',
    'action' => ['/vehiculos/create'],
    'method' => 'post',
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
        'data-pjax' => false
    ],
]); ?>

<div class="modal-body">
    <!-- Step Indicators -->
    <div class="step-indicators mb-4">
        <div class="d-flex justify-content-between">
            <div class="step-indicator active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Información Básica</div>
            </div>
            <div class="step-indicator" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Características</div>
            </div>
            <div class="step-indicator" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">Fotografías</div>
            </div>
            <div class="step-indicator" data-step="4">
                <div class="step-number">4</div>
                <div class="step-title">Asignación</div>
            </div>
        </div>
    </div>

    <!-- Step 1: Basic Information -->
    <div id="step-content-1" class="step-content" data-step="1">
        <h5 class="text-center text-primary mb-4">Información Básica del Vehículo</h5>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'modelo_auto')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'marca_auto')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'placa')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'no_serie')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'ano_adquisicion')->textInput([
                  'maxlength' => 4, 
                  'required' => true,
                  'type' => 'number',
                  'min' => '1900',
                  'max' => date('Y'),
                  'placeholder' => 'YYYY',
                  'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57)',
                  'oninput' => 'javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);'
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'ano_auto')->textInput([
                'maxlength' => 4, 
                'required' => true,
                'type' => 'number',
                'min' => '1900',
                'max' => date('Y'),
                'placeholder' => 'YYYY',
                'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57)',
                'oninput' => 'javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);'
                ]) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
            <?= $form->field($model, 'identificador')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: CAP-36'
                ]) ?>   
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'no_economico')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: VEH-001'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'tipo_motor')->dropDownList([
                    'Gasolina' => 'Gasolina',
                    'Diesel' => 'Diesel',
                    'Eléctrico' => 'Eléctrico',
                    'Híbrido' => 'Híbrido'
                ], ['prompt' => 'Seleccione tipo de motor']) ?>
            </div>
            <div class="col-md-6">
            <?= $form->field($model, 'color_auto')->textInput(['maxlength' => true]) ?>                 
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 2: Vehicle Characteristics -->
    <div id="step-content-2" class="step-content" data-step="2" style="display:none;">
        <h5 class="text-center text-primary mb-4">Características del Vehículo</h5>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'km_recorridos')->textInput(['required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'velocidad_max')->textInput(['required' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'km_litro')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'estatus')->dropDownList([
                    1 => 'Activo',
                    0 => 'Inactivo',
                ], ['required' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'estado_llantas')->dropDownList([
                    'Nuevo' => 'Nuevo',
                    'Bueno' => 'Bueno',
                    'Regular' => 'Regular',
                    'Malo' => 'Malo'
                ], ['prompt' => 'Seleccione estado']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'estado_vehiculo')->dropDownList([
                    'Nuevo' => 'Nuevo',
                    'Bueno' => 'Bueno',
                    'Regular' => 'Regular',
                    'Malo' => 'Malo'
                ], ['prompt' => 'Seleccione estado']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'estado_motor')->dropDownList([
                    'Nuevo' => 'Nuevo',
                    'Bueno' => 'Bueno',
                    'Regular' => 'Regular',
                    'Malo' => 'Malo'
                ], ['prompt' => 'Seleccione estado']) ?>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-warning prev-step me-3"><i class="fas fa-arrow-left"></i> Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 3: Vehicle Photos -->
    <div id="step-content-3" class="step-content" data-step="3" style="display:none;">
        <h5 class="text-center text-primary mb-4">Fotografías del Vehículo</h5>
        
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>¡Importante!</strong> Las fotos del vehículo deben agregarse en el siguiente orden:
            <ol>
                <li>Frente del vehículo</li>
                <li>Lateral derecho</li>
                <li>Lateral izquierdo</li>
                <li>Trasera</li>
                <li>Llantas</li>
                <li>Motor</li>
                <li>Kilometraje</li>
            </ol>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="view-mode-gallery" style="display: none;">
                    <h6 class="mb-3">Imágenes del Vehículo</h6>
                    <div class="image-gallery d-flex flex-wrap gap-3"></div>
                </div>
                
                <div class="edit-mode-upload">
                    <label class="form-label">Imágenes del Vehículo</label>
                    <div class="image-upload-container">
                        <div class="image-preview-container d-flex flex-wrap gap-2 mb-2" style="min-height: 150px;"></div>
                        <div class="upload-controls mt-2">
                            <input type="file" id="imagen-vehiculo" name="VehiculoImagenes[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted d-block mt-1">Agregue las imágenes en el orden especificado arriba. Formatos permitidos: JPG, PNG</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Icono personalizado para el mapa</label>
                <div class="image-upload-container">
                    <div class="icon-preview-container d-flex flex-wrap gap-2 mb-2"></div>
                    <div class="upload-controls">
                        <input type="file" id="icono-vehiculo" name="Vehiculos[icono_personalizado]" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Seleccione un icono personalizado para mostrar en el mapa (máx. 1MB). Si no selecciona ninguno, se usará el icono predeterminado.</small>
                        <div id="icono-preview" class="mt-2" style="display: none;">
                            <img src="" alt="Vista previa del icono" style="max-width: 64px; max-height: 64px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-warning prev-step me-3"><i class="fas fa-arrow-left"></i> Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 4: Assignment -->
    <div id="step-content-4" class="step-content" data-step="4" style="display:none;">
        <h5 class="text-center text-primary mb-4">Asignación</h5>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'conductor_id')->dropDownList(
                    ArrayHelper::map(Conductores::find()->all(), 'id', function($model) {
                        return $model->nombre . ' ' . $model->apellido_p . ' ' . ($model->apellido_m ? $model->apellido_m : '');
                    }),
                    ['prompt' => 'Seleccione un conductor']
                ) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'dispositivo_id')->dropDownList(
                    ArrayHelper::map(Dispositivos::find()->all(), 'id', function($model) {
                        return $model->nombre . ' - ' . $model->imei;
                    }),
                    ['prompt' => 'Seleccione un dispositivo']
                ) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'poliza_id')->dropDownList(
                    ArrayHelper::map(PolizaSeguro::find()->all(), 'id', function($model) {
                        return $model->aseguradora . ' - ' . $model->no_poliza;
                    }),
                    ['prompt' => 'Seleccione una póliza']
                ) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'direccion_id')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'departamento_id')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-warning prev-step me-3"><i class="fas fa-arrow-left"></i> Atrás</button>
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-danger" data-dismiss="modal" id="btn-cancelar">Cancelar</button>
    <button type="button" class="btn btn-success" id="btn-guardar" style="display:none;" onclick="$('#create-vehiculos-form').submit()">Guardar</button>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>

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

.image-preview {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    margin: 5px;
    display: inline-block;
}

.remove-image {
    background: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 50%;
    padding: 5px;
    width: 25px;
    height: 25px;
    line-height: 15px;
    text-align: center;
    cursor: pointer;
    color: #dc3545;
}

.image-upload-container {
    border: 2px dashed #ccc;
    padding: 20px;
    border-radius: 5px;
    background-color: #f8f9fa;
}

/* Hide buttons by default */
#btn-guardar, #btn-cancelar {
    display: none;
}
</style>

<script>
$(document).ready(function() {
    // Function to show a specific step
    window.showStep = function(stepNumber) {
        $('.step-content').hide();
        $(`#step-content-${stepNumber}`).show();
        
        // Update progress indicator
        $('.step-indicator').removeClass('active');
        $(`.step-indicator[data-step="${stepNumber}"]`).addClass('active');
        
        // Show/hide footer buttons based on step
        if (stepNumber === 4) {
            // Show buttons only on the last step
            $('#btn-guardar, #btn-cancelar').show();
        } else {
            $('#btn-guardar, #btn-cancelar').hide();
        }
    };
    
    // Initialize the first step when modal opens
    $('#exampleModalCenter').on('shown.bs.modal', function() {
        showStep(1);
    });
    
    // Function to check if form has data and show confirmation dialog
    function checkFormDataAndConfirm(callback) {
        // Get the current form action
        const formAction = $('#create-vehiculos-form').attr('action');
 //       console.log('Current form action:', formAction);
        
        // Skip confirmation for view action or non-create/update actions
        if (!/\/create$|\/update/.test(formAction) || /\/view/.test(formAction)) {
   ///         console.log('Skipping confirmation - not create/update or is view action');
            // For view or other actions, allow closing without confirmation
            if (typeof callback === 'function') {
                callback(true);
            }
            return true;
        }
        
      //  console.log('Checking for form data...');
        // Check if any field has data
        let hasData = false;
        $('#create-vehiculos-form input, #create-vehiculos-form select').each(function() {
            if ($(this).val() && $(this).val() !== '') {
                hasData = true;
        //        console.log('Found data in field:', $(this).attr('id') || $(this).attr('name'), 'Value:', $(this).val());
                return false; // Break the loop
            }
        });
        
        // Check if any file input has files - with proper error handling
        try {
            $('.file-preview-thumbnails').each(function() {
                if ($(this).find('.file-preview-frame').length > 0) {
                    hasData = true;
          //          console.log('Found files in file input');
                    return false; // Break the loop
                }
            });
        } catch (e) {
           // console.log('Error checking file inputs:', e);
        }
        
        //console.log('Has data:', hasData);
        
        if (hasData) {
            // Double check for view action
            if (/\/view/.test(formAction)) {
          //      console.log('View action detected with data, bypassing confirmation');
                if (typeof callback === 'function') {
                    callback(true); // Allow closing without confirmation for view
                }
                return true;
            }
            
            //console.log('Showing confirmation dialog');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Si cancelas, perderás toda la información ingresada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, continuar editando'
            }).then((result) => {
              //  console.log('Dialog result:', result);
                if (result.isConfirmed) {
                //    console.log('User confirmed, resetting form');
                    // Reset form and close modal
                    $('#create-vehiculos-form')[0].reset();
                    
                    // Clear file inputs safely
                    try {
                        $('.file-input').each(function() {
                            // Check if the element has the fileinput plugin initialized
                            if ($(this).data('fileinput')) {
                                $(this).fileinput('clear');
                  //              console.log('Cleared file input');
                            }
                        });
                    } catch (e) {
                    //    console.log('Error clearing file inputs:', e);
                        // Fallback method to clear file inputs
                        $('input[type="file"]').val('');
                    }
                    
                    if (typeof callback === 'function') {
                      //  console.log('Executing callback with true');
                        callback(true); // Allow closing
                    }
                }
            });
            return false; // Prevent default closing
        }
     //   console.log('No data found, allowing close without confirmation');
        return true; // Allow closing if no data
    }
    
    // Handle all modal closing events
    $('#exampleModalCenter').on('hide.bs.modal', function(e) {
    //    console.log('Modal hide event triggered');
        // If this is triggered by our confirmed actions, don't interfere
        if ($(document).data('confirmed-close')) {
           // console.log('Confirmed close flag found, allowing close');
            $(document).data('confirmed-close', false);
            return true;
        }
        
        // Otherwise check and confirm
        if (!checkFormDataAndConfirm(function(confirmed) {
           // console.log('Confirmation callback with result:', confirmed);
            if (confirmed) {
             //   console.log('Setting confirmed-close flag and hiding modal');
                $(document).data('confirmed-close', true);
                $('#exampleModalCenter').modal('hide');
            }
        })) {
          //  console.log('Preventing default close');
            e.preventDefault();
            e.stopPropagation();
        }
    });
    
    // For direct button clicks (cancel button and close button)
    $('#btn-cancelar, button[data-dismiss="modal"]').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (checkFormDataAndConfirm(function(confirmed) {
            if (confirmed) {
                $(document).data('confirmed-close', true);
                $('#exampleModalCenter').modal('hide');
            }
        })) {
            // If no data, just close
            $('#exampleModalCenter').modal('hide');
        }
    });
    
    // Handle the Bootstrap 5 close button specifically
    $('.btn-close').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (checkFormDataAndConfirm(function(confirmed) {
            if (confirmed) {
                $(document).data('confirmed-close', true);
                $('#exampleModalCenter').modal('hide');
            }
        })) {
            // If no data, just close
            $('#exampleModalCenter').modal('hide');
        }
    });
});
</script>