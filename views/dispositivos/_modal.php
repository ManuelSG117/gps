    <?php
    use yii\widgets\ActiveForm;
    use yii\helpers\Html;
    use yii\widgets\Pjax;
    use kartik\file\FileInput; // Asegúrate de importar esta clase
    use yii\helpers\Url;
    
    /** @var yii\web\View $this */
    /** @var app\models\Dispositivos $model */
    /** @var yii\widgets\ActiveForm $form */
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <?php Pjax::begin(['id' => 'create-dispositivos-pjax', 'enablePushState' => false]); ?>
    <?php $form = ActiveForm::begin([
        'id' => 'create-dispositivos-form',
        'action' => ['dispositivos/create'],
        'method' => 'post',
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data', 'data-pjax' => true],
    ]); ?>
    
    <div class="step-bar">
        <div class="progress-bar" id="progress-bar" style="width: 25%;"></div>
        <div class="step active" id="step-1">1</div>
        <div class="step" id="step-2">2</div>
        <div class="step" id="step-3">3</div>
        <div class="step" id="step-4">4</div>
        <div class="step" id="step-5">5</div>
    </div>
    
    <div class="modal-body">
        <div id="step-content-1" class="step-content active">
            <h5 class="text-center text-primary">Información del dispositivo</h5>
            
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'imei')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'num_tel')->textInput([
                        'maxlength' => 10,
                        'pattern' => '\d{10}',
                        'title' => 'El teléfono debe contener 10 dígitos',
                        'inputmode' => 'numeric',
                        'type' => 'tel',
                        'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');"
                    ]) ?>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'marca')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'modelo')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
    
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-2" class="step-content">
            <h5 class="text-center text-primary">Características del vehículo</h5>
            
            <?= $form->field($model, 'cat_dispositivo')->textInput(['maxlength' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'modelo_auto')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'marca_auto')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'no_serie')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'color_auto')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'ano_auto')->textInput([
                        'maxlength' => 4,
                        'pattern' => '\d{4}',
                        'title' => 'El año debe contener 4 dígitos',
                        'inputmode' => 'numeric',
                        'type' => 'year',
                        'placeholder' => 'AAAA',
                        'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');"
                    ]) ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'velocidad_max')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'sensor_temp')->dropDownList([
                        '1' => 'Sí',
                        '0' => 'No',
                    ], ['prompt' => 'Seleccione una opción']) ?>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'tipo_motor')->dropDownList([
                        'Gasolina' => 'Gasolina',
                        'Diesel' => 'Diesel',
                        'Eléctrico' => 'Eléctrico',
                        'Híbrido' => 'Híbrido',
                    ], ['prompt' => 'Seleccione el tipo de motor']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'km_litro')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'estado_vehiculo')->dropDownList([
                        'Excelente' => 'Excelente',
                        'Bueno' => 'Bueno',
                        'Regular' => 'Regular',
                        'Malo' => 'Malo',
                    ], ['prompt' => 'Seleccione el estado del vehículo']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'estado_llantas')->dropDownList([
                        'Excelente' => 'Excelente',
                        'Bueno' => 'Bueno',
                        'Regular' => 'Regular',
                        'Malo' => 'Malo',
                    ], ['prompt' => 'Seleccione el estado de las llantas']) ?>
                </div>
            </div>
    
            <button type="button" class="btn btn-secondary prev-step">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-3" class="step-content">
            <h5 class="text-center text-primary">Fotografías del vehículo</h5>
            
            <div class="tooltip-wrapper" data-toggle="tooltip" data-placement="top" title="1. Frontal, 2. Derecha, 3. Izquierda, 4. Trasera, 5. Tarjeta de circulación, 6. Llantas, 7. Km recorridos">
                <?= FileInput::widget([
                    'name' => 'vehicle_images[]',
                    'options' => [
                        'multiple' => true,
                        'maxFileCount' => 7, // Maximum number of files
                    ],
                    'pluginOptions' => [
                        'initialPreviewAsData' => true,
                        'initialCaption' => "Sube hasta 7 fotos",
                        'overwriteInitial' => false,
                        'maxFileSize' => 2800,
                        'showUpload' => false, // Hide the upload button
                        'fileActionSettings' => [
                            'showRemove' => true, // Show the remove button for each file
                            'showUpload' => false, // Hide the upload button for each file
                        ],
                        'previewFileType' => 'image',
                        'allowedFileExtensions' => ['jpg', 'jpeg', 'png'],
                        'uploadExtraData' => new \yii\web\JsExpression('function() {
                            var out = {}, key, i = 0;
                            $(".file-caption-name").each(function() {
                                key = "foto" + (i + 1);
                                out[key] = $(this).text();
                                i++;
                            });
                            return out;
                        }'),
                        'msgFilesTooMany' => 'El número de archivos seleccionados ({n}) excede el límite permitido de {m}. Por favor, seleccione un máximo de 7 archivos.',
                    ]
                ]); ?>
            </div>
            
            <button type="button" class="btn btn-secondary prev-step">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-4" class="step-content">
            <h5 class="text-center text-primary">Póliza de Seguro</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'aseguradora')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'no_poliza')->textInput() ?>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'fecha_compra')->textInput(['id' => 'fecha_compra']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'fecha_vencimiento')->textInput(['id' => 'fecha_vencimiento']) ?>
                </div>
            </div>
            
            <br>
            <?= FileInput::widget([
                'name' => 'policy_images[]',
                'options' => [
                    'multiple' => true,
                    'maxFileCount' => 2, // Maximum number of files
                ],
                'pluginOptions' => [
                    'initialPreviewAsData' => true,
                    'initialCaption' => "Sube hasta 2 fotos",
                    'overwriteInitial' => false,
                    'maxFileSize' => 2800,
                    'showUpload' => false, // Hide the upload button
                    'fileActionSettings' => [
                        'showRemove' => true, // Show the remove button for each file
                        'showUpload' => false, // Hide the upload button for each file
                    ],
                    'previewFileType' => 'image',
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png'],
                    'uploadExtraData' => new \yii\web\JsExpression('function() {
                        var out = {}, key, i = 0;
                        $(".file-caption-name").each(function() {
                            key = "foto" + (i + 1);
                            out[key] = $(this).text(); 
                            i++;
                        });
                        return out;
                    }'),
                    'msgFilesTooMany' => 'El número de archivos seleccionados ({n}) excede el límite permitido de {m}. Por favor, seleccione un máximo de 2 archivos.',
                ]
            ]); ?>
            
            <button type="button" class="btn btn-secondary prev-step ">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-5" class="step-content">
            <h5 class="text-center text-primary">Dirección</h5>
    
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'direccion')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'departamento')->textInput() ?>
                </div>
            </div>
    
            <?= $form->field($model, 'conductor_id')->textInput(['maxlength' => true]) ?>
            
            <button type="button" class="btn btn-secondary prev-step">Atrás</button>
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
            
    <script>
        let currentStep = 1;
        const totalSteps = 5;
        
        function updateStep(step) {
            $('.step').removeClass('active');
            $('#step-' + step).addClass('active');
            
            $('.step-content').removeClass('active');
            $('#step-content-' + step).addClass('active');
            
            const progress = (step / totalSteps) * 100;
            $('#progress-bar').css('width', progress + '%');
        }
        
        $('.next-step').click(function() {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStep(currentStep);
            }
        });
        
        $('.prev-step').click(function() {
            if (currentStep > 1) {
                currentStep--;
                updateStep(currentStep);
            }
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializa Flatpickr con localización en español
            flatpickr("#fecha_compra, #fecha_vencimiento", {
                dateFormat: "Y-m-d", // Formato de fecha
                locale: "es",       // Cambia al idioma español
            });
        });
    </script>
    
    <style>
        .step-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            position: relative;
        }
        .step {
            padding: 10px 15px;
            border-radius: 50%;
            background: #ccc;
            color: white;
            font-weight: bold;
            transition: background 0.3s;
            z-index: 1;
        }
        .step.active {
            background: #007bff;
        }
        .progress-bar {
            position: absolute;
            top: 50%;
            left: 0;
            height: 5px;
            background: #007bff;
            transition: width 0.3s;
            z-index: 0;
        }
        .modal-body {
            position: relative;
        }
        .step-content {
            display: none;
            transition: opacity 0.5s;
        }
        .step-content.active {
            display: block;
            opacity: 1;
        }
        .btn {
            margin-top: 10px;
            padding: 10px; /* Added padding */
        }
    
        .tooltip-wrapper {
            display: inline-block;
            width: 100%;
        }
        .tooltip-inner {
            max-width: none; /* Allow the tooltip to expand */
            white-space: pre-wrap; /* Allow line breaks in the tooltip */
        }
        .tooltip{
            margin-top: -10px !important;
        }
    </style>

    