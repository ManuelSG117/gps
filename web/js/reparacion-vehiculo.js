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

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Actualizar título del modal
                    $('#reparacionModalLabel').text('Ver Reparación de Vehículo');
                    
                    // Deshabilitar todos los campos
                    $('#create-reparacion-form').find('input, select, textarea').prop('disabled', true);
                    
                    // Ocultar botones de navegación y guardar
                    $('.next-step, .prev-step, button[type="submit"]').hide();
                    
                    // Llenar los campos con los datos
                    Object.keys(data).forEach(key => {
                        $(`#reparacionvehiculo-${key}`).val(data[key]);
                    });
                    
                    // Mostrar imágenes si existen
                    const galleryContainer = $('.image-gallery');
                    galleryContainer.empty();
                    
                    if (data.imagenes && data.imagenes.length > 0) {
                        $('.view-mode-gallery').show();
                        $('.edit-mode-upload').hide();
                        
                        data.imagenes.forEach((imagen, index) => {
                            const galleryItem = $(`
                                <div class="gallery-item" data-index="${index}">
                                    <img src="${imagen.url}" alt="Imagen ${index + 1}">
                                </div>
                            `);
                            galleryContainer.append(galleryItem);
                        });
                    }
                    
                    // Mostrar todos los pasos
                    $('.step-content').show();
                    $('.step-indicators').hide();
                    
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
    $(document).on('click', '.ajax-view', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        // Show loading indicator
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

                    // Update modal title
                    $('#reparacionModalLabel').text('Ver Reparación de Vehículo');

                    // Fill form fields
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

                    // Disable all form fields
                    $('#create-reparacion-form').find('input, select, textarea').prop('disabled', true);
                      // Show navigation buttons but hide submit button
                    $('.next-step, .prev-step').show();
                    $('button[type="submit"]').hide();
                    
                    // Show first step and hide others
                    $('.step-content').not('#step-content-1').hide();
                    $('#step-content-1').show();
                    $('.step-indicators').show();

                    // Show images if any
                    if (response.imagenes && response.imagenes.length > 0) {
                        $('.view-mode-gallery').show();
                        $('.edit-mode-upload').hide();
                        displayImages(response.imagenes);
                    } else {
                        $('.view-mode-gallery, .edit-mode-upload').hide();
                    }

                    // Show the modal
                    $('#reparacionModal').modal('show');

                    // Close loading indicator
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

    function displayImages(images) {
        const galleryContainer = $('.image-gallery');
        galleryContainer.empty();
        
        images.forEach((image, index) => {
            const galleryItem = $(`
                <div class="gallery-item" data-index="${index}">
                    <img src="${image.url}" alt="Imagen ${index + 1}">
                </div>
            `);
            galleryContainer.append(galleryItem);
        });
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

        // Manejo del formulario AJAX
        $('#create-reparacion-form').on('beforeSubmit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(form[0]);
            
            // Mostrar indicador de carga
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
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message
                        }).then((result) => {
                            $('#reparacionModal').modal('hide');
                            // Recargar solo la grilla usando pjax
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
        handleImageUpload();

        // Modificar el manejo del formulario para incluir las imágenes
        $('#create-reparacion-form').on('beforeSubmit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(form[0]);
            
            // Agregar las imágenes al FormData
            const imageInput = document.getElementById('imagen-servicio');
            if (imageInput.files.length > 0) {
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
                            // Recargar solo la grilla usando pjax
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
    });

