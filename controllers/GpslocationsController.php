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

     // ...existing code...

public function actionGeocerca()
{
    return $this->render('geocerca');
}

public function actionSaveGeofence()
{
    $request = Yii::$app->request;
    $geofenceData = $request->post('geofenceData');

    return $this->asJson(['status' => 'success']);
}


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
        $startDateTime = $startDate . ' 00:00:00';
        $query->andWhere(['>=', 'lastUpdate', $startDateTime]);
    }

    if ($endDate) {
        $endDateTime = $endDate . ' 23:59:59';
        $query->andWhere(['<=', 'lastUpdate', $endDateTime]);
    }

    Yii::info($query->createCommand()->getRawSql(), __METHOD__); // Log the SQL query

    $gpsLocations = $query->all();

    if (empty($gpsLocations)) {
        return $this->asJson([]);
    }

    $locations = [];
    $totalDistance = 0;

    for ($i = 0; $i < count($gpsLocations); $i++) {
        $location = $gpsLocations[$i];
        $locations[] = [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'lastUpdate' => date('Y-m-d H:i:s', strtotime($location->lastUpdate)),
            'speed' => $location->speed,
            'direction' => $location->direction,
        ];

        // Calcular la distancia entre puntos consecutivos
        if ($i > 0) {
            $prevLocation = $gpsLocations[$i - 1];
            $totalDistance += $this->calculateDistance(
                $prevLocation->latitude, $prevLocation->longitude,
                $location->latitude, $location->longitude
            );
        }
    }

    return $this->asJson(['locations' => $locations, 'totalDistance' => $totalDistance]);
}

private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // Radio de la Tierra en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return $distance; // Retorna la distancia en km
}




// public function actionGetDistances()
// {
//     $date = '2024-12-23';
//     $startDate = $date . ' 00:00:00';
//     $endDate = $date . ' 23:59:59';

//     $gpsLocations = Gpslocations::find()
//         ->where(['between', 'lastUpdate', $startDate, $endDate])
//         ->orderBy(['lastUpdate' => SORT_ASC])
//         ->all();

//     if (count($gpsLocations) < 2) {
//         return $this->asJson(['error' => 'Not enough data points']);
//     }

//     $maxDistance = 0;
//     $minDistance = PHP_INT_MAX;
//     $maxPoints = [];
//     $minPoints = [];

//     for ($i = 0; $i < count($gpsLocations) - 1; $i++) {
//         for ($j = $i + 1; $j < count($gpsLocations); $j++) {
//             $distance = $this->calculateDistance(
//                 $gpsLocations[$i]->latitude,
//                 $gpsLocations[$i]->longitude,
//                 $gpsLocations[$j]->latitude,
//                 $gpsLocations[$j]->longitude
//             );

//             if ($distance > $maxDistance) {
//                 $maxDistance = $distance;
//                 $maxPoints = [$gpsLocations[$i], $gpsLocations[$j]];
//             }

//             if ($distance < $minDistance) {
//                 $minDistance = $distance;
//                 $minPoints = [$gpsLocations[$i], $gpsLocations[$j]];
//             }
//         }
//     }

//     return $this->asJson([
//         'maxDistance' => $maxDistance,
//         'maxPoints' => $maxPoints,
//         'minDistance' => $minDistance,
//         'minPoints' => $minPoints,
//     ]);
// }
   
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
