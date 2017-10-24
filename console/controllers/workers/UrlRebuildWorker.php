<?php

namespace console\controllers\workers;

use Yii;
use common\models\SeoRecalcQueueItem;
use common\components\UrlManager;
use yii\helpers\Console;

class UrlRebuildWorker extends \inpassor\daemon\Worker
{
    public $active = true;
    public $maxProcesses = 10;
    public $delay = 1;
    public $processingLimit = 20;
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $this->logFile = Yii::getAlias('@console') . '/runtime/logs/url-rebuild-worker.log';
    }
    
    /**
     * @return UrlManager
     */
    protected function getUrlManager()
    {
        return Yii::$app->urlManager;
    }

    /**
     * {@inheritDoc}
     * @see \inpassor\daemon\Worker::run()
     */
    public function run()
    {
        $pid = getmypid();
        $connection = Yii::$app->getDb();
        $dateTimeZone = Yii::$app->timezone;
        $sql = "SET SESSION timezone TO '" . $dateTimeZone . "'";
        $connection->createCommand($sql)->execute();
        
        $transaction = $connection->beginTransaction();
        $tableName = SeoRecalcQueueItem::tableName();
        $sql = '
            SELECT "id", "type", "ids" FROM ' . $tableName .
            ' WHERE 
                "b_processed" = false
                ORDER BY "created_at" ASC
                LIMIT ' . $this->processingLimit . ' FOR UPDATE';
        
        $items = $connection->createCommand($sql)->queryAll();
        $idsToUpdate = [];
        foreach ($items as $item) {
            $idsToUpdate[] = $item['id'];
        }
        
        if (!empty($idsToUpdate)) {
            $sql = 'UPDATE ' . $tableName . '
                SET
                    "b_processed" = true
                WHERE
                    "id" IN (' . implode(',', $idsToUpdate) . ')';
            $connection->createCommand($sql)->execute();
            $this->log('Fetched ' . count($idsToUpdate) . ' items to update urls. PID: ' . $pid);
            $transaction->commit();
        } else {
            $transaction->rollBack();
            $this->log('Fetched an empty list to rebuild urls. PID: ' . $pid);
            return;
        }
        
        $recMap = [];
        foreach ($items as $item) {
            $ids = explode(',', trim($item['ids'], '{}'));
            $query = call_user_func([$item['type'], "find"]);
            $records = $query->where(['id' => $ids])->all();
            if (empty($recMap[$item['type']])) {
                $recMap[$item['type']] = $records;
            } else {
                $recMap[$item['type']] = array_merge(
                    $recMap[$item['type']],
                    $records
                );
            }
        }
        
        $urlManager = $this->getUrlManager();
        foreach ($recMap as $className => $records) {
            foreach ($records as $record) {
                if ($urlManager->buildSefUrl($record, true)) {
                    $this->log(
                        'Url has successfully rebuilded. Class: ' .
                        get_class($record) . ', ID: ' . $record->id .
                        '. PID: ' . $pid
                    );
                } else {
                    $this->log(
                        'Url has not rebuilded. Class: ' . 
                        get_class($record) . ', ID: ' . $record->id .
                        '. PID: ' . $pid
                    );
                }
            }
        }
    }
}