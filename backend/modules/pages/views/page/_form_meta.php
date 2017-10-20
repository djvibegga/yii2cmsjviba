<?php

/* @var $this  yii\web\View */
/* @var $form  yii\widgets\ActiveForm */
/* @var $model backend\modules\pages\models\Page */
/* @var $meta  MetaForm */
/* @var $lang  array */
?>

<div class="page-meta-form">

    <?= $form->field($meta, '[' . $lang['name'] . ']title')->textInput() ?>
    
    <?= $form->field($meta, '[' . $lang['name'] . ']description')->textInput() ?>
    
    <?= $form->field($meta, '[' . $lang['name'] . ']keywords')->textInput() ?>

</div>
