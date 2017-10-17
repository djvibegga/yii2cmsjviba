<?php
/**
 * CacheAdapterFactory class
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
 * CacheAdapterFactory is the adapter factory class which provides
 * flexible instantiation of cache adapters
 * 
 * PHP version 5
 * 
 * @category YII2-CMS
 * @package  Ext.caching
 * @author   Evgeniy Marilev <marilev@jviba.com>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://jviba.com/packages/php/docs
 */
class CacheAdapterFactory extends \common\components\Component
{
    /**
     * Cache adapter enumeration for SQL adapters
     * @var string
     */
    const SQL_CACHE_ADAPTER = 'sql';
    
    /**
     * Map for cache adapters
     * @var array
     */
    public $adapters = array(
        self::SQL_CACHE_ADAPTER => '\common\components\caching\SqlDataCacheAdapter',
    );
    
    /**
     * Created adaptes with data sources cache
     * Format: array(dataSourceClassName => adapterInstance).
     * @var array
     */
    private $_adaptersCache = array();
    
    /**
     * Component name used in operations with cache
     * @var string
     */
    public $cacheComponentName = 'cache';
    
    /**
     * Creates and initializes an instance of SqlDataCacheAdapter
     * 
     * @param string adapter type (alias).
     * @param array $config  adapter configuration
     * 
     * @return SqlDataCacheAdapter
     */
    public function create($type = self::SQL_CACHE_ADAPTER, $config = array())
    {
        if (!isset($config['class']) && isset($this->adapters[$type])) {
            $config['class'] = $this->adapters[$type];
        }
        $config['factory'] = $this;
        $adapter = Yii::createObject($config);
        $adapter->init();
        return $adapter;
    }
    
    /**
     * Creates an adapter required for given data source
     * 
     * @param ICacheableDataSource $source data source
     * 
     * @return IDataCacheAdapter created adapter
     */
    public function createFromSource(ICacheableDataSource $source)
    {
        $sourceClassName = get_class($source);
        if (!isset($this->_adaptersCache[$sourceClassName])) {
            $this->_adaptersCache[$sourceClassName] = $source->createAdapter($this);
        }
        return $this->_adaptersCache[$sourceClassName];
    }
    
    /**
     * Returns execute pull state for given data source class name
     * 
     * @param string $sourceClassName data source class name
     * 
     * @return boolean pull state
     */
    public function getExecPullState($sourceClassName)
    {
        $cache = Yii::$app->get($this->cacheComponentName);
        $cacheId = self::_buildExecPullCacheId($sourceClassName);
        return $cache->get($cacheId);
    }
    
    /**
     * Changes execute pull state for given data source class name
     * 
     * @param string  $sourceClassName data source class name
     * @param boolean $state           new pull state
     * 
     * @return boolean whether operation success
     */
    public function setExecPullState($sourceClassName, $state)
    {
        $cache = Yii::$app->get($this->cacheComponentName);
        $cacheId = self::_buildExecPullCacheId($sourceClassName);
        return $cache->set($cacheId, $state);
    }
    
    /**
     * Creates and returns cache ID of execute pull state for
     * given data source class name
     * 
     * @param string $sourceClassName data source class name
     * 
     * @return string cache unique ID
     */
    private static function _buildExecPullCacheId($sourceClassName)
    {
        return __CLASS__ . '#pull#' .  $sourceClassName;
    }
}