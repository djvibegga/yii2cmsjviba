<?php

use yii\helpers\Html;


/* @var $this     yii\web\View */
/* @var $model    app\models\User */
/* @var $statuses array */
/* @var $roles    array */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'roles' => $roles,
        'statuses' => $statuses
    ]) ?>

</div>
