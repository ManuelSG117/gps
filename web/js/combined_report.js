// Este archivo asume que las variables 'locations' y 'stops' están disponibles como arrays JSON

function initCombinedMap() {
    // Obtener datos de PHP (inyectados en la vista)
    if (typeof locations === 'undefined' || locations.length === 0) {
        document.getElementById('combined-map').innerHTML = '<div class="alert alert-info">No hay datos de ubicación disponibles para mostrar en el mapa.</div>';
        return;
    }

    // Limpiar el contenedor
    const mapContainer = document.getElementById('combined-map');
    mapContainer.innerHTML = '';

    // Inicializar el mapa
    const map = L.map('combined-map', {
        minZoom: 2,
        maxZoom: 18
    }).setView([locations[0].latitude, locations[0].longitude], 13);

    // Capa Google y OSM
    const googleStreets = L.gridLayer.googleMutant({ type: 'roadmap' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // --- Dibujar la ruta ---
    const routeLatLngs = locations.map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
    const routePolyline = L.polyline(routeLatLngs, { color: '#007bff', weight: 5, opacity: 0.8 }).addTo(map);

    // Marcador de inicio y fin
    const startMarker = L.marker(routeLatLngs[0], {
        title: 'Inicio',
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);
    startMarker.bindPopup(`<b>Punto de inicio</b><br>Fecha: ${locations[0].lastUpdate}`);

    const endMarker = L.marker(routeLatLngs[routeLatLngs.length - 1], {
        title: 'Fin',
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);
    endMarker.bindPopup(`<b>Punto final</b><br>Fecha: ${locations[locations.length - 1].lastUpdate}`);

    // --- Paradas ---
    let stopMarkers = [];
    function showStops() {
        if (!Array.isArray(stops) || stops.length === 0) return;
        stopMarkers = stops.map((stop, idx) => {
            const marker = L.marker([parseFloat(stop.latitude), parseFloat(stop.longitude)], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            let durationText = '-';
            if (stop.duration) {
                const d = parseInt(stop.duration);
                if (d >= 3600) {
                    const h = Math.floor(d / 3600);
                    const m = Math.floor((d % 3600) / 60);
                    const s = d % 60;
                    durationText = `${h}h ${m}m ${s}s`;
                } else {
                    const m = Math.floor(d / 60);
                    const s = d % 60;
                    durationText = `${m}m ${s}s`;
                }
            }
            marker.bindPopup(
                `<b>Parada #${idx + 1}</b><br>Inicio: ${stop.start_time}<br>Fin: ${stop.end_time || 'En curso'}<br>Duración: ${durationText}`
            );
            return marker;
        });
    }
    function hideStops() {
        stopMarkers.forEach(m => map.removeLayer(m));
        stopMarkers = [];
    }

    // Switch para mostrar/ocultar paradas
    const switchStops = document.getElementById('showStopsOnMap');
    if (switchStops) {
        switchStops.checked = false;
        switchStops.onchange = function() {
            if (this.checked) {
                showStops();
            } else {
                hideStops();
            }
        };
    }

    // Ajustar el mapa a la ruta
    if (routeLatLngs.length > 1) {
        map.fitBounds(routeLatLngs, { padding: [50, 50] });
    } else {
        map.setView(routeLatLngs[0], 15);
    }

    // Leyenda
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'info legend');
        div.innerHTML = `
            <div><span style="color: green; font-size: 18px;">●</span> Inicio</div>
            <div><span style="color: red; font-size: 18px;">●</span> Fin</div>
            <div><span style="color: orange; font-size: 18px;">●</span> Parada</div>
        `;
        return div;
    };
    legend.addTo(map);

    createAnimationControlsCombined(map, locations);
}

function createAnimationControlsCombined(map, locations) {
    // Evitar duplicados
    if (document.getElementById('route-anim-controls')) return;
    const mapContainer = document.getElementById('combined-map');
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

    // --- Variables de animación ---
    let animMarker = null;
    let animIndex = 0;
    let animating = false;
    let animTimeout = null;
    let animSpeed = 1;
    let paused = false;
    let progressBar = null;
    let passedPolyline = null;
    let pendingPolyline = null;
    let infoPopup = null;

    document.getElementById('btn-playpause-route').onclick = function() {
        if (!animating) startAnimation();
        else paused = !paused;
        this.querySelector('i').className = paused ? 'fa fa-play' : 'fa fa-pause';
        if (!paused) stepAnim();
    };
    document.getElementById('btn-restart-route').onclick = function() {
        stopAnimation();
        if (!animMarker) {
            animMarker = L.marker([locations[0].latitude, locations[0].longitude], {
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
                    iconSize: [38, 38],
                    iconAnchor: [19, 19]
                })
            }).addTo(map);
            showInfoPopup(locations[0]);
        }
        const playPauseBtn = document.getElementById('btn-playpause-route');
        if (playPauseBtn) playPauseBtn.querySelector('i').className = 'fa fa-play';
    };
    document.getElementById('slider-speed-route').oninput = function() {
        animSpeed = parseFloat(this.value);
        document.getElementById('speed-label').textContent = animSpeed + 'x';
    };
    // Barra de progreso interactiva
    const progressContainer = document.querySelector('.progress');
    if (progressContainer) {
        progressContainer.style.cursor = 'pointer';
        function handleProgressBarInteraction(e) {
            const rect = progressContainer.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const percentClicked = (clickX / rect.width) * 100;
            const totalPoints = locations.length - 1;
            let targetIndex = Math.floor((percentClicked / 100) * totalPoints);
            targetIndex = Math.max(0, Math.min(targetIndex, totalPoints));
            if (animating && !paused) {
                paused = true;
                const playPauseBtn = document.getElementById('btn-playpause-route');
                if (playPauseBtn) playPauseBtn.querySelector('i').className = 'fa fa-play';
            }
            if (animTimeout) clearTimeout(animTimeout);
            animIndex = targetIndex;
            if (animMarker) {
                const position = locations[targetIndex];
                animMarker.setLatLng([position.latitude, position.longitude]);
                map.panTo([position.latitude, position.longitude], {animate: true, duration: 0.2});
                showInfoPopup(position);
            }
            updateProgressBar((targetIndex / totalPoints) * 100);
            if (passedPolyline) map.removeLayer(passedPolyline);
            if (pendingPolyline) map.removeLayer(pendingPolyline);
            const passedCoords = locations.slice(0, targetIndex + 1).map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            passedPolyline = L.polyline(passedCoords, {color:'#007bff',weight:6,opacity:0.9}).addTo(map);
            const pendingCoords = locations.slice(targetIndex).map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            if (pendingCoords.length > 1) {
                pendingPolyline = L.polyline(pendingCoords, {color:'#bbb',weight:4,opacity:0.5,dashArray:'6,8'}).addTo(map);
            }
        }
        progressContainer.addEventListener('click', handleProgressBarInteraction);
        let isDragging = false;
        progressContainer.addEventListener('mousedown', function(e) {
            isDragging = true;
            handleProgressBarInteraction(e);
        });
        document.addEventListener('mousemove', function(e) {
            if (isDragging) handleProgressBarInteraction(e);
        });
        document.addEventListener('mouseup', function() { isDragging = false; });
    }
    function stopAnimation() {
        animating = false;
        paused = false;
        animIndex = 0;
        if (animTimeout) clearTimeout(animTimeout);
        if (passedPolyline) { map.removeLayer(passedPolyline); passedPolyline = null; }
        if (pendingPolyline) { map.removeLayer(pendingPolyline); pendingPolyline = null; }
        if (infoPopup) { map.closePopup(infoPopup); infoPopup = null; }
        if (progressBar) progressBar.style.width = '0%';
        if (animMarker && locations && locations.length > 0) {
            animMarker.setLatLng([locations[0].latitude, locations[0].longitude]);
        }
    }
    function startAnimation() {
        stopAnimation();
        animating = true;
        paused = false;
        animIndex = 0;
        if (!animMarker) {
            animMarker = L.marker([locations[0].latitude, locations[0].longitude], {
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
                    iconSize: [38, 38],
                    iconAnchor: [19, 19]
                })
            }).addTo(map);
        } else {
            animMarker.setLatLng([locations[0].latitude, locations[0].longitude]);
        }
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
        const steps = 10;
        let step = 0;
        function interpolate() {
            if (!animating || paused) return;
            const start = locations[animIndex];
            const end = locations[animIndex+1];
            const startLat = parseFloat(start.latitude);
            const endLat = parseFloat(end.latitude);
            const startLng = parseFloat(start.longitude);
            const endLng = parseFloat(end.longitude);
            const lat = startLat + (endLat - startLat) * (step/steps);
            const lng = startLng + (endLng - startLng) * (step/steps);
            animMarker.setLatLng([lat, lng]);
            map.panTo([lat, lng], {animate: true, duration: 0.2});
            showInfoPopup({ ...end, latitude: lat, longitude: lng });
            updateProgressBar(((animIndex + step/steps) / (locations.length-1)) * 100);
            if (passedPolyline) map.removeLayer(passedPolyline);
            if (pendingPolyline) map.removeLayer(pendingPolyline);
            const passedCoords = locations.slice(0, animIndex+1).map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            passedCoords.push([lat, lng]);
            passedPolyline = L.polyline(passedCoords, {color:'#007bff',weight:6,opacity:0.9}).addTo(map);
            const pendingCoords = locations.slice(animIndex+1).map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
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
        const html = `<b>Fecha:</b> ${loc.lastUpdate || ''}<br><b>Velocidad:</b> ${loc.speed || ''} km/h`;
        if (!infoPopup) {
            infoPopup = L.popup({closeButton:false,autoPan:false,offset:[0,-20]}).setLatLng(animMarker.getLatLng()).setContent(html).openOn(map);
        } else {
            infoPopup.setLatLng(animMarker.getLatLng()).setContent(html);
        }
    }
}

// --- Flatpickr personalizado para rango de fechas ---
function setupFlatpickrRange() {
    const filter = document.getElementById('filter');
    const customDates = document.querySelector('.custom-dates');
    if (!filter || !customDates) return;
    // Eliminar campos individuales y dejar solo uno
    let rangeInput = document.getElementById('rangeDate');
    if (!rangeInput) {
        rangeInput = document.createElement('input');
        rangeInput.type = 'text';
        rangeInput.className = 'form-control';
        rangeInput.id = 'rangeDate';
        rangeInput.placeholder = 'Selecciona rango de fechas y horas';
        customDates.innerHTML = '';
        customDates.appendChild(rangeInput);
    }
    flatpickr('#rangeDate', {
        mode: 'range',
        dateFormat: 'Y-m-d H:i',
        enableTime: true,
        time_24hr: true,
        locale: 'es',
        allowInput: true,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates.length === 2) {
                // Llenar los campos ocultos startDate y endDate
                function pad(n) { return n < 10 ? '0' + n : n; }
                function formatDateTime(date) {
                    return date.getFullYear() + '-' +
                        pad(date.getMonth() + 1) + '-' +
                        pad(date.getDate()) + ' ' +
                        pad(date.getHours()) + ':' +
                        pad(date.getMinutes()) + ':' +
                        pad(date.getSeconds());
                }
                let start = formatDateTime(selectedDates[0]);
                let end = formatDateTime(selectedDates[1]);
                let startInput = document.getElementById('startDate');
                let endInput = document.getElementById('endDate');
                if (!startInput) {
                    startInput = document.createElement('input');
                    startInput.type = 'hidden';
                    startInput.id = 'startDate';
                    startInput.name = 'startDate';
                    customDates.appendChild(startInput);
                }
                if (!endInput) {
                    endInput = document.createElement('input');
                    endInput.type = 'hidden';
                    endInput.id = 'endDate';
                    endInput.name = 'endDate';
                    customDates.appendChild(endInput);
                }
                startInput.value = start;
                endInput.value = end;
            }
        }
    });
}

// Inicializar flatpickr y eventos de filtro personalizados
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('filter');
    const customDates = document.querySelector('.custom-dates');
    
    if (filter) {
        filter.addEventListener('change', function() {
            if (filter.value === 'custom') {
                if (customDates) customDates.style.display = 'block';
                setupFlatpickrRange();
            } else {
                if (customDates) customDates.style.display = 'none';
            }
        });
        
        // Inicializar al cargar la página
        if (filter.value === 'custom') {
            if (customDates) customDates.style.display = 'block';
            setupFlatpickrRange();
        } else {
            if (customDates) customDates.style.display = 'none';
        }
    }
    
    // Inicializar el mapa
    if (document.getElementById('combined-map')) {
        initCombinedMap();
        showRouteStatsCards();
        showStopsStatsCards();
    }
});

// Soporte para PJAX: volver a inicializar el mapa tras recarga parcial
$(document).on('pjax:end', function(e) {
    if ($('#combined-map').length) {
        initCombinedMap();
        showRouteStatsCards();
        showStopsStatsCards();
    }
});

function showRouteStatsCards() {
    if (!Array.isArray(locations) || locations.length < 2) return;
    // Distancia total
    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    let totalDistance = 0;
    for (let i = 1; i < locations.length; i++) {
        totalDistance += haversine(
            parseFloat(locations[i-1].latitude),
            parseFloat(locations[i-1].longitude),
            parseFloat(locations[i].latitude),
            parseFloat(locations[i].longitude)
        );
    }
    // Velocidad promedio
    let totalSpeed = 0, speedCount = 0;
    locations.forEach(l => {
        if (l.speed && !isNaN(l.speed)) {
            totalSpeed += parseFloat(l.speed);
            speedCount++;
        }
    });
    let avgSpeed = speedCount > 0 ? totalSpeed / speedCount : 0;
    // Duración total
    let start = new Date(locations[0].lastUpdate);
    let end = new Date(locations[locations.length-1].lastUpdate);
    let durationSec = Math.floor((end - start) / 1000);
    function formatDuration(sec) {
        if (sec >= 3600) {
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            return `${h}h ${m}m ${s}s`;
        } else {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return `${m}m ${s}s`;
        }
    }
    // Mostrar en cards
    const statDistance = document.getElementById('stat-distance');
    if (statDistance) statDistance.textContent = totalDistance.toFixed(2) + ' km';
    const statAvgSpeed = document.getElementById('stat-avg-speed');
    if (statAvgSpeed) statAvgSpeed.textContent = avgSpeed.toFixed(1) + ' km/h';
    const statDuration = document.getElementById('stat-duration');
    if (statDuration) statDuration.textContent = formatDuration(durationSec);
    // Mostrar en consola
    console.log('Distancia total:', totalDistance.toFixed(2), 'km');
    console.log('Velocidad promedio:', avgSpeed.toFixed(1), 'km/h');
    console.log('Duración total:', formatDuration(durationSec));
    // Mostrar cards
    const cards = document.getElementById('route-stats-cards');
    if (cards) cards.style.display = '';
}

function showStopsStatsCards() {
    if (!Array.isArray(stops) || stops.length === 0) return;
    // Total de paradas
    const totalStops = stops.length;
    // Tiempo promedio de parada
    let totalStopDuration = 0;
    stops.forEach(s => {
        if (s.duration && !isNaN(s.duration)) totalStopDuration += parseInt(s.duration);
    });
    let avgStopDuration = totalStops > 0 ? Math.floor(totalStopDuration / totalStops) : 0;
    function formatDuration(sec) {
        if (sec >= 3600) {
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            return `${h}h ${m}m ${s}s`;
        } else {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return `${m}m ${s}s`;
        }
    }
    // Mostrar en cards
    const statTotalStops = document.getElementById('stat-total-stops');
    if (statTotalStops) statTotalStops.textContent = totalStops;
    const statAvgStopDuration = document.getElementById('stat-avg-stop-duration');
    if (statAvgStopDuration) statAvgStopDuration.textContent = formatDuration(avgStopDuration);
    // Mostrar en consola
    console.log('Total de paradas:', totalStops);
    console.log('Tiempo promedio de parada:', formatDuration(avgStopDuration));
    // Mostrar cards
    const cards = document.getElementById('stops-stats-cards');
    if (cards) cards.style.display = '';
} 