<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\articles\models\ArticleCategoryInfo;
use yii\bootstrap\Tabs;
use common\helpers\FormHelper;

/* @var $this yii\web\View */
/* @var $model backend\modules\articles\models\ArticleCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-category-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_id')->dropDownList($parentItems) ?>

    <?= $form->field($model, 'status')->dropDownList($statuses) ?>
    
    <?php
        $tabItems = [];
        foreach ($langs as $lang) {
            $infoModel = new ArticleCategoryInfo();
            $infoModel->attributes = isset($model->infos[$lang['name']])
                ? $model->infos[$lang['name']] : [];
            if ($infoErrors = FormHelper::getCustomErrors($model, 'infos', $lang['name'])) {
                $infoModel->addErrors($infoErrors);
            }
            $tabItems[] = [
                'label' => $lang['label'],
                'content' => $this->render('_form_info', [
                    'lang' => $lang,
                    'info' => $infoModel,
                    'form' => $form,
                    'model' => $model
                ])
            ];
        }
    ?>
    
    <?= Tabs::widget([
        'items' => $tabItems
    ]) ?>
    
    <br><br><br>
    

    <div class="form-group">
        <?= Html::submitButton(
            $model->scenario == 'insert' 
                ? Yii::t('app', 'Create')
                : Yii::t('app', 'Update'),
            [
                'class' => $model->scenario == 'insert' ? 'btn btn-success' : 'btn btn-primary'
            ]
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
