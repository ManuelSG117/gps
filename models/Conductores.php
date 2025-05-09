<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "conductores".
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido_p
 * @property string|null $apellido_m
 * @property string $no_licencia
 * @property string $estado
 * @property string $municipio
 * @property string|null $colonia
 * @property string|null $calle
 * @property int|null $num_ext
 * @property string|null $num_int
 * @property string|null $cp
 * @property string $telefono
 * @property string|null $email
 * @property string|null $tipo_sangre
 * @property string|null $fecha_nacimiento
 * @property string $nombre_contacto
 * @property string $apellido_p_contacto
 * @property string|null $apellido_m_contacto
 * @property string|null $parentesco
 * @property string $telefono_contacto
 * @property int|null $estatus
 *
 * @property Vehiculos[] $vehiculos
 */
class Conductores extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conductores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apellido_m', 'colonia', 'calle', 'num_ext', 'num_int', 'cp', 'email', 'tipo_sangre', 'fecha_nacimiento', 'apellido_m_contacto', 'parentesco', 'estatus'], 'default', 'value' => null],
            [['nombre', 'apellido_p', 'no_licencia', 'estado', 'municipio', 'telefono', 'nombre_contacto', 'apellido_p_contacto', 'telefono_contacto'], 'required'],
            [['num_ext', 'estatus'], 'integer'],
            [['fecha_nacimiento'], 'safe'],
            [['nombre', 'apellido_p', 'apellido_m', 'estado', 'colonia', 'calle', 'email', 'nombre_contacto', 'apellido_p_contacto', 'apellido_m_contacto', 'parentesco'], 'string', 'max' => 55],
            [['no_licencia', 'municipio', 'tipo_sangre'], 'string', 'max' => 45],
            [['num_int', 'telefono', 'telefono_contacto'], 'string', 'max' => 10],
            [['cp'], 'string', 'max' => 5],
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
            'apellido_p' => 'A. Paterno',
            'apellido_m' => 'A. Materno',
            'no_licencia' => 'No. Licencia',
            'estado' => 'Estado',
            'municipio' => 'Municipio',
            'colonia' => 'Colonia',
            'calle' => 'Calle',
            'num_ext' => 'Num Ext',
            'num_int' => 'Num Int',
            'cp' => 'Cp',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'tipo_sangre' => 'Tipo Sangre',
            'fecha_nacimiento' => 'Fecha Nacimiento',
            'nombre_contacto' => 'Nombre',
            'apellido_p_contacto' => 'A. Paterno',
            'apellido_m_contacto' => 'A. Materno',
            'parentesco' => 'Parentesco',
            'telefono_contacto' => 'Telefono',
            'estatus' => 'Estatus',
        ];
    }

    /**
     * Gets query for [[Vehiculos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiculos()
    {
        return $this->hasMany(Vehiculos::class, ['conductor_id' => 'id']);
    }

}
