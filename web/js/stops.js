document.addEventListener('DOMContentLoaded', function () {
    console.log("DOMContentLoaded event detected");

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

    const filter = document.getElementById('filter');
    const customDates = document.querySelectorAll('.custom-dates');
    const deviceColumn = document.querySelector('.col-lg-2.col-md-4.col-12').nextElementSibling;
    const showButtonColumn = deviceColumn.nextElementSibling;
    const exportButtonColumn = showButtonColumn.nextElementSibling;

    // Función para ajustar la visibilidad y el orden de los campos de fecha
    function adjustDateFields() {
        console.log("adjustDateFields called");
        console.log("Filter value:", filter.value);
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
        console.log("Filter change event detected");
        adjustDateFields();
    });

    // Mostrar las fechas si ya estaban seleccionadas como "Personalizado"
    if (filter.value === 'custom') {
        adjustDateFields();
    }

});

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
