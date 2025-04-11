<?php

namespace app\controllers;

use app\models\Vehiculos;
use app\models\VehiculosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * VehiculosController implements the CRUD actions for Vehiculos model.
 */
class VehiculosController extends Controller
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
     * Lists all Vehiculos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new VehiculosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        // Crear un nuevo modelo para el formulario del modal
        $model = new Vehiculos();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model, // Pasa el modelo a la vista
        ]);
    }

    /**
     * Displays a single Vehiculos model.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (Yii::$app->request->isAjax) {
            $model = $this->findModel($id);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'data' => $model->attributes,
                'isViewMode' => true, // Add this flag to indicate view mode
            ];
        }
        
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Vehiculos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Vehiculos();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            try {
                if ($model->save()) {
                    // Handle image uploads after saving the vehicle
                    $this->saveVehicleImages($model);
                    
                    return [
                        'success' => true, 
                        'message' => 'Vehículo creado exitosamente.',
                        'closeModal' => true, // Add this flag
                        'html' => $this->renderAjax('_modal', ['model' => new Vehiculos()]) // Return fresh form
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => 'Error al guardar el vehículo.', 
                        'errors' => $model->errors,
                        'html' => $this->renderAjax('_modal', ['model' => $model]) // Return form with errors
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false, 
                    'message' => 'Ocurrió un error: ' . $e->getMessage(),
                    'html' => $this->renderAjax('_modal', ['model' => $model]) // Return form with model
                ];
            }
        }

        return $this->renderAjax('_modal', ['model' => $model]);
    }

    /**
     * Updates an existing Vehiculos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                
                try {
                    if ($model->save()) {
                        // Handle image uploads after updating the vehicle
                        $this->saveVehicleImages($model);
                        
                        return [
                            'success' => true, 
                            'message' => 'Vehículo actualizado exitosamente.',
                            'closeModal' => true, // Add this flag
                            // Don't return a fresh form, just return success
                        ];
                    } else {
                        return [
                            'success' => false, 
                            'message' => 'Error al actualizar el vehículo.', 
                            'errors' => $model->errors,
                            'html' => $this->renderAjax('_modal', ['model' => $model]) // Return form with errors
                        ];
                    }
                } catch (\Exception $e) {
                    return [
                        'success' => false, 
                        'message' => 'Ocurrió un error: ' . $e->getMessage(),
                        'html' => $this->renderAjax('_modal', ['model' => $model]) // Return form with model
                    ];
                }
            }
            
            if (Yii::$app->request->isGet) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'data' => $model->attributes,
                    'html' => $this->renderAjax('_modal', ['model' => $model]) // Return form with model
                ];
            }
            
            return $this->renderAjax('_modal', ['model' => $model]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Vehiculos model.
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
            return ['success' => true, 'message' => 'El vehículo ha sido eliminado exitosamente.'];
        }
        
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Vehiculos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vehiculos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vehiculos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    /**
     * Saves vehicle images to the specified directory structure
     * @param Vehiculos $model The vehicle model
     */
    protected function saveVehicleImages($model)
    {
        // Define the base upload directory
        $baseUploadDir = Yii::getAlias('@webroot') . '/uploads/Vehiculos/';
        
        // Create a unique folder name for this vehicle using marca, modelo and timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $folderName = $model->marca_auto . '_' . $model->modelo_auto . '_' . $timestamp;
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
        
        // Define image categories
        $imageCategories = [
            'frente', 'lateral_derecho', 'lateral_izquierdo', 'trasera', 
            'llantas', 'motor', 'kilometraje'
        ];
        
        // Process each image upload
        foreach ($imageCategories as $category) {
            $uploadedFile = \yii\web\UploadedFile::getInstanceByName("VehiculoImagenes[{$category}]");
            
            if ($uploadedFile) {
                // Generate a unique filename
                $fileName = $category . '_' . time() . '.' . $uploadedFile->extension;
                $filePath = $uploadDir . $fileName;
                
                // Save the file
                if ($uploadedFile->saveAs($filePath)) {
                    Yii::info("Saved {$category} image for vehicle {$model->id} to {$filePath}", 'app');
                } else {
                    Yii::error("Failed to save {$category} image for vehicle {$model->id}", 'app');
                }
            }
        }
    }
}
