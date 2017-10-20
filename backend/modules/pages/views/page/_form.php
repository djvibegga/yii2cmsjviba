<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\base\Widget;
use yii\bootstrap\Tabs;
use backend\modules\pages\models\PageInfo;
use common\helpers\FormHelper;
use common\models\MetaForm;

/* @var $this yii\web\View */
/* @var $model app\modules\pages\models\PageForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $langs array */
?>

<div class="article-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <h3><?= Yii::t('app', 'Attributes') ?></h3>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList($statuses) ?>

    <?php
        $tabItems = [];
        foreach ($langs as $lang) {
            $infoModel = new PageInfo();
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
            $model->getScenario() == 'insert' ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            [
                'class' => $model->getScenario() == 'insert' ? 'btn btn-success' : 'btn btn-primary'
            ]
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
