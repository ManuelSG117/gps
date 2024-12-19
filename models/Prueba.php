<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prueba".
 *
 * @property int $id
 * @property string|null $prueba
 * @property string|null $pjax
 */
class Prueba extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prueba';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['prueba', 'pjax'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prueba' => 'Prueba',
            'pjax' => 'Pjax',
        ];
    }
}
