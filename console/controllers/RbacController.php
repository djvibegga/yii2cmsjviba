<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;
use common\rbac\UserRoleRule;
use backend\modules\articles\components\ArticleManager;
use backend\components\ProfileManager;
use backend\modules\pages\components\PageManager;
use backend\modules\articles\components\CategoryManager;

class RbacController extends Controller
{
    /**
     * Install rbac configuration into database
     */
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;
        
        $userRoleRule = new UserRoleRule();
        $userRoleRule->name = 'userRole';
        $authManager->add($userRoleRule);
        
        // Create roles
        $admin = $authManager->createRole(User::ROLE_ADMIN_NAME);
        $admin->ruleName = $userRoleRule->name;
        $authManager->add($admin);
        
        $user = $authManager->createRole(User::ROLE_USER_NAME);
        $user->ruleName = $userRoleRule->name;
        $authManager->add($user);
        
        // Create simple, based on action{$NAME} permissions
        $articleCreate = $authManager->createPermission(ArticleManager::PERM_CREATE);
        $authManager->add($articleCreate);
        $articleUpdate = $authManager->createPermission(ArticleManager::PERM_UPDATE);
        $authManager->add($articleUpdate);
        $articleDelete = $authManager->createPermission(ArticleManager::PERM_DELETE);
        $authManager->add($articleDelete);
        $articleList = $authManager->createPermission(ArticleManager::PERM_LIST);
        $authManager->add($articleList);
        $articleView = $authManager->createPermission(ArticleManager::PERM_VIEW);
        $authManager->add($articleView);
        
        $pageCreate = $authManager->createPermission(PageManager::PERM_CREATE);
        $authManager->add($pageCreate);
        $pageUpdate = $authManager->createPermission(PageManager::PERM_UPDATE);
        $authManager->add($pageUpdate);
        $pageDelete = $authManager->createPermission(PageManager::PERM_DELETE);
        $authManager->add($pageDelete);
        $pageList = $authManager->createPermission(PageManager::PERM_LIST);
        $authManager->add($pageList);
        $pageView = $authManager->createPermission(PageManager::PERM_VIEW);
        $authManager->add($pageView);
        
        $categoryCreate = $authManager->createPermission(CategoryManager::PERM_CREATE);
        $authManager->add($categoryCreate);
        $categoryUpdate = $authManager->createPermission(CategoryManager::PERM_UPDATE);
        $authManager->add($categoryUpdate);
        $categoryDelete = $authManager->createPermission(CategoryManager::PERM_DELETE);
        $authManager->add($categoryDelete);
        $categoryList = $authManager->createPermission(CategoryManager::PERM_LIST);
        $authManager->add($categoryList);
        $categoryView = $authManager->createPermission(CategoryManager::PERM_VIEW);
        $authManager->add($categoryView);
        
        $userCreate = $authManager->createPermission(ProfileManager::PERM_CREATE);
        $authManager->add($userCreate);
        $userUpdate = $authManager->createPermission(ProfileManager::PERM_UPDATE);
        $authManager->add($userUpdate);
        $userDelete = $authManager->createPermission(ProfileManager::PERM_DELETE);
        $authManager->add($userDelete);
        $userList = $authManager->createPermission(ProfileManager::PERM_LIST);
        $authManager->add($userList);
        $userView = $authManager->createPermission(ProfileManager::PERM_VIEW);
        $authManager->add($userView);
        
        // Add permission-per-role in Yii::$app->authManager
        $authManager->addChild($admin, $articleCreate);
        $authManager->addChild($admin, $articleUpdate);
        $authManager->addChild($admin, $articleDelete);
        $authManager->addChild($admin, $articleList);
        $authManager->addChild($admin, $articleView);
        $authManager->addChild($user, $articleView);
        
        $authManager->addChild($admin, $categoryCreate);
        $authManager->addChild($admin, $categoryUpdate);
        $authManager->addChild($admin, $categoryDelete);
        $authManager->addChild($admin, $categoryList);
        $authManager->addChild($admin, $categoryView);
        $authManager->addChild($user, $categoryView);
        
        $authManager->addChild($admin, $pageCreate);
        $authManager->addChild($admin, $pageUpdate);
        $authManager->addChild($admin, $pageDelete);
        $authManager->addChild($admin, $pageList);
        $authManager->addChild($admin, $pageView);
        $authManager->addChild($user, $pageView);
        
        $authManager->addChild($admin, $userCreate);
        $authManager->addChild($admin, $userUpdate);
        $authManager->addChild($admin, $userDelete);
        $authManager->addChild($admin, $userList);
        $authManager->addChild($admin, $userView);
        $authManager->addChild($user, $userView);
    }
}