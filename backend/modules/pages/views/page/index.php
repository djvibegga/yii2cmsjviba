<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\modules\pages\models\Page;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Pages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Page', ['create'], ['class' => 'btn btn-success']) ?>
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
                    $statuses = Page::getAvailableStatuses();
                    return empty($statuses[$model->status]) ? Yii::t('app', 'Undefined') : $statuses[$model->status];
                }
            ],
            'created_at',
            'published_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
