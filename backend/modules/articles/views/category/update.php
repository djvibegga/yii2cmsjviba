<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\articles\models\ArticleCategory */

$this->title = Yii::t('app', 'Update Article Category') . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Article Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="article-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'statuses' => $statuses,
        'parentItems' => $parentItems,
        'langs' => $langs
    ]) ?>

</div>
