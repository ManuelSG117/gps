<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "geocerca".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $coordinates
 * @property string|null $created_at
 */
class Geocerca extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'geocerca';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['name', 'coordinates'], 'required'],
            [['description', 'coordinates'], 'string'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'coordinates' => 'Coordinates',
            'created_at' => 'Created At',
        ];
    }

}
