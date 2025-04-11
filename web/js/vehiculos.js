// Add this at the top of the file
//console.log('Vehiculos.js loaded successfully');

// Add a click handler for the create button
$(document).ready(function() {
  //  console.log('Document ready in vehiculos.js');
    
    $(document).on('click', 'button[data-target="#exampleModalCenter"]', function(e) {
    //    console.log('Create vehicle button clicked');
      //  console.log('Modal target:', $(this).data('target'));
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
            // Make sure the field is visible and enabled before validation
            if($(this).is(':visible') && !$(this).prop('disabled')) {
                if($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
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
   // console.log('Delete button clicked');
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


// Add a form submission handler
$(document).ready(function() {
    // Handle form submission
    $(document).on('submit', '#create-vehiculos-form', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Temporarily enable all fields to allow form submission
        $(this).find(':input:disabled').prop('disabled', false);
        
        var form = $(this);
        var formData = new FormData(this);
        
        // Ensure all file inputs are included in the form data
        $('.file-input').each(function() {
            var inputId = $(this).attr('id');
            // Check if inputId is defined before using replace
            if (inputId) {
                var fileCategory = inputId.replace('vehiculo-imagen-', '');
                
                // Check if there's a file selected
                var fileInput = document.getElementById(inputId);
                if (fileInput && fileInput.files.length > 0) {
                    // The file will be automatically included in FormData
                    console.log('File detected for ' + fileCategory);
                }
            }
        });
        
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
            type: form.attr('method'),
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
                        $(document).data('confirmed-close', true);
                        $('#exampleModalCenter').modal('hide');
                        $.pjax.reload({container: '#vehiculos-grid'});
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al guardar el vehículo'
                    });
                    
                    // If there's HTML to update, do it
                    if (response.html) {
                        $('#create-vehiculos-pjax').html(response.html);
                    }
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al guardar el vehículo'
                });
            }
        });
    });
});

// Add a click handler for the view button
$(document).on('click', '.ajax-view', function(e) {
    e.preventDefault();
    
    var url = $(this).data('url');
    
    // Show loading indicator
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Fetch vehicle data and update modal
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Open the modal
                $('#exampleModalCenter').modal('show');
                
                // Update modal title
                $('#exampleModalCenterTitle').text('Ver Vehículo');
                
                // Update form action to view
                $('#create-vehiculos-form').attr('action', url);
                
                // Populate form with data
                for (var field in response.data) {
                    $('#vehiculos-' + field.toLowerCase()).val(response.data[field]);
                }
                
                // First, hide all file input containers in view mode only
                if (response.isViewMode) {
                    // Remove any previous messages
                    $('#step-content-3 .alert').remove();
                    
                    // Hide all file input containers initially
                    $('.file-input').each(function() {
                        $(this).closest('.col-md-6, .col-md-4').hide();
                    });
                    
                    // Display only images that are available
                    if (response.images && Object.keys(response.images).length > 0) {
                        console.log('Images received:', response.images);
                        
                        // Initialize file inputs with preview images
                        for (var category in response.images) {
                            var inputId = 'vehiculo-imagen-' + category;
                            var fileInput = $('#' + inputId);
                            
                            if (fileInput.length) {
                                // Show this file input's container
                                fileInput.closest('.col-md-6, .col-md-4').show();
                                
                                console.log('Setting preview for', category, response.images[category]);
                                
                                // Destroy existing file input instance if it exists
                                if (fileInput.data('fileinput')) {
                                    fileInput.fileinput('destroy');
                                }
                                
                                // Initialize with preview image
                                fileInput.fileinput({
                                    theme: 'fa',
                                    showUpload: false,
                                    showCancel: false,
                                    showRemove: false,
                                    showBrowse: false,
                                    showClose: false,
                                    initialPreview: [response.images[category]],
                                    initialPreviewAsData: true,
                                    initialPreviewConfig: [
                                        {caption: category, downloadUrl: response.images[category], key: 1}
                                    ],
                                    fileActionSettings: {
                                        showRemove: false,
                                        showUpload: false,
                                        showZoom: true,
                                        showDrag: false,
                                        showDownload: true
                                    }
                                });
                            }
                        }
                        
                        // If no images were found, show a message
                        if (Object.keys(response.images).length === 0) {
                            $('#step-content-3').append('<div class="alert alert-info text-center">No hay imágenes disponibles para este vehículo.</div>');
                        }
                    } else {
                        // If no images property in response, show a message
                        $('#step-content-3').append('<div class="alert alert-info text-center">No hay imágenes disponibles para este vehículo.</div>');
                    }
                }
                
                // Disable all form fields for view mode
                if (response.isViewMode) {
                    $('#create-vehiculos-form').find('input, select, textarea').prop('disabled', true);
                    
                    // Hide all submit buttons in view mode
                    $('#create-vehiculos-form .btn-success').hide();
                }
                
                // Initialize the first step
                showStep(1);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos del vehículo'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al cargar los datos del vehículo'
            });
        }
    });
});

// Add a click handler for the update button
$(document).on('click', '.ajax-update', function(e) {
    e.preventDefault();
    
    var url = $(this).data('url');
    
    // Show loading indicator
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espera.',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Fetch vehicle data and update modal
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Open the modal
                $('#exampleModalCenter').modal('show');
                
                // Update modal title
                $('#exampleModalCenterTitle').text('Actualizar Vehículo');
                
                // Update form action to update
                $('#create-vehiculos-form').attr('action', url);
                $('#create-vehiculos-form').attr('method', 'post');
                
                // Populate form with data
                for (var field in response.data) {
                    $('#vehiculos-' + field.toLowerCase()).val(response.data[field]);
                }
                
                // Make sure all file inputs are visible in update mode
                $('.file-input').each(function() {
                    $(this).closest('.col-md-6, .col-md-4').show();
                });
                
                // Remove any previous messages
                $('#step-content-3 .alert').remove();
                
                // Initialize the first step
                showStep(1);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos del vehículo'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión al cargar los datos del vehículo'
            });
        }
    });
});


// Restablecer el formulario al cerrar el modal
$('#exampleModalCenter').on('hidden.bs.modal', function () {
    // Reset and enable all form fields
    $('#create-vehiculos-form').find('input, select, textarea').prop('disabled', false).val('');
    
    // Show the save button again
    $('#create-vehiculos-form .btn-success').show();
    
    // Reset modal title and form action
    $('#exampleModalCenterTitle').text('Crear Vehículo');
    $('#create-vehiculos-form').attr('action', '/vehiculos/create');
    
    // Reset file inputs
    try {
        $('.file-input').fileinput('clear');
    } catch (e) {
        console.log('Error clearing file inputs:', e);
    }
    
    // Reset to first step
    showStep(1);
});
