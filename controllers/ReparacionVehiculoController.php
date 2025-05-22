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
    
                // Obtener el historial de estados
                $historial = $model->estadoHistorial;
                $historialData = [];
                
                foreach ($historial as $item) {
                    $historialData[] = [
                        'id' => $item->id,
                        'estado_anterior' => $item->estado_anterior,
                        'estado_anterior_nombre' => \app\models\EstadoReparacionHistorial::getNombreEstado($item->estado_anterior),
                        'estado_nuevo' => $item->estado_nuevo,
                        'estado_nuevo_nombre' => \app\models\EstadoReparacionHistorial::getNombreEstado($item->estado_nuevo),
                        'fecha_cambio' => $item->fecha_cambio,
                        'comentario' => $item->comentario,
                        'clase_estado' => \app\models\EstadoReparacionHistorial::getClaseEstado($item->estado_nuevo),
                        'motivo_pausa' => property_exists($item, 'motivo_pausa') ? $item->motivo_pausa : (isset($item->motivo_pausa) ? $item->motivo_pausa : null),
                        'requisitos_reanudar' => property_exists($item, 'requisitos_reanudar') ? $item->requisitos_reanudar : (isset($item->requisitos_reanudar) ? $item->requisitos_reanudar : null),
                    ];
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
                    'historial' => $historialData,
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
                
                // Establecer estado_servicio en 2 (En Proceso) por defecto
                if (!isset($model->estado_servicio)) {
                    $model->estado_servicio = 2;
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
     * Cambia el estado de una reparación y registra el cambio en el historial
     * @param int $id ID de la reparación
     * @return mixed
     */
    public function actionCambiarEstado()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $request = \Yii::$app->request->post();
            
            if (!isset($request['id']) || !isset($request['estado'])) {
                return [
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos.'
                ];
            }
            
            $id = $request['id'];
            $nuevoEstado = $request['estado'];
            $comentario = isset($request['comentario']) ? $request['comentario'] : '';
            
            $model = $this->findModel($id);
            
            // Verificar si el estado es 4 (Completado), no permitir cambios
            if ($model->estado_servicio == 4) {
                return [
                    'success' => false,
                    'message' => 'No se puede cambiar el estado de una reparación completada.'
                ];
            }
            
            // Guardar el estado anterior para usarlo en el nombre de las imágenes
            $estadoAnterior = $model->estado_servicio;
            $estadoAnteriorNombre = \app\models\EstadoReparacionHistorial::getNombreEstado($estadoAnterior);
            
            if ($model->cambiarEstado($nuevoEstado, $comentario)) {
                // Guardar imágenes asociadas al cambio de estado si existen
                $imagenes = [];
                $uploadedFiles = \yii\web\UploadedFile::getInstancesByName('imagenes');
                
                if (!empty($uploadedFiles)) {
                    $estadoNuevoNombre = \app\models\EstadoReparacionHistorial::getNombreEstado($nuevoEstado);
                    $imagenes = $this->saveStateChangeImages($model, $uploadedFiles, $estadoAnteriorNombre, $estadoNuevoNombre);
                }
                
                // Obtener el historial actualizado
                $historial = $model->estadoHistorial;
                $historialData = [];
                
                foreach ($historial as $item) {
                    $historialData[] = [
                        'id' => $item->id,
                        'estado_anterior' => $item->estado_anterior,
                        'estado_anterior_nombre' => \app\models\EstadoReparacionHistorial::getNombreEstado($item->estado_anterior),
                        'estado_nuevo' => $item->estado_nuevo,
                        'estado_nuevo_nombre' => \app\models\EstadoReparacionHistorial::getNombreEstado($item->estado_nuevo),
                        'fecha_cambio' => $item->fecha_cambio,
                        'comentario' => $item->comentario,
                        'clase_estado' => \app\models\EstadoReparacionHistorial::getClaseEstado($item->estado_nuevo),
                        'motivo_pausa' => property_exists($item, 'motivo_pausa') ? $item->motivo_pausa : (isset($item->motivo_pausa) ? $item->motivo_pausa : null),
                        'requisitos_reanudar' => property_exists($item, 'requisitos_reanudar') ? $item->requisitos_reanudar : (isset($item->requisitos_reanudar) ? $item->requisitos_reanudar : null),
                    ];
                }
                
                // Obtener todas las imágenes existentes para mostrarlas en la galería
                $vehiculo = $model->vehiculo;
                $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
                $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
                $placa = $vehiculo ? $vehiculo->placa : '';
                $fecha = $model->fecha ?? date('Y-m-d');
                $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");
                
                $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
                $webPath = '/uploads/reparacion_vehiculo/' . $carpeta . '/';
                
                $allImages = [];
                if (file_exists($uploadDir)) {
                    $files = glob($uploadDir . '*.*');
                    
                    // Registrar en el log para depuración
                    \Yii::info("Total de archivos encontrados en {$uploadDir}: " . count($files), 'app');
                    
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            $fileName = basename($file);
                            $allImages[] = [
                                'url' => $webPath . $fileName,
                            ];
                            
                            // Registrar en el log para depuración
                            \Yii::info("Imagen agregada a la respuesta: {$fileName}", 'app');
                        }
                    }
                }
                
                // Registrar en el log para depuración
                \Yii::info("Total de imágenes enviadas en la respuesta: " . count($allImages), 'app');
                
                return [
                    'success' => true,
                    'message' => 'Estado actualizado correctamente.',
                    'estado_actual' => $model->estado_servicio,
                    'estado_nombre' => \app\models\EstadoReparacionHistorial::getNombreEstado($model->estado_servicio),
                    'historial' => $historialData,
                    'imagenes' => $allImages
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo actualizar el estado.'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
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

/**
 * Guarda imágenes asociadas a un cambio de estado
 * @param ReparacionVehiculo $model
 * @param array $uploadedFiles Archivos subidos
 * @param string $estadoAnterior Nombre del estado anterior
 * @param string $estadoNuevo Nombre del estado nuevo
 * @return array saved image paths
 */
protected function saveStateChangeImages($model, $uploadedFiles, $estadoAnterior, $estadoNuevo)
{
    $savedImages = [];
    if (empty($uploadedFiles)) {
        \Yii::error("No hay archivos para guardar en el cambio de estado. UploadedFiles está vacío.", 'app');
        return $savedImages;
    }

    \Yii::info("Iniciando guardado de " . count($uploadedFiles) . " imágenes para cambio de estado", 'app');
    
    // Verificar que los archivos sean instancias de UploadedFile
    foreach ($uploadedFiles as $index => $file) {
        if (!($file instanceof \yii\web\UploadedFile)) {
            \Yii::error("El archivo {$index} no es una instancia válida de UploadedFile", 'app');
        } else {
            \Yii::info("Archivo {$index}: {$file->name}, tipo: {$file->type}, tamaño: {$file->size} bytes", 'app');
        }
    }

    // Obtener datos del vehículo
    $vehiculo = $model->vehiculo;
    $marca = $vehiculo ? $vehiculo->marca_auto : 'vehiculo';
    $modelo = $vehiculo ? $vehiculo->modelo_auto : '';
    $placa = $vehiculo ? $vehiculo->placa : '';
    $fecha = $model->fecha ?? date('Y-m-d');

    // Limpiar datos para el nombre de la carpeta
    $carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$marca}_{$modelo}_{$placa}_{$fecha}_{$model->id}");
    \Yii::info("Carpeta de destino: {$carpeta}", 'app');

    // Carpeta final: reparacion_vehiculo/MARCA_MODELO_PLACA_FECHA_ID/
    $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $carpeta . '/';
    \Yii::info("Directorio completo: {$uploadDir}", 'app');
    
    if (!file_exists($uploadDir)) {
        $dirCreated = mkdir($uploadDir, 0777, true);
        if ($dirCreated) {
            \Yii::info("Carpeta creada exitosamente: {$uploadDir}", 'app');
        } else {
            \Yii::error("Error al crear la carpeta: {$uploadDir}", 'app');
            return $savedImages;
        }
    } else {
        \Yii::info("La carpeta ya existe: {$uploadDir}", 'app');
    }

    // Verificar permisos de escritura
    if (!is_writable($uploadDir)) {
        \Yii::error("La carpeta no tiene permisos de escritura: {$uploadDir}", 'app');
        chmod($uploadDir, 0777);
        if (!is_writable($uploadDir)) {
            \Yii::error("No se pudieron establecer permisos de escritura en la carpeta", 'app');
            return $savedImages;
        }
    }

    // Guardar cada imagen con prefijo del cambio de estado
    $timestamp = date('Ymd_His');
    
    // Normalizar los nombres de estado para evitar problemas con caracteres especiales
    $estadoAnteriorNormalizado = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($estadoAnterior));
    $estadoNuevoNormalizado = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($estadoNuevo));
    
    $cambioEstado = "cambio_{$estadoAnteriorNormalizado}_a_{$estadoNuevoNormalizado}_{$timestamp}";
    
    // Registrar en el log para depuración
    \Yii::info("Guardando imágenes con prefijo: {$cambioEstado}", 'app');
    
    foreach ($uploadedFiles as $index => $file) {
        try {
            if ($file->type && strpos($file->type, 'image/') === 0) {
                $fileName = $cambioEstado . '_' . uniqid() . '.' . $file->extension;
                $filePath = $uploadDir . $fileName;
                
                \Yii::info("Intentando guardar imagen {$index}: {$fileName}", 'app');
                
                // Verificar si el archivo ya existe y eliminarlo si es necesario
                if (file_exists($filePath)) {
                    unlink($filePath);
                    \Yii::info("Archivo existente eliminado: {$filePath}", 'app');
                }
                
                $saved = $file->saveAs($filePath);
                
                if ($saved) {
                    \Yii::info("Imagen guardada exitosamente: {$fileName}", 'app');
                    
                    // Verificar que el archivo realmente se haya creado
                    if (file_exists($filePath)) {
                        \Yii::info("Archivo verificado en disco: {$filePath}, tamaño: " . filesize($filePath) . " bytes", 'app');
                        
                        $webPath = '/uploads/reparacion_vehiculo/' . $carpeta . '/' . $fileName;
                        $savedImages[] = [
                            'fileName' => $fileName,
                            'filePath' => str_replace(\Yii::getAlias('@webroot'), '', $filePath),
                            'url' => $webPath,
                        ];
                        
                        \Yii::info("URL de imagen agregada: {$webPath}", 'app');
                    } else {
                        \Yii::error("El archivo no existe en disco después de guardarlo: {$filePath}", 'app');
                    }
                } else {
                    \Yii::error("Error al guardar la imagen {$index}: {$fileName}", 'app');
                }
            } else {
                \Yii::warning("Archivo {$index} no es una imagen válida. Tipo: " . ($file->type ?? 'desconocido'), 'app');
            }
        } catch (\Exception $e) {
            \Yii::error("Excepción al procesar la imagen {$index}: " . $e->getMessage(), 'app');
        }
    }
    
    \Yii::info("Total de imágenes guardadas para el cambio de estado: " . count($savedImages), 'app');
    return $savedImages;
}
}
