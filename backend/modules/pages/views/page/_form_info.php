<?php

use comradepashka\tinymce\TinyMce;

/* @var $this  yii\web\View */
/* @var $form  yii\widgets\ActiveForm */
/* @var $model backend\modules\pages\models\Page */
/* @var $info  backend\modules\pages\models\PageInfo */
/* @var $lang  array */
?>

<div class="page-info-form">

    <?= $form->field($info, '[' . $lang['name'] . ']title')->textInput() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']url')->textInput() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']teaser')->textArea() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']text')->widget(\moonland\tinymce\TinyMCE::className(), [
    ]) ?>

</div>
