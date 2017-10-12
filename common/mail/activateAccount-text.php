<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$activateLink = Yii::$app->urlManager->createAbsoluteUrl(['site/activate-account', 'code' => $user->activation_code]);
?>
Hello <?= $user->username ?>,

Follow the link below to reset your password:

<?= $activateLink ?>
