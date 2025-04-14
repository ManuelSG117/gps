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
            // Handle file uploads
            $uploadedFiles = \yii\web\UploadedFile::getInstancesByName('poliza_images');
            
            if (!empty($uploadedFiles)) {
                // Create upload directory if it doesn't exist
                $uploadDir = Yii::getAlias('@webroot/uploads/polizas/');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $today = date('Ymd');
                $aseguradora = preg_replace('/[^a-zA-Z0-9]/', '', $model->aseguradora);
                
                foreach ($uploadedFiles as $index => $file) {
                    if ($index < 2) { // Limit to 2 images
                        $fileName = $model->id . '_' . $today . '_' . $aseguradora . '_' . ($index + 1) . '.' . $file->extension;
                        $filePath = $uploadDir . $fileName;
                        $file->saveAs($filePath);
                    }
                }
            }
            
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
        
        // Create a unique folder name for this poliza
        $timestamp = date('Y-m-d_H-i-s');
        $folderName = $model->aseguradora . '_' . $model->no_poliza . '_' . $timestamp;
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
            foreach ($uploadedFiles as $index => $file) {
                // Generate a unique filename
                $fileName = 'poliza_' . ($index + 1) . '_' . time() . '.' . $file->extension;
                $filePath = $uploadDir . $fileName;
                
                // Save the file
                if ($file->saveAs($filePath)) {
                    Yii::info("Saved poliza image {$index} for poliza {$model->id} to {$filePath}", 'app');
                } else {
                    Yii::error("Failed to save poliza image {$index} for poliza {$model->id}", 'app');
                }
            }
        }
    }
}
