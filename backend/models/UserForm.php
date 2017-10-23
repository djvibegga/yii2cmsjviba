<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\User;

class UserForm extends Model
{
    public $id;
    public $username;
    public $email;
    public $role;
    public $status;
    public $password;
    public $confirmPassword;
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::rules()
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            [['username', 'email', 'password', 'confirmPassword'], 'required'],
            ['username', 'string', 'max' => 255],
            [
                'email', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('app', 'This email has already been taken.')
            ],
            ['role', 'in', 'range' => array_keys(User::getAvailableRoles())],
            ['status', 'in', 'range' => array_keys(User::getAvailableStatuses())],
            ['password', 'string', 'min' => 6],
            [
                'confirmPassword', 'compare',
                'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Password is confirmed incorrectly.')
            ]
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [
            'insert' => ['username', 'email', 'status', 'password', 'confirmPassword', 'role'],
            'update' => ['username', 'email', 'status', 'password', 'confirmPassword', 'role']
        ];
    }
}