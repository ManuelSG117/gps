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
                        $.pjax.reload({ container: '#dispositivos-grid' });
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
                $('#create-dispositivos-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Dispositivoes[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Dispositivoes[', '').replace(']', '')]);
                    }
                });

                // Deshabilitar los campos
                $('#create-dispositivos-form').find('input, select, textarea').prop('disabled', true);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Ver Dispositivo');
                $('#exampleModalCenter').modal('show');
                $('#create-dispositivos-form').find('.btn-primary').hide();
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
    $('#create-dispositivos-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-dispositivos-form').attr('action', $('#create-dispositivos-form').data('create-url'));
    $('#btn-guardar').show();  // Mostrar el botón "Guardar"
    $('#exampleModalCenterTitle').text('Crear Dispositivo');
    
    // Restablecer los pasos
    window.showStep(1);
    
    // Eliminar cualquier método oculto que se haya agregado
    $('#create-dispositivos-form').find('input[name="_method"]').remove();
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
                $('#create-dispositivos-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Dispositivos[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Dispositivos[', '').replace(']', '')]);
                    }
                });

                // Habilitar los campos para editar
                $('#create-dispositivos-form').find('input, select, textarea').prop('disabled', false);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Actualizar Conductor');
                $('#exampleModalCenter').modal('show');

                // Cambiar la acción del formulario para actualizar
                $('#create-dispositivos-form').attr('action', url);
                
                // Asegurarse de que el método sea POST para la actualización
                if ($('#create-dispositivos-form').find('input[name="_method"]').length) {
                    $('#create-dispositivos-form').find('input[name="_method"]').val('POST');
                } else {
                    $('#create-dispositivos-form').append('<input type="hidden" name="_method" value="POST">');
                }
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

// Handler para crear/actualizar dispositivos con AJAX y PJAX
$(document).on('submit', '#create-dispositivos-form', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(form[0]);

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
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            Swal.close();
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                }).then((result) => {
                    // Cerrar el modal después de que el usuario haga clic en OK
                    $('#exampleModalCenter').modal('hide');
                    // Asegurarse de que el backdrop también se elimine
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                    // Recargar el GridView
                    $.pjax.reload({ container: '#dispositivos-grid' });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo procesar la solicitud.',
                });
            }
        },
        error: function (xhr) {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo procesar la solicitud. Por favor, intenta de nuevo.',
            });
            console.error('Error en la solicitud AJAX:', xhr.responseText);
        }
    });

    return false;
});
