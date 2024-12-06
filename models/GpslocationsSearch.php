<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Gpslocations;

/**
 * GpslocationsSearch represents the model behind the search form of `app\models\Gpslocations`.
 */
class GpslocationsSearch extends Gpslocations
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['GPSLocationID', 'speed', 'direction', 'accuracy'], 'integer'],
            [['lastUpdate', 'phoneNumber', 'userName', 'sessionID', 'gpsTime', 'locationMethod', 'extraInfo', 'eventType'], 'safe'],
            [['latitude', 'longitude', 'distance'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Gpslocations::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'GPSLocationID' => $this->GPSLocationID,
            'lastUpdate' => $this->lastUpdate,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'speed' => $this->speed,
            'direction' => $this->direction,
            'distance' => $this->distance,
            'gpsTime' => $this->gpsTime,
            'accuracy' => $this->accuracy,
        ]);

        $query->andFilterWhere(['like', 'phoneNumber', $this->phoneNumber])
            ->andFilterWhere(['like', 'userName', $this->userName])
            ->andFilterWhere(['like', 'sessionID', $this->sessionID])
            ->andFilterWhere(['like', 'locationMethod', $this->locationMethod])
            ->andFilterWhere(['like', 'extraInfo', $this->extraInfo])
            ->andFilterWhere(['like', 'eventType', $this->eventType]);

        return $dataProvider;
    }
}
