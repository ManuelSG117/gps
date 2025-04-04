<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Conductores;

/**
 * ConductoresSearch represents the model behind the search form of `app\models\Conductores`.
 */
class ConductoresSearch extends Conductores
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'num_ext', 'cp', 'telefono'], 'integer'],
            [['nombre', 'apellido_p', 'apellido_m', 'no_licencia', 'estado', 'municipio', 'colonia', 'calle', 'num_int', 'email', 'tipo_sangre', 'fecha_nacimiento', 'nombre_contacto', 'apellido_p_contacto', 'apellido_m_contacto', 'parentesco', 'telefono_contacto'], 'safe'],
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
        $query = Conductores::find();

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
            'num_ext' => $this->num_ext,
            'cp' => $this->cp,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'apellido_p', $this->apellido_p])
            ->andFilterWhere(['like', 'apellido_m', $this->apellido_m])
            ->andFilterWhere(['like', 'no_licencia', $this->no_licencia])
            ->andFilterWhere(['like', 'estado', $this->estado])
            ->andFilterWhere(['like', 'municipio', $this->municipio])
            ->andFilterWhere(['like', 'colonia', $this->colonia])
            ->andFilterWhere(['like', 'calle', $this->calle])
            ->andFilterWhere(['like', 'num_int', $this->num_int])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'tipo_sangre', $this->tipo_sangre])
            ->andFilterWhere(['like', 'nombre_contacto', $this->nombre_contacto])
            ->andFilterWhere(['like', 'apellido_p_contacto', $this->apellido_p_contacto])
            ->andFilterWhere(['like', 'apellido_m_contacto', $this->apellido_m_contacto])
            ->andFilterWhere(['like', 'parentesco', $this->parentesco])
            ->andFilterWhere(['like', 'telefono_contacto', $this->telefono_contacto]);

        return $dataProvider;
    }
}
