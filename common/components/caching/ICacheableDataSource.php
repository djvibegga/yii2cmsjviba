<?php
/**
 * ICacheableDataSource interface
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
 * ICacheableDataSource is the interface which describes
 * functionality of any data source with caching feature.
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/yii2cms/php/docs
 */
interface ICacheableDataSource
{
    /**
     * Returns cache component instance
     * @return \yii\caching\Cache cache component
     */
    public function getCacheComponent();
    
    /**
     * Returns collection name to cache
     * @return string collection name
     */
    public function getCacheTableName();
    
    /**
     * Returns unique cache ID for given unique key value
     * @param mixed $keyValue unique key value
     * @return string unique cache ID
     */
    public function getCacheId($keyValue);
    
    /**
     * Returns cache key field
     * @return string|array cache key
     */
    public function getUniqueKeyField();
    
    /**
     * Returns criteria used for fetching data
     * @param \yii\db\Query $query    the query instance
     * @param mixed         $keyValue unique key value
     * @return mixed fetch criteria
     */
    public function buildCriteria(\yii\db\Query $query, $keyValue = null);
    
    /**
     * Returns fetch block size
     * @return integer fetch records batch size
     */
    public function getFetchBlockSize();
    
    /**
     * Returns cache life time (in seconds)
     * @return integer life time
     */
    public function getCacheLifeTime();
    
    /**
     * Creates a cache adapter by cache adapter factory. You can
     * specify an adapter type and adapter config to be created.
     * @param CacheAdapterFactory $factory adapter factory
     * @return IDataCacheAdapter created adapter
     */
    public function createAdapter(CacheAdapterFactory $factory);
    
    /**
     * Returns active database connection
     * @return \yii\db\Connection database connection
     */
    public function getDbConnection();
}