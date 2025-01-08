<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2; 
use app\models\Conductores;
use app\models\ConductoresSearch;

/** @var yii\web\View $this */
/** @var app\models\ConductoresSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conductores';
$this->params['breadcrumbs'][] = $this->title;

?>   
 <link href="/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	<link href="/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">

<div class="conductores-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button('Crear Conductor', [
            'class' => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#exampleModalCenter',
            'onclick' => 'clearForm()',
        ]) ?>
    </p>
        
    <?php \yii\widgets\Pjax::begin(['id' => 'grid-conductores']); ?>

    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'rowOptions' => function ($model, $index, $widget, $grid) {
        return ['data-id' => $model->id];
    },

    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombres',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'nombres'], 
        ], 
        [
            'attribute' => 'apellido_p',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'apellido_p'], 
        ],
        [
            'attribute' => 'apellido_m',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'apellido_m'], 
        ],
       
        [
            'class' => ActionColumn::className(),
            'template' => '{view} {update} {delete}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::button(' <span class="btn btn-info btn-icon-xxs"><i class="fa fa-eye"></i></span>', [
                        'class' => 'btn btn-info btn-icon-xxs',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#exampleModalCenter',
                        'data-id' => $model->id,
                        'onclick' => 'loadViewForm(' . $model->id . ')',
                    ]);
                },
                'update' => function ($url, $model, $key) {
                    return Html::button(' <span class="btn btn-success btn-icon-xxs"><i class="fa fa-pencil-alt"></i></span>', [
                        'class' => 'btn btn-success btn-icon-xxs',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#exampleModalCenter',
                        'data-id' => $model->id,
                        'onclick' => 'loadUpdateForm(' . $model->id . ')',
                    ]);
                },
        
                'delete' => function ($url, $model, $key) {
                    return Html::button('<span class="btn btn-danger btn-icon-xxs"><i class="fa fa-trash"></i></span>', [
                        'class' => 'btn btn-danger btn-icon-xxs btn-delete',
                        'data-id' => $model->id,
                        'data-nombre' => $model->nombres, // Añadimos el nombre aquí
                        'data-url' => Url::to(['delete', 'id' => $model->id]),
                    ]);
                },
            ],

            'contentOptions' => ['style' => 'width:150px;'], // Establecer el ancho a 150px
            'urlCreator' => function ($action, $model, $key, $index, $column) {
                return Url::toRoute([$action, 'id' => $model->id]);
            },
        ],
    ],
]); ?>
<?php \yii\widgets\Pjax::end(); ?>

</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Crear Conductor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', ['model' => $model]) ?>
        </div>
    </div>
</div>

<script>
    
function clearForm() {
    var form = $('#create-conductores-form');
    form.trigger('reset'); // Limpiar el formulario

    // Asegurarse de habilitar todos los campos del formulario
    form.find('input, select').prop('disabled', false);

    // Cambiar el título del modal a "Crear Conductor"
    $('#exampleModalCenterTitle').text('Crear Conductor');

    // Cambiar la acción del formulario para crear un nuevo conductor
    form.attr('action', '<?= Url::to(['conductores/create']) ?>');

    // Mostrar el botón de guardar
    form.find('button[type="submit"]').show();
    $('button[type="button"].btn.btn-primary').show(); // Mostrar el botón de guardar manualmente
}


// Función para cargar los datos en modo de visualización (ver)
function loadViewForm(id) {
    $.ajax({
        url: '<?= Url::to(['conductores/get-conductor']) ?>',
        type: 'GET',
        data: { id: id },
        success: function (data) {
            $('#exampleModalCenterTitle').text('Ver Conductor'); // Cambiar título a "Ver Conductor"

            var form = $('#create-conductores-form');
            form.trigger('reset'); // Limpiar el formulario antes de cargar los datos

            // Cargar los datos desde initialValues
            var initialValues = data.initialValues;

            // Cargar los datos en el formulario
            form.find('input[name="Conductores[nombres]"]').val(initialValues.nombres).prop('disabled', true);
            form.find('input[name="Conductores[apellido_p]"]').val(initialValues.apellido_p).prop('disabled', true);
            form.find('input[name="Conductores[apellido_m]"]').val(initialValues.apellido_m).prop('disabled', true);
            form.find('input[name="Conductores[fecha_nacimiento]"]').val(initialValues.fecha_nacimiento).prop('disabled', true);
            form.find('input[name="Conductores[no_licencia]"]').val(initialValues.no_licencia).prop('disabled', true);
            form.find('input[name="Conductores[cp]"]').val(initialValues.cp).prop('disabled', true);
            form.find('select[name="Conductores[estado]"]').val(initialValues.estado).prop('disabled', true);
            form.find('input[name="Conductores[colonia]"]').val(initialValues.colonia).prop('disabled', true);
            form.find('input[name="Conductores[calle]"]').val(initialValues.calle).prop('disabled', true);
            form.find('input[name="Conductores[num_ext]"]').val(initialValues.num_ext).prop('disabled', true);
            form.find('input[name="Conductores[num_int]"]').val(initialValues.num_int).prop('disabled', true);
            form.find('input[name="Conductores[telefono]"]').val(initialValues.telefono).prop('disabled', true);
            form.find('input[name="Conductores[email]"]').val(initialValues.email).prop('disabled', true);
            form.find('select[name="Conductores[tipo_sangre]"]').val(initialValues.tipo_sangre).prop('disabled', true);
            form.find('input[name="Conductores[nombres_contacto]"]').val(initialValues.nombres_contacto).prop('disabled', true);
            form.find('input[name="Conductores[apellido_p_contacto]"]').val(initialValues.apellido_p_contacto).prop('disabled', true);
            form.find('input[name="Conductores[apellido_m_contacto]"]').val(initialValues.apellido_m_contacto).prop('disabled', true);
            form.find('select[name="Conductores[parentesco]"]').val(initialValues.parentesco).prop('disabled', true);
            form.find('input[name="Conductores[telefono_contacto]"]').val(initialValues.telefono_contacto).prop('disabled', true);

            // Reemplazar el select de municipio por un input de texto deshabilitado
            var municipioField = form.find('select[name="Conductores[municipio]"]');
            municipioField.replaceWith('<input type="text" class="form-control" value="' + initialValues.municipio + '" disabled>');

            // Cambiar acción del formulario a '#' para evitar el envío de datos
            form.attr('action', '#');

            // Ocultar el botón de guardar en el modo de visualización
            form.find('button[type="submit"]').hide();

            // Ocultar el botón de "Guardar" manualmente
            $('button[type="button"].btn.btn-primary').hide();
        },
        error: function () {
            alert('Hubo un error al cargar los datos del conductor.');
        }
    });
}


function loadUpdateForm(id) {
    $.ajax({
        url: '<?= Url::to(['conductores/get-conductor']) ?>',
        type: 'GET',
        data: { id: id },
        success: function (response) {
            $('#exampleModalCenterTitle').text('Actualizar Conductor');
            var form = $('#create-conductores-form');
            form.trigger('reset'); // Limpiar el formulario antes de cargar los datos

            // Habilitar todos los campos antes de cargar los datos (evitar que se mantengan deshabilitados)
            form.find('input').prop('disabled', false);
            form.find('select').prop('disabled', false);

            // Cargar los valores del modelo
            var model = response.model;

            // Asignar los valores del modelo a los campos del formulario
            form.find('input[name="Conductores[nombres]"]').val(model.nombres);
            form.find('input[name="Conductores[apellido_p]"]').val(model.apellido_p);
            form.find('input[name="Conductores[apellido_m]"]').val(model.apellido_m);
            form.find('input[name="Conductores[fecha_nacimiento]"]').val(model.fecha_nacimiento);
            form.find('input[name="Conductores[no_licencia]"]').val(model.no_licencia);
            form.find('input[name="Conductores[cp]"]').val(model.cp);
            form.find('select[name="Conductores[estado]"]').val(model.estado);
            form.find('select[name="Conductores[municipio]"]').val(model.municipio);
            form.find('input[name="Conductores[colonia]"]').val(model.colonia);
            form.find('input[name="Conductores[calle]"]').val(model.calle);
            form.find('input[name="Conductores[num_ext]"]').val(model.num_ext);
            form.find('input[name="Conductores[num_int]"]').val(model.num_int);
            form.find('input[name="Conductores[telefono]"]').val(model.telefono);
            form.find('input[name="Conductores[email]"]').val(model.email);
            form.find('select[name="Conductores[tipo_sangre]"]').val(model.tipo_sangre);
            form.find('input[name="Conductores[nombres_contacto]"]').val(model.nombres_contacto);
            form.find('input[name="Conductores[apellido_p_contacto]"]').val(model.apellido_p_contacto);
            form.find('input[name="Conductores[apellido_m_contacto]"]').val(model.apellido_m_contacto);
            form.find('select[name="Conductores[parentesco]"]').val(model.parentesco);
            form.find('input[name="Conductores[telefono_contacto]"]').val(model.telefono_contacto);

            // Establecer los valores iniciales en el formulario para futuras comparaciones
            form.data('initial-values', response.initialValues);

            // Acción del formulario
            form.attr('action', '<?= Url::to(['conductores/update']) ?>' + '?id=' + id);

            // Mostrar el formulario
            form.find('button[type="submit"]').show();

            // Asegurarse de mostrar el botón de "Guardar"
            $('button[type="button"].btn.btn-primary').show(); // Mostrar el botón de guardar

            // Obtener el valor del estado seleccionado en el formulario
            var estadoInicial = model.estado // Obtenemos el estado desde el formulario
            var municipioInicial = model.municipio;

            // Cargar los estados
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['conductores/get-estados']) ?>',
                method: 'GET',
                success: function (data) {
                    var $estadoDropdown = $('#estado-dropdown');
                    $estadoDropdown.empty();

                    // Agregar la opción por defecto
                    $estadoDropdown.append($('<option>', {
                        value: '',
                        text: ''
                    }));

                    var estados = {};
                    var cvegeoEstadoSeleccionado = null;

                    // Llenar el dropdown de estados
                    $.each(data.datos, function (index, estado) {
                        estados[estado.cvegeo] = estado.nomgeo;

                        $estadoDropdown.append($('<option>', {
                            value: estado.nomgeo,
                            'data-cvegeo': estado.cvegeo,
                            text: estado.nomgeo
                        }));

                        // Verificar si el estado inicial coincide con el valor actual
                        if (estado.nomgeo === estadoInicial) {
                            cvegeoEstadoSeleccionado = estado.cvegeo;
                        }
                    });

                    // Si encontramos el estado inicial, lo seleccionamos
                    if (cvegeoEstadoSeleccionado) {
                        $estadoDropdown.val(estadoInicial); // Seleccionar el estado inicial
                        cargarMunicipios(cvegeoEstadoSeleccionado, estadoInicial, municipioInicial); // Cargar municipios para el estado
                    }
                },
                error: function () {
                    alert('Hubo un error al cargar los estados.');
                }
            });
        },
        error: function () {
            alert('Hubo un error al cargar los datos del conductor.');
        }
    });
}


    // Comparar valores actuales con los iniciales
function hasFormChanged(form) {
    var initialValues = form.data('initial-values');
    var hasChanged = false;

    // Recorre cada valor inicial y lo compara con el valor actual
    $.each(initialValues, function (key, value) {
        var currentValue = form.find(`[name="Conductores[${key}]"]`).val();
        if (currentValue !== value) {
            hasChanged = true;
            return false; // Termina el bucle si se detecta un cambio
        }
    });

    return hasChanged;
}

// Manejador de envío del formulario
$('#create-conductores-form').on('submit', function (e) {
    var form = $(this);

    if (!hasFormChanged(form)) {
        // Si el formulario no ha cambiado, cierra el modal y cancela el envío
        e.preventDefault();
        $('#exampleModalCenter').modal('hide');
    }
});
function submitForm() {
    var form = $('#create-conductores-form');
    var action = $('#exampleModalCenter').data('action'); // Obtén la acción del modal
    var initialValues = form.data('initial-values') || {};

    console.log('Form Action:', action);
    console.log('Initial Values:', initialValues);

    var hasChanges = false;
    var nombreConductor = form.find('input[name="Conductores[nombres]"]').val();

    // Recorremos los campos del formulario y los comparamos con los valores iniciales
    form.find('input, select').each(function () {
        var name = $(this).attr('name');

        // Ignorar el campo _csrf
        if (name === '_csrf') {
            return true; // Continúa con el siguiente campo
        }

        // Limpiar el nombre para comparación
        name = name.replace('Conductores[', '').replace(']', '');

        var currentValue = $(this).val().trim();  // Eliminar espacios en blanco
        var initialValue = initialValues[name];

        // Asegurarse de que `initialValue` sea una cadena
        initialValue = (initialValue !== undefined && initialValue !== null) ? String(initialValue).trim() : '';

        console.log('Checking field:', name);
        console.log('Initial value:', initialValue);
        console.log('Current value:', currentValue);

        // Si ambos valores son vacíos, no consideramos que haya cambio
        if (currentValue === '' && initialValue === '') {
            return true; // Continúa con el siguiente campo
        }

        // Comparar como enteros si el campo es numérico
        if (!isNaN(currentValue) && !isNaN(initialValue)) {
            currentValue = parseInt(currentValue, 10);
            initialValue = parseInt(initialValue, 10);
        }

        // Comparación final
        if (currentValue !== initialValue) {
            console.log('Change detected in field:', name);
            hasChanges = true;
            return false; // Salimos del bucle si detectamos un cambio
        }
    });

    if (hasChanges || action === 'create') {
        console.log('Changes detected or creating new conductor, submitting form...');

        // Primero, simula el clic en el botón de cierre del modal
        $('#exampleModalCenter .btn-danger').click();  // Cierra el modal

        // Enviar los datos con AJAX sin recargar la página
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                console.log('AJAX Success Response:', response);
                var data = response;
                if (data.success) {
                    if (action === 'update') {
                        console.log("Modelo actualizado:", data.model); // Asegúrate de que el modelo contiene un ID
                        updateTableRow(data.model);

                        // Mostrar SweetAlert de éxito
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Datos del conductor actualizados correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Cerrar'
                        }).then(function () {
                            // Este código se ejecutará cuando el SweetAlert se cierre
                            var row = $('tr[data-id="' + data.model.id + '"]');

                            if (row.length > 0) {
                                // Agregar la clase de parpadeo
                                row.addClass('blink-border');

                                // Eliminar la clase de parpadeo después de 2 segundos
                                setTimeout(function() {
                                    row.removeClass('blink-border');
                                }, 3000);
                            }
                        });
                    } else {
                        // Crear nueva fila en la tabla
                        var newRow = createTableRow(data.model);  // Crea la fila para el nuevo conductor
                        $('#conductor-table tbody').append(newRow);  // Añadir la fila debajo

                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Conductor creado exitosamente.',
                            icon: 'success',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                } else {
                    // Mostrar un error si la actualización o creación falla
                    console.log('AJAX Error Response:', data);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('AJAX Error:', textStatus, errorThrown);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un error al procesar la solicitud.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        });
    } else {
        console.log('No changes detected, closing modal and showing SweetAlert...');

        // Primero, simula el clic en el botón de cierre del modal
        $('#exampleModalCenter .btn-danger').click();  // Cierra el modal

        // Luego, muestra el SweetAlert con el nombre del conductor
        Swal.fire({
            title: '¡No se actualizó!',
            text: 'No se realizaron cambios en los datos de ' + nombreConductor + '.',
            icon: 'info',
            showCancelButton: false,
            confirmButtonText: 'Cerrar',
            timer: 3000, // Tiempo en milisegundos antes de cerrar automáticamente
            timerProgressBar: true
        });
    }
}
// Asigna la acción al modal cuando se abre
$('#exampleModalCenter').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que abrió el modal
    var action = button.data('action'); // Extrae la información de acción
    console.log('Modal action:', action);
    $(this).data('action', action); // Almacena la acción en el modal
});
// Función para crear una nueva fila en la tabla
function createTableRow(conductor) {
    return `<tr data-id="${conductor.id}">
                <td>${conductor.nombres}</td>
                <td>${conductor.apellido_p}</td>
                <td>${conductor.apellido_m}</td>
                <td>
                    <button class="btn btn-info">Ver</button>
                    <button class="btn btn-primary">Editar</button>
                    <button class="btn btn-danger">Eliminar</button>
                </td>
            </tr>`;
}



function updateTableRow(model) {
    // Buscar la fila con el atributo 'data-id' igual al 'id' del modelo
    var row = $('tr[data-id="' + model.id + '"]');

    if (row.length > 0) {
        console.log('Fila encontrada con id:', model.id);

        // Actualizar los campos en la fila con los nuevos datos
        row.find('.nombres').text(model.nombres);
        row.find('.apellido_p').text(model.apellido_p);
        row.find('.apellido_m').text(model.apellido_m);
        // Asegúrate de agregar otros campos que quieras actualizar

    } else {
        console.log('Fila no encontrada con id:', model.id);
    }
}

    // Función para cargar los municipios
    function cargarMunicipios(cvegeoEstado, nombreEstado, municipioInicial) {
        $.ajax({
            url: 'https://gaia.inegi.org.mx/wscatgeo/v2/mgem/' + cvegeoEstado, // Usamos el cvegeo del estado
            method: 'GET',
            success: function (data) {
                var $municipioDropdown = $('#municipio-dropdown');
                $municipioDropdown.empty();
                $municipioDropdown.append($('<option>', {
                    value: '',
                    text: 'Selecciona el municipio...'
                }));

                var municipios = {};

                // Llenar el dropdown de municipios
                $.each(data.datos, function (index, municipio) {
                    municipios[municipio.cvegeo] = municipio.nomgeo;

                    $municipioDropdown.append($('<option>', {
                        value: municipio.nomgeo, // Guardamos el nombre del municipio como value
                        'data-cvegeo': municipio.cvegeo, // Guardamos el cvegeo como un atributo data-cvegeo
                        text: municipio.nomgeo
                    }));

                    // Comparar el municipio inicial con los municipios de la API
                    if (municipio.nomgeo === municipioInicial) {
                        $municipioDropdown.val(municipio.nomgeo); // Seleccionar el municipio inicial
                    }
                });
            },
            error: function () {
                alert('Hubo un error al cargar los municipios.');
            }
        });
    }

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre'); // Capturar el nombre
                const url = this.getAttribute('data-url');

                console.log('ID a eliminar:', id);
                console.log('Nombre:', nombre); // Mostrar el nombre en consola
                console.log('URL de eliminación:', url);

                Swal.fire({
                    title: `¿Estás seguro de eliminar a ${nombre}?`, 
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Confirmación aceptada.');

                        // Enviar solicitud AJAX para eliminar
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>' // Token CSRF
                            }
                        })
                        .then(response => {
                            console.log('Estado de la respuesta:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Respuesta del servidor:', data);
                            if (data.success) {
                                Swal.fire('¡Eliminado!', `El conductor "${nombre}" ha sido eliminado.`, 'success');

                                // Eliminar la fila manualmente
                                const row = document.querySelector(`tr[data-key="${id}"]`);
                                if (row) {
                                    row.remove();
                                    console.log(`Fila con ID ${id} eliminada.`);
                                } else {
                                    console.warn('No se encontró la fila a eliminar.');
                                }
                            } else {
                                Swal.fire('Error', data.message || 'Ocurrió un error inesperado.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud AJAX:', error);
                            Swal.fire('Error', 'No se pudo completar la acción.', 'error');
                        });
                    } else {
                        console.log('Eliminación cancelada por el usuario.');
                    }
                });
            });
        });
    });
</script>



<script src="/vendor/sweetalert2/sweetalert2.min.js"></script>
<script src="/js/plugins-init/sweetalert.init.js"></script>
<style>
@keyframes blink {
    0% {
        border: 2px solid transparent;
    }
    50% {
        border: 2px solid #3A9B94;
    }
    100% {
        border: 2px solid transparent;
    }
}

.blink-border {
    animation: blink 1.5s ease-in-out 0s 2; /* Parpadeo dos veces en 3 segundos */
    border-width: 2px;
    border-style: solid;
}


</style>