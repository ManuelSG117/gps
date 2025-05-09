<?php

namespace app\controllers;

use app\models\Geocerca;
use app\models\Vehiculos;
use app\models\VehiculoGeocerca;
use app\models\VehiculoGeocercaSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;

/**
 * VehiculoGeocercaController implementa las acciones CRUD para el modelo VehiculoGeocerca.
 */
class VehiculoGeocercaController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lista todas las asignaciones de Vehículos a Geocercas.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Obtener todos los vehículos y geocercas para mostrar en la vista
        $vehiculos = Vehiculos::find()->all();
        $geocercas = Geocerca::find()->all();
        
        // Obtener las asignaciones existentes
        $asignaciones = VehiculoGeocerca::find()->all();
        
        return $this->render('index', [
            'vehiculos' => $vehiculos,
            'geocercas' => $geocercas,
            'asignaciones' => $asignaciones,
        ]);
    }

    /**
     * Asigna vehículos a una geocerca.
     * @return \yii\web\Response
     */
    public function actionAsignarVehiculos()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            
            if (empty($data['geocerca_id']) || empty($data['vehiculo_ids'])) {
                return [
                    'success' => false,
                    'message' => 'Se requiere ID de geocerca y al menos un vehículo'
                ];
            }
            
            $geocercaId = $data['geocerca_id'];
            $vehiculoIds = $data['vehiculo_ids'];
            $exito = true;
            $mensaje = '';
            
            // Eliminar asignaciones existentes para esta geocerca si se especifica
            if (isset($data['eliminar_existentes']) && $data['eliminar_existentes']) {
                VehiculoGeocerca::deleteAll(['geocerca_id' => $geocercaId]);
            }
            
            // Crear nuevas asignaciones
            foreach ($vehiculoIds as $vehiculoId) {
                // Verificar si ya existe la asignación
                $asignacionExistente = VehiculoGeocerca::findOne([
                    'vehiculo_id' => $vehiculoId,
                    'geocerca_id' => $geocercaId
                ]);
                
                if (!$asignacionExistente) {
                    $asignacion = new VehiculoGeocerca();
                    $asignacion->vehiculo_id = $vehiculoId;
                    $asignacion->geocerca_id = $geocercaId;
                    $asignacion->created_at = date('Y-m-d H:i:s');
                    $asignacion->activo = 1;
                    
                    if (!$asignacion->save()) {
                        $exito = false;
                        $mensaje .= 'Error al asignar vehículo ID ' . $vehiculoId . ': ' . 
                                   Json::encode($asignacion->getErrors()) . '\n';
                    }
                }
            }
            
            return [
                'success' => $exito,
                'message' => $exito ? 'Vehículos asignados correctamente' : $mensaje
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Método no válido'
        ];
    }

    /**
     * Asigna geocercas a un vehículo.
     * @return \yii\web\Response
     */
    public function actionAsignarGeocercas()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            
            if (empty($data['vehiculo_id']) || empty($data['geocerca_ids'])) {
                return [
                    'success' => false,
                    'message' => 'Se requiere ID de vehículo y al menos una geocerca'
                ];
            }
            
            $vehiculoId = $data['vehiculo_id'];
            $geocercaIds = $data['geocerca_ids'];
            $exito = true;
            $mensaje = '';
            
            // Eliminar asignaciones existentes para este vehículo si se especifica
            if (isset($data['eliminar_existentes']) && $data['eliminar_existentes']) {
                VehiculoGeocerca::deleteAll(['vehiculo_id' => $vehiculoId]);
            }
            
            // Crear nuevas asignaciones
            foreach ($geocercaIds as $geocercaId) {
                // Verificar si ya existe la asignación
                $asignacionExistente = VehiculoGeocerca::findOne([
                    'vehiculo_id' => $vehiculoId,
                    'geocerca_id' => $geocercaId
                ]);
                
                if (!$asignacionExistente) {
                    $asignacion = new VehiculoGeocerca();
                    $asignacion->vehiculo_id = $vehiculoId;
                    $asignacion->geocerca_id = $geocercaId;
                    $asignacion->created_at = date('Y-m-d H:i:s');
                    $asignacion->activo = 1;
                    
                    if (!$asignacion->save()) {
                        $exito = false;
                        $mensaje .= 'Error al asignar geocerca ID ' . $geocercaId . ': ' . 
                                   Json::encode($asignacion->getErrors()) . '\n';
                    }
                }
            }
            
            return [
                'success' => $exito,
                'message' => $exito ? 'Geocercas asignadas correctamente' : $mensaje
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Método no válido'
        ];
    }

    /**
     * Elimina una asignación de vehículo a geocerca.
     * @param int $id ID
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $this->findModel($id)->delete();
            return [
                'success' => true,
                'message' => 'Asignación eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las geocercas asignadas a un vehículo.
     * @param int $vehiculoId ID del vehículo
     * @return \yii\web\Response
     */
    public function actionGetGeocercasVehiculo($vehiculoId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $asignaciones = VehiculoGeocerca::find()
            ->where(['vehiculo_id' => $vehiculoId, 'activo' => 1])
            ->all();
        
        $geocercaIds = [];
        foreach ($asignaciones as $asignacion) {
            $geocercaIds[] = $asignacion->geocerca_id;
        }
        
        return [
            'success' => true,
            'geocerca_ids' => $geocercaIds
        ];
    }

    /**
     * Obtiene los vehículos asignados a una geocerca.
     * @param int $geocercaId ID de la geocerca
     * @return \yii\web\Response
     */
    public function actionGetVehiculosGeocerca($geocercaId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $asignaciones = VehiculoGeocerca::find()
            ->where(['geocerca_id' => $geocercaId, 'activo' => 1])
            ->all();
        
        $vehiculoIds = [];
        foreach ($asignaciones as $asignacion) {
            $vehiculoIds[] = $asignacion->vehiculo_id;
        }
        
        return [
            'success' => true,
            'vehiculo_ids' => $vehiculoIds
        ];
    }

    /**
     * Encuentra el modelo VehiculoGeocerca basado en su clave primaria.
     * Si no se encuentra el modelo, se lanza una excepción 404 HTTP.
     * @param int $id ID
     * @return VehiculoGeocerca el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = VehiculoGeocerca::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}