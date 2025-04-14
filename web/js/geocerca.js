var mapOptions;
var map;

var coordinates = []
let new_coordinates = []
let lastElement
var polygonsMap = {}; 
var selectedShape; // Move this to global scope

function InitMap() {
    var location = new google.maps.LatLng(19.4091657,-102.076571)
    mapOptions = {
        zoom: 15,
        center: location,
        mapTypeId: google.maps.MapTypeId.RoadMap
    }
    map = new google.maps.Map(document.getElementById('map'), mapOptions)
    
    // Add this code to display existing geofences
    const existingGeofences = geofencesData;
    existingGeofences.forEach(geofence => {
        const coordinates = geofence.coordinates.split('|').map(coord => {
            const [lat, lng] = coord.split(',');
            return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
        });

        const polygon = new google.maps.Polygon({
            paths: coordinates,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            editable: false
        });

        polygon.setMap(map);
        polygonsMap[geofence.id] = polygon; 
        
// Calculate the top position of the polygon
const topPoint = coordinates.reduce((highest, coord) => {
    return coord.lat() > highest.lat() ? coord : highest;
}, coordinates[0]);

// Add a tooltip to the polygon
const tooltip = new google.maps.Marker({
    position: topPoint, // Use the top point instead of the center
    map: map,
    icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 0, // Invisible marker
    },
    label: {
        text: geofence.name,
        color: '#000',
        fontSize: '14px',
        className: 'custom-tooltip', // Custom class for styling
    }
});
        
    document.getElementById('geofenceList').addEventListener('change', function (e) {
        if (e.target.classList.contains('geofence-checkbox')) {
            const geofenceId = e.target.getAttribute('data-id');
            const polygon = polygonsMap[geofenceId];

            if (e.target.checked) {
                polygon.setMap(map); 
            } else {
                polygon.setMap(null); 
            }
        }
    });
    
        // Add click listener to polygon
        google.maps.event.addListener(polygon, 'click', function() {
            Swal.fire({
                title: '¿Editar Geofence?',
                text: `¿Desea editar "${geofence.name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, editar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    polygon.setEditable(true);
                    // Show save button
            document.getElementById('save-polygon-changes').style.display = 'block';
                    // Store current polygon in global variable
                    window.currentEditingPolygon = {
                        polygon: polygon,
                        geofenceId: geofence.id
                    };
                }
            });
        });
    });

    var all_overlays = [];
    // Remove this line: var selectedShape;
    var drawingManager = new google.maps.drawing.DrawingManager({
        //drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [
                google.maps.drawing.OverlayType.POLYGON,
                google.maps.drawing.OverlayType.POLYLINE
            ]
        },
        polygonOptions: {
            clickable: true,
            draggable: true,
            editable: true,
            // fillColor: '#ffff00',
            fillColor: '#00000',
            fillOpacity: 0.5,

        },

    });

    function clearSelection() {
        if (selectedShape) {
            selectedShape.setEditable(false);
            selectedShape = null;
        }
    }
    //to disable drawing tools
    function stopDrawing() {
        drawingManager.setMap(null);
    }

    function setSelection(shape) {
        clearSelection();
        stopDrawing()
        selectedShape = shape;
        shape.setEditable(true);
    }

    function deleteSelectedShape() {
        if (selectedShape) {
            selectedShape.setMap(null);
            drawingManager.setMap(map);
            coordinates.splice(0, coordinates.length);
            document.getElementById('info').innerHTML = "";

            // Hide the delete button after deleting the shape
            document.getElementById('delete-button-container').style.display = 'none';
        }
    }

    function CenterControl(controlDiv, map) {
        // Set CSS for the control border.
        var controlUI = document.createElement('div');
        controlUI.style.backgroundColor = '#fff';
        controlUI.style.border = '2px solid #fff';
        controlUI.style.borderRadius = '3px';
        controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
        controlUI.style.cursor = 'pointer';
        controlUI.style.marginBottom = '22px';
        controlUI.style.textAlign = 'center';
        controlUI.title = 'Select to delete the shape';
        controlUI.id = 'delete-button-container'; // Add an ID for easy access
        controlUI.style.display = 'none'; // Initially hide the button
        controlDiv.appendChild(controlUI);

        // Set CSS for the control interior.
        var controlText = document.createElement('div');
        controlText.style.color = 'rgb(25,25,25)';
        controlText.style.fontFamily = 'system-ui, sans-serif';
        controlText.style.fontSize = '16px';
        controlText.style.lineHeight = '28px';
        controlText.style.paddingLeft = '5px';
        controlText.style.paddingRight = '5px';
        controlText.innerHTML = 'Delete Selected Area';
        controlUI.appendChild(controlText);

        // Add event listener to delete the polygon
        controlUI.addEventListener('click', function () {
            deleteSelectedShape();
            // Hide the button after deleting the shape
            document.getElementById('delete-button-container').style.display = 'none';
        });
    }

    drawingManager.setMap(map);

    var getPolygonCoords = function (newShape) {
        console.log('getPolygonCoords called with shape:', newShape);
        coordinates.splice(0, coordinates.length);
        console.log('Coordinates array cleared:', coordinates);

        var len = newShape.getPath().getLength();
        console.log('Path length:', len);

        for (var i = 0; i < len; i++) {
            var point = newShape.getPath().getAt(i).toUrlValue(6);
            coordinates.push(point);
            console.log(`Added point ${i}:`, point);
        }
        
        console.log('Final coordinates array:', coordinates);
        document.getElementById('info').innerHTML = coordinates.join('|');
    }

    google.maps.event.addListener(drawingManager, 'polygoncomplete', function (event) {
        console.log('Polygon complete event fired');
        console.log('Initial path length:', event.getPath().getLength());
        
        // Call getPolygonCoords immediately to set initial coordinates
        getPolygonCoords(event);
        
        google.maps.event.addListener(event, "dragend", function() {
            console.log('Drag end event fired');
            getPolygonCoords(event);
        });

        google.maps.event.addListener(event.getPath(), 'insert_at', function () {
            console.log('Insert at event fired');
            getPolygonCoords(event);
        });

        google.maps.event.addListener(event.getPath(), 'set_at', function () {
            console.log('Set at event fired');
            getPolygonCoords(event);
        });

        // Show the delete button when a shape is drawn
        document.getElementById('delete-button-container').style.display = 'block';
    });

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
        all_overlays.push(event);
        if (event.type !== google.maps.drawing.OverlayType.MARKER) {
            drawingManager.setDrawingMode(null);

            var newShape = event.overlay;
            newShape.type = event.type;
            google.maps.event.addListener(newShape, 'click', function () {
                setSelection(newShape);
            });
            setSelection(newShape);

            // Show the delete button when a shape is drawn
            document.getElementById('delete-button-container').style.display = 'block';
        }
    });

    var centerControlDiv = document.createElement('div');
    var centerControl = new CenterControl(centerControlDiv, map);

    
    centerControlDiv.index = 1;
    map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(centerControlDiv);

}


document.getElementById('floating-button').addEventListener('click', function() {
    if (coordinates.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Área requerida',
            text: 'Por favor dibuje un área primero',
            confirmButtonText: 'Aceptar'
        });
        return;
    }       

    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('geofenceModal'));
    modal.show();
});



function populateGeofenceList() {
    const geofenceList = document.getElementById('geofenceList');
    const existingGeofences = geofencesData;

    geofenceList.innerHTML = existingGeofences.map(geofence => `
        <div class="item-item geofence-item" data-id="${geofence.id}">
            <input type="checkbox" class="geofence-checkbox" data-id="${geofence.id}" checked>
            <div class="geofence-info">
                <strong>${geofence.name}</strong>
            </div>
            <div class="geofence-actions">
                <button class="btn-icon" onclick="editGeofence(${geofence.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-icon-delete" onclick="deleteGeofence(${geofence.id})" title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function editGeofence(id) {
    const existingGeofences = geofencesData;
    const geofence = existingGeofences.find(g => g.id === id);
    
    if (geofence) {
        // Load the coordinates of the existing geofence into the global coordinates array
        coordinates = geofence.coordinates.split('|');
        console.log('Loaded coordinates for editing:', coordinates);
        
        const modal = new bootstrap.Modal(document.getElementById('geofenceModal'));
        document.getElementById('geofenceModalLabel').textContent = 'Editar Geofence';
        document.getElementById('geofenceId').value = geofence.id;
        document.getElementById('geofenceName').value = geofence.name;
        document.getElementById('geofenceDescription').value = geofence.description;
        modal.show();
    }
}

document.getElementById('searchGeofence').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const geofenceItems = document.querySelectorAll('.geofence-item');
    
    geofenceItems.forEach(item => {
        const name = item.querySelector('strong').textContent.toLowerCase();
        const description = item.querySelector('p').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || description.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});



function deleteGeofence(id) {
    const geofence = geofencesData.find(g => g.id === id);
    
    if (!geofence) {
        console.error('Geofence not found:', id);
        return;
    }
    
    Swal.fire({
        title: '¿Eliminar geofence?',
        text: `¿Estás seguro de eliminar "${geofence.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Eliminar',
        cancelButtonText: 'Cancelar',
        background: '#fff',
        showClass: {
            popup: 'animate__animated animate__fadeIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOut'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/geocerca/delete-ajax?id=${id}`, {
                method: 'POST',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the polygon from the map
                    if (polygonsMap[id]) {
                        polygonsMap[id].setMap(null);
                        delete polygonsMap[id];
                    }
                    
                    // Remove the geofence from the geofencesData array
                    const index = geofencesData.findIndex(g => g.id === id);
                    if (index !== -1) {
                        geofencesData.splice(index, 1);
                    }
                    
                    // Remove the item from the UI
                    const item = document.querySelector(`.geofence-item[data-id="${id}"]`);
                    if (item) {
                        item.style.animation = 'slideOut 0.3s ease forwards';
                        setTimeout(() => {
                            item.remove();
                        }, 300);
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'Geofence eliminado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    console.error('Error deleting geofence:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar el geofence: ' + data.message,
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el geofence: ' + error.message,
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
}

// Update the saveGeofenceData event listener to handle both create and update
document.getElementById('saveGeofenceData').addEventListener('click', function() {
    console.log('Save button clicked');
    
    const id = document.getElementById('geofenceId').value;
    const name = document.getElementById('geofenceName').value;
    const description = document.getElementById('geofenceDescription').value;
    
    console.log('Form data:', { id, name, description });
    console.log('Current coordinates:', coordinates);

    if (!name || !description) {
        console.log('Name or description missing:', { name, description });
        Swal.fire({
            icon: 'warning',
            title: 'Campos requeridos',
            text: 'Nombre y descripción son requeridos',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    // Check if we have coordinates for new geofences only
    if (coordinates.length === 0 && !id) {
        console.log('No coordinates and no ID (new geofence)');
        Swal.fire({
            icon: 'warning',
            title: 'Área requerida',
            text: 'Por favor dibuje un área primero',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    const url = id ? `/geocerca/update?id=${id}` : '/geocerca/create-ajax';
    const coordString = coordinates.join('|');
    console.log('URL:', url);
    console.log('Coordinates string:', coordString);
    
    const geofenceData = {
        coordinates: coordString,
        name: name,
        description: description
    };

    console.log('Sending data:', geofenceData);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(geofenceData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        // Check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, throw an error with the text response
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text);
            });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Add the new geofence to the geofencesData array if it's a new one
            if (!id && data.id) {
                geofencesData.push({
                    id: data.id,
                    name: name,
                    description: description,
                    coordinates: coordString
                });
                
                // Add the new geofence to the map
                const newCoordinates = coordString.split('|').map(coord => {
                    const [lat, lng] = coord.split(',');
                    return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
                });
                
                const polygon = new google.maps.Polygon({
                    paths: newCoordinates,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    editable: false
                });
                
                polygon.setMap(map);
                polygonsMap[data.id] = polygon;
                
                // Update the geofence list
                populateGeofenceList();
            } else if (id) {
                // Update the existing geofence in the geofencesData array
                const index = geofencesData.findIndex(g => g.id == id);
                if (index !== -1) {
                    geofencesData[index].name = name;
                    geofencesData[index].description = description;
                    if (coordString) {
                        geofencesData[index].coordinates = coordString;
                    }
                    
                    // Update the polygon on the map if coordinates were changed
                    if (coordString && polygonsMap[id]) {
                        const updatedCoordinates = coordString.split('|').map(coord => {
                            const [lat, lng] = coord.split(',');
                            return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
                        });
                        
                        polygonsMap[id].setPath(updatedCoordinates);
                    }
                    
                    // Update the geofence list
                    populateGeofenceList();
                }
            }
            
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Geofence guardado exitosamente',
                showConfirmButton: false,
                timer: 1500
            });
            
            // Clear the form and coordinates
            document.getElementById('geofenceId').value = '';
            document.getElementById('geofenceName').value = '';
            document.getElementById('geofenceDescription').value = '';
            document.getElementById('geofenceModalLabel').textContent = 'Nueva Geofence';
            coordinates = [];
            
            // Check if selectedShape exists before trying to use it
            if (typeof selectedShape !== 'undefined' && selectedShape) {
                selectedShape.setMap(null);
                selectedShape = null;
            }
            
            // Hide the delete button
            if (document.getElementById('delete-button-container')) {
                document.getElementById('delete-button-container').style.display = 'none';
            }
        } else {
            console.error('Error saving geofence:', data.message);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar el geofence: ' + JSON.stringify(data.message),
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar el geofence: ' + error.message,
            confirmButtonText: 'Aceptar'
        });
    });

    const modal = bootstrap.Modal.getInstance(document.getElementById('geofenceModal'));
    if (modal) {
        modal.hide();
    } else {
        $('#geofenceModal').modal('hide'); // Fallback for older Bootstrap versions
    }
});

// Call this after InitMap()
populateGeofenceList();

InitMap()



// Add this new event listener after the existing code

document.getElementById('save-polygon-changes').addEventListener('click', function() {
    if (!window.currentEditingPolygon) {
        return;
    }

    Swal.fire({
        title: '¿Guardar cambios?',
        text: '¿Está seguro de guardar los cambios en el geofence?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Get coordinates from current editing polygon
            const path = window.currentEditingPolygon.polygon.getPath();
            const coordinates = [];
            for (let i = 0; i < path.getLength(); i++) {
                const point = path.getAt(i);
                coordinates.push(`${point.lat()},${point.lng()}`);
            }

            // Send update request
            fetch(`/geocerca/update-coordinates?id=${window.currentEditingPolygon.geofenceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    coordinates: coordinates.join('|')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Coordenadas actualizadas exitosamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar las coordenadas',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar las coordenadas',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
});
function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.item-item input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        toggleMarker(checkbox.id);
    });
}