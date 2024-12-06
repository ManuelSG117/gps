<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "conductores".
 *
 * @property int $id
 * @property string|null $nombres
 * @property string|null $apellido_p
 * @property string|null $apellido_m
 * @property string|null $no_licencia
 * @property string|null $estado
 * @property string|null $municipio
 * @property string|null $colonia
 * @property string|null $calle
 * @property int|null $num_ext
 * @property string|null $num_int
 * @property int|null $cp
 * @property int|null $telefono
 * @property string|null $email
 * @property string|null $tipo_sangre
 * @property string|null $fecha_nacimiento
 * @property string|null $nombres_contacto
 * @property string|null $apellido_p_contacto
 * @property string|null $apellido_m_contacto
 * @property string|null $parentesco
 * @property string|null $telefono_contacto
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
            [['num_ext', 'cp', 'telefono'], 'integer'],
            [['fecha_nacimiento'], 'safe'],
            [['nombres', 'apellido_p', 'apellido_m', 'estado', 'colonia', 'calle', 'email', 'nombres_contacto', 'apellido_p_contacto', 'apellido_m_contacto', 'parentesco', 'telefono_contacto'], 'string', 'max' => 55],
            [['no_licencia', 'municipio', 'tipo_sangre'], 'string', 'max' => 45],
            [['num_int'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombres' => 'Nombres',
            'apellido_p' => 'Apellido P',
            'apellido_m' => 'Apellido M',
            'no_licencia' => 'No Licencia',
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
            'nombres_contacto' => 'Nombres Contacto',
            'apellido_p_contacto' => 'Apellido P Contacto',
            'apellido_m_contacto' => 'Apellido M Contacto',
            'parentesco' => 'Parentesco',
            'telefono_contacto' => 'Telefono Contacto',
        ];
    }
}
