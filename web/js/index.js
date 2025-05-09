    let map;
    let markers = [];
    let routePath;
    let routeCoordinates = [];
    let recentLocationsInterval; 
    let lastKnownLocations = {};
    const addressCache = {};
    const lastUpdateTime = {}; 
    let startEndMarkers = [];
    let isPaused = false;
    let currentTimeout = null;
    let index = 0;
    let numSteps = 200; //numero de pasos por tiempo
    let timePerStep = 25;//paso por tiempo 
    let marker = null; // Variable global para almacenar el marcador en uso
    let speedMultiplier = 1; // Velocidad inicial (1x)
    let passedRoutePath;
    let remainingRoutePath;
    let lastSelectedGPS = null; // Variable para almacenar la última selección

    
function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.item-item input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        toggleMarker(checkbox.id);
    });
}

(function() {
    // Guardamos los métodos originales de Leaflet para poder añadir la rotación a los marcadores
    var proto_initIcon = L.Marker.prototype._initIcon;
    var proto_setPos = L.Marker.prototype._setPos;
    var oldIE = (L.DomUtil.TRANSFORM === 'msTransform');

    L.Marker.addInitHook(function() {
        var iconOptions = this.options.icon && this.options.icon.options;
        var iconAnchor = iconOptions && this.options.icon.options.iconAnchor;
        if (iconAnchor) {
            iconAnchor = (iconAnchor[0] + 'px ' + iconAnchor[1] + 'px');
        }
        this.options.rotationOrigin = this.options.rotationOrigin || iconAnchor || 'center bottom';
        this.options.rotationAngle = this.options.rotationAngle || 0;

        // Aplicamos la rotación cuando el marcador se arrastra
        this.on('drag', function(e) {
            e.target._applyRotation();
        });
    });

    L.Marker.include({
        _initIcon: function() {
            proto_initIcon.call(this);
        },

        _setPos: function(pos) {
            proto_setPos.call(this, pos);
            this._applyRotation();
        },

        _applyRotation: function() {
            if (this.options.rotationAngle) {
                this._icon.style[L.DomUtil.TRANSFORM+'Origin'] = this.options.rotationOrigin;
                if (oldIE) {
                    this._icon.style[L.DomUtil.TRANSFORM] = 'rotate(' + this.options.rotationAngle + 'deg)';
                } else {
                    this._icon.style[L.DomUtil.TRANSFORM] += ' rotateZ(' + this.options.rotationAngle + 'deg)';
                }
            }
        },

        setRotationAngle: function(angle) {
            this.options.rotationAngle = angle;
            this.update();
            return this;
        },

        setRotationOrigin: function(origin) {
            this.options.rotationOrigin = origin;
            this.update();
            return this;
        }
    });
})();


function initMap() {
    map = L.map('map').setView([19.4202403, -102.0686549], 15);

    const googleLayer = L.gridLayer.googleMutant({
        type: 'roadmap' 
    });
    map.addLayer(googleLayer);

    const trafficMutant = L.gridLayer.googleMutant({
        type: 'roadmap'
    });
    trafficMutant.addGoogleLayer("TrafficLayer");
    map.addLayer(trafficMutant);

    loadGpsOptions();
    loadRecentLocations();

    recentLocationsInterval = setInterval(loadRecentLocations, 10000); 
}


async function loadRecentLocations() { 
    try {
        const response = await fetch('get-locations-time');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        const updatedPhoneNumbers = new Set();

        data.forEach(location => {
            updatedPhoneNumbers.add(location.phoneNumber);

            const lastLocation = lastKnownLocations[location.phoneNumber];
            const currentTime = Date.now();

            // Log para ver cómo cambia el lastUpdateTime
          //  console.log(`lastUpdateTime[${location.phoneNumber}]: `, lastUpdateTime[location.phoneNumber]);

            // Si hay un cambio de ubicación, actualizamos el marcador y la hora de actualización
            if (!lastLocation || 
                lastLocation.latitude !== location.latitude || 
                lastLocation.longitude !== location.longitude) {

             //   console.log(`Actualizando marcador para ${location.phoneNumber}: nueva posición [${location.latitude}, ${location.longitude}].`);

                if (markers[location.phoneNumber]) {
                    map.removeLayer(markers[location.phoneNumber]);
                }

                const marker = createMarker(location);
                markers[location.phoneNumber] = marker;

                lastKnownLocations[location.phoneNumber] = {
                    latitude: location.latitude,
                    longitude: location.longitude
                };

                // Actualiza la última hora de actualización solo cuando haya un cambio de posición
                lastUpdateTime[location.phoneNumber] = currentTime;
              //  console.log(`lastUpdateTime actualizado para ${location.phoneNumber}: `, lastUpdateTime[location.phoneNumber]);

            } else {
               // console.log(`El marcador para ${location.phoneNumber} no ha cambiado de posición.`);
            }
        });

        // Verificar si ha pasado más de 2 minutos para cada marcador
        Object.keys(markers).forEach(phoneNumber => {
            const marker = markers[phoneNumber];
            const lastUpdated = lastUpdateTime[phoneNumber];
            const currentTime = Date.now();

            if (lastUpdated) {
                const timeElapsed = currentTime - lastUpdated;
                //console.log(`Tiempo transcurrido desde la última actualización de ${phoneNumber}: ${timeElapsed / 1000} segundos`);

                if (timeElapsed > 2 * 60 * 1000) { // 2 minutos en milisegundos
                    if (marker) {
                  //      console.log(`Cambiando ícono a inactivo para el marcador de ${phoneNumber} (no recibido en la respuesta desde hace más de 2 minutos).`);
                        marker.setIcon(L.icon({
                            iconUrl: 'https://img.icons8.com/?size=100&id=p9Dtg5w9YDAv&format=png&color=000000',
                            iconSize: [30, 30],
                            iconAnchor: [20, 20],
                            popupAnchor: [0, -20]
                        }));
                    }
                }
            } else {
            //    console.log(`No se ha encontrado el tiempo de última actualización para ${phoneNumber}.`);
            }
        });

    } catch (error) {
   //     console.error('Error fetching recent locations:', error);
    }
}


function createMarker(location) {
    const marker = L.marker([location.latitude, location.longitude], {
        icon: L.icon({
            iconUrl: 'https://img.icons8.com/?size=100&id=UfftIT7em2K0&format=png&color=000000',
            iconSize: [30, 30],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        })
    }).addTo(map);

    if (location.direction) {
        marker.setRotationAngle(location.direction);
    }

    marker.bindTooltip(location.userName, { permanent: true, direction: 'top', className: 'custom-tooltip' }).openTooltip();

    marker.on('click', () => {
        handleMarkerClick(marker, location);
    });

    return marker;
}

function loadGpsOptions() {
    fetch('get-gps-options')
        .then(response => response.json())
        .then(gpsData => {
            fetch('get-locations-time')
                .then(response => response.json())
                .then(locationData => {
                    const gpsList = document.getElementById('gpsList');
                    const gpsSelect = document.getElementById('gpsSelector'); 
                    gpsData.forEach(gps => {
                        const location = locationData.find(loc => loc.phoneNumber === gps.phoneNumber);
                        const speed = location ? location.speed : 'N/A';

                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.id = gps.phoneNumber;
                        checkbox.checked = true;
                        checkbox.onchange = () => toggleMarker(gps.phoneNumber);

                        const label = document.createElement('label');
                        label.htmlFor = gps.phoneNumber;
                        label.textContent = `${gps.userName}  ${speed} km/h`;

                    
                        const div = document.createElement('div');
                        div.className = 'item-item';
                        div.appendChild(checkbox);
                        div.appendChild(label);
                        gpsList.appendChild(div);

                        // Crear y agregar opciones al select
                        const option = document.createElement('option');
                        option.value = gps.phoneNumber;
                        option.textContent = gps.userName;
                        gpsSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching locations:', error));
        })
        .catch(error => console.error('Error fetching GPS options:', error));
}




function filterGpsList() {
    const searchInput = document.getElementById('gpsSearch').value.toLowerCase();
    const gpsItems = document.querySelectorAll('.item-item');

    gpsItems.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchInput)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}


function clearMarkers() {
    Object.values(markers).forEach(marker => map.removeLayer(marker));
    markers = {};
}


async function handleMarkerClick(marker, location) {
    try {
        // Mostrar ubicación inicial con el enlace "Mostrar calle"
        marker.bindPopup(`
            <b>Ubicación:</b> <a href="https://www.google.com/maps?q=${location.latitude},${location.longitude}" target="_blank">${location.latitude}, ${location.longitude}</a><br>
            <b>Dirección:</b> <a href="javascript:void(0);" onclick="showAddress(${location.latitude}, ${location.longitude}, this)">Mostrar calle</a><br>
            <b>Velocidad:</b> ${location.speed} km/h
        `, { offset: [0, -40] }).openPopup();

    } catch (error) {
      //  console.error('Error fetching address:', error);
    }
}

async function showAddress(lat, lon, linkElement) {
    try {
        // Obtener dirección usando las coordenadas
        const address = await getAddress(lat, lon);
//console.log(`Dirección obtenida: ${address}`);
        
        // Reemplazar el enlace con la dirección obtenida
        linkElement.innerHTML = address;
   //     console.log(`Dirección mostrada: ${address}`);
        
    } catch (error) {
    //    console.error('Error fetching address in showAddress:', error);
    }
}



function toggleMarker(phoneNumber) {
        const checkbox = document.getElementById(phoneNumber);
        if (checkbox.checked) {
            markers[phoneNumber].addTo(map);
        } else {
            map.removeLayer(markers[phoneNumber]);
        }
    }
    function minimizeButtonContainer() {
        var container = document.getElementById('buttonContainer');
        container.style.display = 'none';
    }

  
    
    // Función auxiliar para calcular la distancia entre dos puntos (fórmula Haversine)
    function calculateDistance(coord1, coord2) {
        const R = 6371; // Radio de la Tierra en kilómetros
        const lat1 = coord1[0] * (Math.PI / 180);
        const lat2 = coord2[0] * (Math.PI / 180);
        const deltaLat = (coord2[0] - coord1[0]) * (Math.PI / 180);
        const deltaLon = (coord2[1] - coord1[1]) * (Math.PI / 180);
    
        const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
                  Math.cos(lat1) * Math.cos(lat2) *
                  Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);
    
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // Distancia en kilómetros
    }

    

    async function loadRoute() {
        const gpsSelector = document.getElementById('gpsSelector');
        const phoneNumber = gpsSelector.value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
    
        // Validar que se haya seleccionado un GPS y la fecha de inicio
        if (!phoneNumber || !startDate) {
            Swal.fire({
                icon: 'warning',
                title: '¡Faltan datos!',
                text: 'Por favor, selecciona un dispositivo y por lo menos la fecha de inicio para cargar la ruta.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
    
        // Validar que se haya cambiado la selección de GPS
        if (phoneNumber === lastSelectedGPS) {
            Swal.fire({
                icon: 'info',
                title: 'Selección ya cargada',
                text: 'Debes seleccionar un dispositivo diferente para cargar una nueva ruta.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
    
        // Actualizar la última selección
        lastSelectedGPS = phoneNumber;
        // Mostrar el overlay y el spinner de carga
        const loadingOverlay = document.getElementById('loadingOverlay');
        loadingOverlay.classList.add('active');
    
        // Detener la actualización periódica de ubicaciones recientes
        if (recentLocationsInterval) {
            clearInterval(recentLocationsInterval);
            recentLocationsInterval = null;
         //   console.log('Intervalo de actualizaciones detenido.');
        }
    
        const formatDate = (date, time) => {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day} ${time}`;
        };
    
        const formattedStartDate = startDate ? formatDate(startDate, '00:00:00') : '';
        const formattedEndDate = endDate ? formatDate(endDate, '23:59:59') : '';
        
        let url = `get-route?phoneNumber=${phoneNumber}`;
        if (formattedStartDate) {
            url += `&startDate=${formattedStartDate}`;
        }
        if (formattedEndDate) {
            url += `&endDate=${formattedEndDate}`;
        }
    
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
    
            if (!Array.isArray(data.locations) || data.locations.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Sin ruta!',
                    text: 'No se encontraron ruta para este dispositivo en esta fecha .',
                    confirmButtonText: 'Aceptar'
                });                return;
            }
    
            // Eliminar todos los marcadores existentes, excepto el seleccionado
            Object.keys(markers).forEach(key => {
                if (key !== phoneNumber && markers[key]) {
                    map.removeLayer(markers[key]);
                    delete markers[key]; // Eliminar del objeto `markers`
                }
            });
    
            // Limpiar la ruta previa
            if (routePath) {
                map.removeLayer(routePath);
            }
            routeCoordinates = [];
    
            // Actualizar las coordenadas de la ruta
            routeCoordinates = data.locations.map(location => [
                parseFloat(location.latitude),
                parseFloat(location.longitude),
            ]);
            const passedCoordinates = routeCoordinates.slice(0, index); // Coordenadas de la parte recorrida
            const remainingCoordinates = routeCoordinates.slice(index); // Coordenadas de la parte no recorrida
    
            // Dibujar la parte recorrida con un color diferente
            if (passedRoutePath) {
                map.removeLayer(passedRoutePath);
                passedRoutePath = null; // Eliminar la ruta recorrida
            }
            passedRoutePath = L.polyline(passedCoordinates, { color: '#28a745' }).addTo(map); // Color verde para la parte recorrida

            // Dibujar la parte restante de la ruta
            if (remainingRoutePath) {
                map.removeLayer(remainingRoutePath);
                remainingRoutePath = null; // Eliminar la ruta restante
            }
            remainingRoutePath = L.polyline(remainingCoordinates, { color: '#454B54' }).addTo(map); // Color gris para la parte no recorrida

            // Calcular la distancia total recorrida
            let totalDistance = 0;
            for (let i = 1; i < routeCoordinates.length; i++) {
                totalDistance += calculateDistance(routeCoordinates[i - 1], routeCoordinates[i]);
            }
    
            // Dibujar la nueva ruta
            routePath = L.polyline(routeCoordinates, { color: '#454B54' }).addTo(map);
    
            // Crear o actualizar el marcador para el GPS seleccionado
            const selectedOption = gpsSelector.options[gpsSelector.selectedIndex];
            const userName = selectedOption.text;
    
            const startPoint = routeCoordinates[0];
            const endPoint = routeCoordinates[routeCoordinates.length - 1]; // Último punto
    
            const startAddress = await getAddress(startPoint[0], startPoint[1]);
            const endAddress = await getAddress(endPoint[0], endPoint[1]);
    
            // Agregar marcador para el primer punto (inicio de la ruta)
            const startMarker = L.marker([startPoint[0], startPoint[1]], {
                icon: L.icon({
                    iconUrl: 'https://img.icons8.com/?size=100&id=13802&format=png&color=000000', // Ícono verde para el inicio
                    iconSize: [40, 40], // Ajustar el tamaño
                    iconAnchor: [20, 20], // Centrar el ícono
                    popupAnchor: [0, -20] // Ajustar el popup
                })
            }).addTo(map).bindPopup(`
                <b>Primer punto de la ruta</b><br>
                <b>Fecha:</b> ${data.locations[0].lastUpdate}<br>
                <b>Velocidad:</b> ${data.locations[0].speed} km/h<br>
                <b>Ubicación:</b> <a href="https://www.google.com/maps?q=${startPoint[0]},${startPoint[1]}" target="_blank">${startPoint[0]}, ${startPoint[1]}</a><br>
                <b>Dirección:</b> <a href="javascript:void(0);" onclick="showAddress(${startPoint[0]}, ${startPoint[1]}, this)">Mostrar calle</a><br>`);
            startEndMarkers.push(startMarker); // Almacenar el marcador
    
            // Calcular el tiempo transcurrido entre el primer y el último punto
            const startTime = new Date(data.locations[0].lastUpdate);
            const endTime = new Date(data.locations[data.locations.length - 1].lastUpdate);
            const timeDiff = endTime - startTime; // Diferencia en milisegundos
            const timeDiffHours = Math.floor(timeDiff / 3600000); // Convertir a horas
            const timeDiffMinutes = Math.floor((timeDiff % 3600000) / 60000); // Convertir el resto a minutos
    
            // Agregar marcador para el último punto (fin de la ruta)
            const endMarker = L.marker([endPoint[0], endPoint[1]], {
                icon: L.icon({
                    iconUrl: 'https://img.icons8.com/?size=100&id=13796&format=png&color=000000', // Ícono rojo para el final
                    iconSize: [40, 40], // Ajustar el tamaño
                    iconAnchor: [20, 20], // Centrar el ícono
                    popupAnchor: [0, -20] // Ajustar el popup
                })
            }).addTo(map).bindPopup(`
                <b>Último punto de la ruta</b><br>
                <b>Fecha:</b> ${data.locations[data.locations.length - 1].lastUpdate}<br>
                <b>Ubicación:</b> <a href="https://www.google.com/maps?q=${endPoint[0]},${endPoint[1]}" target="_blank">${endPoint[0]}, ${endPoint[1]}</a><br>
                <b>Dirección:</b> <a href="javascript:void(0);" onclick="showAddress(${endPoint[0]}, ${endPoint[1]}, this)">Mostrar calle</a><br>
                <b>Tiempo transcurrido:</b> ${timeDiffHours} horas y ${timeDiffMinutes} minutos<br>
                <b>Distancia recorrida:</b> ${totalDistance.toFixed(2)} km<br>`);
            startEndMarkers.push(endMarker); // Almacenar el marcador
    
            // Crear o actualizar el marcador para el GPS seleccionado
            if (markers[phoneNumber]) {
                // Si el marcador ya existe, actualizar su posición y rotación
                markers[phoneNumber].setLatLng([startPoint[0], startPoint[1]]);
                const rotationAngle = routeCoordinates[0][3] ? routeCoordinates[0][3] : 0; // Usar `direction` para rotación
                markers[phoneNumber].setRotationAngle(rotationAngle);
            } else {
                // Crear un nuevo marcador con la imagen de una flecha
                markers[phoneNumber] = L.marker([startPoint[0], startPoint[1]], {
                    icon: L.icon({
                        iconUrl: 'https://img.icons8.com/?size=100&id=UfftIT7em2K0&format=png&color=000000', 
                        iconSize: [40, 40], // Ajustar el tamaño de la flecha
                        iconAnchor: [20, 20], // Centrar el ancla en la flecha
                        popupAnchor: [0, -20] // Ajustar el popup
                    })
                }).addTo(map);
    
                // Actualizar la rotación si la dirección está disponible
                const rotationAngle = routeCoordinates[0][3] ? routeCoordinates[0][3] : 0;
                markers[phoneNumber].setRotationAngle(rotationAngle);
            }
    
            // Añadir un tooltip al marcador
            markers[phoneNumber].bindTooltip(userName, { permanent: true, direction: 'top', className: 'custom-tooltip' }).openTooltip();
    
            // Realizar un zoom proporcional a toda la ruta con animación
            const bounds = routePath.getBounds(); // Obtener los límites de la ruta
            map.flyToBounds(bounds, { // Aplicar animación para mostrar toda la ruta
                animate: true,
                duration: 2, // Duración de la animación en segundos
                padding: [20, 20] // Agregar margen al límite
            });
    
            // console.log('Ruta cargada con éxito:', {
            //     phoneNumber,
            //     startDate: formattedStartDate,
            //     endDate: formattedEndDate,
            //     routeCoordinates,
            //     totalDistance: totalDistance.toFixed(2) + ' km'
            // });
        } catch (error) {
          //  console.error('Error fetching route:', error);
        } finally {
            // Ocultar el overlay y el spinner de carga
            loadingOverlay.classList.remove('active');
        }
        document.getElementById('startRouteButton').classList.remove('hidden');

    }
    

    function changeSpeed() {
        // Obtener la nueva velocidad seleccionada
        const speedControl = document.getElementById('speedControl');
        speedMultiplier = parseFloat(speedControl.value);
    
        // Ajustar el tiempo por paso para hacer la animación más rápida
        timePerStep = 10 / speedMultiplier;  // Reducir el tiempo por paso para mayor velocidad
    
        // Reducir ligeramente el número de pasos
        numSteps = Math.max(100, 200 / speedMultiplier);  // Reducir numSteps ligeramente según la velocidad
    
        // Si la animación ya está corriendo, ajustamos la velocidad en tiempo real
        if (isAnimating) {
            // Actualizamos los valores y reiniciamos la animación
            clearTimeout(currentTimeout);  // Limpiar el timeout actual
            moveMarker();  // Llamar de nuevo a moveMarker con la nueva velocidad
        }
    }
    
    
    function startAnimation() {
        if (!routeCoordinates || routeCoordinates.length === 0) {
            //console.error('No route loaded for animation.');
            return;
        }
    
        const gpsSelector = document.getElementById('gpsSelector');
        const phoneNumber = gpsSelector.value;
        marker = markers[phoneNumber];
    
        if (!marker) {
          //  console.error('No marker available for animation.');
            return;
        }
    
        // Reiniciar la animación
        index = 0;
        isPaused = false;
        isAnimating = true;  // Marcar que la animación ha comenzado
        
        // Eliminar cualquier ruta previa antes de comenzar la animación
        if (passedRoutePath) {
            map.removeLayer(passedRoutePath);
            passedRoutePath = null;
        }
        if (remainingRoutePath) {
            map.removeLayer(remainingRoutePath);
            remainingRoutePath = null;
        }

        // Mostrar el botón de Play/Pause
        document.getElementById('pauseResumeButton').classList.remove('hidden');
        document.getElementById('speedControl').classList.remove('hidden');

        minimizeButtonContainer();
    
        // Iniciar la animación
        moveMarker();
    }
    
    function moveMarker() {
        if (isPaused || index >= routeCoordinates.length - 1) {
            return;
        }
    
        const start = routeCoordinates[index];
        const end = routeCoordinates[index + 1];
        let stepIndex = 0;
    
        function interpolate() {
            if (isPaused) {
                return; // Detiene la animación si está en pausa
            }
            if (stepIndex > numSteps) {
                index++;
                moveMarker();
                return;
            }
    
            const lat = start[0] + (end[0] - start[0]) * (stepIndex / numSteps);
            const lng = start[1] + (end[1] - start[1]) * (stepIndex / numSteps);
            const position = [lat, lng];
    
            marker.setLatLng(position);
            map.panTo(position);
    
            // Actualizar las rutas recorrida y no recorrida
            const passedCoordinates = routeCoordinates.slice(0, index + 1);
            const remainingCoordinates = routeCoordinates.slice(index + 1);
    
            if (passedRoutePath) {
                map.removeLayer(passedRoutePath);
            }
            passedRoutePath = L.polyline(passedCoordinates, { color: 'red' }).addTo(map);
    
            if (remainingRoutePath) {
                map.removeLayer(remainingRoutePath);
            }
            remainingRoutePath = L.polyline(remainingCoordinates, { color: '#454B54' }).addTo(map);
    
            const dx = end[1] - start[1];
            const dy = end[0] - start[0];
            const angle = Math.atan2(dy, dx) * (180 / Math.PI);
            marker.setRotationAngle(angle);
    
            // Calcular y mostrar el porcentaje de avance
            const progress = ((index + stepIndex / numSteps) / (routeCoordinates.length - 1)) * 100;
            console.log(`Progreso de la ruta: ${progress.toFixed(2)}%`);
    
            stepIndex++;
            currentTimeout = setTimeout(interpolate, timePerStep / speedMultiplier);
        }
    
        interpolate();
    }
    
    
    
  // La función de pausa/reanudación
function toggleAnimation() {
    isPaused = !isPaused;
    const btn = document.getElementById("pauseResumeButton");
    const icon = document.getElementById("playPauseIcon");

    if (isPaused) {
        icon.classList.remove("fa-play");
        icon.classList.add("fa-pause");
    } else {
        icon.classList.remove("fa-pause");
        icon.classList.add("fa-play");
    }

    if (!isPaused) {
        moveMarker(); // Reanudar animación desde el último punto
    }
}
    
    
    
    async function getAddress(lat, lng) {
        const key = `${lat},${lng}`;
        if (addressCache[key]) {
            return addressCache[key];
        }
    
        try {
            const response = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE`);
            const data = await response.json();
            if (data.status === 'OK' && data.results.length > 0) {
                const address = data.results[0].formatted_address;
                addressCache[key] = address; // Almacenar en la caché
                return address;
            } else {
                return 'Address not found';
            }
        } catch (error) {
          //  console.error('Error fetching address:', error);
            return 'Error fetching address';
        }
    }   


        function resetMap() {
            // Limpiar el mapa
            clearMarkers();
            if (routePath) {
                map.removeLayer(routePath);
                routePath = null;
            }
        
            // Eliminar las rutas de animación (pasada y restante)
            if (passedRoutePath) {
                map.removeLayer(passedRoutePath);
                passedRoutePath = null;
            }
        
            if (remainingRoutePath) {
                map.removeLayer(remainingRoutePath);
                remainingRoutePath = null;
            }
        
            // Eliminar los marcadores del primer y último punto si existen
            startEndMarkers.forEach(marker => {
                map.removeLayer(marker); // Eliminar del mapa
            });
            startEndMarkers = []; // Limpiar el arreglo de marcadores
        
            // Reiniciar las ubicaciones recientes y el estado
            lastKnownLocations = {};
            markers = {};
            routeCoordinates = [];
        
            // Reiniciar el intervalo de actualizaciones si está detenido
            if (!recentLocationsInterval) {
                recentLocationsInterval = setInterval(loadRecentLocations, 10000);
            }
        
            // Recargar las opciones de GPS y las ubicaciones recientes
            document.getElementById('gpsList').innerHTML = ''; // Limpiar la lista de GPS
            document.getElementById('gpsSelector').innerHTML = ''; // Limpiar el selector
            const gpsSelector = document.getElementById('gpsSelector');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
        
            if (gpsSelector) gpsSelector.value = ''; // Restablecer selector de GPS
            if (startDate) startDate.value = ''; // Restablecer fecha de inicio
            if (endDate) endDate.value = ''; // Restablecer fecha de fin
            loadGpsOptions();
        
            // Restablecer el mapa al zoom y coordenadas iniciales con animación más lenta
            const initialCoordinates = [19.4202403, -102.0686549];
            const initialZoom = 15;
        
            map.options.zoomAnimation = true; // Asegurar que la animación de zoom esté habilitada
            map.flyTo(initialCoordinates, initialZoom, { 
                animate: true, 
                duration: 2 // Duración de la animación en segundos 
            });
            // Ocultar los botones de "Iniciar Ruta" y "Play/Pause" después de resetear el mapa
            document.getElementById('startRouteButton').classList.add('hidden');
            document.getElementById('pauseResumeButton').classList.add('hidden');
            document.getElementById('speedControl').classList.add('hidden');
            minimizeButtonContainer();
        }    

function toggleButtonContainer() {
    var container = document.getElementById('buttonContainer');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Make the floating button draggable
dragElement(document.getElementById("floatingButton"));

function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    elmnt.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // Get the mouse cursor position at startup
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // Call a function whenever the cursor moves
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // Calculate the new cursor position
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // Set the element's new position
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        // Stop moving when mouse button is released
        document.onmouseup = null;
        document.onmousemove = null;
    }
}
    document.addEventListener('DOMContentLoaded', initMap);


    function clearSearch() {
        document.getElementById('gpsSearch').value = '';
        filterGpsList();
    }
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const maximizeButton = document.getElementById('maximizeButton');
    
        if (sidebar.classList.contains('minimized')) {
            // Maximizar la barra lateral
            sidebar.classList.remove('minimized');
            maximizeButton.style.display = 'none';
        } else {
            // Minimizar la barra lateral
            sidebar.classList.add('minimized');
            maximizeButton.style.display = 'flex'; // Mostrar botón circular
        }
    }

// Add this function to adjust sidebar height
function adjustSidebarHeight() {
    const sidebar = document.getElementById('sidebar');
    const mapHeight = document.getElementById('map').offsetHeight;
    sidebar.style.height = (mapHeight - 24) + 'px'; // 24px for the margins
    
    // Also adjust the gps-list height to ensure scrolling works properly
    const gpsList = document.getElementById('gpsList');
    const sidebarHeader = sidebar.querySelector('h4');
    const searchBox = document.getElementById('gpsSearch');
    const gpsTitles = document.querySelector('.gps-titles');
    
    const headerHeight = sidebarHeader ? sidebarHeader.offsetHeight : 0;
    const searchHeight = searchBox ? searchBox.offsetHeight : 0;
    const titlesHeight = gpsTitles ? gpsTitles.offsetHeight : 0;
    
    // Calculate available height for the list
    const availableHeight = mapHeight - headerHeight - searchHeight - titlesHeight - 80; // 80px for padding and margins
    
    if (gpsList) {
        gpsList.style.maxHeight = availableHeight + 'px';
        gpsList.style.overflowY = 'auto';
    }
}

// Add this to the existing document.addEventListener('DOMContentLoaded', ...) function
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Adjust sidebar height when page loads
    adjustSidebarHeight();
    
    // Adjust sidebar height when window is resized
    window.addEventListener('resize', adjustSidebarHeight);
});
    