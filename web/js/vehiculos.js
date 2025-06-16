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

// Initialize file upload widgets when modal opens
$('#exampleModalCenter').on('shown.bs.modal', function() {
    initFileUploads();
});

// Initialize Kartik file upload widgets
function initFileUploads() {
    // Define image categories with their specific order
    const imageCategories = [
        {id: 'frente', label: 'Frente del vehículo', required: true},
        {id: 'lateral_derecho', label: 'Lateral derecho', required: true},
        {id: 'lateral_izquierdo', label: 'Lateral izquierdo', required: true},
        {id: 'trasera', label: 'Trasera', required: true},
        {id: 'llantas', label: 'Llantas', required: true},
        {id: 'motor', label: 'Motor', required: true},
        {id: 'kilometraje', label: 'Kilometraje', required: true}
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
                required: category.required,
                allowedFileExtensions: ['jpg', 'png', 'jpeg'],
                maxFileSize: 2048,
                initialPreviewAsData: true,
                browseClass: 'btn btn-primary',
                browseIcon: '<i class="fas fa-folder-open"></i> ',
                browseLabel: `Buscar ${category.label}`,
                removeLabel: 'Eliminar',
                removeIcon: '<i class="fas fa-trash"></i> ',
                msgPlaceholder: `Seleccionar imagen (${category.label})`,
                layoutTemplates: {
                    main2: '{preview} {remove} {browse}'
                },
                validateInitial: true,
                msgRequired: `La imagen ${category.label} es requerida`
            });

            // Add validation indicator
            if(category.required) {
                $(`#vehiculo-imagen-${category.id}`).closest('.form-group').find('label').append(
                    ' <span class="required text-danger">*</span>'
                );
            }
        }
    });
}

// Add validation before allowing to move to next step
function validateStep(stepNumber) {
    let isValid = true;
    
    if(stepNumber === 3) { // Image upload step
        $('.file-input[required]').each(function() {
            if($(this).fileinput('getFileStack').length === 0) {
                isValid = false;
                $(this).closest('.form-group').addClass('has-error');
                
                // Get the label text
                const labelText = $(this).closest('.form-group').find('label').text().replace('*', '').trim();
                
                Swal.fire({
                    icon: 'error',
                    title: 'Imagen requerida',
                    text: `Por favor seleccione una imagen para: ${labelText}`
                });
                return false; // Break the loop
            }
        });
    }
    
    return isValid && validateBasicStep(stepNumber);
}

// Basic step validation (existing function)
function validateBasicStep(stepNumber) {
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

// Validate icon file size
$('#vehiculo-icono').on('change', function() {
    const file = this.files[0];
    const maxSize = 1024 * 1024; // 1MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (file) {
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: 'El icono personalizado debe ser menor a 1MB'
            });
            this.value = '';
        } else if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no válido',
                text: 'El icono debe ser una imagen (JPG, PNG o GIF)'
            });
            this.value = '';
        }
    }
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

function handleImageUpload() {
    const input = document.getElementById('imagen-vehiculo');
    const previewContainer = document.querySelector('.image-preview-container');
    let uploadedFiles = [];

    if (input) {
        input.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            
            newFiles.forEach((file, index) => {
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
                    previewDiv.style.position = 'relative';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="width: 150px; height: 150px; object-fit: cover;">
                        <button type="button" class="remove-image" style="position: absolute; top: 5px; right: 5px;">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    previewContainer.appendChild(previewDiv);

                    // Agregar evento para eliminar imagen
                    previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                        previewDiv.remove();
                        uploadedFiles = uploadedFiles.filter(f => f !== file);
                    });
                };
                reader.readAsDataURL(file);
                uploadedFiles.push(file);
            });
        });
    }
}

// Initialize image upload handling when document is ready
$(document).ready(function() {
    handleImageUpload();
});

// Manejar la vista previa del icono
$(document).ready(function() {
    $('#icono-vehiculo').change(function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 1024 * 1024) { // 1MB
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El icono debe ser menor a 1MB'
                });
                this.value = '';
                $('#icono-preview').hide();
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#icono-preview').show().find('img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        } else {
            $('#icono-preview').hide();
        }
    });
});

// Modificar la función de envío del formulario para incluir el icono
$(document).on('submit', '#create-vehiculos-form', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(form[0]);

    // Agregar el icono al FormData si existe
    const iconoInput = document.getElementById('icono-vehiculo');
    if (iconoInput && iconoInput.files[0]) {
        formData.append('Vehiculos[icono_personalizado]', iconoInput.files[0]);
    }
    
    // Temporarily enable all fields to allow form submission
    $(this).find(':input:disabled').prop('disabled', false);
    
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

// --- INICIO: Lógica para logs de geocercas ---
$(document).on('click', '.geofence-log-btn', function(e) {
    e.preventDefault();
    const vehiculoId = $(this).data('id');
    $('#geofenceLogModal').modal('show');
    $('#geofenceLogTable tbody').html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');
    $('#geofenceFilterSelect').html('<option value="">Todas</option>');
    // Reset fechas
    $('#geofenceLogDateStart').val('');
    $('#geofenceLogDateEnd').val('');
    window._showingAllGeofenceLogs = false;

    // Obtener logs de geocercas por AJAX
    fetchGeofenceLogs(vehiculoId);
    // Guardar id para futuros filtros
    window._currentGeofenceVehiculoId = vehiculoId;
});

// Inputs de fecha y botón de filtrar
if ($('#geofenceLogDateStart').length === 0) {
    $('#geofenceLogModal .modal-body').prepend(`
        <div class="row mb-2">
            <div class="col-md-3">
                <label for="geofenceLogDateStart" class="form-label">Fecha inicio:</label>
                <input type="date" id="geofenceLogDateStart" class="form-control" />
            </div>
            <div class="col-md-3">
                <label for="geofenceLogDateEnd" class="form-label">Fecha fin:</label>
                <input type="date" id="geofenceLogDateEnd" class="form-control" />
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary" id="geofenceLogDateFilterBtn">Filtrar</button>
            </div>
        </div>
    `);
}

$(document).on('click', '#geofenceLogDateFilterBtn', function() {
    const vehiculoId = window._currentGeofenceVehiculoId;
    if (!vehiculoId) return;
    window._showingAllGeofenceLogs = false;
    fetchGeofenceLogs(vehiculoId);
});

function fetchGeofenceLogs(vehiculoId) {
    const dateStart = $('#geofenceLogDateStart').val();
    const dateEnd = $('#geofenceLogDateEnd').val();
    $.ajax({
        url: '/vehiculos/geofence-logs',
        type: 'GET',
        data: {
            vehiculo_id: vehiculoId,
            date_start: dateStart,
            date_end: dateEnd
        },
        success: function(response) {
            if (response.success) {
                // Llenar select de geocercas
                const geocercas = response.geocercas;
                $('#geofenceFilterSelect').html('<option value="">Todas</option>');
                geocercas.forEach(function(g) {
                    $('#geofenceFilterSelect').append(`<option value="${g.id}">${g.name}</option>`);
                });
                // Guardar logs en variable global temporal
                window._geofenceLogs = response.logs;
                renderGeofenceLogsTable(response.logs);
            } else {
                $('#geofenceLogTable tbody').html('<tr><td colspan="4" class="text-center text-danger">No hay datos</td></tr>');
            }
        },
        error: function() {
            $('#geofenceLogTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Error al obtener datos</td></tr>');
        }
    });
}

// Filtrar logs por geocerca
$('#geofenceFilterSelect').on('change', function() {
    const geocercaId = $(this).val();
    let logs = window._geofenceLogs || [];
    if (geocercaId) {
        logs = logs.filter(l => l.geocerca_id == geocercaId);
    }
    renderGeofenceLogsTable(logs);
});

// Renderizar tabla de logs (solo coordenadas y botón de mapa)
function renderGeofenceLogsTable(logs) {
    const tbody = $('#geofenceLogTable tbody');
    tbody.empty();
    if (!logs || logs.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center">Sin registros</td></tr>');
        return;
    }
    const maxToShow = 10;
    let showingAll = window._showingAllGeofenceLogs || false;
    let logsToShow = showingAll ? logs : logs.slice(0, maxToShow);
    logsToShow.forEach(function(log, idx) {
        const coords = `${log.lat}, ${log.lng}`;
        const verMapaBtn = `<a class=\"btn btn-sm btn-outline-success ms-1\" title=\"Ver en Google Maps\" href=\"https://maps.google.com/?q=${log.lat},${log.lng}\" target=\"_blank\"><i class=\"fas fa-map-marker-alt\"></i></a>`;
        const ubicacionHtml = `<span>${coords}</span> ${verMapaBtn}`;
        tbody.append(`<tr>
            <td>${log.fecha}</td>
            <td>${log.evento}</td>
            <td>${log.geocerca}</td>
            <td>${ubicacionHtml}</td>
        </tr>`);
    });
    if (!showingAll && logs.length > maxToShow) {
        tbody.append(`<tr><td colspan='4' class='text-center'><button class='btn btn-link' id='showAllGeofenceLogsBtn'>Mostrar todos (${logs.length})</button></td></tr>`);
    } else if (showingAll && logs.length > maxToShow) {
        tbody.append(`<tr><td colspan='4' class='text-center'><button class='btn btn-link' id='showLessGeofenceLogsBtn'>Mostrar menos</button></td></tr>`);
    }
}

$(document).on('click', '#showAllGeofenceLogsBtn', function() {
    window._showingAllGeofenceLogs = true;
    renderGeofenceLogsTable(window._geofenceLogs || []);
});

$(document).on('click', '#showLessGeofenceLogsBtn', function() {
    window._showingAllGeofenceLogs = false;
    renderGeofenceLogsTable(window._geofenceLogs || []);
});
// --- FIN: Lógica para logs de geocercas ---

function initFileInputsDispositivos() {
    // Ajusta los IDs según tus campos
    const imageInputs = [
        '#dispositivo-vehiculo-imagen', // ejemplo, cambia por tus IDs reales
        '#dispositivo-poliza-imagen'
    ];
    imageInputs.forEach(function(selector) {
        if ($(selector).length) {
            $(selector).fileinput({
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
                msgPlaceholder: 'Seleccionar imagen',
                layoutTemplates: {
                    main2: '{preview} {remove} {browse}'
                },
                validateInitial: true
            });
        }
    });
}

// Inicializa al abrir el modal
$('#exampleModalCenter').on('shown.bs.modal', function() {
    initFileInputsDispositivos();
});

$('#exampleModalCenter').on('hidden.bs.modal', function () {
    $('#create-dispositivos-form').find('input, select, textarea').prop('disabled', false).val('');
    $('#create-dispositivos-form .btn-primary').show();
    $('#exampleModalCenterTitle').text('Crear Dispositivo');
    // Limpia los fileinputs
    $('.file-input').fileinput('clear');
});

$('#create-dispositivos-form').on('submit', function (e) {
    e.preventDefault();
    // ...
});
