<?php

namespace app\controllers;

use app\models\PolizaSeguro;
use app\models\PolizaSeguroSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use Yii;

/**
 * PolizaSeguroController implements the CRUD actions for PolizaSeguro model.
 */
class PolizaSeguroController extends Controller
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
     * Lists all PolizaSeguro models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PolizaSeguroSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        // Create a new model for the modal form
        $model = new PolizaSeguro();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model, // Pass the model to the view
        ]);
    }

    /**
     * Displays a single PolizaSeguro model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            // Buscar imágenes de la póliza
            $baseUploadDir = Yii::getAlias('@webroot') . '/uploads/polizas/';
            $images = [];
            
            // Buscar en todas las carpetas que contengan la aseguradora y número de póliza
            if (file_exists($baseUploadDir) && is_dir($baseUploadDir)) {
                // Sanitizar los nombres para la búsqueda, similar a como se hace en savePolizaImages
                $aseguradora = preg_replace('/[^A-Za-z0-9_\-]/', '_', $model->aseguradora);
                $noPoliza = preg_replace('/[^A-Za-z0-9_\-]/', '_', $model->no_poliza);
                
                // Buscar carpetas que coincidan con el patrón
                $pattern = $baseUploadDir . $aseguradora . '_' . $noPoliza . '_*';
                $folders = glob($pattern);
                
                if (empty($folders)) {
                    // Si no encuentra con el patrón exacto, intentar una búsqueda más flexible
                    $folders = glob($baseUploadDir . '*' . $aseguradora . '*' . $noPoliza . '*');
                }
                
                foreach ($folders as $folder) {
                    if (is_dir($folder)) {
                        $files = glob($folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                        foreach ($files as $file) {
                            $relativePath = str_replace(Yii::getAlias('@webroot'), '', $file);
                            $images[] = Yii::getAlias('@web') . $relativePath;
                        }
                    }
                }
            }
            
            // Obtener el historial de estados de la póliza para respuestas AJAX
            $historial = [];
            foreach ($model->historial as $item) {
                $historial[] = [
                    'id' => $item->id,
                    'estado_anterior' => $item->estado_anterior,
                    'estado_anterior_nombre' => $item->estado_anterior ? \app\models\PolizaSeguro::getNombreEstado($item->estado_anterior) : '',
                    'estado_nuevo' => $item->estado_nuevo,
                    'estado_nuevo_nombre' => \app\models\PolizaSeguro::getNombreEstado($item->estado_nuevo),
                    'fecha_cambio' => $item->fecha_cambio,
                    'comentario' => $item->comentario,
                    'motivo' => $item->motivo,
                    'clase_estado' => \app\models\PolizaSeguro::getClaseEstado($item->estado_nuevo)
                ];
            }
            
            return [
                'success' => true,
                'data' => $model->attributes,
                'images' => $images,
                'historial' => $historial,
            ];
        }
        
        // Obtener el historial de estados de la póliza
        $historial = [];
        foreach ($model->historial as $item) {
            $historial[] = [
                'id' => $item->id,
                'estado_anterior' => $item->estado_anterior,
                'estado_anterior_nombre' => $item->estado_anterior ? \app\models\PolizaSeguro::getNombreEstado($item->estado_anterior) : '',
                'estado_nuevo' => $item->estado_nuevo,
                'estado_nuevo_nombre' => \app\models\PolizaSeguro::getNombreEstado($item->estado_nuevo),
                'fecha_cambio' => $item->fecha_cambio,
                'comentario' => $item->comentario,
                'motivo' => $item->motivo,
                'clase_estado' => \app\models\PolizaSeguro::getClaseEstado($item->estado_nuevo)
            ];
        }
        
        return $this->render('view', [
            'model' => $model,
            'historial' => $historial,
        ]);
    }

    /**
     * Creates a new PolizaSeguro model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PolizaSeguro();
    
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            try {
                if ($model->save()) {
                    // Handle image uploads after saving the poliza
                    $this->savePolizaImages($model);
                    
                    return [
                        'success' => true, 
                        'message' => 'Póliza de seguro creada exitosamente.',
                        'closeModal' => true
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => 'Error al guardar la póliza de seguro.', 
                        'errors' => $model->errors
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false, 
                    'message' => 'Ocurrió un error: ' . $e->getMessage()
                ];
            }
        }
    
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }
    
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PolizaSeguro model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Handle file uploads using the same method as create
            $this->savePolizaImages($model);

            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Póliza de seguro actualizada correctamente.',
                ];
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        }
    
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'data' => $model->attributes,
            ];
        }
    
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PolizaSeguro model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (Yii::$app->request->isAjax) {
            $this->findModel($id)->delete();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'La póliza de seguro ha sido eliminada exitosamente.'];
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PolizaSeguro model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PolizaSeguro the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PolizaSeguro::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    /**
     * Saves poliza images to the specified directory
     * @param PolizaSeguro $model The poliza model
     */
    protected function savePolizaImages($model)
    {
        // Define the base upload directory
        $baseUploadDir = Yii::getAlias('@webroot') . '/uploads/polizas/';
        
        // Create a folder name for this poliza (without timestamp)
        $folderName = $model->aseguradora . '_' . $model->no_poliza;
        $folderName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $folderName); // Sanitize folder name
        
        // Create the full directory path
        $uploadDir = $baseUploadDir . $folderName . '/';
        
        // Create directory if it doesn't exist
        if (!file_exists($baseUploadDir)) {
            mkdir($baseUploadDir, 0777, true);
        }
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Process uploaded files
        $uploadedFiles = \yii\web\UploadedFile::getInstancesByName('poliza_images');
        
        if (!empty($uploadedFiles)) {
            // Contador para limitar a 2 imágenes como máximo (consistente con la UI)
            $count = 0;
            
            // Obtener el estado actual de la póliza para incluirlo en el nombre del archivo
            $estadoNombre = PolizaSeguro::getNombreEstado($model->estado);
            $timestamp = date('Y-m-d_H-i-s');
            
            foreach ($uploadedFiles as $index => $file) {
                // Limitar a 2 imágenes como máximo
                if ($count >= 2) {
                    Yii::info("Skipping additional images beyond limit of 2 for poliza {$model->id}", 'app');
                    break;
                }
                
                // Generate a unique filename with estado and timestamp
                $fileName = 'poliza_' . $estadoNombre . '_' . $timestamp . '_' . ($index + 1) . '.' . $file->extension;
                $filePath = $uploadDir . $fileName;
                
                // Save the file
                if ($file->saveAs($filePath)) {
                    Yii::info("Saved poliza image {$index} for poliza {$model->id} to {$filePath}", 'app');
                    $count++;
                } else {
                    Yii::error("Failed to save poliza image {$index} for poliza {$model->id}", 'app');
                }
            }
        }
    }

    /**
     * Acción para cambiar el estado de una póliza
     * @param int $id ID de la póliza
     * @return array Respuesta JSON
     */
    public function actionCambiarEstado($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        $request = Yii::$app->request;
        
        $nuevoEstado = $request->post('estado');
        $comentario = $request->post('comentario', '');
        $motivo = $request->post('motivo', '');
        
        if (!$nuevoEstado) {
            return [
                'success' => false,
                'message' => 'El estado es requerido'
            ];
        }
        
        // Cambiar el estado de la póliza
        if ($model->cambiarEstado($nuevoEstado, $comentario, $motivo)) {
            // Procesar las imágenes si se han subido
            $this->savePolizaImages($model);
            
            // Obtener el historial actualizado
            $historial = [];
            foreach ($model->historial as $item) {
                $historial[] = [
                    'id' => $item->id,
                    'estado_anterior' => $item->estado_anterior,
                    'estado_anterior_nombre' => $item->estado_anterior ? \app\models\PolizaSeguro::getNombreEstado($item->estado_anterior) : '',
                    'estado_nuevo' => $item->estado_nuevo,
                    'estado_nuevo_nombre' => \app\models\PolizaSeguro::getNombreEstado($item->estado_nuevo),
                    'fecha_cambio' => $item->fecha_cambio,
                    'comentario' => $item->comentario,
                    'motivo' => $item->motivo,
                    'clase_estado' => \app\models\PolizaSeguro::getClaseEstado($item->estado_nuevo)
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Estado de la póliza actualizado correctamente',
                'estado_actual' => $model->estado,
                'historial' => $historial
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado de la póliza'
            ];
        }
    }
    
    /**
     * Acción para checar vencimientos de pólizas y crear notificaciones si corresponde
     */
    public function actionCheckVencimientos()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $hoy = new \DateTime(date('Y-m-d')); // Solo fecha, sin hora
        $notificaciones = [];

        $polizas = \app\models\PolizaSeguro::find()->all();
        foreach ($polizas as $poliza) {
            if (!$poliza->fecha_vencimiento) continue;
            $vencimiento = new \DateTime($poliza->fecha_vencimiento);
            $diff = $hoy->diff($vencimiento);
            $dias = (int)$diff->format('%r%a');
            \Yii::info("Poliza {$poliza->id} vence en {$poliza->fecha_vencimiento}, días: $dias", 'app');

            $vehiculo = \app\models\Vehiculos::findOne(['poliza_id' => $poliza->id]);

            $mensaje = "La póliza de seguro del vehículo " . ($vehiculo ? $vehiculo->placa : 'desconocido') . " vence en " . ($dias == 1 ? '1 día' : ($dias == 7 ? '1 semana' : '1 mes')) . " (" . $poliza->fecha_vencimiento . ")";
            $yaExiste = \app\models\Notificaciones::find()
                ->where([
                    'tipo' => 'poliza_vencimiento',
                    'mensaje' => $mensaje,
                ])->exists();

            if (!$yaExiste) {
                $n = new \app\models\Notificaciones();
                $n->tipo = 'poliza_vencimiento';
                $n->mensaje = $mensaje;
                $n->fecha_creacion = date('Y-m-d H:i:s');
                $n->leido = 0;
                $n->id_vehiculo = $vehiculo ? $vehiculo->id : null;
                if (!$n->save()) {
                    \Yii::error('No se pudo guardar la notificación: ' . json_encode($n->errors), 'app');
                }
            }

            $notificaciones[] = [
                'vehiculo' => $vehiculo ? $vehiculo->placa : '',
                'dias' => $dias,
                'fecha_vencimiento' => $poliza->fecha_vencimiento,
                'mensaje' => $mensaje,
            ];
        }
        return ['success' => true, 'notificaciones' => $notificaciones];
    }
}