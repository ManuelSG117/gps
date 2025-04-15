<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2; 
use app\models\Conductores;
use app\models\ConductoresSearch;

/** @var yii\web\View $this */
/** @var app\models\ConductoresSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conductores';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/conductores.js',['depends' => [\yii\web\JqueryAsset::class]]
);


?>   
 <link href="/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	<link href="/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">

<div class="conductores-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
               <?= Html::button('Crear Conductor', [
            'class' => 'btn btn-sm btn-success btn-index ',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#exampleModalCenter',
        ]) ?>
    </p>
        

    <?php Pjax::begin(['id' => 'conductores-grid', 'timeout' => 10000]); ?>
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'responsive' => true,  // Hacer la tabla responsive
    'hover' => true,  // Efecto hover sobre las filas
    
    'rowOptions' => function ($model, $index, $widget, $grid) {
        return ['data-id' => $model->id];
    },

    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombre',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'nombre'], 
        ], 
        [
            'attribute' => 'apellido_p',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'apellido_p'], 
        ],
        [
            'attribute' => 'apellido_m',
            'hAlign' => 'center',
            'contentOptions' => ['class' => 'apellido_m'], 
        ],
       
        [
            'class' => ActionColumn::className(),
            'template' => '{view} {update} {delete}',
            'contentOptions' => ['class' => 'action-column'], // Añadir esta línea
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a(
                        '<i class="fa fa-pencil-alt"></i></span>',
                        '#',
                        [
                            'title' => 'Actualizar',
                            'class' => 'btn btn-primary light btn-sharp ajax-update',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['conductores/update', 'id' => $model->id]),
                        ]
                    );
                },

                'view' => function ($url, $model, $key) {
                    return Html::a(
                        '<i class="fa fa-eye"></i></span>',
                        '#',
                        [
                            'title' => 'Ver',
                            'class' => 'btn btn-info light btn-sharp ajax-view',
                            'data-id' => $model->id,
                            'data-url' => Url::to(['conductores/view', 'id' => $model->id]),
                        ]
                    );
                },

              'delete' => function ($url, $model, $key) {
             return Html::a(
        '<i class="fa fa-trash"></i></span>',
        '#',
        [
            'title' => 'Eliminar',
            'class' => 'btn btn-danger light btn-sharp ajax-delete',
            'data-id' => $model->id,
            'data-url' => $url,
        ]
    );
},
            ],
        ],
    ],
]); ?>
    <?php Pjax::end(); ?>

</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Crear Conductor</h5>
                <script src="https://animatedicons.co/scripts/embed-animated-icons.js"></script>
                <animated-icons
                  src="https://animatedicons.co/get-icon?name=Register&style=minimalistic&token=be93a354-eb41-497f-bb52-cdf419e7d920"
                  trigger="loop"
                  attributes='{"variationThumbColour":"#536DFE","variationName":"Two Tone","variationNumber":2,"numberOfGroups":2,"backgroundIsGroup":false,"strokeWidth":1,"defaultColours":{"group-1":"#000000","group-2":"#536DFE","background":"#FFFFFF"}}'
                  height="35"
                  width="35"
                ></animated-icons>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= $this->render('_modal', ['model' => $model, 'action' => 'create']) ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>





 <script>
    
    function cargarMunicipios(cvegeoEstado, nombreEstado, municipioInicial) {
        $.ajax({
            url: 'https://gaia.inegi.org.mx/wscatgeo/v2/mgem/' + cvegeoEstado, // Usamos el cvegeo del estado
            method: 'GET',
            success: function (data) {
                var $municipioDropdown = $('#municipio-dropdown');
                $municipioDropdown.empty();
                $municipioDropdown.append($('<option>', {
                    value: '',
                    text: 'Selecciona el municipio...'
                }));

                var municipios = {};

                // Llenar el dropdown de municipios
                $.each(data.datos, function (index, municipio) {
                    municipios[municipio.cvegeo] = municipio.nomgeo;

                    $municipioDropdown.append($('<option>', {
                        value: municipio.nomgeo, // Guardamos el nombre del municipio como value
                        'data-cvegeo': municipio.cvegeo, // Guardamos el cvegeo como un atributo data-cvegeo
                        text: municipio.nomgeo
                    }));

                    // Comparar el municipio inicial con los municipios de la API
                    if (municipio.nomgeo === municipioInicial) {
                        $municipioDropdown.val(municipio.nomgeo); // Seleccionar el municipio inicial
                    }
                });
            },
            error: function () {
                alert('Hubo un error al cargar los municipios.');
            }
        });
    }

</script>


<style>
    
</style>

<script src="/vendor/sweetalert2/sweetalert2.min.js"></script>


