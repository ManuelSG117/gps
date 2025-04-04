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
            
         
            </div>
    
            <button type="button" class="btn btn-secondary prev-step">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-3" class="step-content">
            <h5 class="text-center text-primary">Fotografías del vehículo</h5>
            
          
            </div>
            
            <button type="button" class="btn btn-secondary prev-step">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-4" class="step-content">
            <h5 class="text-center text-primary">Póliza de Seguro</h5>
            
         
            
            <button type="button" class="btn btn-secondary prev-step ">Atrás</button>
            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>
        
        <div id="step-content-5" class="step-content">
            <h5 class="text-center text-primary">Dirección</h5>
    
          
            
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

    