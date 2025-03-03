function initStops() {
   // console.log("initStops called");

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
  //  console.log("DOMContentLoaded event detected");
    initStops();
});
