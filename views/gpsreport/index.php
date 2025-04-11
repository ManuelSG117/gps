<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<link href="/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="/vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA73efm01Xa11C5aXzXBGFbWUjMtkad5HE"></script>
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>

<?php Pjax::begin(['id' => 'gps-report-pjax', 'timeout' => 10000]); ?>
<div class="gps-report-form">
    <?php $form = ActiveForm::begin([
        'method' => 'get', 
        'action' => ['gpsreport/index'],
        'options' => ['data-pjax' => true]
    ]); ?>

    <div class="container-fluid">
    <div class="row align-items-end">
        <!-- Filtro Periodo -->
        <div class="col-lg-2 col-md-4 col-12">
            <div class="form-group">
                <label for="filter">Periodo:</label>
                <?= Html::dropDownList('filter', Yii::$app->request->get('filter', 'today'), [
                    'today' => 'Hoy',
                    'yesterday' => 'Ayer',
                    'current_week' => 'Semana Actual',
                    'last_week' => 'Semana Anterior',
                    'current_month' => 'Mes Actual',
                    'last_month' => 'Mes Anterior',
                    'custom' => 'Personalizado',
                ], [
                    'class' => 'form-control',
                    'id' => 'filter',
                ]) ?>
            </div>
        </div>

        <!-- Filtro Dispositivo -->
        <div class="col-lg-2 col-md-4 col-12">
            <div class="form-group">
                <label for="gps">Dispositivo:</label>
                <?= Html::dropDownList('gps', Yii::$app->request->get('gps', null), GpsLocations::find()->select(['phoneNumber'])->indexBy('phoneNumber')->column(), [
                    'class' => 'form-control',
                    'id' => 'gps',
                ]) ?>
            </div>
        </div>

        <!-- Botón Mostrar -->
        <div class="col-lg-2 col-md-4 col-12">
            <?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary w-100']) ?>
        </div>

        <!-- Exportar a Excel -->
        <div class="col-lg-2 col-md-4 col-12">
            <?= Html::a('Export to Excel', '#', [
                'class' => 'btn btn-success w-100',
                'onclick' => 'return confirmExport();'
            ]) ?>
        </div>

        <!-- Rango de Fechas (se muestra solo cuando se selecciona 'Personalizado') -->
        <div class="col-lg-4 col-md-12 col-12 custom-dates" style="display: none;">
            <div class="row">
                <div class="col-6">
                    <?= Html::input('text', 'startDate', Yii::$app->request->get('startDate', null), [
                        'class' => 'form-control',
                        'id' => 'startDate',
                        'placeholder' => 'Desde:',
                    ]) ?>
                </div>
                <div class="col-6">
                    <?= Html::input('text', 'endDate', Yii::$app->request->get('endDate', null), [
                        'class' => 'form-control',
                        'id' => 'endDate',
                        'placeholder' => 'Hasta:',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<br>

    <?php ActiveForm::end(); ?>
</div>

<div class="card" <?= empty($locations) ? 'style="display: none;"' : '' ?>>
    <div class="card-body p-0">
        <div class="table-responsive active-projects">
            <div class="tbl-caption">
                <h4 class="heading mb-0">Reporte Ruta</h4>
            </div>
            <table id="projects-tbl" class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Velocidad</th>
                        <th>Dirección</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <tr>
                            <td><?= $location->lastUpdate ?></td>
                            <td><?= $location->latitude ?></td>
                            <td><?= $location->longitude ?></td>
                            <td><?= $location->speed ?> km/h</td>
                            <td>
                            <a href="javascript:void(0);" 
                            onclick="showAddress(<?= $location->latitude ?>, <?= $location->longitude ?>, this)">
                                <b>Mostrar calle</b>
                            </a>
                            <br>
                            <span class="address-result"></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (empty($locations)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay información de ubicación disponible para el período y dispositivo seleccionados.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        });
    </script>
<?php endif; ?>

<!-- After the table card div, add the map container -->
<div class="card mt-4" <?= empty($locations) ? 'style="display: none;"' : '' ?>>
    <div class="card-body" style="padding: 15px;">
        <div class="tbl-caption">
            <h4 class="heading mb-0">Mapa de Ruta</h4>
        </div>
        <div id="map" style="height: 500px; width: 100%; margin-top: 15px; position: relative;"></div>
    </div>
</div>
<?php Pjax::end(); ?>

<script>
// Initialize map and route display
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr
    initFlatpickr();
    
    // Setup filter change event
    setupFilterChangeEvent();
    
    // Initialize the map if we have location data
    initMap();
    
    // Setup Pjax events
    $(document).on('pjax:success', function() {
        initFlatpickr();
        setupFilterChangeEvent();
        initMap();
    });
});

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
    const colSize = 'col-lg-2 col-md-4 col-12'; // Tamaño por defecto para cada campo

    filter.addEventListener('change', function () {
        if (filter.value === 'custom') {
            customDates.style.display = 'flex';
            // Ajustar el tamaño de las columnas
            dateFields.forEach(function(field) {
                field.closest('.col-6').classList.remove('col-6');
                field.closest('.col-6').classList.add(colSize);
            });
        } else {
            customDates.style.display = 'none';
        }
    });

    // Mostrar las fechas si ya estaban seleccionadas como "Personalizado"
    if (filter.value === 'custom') {
        customDates.style.display = 'flex';
        dateFields.forEach(function(field) {
            field.closest('.col-6').classList.remove('col-6');
            field.closest('.col-6').classList.add(colSize);
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
        const map = L.map('map').setView([locations[0].lat, locations[0].lng], 13);
        
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
        const legend = L.control({position: 'bottomright'});
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
            window.location.href = '<?= Url::to(['gpsreport/download-report', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => true]) ?>';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Redirigir con includeChart=false
            window.location.href = '<?= Url::to(['gpsreport/download-report', 'filter' => Yii::$app->request->get('filter'), 'gps' => Yii::$app->request->get('gps'), 'startDate' => Yii::$app->request->get('startDate'), 'endDate' => Yii::$app->request->get('endDate'), 'includeChart' => false]) ?>';
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
</script>

<!-- Add Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<style>
.table-responsive {
    height: calc(100vh - 400px);
    overflow-y: auto;
}

/* Override the padding for the map container specifically */
.card-body {
    padding: 0;
}

.card-body .tbl-caption {
    padding: 15px 15px 0 15px;
}

/* Specific styling for the map card */
.card.mt-4 .card-body {
    padding: 15px !important;
}

#projects-tbl {
    width: 100%;
    table-layout: fixed;
    overflow-x: hidden;
}

#map {
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    z-index: 1;
}

.legend {
    line-height: 18px;
    color: #555;
    background: white;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

.legend i {
    width: 18px;
    height: 18px;
    float: left;
    margin-right: 8px;
    opacity: 0.7;
}

/* Add loading indicator for PJAX */
.pjax-loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.pjax-loading:after {
    content: '';
    width: 50px;
    height: 50px;
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>