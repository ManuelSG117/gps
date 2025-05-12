<?php
use yii\helpers\Html;
use yii\helpers\Url;
/** @var yii\web\View $this */
/** @var app\models\Notificaciones[] $notificaciones */
$this->title = 'Historial de Notificaciones';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container mt-4">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Mensaje</th>
                    <th>Vehículo</th>
                    <th>Leído</th>
                    <th>Fecha Creación</th>
                    <th>Fecha Lectura</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notificaciones as $n): ?>
                    <tr class="<?= $n->leido ? '' : 'table-warning' ?>">
                        <td><?= Html::encode($n->tipo) ?></td>
                        <td><?= Html::encode($n->mensaje) ?></td>
                        <td><?= $n->vehiculo ? Html::encode($n->vehiculo->placa) : '-' ?></td>
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
});
</script> 