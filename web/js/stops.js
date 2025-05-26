function initStops() {
   console.log("initStops called");

    // Inicializar Flatpickr
    flatpickr('#startDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true, 
    });

    flatpickr('#endDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true,
    });

    const filter = document.getElementById('filter');
    const customDates = document.querySelectorAll('.custom-dates');
    const deviceColumn = document.querySelector('.col-lg-2.col-md-4.col-12').nextElementSibling;
    const showButtonColumn = deviceColumn.nextElementSibling;
    const exportButtonColumn = showButtonColumn.nextElementSibling;


    // Función para ajustar la visibilidad y el orden de los campos de fecha
    function adjustDateFields() {
      //  console.log("adjustDateFields called");
       // console.log("Filter value:", filter.value);
        if (filter.value === 'custom') {
            customDates.forEach(function(dateField) {
                dateField.style.display = 'block';
            });
            // Mover los campos de fecha después del dispositivo
            deviceColumn.after(...customDates);
            // Mover el botón de mostrar después de los campos de fecha
            customDates[customDates.length - 1].after(showButtonColumn);
            // Mover el botón de exportar después del botón de mostrar
            showButtonColumn.after(exportButtonColumn);
        } else {
            customDates.forEach(function(dateField) {
                dateField.style.display = 'none';
            });
            // Restaurar el orden original
            deviceColumn.after(showButtonColumn);
            showButtonColumn.after(exportButtonColumn);
        }
    }

    filter.addEventListener('change', function () {
    //    console.log("Filter change event detected");
        adjustDateFields();
    });

    // Mostrar las fechas si ya estaban seleccionadas como "Personalizado"
    if (filter.value === 'custom') {
        adjustDateFields();
    }
}

// Llamar a initStops() en la carga inicial
document.addEventListener('DOMContentLoaded', function () {
  //  console.log("DOMContentLoaded event fired");
    initStops();
    
    // Configurar los eventos de pjax una sola vez
    setupPjaxEvents();
});

// Configurar eventos de pjax
function setupPjaxEvents() {
    // Eliminar manejadores existentes para evitar duplicados
    $(document).off('pjax:complete.stopsCheck');
    $(document).off('pjax:success.stopsCheck');
    
    // Registrar nuevos manejadores con namespace para poder eliminarlos después
    $(document).on('pjax:complete.stopsCheck', function() {
    //    console.log('pjax:complete triggered with namespace');
        setTimeout(function() {
            initStops();
            checkForEmptyResults();
        }, 100); // Pequeño retraso para asegurar que el DOM esté actualizado
    });
    
    // También escuchar pjax:success como respaldo
    $(document).on('pjax:success.stopsCheck', function() {
      //  console.log('pjax:success triggered with namespace');
        setTimeout(function() {
            initStops();
            checkForEmptyResults();
        }, 100);
    });
}

// Función separada para verificar resultados vacíos
function checkForEmptyResults() {
   // console.log('Checking for empty results');
    
    // Verificar si hay parámetros en la URL (indica que se realizó una búsqueda)
    const urlParams = new URLSearchParams(window.location.search);
    //console.log('URL params:', Object.fromEntries(urlParams));
    
    const hasSearchParams = urlParams.has('filter') || urlParams.has('gps');
    //console.log('Has search params:', hasSearchParams);
    
    // Solo mostrar el mensaje si hay parámetros de búsqueda y no hay resultados
    const tableRows = document.querySelectorAll('#projects-tbls tbody tr');
    //console.log('Table rows found:', tableRows.length);
    
    // Verificar también si hay un mensaje de "No data available"
    const noDataMessage = document.querySelector('#projects-tbls tbody tr td.dataTables_empty');
    const hasNoResults = tableRows.length === 0 || noDataMessage !== null;
    //console.log('Has no results:', hasNoResults, 'Empty message found:', noDataMessage !== null);
    
    if (hasNoResults && hasSearchParams) {
      //  console.log('Showing SweetAlert - No data');
        Swal.fire({
            title: 'Sin datos',
            text: 'No hay información de paradas disponible para el período y dispositivo seleccionados.',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }
}

// Function to initialize the map with stops and route
async function initStopsMap() {
    // Create map
    var map = L.map('stops-map').setView([0, 0], 13);
    
    // Add Google Maps layer
    var googleStreets = L.gridLayer.googleMutant({
        type: 'roadmap'
    }).addTo(map);
    
    // Add OpenStreetMap as fallback/alternative
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Create markers for stops
    var stopMarkers = [];
    var stopCoordinates = [];
    var bounds = L.latLngBounds();
    var allStops = [];
    var hasPagination = false;
    
    // Verificar si hay paginación
    const totalCountElement = document.querySelector('.text-center.text-muted');
    if (totalCountElement) {
        const totalCountText = totalCountElement.textContent;
        const match = totalCountText.match(/de (\d+) registros/);
        if (match) {
            const totalRows = parseInt(match[1]);
            const visibleRows = document.querySelectorAll('#projects-tbls tbody tr').length;
            hasPagination = totalRows > visibleRows;
            console.log('La tabla tiene paginación, hay más datos que los mostrados actualmente');
        }
    }
    
    // Si hay paginación, obtener todos los datos mediante AJAX
    if (hasPagination) {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            // Crear una URL para obtener todas las paradas sin paginación
            const ajaxUrl = `/gpsreport/get-all-stops?filter=${urlParams.get('filter') || 'today'}&gps=${urlParams.get('gps') || ''}&startDate=${urlParams.get('startDate') || ''}&endDate=${urlParams.get('endDate') || ''}`;
            
            console.log('Obteniendo todas las paradas desde:', ajaxUrl);
            const response = await fetch(ajaxUrl);
            if (response.ok) {
                allStops = await response.json();
                console.log(`Datos completos cargados: ${allStops.length} paradas`);
            }
        } catch (error) {
            console.error('Error al obtener todas las paradas:', error);
            // Si falla, continuamos con los datos de la página actual
            allStops = [];
        }
    }
    
    // Si no se pudieron obtener todos los datos o no hay paginación, usar los datos visibles
    if (allStops.length === 0) {
        // Extraer datos de las filas visibles
        const tableRows = document.querySelectorAll('#projects-tbls tbody tr');
        tableRows.forEach((row, index) => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 4) {
                const locationLink = cells[3].querySelector('a');
                if (locationLink) {
                    const href = locationLink.getAttribute('href');
                    const coordsMatch = href.match(/q=([\d.-]+),([\d.-]+)/);
                    if (coordsMatch) {
                        const lat = parseFloat(coordsMatch[1]);
                        const lng = parseFloat(coordsMatch[2]);
                        const startTime = cells[0].textContent.trim();
                        const endTime = cells[1].textContent.trim();
                        const duration = cells[2].textContent.trim();
                        
                        allStops.push({
                            start_time: startTime,
                            end_time: endTime,
                            duration: duration,
                            latitude: lat,
                            longitude: lng
                        });
                    }
                }
            }
        });
    }
    
    // Si no hay datos, mostrar mensaje
    if (allStops.length === 0) {
        document.getElementById('stops-map').innerHTML = '<div class="alert alert-info">No hay datos de paradas disponibles para mostrar en el mapa.</div>';
        return;
    }
    
    // Crear marcadores y líneas para todas las paradas
    allStops.forEach((stop, index) => {
        const lat = parseFloat(stop.latitude);
        const lng = parseFloat(stop.longitude);
        
        // Usar marcador rojo para las paradas
        const marker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        
        // Formatear la duración para el popup
        let durationText = stop.duration;
        if (typeof durationText === 'number') {
            if (durationText >= 3600) {
                const hours = Math.floor(durationText / 3600);
                const minutes = Math.floor((durationText % 3600) / 60);
                const seconds = durationText % 60;
                durationText = `${hours} horas, ${minutes} minutos, ${seconds} segundos`;
            } else {
                const minutes = Math.floor(durationText / 60);
                const seconds = durationText % 60;
                durationText = `${minutes} minutos, ${seconds} segundos`;
            }
        }
        
        marker.bindPopup(
            `<strong>Parada #${index + 1}</strong><br>` +
            `Inicio: ${stop.start_time}<br>` +
            `Fin: ${stop.end_time || 'En curso'}<br>` +
            `Duración: ${durationText}<br>` +
            `<a href='https://www.google.com/maps?q=${lat},${lng}' target='_blank'>Ver en Google Maps</a>`
        );
        
        stopMarkers.push(marker);
        stopCoordinates.push([lat, lng]);
        bounds.extend([lat, lng]);
    });


    // Ajustar el mapa a los límites de las paradas
    if (stopCoordinates.length > 0) {
        map.fitBounds(bounds, {padding: [50, 50]});
    }

    // Agregar leyenda (solo marcadores)
    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'info legend');
        div.innerHTML = 
            '<img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png" height="20"> Paradas';
        return div;
    };
    legend.addTo(map);

    // Agregar mensaje informativo sobre la ruta completa si hay paginación
    if (hasPagination) {
        const infoControl = L.control({position: 'topright'});
        infoControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'info-control');
            div.innerHTML = `
                <div class="alert alert-info p-2 m-0" style="font-size: 0.9rem; opacity: 0.9;">
                    <i class="fa fa-info-circle"></i> Mostrando todas las paradas (${allStops.length} puntos)
                </div>
            `;
            return div;
        };
        infoControl.addTo(map);
    }
}

function confirmExport() {
    Swal.fire({
        title: '¿Incluir gráfica?',
        text: "¿Deseas incluir la gráfica en el reporte?",
        icon: 'question',
        showCancelButton: true,
        showCloseButton: true, // Mostrar botón de cierre
        confirmButtonText: 'Sí, incluir',
        cancelButtonText: 'No, solo datos'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir con includeChart=true
        //    console.log('El usuario eligió incluir la gráfica.');
            window.location.href = '/gpsreport/download-report-stops' + 
                '?filter=' + encodeURIComponent($('#filter').val()) + 
                '&gps=' + encodeURIComponent($('#gps').val()) + 
                '&startDate=' + encodeURIComponent($('#startDate').val()) + 
                '&endDate=' + encodeURIComponent($('#endDate').val()) + 
                '&includeChart=true';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Redirigir con includeChart=false
          ///  console.log('El usuario eligió no incluir la gráfica.');
            window.location.href = '/gpsreport/download-report-stops' + 
                '?filter=' + encodeURIComponent($('#filter').val()) + 
                '&gps=' + encodeURIComponent($('#gps').val()) + 
                '&startDate=' + encodeURIComponent($('#startDate').val()) + 
                '&endDate=' + encodeURIComponent($('#endDate').val()) + 
                '&includeChart=false';
        }
    });
    return false; 
}


