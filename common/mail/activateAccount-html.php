<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$activateLink = Yii::$app->urlManager->createAbsoluteUrl(['site/activate-account', 'code' => $user->activation_code]);
?>
<div class="activate-account">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Follow the link below to activate your account:</p>

    <p><?= Html::a(Html::encode($activateLink), $activateLink) ?></p>
</div>
