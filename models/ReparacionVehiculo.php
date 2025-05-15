<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reparacion_vehiculo".
 *
 * @property int $id
 * @property int $vehiculo_id
 * @property string $fecha
 * @property string $tipo_servicio
 * @property string $descripcion
 * @property float|null $costo
 * @property string|null $tecnico
 * @property string|null $notas
 * @property int $estatus
 *
 * @property Vehiculos $vehiculo
 */
class ReparacionVehiculo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparacion_vehiculo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['costo', 'tecnico', 'notas'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 1],
            [['vehiculo_id', 'fecha', 'tipo_servicio', 'descripcion'], 'required'],
            [['vehiculo_id', 'estatus'], 'integer'],
            [['fecha'], 'safe'],
            [['descripcion', 'notas'], 'string'],
            [['costo'], 'number'],
            [['tipo_servicio'], 'string', 'max' => 50],
            [['tecnico'], 'string', 'max' => 100],
            [['vehiculo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehiculos::class, 'targetAttribute' => ['vehiculo_id' => 'id']],
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
            'fecha' => 'Fecha',
            'tipo_servicio' => 'Tipo Servicio',
            'descripcion' => 'Descripcion',
            'costo' => 'Costo',
            'tecnico' => 'Tecnico',
            'notas' => 'Notas',
            'estatus' => 'Estatus',
        ];
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
