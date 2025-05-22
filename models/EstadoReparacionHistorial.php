<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "estado_reparacion_historial".
 *
 * @property int $id
 * @property int $reparacion_id
 * @property int $estado_anterior
 * @property int $estado_nuevo
 * @property string $fecha_cambio
 * @property string|null $comentario
 * @property int $usuario_id
 *
 * @property ReparacionVehiculo $reparacion
 */
class EstadoReparacionHistorial extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estado_reparacion_historial';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reparacion_id', 'estado_nuevo', 'fecha_cambio'], 'required'],
            [['reparacion_id', 'estado_anterior', 'estado_nuevo', 'usuario_id'], 'integer'],
            [['fecha_cambio'], 'safe'],
            [['comentario'], 'string'],
            [['reparacion_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReparacionVehiculo::class, 'targetAttribute' => ['reparacion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reparacion_id' => 'ID Reparación',
            'estado_anterior' => 'Estado Anterior',
            'estado_nuevo' => 'Estado Nuevo',
            'fecha_cambio' => 'Fecha de Cambio',
            'comentario' => 'Comentario',
            'usuario_id' => 'Usuario',
        ];
    }

    /**
     * Gets query for [[Reparacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReparacion()
    {
        return $this->hasOne(ReparacionVehiculo::class, ['id' => 'reparacion_id']);
    }
    
    /**
     * Devuelve el nombre del estado según su ID
     * 
     * @param int $estadoId
     * @return string
     */
    public static function getNombreEstado($estadoId)
    {
        $estados = [
            1 => 'Pendiente',
            2 => 'En Proceso',
            3 => 'Pausado',
            4 => 'Completado'
        ];
        
        return isset($estados[$estadoId]) ? $estados[$estadoId] : 'Desconocido';
    }
    
    /**
     * Devuelve la clase CSS para el estado
     * 
     * @param int $estadoId
     * @return string
     */
    public static function getClaseEstado($estadoId)
    {
        $clases = [
            1 => 'warning',   // Pendiente
            2 => 'primary',   // En Proceso
            3 => 'info',      // Pausado
            4 => 'success'    // Completado
        ];
        
        return isset($clases[$estadoId]) ? $clases[$estadoId] : 'secondary';
    }
}