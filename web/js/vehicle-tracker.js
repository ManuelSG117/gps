// Función para verificar ubicaciones de vehículos
function checkVehicleLocations() {
    // Obtener ubicaciones reales desde el backend
    $.getJSON('/index.php/vehiculo-geocerca/get-vehiculos-ubicacion', function(data) {
        // Si existe el mapa, actualizar marcadores
        if (window.map) {
            updateVehicleMarkers(data);
        }
        
        // Procesar datos independientemente del mapa
        // Los datos ya incluyen la información de geocercas y el backend maneja las notificaciones
    });
}

// Iniciar el seguimiento
$(document).ready(function() {
    // Primera verificación inmediata
    checkVehicleLocations();
    
    // Configurar el intervalo de verificación (30 segundos)
    setInterval(checkVehicleLocations, 30000);
});

// Mantener la función displayVehicles para compatibilidad con código existente
function displayVehicles() {
    checkVehicleLocations();
}

// Mantener la función displayVehiclesReal para compatibilidad
function displayVehiclesReal() {
    checkVehicleLocations();
} 