<?php

namespace common\components;

use common\models\ObjectRecord;
use yii\base\Behavior;
use common\models\SeoRecalcQueueItem;
use yii\helpers\VarDumper;

abstract class BaseUrlDependenciesBehavior extends Behavior
{
    /**
     * Entity URLs dependency map. Keys are dependent
     * entities class names, values are set with class
     * names which are affected to dependent entities.
     * @example:
     * <pre>
     * array(
     *    '\backend\modules\articles\models\Article' => array(
     *         '\backend\modules\articles\models\ArticleCategory',
     *    ),
     *    ...
     * ),
     * </pre>
     * @var array
     */
    public $entityDependenciesMap = [];
    
    /**
     * Resolves dependent entity class list by given affected entity
     * @param string|object $affectedEntity entity which SEF url was affected
     * @return array
     */
    public function resolveDependentEntities($affectedEntity)
    {
        $affectedClassName = is_object($affectedEntity)
            ? get_class($affectedEntity)
            : $affectedEntity;
        $ret = [];
        foreach ($this->entityDependenciesMap as $dependent => $affected) {
            if (in_array($affectedClassName, $affected)) {
                $ret[] = $dependent;
            }
        }
        return $ret;
    }
    
    /**
     * Schedule dependent class list refresh
     * @param array $dependentClassList dependent entities class list
     * @return array
     */
    public function scheduleRefreshDependentList(ObjectRecord $record, $dependentClassList)
    {
        foreach ($dependentClassList as $className) {
            $query = new \yii\db\Query();
            $query->offset = 0;
            $query->select = ['"t".id'];
            $query->from = ['"' . call_user_func([$className, 'tableName']) . '" "t"'];
            $this->buildDependentEntityQuery($query, $record, $className);
            
            while ($ids = $query->createCommand()->queryColumn()) {
                $queueItem = new SeoRecalcQueueItem();
                $queueItem->setJsonAttributeFromArray('ids', $ids);
                $queueItem->type = $className;
                if (!$queueItem->save()) {
                    Yii::error(
                        'Unable to schedule refresh url dependencies. Record class: ' . $record::className() .
                        '. Attributes: ' . VarDumper::dumpAsString($record->attributes) .
                        '. Queue item attributes: ' . VarDumper::dumpAsString($queueItem->attributes) .
                        '. Queue item errors: ' . VarDumper::dumpAsString($queueItem->errors)
                    );
                    break;
                }
                $query->offset += $query->limit;
            }
        }
    }
    
    /**
     * Builds db query for selection a list of dependent records
     * @param \yii\db\Query $query                the query instance to configure
     * @param ObjectRecord  $sourceRecord         the source record instance
     * @param string        $dependentEntityClass depended entity class name
     * @return void
     */
    public abstract function buildDependentEntityQuery(
        \yii\db\Query $query,
        ObjectRecord $sourceRecord,
        $dependentEntityClass
    );
}