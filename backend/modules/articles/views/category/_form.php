<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\articles\models\ArticleCategoryInfo;
use yii\bootstrap\Tabs;
use common\helpers\FormHelper;
use common\models\MetaForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\modules\articles\models\ArticleCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-category-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <h3><?= Yii::t('app', 'Attributes') ?></h3>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_id')->dropDownList($parentItems) ?>

    <?= $form->field($model, 'status')->dropDownList($statuses) ?>
    
    <?= $form->field($model, 'photo')->widget(common\components\photoField\Widget::className(), [
        'id' => 'categoryPhotoUploader',
        'uploadUrl' => Url::to(['/articles/category/upload-photo'])
    ]) ?>
    
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
    
    <h3><?= Yii::t('app', 'Content') ?></h3>
    
    <?= Tabs::widget([
        'items' => $tabItems
    ]) ?>
    
    <?php
        $tabItems = [];
        foreach ($langs as $lang) {
            $metaModel = new MetaForm();
            $metaModel->attributes = isset($model->meta[$lang['name']])
                ? $model->meta[$lang['name']]
                : [];
            $tabItems[] = [
                'label' => $lang['label'],
                'content' => $this->render('_form_meta', [
                    'lang' => $lang,
                    'meta' => $metaModel,
                    'form' => $form,
                    'model' => $model
                ])
            ];
        }
    ?>
    
    <h3><?= Yii::t('app', 'Meta') ?></h3>
    
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
