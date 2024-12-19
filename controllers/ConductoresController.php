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
         $token = '8e31c000-9199-be10-eff1-04dd071e4c18';  // Tu token de prueba
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

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionGetConductor($id)
{
    $model = $this->findModel($id);
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $model;
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
    


    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


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
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

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
