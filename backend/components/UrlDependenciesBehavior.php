<?php

namespace backend\components;

use common\models\ObjectRecord;
use common\components\BaseUrlDependenciesBehavior;
use backend\modules\articles\models\Article;
use backend\modules\articles\models\ArticleCategory;

class UrlDependenciesBehavior extends BaseUrlDependenciesBehavior
{
    /**
     * Builds db query for selection a list of dependent records
     * @param \yii\db\Query $query                the query instance to configure
     * @param ObjectRecord  $sourceRecord         the source record instance
     * @param string        $dependentEntityClass depended entity class name
     * @return void
     */
    public function buildDependentEntityQuery(
        \yii\db\Query $query,
        ObjectRecord $sourceRecord,
        $dependentEntityClass
    )
    {
        if ($dependentEntityClass == Article::className()) {
            if ($sourceRecord instanceof ArticleCategory) {
                $query->limit = 2;
                $query->andWhere(
                    ':categoryId = ANY(article_category_ids)',
                    [':categoryId' => $sourceRecord->id]
                );
            }
        }
    }
}