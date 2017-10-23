<?php

namespace backend\components;

use common\components\Component;
use backend\models\UserForm;
use common\models\User;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;

class ProfileManager extends Component
{
    const PERM_CREATE = 'userCreate';
    const PERM_UPDATE = 'userUpdate';
    const PERM_DELETE = 'userDelete';
    const PERM_VIEW = 'userView';
    const PERM_LIST = 'userList';
    
    /**
     * Returns built data provider to fetch list of users
     * @param array $params request parameters
     * @return @return \yii\data\ActiveDataProvider
     */
    public function getDataProvider(array $params = [])
    {
        return new ActiveDataProvider([
            'query' => User::find(),
        ]);
    }
    
    /**
     * Loads user by id
     * @param int $userId the user id
     * @throws \InvalidArgumentException
     * @return \common\models\User loaded user
     */
    public function loadUserById($userId)
    {
        try {
            $userId = self::toPositiveInt($userId);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('User id is invalid.');
        }
        try {
            return User::findOne($userId);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to find the user because of db error: ' . $e->getMessage());
        }
    }
    
    /**
     * Loads user data form by user id
     * @param UserForm $model  the user form
     * @param int      $userId the user id
     * @return void
     * @throws \InvalidArgumentException if the user id is invalid
     * @throws InvalidParamException     if the user has not found
     */
    public function loadUserFormById(UserForm $model, $userId)
    {
        if (! $user = $this->loadUserById($userId)) {
            throw new InvalidParamException('User has not found.');
        }
        $model->setAttributes($user->attributes);
        $model->id = $userId;
    }
    
    /**
     * Deletes user by id
     * @param int $userId the user id
     * @return bool whether operation has successfully completed
     */
    public function deleteUserById($userId)
    {
        if (! $user = $this->loadUserById($userId)) {
            throw new InvalidParamException('User has not found.');
        }
        try {
            return $user->delete();
        } catch (\yii\db\Exception $e) {
            Yii::error(
                'Unable to delete user. ID: ' . $userId .
                '. Cause: ' . $e->getMessage()
            );
        }
        return false;
    }
    
    /**
     * Creates new user
     * @param UserForm $model the user data form
     * @return User|array created user record on success, otherwise
     * returns list of errors
     */
    public function createUser(UserForm $model)
    {
        if ($model->hasErrors()) {
            return $model->getErrors();
        }
        
        try {
            $user = new User();
            $user->attributes = $model->attributes;
            $user->setPassword($model->password);
            $user->generateActivationCode();
            $user->generatePasswordResetToken();
            $user->generateAuthKey();
            if (! $user->save()) {
                return $user->getErrors();
            }
            $user->refresh();
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to create a user because of db error: ' . $e->getMessage());
            return [
                'email' => Yii::t('app', 'Unable to create a user because of database error.')
            ];
        }
        
        return $user;
    }
    
    /**
     * Updates the existing user
     * @param int      $userId the user id
     * @param UserForm $model  the user data form
     * @return User|array the updated record on success,
     * otherwise list of errors
     * @throws \InvalidParamException if the user id is invalid
     * @throws InvalidParamException  if the user has not found
     */
    public function updateUserById($userId, UserForm $model)
    {
        if ($model->hasErrors()) {
            return $model->getErrors();
        }
        
        $user = $this->loadUserById($userId);
        if (! $user) {
            throw new InvalidParamException('User has not found.');
        }
        
        try {
            $user->attributes = $model->attributes;
            if (! $user->save()) {
                return $user->getErrors();
            }
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to update the user. ID: ' . $userId . '. Cause db error: ' . $e->getMessage());
            return [
                'email' => Yii::t('app', 'Unable to update the user because of database error.')
            ];
        }
        
        return $user;
    }
}