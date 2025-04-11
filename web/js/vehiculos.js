// Add this at the top of the file
console.log('Vehiculos.js loaded successfully');

// Add a click handler for the create button
$(document).ready(function() {
    console.log('Document ready in vehiculos.js');
    
    $(document).on('click', 'button[data-target="#exampleModalCenter"]', function(e) {
        console.log('Create vehicle button clicked');
        console.log('Modal target:', $(this).data('target'));
        $('#exampleModalCenter').modal('show');
        // Initialize the first step
        showStep(1);
    });
    
    // Step navigation
    $(document).on('click', '.next-step', function() {
        let currentStep = parseInt($(this).closest('.step-content').attr('data-step'));
        let nextStep = currentStep + 1;
        
        // Validate current step before proceeding
        if(validateStep(currentStep)) {
            showStep(nextStep);
        }
    });
    
    $(document).on('click', '.prev-step', function() {
        let currentStep = parseInt($(this).closest('.step-content').attr('data-step'));
        let prevStep = currentStep - 1;
        showStep(prevStep);
    });
    
    // Function to show a specific step
    function showStep(stepNumber) {
        $('.step-content').hide();
        $(`#step-content-${stepNumber}`).show();
        
        // Update progress indicator
        $('.step-indicator').removeClass('active');
        $(`.step-indicator[data-step="${stepNumber}"]`).addClass('active');
    }
    
    // Function to validate each step
    function validateStep(stepNumber) {
        let isValid = true;
        
        // Get all required fields in the current step
        $(`#step-content-${stepNumber} [required]`).each(function() {
            if($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if(!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor complete todos los campos obligatorios antes de continuar.'
            });
        }
        
        return isValid;
    }
    
    // Initialize file upload widgets when modal opens
    $('#exampleModalCenter').on('shown.bs.modal', function() {
        initFileUploads();
    });
    
    // Initialize Kartik file upload widgets
    function initFileUploads() {
        // Define image categories
        const imageCategories = [
            {id: 'frente', label: 'Frente del vehículo'},
            {id: 'lateral_derecho', label: 'Lateral derecho'},
            {id: 'lateral_izquierdo', label: 'Lateral izquierdo'},
            {id: 'trasera', label: 'Trasera'},
            {id: 'llantas', label: 'Llantas'},
            {id: 'motor', label: 'Motor'},
            {id: 'kilometraje', label: 'Kilometraje'}
        ];
        
        // Initialize each file input
        imageCategories.forEach(category => {
            if($(`#vehiculo-imagen-${category.id}`).length) {
                $(`#vehiculo-imagen-${category.id}`).fileinput({
                    theme: 'fa',
                    showUpload: false,
                    showCancel: false,
                    showRemove: true,
                    showPreview: true,
                    allowedFileExtensions: ['jpg', 'png', 'jpeg'],
                    maxFileSize: 2048,
                    initialPreviewAsData: true,
                    browseClass: 'btn btn-primary',
                    browseIcon: '<i class="fas fa-folder-open"></i> ',
                    browseLabel: 'Buscar imagen',
                    removeLabel: 'Eliminar',
                    removeIcon: '<i class="fas fa-trash"></i> ',
                    msgPlaceholder: `Seleccionar imagen (${category.label})`,
                    layoutTemplates: {
                        main2: '{preview} {remove} {browse}'
                    }
                });
            }
        });
    }
});

$(document).on('click', '.ajax-delete', function (e) {
    console.log('Delete button clicked');
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
                        $.pjax.reload({ container: '#vehiculos-grid' });
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


// Maneja la creación de vehículos con AJAX
$('#create-vehiculos-form').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);
    
    // Create FormData object to handle file uploads
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
                // Close the modal first
                $('#exampleModalCenter').modal('hide');
                
                // Then show success message
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                }).then((result) => {
                    // Reload the grid after the user clicks OK
                    $.pjax.reload({ container: '#vehiculos-grid' });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo crear el vehículo.',
                });
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo crear el vehículo.',
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
                $('#create-vehiculos-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Vehiculos[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Vehiculos[', '').replace(']', '')]);
                    }
                });

                // Deshabilitar los campos
                $('#create-vehiculos-form').find('input, select, textarea').prop('disabled', true);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Ver Vehículo');
                $('#exampleModalCenter').modal('show');
                $('#create-vehiculos-form').find('.btn-success').hide();
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar los datos del vehículo.',
            });
        }
    });
});


// Restablecer el formulario al cerrar el modal
$('#exampleModalCenter').on('hidden.bs.modal', function () {
    $('#create-vehiculos-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-vehiculos-form .btn-success').show();  // Mostrar el botón "Guardar"
    $('#exampleModalCenterTitle').text('Crear Vehículo');
    $('#create-vehiculos-form').attr('action', 'vehiculos/create');
    
    // Reset file inputs
    $('.file-input').fileinput('clear');
    
    // Reset to first step
    showStep(1);
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
                $('#create-vehiculos-form').find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name && data[name.replace('Vehiculos[', '').replace(']', '')] !== undefined) {
                        $(this).val(data[name.replace('Vehiculos[', '').replace(']', '')]);
                    }
                });

                // Habilitar los campos para editar
                $('#create-vehiculos-form').find('input, select, textarea').prop('disabled', false);

                // Cambiar el título del modal y mostrarlo
                $('#exampleModalCenterTitle').text('Actualizar Vehículo');
                $('#exampleModalCenter').modal('show');

                // Cambiar la acción del formulario para actualizar
                $('#create-vehiculos-form').attr('action', url);
            }
        },
        error: function () {
            Swal.close(); // Cerrar el modal de carga
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del vehículo.',
            });
        }
    });});
