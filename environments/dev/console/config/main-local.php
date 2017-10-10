<?php
return [
    'bootstrap' => ['gii'],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;dbname=yii2cms;port=5432',
            'username' => 'postgres',
            'password' => ''
        ]
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
];
