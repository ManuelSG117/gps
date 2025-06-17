let gpsQueueAlertActive = false;
function checkGpsQueueAlert() {
    // Si el usuario eligió no volver a mostrar, no mostrar la alerta
    if (localStorage.getItem('gpsQueueAlertDismissed') === 'true') {
        return;
    }
    $.get('/gpslocations/queue-alert', function(data) {
        if (data.alert && !gpsQueueAlertActive) {
            gpsQueueAlertActive = true;
            Swal.fire({
                icon: 'warning',
                title: '¡Alerta de procesamiento GPS!',
                text: 'El sistema está experimentando una acumulación inusual de datos GPS. Por favor, contacte a soporte si el problema persiste.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                showDenyButton: true,
                denyButtonText: 'No volver a mostrar',
                didClose: () => { gpsQueueAlertActive = false; }
            }).then((result) => {
                if (result.isDenied) {
                    localStorage.setItem('gpsQueueAlertDismissed', 'true');
                }
            });
        } else if (!data.alert && gpsQueueAlertActive) {
            gpsQueueAlertActive = false;
            Swal.close();
        }
    });
}
setInterval(checkGpsQueueAlert, 30000); // cada 30 segundos
checkGpsQueueAlert();