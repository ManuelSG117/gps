<?php
namespace app\controllers;
use yii\web\Controller;
use app\models\Gpslocations;
use Yii;
use app\models\Geocerca;
use app\models\Vehiculos;


class WebserviceController extends Controller{

    // Algoritmo punto en polígono (ray casting)
    public static function pointInPolygon($point, $polygon) {
        $x = $point[0];
        $y = $point[1];
        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0]; $yi = $polygon[$i][1];
            $xj = $polygon[$j][0]; $yj = $polygon[$j][1];
            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-10) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }

private function getVehiculosCapasuData()
    {
        $vehiculos = \app\models\Vehiculos::find()->with(['dispositivo', 'conductor'])->all();
        $vehiculosDentro = [];
        $vehiculosFuera = [];

        // Buscar la geocerca 'capasu'
        $geocerca = \app\models\Geocerca::findOne(['name' => 'capasu']);
        if (!$geocerca) {
            return ['error' => 'No se encontró la geocerca capasu'];
        }

        $coords = array_map(function($pair) {
            $latlng = explode(',', $pair);
            return [floatval($latlng[0]), floatval($latlng[1])];
        }, explode('|', $geocerca->coordinates));

        foreach ($vehiculos as $vehiculo) {
            $imei = $vehiculo->dispositivo ? $vehiculo->dispositivo->imei : null;
            $ubicacion = null;
            if ($imei) {
                $ubicacion = \app\models\Gpslocations::find()
                    ->where(['phoneNumber' => $imei])
                    ->orderBy(['lastUpdate' => SORT_DESC])
                    ->one();
            }

            if ($ubicacion) {
                $isInside = self::pointInPolygon(
                    [$ubicacion->latitude, $ubicacion->longitude],
                    $coords
                );

                $vehiculoData = [
                    'id' => $vehiculo->id,
                    'modelo' => $vehiculo->modelo_auto,
                    'marca' => $vehiculo->marca_auto,
                    'placa' => $vehiculo->placa,
                    'identificador' => $vehiculo->identificador,
                    'latitude' => $ubicacion->latitude,
                    'longitude' => $ubicacion->longitude,
                    'ultima_actualizacion' => Yii::$app->formatter->asDatetime($ubicacion->lastUpdate, 'php:Y-m-d H:i:s'),
                    'velocidad' => $ubicacion->speed,
                    'estado' => $isInside ? 'dentro' : 'fuera',
                    'conductor' => $vehiculo->conductor ? 
                        $vehiculo->conductor->nombre . ' ' . 
                        $vehiculo->conductor->apellido_p . ' ' . 
                        $vehiculo->conductor->apellido_m
                    : null
                ];
                
                // Buscar la última ubicación con estado diferente
                $ultimaUbicacionDiferente = Gpslocations::find()
                    ->where(['phoneNumber' => $imei])
                    ->andWhere(['<', 'lastUpdate', $ubicacion->lastUpdate])
                    ->orderBy(['lastUpdate' => SORT_DESC])
                    ->one();
                
                if ($ultimaUbicacionDiferente) {
                    $estadoAnterior = self::pointInPolygon(
                        [$ultimaUbicacionDiferente->latitude, $ultimaUbicacionDiferente->longitude],
                        $coords
                    ) ? 'dentro' : 'fuera';
                    
                    if ($estadoAnterior !== ($isInside ? 'dentro' : 'fuera')) {
                        $vehiculoData['ultima_transicion'] = Yii::$app->formatter->asDatetime($ultimaUbicacionDiferente->lastUpdate, 'php:Y-m-d H:i:s');
                    }
                }

                if ($isInside) {
                    $vehiculosDentro[] = $vehiculoData;
                } else {
                    $vehiculosFuera[] = $vehiculoData;
                }
            }
        }

        return [
            'dentro' => $vehiculosDentro,
            'fuera' => $vehiculosFuera
        ];
    }

    public function actionGetVehiculosDentroCapasu()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $resultado = $this->getVehiculosCapasuData();
        
        if (isset($resultado['error'])) {
            return $resultado;
        }
        
        return $resultado['dentro'];
    }

    public function actionGetVehiculosFueraCapasu()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $resultado = $this->getVehiculosCapasuData();
        
        if (isset($resultado['error'])) {
            return $resultado;
        }
        
        return $resultado['fuera'];
    }

    public function actionBuscarVehiculo($busqueda)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Buscar por identificador del vehículo
        $vehiculo = Vehiculos::find()
            ->with(['dispositivo', 'conductor'])
            ->where(['identificador' => $busqueda])
            ->one();

        // Si no se encuentra por identificador, buscar por nombre completo del conductor
        if (!$vehiculo) {
            $vehiculo = Vehiculos::find()
                ->select(['vehiculos.*', 'conductores.*'])
                ->with(['dispositivo', 'conductor'])
                ->joinWith('conductor')
                ->where(['or',
                    ['like', 'CONCAT(conductores.nombre, " ", conductores.apellido_p, " ", conductores.apellido_m)', $busqueda],
                    ['like', 'CONCAT(conductores.nombre, " ", conductores.apellido_p)', $busqueda],
                    ['like', 'conductores.nombre', $busqueda]
                ])
                ->one();
        }

        if (!$vehiculo) {
            return ['error' => 'No se encontró ningún vehículo con el identificador o conductor especificado'];
        }

        if (!$vehiculo->dispositivo || !$vehiculo->dispositivo->imei) {
            return ['error' => 'El vehículo no tiene un dispositivo GPS asignado'];
        }

        // Obtener la última ubicación del vehículo
        $ubicacion = Gpslocations::find()
            ->where(['phoneNumber' => $vehiculo->dispositivo->imei])
            ->orderBy(['lastUpdate' => SORT_DESC])
            ->one();

        if (!$ubicacion) {
            return ['error' => 'No se encontró la ubicación del vehículo'];
        }

        return [
            'vehiculo' => [
                'modelo' => $vehiculo->modelo_auto,
                'marca' => $vehiculo->marca_auto,
                'placa' => $vehiculo->placa,
                'identificador' => $vehiculo->identificador,
                'conductor' => $vehiculo->conductor ? 
                    $vehiculo->conductor->nombre . ' ' . 
                    $vehiculo->conductor->apellido_p . ' ' . 
                    $vehiculo->conductor->apellido_m : null,
                'latitude' => $ubicacion->latitude,
                'longitude' => $ubicacion->longitude,
                'velocidad' => $ubicacion->speed,
                'direccion' => $ubicacion->direction,
                'ultima_actualizacion' => Yii::$app->formatter->asDatetime($ubicacion->lastUpdate, 'php:Y-m-d H:i:s')
            ]
        ];
    }
}