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
    private function uploadImages($model)
    {
        // Definir rutas base
        $baseDir = Yii::getAlias('@webroot/uploads/' . $model->nombre);
        $vehicleDir = $baseDir . '/vehiculo';
        $policyDir = $baseDir . '/poliza';
    
        // Crear directorios de forma segura
        try {
            FileHelper::createDirectory($baseDir, 0755);
            FileHelper::createDirectory($vehicleDir, 0755);
            FileHelper::createDirectory($policyDir, 0755);
        } catch (\Exception $e) {
            Yii::error("Error al crear directorios: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    
        // Guardar imágenes de vehículo y póliza utilizando método auxiliar
        $vehicleImages = UploadedFile::getInstancesByName('vehicle_images');
        if (!empty($vehicleImages)) {
            Yii::info("Se encontraron " . count($vehicleImages) . " imágenes de vehículo.", __METHOD__);
            $this->saveFiles($vehicleImages, $vehicleDir, 'imagen de vehículo');
        } else {
            Yii::info("No se encontraron imágenes de vehículo.", __METHOD__);
        }
    
        $policyImages = UploadedFile::getInstancesByName('policy_images');
        if (!empty($policyImages)) {
            Yii::info("Se encontraron " . count($policyImages) . " imágenes de póliza.", __METHOD__);
            $this->saveFiles($policyImages, $policyDir, 'imagen de póliza');
        } else {
            Yii::info("No se encontraron imágenes de póliza.", __METHOD__);
        }
    }
    
    private function saveFiles($files, $directory, $type)
    {
        foreach ($files as $file) {
            $filePath = $directory . '/' . $file->baseName . '.' . $file->extension;
            if ($file->saveAs($filePath)) {
                Yii::info("Archivo $type guardado en: $filePath", __METHOD__);
            } else {
                Yii::error("Error al guardar $type: " . $file->error, __METHOD__);
                throw new \Exception("Error al guardar $type.");
            }
        }
    }
    
    public function actionCreate()
    {
        $model = new Dispositivos();
    
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
            try {
                if ($model->save()) {
                    // Call the uploadImages function
                    $this->uploadImages($model);
    
                    return ['success' => true, 'message' => 'Dispositivo creado exitosamente.'];
                } else {
                    return ['success' => false, 'message' => 'Error al guardar el dispositivo.', 'errors' => $model->errors];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Ocurrió un error: ' . $e->getMessage()];
            }
        }
    
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
