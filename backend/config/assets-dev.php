<?php

return [
    'app' => [
        'class' => 'yii\web\AssetBundle',
        'sourcePath' => '@backend/assets',
        'js' => [],
        'css' => [],
        'depends' => [
            'yii\\web\\JqueryAsset',
            'yii\\web\\YiiAsset',
            'yii\\validators\\ValidationAsset',
            'yii\\captcha\\CaptchaAsset',
            'yii\\widgets\\ActiveFormAsset',
            'yii\\bootstrap\\BootstrapPluginAsset',
            'yii\\bootstrap\\BootstrapAsset',
            'backend\\assets\\AppAsset'
        ]
    ]
];