
function bindStepNavigation() {
    // Desasocia primero para evitar duplicados
    $(document).off('click', '.next-step');
    $(document).off('click', '.prev-step');

    $(document).on('click', '.next-step', function() {
        var currentStep = parseInt($(this).closest('.step-content').data('step'));
        showStep(currentStep + 1);
    });

    $(document).on('click', '.prev-step', function() {
        var currentStep = parseInt($(this).closest('.step-content').data('step'));
        showStep(currentStep - 1);
    });
}


function showStep(stepNumber) {
    $('.step-content').hide();
    $(`#step-content-${stepNumber}`).show();
    $('.step-indicator').removeClass('active');
    $(`.step-indicator[data-step="${stepNumber}"]`).addClass('active');
}


// Manejo del click en el botón eliminar
    $(document).on('click', '.ajax-delete', function(e) {
        e.preventDefault();
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
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        '_csrf-frontend': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function(response) {
                        Swal.close(); // Cerrar el modal de carga

                        if (response.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message || 'La reparación ha sido eliminada con éxito.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                $.pjax.reload({container: '#reparaciones-grid'});
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                response.message || 'No se pudo eliminar la reparación.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close(); // Cerrar el modal de carga
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar la reparación. Por favor, intenta nuevamente.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Ver detalles de una reparación
    $(document).on('click', '.ajax-view', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Cargando...',
            text: 'Por favor espera.',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch repair data
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Actualizar título del modal
                    $('#reparacionModalLabel').text('Ver Reparación de Vehículo');
                    
                    // Llenar los campos con los datos
                    $('#reparacionvehiculo-vehiculo_id').val(data.vehiculo_id).trigger('change');
                    $('#reparacionvehiculo-fecha').val(data.fecha);
                    $('#reparacionvehiculo-tipo_servicio').val(data.tipo_servicio);
                    $('#reparacionvehiculo-descripcion').val(data.descripcion);
                    $('#reparacionvehiculo-costo').val(data.costo);
                    $('#reparacionvehiculo-tecnico').val(data.tecnico);
                    $('#reparacionvehiculo-notas').val(data.notas);
                    $('#reparacionvehiculo-estado_servicio').val(data.estado_servicio);
                    $('#reparacionvehiculo-motivo_pausa').val(data.motivo_pausa);
                    $('#reparacionvehiculo-requisitos_reanudar').val(data.requisitos_reanudar);
                    $('#reparacionvehiculo-fecha_finalizacion').val(data.fecha_finalizacion);
                    
                    // Deshabilitar todos los campos
                    $('#create-reparacion-form').find('input, select, textarea').prop('disabled', true);
                    
                    // Mostrar botones de navegación pero ocultar guardar
                    $('.next-step, .prev-step').show();
                    $('button[type="submit"]').hide();
                    
                    // Mostrar el primer paso y ocultar los demás
                    $('.step-content').not('#step-content-1').hide();
                    $('#step-content-1').show();
                    $('.step-indicators').show();
                    
                    // Mostrar imágenes si existen
                    if (response.imagenes && response.imagenes.length > 0) {
                        $('.view-mode-gallery').show();
                        $('.edit-mode-upload').hide();
                        displayImages(response.imagenes);
                    } else {
                        $('.view-mode-gallery, .edit-mode-upload').hide();
                    }
                    
                    // Mostrar el modal
                    $('#reparacionModal').modal('show');
                    
                    // Cerrar el indicador de carga
                    Swal.close();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo cargar la reparación'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al cargar la reparación'
                });
            }
        });
    });

    // Lightbox para las imágenes
    let currentImageIndex = 0;
    const lightboxTemplate = `
        <div class="lightbox">
            <button class="lightbox-close">&times;</button>
            <button class="lightbox-nav lightbox-prev">&lt;</button>
            <button class="lightbox-nav lightbox-next">&gt;</button>
            <img src="" alt="Imagen ampliada">
        </div>
    `;
    
    $(document).on('click', '.gallery-item', function() {
        const images = $('.gallery-item img').map(function() {
            return $(this).attr('src');
        }).get();
        
        currentImageIndex = $(this).data('index');
        
        if (!$('.lightbox').length) {
            $('body').append(lightboxTemplate);
        }
        
        updateLightboxImage(images);
        $('.lightbox').fadeIn();
    });
    
    $(document).on('click', '.lightbox-close', function() {
        $('.lightbox').fadeOut();
    });
    
    $(document).on('click', '.lightbox-prev', function(e) {
        e.stopPropagation();
        const images = $('.gallery-item img').map(function() {
            return $(this).attr('src');
        }).get();
        
        currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        updateLightboxImage(images);
    });
    
    $(document).on('click', '.lightbox-next', function(e) {
        e.stopPropagation();
        const images = $('.gallery-item img').map(function() {
            return $(this).attr('src');
        }).get();
        
        currentImageIndex = (currentImageIndex + 1) % images.length;
        updateLightboxImage(images);
    });
    
    function updateLightboxImage(images) {
        $('.lightbox img').attr('src', images[currentImageIndex]);
    }

    // Restablecer el formulario cuando se cierra el modal
    $('#reparacionModal').on('hidden.bs.modal', function () {
        // Reset y habilitar todos los campos del formulario
        $('#create-reparacion-form')[0].reset();
        $('#create-reparacion-form').find('input, select, textarea').prop('disabled', false);
        
        // Mostrar el botón de guardar nuevamente
        $('#create-reparacion-form').find('button[type="submit"]').show();
        $('.next-step, .prev-step').show();
        
        // Restablecer título del modal y acción del formulario
        $('#reparacionModalLabel').text('Nueva Reparación de Vehículo');
        $('#create-reparacion-form').attr('action', '/reparacion-vehiculo/create');
        
        // Restablecer la visibilidad de los pasos
        $('.step-content').not('#step-content-1').hide();
        $('#step-content-1').show();
        $('.step-indicators').show();
        
        // Restablecer los indicadores de paso
        $('.step-indicator').removeClass('active');
        $('.step-indicator[data-step="1"]').addClass('active');
        
        // Restablecer la galería y el modo de carga de imágenes
        $('.view-mode-gallery').hide();
        $('.edit-mode-upload').show();
        $('.image-gallery').empty();
    });    // Handler for view button click


    function displayImages(images) {
    console.log('Función displayImages llamada con imágenes:', images);
    
    // Almacenar las imágenes globalmente para que puedan ser accedidas por la línea de tiempo
    // Importante: No reemplazar las imágenes existentes, sino acumularlas
    if (!window.allImages) {
        window.allImages = [];
    }
    
    // Verificar si hay nuevas imágenes para agregar
    if (images && images.length > 0) {
        // Crear un mapa de URLs existentes para evitar duplicados
        const existingUrls = new Set(window.allImages.map(img => img.url));
        
        // Agregar solo imágenes nuevas que no existan ya
        let newImagesAdded = 0;
        images.forEach(image => {
            // Asegurarse de que la imagen tenga una URL válida
            if (!image.url) {
                console.warn('Imagen sin URL detectada:', image);
                return;
            }
            
            // Normalizar la URL para comparación
            const normalizedUrl = image.url.trim();
            if (!normalizedUrl) {
                console.warn('URL vacía detectada');
                return;
            }
            
            if (!existingUrls.has(normalizedUrl)) {
                window.allImages.push({
                    url: normalizedUrl
                });
                newImagesAdded++;
                console.log('Nueva imagen agregada:', normalizedUrl);
            } else {
                console.log('Imagen ya existente, no agregada:', normalizedUrl);
            }
        });
        
        console.log(`Total de imágenes acumuladas: ${window.allImages.length} (${newImagesAdded} nuevas)`);
    } else {
        console.warn('No se recibieron imágenes para mostrar');
    }
    
    // Mostrar todas las imágenes en la galería
    const galleryContainer = $('.image-gallery');
    galleryContainer.empty();
    
    if (window.allImages.length === 0) {
        console.warn('No hay imágenes para mostrar en la galería');
        galleryContainer.html('<p class="text-muted">No hay imágenes disponibles</p>');
    } else {
        window.allImages.forEach((image, index) => {
            const galleryItem = $(`
                <div class="gallery-item" data-index="${index}">
                    <img src="${image.url}" alt="Imagen ${index + 1}">
                </div>
            `);
            galleryContainer.append(galleryItem);
        });
        
        // Analizar nombres de archivos para depuración
        const fileNames = window.allImages.map(img => {
            const fileName = img.url.split('/').pop();
            return fileName;
        });
        console.log('Nombres de archivos en la galería:', fileNames);
    }
    
    // Si hay un historial de estados visible, actualizarlo para mostrar las imágenes
    if ($('#historial-estados-timeline').length > 0 && window.historialEstados) {
        mostrarHistorialEstados(window.historialEstados);
    }
}

    $(document).ready(function() {
        // Función para inicializar Flatpickr
        function initializeFlatpickr() {
            const dateInputs = document.querySelectorAll('.flatpickr');
            if (dateInputs.length) {
                dateInputs.forEach(input => {
                    if (!input._flatpickr) {
                        flatpickr(input, {
                            locale: "es",
                            dateFormat: "Y-m-d",
                            allowInput: true,
                            minDate: "today",
                            enableTime: false,
                            time_24hr: true
                        });
                    }
                });
            }
        }

        // Inicializar Flatpickr cuando se muestra el modal
        $('#reparacionModal').on('shown.bs.modal', function() {
            initializeFlatpickr();
        });

        // Inicializar cuando el documento está listo
        initializeFlatpickr();

        // Función para mostrar un paso específico
        function showStep(stepNumber) {
            $('.step-content').hide();
            $(`#step-content-${stepNumber}`).show();
            
            // Actualizar indicadores de paso
            $('.step-indicator').removeClass('active');
            $(`.step-indicator[data-step="${stepNumber}"]`).addClass('active');
        }

        // Event handlers para los botones de navegación
        $('.next-step').click(function() {
            var currentStep = parseInt($(this).closest('.step-content').data('step'));
            showStep(currentStep + 1);
        });

        $('.prev-step').click(function() {
            var currentStep = parseInt($(this).closest('.step-content').data('step'));
            showStep(currentStep - 1);
        });

        // Mostrar/ocultar campos de pausa según el estado del servicio
        $('#reparacionvehiculo-estado_servicio').change(function() {
            if ($(this).val() == '3') { // Si el estado es "Pausado"
                $('.pause-fields').show();
            } else {
                $('.pause-fields').hide();
            }
        });

        // Inicializar en el primer paso cuando se abre el modal
        $('#reparacionModal').on('shown.bs.modal', function() {
            showStep(1);
        });

       

        // Manejo de carga de imágenes
        function handleImageUpload() {
            const input = document.getElementById('imagen-servicio');
            const previewContainer = document.querySelector('.image-preview-container');
            let files = [];

            input.addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
                
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
                            <button type="button" class="remove-image">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(previewDiv);

                        // Agregar evento para eliminar imagen
                        previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                            previewDiv.remove();
                            files = files.filter(f => f !== file);
                        });
                    };
                    reader.readAsDataURL(file);
                    files.push(file);
                });
            });

            // Limpiar las imágenes cuando se cierra el modal
            $('#reparacionModal').on('hidden.bs.modal', function() {
                previewContainer.innerHTML = '';
                files = [];
            });
        }

        // Inicializar el manejo de imágenes
        handleImageUpload();        // Asociar el manejador del formulario
        bindAjaxFormSubmit();
    });

function bindAjaxFormSubmit() {
    $('#create-reparacion-form').off('beforeSubmit').on('beforeSubmit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(form[0]);
        // Agregar las imágenes al FormData
        const imageInput = document.getElementById('imagen-servicio');
        if (imageInput && imageInput.files.length > 0) {
            Array.from(imageInput.files).forEach((file, index) => {
                formData.append(`imagenes[${index}]`, file);
            });
        }
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Cargando...',
            text: 'Por favor espera mientras se suben las imágenes.',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then((result) => {
                        $('#reparacionModal').modal('hide');
                        $.pjax.reload({container: '#reparaciones-grid'});
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al guardar la reparación'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al guardar la reparación'
                });
            }
        });
        return false;
    });
}


// Modal para cambiar el estado de una reparación
function mostrarModalCambioEstado(id, estadoActual) {
    // Crear el contenido del modal
    let estadoOptions = '';
    const estados = {
        1: 'Pendiente',
        2: 'En Proceso',
        3: 'Pausado',
        4: 'Completado'
    };
    
    // Generar opciones de estado, excluyendo el estado actual
    for (const [value, label] of Object.entries(estados)) {
        if (parseInt(value) !== estadoActual) {
            estadoOptions += `<option value="${value}">${label}</option>`;
        }
    }
    
    const modalContent = `
        <div id="cambio-estado-container">
            <div class="form-group mb-3">
                <label for="nuevo-estado">Nuevo Estado:</label>
                <select class="form-control" id="nuevo-estado" name="estado" required>
                    <option value="">Seleccione un estado</option>
                    ${estadoOptions}
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="comentario-estado">Comentario:</label>
                <textarea class="form-control" id="comentario-estado" name="comentario" rows="3" placeholder="Agregue un comentario sobre el cambio de estado" style="width: 100%; min-height: 80px; z-index: 9999; position: relative; display: block !important;"></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="imagenes-estado">Imágenes del cambio de estado:</label>
                <div class="image-upload-container">
                    <div class="image-preview-container d-flex flex-wrap gap-2 mb-2"></div>
                    <div class="upload-controls">
                        <input type="file" id="imagenes-estado" name="imagenes[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Puede seleccionar múltiples imágenes. Formatos permitidos: JPG, PNG</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar estilos para asegurar que el textarea sea interactivo
    const style = document.createElement('style');
    style.textContent = `
        #comentario-estado {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        .swal2-content {
            z-index: 1;
        }
    `;
    document.head.appendChild(style);
    
    Swal.fire({
        title: 'Cambiar Estado de la Reparación',
        html: modalContent,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        allowOutsideClick: false,
        didOpen: () => {
            // Asegurar que el campo de comentario sea interactivo
            const comentarioTextarea = document.getElementById('comentario-estado');
            if (comentarioTextarea) {
                // Asegurar que el textarea sea editable
                comentarioTextarea.disabled = false;
                comentarioTextarea.readOnly = false;
                
                // Forzar el foco en el textarea para asegurar que sea interactivo
                setTimeout(() => {
                    comentarioTextarea.focus();
                    comentarioTextarea.blur();
                }, 100);
            }
            
            // Inicializar la previsualización de imágenes
            const input = document.getElementById('imagenes-estado');
            const previewContainer = document.querySelector('.image-preview-container');
            
            input.addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
                
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
                            <button type="button" class="remove-image">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(previewDiv);

                        // Agregar evento para eliminar imagen
                        previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                            previewDiv.remove();
                        });
                    };
                    reader.readAsDataURL(file);
                });
            });
        },
        preConfirm: () => {
            const nuevoEstado = document.getElementById('nuevo-estado').value;
            // Obtener el valor del comentario directamente del DOM
            const comentarioElement = document.getElementById('comentario-estado');
            const comentario = comentarioElement ? comentarioElement.value : '';
            
            if (!nuevoEstado) {
                Swal.showValidationMessage('Por favor seleccione un estado');
                return false;
            }
            
            // Mostrar el comentario en la consola para depuración
            console.log('Comentario capturado:', comentario);
            
            return { estado: nuevoEstado, comentario: comentario };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            cambiarEstadoReparacion(id, result.value.estado, result.value.comentario);
        }
    });
}

// Función para cambiar el estado de una reparación
function cambiarEstadoReparacion(id, estado, comentario) {
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Actualizando estado...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Crear FormData para enviar datos e imágenes
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', estado);
    
    // Asegurar que el comentario se procese correctamente
    if (comentario !== undefined && comentario !== null) {
        formData.append('comentario', comentario);
    } else {
        // Si por alguna razón el comentario no está disponible, intentar obtenerlo directamente del campo
        const comentarioElement = document.getElementById('comentario-estado');
        if (comentarioElement) {
            formData.append('comentario', comentarioElement.value);
        } else {
            formData.append('comentario', '');
        }
    }
    
    // Se ha eliminado la parte del token CSRF ya que no es necesario
    
    // Agregar las imágenes al FormData si existen
    const imageInput = document.getElementById('imagenes-estado');
    if (imageInput && imageInput.files && imageInput.files.length > 0) {
        console.log('Imágenes encontradas para subir:', imageInput.files.length);
        
        // Verificar que las imágenes sean válidas antes de agregarlas
        const validFiles = Array.from(imageInput.files).filter(file => {
            const isValid = file.type.startsWith('image/');
            if (!isValid) {
                console.warn('Archivo no válido detectado:', file.name, file.type);
            }
            return isValid;
        });
        
        console.log('Imágenes válidas para subir:', validFiles.length);
        
        // Agregar cada imagen válida al FormData
        validFiles.forEach((file, index) => {
            formData.append(`imagenes[${index}]`, file);
            console.log(`Imagen ${index} agregada al FormData:`, file.name, file.type, file.size);
        });
    } else {
        console.log('No se encontraron imágenes para subir');
    }
    
    // Verificar el contenido del FormData para depuración
    console.log('Contenido del FormData:');
    for (let pair of formData.entries()) {
        console.log(pair[0], pair[1] instanceof File ? `${pair[1].name} (${pair[1].size} bytes)` : pair[1]);
    }
    
    $.ajax({
        url: '/reparacion-vehiculo/cambiar-estado',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            console.log('Respuesta del servidor:', response);
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Actualizar la tabla de reparaciones
                    $.pjax.reload({container: '#reparaciones-grid'});
                    
                    // Si hay un modal abierto con la vista de la reparación, actualizar el estado mostrado
                    if ($('#reparacionModal').hasClass('show')) {
                        $('#reparacionvehiculo-estado_servicio').val(response.estado_actual);
                        
                        // Mostrar el historial en la línea de tiempo si existe
                        if (response.historial && response.historial.length > 0) {
                            // Almacenar el historial globalmente para poder actualizarlo cuando se carguen nuevas imágenes
                            window.historialEstados = response.historial;
                            mostrarHistorialEstados(response.historial);
                        }
                        
                        // Si hay imágenes nuevas, actualizar la galería
                        if (response.imagenes && response.imagenes.length > 0) {
                            $('.view-mode-gallery').show();
                            // Llamar a displayImages con las imágenes recibidas
                            // La función displayImages ya está modificada para acumular imágenes en lugar de reemplazarlas
                            console.log('Recibidas nuevas imágenes después del cambio de estado:', response.imagenes.length);
                            displayImages(response.imagenes);
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al actualizar el estado'
            });
        }
    });
}

// Función para mostrar el historial de estados en una línea de tiempo
function mostrarHistorialEstados(historial) {
    if (!historial || historial.length === 0) return;
    
    console.log('Mostrando historial de estados:', historial);
    console.log('Imágenes disponibles:', window.allImages);
    
    // Crear el contenedor de la línea de tiempo si no existe
    if ($('#historial-estados-timeline').length === 0) {
        // Agregar el contenedor después del paso 3 en el modal
        $('#step-content-3').append(`
            <div class="mt-4 pt-3 border-top">
                <h5 class="text-center text-primary mb-3">Historial de Estados</h5>
                <div id="historial-estados-timeline" class="timeline-container"></div>
            </div>
        `);
    }
    
    // Limpiar el contenedor
    const timelineContainer = $('#historial-estados-timeline');
    timelineContainer.empty();
    
    // Crear la línea de tiempo
    const timeline = $('<div class="widget-timeline"></div>');
    const timelineList = $('<ul class="timeline"></ul>');
    
    // Agregar cada cambio de estado a la línea de tiempo
    historial.forEach((item, index) => {
        const fecha = new Date(item.fecha_cambio);
        const fechaFormateada = fecha.toLocaleString('es-ES');
        const timestamp = fecha.toISOString().split('T')[0].replace(/-/g, '') + '_' + fecha.toTimeString().split(' ')[0].replace(/:/g, '');
        
        // Buscar imágenes asociadas a este cambio de estado
        let imagenesHtml = '';
        if (window.allImages && window.allImages.length > 0) {
            // Normalizar nombres de estado para la comparación (quitar caracteres especiales)
            const estadoAntNormalizado = item.estado_anterior_nombre.toLowerCase().replace(/[^a-z0-9_]/g, "_");
            const estadoNuevoNormalizado = item.estado_nuevo_nombre.toLowerCase().replace(/[^a-z0-9_]/g, "_");
            const patronCambio = `cambio_${estadoAntNormalizado}_a_${estadoNuevoNormalizado}`;
            
            // Filtrar imágenes que coincidan con el patrón de cambio de estado
            const imagenesEstado = window.allImages.filter(img => {
                const fileName = img.url.split('/').pop();
                return fileName.toLowerCase().includes(patronCambio);
            });
            
            console.log(`Buscando imágenes para cambio de estado [${index}]:`, patronCambio);
            console.log('Nombres de archivos disponibles:', window.allImages.map(img => img.url.split('/').pop()));
            console.log('Imágenes encontradas para este cambio:', imagenesEstado.length);
            
            // Si hay imágenes para este cambio de estado, mostrarlas
            if (imagenesEstado.length > 0) {
                imagenesHtml = `
                    <div class="timeline-images mt-2">
                        <h6 class="text-muted mb-2">Imágenes del cambio de estado:</h6>
                        <div class="d-flex flex-wrap gap-2">
                `;
                
                imagenesEstado.forEach((img, imgIndex) => {
                    imagenesHtml += `
                        <div class="timeline-image-item" data-index="${imgIndex}" data-estado="${index}">
                            <img src="${img.url}" alt="Imagen cambio estado" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                    `;
                });
                
                imagenesHtml += `
                        </div>
                    </div>
                `;
            }
        }
        
        const timelineItem = $(`
            <li>
                <div class="timeline-badge ${item.clase_estado}"></div>
                <div class="timeline-panel">
                    <div class="media">
                        <div class="media-body">
                            <h6 class="mb-1">${item.estado_nuevo_nombre}</h6>
                            <small class="d-block">${fechaFormateada}</small>
                            ${item.comentario ? `<p class="mb-0 mt-2">${item.comentario}</p>` : ''}
                            ${item.estado_anterior ? `<small class="text-muted">Cambio desde: ${item.estado_anterior_nombre}</small>` : ''}
                            ${imagenesHtml}
                        </div>
                    </div>
                </div>
            </li>
        `);
        
        timelineList.append(timelineItem);
    });
    
    timeline.append(timelineList);
    timelineContainer.append(timeline);
    
    // Agregar evento para abrir el lightbox al hacer clic en las imágenes de la línea de tiempo
    $('.timeline-image-item').on('click', function() {
        const img = $(this).find('img').attr('src');
        if (!$('.lightbox').length) {
            $('body').append(lightboxTemplate);
        }
        $('.lightbox img').attr('src', img);
        $('.lightbox').fadeIn();
    });}
    
    // Agregar evento para ver imágenes en lightbox
    $('.timeline-image-item').on('click', function() {
        const images = $(this).closest('.timeline-images').find('img').map(function() {
            return $(this).attr('src');
        }).get();
        
        const index = $(this).data('index');
        
        // Mostrar lightbox
        if (!$('.lightbox').length) {
            $('body').append(`
                <div class="lightbox">
                    <button class="lightbox-close">&times;</button>
                    <button class="lightbox-nav lightbox-prev">&lt;</button>
                    <button class="lightbox-nav lightbox-next">&gt;</button>
                    <img src="" alt="Imagen ampliada">
                </div>
            `);
            
            // Agregar eventos al lightbox
            $('.lightbox-close').on('click', function() {
                $('.lightbox').fadeOut();
            });
            
            $('.lightbox-prev').on('click', function(e) {
                e.stopPropagation();
                currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                $('.lightbox img').attr('src', images[currentImageIndex]);
            });
            
            $('.lightbox-next').on('click', function(e) {
                e.stopPropagation();
                currentImageIndex = (currentImageIndex + 1) % images.length;
                $('.lightbox img').attr('src', images[currentImageIndex]);
            });
        }
        
        // Mostrar la imagen seleccionada
        currentImageIndex = index;
        $('.lightbox img').attr('src', images[currentImageIndex]);
        $('.lightbox').fadeIn();
    });


// Agregar botón de cambio de estado en la vista de detalle
$(document).on('click', '.ajax-view', function(e) {
    e.preventDefault();
    var url = $(this).data('url');
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch repair data
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Actualizar título del modal
                $('#reparacionModalLabel').text('Ver Reparación de Vehículo');
                
                // Llenar los campos con los datos
                $('#reparacionvehiculo-vehiculo_id').val(data.vehiculo_id).trigger('change');
                $('#reparacionvehiculo-fecha').val(data.fecha);
                $('#reparacionvehiculo-tipo_servicio').val(data.tipo_servicio);
                $('#reparacionvehiculo-descripcion').val(data.descripcion);
                $('#reparacionvehiculo-costo').val(data.costo);
                $('#reparacionvehiculo-tecnico').val(data.tecnico);
                $('#reparacionvehiculo-notas').val(data.notas);
                $('#reparacionvehiculo-estado_servicio').val(data.estado_servicio);
                $('#reparacionvehiculo-motivo_pausa').val(data.motivo_pausa);
                $('#reparacionvehiculo-requisitos_reanudar').val(data.requisitos_reanudar);
                $('#reparacionvehiculo-fecha_finalizacion').val(data.fecha_finalizacion);
                
                // Deshabilitar todos los campos
                $('#create-reparacion-form').find('input, select, textarea').prop('disabled', true);
                
                // Mostrar botones de navegación pero ocultar guardar
                $('.next-step, .prev-step').show();
                $('button[type="submit"]').hide();
                
                // Agregar botón para cambiar estado si no está completado
                if (data.estado_servicio !== 4) {
                    // Verificar si ya existe el botón para evitar duplicados
                    if ($('#btn-cambiar-estado').length === 0) {
                        const btnCambiarEstado = $(`
                            <button type="button" id="btn-cambiar-estado" class="btn btn-warning ms-2">
                                <i class="fas fa-exchange-alt"></i> Cambiar Estado
                            </button>
                        `);
                        
                        // Agregar el botón después del botón de cerrar en el footer
                        $('.modal-footer .btn-close').after(btnCambiarEstado);
                        
                        // Asociar evento al botón
                        btnCambiarEstado.on('click', function() {
                            mostrarModalCambioEstado(data.id, data.estado_servicio);
                        });
                    }
                }
                
                // Mostrar el historial de estados si existe en la respuesta
                if (response.historial && response.historial.length > 0) {
                    // Almacenar el historial globalmente para poder actualizarlo cuando se carguen nuevas imágenes
                    window.historialEstados = response.historial;
                    mostrarHistorialEstados(response.historial);
                }
                
                // Mostrar el primer paso y ocultar los demás
                $('.step-content').not('#step-content-1').hide();
                $('#step-content-1').show();
                $('.step-indicators').show();
                
                // Mostrar imágenes si existen
                if (response.imagenes && response.imagenes.length > 0) {
                    $('.view-mode-gallery').show();
                    $('.edit-mode-upload').hide();
                    displayImages(response.imagenes);
                } else {
                    $('.view-mode-gallery, .edit-mode-upload').hide();
                }
                
                // Mostrar el modal
                $('#reparacionModal').modal('show');
                
                // Cerrar el indicador de carga
                Swal.close();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo cargar la reparación'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al cargar la reparación'
            });
        }
    });
});

// Handler para el botón editar (puede ser .ajax-update o similar)
$(document).on('click', '.ajax-update', function(e) {
    e.preventDefault();
    var url = $(this).data('url'); // Debe ser la URL de update, ej: /reparacion-vehiculo/update?id=XX

    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });    // Obtener datos del registro
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Llenar los campos con los datos
                $('#reparacionvehiculo-vehiculo_id').val(data.vehiculo_id).trigger('change');
                $('#reparacionvehiculo-fecha').val(data.fecha);
                $('#reparacionvehiculo-tipo_servicio').val(data.tipo_servicio);
                $('#reparacionvehiculo-descripcion').val(data.descripcion);
                $('#reparacionvehiculo-costo').val(data.costo);
                $('#reparacionvehiculo-tecnico').val(data.tecnico);
                $('#reparacionvehiculo-notas').val(data.notas);
                $('#reparacionvehiculo-estado_servicio').val(data.estado_servicio);
                $('#reparacionvehiculo-motivo_pausa').val(data.motivo_pausa);
                $('#reparacionvehiculo-requisitos_reanudar').val(data.requisitos_reanudar);
                $('#reparacionvehiculo-fecha_finalizacion').val(data.fecha_finalizacion);
                
                // Volver a asociar los eventos de navegación y submit
                bindStepNavigation();
                bindAjaxFormSubmit();

                // Configurar el modal para edición
                $('#reparacionModalLabel').text('Editar Reparación de Vehículo');
                $('#create-reparacion-form').attr('action', url);
                $('#create-reparacion-form').find('input, select, textarea').prop('disabled', false);
                $('#create-reparacion-form').find('button[type="submit"]').show();
                $('.next-step, .prev-step').show();

                // Mostrar imágenes existentes si hay
                if (response.imagenes && response.imagenes.length > 0) {
                    $('.view-mode-gallery').show();
                    displayImages(response.imagenes);
                } else {
                    $('.view-mode-gallery').hide();
                }
                
                // Mostrar la sección de carga de nuevas imágenes
                $('.edit-mode-upload').show();
                
                // Mostrar el modal
                $('#reparacionModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo cargar la reparación'
                });
            }
            
            Swal.close();
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la reparación para editar'
            });
        }
    });
});


// Al cargar el modal por crear, también asocia el submit AJAX
$(document).ready(function() {
    // ...existing code...
    bindAjaxFormSubmit();
    // ...existing code...
});