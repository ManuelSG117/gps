<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "poliza_seguro".
 *
 * @property int $id
 * @property string $aseguradora
 * @property string $no_poliza
 * @property string|null $fecha_compra
 * @property string|null $fecha_vencimiento
 *
 * @property Vehiculos[] $vehiculos
 */
class PolizaSeguro extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poliza_seguro';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_compra', 'fecha_vencimiento'], 'default', 'value' => null],
            [['aseguradora', 'no_poliza'], 'required'],
            [['aseguradora', 'no_poliza', 'fecha_compra', 'fecha_vencimiento'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aseguradora' => 'Aseguradora',
            'no_poliza' => 'No Poliza',
            'fecha_compra' => 'Fecha Compra',
            'fecha_vencimiento' => 'Fecha Vencimiento',
        ];
    }

    /**
     * Gets query for [[Vehiculos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiculos()
    {
        return $this->hasMany(Vehiculos::class, ['poliza_id' => 'id']);
    }

}
