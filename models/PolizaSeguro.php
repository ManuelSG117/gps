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
 * @property int|null $estado
 *
 * @property Vehiculos[] $vehiculos
 * @property PolizaSeguroHistorial[] $historial
 */
class PolizaSeguro extends \yii\db\ActiveRecord
{
    // Estados de la póliza
    const ESTADO_ACTIVA = 1;
    const ESTADO_VENCIDA = 2;
    const ESTADO_CANCELADA = 3;
    const ESTADO_SUSPENDIDA = 4;
    const ESTADO_RENOVADA = 5;

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
            [['estado'], 'integer'],
            [['estado'], 'default', 'value' => self::ESTADO_ACTIVA],
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
            'estado' => 'Estado',
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
    
    /**
     * Gets query for [[Historial]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHistorial()
    {
        return $this->hasMany(PolizaSeguroHistorial::class, ['poliza_id' => 'id'])->orderBy(['fecha_cambio' => SORT_DESC]);
    }
    
    /**
     * Cambia el estado de la póliza y registra el cambio en el historial
     * 
     * @param int $nuevoEstado
     * @param string $comentario
     * @param string $motivo
     * @return bool
     */
    public function cambiarEstado($nuevoEstado, $comentario = '', $motivo = '')
    {
        // Guardar el estado anterior
        $estadoAnterior = $this->estado;
        
        // Actualizar el estado
        $this->estado = $nuevoEstado;
        
        // Guardar el modelo
        if ($this->save()) {
            // Registrar en el historial
            $historial = new PolizaSeguroHistorial();
            $historial->poliza_id = $this->id;
            $historial->estado_anterior = $estadoAnterior;
            $historial->estado_nuevo = $nuevoEstado;
            $historial->fecha_cambio = date('Y-m-d H:i:s');
            $historial->comentario = $comentario;
            $historial->usuario_id = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
            $historial->motivo = $motivo;
            
            return $historial->save();
        }
        
        return false;
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
            self::ESTADO_ACTIVA => 'Activa',
            self::ESTADO_VENCIDA => 'Vencida',
            self::ESTADO_CANCELADA => 'Cancelada',
            self::ESTADO_SUSPENDIDA => 'Suspendida',
            self::ESTADO_RENOVADA => 'Renovada'
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
            self::ESTADO_ACTIVA => 'success',   // Activa
            self::ESTADO_VENCIDA => 'danger',   // Vencida
            self::ESTADO_CANCELADA => 'dark',   // Cancelada
            self::ESTADO_SUSPENDIDA => 'warning', // Suspendida
            self::ESTADO_RENOVADA => 'primary'  // Renovada
        ];
        
        return isset($clases[$estadoId]) ? $clases[$estadoId] : 'secondary';
    }
}
