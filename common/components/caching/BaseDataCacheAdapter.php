<?php
/**
 * BaseDataCacheAdapter class
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/packages/php/docs
 */
namespace common\components\caching;

use Yii;
/**
 * BaseDataCacheAdapter is the base class which implements basic functionality
 * of data cache adapter.
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/packages/php/docs
 * @abstract
 */
abstract class BaseDataCacheAdapter extends \common\components\Component implements IDataCacheAdapter
{
    /**
     * Adapter factory
     * @var CacheAdapterFactory
     */
    private $_factory;
    
    /**
     * PHP cache for served data
     * Format: array(dataRecordKeyValueHash => dataRecord)
     * @var array
     */
    private $_dataCache;
    
    /**
     * Resolves key value depends on given data source, record.
     * 
     * @param ICacheableDataSource $source   data source
     * @param array                $record   data record
     * @param mixed                $keyValue unique key value
     * 
     * @return mixed unique key value
     */
    protected function resolveKeyValue(ICacheableDataSource $source, $record, $keyValue)
    {
        if ($keyValue === null) {
            $keyField = $source->getUniqueKeyField();
            $keys = is_array($keyField) ? $keyField : array($keyField);
            $keyValue = array();
            foreach ($keys as $key) {
                $keyValue[$key] = isset($record[$key]) ? $record[$key] : null;
            }
            if (!is_array($keyField)) {
                $keyValue = $keyValue[$keyField];
            }
        }
        return $keyValue;
    }
    
    /**
     * Resolves cache component instance which will be used
     * @param ICacheableDataSource $source data source provider
     * @return \yii\caching\Cache cache instance
     * @throws CException
     */
    protected function resolveCacheComponent(ICacheableDataSource $source)
    {
        return $source->getCacheComponent();
    }
    
    /**
     * Saves data into cache with cache ID which depends on unique key value ($keyValue).
     * @param ICacheableDataSource $source   data source provider
     * @param array                $record   data record
     * @param mixed                $keyValue key value to store in cache (optional)
     * @return boolean whether operation success
     * @see IDataCacheAdapter::set()
     */
    protected function set(ICacheableDataSource $source, &$record, $keyValue = null)
    {
        if (!$cache = $this->resolveCacheComponent($source)) {
            return false;
        }
        $keyValue = $this->resolveKeyValue($source, $record, $keyValue);
        $cacheId = $source->getCacheId($keyValue);
        $expire = $source->getCacheLifeTime();
        Yii::trace('Data source record pushed into cache: ' . get_class($source));
        $this->_dataCache[$cacheId] = $record;
        return $cache->set($cacheId, $record, $expire);
    }
    
    /**
     * Returns cache adapter factory instance
     * @return CacheAdapterFactory factory instance
     */
    public function getFactory()
    {
        return $this->_factory;
    }
    
    /**
     * Sets active data cache factory
     * @param CacheAdapterFactory $factory factory instance
     * @return void
     */
    public function setFactory(CacheAdapterFactory $factory)
    {
        $this->_factory = $factory;
    }
    
    /**
     * Startup initialization
     * 
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * Notifies cache factory when pull is completed.
     * Please, override this method in any inheritor.
     * @param ICacheableDataSource $source data source provider
     * @return void
     * @throws Exception
     * @see IDataCacheAdapter::pull()
     */
    public function pull(ICacheableDataSource $source)
    {
        $this->_dataCache = array();
    }
    
    /**
     * Checks whether data value exists in the php script memory
     * Please, override this method in any inheritor.
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value for storing data in the cache
     * 
     * @return array signle record
     * @see IDataCacheAdapter::get()
     */
    public function get(ICacheableDataSource $source, $keyValue)
    {
        $cacheId = $source->getCacheId($keyValue);
        if (isset($this->_dataCache[$cacheId])) {
            return $this->_dataCache[$cacheId];
        }
        return array();
    }
    
    /**
     * Deletes record with $keyValue from cache storage
     * and php script memory.
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value used for storing data in the cache
     * @return boolean whether record successfully removed from the cache
     * @see IDataCacheAdapter::delete()
     */
    public function delete(ICacheableDataSource $source, $keyValue)
    {
        if (!$cache = $this->resolveCacheComponent($source)) {
            return false;
        }
        $cacheId = $source->getCacheId($keyValue);
        unset($this->_dataCache[$cacheId]);
        if ($cache->delete($cacheId)) {
            Yii::trace('Data source record removed from cache: ' . get_class($source));
            return true;
        }
        return false;
    }
    
    /**
     * Puts value into PHP script data cache with given $cacheId and $data record
     * @param string $cacheId data record cache ID
     * @param mixed  &$data   data record
     * @return void
     */
    protected function putScriptCache($cacheId, &$data)
    {
        $this->_dataCache[$cacheId] = $data;
    }
    
    /**
     * Returns value from PHP script data cache with given $cacheId
     * 
     * @param string $cacheId data record cache ID
     * 
     * @return mixed data record value or NULL when not found.
     */
    protected function getScriptCache($cacheId)
    {
        return isset($this->_dataCache[$cacheId]) ? $this->_dataCache[$cacheId] : null;
    }
}