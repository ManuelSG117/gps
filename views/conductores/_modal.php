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

<?php Pjax::begin(['id' => 'create-conductores-pjax', 'enablePushState' => false]); ?>
<?php $form = ActiveForm::begin([
   'id' => 'create-conductores-form',
   'action' => ['conductores/create'], 
   'method' => 'post',
   'enableClientValidation' => false, 
   'options' => ['data-pjax' => true], 

]); ?>

<div class="modal-body">
    <div class="row">
        <!-- Información personal -->
        <?= $form->field($model, 'nombres', [
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
            'inputmode' => 'numeric', // Asegura que solo se acepten números
            'type' => 'tel', // Define el tipo de entrada como teléfono
            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');" // Elimina caracteres no numéricos
        ]) ?>

        <?= $form->field($model, 'email', [
            'options' => ['class' => 'col-md-4 form-field-spacing']
        ])->textInput([
            'type' => 'email',
            'maxlength' => 255,
            'title' => 'Ingresa una dirección de correo electrónico válida'
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
        ], ['prompt' => '']) ?>

        <!-- Sección de Contacto de Emergencia -->
        <div class="col-12 mt-4 mb-3">
            <hr>
            <h5 class="text-center text-primary">Contacto de Emergencia</h5>
            <hr>
        </div>

        <?= $form->field($model, 'nombres_contacto', [
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
        ], ['prompt' => '']) ?>

        <?= $form->field($model, 'telefono_contacto', [
                  'options' => ['class' => 'col-md-4 form-field-spacing']
        ])->textInput([
            'maxlength' => 10, 
            'pattern' => '\d{10}', 
            'title' => 'El teléfono debe contener 10 dígitos',
            'inputmode' => 'numeric', // Asegura que solo se acepten números
            'type' => 'tel', // Define el tipo de entrada como teléfono
            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '');" // Elimina caracteres no numéricos
                ]) ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary', 'id' => 'btn-guardar']) ?>
    </div>

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>


<style>
.form-field-spacing label {
    padding-top: 8px; /* Espacio debajo del label */
}


</style>     
    <script src="/vendor/global/global.min.js"></script>
	<script src="/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    
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
            text: ''
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
                text: 'Selecciona el municipio...'
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