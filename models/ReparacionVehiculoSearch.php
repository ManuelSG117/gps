<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ReparacionVehiculo;

/**
 * ReparacionVehiculoSearch represents the model behind the search form of `app\models\ReparacionVehiculo`.
 */
class ReparacionVehiculoSearch extends ReparacionVehiculo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vehiculo_id', 'estatus', 'estado_servicio'], 'integer'],
            [['fecha', 'tipo_servicio', 'descripcion', 'tecnico', 'notas', 'motivo_pausa', 'requisitos_reanudar', 'fecha_finalizacion'], 'safe'],
            [['costo'], 'number'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = ReparacionVehiculo::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'vehiculo_id' => $this->vehiculo_id,
            'fecha' => $this->fecha,
            'costo' => $this->costo,
            'estatus' => $this->estatus,
            'estado_servicio' => $this->estado_servicio,
            'fecha_finalizacion' => $this->fecha_finalizacion,
        ]);

        $query->andFilterWhere(['like', 'tipo_servicio', $this->tipo_servicio])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'tecnico', $this->tecnico])
            ->andFilterWhere(['like', 'notas', $this->notas])
            ->andFilterWhere(['like', 'motivo_pausa', $this->motivo_pausa])
            ->andFilterWhere(['like', 'requisitos_reanudar', $this->requisitos_reanudar]);

        return $dataProvider;
    }
}
