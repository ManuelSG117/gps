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
 * @property int|null $estado_servicio
 * @property string|null $motivo_pausa
 * @property string|null $requisitos_reanudar
 * @property string|null $fecha_finalizacion
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
            [['costo', 'tecnico', 'notas', 'estado_servicio', 'motivo_pausa', 'requisitos_reanudar', 'fecha_finalizacion'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 1],
            [['vehiculo_id', 'fecha', 'tipo_servicio', 'descripcion'], 'required'],
            [['vehiculo_id', 'estatus', 'estado_servicio'], 'integer'],
            [['fecha', 'fecha_finalizacion'], 'safe'],
            [['descripcion', 'notas', 'motivo_pausa', 'requisitos_reanudar'], 'string'],
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
            'estado_servicio' => 'Estado Servicio',
            'motivo_pausa' => 'Motivo Pausa',
            'requisitos_reanudar' => 'Requisitos Reanudar',
            'fecha_finalizacion' => 'Fecha Finalizacion',
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
    
    /**
     * Gets query for [[EstadoHistorial]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstadoHistorial()
    {
        return $this->hasMany(EstadoReparacionHistorial::class, ['reparacion_id' => 'id'])->orderBy(['fecha_cambio' => SORT_DESC]);
    }
    
    /**
     * Cambia el estado de la reparación y registra el cambio en el historial
     * 
     * @param int $nuevoEstado
     * @param string $comentario
     * @return bool
     */
    public function cambiarEstado($nuevoEstado, $comentario = '')
    {
        // Si el estado actual es Completado (4), no permitir cambios
        if ($this->estado_servicio == 4) {
            return false;
        }
        
        // Guardar el estado anterior
        $estadoAnterior = $this->estado_servicio;
        
        // Actualizar el estado
        $this->estado_servicio = $nuevoEstado;
        
        // Si el nuevo estado es Completado, establecer la fecha de finalización
        if ($nuevoEstado == 4 && empty($this->fecha_finalizacion)) {
            $this->fecha_finalizacion = date('Y-m-d');
            
            // Reactivar el vehículo cuando se complete la reparación
            if ($this->vehiculo) {
                $this->vehiculo->estatus = 1; // Activo
                $this->vehiculo->save();
            }
        }
        // Si el estado es "En Proceso", marcar el vehículo como inactivo
        elseif ($nuevoEstado == 2) {
            if ($this->vehiculo) {
                $this->vehiculo->estatus = 0; // Inactivo
                $this->vehiculo->save();
            }
        }
        
        // Guardar el modelo
        if ($this->save()) {
            // Registrar en el historial
            $historial = new EstadoReparacionHistorial();
            $historial->reparacion_id = $this->id;
            $historial->estado_anterior = $estadoAnterior;
            $historial->estado_nuevo = $nuevoEstado;
            $historial->fecha_cambio = date('Y-m-d H:i:s');
            $historial->comentario = $comentario;
            $historial->usuario_id = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
            
            return $historial->save();
        }
        
        return false;
    }

}
