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
            mostrarCargando('Cargando...', 'Por favor espera.');

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
                        ).then((result) => {
                            $.pjax.reload({ container: '#poliza-grid' });
                        });
                    } else {
                        mostrarError('Error', response.message || 'No se pudo eliminar la póliza.');
                    }
                },
                error: function () {
                    Swal.close(); // Cerrar el modal de carga
                    mostrarError('Error', 'No se pudo eliminar la póliza.');
                }
            });
        }
    });
});

// Función para mostrar el modal de cambio de estado
function mostrarModalCambioEstado(id) {
    // Limpiar el formulario
    $('#form-cambio-estado')[0].reset();
    $('#poliza-id').val(id);
    
    // Ocultar el historial inicialmente
    $('#historial-estados-container').addClass('d-none');
    $('#historial-estados-lista').empty();
    
    // Cargar el historial de estados
    $.ajax({
        url: '/poliza-seguro/view',
        type: 'GET',
        data: { id: id, format: 'json' },
        success: function(response) {
            if (response.historial && response.historial.length > 0) {
                mostrarHistorialEstados(response.historial);
            }
        }
    });
    
    // Mostrar el modal
    $('#cambioEstadoModal').modal('show');
}

// Función para cambiar el estado de la póliza
function cambiarEstadoPoliza() {
    // Obtener los datos del formulario
    const id = $('#poliza-id').val();
    const estado = $('#nuevo-estado').val();
    const motivo = $('#motivo').val();
    const comentario = $('#comentario').val();
    
    // Validar campos requeridos
    if (!estado) {
        Swal.fire('Error', 'Por favor seleccione un estado', 'error');
        return;
    }
    
    if (!motivo) {
        Swal.fire('Error', 'Por favor seleccione un motivo', 'error');
        return;
    }
    
    // Mostrar indicador de carga
    mostrarCargando('Actualizando estado...', 'Por favor espera.');
    
    // Obtener el formulario completo para incluir las imágenes
    const formData = new FormData(document.getElementById('form-cambio-estado'));
    
    $.ajax({
        url: '/poliza-seguro/cambiar-estado?id=' + id,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Cerrar el modal de cambio de estado
                $('#cambioEstadoModal').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Recargar la tabla de pólizas
                    $.pjax.reload({container: '#poliza-grid'});
                    
                    // Si estamos viendo la póliza, actualizar la información
                    if ($('#polizaModal').hasClass('show')) {
                        // Actualizar el estado en el formulario si existe
                        if ($('#polizaseguro-estado').length) {
                            $('#polizaseguro-estado').val(response.estado_actual);
                        }
                        
                        // Actualizar el historial si está disponible
                        if (response.historial && response.historial.length > 0) {
                            mostrarHistorialEstados(response.historial);
                        }
                    }
                });
            } else {
                mostrarError('Error', response.message);
            }
        },
        error: function() {
            Swal.close();
            mostrarError('Error', 'Error de conexión al actualizar el estado');
        }
    });
}

// Función para mostrar el historial de estados
function mostrarHistorialEstados(historial, contenedor = '#historial-estados-lista') {
    if (!historial || historial.length === 0) return;
    
    // Mostrar el contenedor del historial si es el contenedor por defecto
    if (contenedor === '#historial-estados-lista') {
        $('#historial-estados-container').removeClass('d-none');
    }
    
    // Obtener la lista donde se mostrará el historial
    const timelineContainer = $(contenedor);
    timelineContainer.empty();
    
    // Crear el timeline
    const timeline = $('<div class="widget-timeline"></div>');
    const timelineList = $('<ul class="timeline"></ul>');
    
    // Agregar cada elemento del historial
    historial.forEach((item) => {
        const fecha = new Date(item.fecha_cambio);
        const fechaFormateada = fecha.toLocaleString('es-ES');
        
        // Generar HTML para las imágenes si existen
        const imagenesHTML = generarHTMLImagenes(item.imagenes);
        
        const timelineItem = $(`
            <li>
                <div class="timeline-badge ${item.clase_estado}"></div>
                <div class="timeline-panel">
                    <div class="media">
                        <div class="media-body">
                            <h6 class="mb-1">${item.estado_nuevo_nombre}</h6>
                            <small class="d-block">${fechaFormateada}</small>
                            ${item.comentario ? `<p class="mb-0 mt-2">${item.comentario}</p>` : ''}
                            ${item.motivo ? `<div class="mt-2"><strong>Motivo:</strong> ${item.motivo}</div>` : ''}
                            ${item.estado_anterior ? `<small class="text-muted">Cambio desde: ${item.estado_anterior_nombre}</small>` : ''}
                            ${imagenesHTML}
                        </div>
                    </div>
                </div>
            </li>
        `);
        
        timelineList.append(timelineItem);
    });
    
    timeline.append(timelineList);
    timelineContainer.append(timeline);
    
    // Si no es el contenedor por defecto y está vacío, mostrar mensaje
    if (contenedor !== '#historial-estados-lista' && historial.length === 0) {
        $(contenedor).html('<p class="text-muted text-center py-3">No hay historial de estados para esta póliza.</p>');
    }
}

// Función para generar HTML de imágenes
function generarHTMLImagenes(imagenes) {
    if (!imagenes || imagenes.length === 0) return '';
    
    let imagenesHTML = '<div class="timeline-images mt-2">';
    imagenes.forEach(function(imageSrc) {
        imagenesHTML += `<div class="timeline-image-item mb-2">
            <a href="${imageSrc}" target="_blank">
                <img src="${imageSrc}" alt="Imagen de cambio de estado" class="img-fluid" style="max-height: 150px; border-radius: 5px;">
            </a>
        </div>`;
    });
    imagenesHTML += '</div>';
    
    return imagenesHTML;
}

// Función para cargar datos en el formulario
function cargarDatosEnFormulario(data, formulario = '#create-poliza-form') {
    $(formulario).find('input, select, textarea').each(function () {
        var name = $(this).attr('name');
        if (name && data[name.replace('PolizaSeguro[', '').replace(']', '')] !== undefined) {
            $(this).val(data[name.replace('PolizaSeguro[', '').replace(']', '')]);
        }
    });
}

// Función para mostrar imágenes en el contenedor de previsualización
function mostrarImagenesPoliza(images, previewContainer = '.image-preview-container') {
    const container = $(previewContainer);
    container.empty();
    
    if (images && images.length > 0) {
        images.forEach(function(imageSrc) {
            var previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview';
            previewDiv.innerHTML = `<img src="${imageSrc}" alt="Imagen de póliza">`;
            container.append(previewDiv);
        });
    } else {
        container.html('<p class="text-muted">No hay imágenes disponibles para esta póliza.</p>');
    }
}

// Función para mostrar modal de carga
function mostrarCargando(titulo = 'Cargando...', texto = 'Por favor espera.') {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Función para mostrar error
function mostrarError(titulo = 'Error', mensaje = 'Ha ocurrido un error.') {
    Swal.fire({
        icon: 'error',
        title: titulo,
        text: mensaje
    });
}

// Maneja la creación de pólizas con AJAX
$('#create-poliza-form').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(form[0]);
    
    // Mostrar el modal de carga
    mostrarCargando('Cargando...', 'Por favor espera mientras se suben las imágenes.');

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
                }).then((result) => {
                    // Close modal and refresh grid
                    $('#polizaModal').modal('hide');
                    $.pjax.reload({container: '#poliza-grid'});
                });
            } else {
                mostrarError('Error', response.message || 'No se pudo guardar la póliza.');
            }
        },
        error: function () {
            Swal.close();
            mostrarError('Error', 'No se pudo guardar la póliza.');
        }
    });
    
    return false;
});

// Función para ver una póliza
function verPoliza(url) {
    // Mostrar el modal de carga
    mostrarCargando();

    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            Swal.close(); // Cerrar el modal de carga

            if (response.success) {
                // Cargar los datos en el formulario
                cargarDatosEnFormulario(response.data);

                // Deshabilitar los campos
                $('#create-poliza-form').find('input, select, textarea').prop('disabled', true);
                
                // Ocultar el control de carga de archivos y mostrar las imágenes
                $('.upload-controls').hide();
                
                // Mostrar las imágenes si existen
                mostrarImagenesPoliza(response.images);

                // Mostrar el historial si existe
                if (response.historial && response.historial.length > 0) {
                    // Usar el contenedor de historial en la pestaña correspondiente
                    mostrarHistorialEstados(response.historial, '#historial-content .timeline-container');
                } else {
                    $('#historial-content .timeline-container').html('<p class="text-muted text-center py-3">No hay historial de estados para esta póliza.</p>');
                }

                // Cambiar el título del modal y mostrarlo
                $('#polizaModalTitle').text('Ver Póliza de Seguro');
                
                // Activar la pestaña de datos por defecto
                $('#polizaModalTabs button[data-bs-target="#datos-content"]').tab('show');
                
                $('#polizaModal').modal('show');
                
                // Ocultar el botón de guardar en el footer
                $('#btn-save-poliza-footer').hide();
            } else {
                mostrarError('Error', response.message || 'No se pudo cargar la información de la póliza.');
            }
        },
        error: function () {
            Swal.close();
            mostrarError('Error', 'Ocurrió un error al cargar la información de la póliza.');
        }
    });
}

// Evento para ver una póliza
$(document).on('click', '.ajax-view', function (e) {
    e.preventDefault();
    var url = $(this).data('url');
    verPoliza(url);
});

// Evento para actualizar una póliza
$(document).on('click', '.ajax-update', function (e) {
    e.preventDefault();
    var url = $(this).data('url');

    // Mostrar el modal de carga
    mostrarCargando();

    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            Swal.close(); // Cerrar el modal de carga

            if (response.success) {
                // Cargar los datos en el formulario
                cargarDatosEnFormulario(response.data);

                // Habilitar los campos para editar
                $('#create-poliza-form').find('input, select, textarea').prop('disabled', false);

                // Cambiar el título del modal y mostrarlo
                $('#polizaModalTitle').text('Actualizar Póliza de Seguro');
                $('#polizaModal').modal('show');

                // Cambiar la acción del formulario para actualizar
                $('#create-poliza-form').attr('action', url);
                
                // Mostrar el botón de guardar
                $('#btn-save-poliza-footer').show();
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            mostrarError('Error', 'No se pudieron cargar los datos de la póliza.');
        }
    });
});

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
            mostrarError('Error', 'Solo puede subir un máximo de 2 imágenes para la póliza');
            return;
        }
        
        newFiles.forEach(file => {
            if (!file.type.startsWith('image/')) {
                mostrarError('Error', 'Por favor, seleccione solo archivos de imagen');
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
    
    // Show loading indicator
    mostrarCargando('Guardando...', 'Por favor espera mientras se guarda la información.');
    
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
                mostrarError('Error', response.message || 'Error al guardar la póliza');
            }
        },
        error: function() {
            Swal.close();
            mostrarError('Error', 'Error de conexión al guardar la póliza');
        }
    });
});

// Initialize image upload handling when document is ready
$(document).ready(function() {
    handleImageUpload();
    
    // Agregar evento para el botón de guardar cambios de estado
    $('#btn-guardar-estado').on('click', function() {
        cambiarEstadoPoliza();
    });
});
