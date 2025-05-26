// Función para calcular la distancia entre dos puntos geográficos en kilómetros
function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radio de la Tierra en km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
              Math.sin(dLon/2) * Math.sin(dLon/2); 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    const d = R * c; // Distancia en km
    return d;
}

// Convertir grados a radianes
function deg2rad(deg) {
    return deg * (Math.PI/180);
}

// Convertir milisegundos a formato horas:minutos:segundos
function msToHMS(ms) {
    // Convertir a segundos
    let seconds = Math.floor(ms / 1000);
    // Extraer horas
    const hours = Math.floor(seconds / 3600);
    seconds = seconds % 3600;
    // Extraer minutos
    const minutes = Math.floor(seconds / 60);
    // Mantener segundos
    seconds = seconds % 60;
    // Formatear resultado
    return `${hours}h ${minutes}m ${seconds}s`;
}

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
    
    // Setup export button visibility
    setupExportButtonVisibility();

    // Setup Pjax events
    $(document).on('pjax:success', function() {
        initFlatpickr();
        setupFilterChangeEvent();
        initMap();
        setupExportButtonVisibility();
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
        
        // Controlar la visibilidad del botón de exportar
        const exportButton = document.querySelector('.btn-export-excel');
        if (exportButton) {
            if (tableRows.length > 0) {
                exportButton.style.display = 'block';
            } else {
                exportButton.style.display = 'none';
            }
        }
        
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
    // Verificar si hay datos de ubicación disponibles
    const allTableRows = document.querySelectorAll('#projects-tbl tbody tr');
    if (allTableRows.length === 0 || allTableRows[0].cells.length <= 1) {
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.innerHTML = '<div class="alert alert-info">No hay datos de ubicación disponibles para mostrar en el mapa.</div>';
        }
        // Ocultar panel de estadísticas
        document.getElementById('route-stats-cards').style.display = 'none';
        return;
    }

    // Obtener todos los datos de ubicación (no solo los de la página actual)
    const locations = [];
    
    // Primero intentamos obtener todos los datos de la tabla completa (incluyendo datos ocultos por paginación)
    const totalCountElement = document.querySelector('.text-center.text-muted');
    let hasPagination = false;
    
    if (totalCountElement) {
        const totalCountText = totalCountElement.textContent;
        const match = totalCountText.match(/de (\d+) registros/);
        if (match && parseInt(match[1]) > allTableRows.length) {
            hasPagination = true;
            console.log('La tabla tiene paginación, hay más datos que los mostrados actualmente');
        }
    }
    
    // Si hay paginación, intentamos obtener todos los datos de la tabla
    if (hasPagination) {
        // Extraer datos de todas las filas visibles primero
        allTableRows.forEach(row => {
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
        
        // Intentar obtener el resto de los datos mediante una solicitud AJAX
        try {
            const urlParams = new URLSearchParams(window.location.search);
            // Crear una URL para obtener todos los datos sin paginación
            const ajaxUrl = `/gpsreport/get-all-locations?filter=${urlParams.get('filter') || 'today'}&gps=${urlParams.get('gps') || 'all'}&startDate=${urlParams.get('startDate') || ''}&endDate=${urlParams.get('endDate') || ''}`;
            
            const response = await fetch(ajaxUrl);
            if (response.ok) {
                const allLocations = await response.json();
                if (Array.isArray(allLocations) && allLocations.length > 0) {
                    // Reemplazar los datos con todos los puntos
                    locations.length = 0; // Limpiar el array
                    allLocations.forEach(loc => {
                        locations.push({
                            lat: parseFloat(loc.latitude),
                            lng: parseFloat(loc.longitude),
                            timestamp: loc.lastUpdate,
                            speed: parseFloat(loc.speed)
                        });
                    });
                    console.log(`Datos completos cargados: ${locations.length} puntos`);
                }
            }
        } catch (error) {
            console.error('Error al obtener todos los datos:', error);
            // Si falla, continuamos con los datos de la página actual
            if (locations.length === 0) {
                // Si no tenemos datos, extraemos de la tabla visible
                allTableRows.forEach(row => {
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
            }
        }
    } else {
        // No hay paginación, simplemente extraemos los datos de la tabla visible
        allTableRows.forEach(row => {
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
    }

    if (locations.length === 0) {
        document.getElementById('map').innerHTML = '<div class="alert alert-info">No se pudieron extraer coordenadas válidas de los datos.</div>';
        document.getElementById('route-stats-cards').style.display = 'none';
        return;
    }

    // --- Calcular estadísticas ---
    function toDate(str) {
        // Intenta parsear como ISO o formato local
        let d = new Date(str);
        if (isNaN(d.getTime()) && str.includes('/')) {
            // dd/mm/yyyy hh:mm:ss
            const [date, time] = str.split(' ');
            const [d_, m_, y_] = date.split('/');
            d = new Date(`${y_}-${m_}-${d_}T${time}`);
        }
        return d;
    }
    let totalDistance = 0;
    let totalSpeed = 0;
    let minTime = toDate(locations[0].timestamp);
    let maxTime = toDate(locations[locations.length-1].timestamp);
    for (let i = 1; i < locations.length; i++) {
        totalDistance += getDistanceFromLatLonInKm(
            locations[i-1].lat, locations[i-1].lng,
            locations[i].lat, locations[i].lng
        );
    }
    for (let i = 0; i < locations.length; i++) {
        totalSpeed += locations[i].speed;
    }
    const avgSpeed = totalSpeed / locations.length;
    const durationMs = maxTime - minTime;
    // Mostrar estadísticas
    document.getElementById('route-stats-cards').style.display = 'block';
    document.getElementById('stat-distance').textContent = totalDistance.toFixed(2) + ' km';
    document.getElementById('stat-avg-speed').textContent = avgSpeed.toFixed(1) + ' km/h';
    document.getElementById('stat-duration').textContent = msToHMS(durationMs);

    // --- Renderizar mapa ---
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
        
        // Agregar mensaje informativo sobre la ruta completa
        if (hasPagination) {
            const infoControl = L.control({position: 'topright'});
            infoControl.onAdd = function() {
                const div = L.DomUtil.create('div', 'info-control');
                div.innerHTML = `
                    <div class="alert alert-info p-2 m-0" style="font-size: 0.9rem; opacity: 0.9;">
                        <i class="fa fa-info-circle"></i> Mostrando la ruta completa (${locations.length} puntos)
                    </div>
                `;
                return div;
            };
            infoControl.addTo(map);
        }

        // --- Segmentos coloreados por velocidad ---
        // Definir rangos de velocidad y colores
        const speedColors = [
            { max: 10, color: '#2ecc40' },   // Verde
            { max: 30, color: '#f1c40f' },   // Amarillo
            { max: 60, color: '#ff9800' },   // Naranja
            { max: 120, color: '#e74c3c' },  // Rojo
            { max: Infinity, color: '#8e44ad' } // Morado
        ];
        function getColorForSpeed(speed) {
            for (const s of speedColors) {
                if (speed <= s.max) return s.color;
            }
            return '#000';
        }
        // Dibujar segmentos
        for (let i = 1; i < locations.length; i++) {
            const segColor = getColorForSpeed(locations[i].speed);
            L.polyline([
                [locations[i-1].lat, locations[i-1].lng],
                [locations[i].lat, locations[i].lng]
            ], {
                color: segColor,
                weight: 5,
                opacity: 0.85
            }).addTo(map);
        }

        // --- Marcadores de inicio y fin ---
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

        // --- Marcadores intermedios (opcional, igual que antes) ---
        for (let i = 1; i < locations.length - 1; i += Math.max(1, Math.floor(locations.length / 10))) {
            const marker = L.marker([locations[i].lat, locations[i].lng], {
                opacity: 0.7,
                title: locations[i].timestamp
            }).addTo(map);
            marker.bindPopup(`<b>Punto intermedio</b><br>Fecha: ${locations[i].timestamp}<br>Velocidad: ${locations[i].speed} km/h`);
        }

        // --- Animación de la ruta mejorada ---
        let animMarker = null;
        let animIndex = 0;
        let animating = false;
        let animTimeout = null;
        let animSpeed = 1; // 1x
        let paused = false;
        let progressBar = null;
        let passedPolyline = null;
        let pendingPolyline = null;
        let infoPopup = null;

        function createAnimationControls() {
            // Evitar duplicados
            if (document.getElementById('route-anim-controls')) return;
            const controls = document.createElement('div');
            controls.id = 'route-anim-controls';
            controls.className = 'mb-2 d-flex align-items-center gap-2';
            controls.innerHTML = `
                <button id="btn-playpause-route" class="btn btn-outline-primary btn-sm me-1"><i class="fa fa-play"></i></button>
                <button id="btn-restart-route" class="btn btn-outline-secondary btn-sm me-2"><i class="fa fa-undo"></i></button>
                <label class="me-1 mb-0">Velocidad:</label>
                <input id="slider-speed-route" type="range" min="0.25" max="3" step="0.25" value="1" style="width:90px;">
                <span id="speed-label" class="me-2">1x</span>
                <div class="flex-grow-1">
                  <div class="progress" style="height: 8px;">
                    <div id="route-anim-progress" class="progress-bar bg-info" style="width:0%"></div>
                  </div>
                </div>
            `;
            mapContainer.parentElement.insertBefore(controls, mapContainer);
            
            // Eventos
            document.getElementById('btn-playpause-route').onclick = function() {
                if (!animating) startAnimation();
                else paused = !paused;
                this.querySelector('i').className = paused ? 'fa fa-play' : 'fa fa-pause';
                if (!paused) stepAnim();
            };
            
            document.getElementById('btn-restart-route').onclick = function() {
                stopAnimation();
                // Crear el marcador en la posición inicial pero sin iniciar la animación
                if (!animMarker) {
                    animMarker = L.marker([locations[0].lat, locations[0].lng], {
                        icon: L.icon({
                            iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
                            iconSize: [38, 38],
                            iconAnchor: [19, 19]
                        })
                    }).addTo(map);
                    // Mostrar información del punto inicial
                    showInfoPopup(locations[0]);
                }
                // Asegurar que el botón de play/pause muestre el icono de play
                const playPauseBtn = document.getElementById('btn-playpause-route');
                if (playPauseBtn) {
                    playPauseBtn.querySelector('i').className = 'fa fa-play';
                }
            };
            
            document.getElementById('slider-speed-route').oninput = function() {
                animSpeed = parseFloat(this.value);
                document.getElementById('speed-label').textContent = animSpeed + 'x';
            };
            
            // Hacer la barra de progreso interactiva
            const progressContainer = document.querySelector('.progress');
            if (progressContainer) {
                // Estilo para indicar que es interactivo
                progressContainer.style.cursor = 'pointer';
                
                // Función para manejar el clic o arrastre en la barra de progreso
                function handleProgressBarInteraction(e) {
                    // Calcular la posición relativa del clic dentro de la barra de progreso
                    const rect = progressContainer.getBoundingClientRect();
                    const clickX = e.clientX - rect.left;
                    const percentClicked = (clickX / rect.width) * 100;
                    
                    // Calcular el índice correspondiente en el array de ubicaciones
                    const totalPoints = locations.length - 1;
                    let targetIndex = Math.floor((percentClicked / 100) * totalPoints);
                    
                    // Asegurar que el índice esté dentro de los límites
                    targetIndex = Math.max(0, Math.min(targetIndex, totalPoints));
                    
                    // Pausar la animación actual si está en curso
                    if (animating && !paused) {
                        paused = true;
                        const playPauseBtn = document.getElementById('btn-playpause-route');
                        if (playPauseBtn) {
                            playPauseBtn.querySelector('i').className = 'fa fa-play';
                        }
                    }
                    
                    // Detener cualquier animación en curso
                    if (animTimeout) clearTimeout(animTimeout);
                    
                    // Actualizar el índice de animación
                    animIndex = targetIndex;
                    
                    // Mover el marcador a la posición correspondiente
                    if (animMarker) {
                        const position = locations[targetIndex];
                        animMarker.setLatLng([position.lat, position.lng]);
                        map.panTo([position.lat, position.lng], {animate: true, duration: 0.2});
                        showInfoPopup(position);
                    }
                    
                    // Actualizar la barra de progreso
                    updateProgressBar((targetIndex / totalPoints) * 100);
                    
                    // Actualizar las líneas de ruta
                    if (passedPolyline) map.removeLayer(passedPolyline);
                    if (pendingPolyline) map.removeLayer(pendingPolyline);
                    
                    const passedCoords = locations.slice(0, targetIndex + 1).map(l => [l.lat, l.lng]);
                    passedPolyline = L.polyline(passedCoords, {color:'#007bff',weight:6,opacity:0.9}).addTo(map);
                    
                    const pendingCoords = locations.slice(targetIndex).map(l => [l.lat, l.lng]);
                    if (pendingCoords.length > 1) {
                        pendingPolyline = L.polyline(pendingCoords, {color:'#bbb',weight:4,opacity:0.5,dashArray:'6,8'}).addTo(map);
                    }
                }
                
                // Evento de clic en la barra de progreso
                progressContainer.addEventListener('click', handleProgressBarInteraction);
                
                // Eventos para arrastrar en la barra de progreso
                let isDragging = false;
                
                progressContainer.addEventListener('mousedown', function(e) {
                    isDragging = true;
                    handleProgressBarInteraction(e);
                });
                
                document.addEventListener('mousemove', function(e) {
                    if (isDragging) {
                        handleProgressBarInteraction(e);
                    }
                });
                
                document.addEventListener('mouseup', function() {
                    isDragging = false;
                });
            }
        }
        function removeAnimationControls() {
            const c = document.getElementById('route-anim-controls');
            if (c) c.remove();
        }
        function stopAnimation() {
            animating = false;
            paused = false;
            animIndex = 0;
            if (animTimeout) clearTimeout(animTimeout);
            // No eliminamos el marcador, solo las líneas de la ruta
            if (passedPolyline) { map.removeLayer(passedPolyline); passedPolyline = null; }
            if (pendingPolyline) { map.removeLayer(pendingPolyline); pendingPolyline = null; }
            if (infoPopup) { map.closePopup(infoPopup); infoPopup = null; }
            if (progressBar) progressBar.style.width = '0%';
            
            // Si existe el marcador, lo movemos a la posición inicial
            if (animMarker && locations && locations.length > 0) {
                animMarker.setLatLng([locations[0].lat, locations[0].lng]);
            }
        }
        function startAnimation() {
            stopAnimation();
            animating = true;
            paused = false;
            animIndex = 0;
            if (!animMarker) {
                animMarker = L.marker([locations[0].lat, locations[0].lng], {
                    icon: L.icon({
                        iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
                        iconSize: [38, 38],
                        iconAnchor: [19, 19]
                    })
                }).addTo(map);
            } else {
                // Si el marcador ya existe, asegurarse de que esté en la posición inicial
                animMarker.setLatLng([locations[0].lat, locations[0].lng]);
            }
            // Mostrar información del punto inicial antes de comenzar
            showInfoPopup(locations[0]);
            stepAnim();
        }
        function stepAnim() {
            if (!animating || paused) return;
            if (animIndex >= locations.length - 1) {
                updateProgressBar(100);
                showInfoPopup(locations[animIndex]);
                return;
            }
            // Interpolación suave
            const steps = 10;
            let step = 0;
            function interpolate() {
                if (!animating || paused) return;
                const start = locations[animIndex];
                const end = locations[animIndex+1];
                const lat = start.lat + (end.lat - start.lat) * (step/steps);
                const lng = start.lng + (end.lng - start.lng) * (step/steps);
                animMarker.setLatLng([lat, lng]);
                map.panTo([lat, lng], {animate: true, duration: 0.2});
                showInfoPopup({
                    ...end,
                    lat, lng
                });
                updateProgressBar(((animIndex + step/steps) / (locations.length-1)) * 100);
                // Ruta recorrida y pendiente
                if (passedPolyline) map.removeLayer(passedPolyline);
                if (pendingPolyline) map.removeLayer(pendingPolyline);
                const passedCoords = locations.slice(0, animIndex+1).map(l => [l.lat, l.lng]);
                passedCoords.push([lat, lng]);
                passedPolyline = L.polyline(passedCoords, {color:'#007bff',weight:6,opacity:0.9}).addTo(map);
                const pendingCoords = locations.slice(animIndex+1).map(l => [l.lat, l.lng]);
                if (pendingCoords.length > 1) pendingPolyline = L.polyline(pendingCoords, {color:'#bbb',weight:4,opacity:0.5,dashArray:'6,8'}).addTo(map);
                step++;
                if (step <= steps) {
                    animTimeout = setTimeout(interpolate, 40/animSpeed);
                } else {
                    animIndex++;
                    animTimeout = setTimeout(stepAnim, 40/animSpeed);
                }
            }
            interpolate();
        }
        function updateProgressBar(percent) {
            if (!progressBar) progressBar = document.getElementById('route-anim-progress');
            if (progressBar) progressBar.style.width = percent + '%';
        }
        function showInfoPopup(loc) {
            if (!animMarker) return;
            const html = `<b>Fecha:</b> ${loc.timestamp || ''}<br><b>Velocidad:</b> ${loc.speed || ''} km/h`;
            if (!infoPopup) {
                infoPopup = L.popup({closeButton:false,autoPan:false,offset:[0,-20]}).setLatLng(animMarker.getLatLng()).setContent(html).openOn(map);
            } else {
                infoPopup.setLatLng(animMarker.getLatLng()).setContent(html);
            }
        }
        // Botón y controles
        createAnimationControls();
        // Si ya existe el botón antiguo, lo quitamos
        const oldBtn = document.getElementById('btn-animate-route');
        if (oldBtn) oldBtn.remove();

        // --- Leyenda mejorada ---
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'info legend legend-speed');
            div.innerHTML = `
                <div><span class="legend-color" style="background:#2ecc40"></span> 0-10 km/h</div>
                <div><span class="legend-color" style="background:#f1c40f"></span> 11-30 km/h</div>
                <div><span class="legend-color" style="background:#ff9800"></span> 31-60 km/h</div>
                <div><span class="legend-color" style="background:#e74c3c"></span> 61-120 km/h</div>
                <div><span class="legend-color" style="background:#8e44ad"></span> >120 km/h</div>
                <div><span style="color: green; font-size: 18px;">●</span> Inicio</div>
                <div><span style="color: red; font-size: 18px;">●</span> Fin</div>
            `;
            return div;
        };
        legend.addTo(map);

        // Fit the map to show all the route
        const bounds = L.latLngBounds(locations.map(l => [l.lat, l.lng]));
        map.fitBounds(bounds, { padding: [50, 50] });

        // Forzar actualización del tamaño del mapa después de renderizar
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

    } catch (error) {
        console.error('Error initializing map:', error);
        document.getElementById('map').innerHTML = `<div class="alert alert-danger">Error al inicializar el mapa: ${error.message}</div>`;
        document.getElementById('route-stats-cards').style.display = 'none';
    }

    // --- Después de cargar locations y antes de renderizar el mapa ---
    // Obtener horas de entrada/salida a la geocerca "capasu"
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const capasuUrl = `/gpsreport/capasu-times?filter=${urlParams.get('filter') || 'today'}&gps=${urlParams.get('gps') || ''}&startDate=${urlParams.get('startDate') || ''}&endDate=${urlParams.get('endDate') || ''}`;
        const capasuResp = await fetch(capasuUrl);
        if (capasuResp.ok) {
            const capasuTimes = await capasuResp.json();
            console.log('Entradas y salidas a la geocerca "capasu":', capasuTimes);
        } else {
            console.warn('No se pudo obtener la información de entrada/salida a capasu');
        }
    } catch (e) {
        console.error('Error al consultar capasu-times:', e);
    }

    // ...resto del código de initMap...
}

// --- Utilidades para estadísticas ---
function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
    var R = 6371; // km
    var dLat = deg2rad(lat2-lat1);
    var dLon = deg2rad(lon2-lon1);
    var a = 
      Math.sin(dLat/2) * Math.sin(dLat/2) +
      Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
      Math.sin(dLon/2) * Math.sin(dLon/2)
      ;
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    var d = R * c;
    return d;
}
function deg2rad(deg) {
    return deg * (Math.PI/180);
}
function msToHMS(ms) {
    if (isNaN(ms) || ms < 0) return '-';
    let s = Math.floor(ms/1000);
    let h = Math.floor(s/3600);
    s = s%3600;
    let m = Math.floor(s/60);
    s = s%60;
    return `${h}h ${m}m ${s}s`;
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
        
        /* Estilos para la barra de progreso interactiva */
        #route-anim-controls .progress {
            cursor: pointer;
            position: relative;
            overflow: visible;
            transition: height 0.2s;
        }
        
        #route-anim-controls .progress:hover {
            height: 12px;
        }
        
        #route-anim-controls .progress:hover::before {
            content: 'Haz clic o arrastra para navegar por la ruta';
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0.9;
            z-index: 1000;
        }
        
        #route-anim-controls .progress-bar {
            transition: width 0.1s ease-out;
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

// Function to set up export button visibility
function setupExportButtonVisibility() {
    // Add the class to the export button if it doesn't have it
    const exportButton = document.querySelector('a.btn-success[onclick*="confirmExport"]');
    if (exportButton) {
        exportButton.classList.add('btn-export-excel');
        
        // Check if there are rows in the table
        const tableRows = document.querySelectorAll('#projects-tbl tbody tr');
        if (tableRows.length === 0) {
            exportButton.style.display = 'none';
        } else {
            exportButton.style.display = 'block';
        }
    }
}