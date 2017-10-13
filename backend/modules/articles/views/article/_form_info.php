<?php

use comradepashka\tinymce\TinyMce;

/* @var $this  yii\web\View */
/* @var $form  yii\widgets\ActiveForm */
/* @var $model backend\modules\articles\models\Article */
/* @var $info  backend\modules\articles\models\ArticleInfo */
/* @var $lang  array */
?>

<div class="article-info-form">

    <?= $form->field($info, '[' . $lang['name'] . ']title')->textInput() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']teaser')->textArea() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']text')->widget(\moonland\tinymce\TinyMCE::className(), [
    ]) ?>

</div>
