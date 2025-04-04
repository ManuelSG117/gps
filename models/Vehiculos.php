<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vehiculos".
 *
 * @property int $id
 * @property string $modelo_auto
 * @property string $marca_auto
 * @property string $placa
 * @property string $no_serie
 * @property string $ano_adquisicion
 * @property string $ano_auto
 * @property int $km_recorridos
 * @property int $velocidad_max
 * @property int|null $km_litro
 * @property string|null $color_auto
 * @property string $tipo_motor
 * @property string|null $estado_llantas
 * @property string|null $estado_vehiculo
 * @property string|null $estado_motor
 * @property int $estatus
 * @property int|null $conductor_id
 * @property int|null $dispositivo_id
 * @property int|null $poliza_id
 * @property int|null $direccion_id
 * @property int|null $departamento_id
 *
 * @property Conductores $conductor
 * @property Dispositivos $dispositivo
 * @property PolizaSeguro $poliza
 */
class Vehiculos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehiculos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['km_litro', 'color_auto', 'estado_llantas', 'estado_vehiculo', 'estado_motor', 'conductor_id', 'dispositivo_id', 'poliza_id', 'direccion_id', 'departamento_id'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 1],
            [['modelo_auto', 'marca_auto', 'placa', 'no_serie', 'ano_adquisicion', 'ano_auto', 'km_recorridos', 'velocidad_max', 'tipo_motor'], 'required'],
            [['ano_adquisicion', 'ano_auto'], 'safe'],
            [['km_recorridos', 'velocidad_max', 'km_litro', 'estatus', 'conductor_id', 'dispositivo_id', 'poliza_id', 'direccion_id', 'departamento_id'], 'integer'],
            [['modelo_auto', 'marca_auto'], 'string', 'max' => 60],
            [['placa'], 'string', 'max' => 10],
            [['no_serie'], 'string', 'max' => 17],
            [['color_auto', 'tipo_motor', 'estado_llantas', 'estado_vehiculo', 'estado_motor'], 'string', 'max' => 45],
            [['conductor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Conductores::class, 'targetAttribute' => ['conductor_id' => 'id']],
            [['dispositivo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dispositivos::class, 'targetAttribute' => ['dispositivo_id' => 'id']],
            [['poliza_id'], 'exist', 'skipOnError' => true, 'targetClass' => PolizaSeguro::class, 'targetAttribute' => ['poliza_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modelo_auto' => 'Modelo Auto',
            'marca_auto' => 'Marca Auto',
            'placa' => 'Placa',
            'no_serie' => 'No Serie',
            'ano_adquisicion' => 'Ano Adquisicion',
            'ano_auto' => 'Ano Auto',
            'km_recorridos' => 'Km Recorridos',
            'velocidad_max' => 'Velocidad Max',
            'km_litro' => 'Km Litro',
            'color_auto' => 'Color Auto',
            'tipo_motor' => 'Tipo Motor',
            'estado_llantas' => 'Estado Llantas',
            'estado_vehiculo' => 'Estado Vehiculo',
            'estado_motor' => 'Estado Motor',
            'estatus' => 'Estatus',
            'conductor_id' => 'Conductor ID',
            'dispositivo_id' => 'Dispositivo ID',
            'poliza_id' => 'Poliza ID',
            'direccion_id' => 'Direccion ID',
            'departamento_id' => 'Departamento ID',
        ];
    }

    /**
     * Gets query for [[Conductor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConductor()
    {
        return $this->hasOne(Conductores::class, ['id' => 'conductor_id']);
    }

    /**
     * Gets query for [[Dispositivo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDispositivo()
    {
        return $this->hasOne(Dispositivos::class, ['id' => 'dispositivo_id']);
    }

    /**
     * Gets query for [[Poliza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPoliza()
    {
        return $this->hasOne(PolizaSeguro::class, ['id' => 'poliza_id']);
    }

}
