<?php

return [
    'timezone' => 'Europe/Moscow',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;dbname=yii2cms;port=5432',
            'username' => 'postgres',
            'password' => ''
        ]
    ],
    'params' => [
    ]
];