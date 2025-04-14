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
                        $.pjax.reload({ container: '#poliza-grid' });
                    }
                },
                error: function () {
                    Swal.close(); // Cerrar el modal de carga
                    Swal.fire(
                        'Error',
                        'No se pudo eliminar la póliza.',
                        'error'
                    );
                }
            });
        }
    });
});

// Maneja la creación de pólizas con AJAX
// Update the modal close action in the success handler
$('#create-poliza-form').on('beforeSubmit', function (e) {
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
                // Update this line to use Bootstrap 5 syntax
                $('#polizaModal').modal('hide');
                $.pjax.reload({ container: '#poliza-grid' }); // Recarga el GridView
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo crear la póliza de seguro.',
            });
        }
    });

    return false;
});

// Update the modal reset function
$('#polizaModal').on('hidden.bs.modal', function () {
    $('#create-poliza-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-poliza-form .btn-success').show();  // Mostrar el botón "Guardar"
    $('#polizaModalTitle').text('Crear Póliza de Seguro');
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
                $('#create-poliza-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('PolizaSeguro[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('PolizaSeguro[', '').replace(']', '')]);
                    }
                });

                // Deshabilitar los campos
                $('#create-poliza-form').find('input, select, textarea').prop('disabled', true);

                // Cambiar el título del modal y mostrarlo
                $('#polizaModalTitle').text('Ver Póliza de Seguro');
                $('#polizaModal').modal('show');
                $('#create-poliza-form').find('.btn-success').hide();
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar los datos de la póliza.',
            });
        }
    });
});

// Restablecer el formulario al cerrar el modal
$('#polizaModal').on('hidden.bs.modal', function () {
    $('#create-poliza-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-poliza-form .btn-success').show();  // Mostrar el botón "Guardar"
    $('#polizaModalTitle').text('Crear Póliza de Seguro');
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
                $('#create-poliza-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('PolizaSeguro[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('PolizaSeguro[', '').replace(']', '')]);
                    }
                });

                // Habilitar los campos para editar
                $('#create-poliza-form').find('input, select, textarea').prop('disabled', false);

                // Cambiar el título del modal y mostrarlo
                $('#polizaModalTitle').text('Actualizar Póliza de Seguro');
                $('#polizaModal').modal('show');

                // Cambiar la acción del formulario para actualizar
                $('#create-poliza-form').attr('action', url);
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos de la póliza.',
            });
        }
    });});

// Handle save button click in modal footer
$(document).on('click', '#btn-save-poliza-footer', function() {
    // Submit the form when the footer save button is clicked
    $('#create-poliza-form').submit();
});
