<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dispositivos".
 *
 * @property int $id
 * @property string $nombre
 * @property string $imei
 * @property string $num_tel
 * @property string|null $marca
 * @property string|null $modelo
 * @property string $cat_dispositivo
 *
 * @property Vehiculos[] $vehiculos
 */
class Dispositivos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dispositivos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['marca', 'modelo'], 'default', 'value' => null],
            [['nombre', 'imei', 'num_tel', 'cat_dispositivo'], 'required'],
            [['nombre'], 'string', 'max' => 100],
            [['imei'], 'string', 'max' => 15],
            [['num_tel'], 'string', 'max' => 10],
            [['marca', 'modelo', 'cat_dispositivo'], 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'imei' => 'Imei',
            'num_tel' => 'Num. Tel',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'cat_dispositivo' => 'Cat. Dispositivo',
        ];
    }

    /**
     * Gets query for [[Vehiculos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiculos()
    {
        return $this->hasMany(Vehiculos::class, ['dispositivo_id' => 'id']);
    }

}
