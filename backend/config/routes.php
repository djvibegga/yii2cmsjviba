<?php

return [
    [
        'class' => 'backend\modules\articles\components\ArticleUrlRule',
        'pattern' => '<category:[\w-]+>/<name:[\w-]+>',
        'route' => 'articles/article/view',
    ]
];