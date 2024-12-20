<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Conductores $model */
/** @var yii\widgets\ActiveForm $form */
?>
<link rel="stylesheet" href="/vendor/pickadate/themes/default.css">
<link rel="stylesheet" href="/vendor/pickadate/themes/default.date.css">

<?php $form = ActiveForm::begin([
   'id' => 'create-conductores-form',
   'action' => ['conductores/create'], // Asegúrate de que la acción sea la correcta
   'method' => 'post',
   'enableClientValidation' => false, 
   'options' => ['onsubmit' => 'submitForm(event)'], // Llama a la función JavaScript

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
        ])->textInput(['class' => 'datepicker-default form-control', 'id' => 'fecha_nacimiento']) ?>
                
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
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>



<style>
.form-field-spacing label {
    padding-top: 8px; /* Espacio debajo del label */
}


</style>     
    <script src="/vendor/global/global.min.js"></script>
	<script src="/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    
    <!-- pickdate -->
    <script src="/vendor/pickadate/picker.js"></script>
    <script src="/vendor/pickadate/picker.time.js"></script>
    <script src="/vendor/pickadate/picker.date.js"></script>

    <!-- Pickdate -->
    <script src="/js/plugins-init/pickadate-init.js"></script>

    <script>
    $(document).ready(function () {
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

            // Llenar el dropdown de estados
            $.each(data.datos, function (index, estado) {
                $estadoDropdown.append($('<option>', {
                    value: estado.cvegeo,  // El valor será el código del estado
                    text: estado.nomgeo    // El texto será el nombre del estado
                }));
            });
        },
        error: function () {
            alert('Hubo un error al cargar los estados.');
        }
    });

    // Cuando se seleccione un estado, cargar los municipios correspondientes
    $('#estado-dropdown').change(function () {
        var estadoSeleccionado = $(this).val();
        if (estadoSeleccionado) {
            var cvegeo = estadoSeleccionado;  // El valor seleccionado es el código del estado
            cargarMunicipios(cvegeo);
        }
    });

    // Función para cargar los municipios
    function cargarMunicipios(cvegeo) {
        $.ajax({
            url: 'https://gaia.inegi.org.mx/wscatgeo/v2/mgem/' + cvegeo,  // Usamos el código del estado
            method: 'GET',
            success: function (data) {
                var $municipioDropdown = $('#municipio-dropdown');
                $municipioDropdown.empty();  // Limpiar cualquier opción previa
                $municipioDropdown.append($('<option>', {
                    value: '',
                    text: 'Selecciona el municipio...'
                }));

                // Llenar el dropdown de municipios con los datos recibidos
                $.each(data.datos, function (index, municipio) {
                    $municipioDropdown.append($('<option>', {
                        value: municipio.cvegeo,  // El código del municipio
                        text: municipio.nomgeo    // El nombre del municipio
                    }));
                });
            },
            error: function () {
                alert('Hubo un error al cargar los municipios.');
            }
        });
    }
});

</script>