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
use app\models\Notificaciones;
use app\models\Gpslocations;

/**
 * VehiculoGeocercaController implementa las acciones CRUD para el modelo VehiculoGeocerca.
 */

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
            $asignacionesCreadas = 0;
            
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
                    } else {
                        $asignacionesCreadas++;
                    }
                }
            }
            
            return [
                'success' => $exito,
                'message' => $exito ? 'Vehículos asignados correctamente (' . $asignacionesCreadas . ' nuevas asignaciones)' : $mensaje,
                'asignaciones_creadas' => $asignacionesCreadas
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
            $asignacionesCreadas = 0;
            
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
                    } else {
                        $asignacionesCreadas++;
                    }
                }
            }
            
            return [
                'success' => $exito,
                'message' => $exito ? 'Geocercas asignadas correctamente (' . $asignacionesCreadas . ' nuevas asignaciones)' : $mensaje,
                'asignaciones_creadas' => $asignacionesCreadas
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
     * Asigna o desasigna rápidamente un vehículo a una geocerca con un solo clic.
     * @return \yii\web\Response
     */
    public function actionToggleAsignacion()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            
            if (empty($data['vehiculo_id']) || empty($data['geocerca_id'])) {
                return [
                    'success' => false,
                    'message' => 'Se requieren IDs de vehículo y geocerca'
                ];
            }
            
            $vehiculoId = $data['vehiculo_id'];
            $geocercaId = $data['geocerca_id'];
            
            // Verificar si ya existe la asignación
            $asignacionExistente = VehiculoGeocerca::findOne([
                'vehiculo_id' => $vehiculoId,
                'geocerca_id' => $geocercaId,
                'activo' => 1
            ]);
            
            if ($asignacionExistente) {
                // Si existe, eliminarla
                $asignacionExistente->delete();
                return [
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Asignación eliminada correctamente'
                ];
            } else {
                // Si no existe, crearla
                $asignacion = new VehiculoGeocerca();
                $asignacion->vehiculo_id = $vehiculoId;
                $asignacion->geocerca_id = $geocercaId;
                $asignacion->created_at = date('Y-m-d H:i:s');
                $asignacion->activo = 1;
                
                if ($asignacion->save()) {
                    return [
                        'success' => true,
                        'action' => 'added',
                        'message' => 'Asignación creada correctamente'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al crear la asignación: ' . Json::encode($asignacion->getErrors())
                    ];
                }
            }
        }
        
        return [
            'success' => false,
            'message' => 'Método no válido'
        ];
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
     * Devuelve la última ubicación de todos los vehículos (si existe) desde gpslocations.
     * @return \yii\web\Response
     */
    public function actionGetVehiculosUbicacion()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $vehiculos = \app\models\Vehiculos::find()->with(['dispositivo', 'conductor'])->all();
        $result = [];
        foreach ($vehiculos as $vehiculo) {
            $imei = $vehiculo->dispositivo ? $vehiculo->dispositivo->imei : null;
            $ubicacion = null;
            if ($imei) {
                $ubicacion = \app\models\Gpslocations::find()
                    ->where(['phoneNumber' => $imei])
                    ->orderBy(['lastUpdate' => SORT_DESC])
                    ->one();
            }
            // --- Lógica de notificación de salida de geocerca ---
            if ($ubicacion) {
                // Obtener geocercas asignadas
                $asignaciones = \app\models\VehiculoGeocerca::find()->where(['vehiculo_id' => $vehiculo->id, 'activo' => 1])->all();
                foreach ($asignaciones as $asignacion) {
                    $geocerca = $asignacion->geocerca;
                    if ($geocerca && $geocerca->coordinates) {
                        $coords = array_map(function($pair) {
                            $latlng = explode(',', $pair);
                            return [floatval($latlng[0]), floatval($latlng[1])];
                        }, explode('|', $geocerca->coordinates));
                        $isInside = self::pointInPolygon([$ubicacion->latitude, $ubicacion->longitude], $coords);
                        $stateKey = 'vehiculo_' . $vehiculo->id . '_geocerca_' . $geocerca->id . '_inside';
                        $wasInside = \Yii::$app->cache->get($stateKey);
                        if ($isInside) {
                            // Si está dentro, actualiza el estado
                            \Yii::$app->cache->set($stateKey, true, 24*3600); // 1 día de cache
                        } else {
                            // Si está fuera y antes estaba dentro (o nunca se notificó), notifica
                            if ($wasInside || $wasInside === null) {
                                // Verificar si ya existe una notificación no leída igual
                                $existe = \app\models\Notificaciones::find()
                                    ->where([
                                        'tipo' => 'geocerca',
                                        'id_vehiculo' => $vehiculo->id,
                                        'leido' => 0,
                                    ])
                                    ->andWhere(['like', 'mensaje', $geocerca->name])
                                    ->one();
                                if (!$existe) {
                                    $not = new \app\models\Notificaciones();
                                    $not->tipo = 'geocerca';
                                    $not->mensaje = 'El vehículo ' . $vehiculo->placa . ' ha salido de la geocerca ' . $geocerca->name;
                                    $not->id_vehiculo = $vehiculo->id;
                                    $not->fecha_creacion = date('Y-m-d H:i:s');
                                    $not->leido = 0;
                                    $not->datos_adicionales = json_encode([
                                        'geocerca_id' => $geocerca->id,
                                        'vehiculo_id' => $vehiculo->id,
                                        'ubicacion' => [
                                            'lat' => $ubicacion->latitude,
                                            'lng' => $ubicacion->longitude,
                                            'fecha' => $ubicacion->lastUpdate
                                        ]
                                    ]);
                                    $not->save();
                                }
                                // Marcar como fuera
                                \Yii::$app->cache->set($stateKey, false, 24*3600);
                            }
                        }
                    }
                }
            }
            // --- Fin lógica notificación ---
            $result[] = [
                'id' => $vehiculo->id,
                'modelo' => $vehiculo->modelo_auto,
                'marca' => $vehiculo->marca_auto,
                'placa' => $vehiculo->placa,
                'imei' => $imei,
                'latitude' => $ubicacion ? $ubicacion->latitude : null,
                'longitude' => $ubicacion ? $ubicacion->longitude : null,
                'lastUpdate' => $ubicacion ? $ubicacion->lastUpdate : null,
                'speed' => $ubicacion ? $ubicacion->speed : null,
                'direction' => $ubicacion ? $ubicacion->direction : null,
            ];
        }
        return $result;
    }

    // Algoritmo punto en polígono (ray casting)
    public static function pointInPolygon($point, $polygon) {
        $x = $point[0];
        $y = $point[1];
        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0]; $yi = $polygon[$i][1];
            $xj = $polygon[$j][0]; $yj = $polygon[$j][1];
            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-10) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
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
    
   

    public function actionGetVehiculosCapasu()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->getVehiculosCapasuData();
    }
}