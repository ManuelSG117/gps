<?php

namespace app\controllers;

use app\models\Conductores;
use app\models\ConductoresSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\httpclient\Client;
use Yii;
/**
 * ConductoresController implements the CRUD actions for Conductores model.
 */
class ConductoresController extends Controller
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

     public function actionGetEstados()
     {
         $token = '8e31c000-9199-be10-eff1-04dd071e4c18';  
         $url = 'https://gaia.inegi.org.mx/wscatgeo/v2/mgee/';
         
         // Crear el cliente HTTP
         $client = new Client();
         // Realizar la solicitud GET
         $response = $client->get($url, ['token' => $token])->send();
         
         // Configurar la respuesta para que se devuelva en formato JSON
         Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
         
         // Verificar si la respuesta es exitosa
         if ($response->isOk) {
             return $response->data;
         } else {
             throw new \yii\web\HttpException($response->statusCode, 'Error al obtener los estados');
         }
     }
     

    /**
     * Lists all Conductores models.
     *
     * @return string
     */
    
    public function actionIndex()
    {
        $searchModel = new ConductoresSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    
        // Crear un nuevo modelo para el formulario del modal
        $model = new Conductores();
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model, // Pasa el modelo a la vista
        ]);
    }

    public function actionCreate()
    {
        $model = new Conductores();
    
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'Conductor creado exitosamente.'];
        }
    
        return $this->renderAjax('_modal', ['model' => $model]);
    }
    
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'message' => 'Conductor actualizado correctamente.',
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
     
     public function actionDelete($id)
     {
         if (Yii::$app->request->isAjax) {
             $this->findModel($id)->delete();
             Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
             return ['success' => true, 'message' => 'El conductor ha sido eliminado exitosamente.'];
         }
     
         $this->findModel($id)->delete();
         return $this->redirect(['index']);
     }
    
    /**
     * Displays a single Conductores model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
   
    /**
     * Creates a new Conductores model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    
  
    /**
     * Updates an existing Conductores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */


    /**
     * Deletes an existing Conductores model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
   
    

    /**
     * Finds the Conductores model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conductores the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conductores::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
