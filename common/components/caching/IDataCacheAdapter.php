<?php
/**
 * IDataCacheAdapter interface
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/yii2cms/php/docs
 */
namespace common\components\caching;
/**
 * IDataCacheAdapter is the interface which describes
 * functionality of any data source based cache adapter
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/yii2cms/php/docs
 */
interface IDataCacheAdapter
{
    /**
     * Makes data source pull and caches fetched data
     * 
     * @param ICacheableDataSource $source data source provider
     * 
     * @return void
     */
    public function pull(ICacheableDataSource $source);
    
    /**
     * Makes single record fething from data source and caches
     * fetched data
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value used for storing data in the cache
     * 
     * @return array signle record
     */
    public function get(ICacheableDataSource $source, $keyValue);
    
    /**
     * Updates or inserts cache's data with unique key ($keyValue)
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value used for storing data in the cache
     * 
     * @return boolean whether operation completed successfully
     */
    public function upsert(ICacheableDataSource $source, $keyValue);
    
    /**
     * Deletes single record from the cache with $keyValue unique key
     * 
     * @param ICacheableDataSource $source   data source provider
     * @param mixed                $keyValue unique key value used for storing data in the cache
     * 
     * @return boolean whether record successfully removed from the cache
     */
    public function delete(ICacheableDataSource $source, $keyValue);
}