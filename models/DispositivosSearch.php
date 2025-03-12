<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Dispositivos;

/**
 * DispositivosSearch represents the model behind the search form of `app\models\Dispositivos`.
 */
class DispositivosSearch extends Dispositivos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'sensor_temp'], 'integer'],
            [['nombre', 'imei', 'num_tel', 'marca', 'modelo', 'cat_dispositivo', 'modelo_auto', 'marca_auto', 'placa', 'no_serie', 'color_auto', 'ano_auto', 'velocidad_max', 'tipo_motor', 'km_litro', 'aseguradora', 'no_poliza', 'fecha_vencimiento', 'fecha_compra', 'direccion', 'departamento', 'conductor_id'], 'safe'],
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
        $query = Dispositivos::find();

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
            'sensor_temp' => $this->sensor_temp,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'fecha_compra' => $this->fecha_compra,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'imei', $this->imei])
            ->andFilterWhere(['like', 'num_tel', $this->num_tel])
            ->andFilterWhere(['like', 'marca', $this->marca])
            ->andFilterWhere(['like', 'modelo', $this->modelo])
            ->andFilterWhere(['like', 'cat_dispositivo', $this->cat_dispositivo])
            ->andFilterWhere(['like', 'modelo_auto', $this->modelo_auto])
            ->andFilterWhere(['like', 'marca_auto', $this->marca_auto])
            ->andFilterWhere(['like', 'placa', $this->placa])
            ->andFilterWhere(['like', 'no_serie', $this->no_serie])
            ->andFilterWhere(['like', 'color_auto', $this->color_auto])
            ->andFilterWhere(['like', 'ano_auto', $this->ano_auto])
            ->andFilterWhere(['like', 'velocidad_max', $this->velocidad_max])
            ->andFilterWhere(['like', 'tipo_motor', $this->tipo_motor])
            ->andFilterWhere(['like', 'km_litro', $this->km_litro])
            ->andFilterWhere(['like', 'aseguradora', $this->aseguradora])
            ->andFilterWhere(['like', 'no_poliza', $this->no_poliza])
            ->andFilterWhere(['like', 'direccion', $this->direccion])
            ->andFilterWhere(['like', 'departamento', $this->departamento])
            ->andFilterWhere(['like', 'conductor_id', $this->conductor_id]);

        return $dataProvider;
    }
}
