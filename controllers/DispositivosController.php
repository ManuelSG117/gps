<?php

namespace app\controllers;

use app\models\Dispositivos;
use app\models\DispositivosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\httpclient\Client;
use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * DispositivosController implements the CRUD actions for Dispositivos model.
 */
class DispositivosController extends Controller
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
     * Lists all Dispositivos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DispositivosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $model = new Dispositivos();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Displays a single Dispositivos model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
     
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'data' => $model->attributes,
            ];
        }
     
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Dispositivos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionUploadVehicle()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $files = \yii\web\UploadedFile::getInstancesByName('vehicle_images');
        $uploadPath = \Yii::getAlias('@webroot/uploads/vehiculo_detalles/');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        $out = [];
        if ($files) {
            foreach ($files as $file) {
                $fileName = uniqid() . '.' . $file->extension;
                if ($file->saveAs($uploadPath . $fileName)) {
                    $out[] = ['success' => true, 'fileName' => $fileName];
                } else {
                    $out[] = ['success' => false, 'error' => "Error al guardar: {$file->name}"];
                }
            }
        }
        return $out;
    }
    
    public function actionUploadPolicy()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $files = \yii\web\UploadedFile::getInstancesByName('policy_images');
        $uploadPath = \Yii::getAlias('@webroot/uploads/poliza_seguro/');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        $out = [];
        if ($files) {
            foreach ($files as $file) {
                $fileName = uniqid() . '.' . $file->extension;
                if ($file->saveAs($uploadPath . $fileName)) {
                    $out[] = ['success' => true, 'fileName' => $fileName];
                } else {
                    $out[] = ['success' => false, 'error' => "Error al guardar: {$file->name}"];
                }
            }
        }
        return $out;
    }
        
    public function actionCreate()
    {
        $model = new Dispositivos();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            try {
                if ($model->save()) {
                    // Handle vehicle images
                    $vehicleImages = UploadedFile::getInstancesByName('vehicle_images');
                    if (!empty($vehicleImages)) {
                        $this->saveVehicleImages($model, $vehicleImages);
                    }
                    
                    // Handle policy images
                    $policyImages = UploadedFile::getInstancesByName('policy_images');
                    if (!empty($policyImages)) {
                        $this->savePolicyImages($model, $policyImages);
                    }
                    
                    return [
                        'success' => true,
                        'message' => 'Dispositivo creado exitosamente.',
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al guardar el dispositivo.',
                        'errors' => $model->errors
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Saves vehicle images to the specified directory
     * @param Dispositivos $model The dispositivo model
     * @param array $images The uploaded images
     */
    protected function saveVehicleImages($model, $images)
    {
        $baseUploadDir = Yii::getAlias('@webroot') . '/uploads/vehiculos/';
        $folderName = 'dispositivo_' . $model->id . '_' . date('Y-m-d_H-i-s');
        $uploadDir = $baseUploadDir . $folderName . '/';
        
        if (!file_exists($baseUploadDir)) {
            mkdir($baseUploadDir, 0777, true);
        }
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($images as $index => $file) {
            $fileName = 'vehiculo_' . ($index + 1) . '_' . time() . '.' . $file->extension;
            $filePath = $uploadDir . $fileName;
            
            if ($file->saveAs($filePath)) {
                Yii::info("Saved vehicle image {$index} for dispositivo {$model->id} to {$filePath}", 'app');
            } else {
                Yii::error("Failed to save vehicle image {$index} for dispositivo {$model->id}", 'app');
            }
        }
    }

    /**
     * Saves policy images to the specified directory
     * @param Dispositivos $model The dispositivo model
     * @param array $images The uploaded images
     */
    protected function savePolicyImages($model, $images)
    {
        $baseUploadDir = Yii::getAlias('@webroot') . '/uploads/polizas/';
        $folderName = 'dispositivo_' . $model->id . '_' . date('Y-m-d_H-i-s');
        $uploadDir = $baseUploadDir . $folderName . '/';
        
        if (!file_exists($baseUploadDir)) {
            mkdir($baseUploadDir, 0777, true);
        }
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($images as $index => $file) {
            $fileName = 'poliza_' . ($index + 1) . '_' . time() . '.' . $file->extension;
            $filePath = $uploadDir . $fileName;
            
            if ($file->saveAs($filePath)) {
                Yii::info("Saved policy image {$index} for dispositivo {$model->id} to {$filePath}", 'app');
            } else {
                Yii::error("Failed to save policy image {$index} for dispositivo {$model->id}", 'app');
            }
        }
    }

    public function actionModal()
    {
        $model = new Dispositivos();
        return $this->renderAjax('_modal', ['model' => $model]);
    }

    /**
     * Updates an existing Dispositivos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'message' => 'Dispositivo actualizado correctamente.',
            ];
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
     * Deletes an existing Dispositivos model.
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
             return ['success' => true, 'message' => 'El dispositivo ha sido eliminado exitosamente.'];
         }
          
     
         $this->findModel($id)->delete();
         return $this->redirect(['index']);
     }

    /**
     * Finds the Dispositivos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Dispositivos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Dispositivos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

