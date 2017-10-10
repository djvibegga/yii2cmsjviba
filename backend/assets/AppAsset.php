<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@backend/assets/AppAsset';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/mvc.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
