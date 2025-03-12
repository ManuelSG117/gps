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
 * @property string $modelo_auto
 * @property string $marca_auto
 * @property string $placa
 * @property string $no_serie
 * @property string|null $color_auto
 * @property string|null $ano_auto
 * @property string|null $velocidad_max
 * @property int|null $sensor_temp
 * @property string $tipo_motor
 * @property string|null $km_litro
 * @property string|null $aseguradora
 * @property string|null $no_poliza
 * @property string|null $fecha_vencimiento
 * @property string|null $fecha_compra
 * @property string $direccion
 * @property string $departamento
 * @property string|null $conductor_id
 * @property string|null $estado_vehiculo
 * @property string|null $estado_llantas
 * @property string|null $km_recorridos
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
            [['marca', 'modelo', 'color_auto', 'ano_auto', 'velocidad_max', 'sensor_temp', 'km_litro', 'aseguradora', 'no_poliza', 'fecha_vencimiento', 'fecha_compra', 'conductor_id'], 'default', 'value' => null],
            [['nombre', 'imei', 'num_tel', 'cat_dispositivo', 'modelo_auto', 'marca_auto', 'placa', 'no_serie', 'tipo_motor', 'direccion', 'departamento'], 'required'],
            [['sensor_temp'], 'integer'],
            [['aseguradora'], 'string'],
            [['fecha_vencimiento', 'fecha_compra'], 'safe'],
            [['nombre', 'direccion', 'departamento'], 'string', 'max' => 100],
            [['imei'], 'string', 'max' => 15],
            [['num_tel', 'placa', 'velocidad_max', 'km_litro'], 'string', 'max' => 10],
            [['marca', 'modelo', 'cat_dispositivo', 'modelo_auto', 'marca_auto'], 'string', 'max' => 60],
            [['no_serie'], 'string', 'max' => 17],
            [['color_auto', 'tipo_motor', 'conductor_id', 'estado_vehiculo', 'estado_llantas','km_recorridos'], 'string', 'max' => 45],
            [['ano_auto'], 'string', 'max' => 4],
            [['no_poliza'], 'string', 'max' => 20],
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
            'num_tel' => 'Número Tel.',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'cat_dispositivo' => 'Categoria Dispositivo',
            'modelo_auto' => 'Modelo Auto',
            'marca_auto' => 'Marca Auto',
            'placa' => 'Placa',
            'no_serie' => 'No Serie',
            'color_auto' => 'Color Auto',
            'ano_auto' => 'Año Auto',
            'velocidad_max' => 'Velocidad Max',
            'sensor_temp' => 'Sensor Temp.',
            'tipo_motor' => 'Tipo Motor',
            'km_litro' => 'Km por Litro',
            'aseguradora' => 'Aseguradora',
            'no_poliza' => 'No.Poliza',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'fecha_compra' => 'Fecha Compra',
            'direccion' => 'Dirección',
            'departamento' => 'Departamento',
            'conductor_id' => 'Conductor',
            'estado_vehiculo' => 'Estado Vehiculo',
            'estado_llantas' => 'Estado Llantas',
            'km_recorridos' => 'Km Recorridos'
        ];
    }

}
