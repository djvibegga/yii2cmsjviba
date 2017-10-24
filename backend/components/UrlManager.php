<?php

namespace backend\components;

class UrlManager extends \common\components\UrlManager
{
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'urlDependencies' => [
                'class' => UrlDependenciesBehavior::className(),
                'entityDependenciesMap' => [
                    'backend\modules\articles\models\Article' => [
                        'backend\modules\articles\models\ArticleCategory',
                    ],
                ]
            ]
        ];
    }
}