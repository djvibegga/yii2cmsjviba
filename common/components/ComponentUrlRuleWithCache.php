<?php

/**
 * ComponentUrlRuleWithCache class file
 * 
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Dmitriy Cherepovskii <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common\components;

use Yii;
use common\components\caching\ICacheableDataSource;
use common\CMS;
use common\components\caching\CacheAdapterFactory;
/**
 * ComponentUrlRuleWithCache is a base class for any custom URL manager rule which implements
 * SEF links building and parsing and caches data used for links manipulationg
 * from the DB.
 *
 * @category   YII-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Dmitriy Cherepovskii <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
abstract class ComponentUrlRuleWithCache extends ComponentUrlRule implements ICacheableDataSource
{
    const DEFAULT_FETCH_BLOCK_SIZE = 256;
    const DEFAULT_CACHE_TIME_TO_LIVE = 5184000; //24 hours in seconds
    
    /**
     * The key value in {@link getCacheId()} gets prefixed by this or by it's class
     * name if no prefix given.
     * @var string
     */
    public $cacheIdPrefix;
    
    /**
     * Cache component used
     * @var string
     */
    public $cacheComponentName = 'cache';
    
    /**
     * Fetch records block size (used in pull method)
     * @var integerhours
     */
    public $fetchBlockSize = self::DEFAULT_FETCH_BLOCK_SIZE;
    
    /**
     * Cache life time duration
     * @var integer
     */
    public $cacheLifeTime = self::DEFAULT_CACHE_TIME_TO_LIVE;
    
    /**
     * Unique key field name
     * @var array
     */
    public $uniqueKeyFieldName = array('id' => 'id', 'lang_id' => 'lang_id');

    /**
     * db connection name
     * @var string
     */
    public $connectionName = 'db';

    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheComponent()
     */
    public function getCacheComponent()
    {
        return Yii::$app->get($this->cacheComponentName);
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheId()
     */
    public function getCacheId($keyValue)
    {
        if (is_array($keyValue)) {
            ksort($keyValue);
            $keyValue['lang_id'] = (int)$keyValue['lang_id'];
            $keyValue = serialize($keyValue);
        }
        return isset($this->cacheIdPrefix)
             ? $this->cacheIdPrefix . $keyValue
             : get_class($this) . '_' . $keyValue;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getFetchBlockSize()
     */
    public function getFetchBlockSize()
    {
        return $this->fetchBlockSize;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheLifeTime()
     */
    public function getCacheLifeTime()
    {
        return $this->cacheLifeTime;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheTableName()
     */
    public function getCacheTableName()
    {
        return $this->tableName;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getUniqueKeyField()
     */
    public function getUniqueKeyField()
    {
        return $this->uniqueKeyFieldName;
    }

    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::createAdapter()
     */
    public function createAdapter(CacheAdapterFactory $factory)
    {
        return $factory->create(CacheAdapterFactory::SQL_CACHE_ADAPTER);
    }

    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::buildCriteria()
     */
    public function buildCriteria(\yii\db\Query $query, $keyValue = null)
    {
        $uniqueKeyField = $this->getUniqueKeyField();
        $query->select = array_merge(
            $query->select,
            [
                '"t".id',
                '"os".url as "seo"',
                '"os".lang_id'
            ]
        );
        $query->leftJoin('object_seo "os"', '"os".to_object_id = t.object_id');
        if ($keyValue !== null) {
            $query->andFilterWhere([
                '"os".lang_id' => $keyValue['lang_id']
            ]);
            unset($uniqueKeyField['lang_id']);
            foreach ($uniqueKeyField as $attribute => $key) {
                $query->andFilterWhere(['"t"."' . $attribute . '"' => $keyValue[$key]]);
            }
        }
    }
    
    /**
     * Tries to serve entity data from cache data source before
     * querying to the database
     *
     * @param string       $seoSearchString parsed SEF url string
     * @param CUrlManager  $manager         the URL manager
     * @param CHttpRequest $request         the request object
     * @param string       $pathInfo        path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string       $rawPathInfo     path info that contains the potential URL suffix
     *
     * @return mixed parsed route if found, otherwise false.
     * @see ComponentUrlRule::parseSeoString()
     */
    protected function parseSeoString($seoSearchString, $manager, $request, $pathInfo, $rawPathInfo)
    {
        $factory = Yii::$app->get('cacheAdapterFactory');
        $source = CMS::model('new', $this->modelClassName);
        $adapter = $factory->createFromSource($source);
        $objectRecordData = $adapter->get(
            $source,
            array(
                'url' => $seoSearchString,
                'lang_id' => $this->resolveLanguageId()
            )
        );
        if (empty($objectRecordData)) {
            return false;
        }
        if ($objectRecordData['type'] != call_user_func([$this->modelClassName, 'tableName'])) {
            return false;
        }
        if (isset($objectRecordData['id']) && $recordId = $objectRecordData['id']) {
            return [$this->route, [$this->requestPrimaryParameter => $recordId]];
        }
        return false;
    }
    
    /**
     * Fetchs full SEF url from cacheable data source
     *
     * @param array $params url building additional parameters
     *
     * @return string SEF url
     */
    protected function fetchSeoString($params)
    {
        $factory = Yii::$app->get('cacheAdapterFactory');
        $adapter = $factory->createFromSource($this);
        $langId = $this->resolveLanguageId($params);
        if (!empty($this->requestPrimaryParameter)) {
            $primaryNames = $this->resolvePrimaryParameterNames();
            foreach ($primaryNames as $param) {
                if (isset($params[$param])) {
                    $primaryParams[$param] = is_numeric($params[$param])
                        ? (int)$params[$param]
                        : $params[$param];
                }
            }
            $params = $primaryParams;
        }
        $params['lang_id'] = $langId;
        $objectRecordData = $adapter->get($this, $params);
        return !empty($objectRecordData['seo']) ? $objectRecordData['seo'] : '';
    }
    
    /**
     * Returns active database connection
     * 
     * @return \yii\db\Connection database connection
     */
    public function getDbConnection() 
    {
       return Yii::$app->get($this->connectionName);
    }
    
    /**
     * Sets database connection name
     * 
     * @param string $connectionName connection name
     * 
     * @return void
     */
    public function setDbConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }
}