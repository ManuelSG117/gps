<?php

namespace app\controllers;

use app\models\Gpslocations;
use app\models\GpslocationsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use Yii;
use yii\web\BadRequestHttpException;


/**
 * GpslocationsController implements the CRUD actions for Gpslocations model.
 */
class GpslocationsController extends Controller
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
     * Lists all Gpslocations models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GpslocationsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexT()
    {
        $searchModel = new GpslocationsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('tlaloc', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public $enableCsrfValidation = false; // Deshabilitar CSRF para las solicitudes entrantes

    public function actionReceiveDatas()
    {
        $request = Yii::$app->request;

      
        // Obtener los parámetros enviados a través de GET
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $speed = $request->get('speed');
        $direction = $request->get('direction');
        $gpsTime = $request->get('gpsTime');
        $phoneNumber = $request->get('phoneNumber');
        $userName = $request->get('userName');
        $sessionID = $request->get('sessionID');
        $locationMethod = $request->get('locationMethod');
        $accuracy = $request->get('accuracy');
        $extraInfo = $request->get('extraInfo');
        $eventType = $request->get('eventType');

        // Verificar que los parámetros obligatorios estén presentes
        if (!$latitude || !$longitude || !$gpsTime || !$phoneNumber || !$userName) {
            throw new BadRequestHttpException('Faltan parámetros obligatorios.');
        }

        // Crear y guardar los datos en la base de datos
        $gpsLocation = new Gpslocations();  // Usando el modelo Gpslocations
        $gpsLocation->latitude = $latitude;
        $gpsLocation->longitude = $longitude;
        $gpsLocation->speed = $speed;
        $gpsLocation->direction = $direction;
        $gpsLocation->gpsTime = $gpsTime;
        $gpsLocation->phoneNumber = $phoneNumber;
        $gpsLocation->userName = $userName;
        $gpsLocation->sessionID = $sessionID;
        $gpsLocation->locationMethod = $locationMethod;
        $gpsLocation->accuracy = $accuracy;
        $gpsLocation->extraInfo = $extraInfo;
        $gpsLocation->eventType = $eventType;

        // Si 'lastUpdate' es un timestamp, puedes configurarlo automáticamente
        $gpsLocation->lastUpdate = date('Y-m-d H:i:s');

        // Intentar guardar el modelo
        if ($gpsLocation->save()) {
            // Responder con una respuesta JSON exitosa
            return $this->asJson([
                'status' => 'success',
                'message' => 'Data received and saved successfully',
                'data' => $gpsLocation
            ]);
        } else {
            // Si hay errores, imprimimos los errores de validación
            return $this->asJson([
                'status' => 'error',
                'message' => 'Failed to save data',
                'errors' => $gpsLocation->errors  // Imprime los errores de validación
            ]);
        }
    }
    
    public function actionGetLocations()
    {
        // Obtener todas las ubicaciones de la tabla gpslocations
        $gpsLocations = Gpslocations::find()->all();
    
        // Crear un array para almacenar las ubicaciones
        $locations = [];
    
        // Recorrer las ubicaciones y agregarlas al array
        foreach ($gpsLocations as $location) {
            $locations[] = [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'direction' => $location->direction,
                'gpsTime' => $location->gpsTime,
                'phoneNumber' => $location->phoneNumber,
                'userName' => $location->userName,
                'sessionID' => $location->sessionID,
                'locationMethod' => $location->locationMethod,
                'accuracy' => $location->accuracy,
                'extraInfo' => $location->extraInfo,
                'eventType' => $location->eventType,
            ];
        }
    
        // Devolver las ubicaciones en formato JSON
        return $this->asJson($locations);
    }

public function actionGetLocationsTime()
{
    // Obtener las ubicaciones más recientes de cada GPS por sessionID
    $subQuery = (new \yii\db\Query())
        ->select(['sessionID', 'MAX(lastUpdate) as maxLastUpdate'])
        ->from('gpslocations')
        ->groupBy('sessionID');

    $gpsLocations = Gpslocations::find()
        ->innerJoin(['sub' => $subQuery], 'gpslocations.sessionID = sub.sessionID AND gpslocations.lastUpdate = sub.maxLastUpdate')
        ->all();

    // Crear un array para almacenar las ubicaciones
    $locations = [];
    $seenSessionIDs = [];

    // Recorrer las ubicaciones y agregarlas al array
    foreach ($gpsLocations as $location) {
        if (!in_array($location->sessionID, $seenSessionIDs)) {
            $locations[] = [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'direction' => $location->direction,
                'gpsTime' => $location->gpsTime,
                'phoneNumber' => $location->phoneNumber,
                'userName' => $location->userName,
                'sessionID' => $location->sessionID,
                'locationMethod' => $location->locationMethod,
                'accuracy' => $location->accuracy,
                'extraInfo' => $location->extraInfo,
                'eventType' => $location->eventType,
                'lastUpdate' => $location->lastUpdate,
            ];
            $seenSessionIDs[] = $location->sessionID;
        }
    }

    // Devolver las ubicaciones en formato JSON
    return $this->asJson($locations);
}

public function actionGetGpsOptions()
{
    $gpsOptions = Gpslocations::find()
        ->select(['phoneNumber', 'userName'])
        ->groupBy(['phoneNumber', 'userName'])
        ->all();

    $options = [];
    foreach ($gpsOptions as $gps) {
        $options[] = [
            'phoneNumber' => $gps->phoneNumber,
            'userName' => $gps->userName,
        ];
    }

    return $this->asJson($options);
}

public function actionGetRoute($phoneNumber, $startDate = null, $endDate = null)
{
    $query = Gpslocations::find()
        ->where(['phoneNumber' => $phoneNumber])
        ->orderBy(['lastUpdate' => SORT_ASC]);

    if ($startDate) {
        $query->andWhere(['>=', 'lastUpdate', $startDate]);
    }

    if ($endDate) {
        $query->andWhere(['<=', 'lastUpdate', $endDate]);
    }

    Yii::info($query->createCommand()->getRawSql(), __METHOD__); // Log the SQL query

    $gpsLocations = $query->all();

    if (empty($gpsLocations)) {
        return $this->asJson([]);
    }

    $locations = [];
    foreach ($gpsLocations as $location) {
        $locations[] = [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'lastUpdate' => date('Y-m-d H:i:s', strtotime($location->lastUpdate)),
            'speed' => $location->speed,
        ];
    }

    return $this->asJson($locations);
}





    
    /**
     * Displays a single Gpslocations model.
     * @param int $GPSLocationID Gps Location ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($GPSLocationID)
    {
        return $this->render('view', [
            'model' => $this->findModel($GPSLocationID),
        ]);
    }

    /**
     * Creates a new Gpslocations model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Gpslocations();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'GPSLocationID' => $model->GPSLocationID]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Gpslocations model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $GPSLocationID Gps Location ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($GPSLocationID)
    {
        $model = $this->findModel($GPSLocationID);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'GPSLocationID' => $model->GPSLocationID]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Gpslocations model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $GPSLocationID Gps Location ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($GPSLocationID)
    {
        $this->findModel($GPSLocationID)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Gpslocations model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $GPSLocationID Gps Location ID
     * @return Gpslocations the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($GPSLocationID)
    {
        if (($model = Gpslocations::findOne(['GPSLocationID' => $GPSLocationID])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
