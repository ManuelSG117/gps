<?php

namespace app\controllers;

use app\models\ReparacionVehiculo;
use app\models\ReparacionVehiculoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ReparacionVehiculoController implements the CRUD actions for ReparacionVehiculo model.
 */
class ReparacionVehiculoController extends Controller
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
     * Lists all ReparacionVehiculo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ReparacionVehiculoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ReparacionVehiculo model.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    
        
    public function actionView($id)
    {
        $model = $this->findModel($id);
    
        if ($this->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
            try {
                $vehiculo = $model->vehiculo;
                $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
                $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
                $placa = $vehiculo ? $vehiculo->placa : '';
                $fecha = $model->fecha ?? date('Y-m-d');
                $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");
    
                $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
                $webPath = '/uploads/reparacion_vehiculo/' . $carpeta . '/';
    
                $images = [];
                if (file_exists($uploadDir)) {
                    $files = glob($uploadDir . '*.*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $fileName = basename($file);
                            $images[] = [
                                'url' => $webPath . $fileName,
                            ];
                        }
                    }
                }
    
                return [
                    'success' => true,
                    'data' => [
                        'id' => $model->id,
                        'vehiculo_id' => $model->vehiculo_id,
                        'fecha' => $model->fecha,
                        'tipo_servicio' => $model->tipo_servicio,
                        'descripcion' => $model->descripcion,
                        'costo' => $model->costo,
                        'tecnico' => $model->tecnico,
                        'notas' => $model->notas,
                        'estado_servicio' => $model->estado_servicio,
                        'motivo_pausa' => $model->motivo_pausa,
                        'requisitos_reanudar' => $model->requisitos_reanudar,
                        'fecha_finalizacion' => $model->fecha_finalizacion, 
                        ],
                    'imagenes' => $images,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'images' => $images,
                ];
            }
        }
    
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new ReparacionVehiculo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ReparacionVehiculo();

        if ($this->request->isAjax && $model->load($this->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            try {
                // Set default values
                $model->estatus = 1;
                if (!isset($model->fecha)) {
                    $model->fecha = date('Y-m-d');
                }
                
                if ($model->save()) {
                    // Save uploaded images
                    $savedImages = $this->saveRepairImages($model);
                    
                    return [
                        'success' => true,
                        'message' => 'Reparación creada exitosamente.',
                        'closeModal' => true,
                        'model' => $model->attributes,
                        'images' => $savedImages
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al guardar la reparación.',
                        'errors' => $model->errors,
                        'model' => $model->attributes
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'errors' => $model->errors
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->fecha = date('Y-m-d');
            $model->estatus = 1;
        }

        if ($this->request->isAjax) {
            return $this->renderAjax('_modal', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ReparacionVehiculo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($this->request->isAjax && $model->load($this->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            try {
                if ($model->save()) {
                    // Guardar imágenes nuevas si se suben
                    $savedImages = $this->saveRepairImages($model);
    
                    // Obtener imágenes existentes (igual que en actionView)
                    $vehiculo = $model->vehiculo;
                    $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
                    $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
                    $placa = $vehiculo ? $vehiculo->placa : '';
                    $fecha = $model->fecha ?? date('Y-m-d');
                    $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");
                    $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
                    $webPath = '/uploads/reparacion_vehiculo/' . $carpeta . '/';
    
                    $images = [];
                    if (file_exists($uploadDir)) {
                        $files = glob($uploadDir . '*.*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                $fileName = basename($file);
                                $images[] = [
                                    'url' => $webPath . $fileName,
                                ];
                            }
                        }
                    }
    
                    return [
                        'success' => true,
                        'message' => 'Reparación actualizada exitosamente.',
                        'closeModal' => true,
                        'model' => $model->attributes,
                        'imagenes' => $images
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al actualizar la reparación.',
                        'errors' => $model->errors,
                        'model' => $model->attributes
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'errors' => $model->errors
                ];
            }
        }
    
        if ($this->request->isAjax) {
            if ($this->request->isGet) {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                try {
                    $vehiculo = $model->vehiculo;
                    $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
                    $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
                    $placa = $vehiculo ? $vehiculo->placa : '';
                    $fecha = $model->fecha ?? date('Y-m-d');
                    $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");
    
                    $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
                    $webPath = '/uploads/reparacion_vehiculo/' . $carpeta . '/';
    
                    $images = [];
                    if (file_exists($uploadDir)) {
                        $files = glob($uploadDir . '*.*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                $fileName = basename($file);
                                $images[] = [
                                    'url' => $webPath . $fileName,
                                ];
                            }
                        }
                    }
    
                    return [
                        'success' => true,
                        'data' => [
                            'id' => $model->id,
                            'vehiculo_id' => $model->vehiculo_id,
                            'fecha' => $model->fecha,
                            'tipo_servicio' => $model->tipo_servicio,
                            'descripcion' => $model->descripcion,
                            'costo' => $model->costo,
                            'tecnico' => $model->tecnico,
                            'notas' => $model->notas,
                            'estado_servicio' => $model->estado_servicio,
                            'motivo_pausa' => $model->motivo_pausa,
                            'requisitos_reanudar' => $model->requisitos_reanudar,
                            'fecha_finalizacion' => $model->fecha_finalizacion,
                        ],
                        'imagenes' => $images
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }

            return $this->renderAjax('_modal', [
                'model' => $model,
            ]);
        }
    
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ReparacionVehiculo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if ($this->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            try {
                $this->findModel($id)->delete();
                return [
                    'success' => true,
                    'message' => 'La reparación ha sido eliminada exitosamente.'
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar la reparación: ' . $e->getMessage()
                ];
            }
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the ReparacionVehiculo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ReparacionVehiculo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ReparacionVehiculo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    /**
     * Saves uploaded images for a repair record
     * @param ReparacionVehiculo $model
     * @return array saved image paths
     */

   protected function saveRepairImages($model)
{
    $savedImages = [];
    $uploadedFiles = \yii\web\UploadedFile::getInstancesByName('imagenes');
    if (empty($uploadedFiles)) {
        return $savedImages;
    }

    // Obtener datos del vehículo
    $vehiculo = $model->vehiculo;
    $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
    $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
    $placa = $vehiculo ? $vehiculo->placa : '';
    $fecha = $model->fecha ?? date('Y-m-d');

    // Limpiar datos para el nombre de la carpeta
    $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");

    // Carpeta final: reparacion_vehiculo/MARCA_MODELO_PLACA_FECHA_ID/
    $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Guardar cada imagen
    foreach ($uploadedFiles as $file) {
        if ($file->type && strpos($file->type, 'image/') === 0) {
            $fileName = time() . '_' . uniqid() . '.' . $file->extension;
            $filePath = $uploadDir . $fileName;
            if ($file->saveAs($filePath)) {
                $savedImages[] = [
                    'fileName' => $fileName,
                    'filePath' => str_replace(\Yii::getAlias('@webroot'), '', $filePath),
                ];
            }
        }
    }
    return $savedImages;
}
}
