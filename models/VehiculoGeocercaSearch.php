<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\VehiculoGeocerca;

/**
 * VehiculoGeocercaSearch representa el modelo detrás del formulario de búsqueda de `app\models\VehiculoGeocerca`.
 */
class VehiculoGeocercaSearch extends VehiculoGeocerca
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vehiculo_id', 'geocerca_id', 'activo'], 'integer'],
            [['created_at'], 'safe'],
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
     * Crea una instancia de proveedor de datos con la consulta de búsqueda aplicada
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = VehiculoGeocerca::find();

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
            'id' => $this->id,
            'vehiculo_id' => $this->vehiculo_id,
            'geocerca_id' => $this->geocerca_id,
            'created_at' => $this->created_at,
            'activo' => $this->activo,
        ]);

        return $dataProvider;
    }
}