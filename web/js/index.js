    let map;
    let markers = [];
    let routePath;
    let routeCoordinates = [];
    let recentLocationsInterval; // Variable para almacenar el identificador del intervalo


function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.gps-item input[type="checkbox"]');
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

    // Agregar la capa de Google Maps
    const googleLayer = L.gridLayer.googleMutant({
        type: 'roadmap' // Valores válidos: 'roadmap', 'satellite', 'terrain', 'hybrid'
    });
    map.addLayer(googleLayer);

    loadGpsOptions();
    loadRecentLocations();

    // Almacenar el identificador del intervalo
    recentLocationsInterval = setInterval(loadRecentLocations, 10000); // Refrescar cada 10 segundos
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

                        const button = document.createElement('button');
                        button.textContent = '...';
                        button.className = 'minimal-button';
                        button.onclick = () => {
                        
                        };

                        const div = document.createElement('div');
                        div.className = 'gps-item';
                        div.appendChild(checkbox);
                        div.appendChild(label);
                        div.appendChild(button);

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
    const gpsItems = document.querySelectorAll('.gps-item');

    gpsItems.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchInput)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function loadRecentLocations() {
    fetch('get-locations-time')
        .then(response => response.json())
        .then(data => {
            // Eliminar todos los marcadores existentes
            Object.values(markers).forEach(marker => map.removeLayer(marker));
            markers = {};

            data.forEach(location => {
                // Crear un marcador con la imagen de una flecha
                const marker = L.marker([location.latitude, location.longitude], {
                    icon: L.icon({
                        iconUrl: 'https://img.icons8.com/?size=100&id=UfftIT7em2K0&format=png&color=000000',  // URL de la imagen de la flecha
                        iconSize: [30, 30],
                        iconAnchor: [20, 20],  // Ajusta el ancla al centro de la imagen
                        popupAnchor: [0, -20]  // Ajusta el ancla para los popups
                    })
                }).addTo(map);

                // Si tienes dirección, puedes usarla para rotar la flecha (en grados)
                if (location.direction) {
                    marker.setRotationAngle(location.direction);
                }

                marker.bindTooltip(location.userName, { permanent: true, direction: 'top',className: 'custom-tooltip'}).openTooltip();

                marker.on('click', () => {
                    getAddress(location.latitude, location.longitude).then(address => {
                        marker.bindPopup(`
                            <b>Ubicación:</b> <a href="https://www.google.com/maps?q=${location.latitude},${location.longitude}" target="_blank">${location.latitude}, ${location.longitude}</a><br>
                            <b>Dirección:</b> ${address}<br>
                            <b>Velocidad:</b> ${location.speed} km/h
                        `, { offset: [0, -40] }).openPopup();
                    });
                });

                markers[location.phoneNumber] = marker;
            });
        })
        .catch(error => console.error('Error fetching recent locations:', error));
}



function toggleMarker(phoneNumber) {
        const checkbox = document.getElementById(phoneNumber);
        if (checkbox.checked) {
            markers[phoneNumber].addTo(map);
        } else {
            map.removeLayer(markers[phoneNumber]);
        }
    }

function loadRoute() {
    const gpsSelector = document.getElementById('gpsSelector');
    const phoneNumber = gpsSelector.value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!phoneNumber) {
        alert('Por favor, selecciona un GPS.');
        return;
    }

    // Detener la actualización periódica de ubicaciones recientes
    if (recentLocationsInterval) {
        clearInterval(recentLocationsInterval);
        recentLocationsInterval = null; // Evitar llamadas múltiples a clearInterval
        console.log('Intervalo de actualizaciones detenido.');
    }

    const formattedStartDate = startDate ? new Date(startDate).toISOString().slice(0, 19).replace('T', ' ') : '';
    const formattedEndDate = endDate ? new Date(endDate).toISOString().slice(0, 19).replace('T', ' ') : '';

    let url = `get-route?phoneNumber=${phoneNumber}`;
    if (formattedStartDate) {
        url += `&startDate=${formattedStartDate}`;
    }
    if (formattedEndDate) {
        url += `&endDate=${formattedEndDate}`;
    }

    document.getElementById('loadingScreen').style.display = 'block'; // Mostrar pantalla de carga

    fetch(url)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingScreen').style.display = 'none'; // Ocultar pantalla de carga

            if (data.length === 0) {
                alert('No se encontraron datos para la ruta seleccionada.');
                return;
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
            routeCoordinates = data.map(location => [
                parseFloat(location.latitude),
                parseFloat(location.longitude),
                location.speed, // Esto es para el cálculo de la dirección (si se quiere)
                location.direction // Utiliza `location.direction` si está disponible
            ]);

            // Dibujar la nueva ruta
            routePath = L.polyline(routeCoordinates.map(coord => [coord[0], coord[1]]), { color: 'red' }).addTo(map);
            map.fitBounds(routePath.getBounds());

            // Crear o actualizar el marcador para el GPS seleccionado
            const selectedOption = gpsSelector.options[gpsSelector.selectedIndex];
            const userName = selectedOption.text;

            const startPoint = routeCoordinates[0];

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
            markers[phoneNumber].bindTooltip(userName, { permanent: true, direction: 'top' ,    className: 'custom-tooltip'}).openTooltip();

            console.log('Ruta cargada con éxito:', {
                phoneNumber,
                startDate: formattedStartDate,
                endDate: formattedEndDate,
                routeCoordinates
            });
        })
        .catch(error => {
            document.getElementById('loadingScreen').style.display = 'none'; // Ocultar pantalla de carga
            console.error('Error fetching route:', error);
        });
}



function startAnimation() {
    if (!routeCoordinates || routeCoordinates.length === 0) {
        console.error('No route loaded for animation.');
        return;
    }

    // Seleccionar el primer marcador de la lista
    const gpsSelector = document.getElementById('gpsSelector');
    const phoneNumber = gpsSelector.value;
    const marker = markers[phoneNumber];

    if (!marker) {
        console.error('No marker available for animation.');
        return;
    }

    let index = 0;
    const step = 0.01; // Ajusta este valor para cambiar la velocidad de movimiento
    const numSteps = 200; // Número de pasos entre cada punto
    const timePerStep = 10; // Tiempo en ms entre cada paso

    function moveMarker() {
        if (index >= routeCoordinates.length - 1) {
            console.log('Animation completed.');
            return;
        }

        const start = routeCoordinates[index];
        const end = routeCoordinates[index + 1];
        let stepIndex = 0;

        function interpolate() {
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

            // Calcular la dirección entre dos puntos para rotar la flecha
            const dx = end[1] - start[1];
            const dy = end[0] - start[0];
            const angle = Math.atan2(dy, dx) * (180 / Math.PI);
            marker.setRotationAngle(angle);

            stepIndex++;
            setTimeout(interpolate, timePerStep);
        }

        interpolate();
    }

    console.log('Animation started.');
    moveMarker();
}

function getAddress(lat, lng) {
        return fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'OK' && data.results.length > 0) {
                    return data.results[0].formatted_address;
                } else {
                    return 'Address not found';
                }
            })
            .catch(error => {
                console.error('Error fetching address:', error);
                return 'Error fetching address';
            });
    }


function resetMap() {
    // Eliminar la ruta si existe
    if (routePath) {
        map.removeLayer(routePath);
    }

    // Eliminar todos los marcadores
    Object.values(markers).forEach(marker => map.removeLayer(marker));
    markers = {};

    // Cargar ubicaciones recientes
    loadRecentLocations();

    // Reiniciar el intervalo de actualizaciones si no está activo
    if (!recentLocationsInterval) {
        recentLocationsInterval = setInterval(loadRecentLocations, 10000);
        console.log('Intervalo de actualizaciones reiniciado.');
    }
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


