$(document).on('click', '.ajax-delete-usuario', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var url = $(this).data('url');
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás deshacer esta acción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
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
                    Swal.close();
                    if (response.success) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        $.pjax.reload({ container: '#usuario-grid' });
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                }
            });
        }
    });
});

$(document).on('submit', '#create-usuario-form', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();
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
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                });
                $('#usuarioModal').modal('hide');
                $.pjax.reload({ container: '#usuario-grid' });
            } else if (response.html) {
                $('#usuarioModalBody').html(response.html);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo actualizar el usuario.',
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar el usuario.',
            });
        }
    });
    return false;
});

$(document).on('click', '.ajax-update-usuario', function (e) {
    e.preventDefault();
    var url = $(this).data('url');
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
            Swal.close();
            if (response.success) {
                $('#usuarioModalBody').html(response.html);
                $('#usuarioModalTitle').text('Actualizar Usuario');
                $('#usuarioModal').modal('show');
            }
        },
        error: function () {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del usuario.',
            });
        }
    });
});

$(document).on('click', '.ajax-view-usuario', function (e) {
    e.preventDefault();
    var url = $(this).data('url');
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
            Swal.close();
            if (response.success) {
                $('#usuarioModalBody').html(response.html);
                $('#usuarioModalTitle').text('Ver Usuario');
                $('#usuarioModal').modal('show');
            }
        },
        error: function () {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del usuario.',
            });
        }
    });
});

$('#usuarioModal').on('hidden.bs.modal', function () {
    $('#usuarioModalBody').html('');
    $('#usuarioModalTitle').text('Crear Usuario');
}); 