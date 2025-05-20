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
                $vehiculoInfo = $vehiculo ? str_replace([' ', '/', '\\'], '_', $vehiculo->marca_auto . '_' . $vehiculo->modelo_auto . '_' . $vehiculo->placa) : 'vehiculo_' . $vehiculo->id;
                $dateFolder = date('Y-m-d', strtotime($model->fecha));
                $folderName = $vehiculoInfo . '-' . $dateFolder;
                $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $folderName . '/';
                $webPath = '/uploads/reparacion_vehiculo/' . $folderName . '/';

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
                        // ...otros campos...
                    ],
                    'imagenes' => $images,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
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
                    return [
                        'success' => true,
                        'message' => 'Reparación actualizada exitosamente.',
                        'closeModal' => true,
                        'model' => $model->attributes
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

        // Get vehicle info for folder name
        $vehiculo = $model->vehiculo;
        $vehiculoInfo = $vehiculo ? str_replace([' ', '/', '\\'], '_', $vehiculo->marca_auto . '_' . $vehiculo->modelo_auto . '_' . $vehiculo->placa) : 'vehiculo_' . $vehiculo->id;
        $dateFolder = date('Y-m-d', strtotime($model->fecha));

        // Create single folder: reparacion_vehiculo/VEHICULO_FECHA/
        $folderName = $vehiculoInfo . '-' . $dateFolder;
        $uploadDir = \Yii::getAlias('@webroot/uploads/reparacion_vehiculo/') . $folderName . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Save each image
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
