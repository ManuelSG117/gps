// Inicialización cuando el documento está listo
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr
    initFlatpickr();

    // Setup filter change event
    setupFilterChangeEvent();

    // Initialize the map if we have location data
    initMap();

    // Setup form submission event for loading screen
    setupFormSubmissionEvent();

    // Setup Pjax events
    $(document).on('pjax:success', function() {
        initFlatpickr();
        setupFilterChangeEvent();
        initMap();
        hideLoadingScreen();
    });
    
    // Evento separado para verificar datos después de pjax
    $(document).on('pjax:complete', function() {
        // Agregar logs para depuración
        console.log('Evento pjax:complete disparado');
        
        // Verificar si hay parámetros en la URL (indica que se realizó una búsqueda)
        const urlParams = new URLSearchParams(window.location.search);
        console.log('Parámetros URL:', Object.fromEntries(urlParams));
        const hasSearchParams = urlParams.has('filter') || urlParams.has('gps');
        console.log('¿Tiene parámetros de búsqueda?:', hasSearchParams);
        
        // Solo mostrar el mensaje si hay parámetros de búsqueda y no hay resultados
        const tableRows = document.querySelectorAll('#projects-tbl tbody tr');
        console.log('Número de filas encontradas:', tableRows.length);
        
        if (tableRows.length === 0 && hasSearchParams) {
            console.log('Mostrando SweetAlert - Sin datos');
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay información de ubicación disponible para el período y dispositivo seleccionados.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }
        
        // Ocultar pantalla de carga
        hideLoadingScreen();
    });
});

// Función para mostrar la pantalla de carga
function showLoadingScreen() {
    // Crear el elemento de pantalla de carga si no existe
    if (!document.getElementById('loading-screen')) {
        // Primero, asegurarse de que el script de dotlottie esté cargado
        if (!document.querySelector('script[src*="dotlottie-player"]')) {
            const script = document.createElement('script');
            script.src = "https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs";
            script.type = "module";
            document.head.appendChild(script);
        }
        
        const loadingScreen = document.createElement('div');
        loadingScreen.id = 'loading-screen';
        loadingScreen.innerHTML = `
            <div class="loading-content">
                <dotlottie-player src="https://lottie.host/5ee4a06b-91b8-4a89-b8a6-7a1ea7f47c47/nJPJm4QWbB.lottie" 
                    background="transparent" 
                    speed="1" 
                    style="width: 300px; height: 300px" 
                    loop 
                    autoplay>
                </dotlottie-player>
                <h4 class="mt-3">Cargando datos...</h4>
            </div>
        `;
        document.body.appendChild(loadingScreen);
    } else {
        document.getElementById('loading-screen').style.display = 'flex';
    }
}

// Función para ocultar la pantalla de carga
function hideLoadingScreen() {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.style.display = 'none';
    }
}

// Configurar evento de envío del formulario
function setupFormSubmissionEvent() {
    const form = document.querySelector('.gps-report-form form');
    if (form) {
        form.addEventListener('submit', function() {
            showLoadingScreen();
        });
    }
}

function initFlatpickr() {
    flatpickr('#startDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true, // Permitir edición manual
    });

    flatpickr('#endDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true,
    });
}

function setupFilterChangeEvent() {
    const filter = document.getElementById('filter');
    if (!filter) return;

    const customDates = document.querySelector('.custom-dates');
    const dateFields = document.querySelectorAll('.custom-dates .form-control');
    
    filter.addEventListener('change', function() {
        if (filter.value === 'custom') {
            customDates.style.display = 'flex';
            // Ajustar el tamaño de las columnas
            dateFields.forEach(function(field) {
                const parentCol = field.closest('.date-field-container');
                if (parentCol) {
                    parentCol.classList.remove('col-6');
                    // Add each class individually instead of all at once
                    parentCol.classList.add('col-lg-2');
                    parentCol.classList.add('col-md-4');
                    parentCol.classList.add('col-12');
                }
            });
        } else {
            customDates.style.display = 'none';
        }
    });

    // Mostrar las fechas si ya estaban seleccionadas como "Personalizado"
    if (filter.value === 'custom') {
        customDates.style.display = 'flex';
        dateFields.forEach(function(field) {
            const parentCol = field.closest('.date-field-container');
            if (parentCol) {
                parentCol.classList.remove('col-6');
                // Add each class individually here too
                parentCol.classList.add('col-lg-2');
                parentCol.classList.add('col-md-4');
                parentCol.classList.add('col-12');
            }
        });
    }
}

async function initMap() {
    // Check if we have location data
    const tableRows = document.querySelectorAll('#projects-tbl tbody tr');
    if (tableRows.length === 0 || tableRows[0].cells.length <= 1) {
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.innerHTML = '<div class="alert alert-info">No hay datos de ubicación disponibles para mostrar en el mapa.</div>';
        }
        
    
        return;
    }

    // Extract location data from the table
    const locations = [];
    tableRows.forEach(row => {
        if (row.cells.length >= 3) {
            const lat = parseFloat(row.cells[1].textContent.trim());
            const lng = parseFloat(row.cells[2].textContent.trim());
            const timestamp = row.cells[0].textContent.trim();
            const speed = parseFloat(row.cells[3].textContent.trim());

            if (!isNaN(lat) && !isNaN(lng)) {
                locations.push({
                    lat: lat,
                    lng: lng,
                    timestamp: timestamp,
                    speed: speed
                });
            }
        }
    });

    if (locations.length === 0) {
        document.getElementById('map').innerHTML = '<div class="alert alert-info">No se pudieron extraer coordenadas válidas de los datos.</div>';
        return;
    }

    try {
        // Clear previous map if exists
        const mapContainer = document.getElementById('map');
        mapContainer.innerHTML = '';

        // Initialize the map with Leaflet
        const map = L.map('map', {
            minZoom: 2,
            maxZoom: 18
        }).setView([locations[0].lat, locations[0].lng], 13);

        // Add Google Maps layer
        const googleStreets = L.gridLayer.googleMutant({
            type: 'roadmap'
        }).addTo(map);

        // Add OpenStreetMap as fallback/alternative
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Create a polyline for the route
        const routeCoordinates = locations.map(loc => [loc.lat, loc.lng]);
        const routeLine = L.polyline(routeCoordinates, {
            color: 'blue',
            weight: 4,
            opacity: 0.7
        }).addTo(map);

        // Add markers for start and end points
        const startMarker = L.marker([locations[0].lat, locations[0].lng], {
            title: 'Inicio: ' + locations[0].timestamp,
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        startMarker.bindPopup(`<b>Punto de inicio</b><br>Fecha: ${locations[0].timestamp}<br>Velocidad: ${locations[0].speed} km/h`);

        const endMarker = L.marker([locations[locations.length - 1].lat, locations[locations.length - 1].lng], {
            title: 'Fin: ' + locations[locations.length - 1].timestamp,
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        endMarker.bindPopup(`<b>Punto final</b><br>Fecha: ${locations[locations.length - 1].timestamp}<br>Velocidad: ${locations[locations.length - 1].speed} km/h`);

        // Add intermediate markers with speed info
        for (let i = 1; i < locations.length - 1; i += Math.max(1, Math.floor(locations.length / 10))) {
            const marker = L.marker([locations[i].lat, locations[i].lng], {
                opacity: 0.7,
                title: locations[i].timestamp
            }).addTo(map);
            marker.bindPopup(`<b>Punto intermedio</b><br>Fecha: ${locations[i].timestamp}<br>Velocidad: ${locations[i].speed} km/h`);
        }

        // Fit the map to show all the route
        map.fitBounds(routeLine.getBounds(), {
            padding: [50, 50]
        });

        // Add a legend
        const legend = L.control({
            position: 'bottomright'
        });
        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend');
            div.innerHTML = `
            <div style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
                <div><span style="color: green; font-size: 20px;">●</span> Inicio</div>
                <div><span style="color: blue; font-size: 20px;">―</span> Ruta</div>
                <div><span style="color: red; font-size: 20px;">●</span> Fin</div>
            </div>
        `;
            return div;
        };
        legend.addTo(map);

        // Forzar actualización del tamaño del mapa después de renderizar
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

    } catch (error) {
        console.error('Error initializing map:', error);
        document.getElementById('map').innerHTML = `<div class="alert alert-danger">Error al inicializar el mapa: ${error.message}</div>`;
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
            window.location.href = exportUrlWithChart;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Redirigir con includeChart=false
            window.location.href = exportUrlWithoutChart;
        }
        // No hacer nada si se cierra el diálogo con la "X" o fuera del modal
    });
    return false; // Prevenir la acción por defecto del enlace
}

function showAddress(lat, lng, element) {
    console.log("Click detectado. Latitud:", lat, "Longitud:", lng);

    // Seleccionar el <span> asociado
    const span = element.parentElement.querySelector('.address-result');

    if (!span) {
        console.error("No se encontró el elemento <span> para mostrar la dirección.");
        return;
    }

    // Verificar si ya contiene texto
    if (span.textContent.trim() === "") {
        console.log("Iniciando búsqueda de dirección...");
        span.textContent = "Buscando...";

        const apiUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`;
        console.log("URL de la API:", apiUrl);

        fetch(apiUrl)
            .then(response => {
                console.log("Respuesta recibida de la API:", response);
                if (!response.ok) {
                    throw new Error(`Error al contactar la API (${response.status})`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Datos obtenidos de la API:", data);

                if (data) {
                    if (data.name) {
                        console.log("Campo 'name' encontrado:", data.name);
                        span.textContent = data.name; // Mostrar el campo `name`
                    } else if (data.display_name) {
                        console.log("Campo 'display_name' encontrado:", data.display_name);
                        span.textContent = data.display_name; // Alternativa
                    } else {
                        console.warn("Ni 'name' ni 'display_name' están disponibles en la respuesta.");
                        span.textContent = "Nombre no disponible";
                    }

                    // Eliminar el enlace de "Mostrar calle"
                    element.parentElement.removeChild(element);
                } else {
                    console.warn("La API no devolvió datos válidos.");
                    span.textContent = "Datos no disponibles";
                }
            })
            .catch(error => {
                console.error("Error al obtener la dirección:", error);
                span.textContent = "Error al obtener la dirección";
            });
    } else {
        console.log("La dirección ya fue cargada anteriormente:", span.textContent);
    }
}

// Eventos de redimensionamiento y carga
window.addEventListener('resize', function() {
    // Obtener el mapa de Leaflet si existe
    const mapContainer = document.getElementById('map');
    if (mapContainer && mapContainer._leaflet_id) {
        const leafletMap = L.DomUtil.get('map')._leaflet;
        if (leafletMap) {
            leafletMap.invalidateSize();
        }
    }
});

// También asegurar que el mapa se actualice después de cargar completamente la página
window.addEventListener('load', function() {
    setTimeout(function() {
        const mapContainer = document.getElementById('map');
        if (mapContainer && mapContainer._leaflet_id) {
            const leafletMap = L.DomUtil.get('map')._leaflet;
            if (leafletMap) {
                leafletMap.invalidateSize();
            }
        }
    }, 500);
});

// Variables globales para las URLs de exportación
let exportUrlWithChart = '';
let exportUrlWithoutChart = '';

// Agregar estilos para la pantalla de carga
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-content {
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    `;
    document.head.appendChild(style);
    
    // Precargar el script de dotlottie para que esté disponible cuando se necesite
    const script = document.createElement('script');
    script.src = "https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs";
    script.type = "module";
    document.head.appendChild(script);
});

// Asegurarse de que las URLs de exportación incluyan el parámetro gps
$(document).on('pjax:complete', function() {
    // Actualizar las URLs de exportación con el parámetro gps actual
    const urlParams = new URLSearchParams(window.location.search);
    const currentGps = urlParams.get('gps') || 'all';
    
    // Actualizar las URLs globales si están definidas en el script
    if (typeof exportUrlWithChart !== 'undefined' && typeof exportUrlWithoutChart !== 'undefined') {
        // Crear nuevas URLs con el parámetro gps actualizado
        let urlWithChart = new URL(exportUrlWithChart, window.location.origin);
        let urlWithoutChart = new URL(exportUrlWithoutChart, window.location.origin);
        
        // Actualizar el parámetro gps
        urlWithChart.searchParams.set('gps', currentGps);
        urlWithoutChart.searchParams.set('gps', currentGps);
        
        // Asignar las nuevas URLs
        exportUrlWithChart = urlWithChart.toString();
        exportUrlWithoutChart = urlWithoutChart.toString();
    }
});