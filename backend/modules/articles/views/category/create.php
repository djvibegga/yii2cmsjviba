<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\modules\articles\models\ArticleCategory */

$this->title = Yii::t('app', 'Create Article Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Article Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'statuses' => $statuses,
        'parentItems' => $parentItems,
        'langs' => $langs
    ]) ?>

</div>
