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
        
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
            // Asegúrate de incluir todos los atributos, especialmente el ID
            $model->refresh();  // Recarga el modelo para obtener el ID si es necesario
    
            return [
                'success' => true,
                'message' => 'Conductor creado exitosamente.',
                'conductor' => [
                    'id' => $model->id,  
                    'nombres' => $model->nombres,
                    'apellido_p' => $model->apellido_p,
                    'apellido_m' => $model->apellido_m,
                ],
            ];
        }
    
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => false, 'message' => 'Error al crear el conductor.'];
    }
    
    
    
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // Retornar los datos del conductor y un indicador de éxito
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => true,
                'model' => $model,
            ];
        }
    
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'success' => false,
            'error' => 'Hubo un error al actualizar los datos.',
        ];
    }
    

    public function actionGetConductor($id)
    {
        $model = $this->findModel($id);
        
        // Preparar los datos iniciales
        $initialValues = [
            'nombres' => $model->nombres,
            'apellido_p' => $model->apellido_p,
            'apellido_m' => $model->apellido_m,
            'fecha_nacimiento' => $model->fecha_nacimiento,
            'no_licencia' => $model->no_licencia,
            'cp' => $model->cp,
            'estado' => $model->estado,
            'municipio' => $model->municipio,
            'colonia' => $model->colonia,
            'calle' => $model->calle,
            'num_ext' => $model->num_ext,
            'num_int' => $model->num_int,
            'telefono' => $model->telefono,
            'email' => $model->email,
            'tipo_sangre' => $model->tipo_sangre,
            'nombres_contacto' => $model->nombres_contacto,
            'apellido_p_contacto' => $model->apellido_p_contacto,
            'apellido_m_contacto' => $model->apellido_m_contacto,
            'parentesco' => $model->parentesco,
            'telefono_contacto' => $model->telefono_contacto
        ];
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        return [
            'model' => $model,
            'initialValues' => $initialValues
        ];
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        try {
            $model = $this->findModel($id);
            if (!$model) {
                throw new \Exception('El registro no existe.');
            }
    
            if ($model->delete()) {
                Yii::debug('Registro eliminado correctamente: ' . $id, __METHOD__);
                return ['success' => true, 'message' => 'El registro ha sido eliminado.'];
            }
    
            throw new \Exception('No se pudo eliminar el registro.');
        } catch (\Exception $e) {
            Yii::error('Error al eliminar registro: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
