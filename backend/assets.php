<?php

/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/web');
Yii::setAlias('@web', '/');
$devBundles = require __DIR__ . '/config/assets-dev.php';
foreach (['app'] as $name) {
    unset($devBundles[$name]);
}

return [
    // Adjust command/callback for JavaScript files compressing:
//     'jsCompressor' => 'java -jar compiler.jar --strict_mode_input false --angular_pass true --compilation_level WHITESPACE_ONLY --js {from} --js_output_file {to}',
    'jsCompressor' => 'java -jar compiler.jar --strict_mode_input false --js {from} --js_output_file {to}',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'java -Xss52m -jar yuicompressor.jar --type css {from} -o {to}',
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    // Asset bundle for compression output:
    'bundles' => [
        'yii\\web\\JqueryAsset',
        'yii\\web\\YiiAsset',
        'yii\\validators\\ValidationAsset',
        'yii\\captcha\\CaptchaAsset',
        'yii\\widgets\\ActiveFormAsset',
        'yii\\bootstrap\\BootstrapPluginAsset',
        'yii\\bootstrap\\BootstrapAsset',
        'backend\\assets\\AppAsset'
    ],
    'targets' => [
        'app' => [
            'class' => 'yii\web\AssetBundle',
//             'sourcePath' => '@backend/assets',
            'basePath' => '@backend/web',
            'baseUrl' => '@web',
            'js' => 'js/pack-app-{hash}.js',
            'css' => 'css/pack-app-{hash}.css',
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
    ],
    // Asset manager configuration:
    'assetManager' => [
        'basePath' => '@backend/web/assets',
        'baseUrl' => '@web/assets',
        'bundles' => $devBundles
    ],
];