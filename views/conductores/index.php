<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\Conductores;

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
            'onclick' => 'clearForm()',
        ]) ?>
    </p>

    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombres',
            'hAlign' => 'center',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Conductores::find()->all(), 'nombres', 'nombres'),
            'filterWidgetOptions' => [
                'options' => ['placeholder' => 'Seleccionar nombre'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'Nombre'],
        ],
        [
            'attribute' => 'apellido_p',
            'hAlign' => 'center',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Conductores::find()->all(), 'apellido_p', 'apellido_p'),
            'filterWidgetOptions' => [
                'options' => ['placeholder' => 'Seleccionar apellido paterno'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'Apellido Paterno'],
        ],
        [
            'attribute' => 'apellido_m',
            'hAlign' => 'center',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Conductores::find()->all(), 'apellido_m', 'apellido_m'),
            'filterWidgetOptions' => [
                'options' => ['placeholder' => 'Seleccionar apellido materno'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'Apellido Materno'],
        ],
        [
            'attribute' => 'no_licencia',
            'hAlign' => 'center',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Conductores::find()->all(), 'no_licencia', 'no_licencia'),
            'filterWidgetOptions' => [
                'options' => ['placeholder' => 'Seleccionar número de licencia'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'Número de Licencia'],
        ],
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
function clearForm() {
    $('#create-conductores-form').trigger('reset');
    $('#create-conductores-form').attr('action', '<?= Url::to(['conductores/create']) ?>');
}


$('#create-conductores-form').on('beforeSubmit', function (e) {
    e.preventDefault();

    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        success: function (data) {
            if (data.success) {
                $('#exampleModalCenter').modal('hide');
            } else {
                form.yiiActiveForm('updateMessages', data.errors, true);
            }
        },
        error: function () {
            alert('Hubo un error al guardar el conductor.');
        }
    });
    return false; 
});

function loadUpdateForm(id) {
    $.ajax({
        url: '<?= Url::to(['conductores/get-conductor']) ?>',
        type: 'GET',
        data: { id: id },
        success: function (data) {
            var form = $('#create-conductores-form');
            form.trigger('reset'); // Limpiar el formulario antes de cargar los datos
            form.find('input[name="Conductores[nombres]"]').val(data.nombres);
            form.find('input[name="Conductores[apellido_p]"]').val(data.apellido_p);
            form.find('input[name="Conductores[apellido_m]"]').val(data.apellido_m);
            form.find('input[name="Conductores[fecha_nacimiento]"]').val(data.fecha_nacimiento);
            form.find('input[name="Conductores[no_licencia]"]').val(data.no_licencia);
            form.find('input[name="Conductores[cp]"]').val(data.cp);
            form.find('select[name="Conductores[estado]"]').val(data.estado);
            form.find('select[name="Conductores[municipio]"]').val(data.municipio);
            form.find('input[name="Conductores[colonia]"]').val(data.colonia);
            form.find('input[name="Conductores[calle]"]').val(data.calle);
            form.find('input[name="Conductores[num_ext]"]').val(data.num_ext);
            form.find('input[name="Conductores[num_int]"]').val(data.num_int);
            form.find('input[name="Conductores[telefono]"]').val(data.telefono);
            form.find('input[name="Conductores[email]"]').val(data.email);
            form.find('select[name="Conductores[tipo_sangre]"]').val(data.tipo_sangre);
            form.find('input[name="Conductores[nombres_contacto]"]').val(data.nombres_contacto);
            form.find('input[name="Conductores[apellido_p_contacto]"]').val(data.apellido_p_contacto);
            form.find('input[name="Conductores[apellido_m_contacto]"]').val(data.apellido_m_contacto);
            form.find('select[name="Conductores[parentesco]"]').val(data.parentesco);
            form.find('input[name="Conductores[telefono_contacto]"]').val(data.telefono_contacto);
            form.attr('action', '<?= Url::to(['conductores/update']) ?>' + '?id=' + id);
        },
        error: function () {
            alert('Hubo un error al cargar los datos del conductor.');
        }
    });
}
</script>