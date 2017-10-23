<?php

namespace common\rbac;

use yii\rbac\Rule;
use common\models\User;

class UserRoleRule extends Rule
{
    /**
     * @var string
     */
    public $name = 'userRole';
    
    /**
     * {@inheritDoc}
     * @see \yii\rbac\Rule::execute()
     */
    public function execute($user, $item, $params)
    {
        if (\Yii::$app->user->isGuest) {
            return false;
        }
        if (!$userModel = User::findOneByIdUsingCache($user)) {
            return false;
        }
        $role = $userModel->role;
        if ($item->name === User::ROLE_ADMIN_NAME) {
            return $role == User::ROLE_ADMIN;
        } else if ($item->name === User::ROLE_USER_NAME) {
            return $role == User::ROLE_USER;
        }
        return false;
    }
}