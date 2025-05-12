<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notificaciones".
 *
 * @property int $id
 * @property string $tipo Tipo de notificación (geocerca, mantenimiento, etc)
 * @property string $mensaje Mensaje de la notificación
 * @property int $id_vehiculo ID del vehículo relacionado
 * @property int|null $leido Estado de lectura (0=no leído, 1=leído)
 * @property string $fecha_creacion Fecha de creación
 * @property string|null $fecha_lectura Fecha de lectura
 * @property string|null $datos_adicionales Datos adicionales en formato JSON
 *
 * @property Vehiculos $vehiculo
 */
class Notificaciones extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notificaciones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_lectura', 'datos_adicionales'], 'default', 'value' => null],
            [['leido'], 'default', 'value' => 0],
            [['tipo', 'mensaje', 'id_vehiculo', 'fecha_creacion'], 'required'],
            [['id_vehiculo', 'leido'], 'integer'],
            [['fecha_creacion', 'fecha_lectura'], 'safe'],
            [['datos_adicionales'], 'string'],
            [['tipo'], 'string', 'max' => 50],
            [['mensaje'], 'string', 'max' => 255],
            [['id_vehiculo'], 'exist', 'skipOnError' => true, 'targetClass' => Vehiculos::class, 'targetAttribute' => ['id_vehiculo' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
            'mensaje' => 'Mensaje',
            'id_vehiculo' => 'Id Vehiculo',
            'leido' => 'Leido',
            'fecha_creacion' => 'Fecha Creacion',
            'fecha_lectura' => 'Fecha Lectura',
            'datos_adicionales' => 'Datos Adicionales',
        ];
    }

    /**
     * Gets query for [[Vehiculo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiculo()
    {
        return $this->hasOne(Vehiculos::class, ['id' => 'id_vehiculo']);
    }

}
