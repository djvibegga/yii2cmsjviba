<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\modules\articles\models\ArticleCategory;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Article Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Article Category'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'depth',
            [
                'attribute' => 'status',
                'label' => Yii::t('app', 'Status'),
                'value' => function($model) {
                    $statuses = ArticleCategory::getAvailableStatuses();
                    return empty($statuses[$model->status]) ? Yii::t('app', 'Undefined') : $statuses[$model->status];
                }
            ],
            'created_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => function($model) {
                        return $model->depth != 0;
                    },
                    'update' => function($model) {
                        return $model->depth != 0;
                    },
                    'view' => function($model) {
                        return $model->depth != 0;
                    },
                ]
            ],
        ],
    ]); ?>
</div>
