<?php

use comradepashka\tinymce\TinyMce;

/* @var $this  yii\web\View */
/* @var $form  yii\widgets\ActiveForm */
/* @var $model backend\modules\articles\models\Article */
/* @var $info  backend\modules\articles\models\ArticleInfo */
/* @var $lang  array */
?>

<div class="article-info-form">

    <?= $form->field($info, 'title[' . $lang['name'] . ']')->textInput() ?>
    
    <?= $form->field($info, 'teaser[' . $lang['name'] . ']')->textArea() ?>
    
    <?= $form->field($info, 'text[' . $lang['name'] . ']')->widget(\moonland\tinymce\TinyMCE::className(), [
    ]) ?>

</div>
