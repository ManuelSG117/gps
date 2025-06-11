<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class User extends \yii\db\ActiveRecord 
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_reset_token'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 10],
            [['username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
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
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    //Este lo pide pero lo dejamos como null por que no lo usamos por el momento
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
