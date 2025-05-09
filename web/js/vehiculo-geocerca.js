/**
 * Archivo JavaScript para la gestión de asignaciones de geocercas a vehículos
 */

var map;
var vehiculoMarkers = {};
var selectedVehiculos = [];
var selectedGeocercas = [];

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    // Inicializar el mapa si existe el elemento
    if (document.getElementById('map')) {
        initMap();
    }
    
    // Configurar eventos para expandir/minimizar secciones
    setupSectionControls();
    
    // Configurar eventos para búsqueda y selección
    setupSearchAndSelection();
    
    // Configurar eventos para los modales de asignación
    setupAssignmentModals();
    
    // Configurar eventos para ver asignaciones
    setupViewAssignments();
});

/**
 * Inicializa el mapa de Google Maps y muestra las geocercas y vehículos
 */
function initMap() {
    var location = new google.maps.LatLng(19.4091657, -102.076571);
    var mapOptions = {
        zoom: 15,
        center: location,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    map = new google.maps.Map(document.getElementById('map'), mapOptions);
    
    // Mostrar geocercas en el mapa
    displayGeofences();
    
    // Mostrar vehículos en el mapa (simulados por ahora)
    displayVehicles();
}

/**
 * Muestra las geocercas en el mapa
 */
function displayGeofences() {
    if (!geofencesData || !map) return;
    
    // Crear un array de colores para las geocercas con mejor contraste visual
    const colors = ['#E53935', '#D81B60', '#8E24AA', '#5E35B1', '#3949AB', '#1E88E5', '#039BE5', '#00ACC1', '#00897B', '#43A047'];
    
    geofencesData.forEach(function(geofence, index) {
        const coordinates = geofence.coordinates.split('|').map(coord => {
            const [lat, lng] = coord.split(',');
            return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
        });

        // Seleccionar un color para la geocerca
        const colorIndex = index % colors.length;
        const color = colors[colorIndex];
        
        const polygon = new google.maps.Polygon({
            paths: coordinates,
            strokeColor: color,
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: color,
            fillOpacity: 0.35,
            editable: false
        });

        polygon.setMap(map);
        
        // Guardar referencia al polígono
        if (!window.geocercaPolygons) {
            window.geocercaPolygons = {};
        }
        window.geocercaPolygons[geofence.id] = polygon;
        
        // Agregar etiqueta a la geocerca
        const topPoint = coordinates.reduce((highest, coord) => {
            return coord.lat() > highest.lat() ? coord : highest;
        }, coordinates[0]);

        const tooltip = new google.maps.Marker({
            position: topPoint,
            map: map,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 0,
            },
            label: {
                text: geofence.name,
                color: '#000',
                fontSize: '14px',
                className: 'custom-tooltip',
            }
        });
        
        // Guardar referencia a la etiqueta
        if (!window.geocercaTooltips) {
            window.geocercaTooltips = {};
        }
        window.geocercaTooltips[geofence.id] = tooltip;
        
        // Agregar evento de clic para mostrar información
        google.maps.event.addListener(polygon, 'click', function() {
            showGeofenceInfo(geofence);
            
            // Resaltar la geocerca en la tabla
            highlightGeofenceInTable(geofence.id);
        });
    });
    
    // Evento para resaltar geocerca en el mapa al seleccionar checkbox
    $('.geocerca-checkbox').on('change', function() {
        const geocercaId = $(this).data('id');
        if ($(this).prop('checked')) {
            highlightGeofenceOnMap(geocercaId);
        } else {
            resetGeofencePolygon(geocercaId);
        }
    });
    
    // Evento para resaltar geocerca en el mapa al hacer hover en la fila
    $('.geocerca-item').hover(
        function() {
            const geocercaId = $(this).data('id');
            highlightGeofenceOnMap(geocercaId, true); // true = solo hover
        },
        function() {
            const geocercaId = $(this).data('id');
            // Solo restaurar si no está seleccionado
            if (!$(this).find('.geocerca-checkbox').prop('checked')) {
                resetGeofencePolygon(geocercaId);
            }
        }
    );
}

/**
 * Muestra los vehículos en el mapa (posiciones simuladas)
 */
function displayVehicles() {
    if (!vehiculosData || !map) return;
    
    // Crear un array de colores para los marcadores con mejor contraste visual
    const colors = ['#1E88E5', '#43A047', '#FB8C00', '#8E24AA', '#FDD835', '#E53935', '#00ACC1', '#3949AB', '#7CB342', '#C0CA33'];
    
    // Para cada vehículo, crear un marcador en una posición aleatoria cerca del centro del mapa
    vehiculosData.forEach(function(vehiculo, index) {
        // Simular una posición aleatoria cerca del centro del mapa
        const lat = map.getCenter().lat() + (Math.random() - 0.5) * 0.01;
        const lng = map.getCenter().lng() + (Math.random() - 0.5) * 0.01;
        const position = new google.maps.LatLng(lat, lng);
        
        // Seleccionar un color para el marcador
        const colorIndex = index % colors.length;
        const color = colors[colorIndex];
        
        // Crear el marcador con un diseño más distintivo
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.placa})`,
            icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                fillColor: color,
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#FFFFFF',
                scale: 6,
                rotation: Math.floor(Math.random() * 360) // Rotación aleatoria para simular dirección
            },
            animation: google.maps.Animation.DROP, // Animación al cargar
            zIndex: 10 // Mayor z-index para que estén por encima de las geocercas
        });
        
        // Guardar referencia al marcador
        vehiculoMarkers[vehiculo.id] = marker;
        
        // Agregar evento de clic para mostrar información
        google.maps.event.addListener(marker, 'click', function() {
            showVehicleInfo(vehiculo);
            
            // Resaltar el vehículo en la tabla
            highlightVehicleInTable(vehiculo.id);
        });
    });
}


/**
 * Muestra información de una geocerca al hacer clic en ella
 */
function showGeofenceInfo(geofence) {
    Swal.fire({
        title: geofence.name,
        text: geofence.description,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

/**
 * Resalta una geocerca en el mapa
 */
function highlightGeofenceOnMap(geocercaId, isHover = false) {
    const polygon = window.geocercaPolygons && window.geocercaPolygons[geocercaId];
    if (!polygon) return;
    
    // Guardar el estilo original si no se ha guardado ya
    if (!polygon.originalStyle) {
        polygon.originalStyle = {
            strokeColor: polygon.strokeColor,
            strokeOpacity: polygon.strokeOpacity,
            strokeWeight: polygon.strokeWeight,
            fillColor: polygon.fillColor,
            fillOpacity: polygon.fillOpacity
        };
    }
    
    // Aplicar estilo resaltado
    polygon.setOptions({
        strokeColor: isHover ? polygon.originalStyle.strokeColor : '#FFFF00',
        strokeOpacity: 1,
        strokeWeight: isHover ? 3 : 4,
        fillColor: isHover ? polygon.originalStyle.fillColor : '#FFFF00',
        fillOpacity: isHover ? 0.5 : 0.6,
        zIndex: 10
    });
    
    // Centrar el mapa en la geocerca si no es solo hover
    if (!isHover) {
        // Calcular el centro del polígono
        const bounds = new google.maps.LatLngBounds();
        polygon.getPath().forEach(function(latLng) {
            bounds.extend(latLng);
        });
        map.fitBounds(bounds);
    }
}

/**
 * Restaura el polígono de una geocerca a su estado original
 */
function resetGeofencePolygon(geocercaId) {
    const polygon = window.geocercaPolygons && window.geocercaPolygons[geocercaId];
    if (!polygon || !polygon.originalStyle) return;
    
    // Restaurar el estilo original
    polygon.setOptions(polygon.originalStyle);
}

/**
 * Resalta una geocerca en la tabla
 */
function highlightGeofenceInTable(geocercaId) {
    // Eliminar resaltado anterior
    $('.geocerca-item').removeClass('active-highlight');
    
    // Resaltar la fila de la geocerca
    $(`.geocerca-item[data-id="${geocercaId}"]`).addClass('active-highlight');
    
    // Hacer scroll a la fila si está fuera de vista
    const $row = $(`.geocerca-item[data-id="${geocercaId}"]`);
    if ($row.length) {
        const $container = $('#geocercas-section-content');
        const containerTop = $container.offset().top;
        const rowTop = $row.offset().top;
        
        if (rowTop < containerTop || rowTop > containerTop + $container.height()) {
            $container.animate({
                scrollTop: $container.scrollTop() + (rowTop - containerTop)
            }, 500);
        }
    }
}

/**
 * Muestra información de un vehículo al hacer clic en su marcador
 */
function showVehicleInfo(vehiculo) {
    Swal.fire({
        title: `${vehiculo.marca} ${vehiculo.modelo}`,
        text: `Placa: ${vehiculo.placa}`,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

/**
 * Configura los controles para expandir/minimizar secciones
 */
function setupSectionControls() {
    // Minimizar sección
    $('.minimize-section').on('click', function() {
        const sectionId = $(this).data('section');
        $(`#${sectionId}`).addClass('minimized');
    });
    
    // Maximizar sección
    $('.maximize-section').on('click', function() {
        const sectionId = $(this).data('section');
        $('.section').removeClass('maximized');
        $(`#${sectionId}`).removeClass('minimized').addClass('maximized');
    });
    
    // Alternar sección
    $('.toggle-section').on('click', function() {
        const sectionId = $(this).data('section');
        $(`#${sectionId}`).toggleClass('minimized');
    });
    
    // Expandir todas las secciones
    $('.expand-all').on('click', function() {
        $('.section').removeClass('minimized').removeClass('maximized');
    });
    
    // Minimizar todas las secciones
    $('.collapse-all').on('click', function() {
        $('.section').addClass('minimized').removeClass('maximized');
    });
}

/**
 * Configura los eventos para búsqueda y selección de elementos
 */
function setupSearchAndSelection() {
    // Búsqueda de vehículos con resaltado de coincidencias
    $('#searchVehiculos').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        // Si el término de búsqueda está vacío, mostrar todos los vehículos
        if (searchTerm === '') {
            $('.vehiculo-item').show();
            $('.vehiculo-item').removeClass('search-highlight');
            return;
        }
        
        $('.vehiculo-item').each(function() {
            const modelo = $(this).find('td:nth-child(2)').text().toLowerCase();
            const marca = $(this).find('td:nth-child(3)').text().toLowerCase();
            const placa = $(this).find('td:nth-child(4)').text().toLowerCase();
            
            if (modelo.includes(searchTerm) || marca.includes(searchTerm) || placa.includes(searchTerm)) {
                $(this).show();
                $(this).addClass('search-highlight');
                
                // Resaltar el vehículo en el mapa si existe
                const vehiculoId = $(this).data('id');
                highlightVehicleOnMap(vehiculoId);
            } else {
                $(this).hide();
                $(this).removeClass('search-highlight');
                
                // Restaurar el marcador original si existe
                const vehiculoId = $(this).data('id');
                resetVehicleMarker(vehiculoId);
            }
        });
    });
    
    // Búsqueda de geocercas con resaltado de coincidencias
    $('#searchGeocercas').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        // Si el término de búsqueda está vacío, mostrar todas las geocercas
        if (searchTerm === '') {
            $('.geocerca-item').show();
            $('.geocerca-item').removeClass('search-highlight');
            return;
        }
        
        $('.geocerca-item').each(function() {
            const nombre = $(this).find('td:nth-child(2)').text().toLowerCase();
            const descripcion = $(this).find('td:nth-child(3)').text().toLowerCase();
            
            if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
                $(this).show();
                $(this).addClass('search-highlight');
            } else {
                $(this).hide();
                $(this).removeClass('search-highlight');
            }
        });
    });
    
    // Seleccionar todos los vehículos
    $('#selectAllVehiculos').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.vehiculo-checkbox').prop('checked', isChecked);
        
        // Resaltar o restaurar todos los marcadores en el mapa
        if (isChecked) {
            $('.vehiculo-checkbox').each(function() {
                const vehiculoId = $(this).data('id');
                highlightVehicleOnMap(vehiculoId);
            });
        } else {
            $('.vehiculo-checkbox').each(function() {
                const vehiculoId = $(this).data('id');
                resetVehicleMarker(vehiculoId);
            });
        }
    });
    
    // Seleccionar todas las geocercas
    $('#selectAllGeocercas').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.geocerca-checkbox').prop('checked', isChecked);
    });
    
    // Seleccionar todos los vehículos en el modal
    $('#selectAllVehiculosModal').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.vehiculo-modal-checkbox').prop('checked', isChecked);
    });
    
    // Seleccionar todas las geocercas en el modal
    $('#selectAllGeocercasModal').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.geocerca-modal-checkbox').prop('checked', isChecked);
    });
    
    // Evento para resaltar vehículo en el mapa al hacer hover en la fila
    $('.vehiculo-item').hover(
        function() {
            const vehiculoId = $(this).data('id');
            highlightVehicleOnMap(vehiculoId, true); // true = solo hover
        },
        function() {
            const vehiculoId = $(this).data('id');
            // Solo restaurar si no está seleccionado
            if (!$(this).find('.vehiculo-checkbox').prop('checked')) {
                resetVehicleMarker(vehiculoId);
            }
        }
    );
    
    // Evento para resaltar vehículo en el mapa al seleccionar checkbox
    $('.vehiculo-checkbox').on('change', function() {
        const vehiculoId = $(this).data('id');
        if ($(this).prop('checked')) {
            highlightVehicleOnMap(vehiculoId);
        } else {
            resetVehicleMarker(vehiculoId);
        }
    });
    
    // Filtro rápido para vehículos en el modal
    $('#searchVehiculosModal').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#vehiculosListModal tr').each(function() {
            const modelo = $(this).find('td:nth-child(2)').text().toLowerCase();
            const marca = $(this).find('td:nth-child(3)').text().toLowerCase();
            const placa = $(this).find('td:nth-child(4)').text().toLowerCase();
            
            if (modelo.includes(searchTerm) || marca.includes(searchTerm) || placa.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filtro rápido para geocercas en el modal
    $('#searchGeocercasModal').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#geocercasListModal tr').each(function() {
            const nombre = $(this).find('td:nth-child(2)').text().toLowerCase();
            const descripcion = $(this).find('td:nth-child(3)').text().toLowerCase();
            
            if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
}

/**
 * Resalta un vehículo en el mapa
 */
function highlightVehicleOnMap(vehiculoId, isHover = false) {
    const marker = vehiculoMarkers[vehiculoId];
    if (!marker) return;
    
    // Guardar el icono original si no se ha guardado ya
    if (!marker.originalIcon) {
        marker.originalIcon = marker.getIcon();
    }
    
    // Crear un nuevo icono con mayor tamaño y brillo
    const icon = {
        ...marker.getIcon(),
        scale: isHover ? 8 : 10,
        strokeWeight: 3,
        strokeColor: '#FFFF00',
        fillColor: isHover ? marker.getIcon().fillColor : '#FFFF00'
    };
    
    // Aplicar el nuevo icono
    marker.setIcon(icon);
    
    // Animar el marcador
    if (!isHover) {
        marker.setAnimation(google.maps.Animation.BOUNCE);
        // Detener la animación después de un tiempo
        setTimeout(() => {
            marker.setAnimation(null);
        }, 1500);
    }
    
    // Centrar el mapa en el vehículo si no es solo hover
    if (!isHover) {
        map.panTo(marker.getPosition());
    }
}

/**
 * Restaura el marcador de un vehículo a su estado original
 */
function resetVehicleMarker(vehiculoId) {
    const marker = vehiculoMarkers[vehiculoId];
    if (!marker || !marker.originalIcon) return;
    
    // Restaurar el icono original
    marker.setIcon(marker.originalIcon);
    
    // Detener cualquier animación
    marker.setAnimation(null);
}

/**
 * Resalta un vehículo en la tabla
 */
function highlightVehicleInTable(vehiculoId) {
    // Eliminar resaltado anterior
    $('.vehiculo-item').removeClass('active-highlight');
    
    // Resaltar la fila del vehículo
    $(`.vehiculo-item[data-id="${vehiculoId}"]`).addClass('active-highlight');
    
    // Hacer scroll a la fila si está fuera de vista
    const $row = $(`.vehiculo-item[data-id="${vehiculoId}"]`);
    if ($row.length) {
        const $container = $('#vehiculos-section-content');
        const containerTop = $container.offset().top;
        const rowTop = $row.offset().top;
        
        if (rowTop < containerTop || rowTop > containerTop + $container.height()) {
            $container.animate({
                scrollTop: $container.scrollTop() + (rowTop - containerTop)
            }, 500);
        }
    }
}

/**
 * Configura los eventos para los modales de asignación
 */
function setupAssignmentModals() {
    // Abrir modal para asignar geocercas a vehículos
    $('#asignarGeocercasBtn').on('click', function() {
        // Obtener vehículos seleccionados
        selectedVehiculos = [];
        $('.vehiculo-checkbox:checked').each(function() {
            const vehiculoId = $(this).data('id');
            const modelo = $(this).closest('tr').find('td:nth-child(2)').text();
            const marca = $(this).closest('tr').find('td:nth-child(3)').text();
            const placa = $(this).closest('tr').find('td:nth-child(4)').text();
            
            selectedVehiculos.push({
                id: vehiculoId,
                modelo: modelo,
                marca: marca,
                placa: placa
            });
        });
        
        if (selectedVehiculos.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos un vehículo',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Mostrar vehículos seleccionados en el modal
        $('#vehiculosSeleccionados').empty();
        selectedVehiculos.forEach(function(vehiculo) {
            const item = $(`<div class="selected-item" data-id="${vehiculo.id}">
                ${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.placa})
                <span class="remove-item"><i class="fas fa-times"></i></span>
            </div>`);
            
            item.find('.remove-item').on('click', function() {
                item.remove();
                selectedVehiculos = selectedVehiculos.filter(v => v.id !== vehiculo.id);
            });
            
            $('#vehiculosSeleccionados').append(item);
        });
        
        // Limpiar campo de búsqueda
        $('#searchGeocercasModal').val('');
        $('#geocercasListModal tr').show();
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('asignarGeocercasModal'));
        modal.show();
    });}
    
    // Abrir modal para asignar vehículos a geocercas
    $('#asignarVehiculosBtn').on('click', function() {
        // Obtener geocercas seleccionadas
        selectedGeocercas = [];
        $('.geocerca-checkbox:checked').each(function() {
            const geocercaId = $(this).data('id');
            const nombre = $(this).closest('tr').find('td:nth-child(2)').text();
            const descripcion = $(this).closest('tr').find('td:nth-child(3)').text();
            
            selectedGeocercas.push({
                id: geocercaId,
                nombre: nombre,
                descripcion: descripcion
            });
        });
        
        if (selectedGeocercas.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos una geocerca',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Mostrar geocercas seleccionadas en el modal
        $('#geocercasSeleccionadas').empty();
        selectedGeocercas.forEach(function(geocerca) {
            const item = $(`<div class="selected-item" data-id="${geocerca.id}">
                ${geocerca.nombre}
                <span class="remove-item"><i class="fas fa-times"></i></span>
            </div>`);
            
            item.find('.remove-item').on('click', function() {
                item.remove();
                selectedGeocercas = selectedGeocercas.filter(g => g.id !== geocerca.id);
            });
            
            $('#geocercasSeleccionadas').append(item);
        });
        
        // Limpiar campo de búsqueda
        $('#searchVehiculosModal').val('');
        $('#vehiculosListModal tr').show();
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('asignarVehiculosModal'));
        modal.show();
    });
    
    // Previsualizar asignaciones antes de guardar (geocercas a vehículos)
    $('#previewAsignacionGeocercas').on('click', function() {
        // Obtener geocercas seleccionadas en el modal
        const geocercaIds = [];
        $('.geocerca-modal-checkbox:checked').each(function() {
            geocercaIds.push($(this).data('id'));
        });
        
        if (geocercaIds.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos una geocerca',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Filtrar las geocercas seleccionadas
        const geocercasSeleccionadas = geofencesData.filter(g => geocercaIds.includes(g.id));
        
        // Crear resumen de asignación
        let resumenHTML = '<div class="preview-container">';
        resumenHTML += '<h5>Resumen de asignación:</h5>';
        resumenHTML += '<div class="preview-section"><h6>Vehículos:</h6><ul>';
        
        selectedVehiculos.forEach(function(vehiculo) {
            resumenHTML += `<li>${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.placa})</li>`;
        });
        
        resumenHTML += '</ul></div><div class="preview-section"><h6>Geocercas a asignar:</h6><ul>';
        
        geocercasSeleccionadas.forEach(function(geocerca) {
            resumenHTML += `<li>${geocerca.name}</li>`;
        });
        
        resumenHTML += '</ul></div>';
        
        // Mostrar opción de eliminar asignaciones existentes
        const eliminarExistentes = $('#eliminarAsignacionesExistentesGeocercas').prop('checked');
        resumenHTML += `<div class="preview-section"><p><strong>Eliminar asignaciones existentes:</strong> ${eliminarExistentes ? 'Sí' : 'No'}</p></div>`;
        
        resumenHTML += '</div>';
        
        // Mostrar vista previa
        Swal.fire({
            title: 'Vista previa de asignación',
            html: resumenHTML,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Confirmar y guardar',
            cancelButtonText: 'Volver a editar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si confirma, hacer clic en el botón de guardar
                $('#guardarAsignacionGeocercas').click();
            }
        });
    });
    
    // Guardar asignación de geocercas a vehículos
    $('#guardarAsignacionGeocercas').on('click', function() {
        // Obtener geocercas seleccionadas en el modal
        const geocercaIds = [];
        $('.geocerca-modal-checkbox:checked').each(function() {
            geocercaIds.push($(this).data('id'));
        });
        
        if (geocercaIds.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos una geocerca',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Obtener IDs de vehículos seleccionados
        const vehiculoIds = selectedVehiculos.map(v => v.id);
        
        // Verificar si se deben eliminar asignaciones existentes
        const eliminarExistentes = $('#eliminarAsignacionesExistentesGeocercas').prop('checked');
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere mientras se guardan las asignaciones',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Para cada geocerca seleccionada, asignar los vehículos
        const promises = geocercaIds.map(geocercaId => {
            return $.ajax({
                url: '/vehiculo-geocerca/asignar-vehiculos',
                type: 'POST',
                data: {
                    geocerca_id: geocercaId,
                    vehiculo_ids: vehiculoIds,
                    eliminar_existentes: eliminarExistentes
                }
            });
        });
        
        // Cuando todas las asignaciones se completen
        Promise.all(promises)
            .then(responses => {
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('asignarGeocercasModal'));
                modal.hide();
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    title: 'Éxito',
                    text: 'Asignaciones guardadas correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Recargar la página para actualizar las asignaciones
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error al guardar asignaciones:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al guardar las asignaciones',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
    });
    
    // Previsualizar asignaciones antes de guardar (vehículos a geocercas)
    $('#previewAsignacionVehiculos').on('click', function() {
        // Obtener vehículos seleccionados en el modal
        const vehiculoIds = [];
        $('.vehiculo-modal-checkbox:checked').each(function() {
            vehiculoIds.push($(this).data('id'));
        });
        
        if (vehiculoIds.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos un vehículo',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Filtrar los vehículos seleccionados
        const vehiculosSeleccionados = vehiculosData.filter(v => vehiculoIds.includes(v.id));
        
        // Crear resumen de asignación
        let resumenHTML = '<div class="preview-container">';
        resumenHTML += '<h5>Resumen de asignación:</h5>';
        resumenHTML += '<div class="preview-section"><h6>Geocercas:</h6><ul>';
        
        selectedGeocercas.forEach(function(geocerca) {
            resumenHTML += `<li>${geocerca.nombre}</li>`;
        });
        
        resumenHTML += '</ul></div><div class="preview-section"><h6>Vehículos a asignar:</h6><ul>';
        
        vehiculosSeleccionados.forEach(function(vehiculo) {
            resumenHTML += `<li>${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.placa})</li>`;
        });
        
        resumenHTML += '</ul></div>';
        
        // Mostrar opción de eliminar asignaciones existentes
        const eliminarExistentes = $('#eliminarAsignacionesExistentesVehiculos').prop('checked');
        resumenHTML += `<div class="preview-section"><p><strong>Eliminar asignaciones existentes:</strong> ${eliminarExistentes ? 'Sí' : 'No'}</p></div>`;
        
        resumenHTML += '</div>';
        
        // Mostrar vista previa
        Swal.fire({
            title: 'Vista previa de asignación',
            html: resumenHTML,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Confirmar y guardar',
            cancelButtonText: 'Volver a editar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si confirma, hacer clic en el botón de guardar
                $('#guardarAsignacionVehiculos').click();
            }
        });
    });
    
    // Guardar asignación de vehículos a geocercas
    $('#guardarAsignacionVehiculos').on('click', function() {
        // Obtener vehículos seleccionados en el modal
        const vehiculoIds = [];
        $('.vehiculo-modal-checkbox:checked').each(function() {
            vehiculoIds.push($(this).data('id'));
        });
        
        if (vehiculoIds.length === 0) {
            Swal.fire({
                title: 'Selección requerida',
                text: 'Por favor seleccione al menos un vehículo',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Obtener IDs de geocercas seleccionadas
        const geocercaIds = selectedGeocercas.map(g => g.id);
        
        // Verificar si se deben eliminar asignaciones existentes
        const eliminarExistentes = $('#eliminarAsignacionesExistentesVehiculos').prop('checked');
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere mientras se guardan las asignaciones',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Para cada vehículo seleccionado, asignar las geocercas
        const promises = vehiculoIds.map(vehiculoId => {
            return $.ajax({
                url: '/vehiculo-geocerca/asignar-geocercas',
                type: 'POST',
                data: {
                    vehiculo_id: vehiculoId,
                    geocerca_ids: geocercaIds,
                    eliminar_existentes: eliminarExistentes
                }
            });
        });
        
        // Cuando todas las asignaciones se completen
        Promise.all(promises)
            .then(responses => {
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('asignarVehiculosModal'));
                modal.hide();
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    title: 'Éxito',
                    text: 'Asignaciones guardadas correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Recargar la página para actualizar las asignaciones
                    location.reload();
                });
            })
            .catch(error => {
                console.error('Error al guardar asignaciones:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al guardar las asignaciones',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
    });


/**
 * Configura los eventos para ver asignaciones existentes
 */
function setupViewAssignments() {
    // Ver geocercas asignadas a un vehículo
    $('.ver-geocercas').on('click', function() {
        const vehiculoId = $(this).data('id');
        const vehiculoNombre = $(this).closest('tr').find('td:nth-child(2)').text() + ' ' + 
                             $(this).closest('tr').find('td:nth-child(3)').text() + ' (' + 
                             $(this).closest('tr').find('td:nth-child(4)').text() + ')';
        
        // Actualizar título del modal
        $('#verGeocercasModalLabel').text('Geocercas asignadas a: ' + vehiculoNombre);
        
        // Mostrar indicador de carga
        $('#geocercasAsignadas').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        
        // Obtener geocercas asignadas
        $.ajax({
            url: '/vehiculo-geocerca/get-geocercas-vehiculo',
            type: 'GET',
            data: { vehiculoId: vehiculoId }
        })
        .done(function(response) {
            if (response.success) {
                const geocercaIds = response.geocerca_ids;
                
                if (geocercaIds.length === 0) {
                    $('#geocercasAsignadas').html('<div class="alert alert-info">No hay geocercas asignadas a este vehículo</div>');
                    return;
                }
                
                // Filtrar las geocercas asignadas
                const geocercasAsignadas = geofencesData.filter(g => geocercaIds.includes(g.id));
                
                // Mostrar las geocercas asignadas
                $('#geocercasAsignadas').empty();
                geocercasAsignadas.forEach(function(geocerca) {
                    $('#geocercasAsignadas').append(`
                        <div class="list-group-item">
                            <h5 class="mb-1">${geocerca.name}</h5>
                            <p class="mb-1">${geocerca.description}</p>
                        </div>
                    `);
                });
            } else {
                $('#geocercasAsignadas').html('<div class="alert alert-danger">Error al obtener las geocercas asignadas</div>');
            }
        })
        .fail(function() {
            $('#geocercasAsignadas').html('<div class="alert alert-danger">Error de conexión al obtener las geocercas asignadas</div>');
        });
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('verGeocercasModal'));
        modal.show();
    });
    
    // Ver vehículos asignados a una geocerca
    $('.ver-vehiculos').on('click', function() {
        const geocercaId = $(this).data('id');
        const geocercaNombre = $(this).closest('tr').find('td:nth-child(2)').text();
        
        // Actualizar título del modal
        $('#verVehiculosModalLabel').text('Vehículos asignados a: ' + geocercaNombre);
        
        // Mostrar indicador de carga
        $('#vehiculosAsignados').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        
        // Obtener vehículos asignados
        $.ajax({
            url: '/vehiculo-geocerca/get-vehiculos-geocerca',
            type: 'GET',
            data: { geocercaId: geocercaId }
        })
        .done(function(response) {
            if (response.success) {
                const vehiculoIds = response.vehiculo_ids;
                
                if (vehiculoIds.length === 0) {
                    $('#vehiculosAsignados').html('<div class="alert alert-info">No hay vehículos asignados a esta geocerca</div>');
                    return;
                }
                
                // Filtrar los vehículos asignados
                const vehiculosAsignados = vehiculosData.filter(v => vehiculoIds.includes(v.id));
                
                // Mostrar los vehículos asignados
                $('#vehiculosAsignados').empty();
                vehiculosAsignados.forEach(function(vehiculo) {
                    $('#vehiculosAsignados').append(`
                        <div class="list-group-item">
                            <h5 class="mb-1">${vehiculo.marca} ${vehiculo.modelo}</h5>
                            <p class="mb-1">Placa: ${vehiculo.placa}</p>
                        </div>
                    `);
                });
            } else {
                $('#vehiculosAsignados').html('<div class="alert alert-danger">Error al obtener los vehículos asignados</div>');
            }
        })
        .fail(function() {
            $('#vehiculosAsignados').html('<div class="alert alert-danger">Error de conexión al obtener los vehículos asignados</div>');
        });
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('verVehiculosModal'));
        modal.show();
    });
}