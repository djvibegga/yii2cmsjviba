<?php

/* @var $this  yii\web\View */
/* @var $form  yii\widgets\ActiveForm */
/* @var $model backend\modules\articles\models\ArticleCategory */
/* @var $info  backend\modules\articles\models\ArticleCategoryInfo */
/* @var $lang  array */
?>

<div class="category-info-form">

    <?= $form->field($info, '[' . $lang['name'] . ']url')->textInput() ?>
    
    <?= $form->field($info, '[' . $lang['name'] . ']description')->textInput() ?>

</div>
