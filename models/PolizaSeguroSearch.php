<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PolizaSeguro;

/**
 * PolizaSeguroSearch represents the model behind the search form of `app\models\PolizaSeguro`.
 */
class PolizaSeguroSearch extends PolizaSeguro
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['aseguradora', 'no_poliza', 'fecha_compra', 'fecha_vencimiento'], 'safe'],
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
        $query = PolizaSeguro::find();

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
        ]);

        $query->andFilterWhere(['like', 'aseguradora', $this->aseguradora])
            ->andFilterWhere(['like', 'no_poliza', $this->no_poliza])
            ->andFilterWhere(['like', 'fecha_compra', $this->fecha_compra])
            ->andFilterWhere(['like', 'fecha_vencimiento', $this->fecha_vencimiento]);

        return $dataProvider;
    }
}
