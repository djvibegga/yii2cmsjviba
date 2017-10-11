<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\modules\articles\models\Article;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Articles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Article', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'user_id',
                'label' => Yii::t('app', 'Author'),
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, ['/user/view', 'id' => $model->user_id]);
                }
            ],
            'name',
            [
                'attribute' => 'status',
                'label' => Yii::t('app', 'Status'),
                'value' => function($model) {
                    $statuses = Article::getAvailableStatuses();
                    return empty($statuses[$model->status]) ? Yii::t('app', 'Undefined') : $statuses[$model->status];
                }
            ],
            'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
