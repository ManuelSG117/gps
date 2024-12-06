<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gpslocations".
 *
 * @property int $GPSLocationID
 * @property string $lastUpdate
 * @property float $latitude
 * @property float $longitude
 * @property string $phoneNumber
 * @property string $userName
 * @property string $sessionID
 * @property int $speed
 * @property int $direction
 * @property float $distance
 * @property string $gpsTime
 * @property string $locationMethod
 * @property int $accuracy
 * @property string $extraInfo
 * @property string $eventType
 */
class Gpslocations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gpslocations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lastUpdate', 'gpsTime'], 'safe'],
            [['latitude', 'longitude', 'distance'], 'number'],
            [['speed', 'direction', 'accuracy'], 'integer'],
            [['phoneNumber', 'userName', 'sessionID', 'locationMethod', 'eventType'], 'string', 'max' => 50],
            [['extraInfo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'GPSLocationID' => 'Gps Location ID',
            'lastUpdate' => 'Last Update',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'phoneNumber' => 'Phone Number',
            'userName' => 'User Name',
            'sessionID' => 'Session ID',
            'speed' => 'Speed',
            'direction' => 'Direction',
            'distance' => 'Distance',
            'gpsTime' => 'Gps Time',
            'locationMethod' => 'Location Method',
            'accuracy' => 'Accuracy',
            'extraInfo' => 'Extra Info',
            'eventType' => 'Event Type',
        ];
    }
}
