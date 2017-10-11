<?php

namespace common\components\photoField;

use Yii;
use yii\helpers\Json;

class Widget extends \budyaga\cropper\Widget
{
    /**
     * @var PhotoManager
     */
    public $photoManager;
    
    /**
     * View file to be rendered
     * @var string
     */
    public $viewFile = 'widget';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->photoManager) {
            $this->photoManager = Yii::$app->get('photoManager');
        }
    }
    
    /**
     * @inheritdoc
     */
    public function registerClientAssets()
    {
        $view = $this->getView();
        $assets = CropperAsset::register($view);
        
        if ($this->noPhotoImage == '') {
            $this->noPhotoImage = $assets->baseUrl . '/img/nophoto.png';
        }
        
        $settings = [
            'url' => $this->uploadUrl,
            'name' => $this->uploadParameter,
            'maxSize' => $this->maxSize / 1024,
            'allowedExtensions' => explode(', ', $this->extensions),
            'size_error_text' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
            'ext_error_text' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions]),
            'accept' => 'image/*'
        ];
        
        if ($this->onCompleteJcrop)
            $settings['onCompleteJcrop'] = $this->onCompleteJcrop;
            
        $view->registerJs(
            'jQuery("#' . $this->options['id'] . '").parent().find(".new-photo-area").cropper(' . Json::encode($settings) . ', ' . $this->width . ', ' . $this->height . ');',
            $view::POS_READY
        );
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientAssets();
        
        return $this->render($this->viewFile, [
            'model' => $this->model,
            'widget' => $this
        ]);
    }
}