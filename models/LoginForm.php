<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm handles user login.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    /** @var User|null */
    private $_user = null;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username'   => 'Username',
            'password'   => 'Password',
            'rememberMe' => 'Ingat saya',
        ];
    }

    public function validatePassword($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Username atau password salah.');
        }
    }

    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        $duration = $this->rememberMe ? 3600 * 24 * 30 : 0;
        return Yii::$app->user->login($this->getUser(), $duration);
    }

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findOne([
                'username' => $this->username,
                'status'   => User::STATUS_ACTIVE,
            ]);
        }
        return $this->_user;
    }
}
