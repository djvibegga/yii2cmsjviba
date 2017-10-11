<?php

namespace common\components;

use Yii;
use common\models\User as UserModel;
use yii\helpers\Url;

class User extends \yii\web\User
{
    /**
     * @var array
     */
    public $adminPanelUrl = ['/user/index'];
    
    /**
     * {@inheritDoc}
     * @see \yii\web\User::getReturnUrl()
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $url = Yii::$app->getSession()->get($this->returnUrlParam, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->getUrlManager()->createUrl($url);
            } else {
                $url = null;
            }
        }
     
        
        if ($this->getIdentity()->role == UserModel::ROLE_ADMIN) {
            $homeUrl = Url::to($this->adminPanelUrl);
        } else {
            $homeUrl = Yii::$app->getHomeUrl();
        }
        
        return in_array($url, [null, '/index.php']) ? $homeUrl : $url;
    }
}