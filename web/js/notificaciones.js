function loadNotifications() {
    $.get('/notificaciones/get-recent', function(data) {
        let html = '';
        let unreadCount = 0;
        if (data.length === 0) {
            html = '<div class="text-center text-muted">No hay notificaciones</div>';
        } else {
            data.forEach(function(n) {
                if (n.leido == 0) unreadCount++;
                html += `<div class="notification-item${n.leido == 0 ? ' bg-warning bg-opacity-25' : ''}" style="border-bottom:1px solid #eee;padding:10px 5px;position:relative;">
                    <div style="display:flex;align-items:center;">
                        ${n.leido == 0 ? `<span class='mark-read-dot' data-id='${n.id}' title='Marcar como leída' style='display:inline-block;width:12px;height:12px;border-radius:50%;background:#28a745;margin-right:8px;cursor:pointer;border:2px solid #fff;box-shadow:0 0 2px #888;'></span>` : '<span style="display:inline-block;width:12px;height:12px;margin-right:8px;"></span>'}
                        <strong>${n.tipo.charAt(0).toUpperCase() + n.tipo.slice(1)}</strong>
                    </div>
                    <div>${n.mensaje}</div>
                    <div style="font-size:12px;color:#888;">${n.fecha_creacion}</div>
                    <div style="margin-top:5px;">
                        <button class='btn btn-xs btn-danger delete-btn' data-id='${n.id}'>Eliminar</button>
                    </div>
                </div>`;
            });
        }
        $('#DZ_W_Notification1').html(html);
        if (unreadCount > 0) {
            $('#notification-badge').text(unreadCount).show();
        } else {
            $('#notification-badge').hide();
        }
    });
}
$(function(){
    // Consulta vencimientos de pólizas solo una vez por día
    var hoy = (new Date()).toISOString().slice(0,10);
    if (localStorage.getItem('polizaCheck') !== hoy) {
        $.get('/poliza-seguro/check-vencimientos', function(resp){
            if(resp.success && resp.notificaciones && resp.notificaciones.length > 0){
                resp.notificaciones.forEach(function(n){
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Atención! Póliza por vencer',
                        html: `La póliza de seguro del vehículo <b>${n.vehiculo}</b> vence en <b>${n.dias == 1 ? '1 día' : (n.dias == 7 ? '1 semana' : '1 mes')}</b> (${n.fecha_vencimiento})`,
                        timer: 10000
                    });
                });
            }
        });
        localStorage.setItem('polizaCheck', hoy);
    }
    loadNotifications();
    setInterval(loadNotifications, 60000);
    $(document).on('click', '.mark-read-dot', function(){
        var id = $(this).data('id');
        $.post('/notificaciones/mark-as-read?id='+id, function(resp){
            if(resp.success) loadNotifications();
        });
    });
    $(document).on('click', '.delete-btn', function(){
        if(!confirm('¿Eliminar esta notificación?')) return;
        var id = $(this).data('id');
        $.post('/notificaciones/delete?id='+id, function(resp){
            if(resp.success) loadNotifications();
        });
    });
}); 