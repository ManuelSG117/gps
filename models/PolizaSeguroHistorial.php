<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "poliza_seguro_historial".
 *
 * @property int $id
 * @property int $poliza_id
 * @property int|null $estado_anterior
 * @property int $estado_nuevo
 * @property string $fecha_cambio
 * @property string|null $comentario
 * @property int|null $usuario_id
 * @property string|null $motivo
 *
 * @property PolizaSeguro $poliza
 */
class PolizaSeguroHistorial extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poliza_seguro_historial';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['estado_anterior', 'comentario', 'usuario_id', 'motivo'], 'default', 'value' => null],
            [['poliza_id', 'estado_nuevo', 'fecha_cambio'], 'required'],
            [['poliza_id', 'estado_anterior', 'estado_nuevo', 'usuario_id'], 'integer'],
            [['fecha_cambio'], 'safe'],
            [['comentario'], 'string'],
            [['motivo'], 'string', 'max' => 255],
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
            'poliza_id' => 'Poliza ID',
            'estado_anterior' => 'Estado Anterior',
            'estado_nuevo' => 'Estado Nuevo',
            'fecha_cambio' => 'Fecha Cambio',
            'comentario' => 'Comentario',
            'usuario_id' => 'Usuario ID',
            'motivo' => 'Motivo',
        ];
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
