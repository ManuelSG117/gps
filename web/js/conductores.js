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
$(document).on('submit', '#create-conductores-form', function (e) {
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
            Swal.close(); // Cerrar el modal de carga
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
                    $.pjax.reload({ container: '#conductores-grid' });
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
                
                // Ocultar el botón de guardar
                $('#btn-guardar').hide();
                
                // Asegurarse de que los botones de navegación estén visibles
                $('.next-step, .prev-step').show();
                
                // Mostrar el primer paso
                window.showStep(1);
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
    $('#create-conductores-form').attr('action', $('#create-conductores-form').data('create-url'));
    $('#btn-guardar').show();  // Mostrar el botón "Guardar"
    $('#exampleModalCenterTitle').text('Crear Conductor');
    
    // Restablecer los pasos
    window.showStep(1);
    
    // Eliminar cualquier método oculto que se haya agregado
    $('#create-conductores-form').find('input[name="_method"]').remove();
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
                
                // Asegurarse de que el método sea POST para la actualización
                if ($('#create-conductores-form').find('input[name="_method"]').length) {
                    $('#create-conductores-form').find('input[name="_method"]').val('POST');
                } else {
                    $('#create-conductores-form').append('<input type="hidden" name="_method" value="POST">');
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


// Handle modal close with confirmation
$(document).on('click', '#btn-cancelar, button[data-bs-dismiss="modal"]', function(e) {
    e.preventDefault();
    
    // Check if we're in view mode (fields are disabled)
    var isViewMode = $('#create-conductores-form').find('input:first').prop('disabled');
    
    // If in view mode, just close the modal without confirmation
    if (isViewMode) {
        $('#exampleModalCenter').modal('hide');
        return;
    }
    
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
                $('#exampleModalCenter').modal('hide');
            }
        });
    } else {
        $('#exampleModalCenter').modal('hide');
    }
});

$(document).ready(function() {
    // Foto preview handler
    $('#conductor-foto').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#foto-preview').show().find('img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        } else {
            $('#foto-preview').hide();
        }
    });
});
