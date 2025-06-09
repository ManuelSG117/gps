<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Vehiculos;

/**
 * VehiculosSearch represents the model behind the search form of `app\models\Vehiculos`.
 */
class VehiculosSearch extends Vehiculos
{
    public $identificador = [];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'km_recorridos', 'velocidad_max', 'km_litro', 'estatus', 'conductor_id', 'dispositivo_id', 'poliza_id', 'direccion_id', 'departamento_id'], 'integer'],
            [['modelo_auto', 'marca_auto', 'placa', 'no_serie', 'ano_adquisicion', 'ano_auto', 'color_auto', 'tipo_motor', 'estado_llantas', 'estado_vehiculo', 'estado_motor'], 'safe'],
            ['identificador', 'safe'],
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
        $query = Vehiculos::find();

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
            'ano_adquisicion' => $this->ano_adquisicion,
            'ano_auto' => $this->ano_auto,
            'km_recorridos' => $this->km_recorridos,
            'velocidad_max' => $this->velocidad_max,
            'km_litro' => $this->km_litro,
            'estatus' => $this->estatus,
            'conductor_id' => $this->conductor_id,
            'dispositivo_id' => $this->dispositivo_id,
            'poliza_id' => $this->poliza_id,
            'direccion_id' => $this->direccion_id,
            'departamento_id' => $this->departamento_id,
        ]);

        // Filtro especial para identificador (soporta array para select2 multiple)
        if (is_array($this->identificador) && count($this->identificador) > 0) {
            $query->andFilterWhere(['in', 'identificador', $this->identificador]);
        } elseif (!empty($this->identificador)) {
            $query->andFilterWhere(['like', 'identificador', $this->identificador]);
        }

        $query->andFilterWhere(['like', 'modelo_auto', $this->modelo_auto])
            ->andFilterWhere(['like', 'marca_auto', $this->marca_auto])
            ->andFilterWhere(['like', 'placa', $this->placa])
            ->andFilterWhere(['like', 'no_serie', $this->no_serie])
            ->andFilterWhere(['like', 'color_auto', $this->color_auto])
            ->andFilterWhere(['like', 'tipo_motor', $this->tipo_motor])
            ->andFilterWhere(['like', 'estado_llantas', $this->estado_llantas])
            ->andFilterWhere(['like', 'estado_vehiculo', $this->estado_vehiculo])
            ->andFilterWhere(['like', 'estado_motor', $this->estado_motor]);

        return $dataProvider;
    }
}
