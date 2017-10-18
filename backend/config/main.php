<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'language' => 'en',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'class' => 'common\components\User',
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'httpOnly' => true,
            ],
        ],
        'session' => [
            'cookieParams' => [
                'httponly' => true,
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en',
                    'fileMap' => array(
//                         'app' => 'app.php',
//                         'menu' => 'menu.php'
                    )
                ],
            ],
        ],
        'photoManager' => [
            'class' => 'common\components\PhotoManager'
        ],
        'assetManager' => [
            'appendTimestamp' => YII_ENV == YII_ENV_PROD,
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'))
        ],
        'modelFactory' => [
            'class' => 'common\components\ModelFactory',
            'classMap' => [
                
            ]
        ],
        'cacheAdapterFactory' => [
            'class' => 'common\components\caching\CacheAdapterFactory',
            'cacheComponentName' => 'memcache'
        ],
        'memcache' => [
            'class' => '\yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ]
        ],
        'urlManager' => [
            'class' => 'common\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => require __DIR__ . '/routes.php'
        ],
    ],
    'modules' => [
        'articles' => [
            'class' => 'backend\modules\articles\Module',
            'components' => [
                'articleManager' => [
                    'class' => 'backend\modules\articles\components\ArticleManager'
                ],
                'categoryManager' => [
                    'class' => 'backend\modules\articles\components\CategoryManager'
                ]
            ]
        ],
        'pages' => [
            'class' => 'backend\modules\pages\Module',
            'components' => [
                'pageManager' => [
                    'class' => 'backend\modules\pages\components\PageManager'
                ]
            ]
        ]
    ],
    'params' => $params,
];
