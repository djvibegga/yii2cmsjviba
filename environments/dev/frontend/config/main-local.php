<?php

$config = [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'Cabaiv3eCoo8sho9ohveePh0'
        ],
        'user' => [
            'identityCookie' => [
                'name' => '_identity',
                'domain' => '.yii2cms.loc'
            ],
        ],
        'session' => [
            'name' => 'advanced-ui',
            'cookieParams' => [
                'domain' => '.yii2cms.loc'
            ]
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;dbname=yii2cms;port=5432',
            'username' => 'postgres',
            'password' => ''
        ]
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
