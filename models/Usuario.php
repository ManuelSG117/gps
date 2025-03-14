<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuario".
 *
 * @property int $id
 * @property string $username
 * @property string $names
 * @property string $password
 */
class Usuario extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'names', 'password'], 'required'],
            [['username', 'names', 'password'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'names' => 'Names',
            'password' => 'Password',
        ];
    }
    public function getAuthKey() {
        return null;
       //return $this->auth_key;
    }    
    
    // interface
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() == $authKey;
    }
    
    // interface
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new \yii\base\NotSupportedException("'findIdentityByAccessToken' is not implemented");
    }
    
    /* Identity Interface */
    public function getId(){
        return $this->id;
    }
        
    public static function findIdentity($id) {
        return static::findOne(['id' => $id]);
    }
    
   // Utilizamos el mismo nombre de método que definimos como el nombre de usuario
    public static function findByUserName($username) {
        return static::findOne(['username' => $username]);
    }
    
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
