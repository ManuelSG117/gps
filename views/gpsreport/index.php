<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\models\GpsLocations;
use yii\helpers\Url;


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

<div class="gps-report-form">
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['gpsreport/index']]); ?>

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

<div class="card" >
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
                    <?php if (!empty($locations)): ?>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- <div id="map"></div> -->

<script>
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


    document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Flatpickr
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

    
});
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('filter');
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
});



    </script>

<script>
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
 <!-- Required vendors -->
 <script src="/vendor/global/global.min.js"></script>
	
	
	<!-- Dashboard 1 -->
	<script src="/js/dashboard/dashboard-2.js"></script>

	<script src="/vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="/vendor/datatables/js/dataTables.buttons.min.js"></script>
	<script src="/vendor/datatables/js/buttons.html5.min.js"></script>
	<script src="/js/plugins-init/datatables.init.js"></script>

    <style>
    .table-responsive {
    height: calc(100vh - 400px);  /* Ajusta el 200px según el espacio superior e inferior que necesites */
    overflow-y: auto;
    }

    .card-body {
        padding: 0;
    }

    #projects-tbl {
        width: 100%;
        table-layout: fixed;  /* Opcional, para que las columnas tengan el mismo tamaño */
        overflow-x: hidden;
    }

    </style>