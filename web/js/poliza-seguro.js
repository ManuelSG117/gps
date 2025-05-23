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
    var formData = new FormData(form[0]);
    
    // No es necesario agregar manualmente las imágenes al FormData
    // ya que el input tiene el nombre correcto 'poliza_images[]'
    // y FormData las captura automáticamente

    // Mostrar el modal de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera mientras se suben las imágenes.',
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

// Cuando se oculta el modal, resetear el formulario
$('#polizaModal').on('hidden.bs.modal', function () {
    $('#create-poliza-form')[0].reset();
    $('#create-poliza-form').attr('action', baseUrl + '/poliza-seguro/create');
    $('#polizaModalTitle').text('Crear Póliza de Seguro');
    $('#create-poliza-form').find('input, select, textarea').prop('disabled', false);
    
    // Mostrar el botón de guardar en el footer
    $('#btn-save-poliza-footer').show();
    
    // Limpiar las previsualizaciones de imágenes
    $('.image-preview-container').empty();
    
    // Limpiar el input de archivos
    $('#imagen-poliza').val('');
    
    // Mostrar los controles de carga de archivos
    $('.upload-controls').show();
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
                
                // Ocultar el control de carga de archivos y mostrar las imágenes
                $('.upload-controls').hide();
                
                // Limpiar el contenedor de previsualizaciones
                var previewContainer = $('.image-preview-container');
                previewContainer.empty();
                
                // Mostrar las imágenes si existen
                if (response.images && response.images.length > 0) {
                    response.images.forEach(function(imageSrc) {
                        var previewDiv = document.createElement('div');
                        previewDiv.className = 'image-preview';
                        previewDiv.innerHTML = `<img src="${imageSrc}" alt="Imagen de póliza">`;
                        previewContainer.append(previewDiv);
                    });
                } else {
                    previewContainer.html('<p class="text-muted">No hay imágenes disponibles para esta póliza.</p>');
                }

                // Cambiar el título del modal y mostrarlo
                $('#polizaModalTitle').text('Ver Póliza de Seguro');
                $('#polizaModal').modal('show');
                
                // Ocultar el botón de guardar en el footer
                $('#btn-save-poliza-footer').hide();
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

function handleImageUpload() {
    const input = document.getElementById('imagen-poliza');
    const previewContainer = document.querySelector('.image-preview-container');
    let files = [];
    if (!input) return;
    
    // Función para actualizar el input de archivos con los archivos actuales
    function updateFileInput() {
        // Crear un nuevo DataTransfer para manipular los archivos
        const dataTransfer = new DataTransfer();
        
        // Agregar cada archivo al DataTransfer
        files.forEach(file => {
            dataTransfer.items.add(file);
        });
        
        // Asignar los archivos al input
        input.files = dataTransfer.files;
    }
    
    input.addEventListener('change', function(e) {
        // Limpiar previsualizaciones existentes
        previewContainer.innerHTML = '';
        files = [];
        
        const newFiles = Array.from(e.target.files);
        
        // Check file limit (maximum 2 images for poliza)
        if (newFiles.length > 2) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: 'Solo puede subir un máximo de 2 imágenes para la póliza' 
            });
            return;
        }
        
        newFiles.forEach(file => {
            if (!file.type.startsWith('image/')) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: 'Por favor, seleccione solo archivos de imagen' 
                });
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'image-preview';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image"><i class="fas fa-times"></i></button>
                `;
                previewContainer.appendChild(previewDiv);
                
                previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                    previewDiv.remove();
                    files = files.filter(f => f !== file);
                    updateFileInput(); // Actualizar el input de archivos
                });
            };
            reader.readAsDataURL(file);
            files.push(file);
        });
    });
    
    $('#polizaModal').on('hidden.bs.modal', function() {
        previewContainer.innerHTML = '';
        files = [];
        input.value = ''; // Limpiar el input de archivos
    });
}

// Handle save button click in modal footer
$(document).on('click', '#btn-save-poliza-footer', function() {
    // Submit the form when the footer save button is clicked
    var form = $('#create-poliza-form');
    var formData = new FormData(form[0]);
    
    // No es necesario agregar manualmente las imágenes al FormData
    // ya que el input tiene el nombre correcto 'poliza_images[]'
    // y FormData las captura automáticamente
    
    // Show loading indicator
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espera mientras se guarda la información.',
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
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message
                }).then((result) => {
                    // Close modal and refresh grid
                    $('#polizaModal').modal('hide');
                    $.pjax.reload({container: '#poliza-grid'});
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar la póliza'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al guardar la póliza'
            });
        }
    });
});

// Initialize image upload handling when document is ready
$(document).ready(function() {
    handleImageUpload();
});
