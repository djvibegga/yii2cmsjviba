<?php
/**
 * SqlDataCacheAdapter class file
 * 
 * PHP version 5
 * 
 * @category Packages
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/packages/php/docs
 */
namespace common\components\caching;

use Yii;
/**
 * SqlDataCacheAdapter is the cache adapter class which provides
 * caching sql database sources.
 * 
 * PHP version 5
 * 
 * @category Packages
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/packages/php/docs
 */
class SqlDataCacheAdapter extends BaseDataCacheAdapter
{
    /**
     * Creates an instance of SqlDataCacheAdapter
     * 
     * @return void
     */
    public function __construct()
    {
        
    }
    
    /**
     * Executes pull of data source and put each key/value into cache using
     * set() method.
     * 
     * @param ICacheableDataSource $source      data source provider
     * @param boolean              $displayInfo whether required to output pull process execution info
     * 
     * @return void
     * @throws Exception
     * @see IDataCacheAdapter::pull()
     */
    public function pull(ICacheableDataSource $source, $displayInfo = false)
    {
        parent::pull($source);
        
        Yii::trace('Executing pull of source: ' . get_class($source));
        $tableName = $source->getCacheTableName();
        $query = new \yii\db\Query();
        $query->from = $tableName;
        $source->buildCriteria($query, null);
        $query->offset = 0;
        $query->limit = $source->getFetchBlockSize();
        $connection = $source->getDbConnection();
        $uniqueKeyField = $source->getUniqueKeyField();
        $uniqueKeyField = is_array($uniqueKeyField) ? $uniqueKeyField : array($uniqueKeyField);
        $count = 0;
        while ($data = $query->createCommand($connection)->queryAll()) {
            foreach ($data as $item) {
                $keyValue = $this->resolveKeyValue($source, $item, null);
                foreach ($uniqueKeyField as $key) {
                    unset($item[$key]);
                }
                $this->set($source, $item, $keyValue);
                ++$count;
            }
            $query->offset += $query->limit;
            $command = $query->createCommand($connection)->queryAll();
            if ($displayInfo) {
                echo 'Executed pull records count: ' . $count . "\n";
            }
        }
    }
    
    /**
     * (non-PHPdoc)
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value for storing data in the cache
     * 
     * @return array signle record
     * @see IDataCacheAdapter::get()
     */
    public function get(ICacheableDataSource $source, $keyValue)
    {
        $data = parent::get($source, $keyValue);
        if (!empty($data)) {
            Yii::trace('Data source record served from script memory: ' . get_class($source));
            return $data;
        }
        $cache = $this->resolveCacheComponent($source);
        $cacheId = $source->getCacheId($keyValue);
        if (!$cache || ($data = $cache->get($cacheId)) === false) {
            $query = new \yii\db\Query();
            $query->from = ['"' . $source->getCacheTableName() . '" "t"'];
            $query->select = [];
            $source->buildCriteria($query, $keyValue);
            Yii::trace('Data source record served from database: ' . get_class($source));
            if (!$data = $query->createCommand($source->getDbConnection())->queryOne()) {
                $data = array();
            }
            $this->set($source, $data, $keyValue);
            return $data;
        } else {
            Yii::trace('Data source record served from cache: ' . get_class($source));
            $this->putScriptCache($cacheId, $data);
        }
        return $data;
    }
    
    /**
     * (non-PHPdoc)
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value used for storing data in the cache
     * 
     * @return boolean whether operation completed successfully
     * @see IDataCacheAdapter::upsert()
     */
    public function upsert(ICacheableDataSource $source, $keyValue)
    {
        $cacheId = $source->getCacheId($keyValue);
        $query = new \yii\db\Query();
        $query->select = [];
        $query->from = ['"' . $source->getCacheTableName() . '" "t"'];
        $source->buildCriteria($query, $keyValue);
        if ($data = $query->createCommand($source->getDbConnection())->queryOne()) {
            $this->set($source, $data, $keyValue);
            return true;
        }
        return false;
    }
}