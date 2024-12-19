<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ConductoresSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conductores';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="conductores-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button('Crear Conductor', [
            'class' => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#exampleModalCenter',
        ]) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'nombres',
            'apellido_p',
            'apellido_m',
            'no_licencia',
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::button('Actualizar <span class="btn-icon-end"><i class="fa fa-heart"></i></span>', [
                            'class' => 'btn btn-info ',
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#exampleModalCenter',
                            'data-id' => $model->id,
                            'onclick' => 'loadUpdateForm(' . $model->id . ')',
                        ]);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Crear Conductor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', ['model' => $model]) ?>
        </div>
    </div>
</div>
<script>
    
$('#create-conductores-form').on('beforeSubmit', function (e) {
    e.preventDefault();

    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        success: function (data) {
            if (data.success) {
                // Recargar el grid usando PJAX
                $.pjax.reload({container: '#grid-pjax-container'});

                // Cerrar el modal
                $('#exampleModalCenter').modal('hide');
            } else {
                // Mostrar errores si existen
                form.yiiActiveForm('updateMessages', data.errors, true);
            }
        },
        error: function () {
            alert('Hubo un error al guardar el conductor.');
        }
    });
    return false; // Prevenir el envío normal del formulario
});

</script>
<script>
function loadUpdateForm(id) {
    $.ajax({
        url: '<?= Url::to(['conductores/get-conductor']) ?>',
        type: 'GET',
        data: { id: id },
        success: function (data) {
            $('#exampleModalCenter').find('input[name="Conductores[nombres]"]').val(data.nombres);
            $('#exampleModalCenter').find('input[name="Conductores[apellido_p]"]').val(data.apellido_p);
            $('#exampleModalCenter').find('input[name="Conductores[apellido_m]"]').val(data.apellido_m);
            $('#exampleModalCenter').find('input[name="Conductores[no_licencia]"]').val(data.no_licencia);
            // Set the form action to the update URL
            $('#create-conductores-form').attr('action', '<?= Url::to(['conductores/update']) ?>' + '?id=' + id);
        },
        error: function () {
            alert('Hubo un error al cargar los datos del conductor.');
        }
    });
}
</script>
