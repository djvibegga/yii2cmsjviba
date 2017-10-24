<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\articles\models\Article */

Yii::$app->metaData->setDataFromJsonMetaAttribute(
    $model->getTranslatedInfo(Yii::$app->language),
    'meta'
);

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Articles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;

?>
<div class="article-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'article_category_ids',
            'user_id',
            'object_id',
            'name',
            'status',
            'created_at',
            'updated_at',
            'published_at',
            [
                'label' => Yii::t('app', 'Photo'),
                'format' => 'raw',
                'value' => Html::img(Yii::$app->photoManager->getPhotoUrl($model, 'photo', 'small'))
            ]
        ],
    ]) ?>

</div>
