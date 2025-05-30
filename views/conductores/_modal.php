<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */
/** @var yii\widgets\ActiveForm $form */
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php Pjax::begin(['id' => 'create-conductores-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
   'id' => 'create-conductores-form',
   'action' => ['conductores/create'], 
   'method' => 'post',
   'enableClientValidation' => false, 
   'options' => ['data-pjax' => true], 
]); ?>

<div class="modal-body">
    <!-- Step Indicators -->
    <div class="step-indicators mb-4">
        <div class="d-flex justify-content-between">
            <div class="step-indicator active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Información Personal</div>
            </div>
            <div class="step-indicator" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Contacto de Emergencia</div>
            </div>
        </div>
    </div>

    <!-- Step 1: Personal Information -->
    <div id="step-content-1" class="step-content" data-step="1">
        <h5 class="text-center text-primary mb-4">Información Personal</h5>
        <div class="row">
            <?= $form->field($model, 'nombre', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'apellido_p', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'apellido_m', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'fecha_nacimiento', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['id' => 'fecha_nacimiento']) ?>
                    
            <?= $form->field($model, 'no_licencia', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'cp', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput([
                'maxlength' => true,
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5)",
            
            ]) ?>

            <?= $form->field($model, 'estado', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->dropDownList([], [
                'prompt' => '',
                'id' => 'estado-dropdown',
            ]) ?>

            <?= $form->field($model, 'municipio', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->dropDownList([], [
                'prompt' => '',
                'id' => 'municipio-dropdown',  
            ]) ?>

            <?= $form->field($model, 'colonia', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'calle', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'num_ext', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput() ?>

            <?= $form->field($model, 'num_int', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'telefono', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput([
                'maxlength' => 10, 
                'pattern' => '\d{10}', 
                'title' => 'El teléfono debe contener 10 dígitos',
                'inputmode' => 'numeric',
                'type' => 'tel',
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');",
                'required' => false
            ]) ?>

            <?= $form->field($model, 'email', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput([
                'type' => 'text', // Changed from 'email' to 'text' to avoid browser validation
                'maxlength' => 255,
                'title' => 'Ingresa una dirección de correo electrónico válida',
                'required' => false,
                'aria-invalid' => false,
                'tabindex' => '0', // Ensure it's in the tab order
                'class' => 'form-control' // Ensure proper styling
            ]) ?>

            <?= $form->field($model, 'tipo_sangre', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->dropDownList([
                'A+' => 'A+',
                'A-' => 'A-',
                'B+' => 'B+',
                'B-' => 'B-',
                'AB+' => 'AB+',
                'AB-' => 'AB-',
                'O+' => 'O+',
                'O-' => 'O-',
            ], ['prompt' => 'Selecciona uno']) ?>

            <?= $form->field($model, 'no_empleado', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'foto', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->fileInput([
                'accept' => 'image/*',
                'class' => 'form-control',
                'id' => 'conductor-foto'
            ]) ?>

            <div class="col-md-4 form-field-spacing">
                <div id="foto-preview" class="mt-2" style="display: none;">
                    <img src="" alt="Vista previa" style="max-width: 150px; max-height: 150px;">
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary next-step" id="to-step-2">Siguiente <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- Step 2: Emergency Contact -->
    <div id="step-content-2" class="step-content" data-step="2" style="display:none;">
        <h5 class="text-center text-primary mb-4">Contacto de Emergencia</h5>
        <div class="row">
            <?= $form->field($model, 'nombre_contacto', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'apellido_p_contacto', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'apellido_m_contacto', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'parentesco', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->dropDownList([
                'Padre' => 'Padre',
                'Madre' => 'Madre',
                'Hermano' => 'Hermano',
                'Hermana' => 'Hermana',
                'Esposo(a)' => 'Esposo(a)',
                'Hijo(a)' => 'Hijo(a)',
                'Amigo(a)' => 'Amigo(a)',
                'Otro' => 'Otro',
            ], ['prompt' => 'Selecciona uno']) ?>

            <?= $form->field($model, 'telefono_contacto', [
                'options' => ['class' => 'col-md-4 form-field-spacing']
            ])->textInput([
                'maxlength' => 10, 
                'pattern' => '\d{10}', 
                'title' => 'El teléfono debe contener 10 dígitos',
                'inputmode' => 'numeric',
                'type' => 'tel',
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');"
            ]) ?>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-warning prev-step"><i class="fas fa-arrow-left"></i> Atrás</button>
            <?= Html::submitButton('Guardar ', ['class' => 'btn btn-success', 'id' => 'btn-guardar']) ?>
        </div>
    </div>
</div>



<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>

<script src="/vendor/global/global.min.js"></script>
<script src="/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>

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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa Flatpickr con localización en español
        flatpickr("#fecha_nacimiento", {
            dateFormat: "Y-m-d", // Formato de fecha
            maxDate: "today",   // No permite seleccionar fechas futuras
            locale: "es",       // Cambia al idioma español
            onChange: function(selectedDates, dateStr, instance) {
                // Calcula la edad a partir de la fecha seleccionada
                var birthDate = new Date(dateStr);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var m = today.getMonth() - birthDate.getMonth();
                
                // Ajusta la edad si el cumpleaños aún no ha pasado este año
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                // Verifica si la edad es mayor o igual a 18
                if (age < 18) {
                    alert("La fecha seleccionada debe ser de una persona mayor de 18 años.");
                    instance.clear(); // Limpia el campo si la edad es menor a 18
                }
            }
        });

        // Function to show a specific step
        window.showStep = function(stepNumber) {
            $('.step-content').hide();
            $(`#step-content-${stepNumber}`).show();
            
            // Update progress indicator
            $('.step-indicator').removeClass('active');
            $(`.step-indicator[data-step="${stepNumber}"]`).addClass('active');
        };

        // Initialize the first step
        showStep(1);

        // Next step button click
        $('.next-step').on('click', function() {
            var currentStep = parseInt($(this).closest('.step-content').data('step'));
            showStep(currentStep + 1);
        });

        // Previous step button click
        $('.prev-step').on('click', function() {
            var currentStep = parseInt($(this).closest('.step-content').data('step'));
            showStep(currentStep - 1);
        });

        // Handle modal close with confirmation
        $('#btn-cancelar, button[data-bs-dismiss="modal"]').on('click', function(e) {
            e.preventDefault();
            
            // Check if form has data
            let hasData = false;
            $('#create-conductores-form input, #create-conductores-form select').each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    hasData = true;
                    return false; // Break the loop
                }
            });
            
            if (hasData) {
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
                    if (result.isConfirmed) {
                        // Reset form and close modal
                        $('#create-conductores-form')[0].reset();
                        $('.modal').modal('hide');
                    }
                });
            } else {
                $('.modal').modal('hide');
            }
        });
    });
</script>

<script>
   // Cargar los estados en el primer dropdown
$.ajax({
    url: '<?= \yii\helpers\Url::to(['conductores/get-estados']) ?>',
    method: 'GET',
    success: function (data) {
        var $estadoDropdown = $('#estado-dropdown');
        // Limpiar cualquier opción previa
        $estadoDropdown.empty();

        // Agregar la opción por defecto
        $estadoDropdown.append($('<option>', {
            value: '',
            text: 'Selecciona uno',
        }));

        // Crear un objeto para almacenar los estados y sus códigos
        var estados = {};

        // Llenar el dropdown de estados
        $.each(data.datos, function (index, estado) {
            // Guardar tanto el nombre como el código (cvegeo)
            estados[estado.cvegeo] = estado.nomgeo;

            // Agregar el estado al dropdown (guardar nombre como valor)
            $estadoDropdown.append($('<option>', {
                value: estado.nomgeo,  // Guardamos el nombre del estado como value
                'data-cvegeo': estado.cvegeo, // Guardamos el cvegeo como un atributo data-cvegeo
                text: estado.nomgeo    // Mostramos el nombre del estado en el dropdown
            }));
        });

        // Al seleccionar un estado, almacenar tanto el nombre como el código
        $('#estado-dropdown').change(function () {
            var estadoSeleccionado = $(this).find(':selected');
            var nombreEstado = estadoSeleccionado.val(); // El nombre del estado
            var cvegeoEstado = estadoSeleccionado.data('cvegeo'); // El cvegeo desde el atributo data-cvegeo
            cargarMunicipios(cvegeoEstado, nombreEstado); // Pasa el cvegeo y el nombre
        });
    },
    error: function () {
        alert('Hubo un error al cargar los estados.');
    }
});


    // Función para cargar los municipios
    function cargarMunicipios(cvegeoEstado, nombreEstado) {
    $.ajax({
        url: 'https://gaia.inegi.org.mx/wscatgeo/v2/mgem/' + cvegeoEstado,  // Usamos el código del estado
        method: 'GET',
        success: function (data) {
            var $municipioDropdown = $('#municipio-dropdown');
            $municipioDropdown.empty();  // Limpiar cualquier opción previa
            $municipioDropdown.append($('<option>', {
                value: '',
                text: 'Selecciona uno'
            }));

            // Crear un objeto para almacenar los municipios y sus códigos
            var municipios = {};

            // Llenar el dropdown de municipios
            $.each(data.datos, function (index, municipio) {
                municipios[municipio.cvegeo] = municipio.nomgeo;

                // Agregar el municipio al dropdown (guardar nombre como value)
                $municipioDropdown.append($('<option>', {
                    value: municipio.nomgeo,  // Guardamos el nombre del municipio como value
                    'data-cvegeo': municipio.cvegeo, // Guardamos el cvegeo como un atributo data-cvegeo
                    text: municipio.nomgeo    // Mostramos el nombre del municipio en el dropdown
                }));
            });

            // Al seleccionar un municipio, almacenar tanto el nombre como el código
            $('#municipio-dropdown').change(function () {
                var municipioSeleccionado = $(this).find(':selected');
                var nombreMunicipio = municipioSeleccionado.val(); // El nombre del municipio
                var cvegeoMunicipio = municipioSeleccionado.data('cvegeo'); // El cvegeo del municipio
                // Almacenar o utilizar los valores según sea necesario
                console.log("Estado:", nombreEstado, cvegeoEstado); // El nombre y código del estado
                console.log("Municipio:", nombreMunicipio, cvegeoMunicipio); // El nombre y código del municipio
            });
        },
        error: function () {
            alert('Hubo un error al cargar los municipios.');
        }
    });
}
</script>