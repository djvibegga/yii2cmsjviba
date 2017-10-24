<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\pages\models\Page */

Yii::$app->metaData->setDataFromJsonMetaAttribute(
    $model->getTranslatedInfo(Yii::$app->language),
    'meta'
);

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="page-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'object_id',
            'name',
            'status',
            'created_at',
            'updated_at',
            'published_at',
        ],
    ]) ?>

</div>
