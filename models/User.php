<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    // Virtual attributes
    public $password;
    public $pin;

    /**
     * Roles
     */
    const ROLE_OPERATOR = 'operator';
    const ROLE_SUBFOREMAN = 'subforeman';
    const ROLE_FOREMAN = 'foreman';
    const ROLE_CHIEF = 'chief';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';

    public static function tableName()
    {
        return '{{%user}}';
    }

    /** IdentityInterface **/
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; // tidak pakai token auth
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /** Rules **/
    public function rules()
    {
        return [
            [['username', 'fullname', 'role'], 'required'],
            ['username', 'unique'],
            ['role', 'in', 'range' => array_keys(self::optsRole())],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['require_pin', 'boolean'],
            ['require_pin', 'default', 'value' => false],

            // password opsional di update
            [['password'], 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 6],
            ['password', 'safe'],

            ['pin', 'safe'],
        ];
    }

    /** beforeSave untuk hash password & PIN **/
    public function beforeSave($insert)
    {
        if (!empty($this->password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }

        if (!empty($this->pin)) {
            $this->pin_hash = Yii::$app->security->generatePasswordHash($this->pin);
        }

        if ($insert && empty($this->auth_key)) {
            $this->generateAuthKey();
        }

        return parent::beforeSave($insert);
    }

    /** Password helpers **/
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /** PIN helpers **/
    public function validatePin($pin)
    {
        return !$this->require_pin || Yii::$app->security->validatePassword($pin, $this->pin_hash);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /** Role Helper **/
    public static function optsRole()
    {
        return [
            self::ROLE_OPERATOR   => 'Operator',
            self::ROLE_SUBFOREMAN => 'Sub Foreman',
            self::ROLE_FOREMAN    => 'Foreman',
            self::ROLE_CHIEF      => 'Chief',
            self::ROLE_MANAGER    => 'Manager',
            self::ROLE_ADMIN      => 'Admin',
        ];
    }

    /** Hide hash dalam API **/
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash'], $fields['pin_hash'], $fields['auth_key']);
        return $fields;
    }
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // Password & PIN hanya divalidasi saat create, bukan update
        $scenarios['create'] = ['username', 'fullname', 'role', 'status', 'password', 'pin', 'require_pin'];
        $scenarios['update'] = ['username', 'fullname', 'role', 'status', 'require_pin']; // no password/pin here

        return $scenarios;
    }
}
