<?php
/**
 * @var \yii\db\ActiveRecord $model
 * @var \common\components\photoField\Widget $widget
 *
 */

use yii\helpers\Html;

$value = !empty($model->{$widget->attribute}) && $model->{$widget->attribute} != '{}'
    ? base64_encode($model->{$widget->attribute}) : '';
?>

<div class="cropper-widget">
    <?= Html::activeHiddenInput(
        $model,
        $widget->attribute,
        [
            'class' => 'photo-field',
            'value' => $value
        ]
    ); ?>
    <?= Html::hiddenInput('width', $widget->width, ['class' => 'width-input']); ?>
    <?= Html::hiddenInput('height', $widget->height, ['class' => 'height-input']); ?>
    <?= Html::img(
        empty($model->{$widget->attribute}) || $model->{$widget->attribute} == '{}'
            ? $widget->noPhotoImage
            : $widget->photoManager->getPhotoUrl($model, $widget->attribute),
        [
            'style' => 'max-height: ' . $widget->thumbnailHeight . 'px; max-width: ' . $widget->thumbnailWidth . 'px',
            'class' => 'thumbnail',
            'data-no-photo' => $widget->noPhotoImage
        ]
    ); ?>

    <div class="cropper-buttons">
        <button type="button" class="btn btn-sm btn-danger delete-photo" aria-label="<?= Yii::t('cropper', 'DELETE_PHOTO');?>">
            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> <?= Yii::t('cropper', 'DELETE_PHOTO');?>
        </button>
        <button type="button" class="btn btn-sm btn-success crop-photo hidden" aria-label="<?= Yii::t('cropper', 'CROP_PHOTO');?>">
            <span class="glyphicon glyphicon-scissors" aria-hidden="true"></span> <?= Yii::t('cropper', 'CROP_PHOTO');?>
        </button>
        <button type="button" class="btn btn-sm btn-info upload-new-photo hidden" aria-label="<?= Yii::t('cropper', 'UPLOAD_ANOTHER_PHOTO');?>">
            <span class="glyphicon glyphicon-picture" aria-hidden="true"></span> <?= Yii::t('cropper', 'UPLOAD_ANOTHER_PHOTO');?>
        </button>
    </div>

    <div class="new-photo-area" style="height: <?= $widget->cropAreaHeight; ?>px; width: <?= $widget->cropAreaWidth; ?>px;">
        <div class="cropper-label">
            <span><?= $widget->label;?></span>
        </div>
    </div>
    <div class="progress hidden" style="width: <?= $widget->cropAreaWidth; ?>px;">
        <div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" style="width: 0%">
            <span class="sr-only"></span>
        </div>
    </div>
</div>