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
 * @property int|null $activo
 * @property string|null $correo_electronico
 * @property string|null $token_acceso
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
            [['activo', 'correo_electronico', 'token_acceso'], 'default', 'value' => null],
            [['username', 'names', 'password'], 'required'],
            [['activo'], 'integer'],
            [['username', 'names', 'password', 'correo_electronico', 'token_acceso'], 'string', 'max' => 255],
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
            'names' => 'Nombre',
            'password' => 'Contraseña',
            'activo' => 'Estatus',
            'correo_electronico' => 'Correo Electronico',
            'token_acceso' => 'Token Acceso',
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
