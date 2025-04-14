<?php

namespace app\controllers;

use app\models\Geocerca;
use app\models\GeocercaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;
/**
 * GeocercaController implements the CRUD actions for Geocerca model.
 */
class GeocercaController extends Controller
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
     * Lists all Geocerca models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GeocercaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Geocerca model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Geocerca model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Geocerca();

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
    public function actionCreateAjax()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (\Yii::$app->request->isPost) {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);
        
        // Validate required fields
        if (empty($data['name']) || empty($data['description']) || empty($data['coordinates'])) {
            return [
                'success' => false,
                'message' => 'Name, description and coordinates are required'
            ];
        }
        
        $model = new Geocerca();
        $model->name = $data['name'];
        $model->description = $data['description'];
        $model->coordinates = $data['coordinates'];
        $model->created_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Geofence saved successfully'
            ];
        } else {
            \Yii::error('Error saving geofence: ' . json_encode($model->getErrors()));
            return [
                'success' => false,
                'message' => $model->getErrors()
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Invalid request method'
    ];
}

    /**
     * Updates an existing Geocerca model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        if (\Yii::$app->request->isPost) {
            $data = json_decode(\Yii::$app->request->getRawBody(), true);
            
            // Validate required fields
            if (empty($data['name']) || empty($data['description'])) {
                return [
                    'success' => false,
                    'message' => 'Name and description are required'
                ];
            }
            
            $model->name = $data['name'];
            $model->description = $data['description'];
            
            // Only update coordinates if they are provided
            if (!empty($data['coordinates'])) {
                $model->coordinates = $data['coordinates'];
            }
            
         
            
            if ($model->save()) {
                return [
                    'success' => true,
                    'message' => 'Geofence updated successfully'
                ];
            } else {
                \Yii::error('Error updating geofence: ' . json_encode($model->getErrors()));
                return [
                    'success' => false,
                    'message' => $model->getErrors()
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Invalid request method'
        ];
    }

    public function actionUpdateCoordinates($id)
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $model = $this->findModel($id);
    $data = json_decode(Yii::$app->request->getRawBody(), true);
    
    if ($model && isset($data['coordinates'])) {
        $model->coordinates = $data['coordinates'];
        if ($model->save()) {
            return ['success' => true];
        }
    }
    
    return ['success' => false];
}
    /**
     * Deletes an existing Geocerca model.
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
     * Finds the Geocerca model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Geocerca the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Geocerca::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
