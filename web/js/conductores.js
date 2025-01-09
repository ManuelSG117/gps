$(document).on('click', '.ajax-delete', function (e) {
    e.preventDefault();

    var id = $(this).data('id');
    var url = $(this).data('url');

    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás deshacer esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar el modal de carga
            Swal.fire({
                title: 'Cargando...',
                text: 'Por favor espera.',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            $.ajax({
                url: url,
                type: 'POST',
                success: function (response) {
                    Swal.close(); // Cerrar el modal de carga

                    if (response.success) {
                        Swal.fire(
                            '¡Eliminado!',
                            response.message,
                            'success'
                        );
                        $.pjax.reload({ container: '#conductores-grid' });
                    }
                },
                error: function () {
                    Swal.close(); // Cerrar el modal de carga
                    Swal.fire(
                        'Error',
                        'No se pudo eliminar el registro.',
                        'error'
                    );
                }
            });
        }
    });
});


// Maneja la creación de conductores con AJAX
$('#create-conductores-form').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);

     // Mostrar el modal de carga
     Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                });
                $('#exampleModalCenter .btn-danger').click();  // Cierra el modal
                $.pjax.reload({ container: '#conductores-grid' }); // Recarga el GridView
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo crear el conductor.',
            });
        }
    });

    return false;
});

$(document).on('click', '.ajax-view', function (e) {
    e.preventDefault();

    var url = $(this).data('url');

    // Mostrar el modal de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            Swal.close(); // Cerrar el modal de carga

            if (response.success) {
                // Cargar los datos en el formulario
                var data = response.data;
                $('#create-conductores-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Conductores[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Conductores[', '').replace(']', '')]);
                    }
                });

                // Deshabilitar los campos
                $('#create-conductores-form').find('input, select, textarea').prop('disabled', true);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Ver Conductor');
                $('#exampleModalCenter').modal('show');
                $('#create-conductores-form').find('.btn-primary').hide();
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar los datos del conductor.',
            });
        }
    });
});


// Restablecer el formulario al cerrar el modal
$('#exampleModalCenter').on('hidden.bs.modal', function () {
    $('#create-conductores-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-conductores-form .btn-primary').show();  // Mostrar el botón "Guardar"
    $('#exampleModalCenterTitle').text('Crear Conductor');
});

$(document).on('click', '.ajax-update', function (e) {
    e.preventDefault();

    var url = $(this).data('url');

    // Mostrar el modal de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            Swal.close(); // Cerrar el modal de carga

            if (response.success) {
                // Cargar los datos en el formulario
                var data = response.data;
                $('#create-conductores-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Conductores[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Conductores[', '').replace(']', '')]);
                    }
                });

                // Habilitar los campos para editar
                $('#create-conductores-form').find('input, select, textarea').prop('disabled', false);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Actualizar Conductor');
                $('#exampleModalCenter').modal('show');

                // Cambiar la acción del formulario para actualizar
                $('#create-conductores-form').attr('action', url);
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del conductor.',
            });
        }
    });
});
