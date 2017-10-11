<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'object_id',
            'status',
            'role',
            'email:email',
            // 'created_at',
            // 'updated_at',
            // 'username',
            // 'auth_key',
            // 'password_hash',
            // 'password_reset_token',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
