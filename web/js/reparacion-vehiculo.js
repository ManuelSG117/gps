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
    });

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

