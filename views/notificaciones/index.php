<?php
use yii\helpers\Html;
use yii\helpers\Url;
/** @var yii\web\View $this */
/** @var app\models\Notificaciones[] $notificaciones */
$this->title = 'Historial de Notificaciones';
$this->params['breadcrumbs'][] = $this->title;

// Obtener opciones únicas para los selects
$tipos = array_unique(array_map(function($n){ return $n->tipo; }, $notificaciones));
$tipos = array_filter($tipos, function($v){ return $v !== null && $v !== ''; });
sort($tipos);
$vehiculos = array_unique(array_map(function($n){ return $n->vehiculo ? $n->vehiculo->identificador : '-'; }, $notificaciones));
$vehiculos = array_filter($vehiculos, function($v){ return $v !== null && $v !== ''; });
sort($vehiculos);
?>
<div class="container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="d-flex justify-content-end align-items-center mb-2">
        <button id="mark-all-read-btn-table" class="btn btn-link btn-sm" style="color:#452B90;font-weight:bold;">Marcar todas como leídas</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped" id="notificaciones-table">
            <thead>
                <tr>
                    <th>Tipo<br>
                        <select class="form-control form-control-sm filter-input" data-col="0">
                            <option value="">Todos</option>
                            <?php foreach($tipos as $tipo): ?>
                                <option value="<?= Html::encode($tipo) ?>"><?= Html::encode($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                    <th>Mensaje<br><input type="text" class="form-control form-control-sm filter-input" data-col="1" placeholder="Filtrar mensaje"></th>
                    <th>Vehículo<br>
                        <select class="form-control form-control-sm filter-input" data-col="2">
                            <option value="">Todos</option>
                            <?php foreach($vehiculos as $vehiculo): ?>
                                <option value="<?= Html::encode($vehiculo) ?>"><?= Html::encode($vehiculo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                    <th>Leído<br>
                        <select class="form-control form-control-sm filter-input" data-col="3">
                            <option value="">Todos</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </th>
                    <th>Fecha Creación<br><input type="text" class="form-control form-control-sm filter-input flatpickr-fecha" data-col="4" placeholder="Filtrar fecha"></th>
                    <th>Fecha Lectura<br><input type="text" class="form-control form-control-sm filter-input flatpickr-fecha" data-col="5" placeholder="Filtrar fecha"></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notificaciones as $n): ?>
                    <tr class="<?= $n->leido ? '' : 'table-warning' ?>">
                        <td><?= Html::encode($n->tipo) ?></td>
                        <td><?= Html::encode($n->mensaje) ?></td>
                        <td><?= $n->vehiculo ? Html::encode($n->vehiculo->identificador) : '-' ?></td>
                        <td><?= $n->leido ? 'Sí' : 'No' ?></td>
                        <td><?= Html::encode($n->fecha_creacion) ?></td>
                        <td><?= $n->fecha_lectura ? Html::encode($n->fecha_lectura) : '-' ?></td>
                        <td>
                            <?php if (!$n->leido): ?>
                                <button class="btn btn-sm btn-success mark-read-btn" data-id="<?= $n->id ?>">Marcar como leída</button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $n->id ?>">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function(){
    $('.mark-read-btn').on('click', function(){
        var id = $(this).data('id');
        $.post('<?= Url::to(['notificaciones/mark-as-read']) ?>?id='+id, function(resp){
            if(resp.success) location.reload();
        });
    });
    $('.delete-btn').on('click', function(){
        if(!confirm('¿Eliminar esta notificación?')) return;
        var id = $(this).data('id');
        $.post('<?= Url::to(['notificaciones/delete']) ?>?id='+id, function(resp){
            if(resp.success) location.reload();
        });
    });

    // Filtros de tabla
    $('.filter-input').on('input change', function() {
        var table = $('#notificaciones-table');
        var rows = table.find('tbody tr');
        rows.show();
        $('.filter-input').each(function() {
            var col = $(this).data('col');
            var val = $(this).val().toLowerCase();
            if (val) {
                rows = rows.filter(function() {
                    var cell = $(this).find('td').eq(col);
                    var cellText = cell.text().toLowerCase();
                    if($(this).find('td').eq(col).find('select').length) {
                        return cellText === val;
                    }
                    if($(this).is(':visible')) {
                        return cellText.indexOf(val) > -1;
                    }
                    return false;
                });
            }
        });
        table.find('tbody tr').hide();
        rows.show();
    });

    // Flatpickr para los campos de fecha
    flatpickr('.flatpickr-fecha', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        locale: 'es'
    });

    // Botón marcar todas como leídas en la tabla
    $('#mark-all-read-btn-table').on('click', function(){
        $.post('<?= Url::to(['notificaciones/mark-all-as-read']) ?>', function(resp){
            if(resp.success) location.reload();
        });
    });
});
</script> 