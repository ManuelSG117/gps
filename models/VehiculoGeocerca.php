<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vehiculo_geocerca".
 *
 * @property int $id
 * @property int $vehiculo_id
 * @property int $geocerca_id
 * @property string|null $created_at
 * @property int|null $activo
 *
 * @property Geocerca $geocerca
 * @property Vehiculos $vehiculo
 */
class VehiculoGeocerca extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehiculo_geocerca';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activo'], 'default', 'value' => 1],
            [['vehiculo_id', 'geocerca_id'], 'required'],
            [['vehiculo_id', 'geocerca_id', 'activo'], 'integer'],
            [['created_at'], 'safe'],
            [['vehiculo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehiculos::class, 'targetAttribute' => ['vehiculo_id' => 'id']],
            [['geocerca_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geocerca::class, 'targetAttribute' => ['geocerca_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vehiculo_id' => 'Vehiculo ID',
            'geocerca_id' => 'Geocerca ID',
            'created_at' => 'Created At',
            'activo' => 'Activo',
        ];
    }

    /**
     * Gets query for [[Geocerca]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGeocerca()
    {
        return $this->hasOne(Geocerca::class, ['id' => 'geocerca_id']);
    }

    /**
     * Gets query for [[Vehiculo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiculo()
    {
        return $this->hasOne(Vehiculos::class, ['id' => 'vehiculo_id']);
    }

}
