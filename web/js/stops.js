function initStops() {
   console.log("initStops called");

    // Inicializar Flatpickr
    flatpickr('#startDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true, 
    });

    flatpickr('#endDate', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true,
    });

    const filter = document.getElementById('filter');
    const customDates = document.querySelectorAll('.custom-dates');
    const deviceColumn = document.querySelector('.col-lg-2.col-md-4.col-12').nextElementSibling;
    const showButtonColumn = deviceColumn.nextElementSibling;
    const exportButtonColumn = showButtonColumn.nextElementSibling;


    // Función para ajustar la visibilidad y el orden de los campos de fecha
    function adjustDateFields() {
      //  console.log("adjustDateFields called");
       // console.log("Filter value:", filter.value);
        if (filter.value === 'custom') {
            customDates.forEach(function(dateField) {
                dateField.style.display = 'block';
            });
            // Mover los campos de fecha después del dispositivo
            deviceColumn.after(...customDates);
            // Mover el botón de mostrar después de los campos de fecha
            customDates[customDates.length - 1].after(showButtonColumn);
            // Mover el botón de exportar después del botón de mostrar
            showButtonColumn.after(exportButtonColumn);
        } else {
            customDates.forEach(function(dateField) {
                dateField.style.display = 'none';
            });
            // Restaurar el orden original
            deviceColumn.after(showButtonColumn);
            showButtonColumn.after(exportButtonColumn);
        }
    }

    filter.addEventListener('change', function () {
    //    console.log("Filter change event detected");
        adjustDateFields();
    });

    // Mostrar las fechas si ya estaban seleccionadas como "Personalizado"
    if (filter.value === 'custom') {
        adjustDateFields();
    }
}

// Llamar a initStops() en la carga inicial
document.addEventListener('DOMContentLoaded', function () {
  //  console.log("DOMContentLoaded event fired");
    initStops();
    
    // Configurar los eventos de pjax una sola vez
    setupPjaxEvents();
});

// Configurar eventos de pjax
function setupPjaxEvents() {
    // Eliminar manejadores existentes para evitar duplicados
    $(document).off('pjax:complete.stopsCheck');
    $(document).off('pjax:success.stopsCheck');
    
    // Registrar nuevos manejadores con namespace para poder eliminarlos después
    $(document).on('pjax:complete.stopsCheck', function() {
    //    console.log('pjax:complete triggered with namespace');
        setTimeout(function() {
            initStops();
            checkForEmptyResults();
        }, 100); // Pequeño retraso para asegurar que el DOM esté actualizado
    });
    
    // También escuchar pjax:success como respaldo
    $(document).on('pjax:success.stopsCheck', function() {
      //  console.log('pjax:success triggered with namespace');
        setTimeout(function() {
            initStops();
            checkForEmptyResults();
        }, 100);
    });
}

// Función separada para verificar resultados vacíos
function checkForEmptyResults() {
   // console.log('Checking for empty results');
    
    // Verificar si hay parámetros en la URL (indica que se realizó una búsqueda)
    const urlParams = new URLSearchParams(window.location.search);
    //console.log('URL params:', Object.fromEntries(urlParams));
    
    const hasSearchParams = urlParams.has('filter') || urlParams.has('gps');
    //console.log('Has search params:', hasSearchParams);
    
    // Solo mostrar el mensaje si hay parámetros de búsqueda y no hay resultados
    const tableRows = document.querySelectorAll('#projects-tbls tbody tr');
    //console.log('Table rows found:', tableRows.length);
    
    // Verificar también si hay un mensaje de "No data available"
    const noDataMessage = document.querySelector('#projects-tbls tbody tr td.dataTables_empty');
    const hasNoResults = tableRows.length === 0 || noDataMessage !== null;
    //console.log('Has no results:', hasNoResults, 'Empty message found:', noDataMessage !== null);
    
    if (hasNoResults && hasSearchParams) {
      //  console.log('Showing SweetAlert - No data');
        Swal.fire({
            title: 'Sin datos',
            text: 'No hay información de paradas disponible para el período y dispositivo seleccionados.',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
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
        //    console.log('El usuario eligió incluir la gráfica.');
            window.location.href = '/gpsreport/download-report-stops' + 
                '?filter=' + encodeURIComponent($('#filter').val()) + 
                '&gps=' + encodeURIComponent($('#gps').val()) + 
                '&startDate=' + encodeURIComponent($('#startDate').val()) + 
                '&endDate=' + encodeURIComponent($('#endDate').val()) + 
                '&includeChart=true';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Redirigir con includeChart=false
          ///  console.log('El usuario eligió no incluir la gráfica.');
            window.location.href = '/gpsreport/download-report-stops' + 
                '?filter=' + encodeURIComponent($('#filter').val()) + 
                '&gps=' + encodeURIComponent($('#gps').val()) + 
                '&startDate=' + encodeURIComponent($('#startDate').val()) + 
                '&endDate=' + encodeURIComponent($('#endDate').val()) + 
                '&includeChart=false';
        }
    });
    return false; 
}


