let map;
let geofenceLayer;
let geofenceCoordinates = [];

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

}

function saveGeofence() {
    fetch('save-geofence', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': yii.getCsrfToken()
        },
        body: JSON.stringify({ geofenceData: geofenceCoordinates })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Geocerca guardada exitosamente');
        } else {
            alert('Error al guardar la geocerca');
        }
    })
    .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    initGeofenceMap();
});