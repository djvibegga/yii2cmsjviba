<?php
/**
 * UrlManager class file
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common\components;

use Yii;
use common\models\Language;
use common\models\ObjectRecord;
use common\models\ObjectSeo;
use yii\base\InvalidParamException;
use common\interfaces\IHasSefUrl;
use common\components\caching\ICacheableDataSource;
use common\components\caching\CacheAdapterFactory;
/**
 * UrlManager is the custom url manager component class
 * extended for system requirements.
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
class UrlManager extends \yii\web\UrlManager
{
    /**
     * Request URI parameter name which identifies a language
     * @var string
     */
    public $languageParameterName = 'lang';
    
    /**
     * Whether required to store a language in the cookies
     * @var string
     */
    public $storeLanguageInCookies = false;
    
    /**
     * Whether required to display a language in the url
     * when it is default
     * @var bool
     */
    public $appendLanguageWhenItIsDefault = true;
    
    /**
     * Cookie name which identifies a language
     * @var string
     */
    public $languageCookieName = 'lang';
    
    /**
     * Whether needed to use language-based urls
     * @var string
     */
    public $useLangBasedUrls = true;
    
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
//     public $entityDependenciesMap = array();
    
    /**
     * Application default language
     * @var string
     */
    private $_defaultLanguage;
    
    /**
     * {@inheritDoc}
     * @see \yii\web\UrlManager::init()
     */
    public function init()
    {
        parent::init();
        $this->_defaultLanguage = Yii::$app->language;
    }
    
    /**
     * Resolves dependent entity class list by given affected entity
     *
     * @param string|object $affectedEntity entity which SEF url was affected
     *
     * @return array
     */
//     public function resolveDependentEntities($affectedEntity)
//     {
//         $affectedClassName = is_object($affectedEntity)
//             ? get_class($affectedEntity)
//             : $affectedEntity;
//         $ret = array();
//         foreach ($this->entityDependenciesMap as $dependent => $affected) {
//             if (in_array($affectedClassName, $affected)) {
//                 $ret[] = $dependent;
//             }
//         }
//         return $ret;
//     }
    
    /**
     * Returns custom rule class by the class name.
     * @param string $ruleClassName class name, not a path alias!
     * @return ComponentUrlRule|null rule instance or null if rule with such
     * class name wasn't found.
     */
    public function getRuleByClassName($ruleClassName)
    {
        foreach ($this->rules as $rule) {
            if (get_class($rule) == $ruleClassName) {
                return $rule;
            }
        }
        return null;
    }
    
    /**
     * Appends into any url current language
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * @return string
     * @see \yii\web\UrlManager::createUrl()
     */
    public function createUrl($params)
    {
        if (! $this->useLangBasedUrls) {
            return parent::createUrl($params);
        }
        $language = null;
        if (isset($params[$this->languageParameterName])) {
            $language = $params[$this->languageParameterName];
        } else if ($this->storeLanguageInCookies) {
            $cookies = Yii::$app->request->getCookies();
            if (isset($cookies[$this->languageCookieName])) {
                $language = $cookies[$this->languageCookieName]->value;
            }
        }
        if ($language === null) {
            $language = Yii::$app->language;
        }
        $ret = parent::createUrl($params);
        $baseUrl = Yii::$app->request->getBaseUrl();
        if ($language == $this->_defaultLanguage && ! $this->appendLanguageWhenItIsDefault) {
            return rtrim($ret, '/');
        } else {
            if (empty($baseUrl)) {
                return '/' . $language . rtrim($ret, '/');
            } else {
                return strtr($ret, array($baseUrl => rtrim($baseUrl, '/') . '/' . $language));
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\web\UrlManager::parseRequest()
     */
    public function parseRequest($request)
    {
        if ($this->useLangBasedUrls) {
            $languages = Language::getNames();
            $rawPathInfo = $request->getPathInfo();
            if (preg_match('/^(' . implode('|', $languages) . ')\/?(.*)$/', $rawPathInfo, $matches)) {
                Yii::$app->language = $language = $matches[1];
                if ($this->storeLanguageInCookies) {
                    $cookie = new \yii\web\Cookie([
                        'name' => $this->languageCookieName,
                        'value' => $matches[1]
                    ]);
                    $request->cookies[$this->languageCookieName] = $cookie;
                }
                $request->setPathInfo($matches[2]);
            } else if ($this->storeLanguageInCookies) {
                $cookies = Yii::$app->request->getCookies();
                if (isset($cookies[$this->languageCookieName])) {
                    Yii::$app->language = $language = $cookies[$this->languageCookieName]->value;
                }
            }
            
        }
        list($route, $params) = parent::parseRequest($request);
        if (isset($language)) {
            $params[$this->languageParameterName] = $language;
        }
        return [$route, $params];
    }
    
    /**
     * Loads object seo record using given attributes
     * @param int $objectId the target object id
     * @param int $langId   the target lang id
     * @return \common\models\ObjectSeo|null loaded record, null
     * if not found.
     */
    public function loadObjectSeo($objectId, $langId)
    {
        $condition = [
            'to_object_id' => $objectId,
            'lang_id' => $langId
        ];
        try {
            return ObjectSeo::findOne($condition);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to load object_seo record because of db error: ' . $e->getMessage());
        }
        return null;
    }
    
    /**
     * Loads object seo records list by object id
     * @param int $objectId the target object id
     * @return array|false loaded seo records list, false otherwise
     */
    public function loadObjectSeoListByObjectId($objectId)
    {
        try {
            return ObjectSeo::findAll(['to_object_id' => $objectId]);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to load object_seo records because of db error: ' . $e->getMessage());
        }
        return false;
    }
    
    /**
     * Deletes object seo records list by object id
     * @param int $objectId the target object id
     * @return bool whether list of records has successfully deleted
     */
    public function deleteObjectSeoByObjectId($objectId)
    {
        try {
            ObjectSeo::deleteAll(['to_object_id' => $objectId]);
            return true;
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to delete object_seo records because of db error: ' . $e->getMessage());
        }
        return false;
    }
    
    /**
     * Performs building of SEF url for given object record
     * @param ObjectRecord $record target record
     * @param bool         $force  whether required to perform operation in force mode
     * @return bool whether operation has successfully completed
     */
    public function buildSefUrl(ObjectRecord $record, $force = false)
    {
        if (! $record instanceOf IHasSefUrl) {
            throw new InvalidParamException('Record should implement IHasSefUrl interface.');
        }
        
        if (! $record->getShouldRebuildSefUrl()) {
            return true;
        }
        
        $ruleClass = $record->getUrlRuleClassName();
        if ($rule = $this->getRuleByClassName($ruleClass)) {
            $languages = Language::getList();
            foreach ($languages as $langId => $langName) {
                if (! $seo = $this->loadObjectSeo($record->object_id, $langId)) {
                    $seo = new ObjectSeo();
                    $seo->type = call_user_func([get_class($record), 'tableName']);
                    $seo->lang_id = $langId;
                    $seo->to_object_id = $record->object_id;
                }
                $seo->url = $rule->buildUrl($record, $langId);
                $i = 0;
                $end = '-' . $i;
                if ($seo->getIsNewRecord()) {
                    while (! $seo->save()) {
                        break; //TODO: remove this line and check it in yii console
                        $seo->url = rtrim($seo->url, $end);
                        ++$i;
                        $end = '-' . $i;
                        $seo->url .= $end;
                    }
                } else {
                    while (! $seo->save(true, array('url'))) {
                        break; //TODO: remove this line and check it in yii console
                        $seo->url = rtrim($seo->url, $end);
                        ++$i;
                        $end = '-' . $i;
                        $seo->url .= $end;
                    }
                }
            }
            if ($rule instanceof ICacheableDataSource) {
                $this->refreshUrlCache($record);
            }
        } else {
            throw new InvalidParamException(
                'Url rule "' . $ruleClass . '" is not defined in routes. Please check configuration.'
            );
        }
    }
    
    /**
     * Refreshes url cache state of given record
     * @param ObjectRecord $record target record
     * @return void
     */
    public function refreshUrlCache(ObjectRecord $record)
    {
        $rule = $this->getRuleByClassName($record->getUrlRuleClassName());
        if (! $rule || ! $rule instanceOf ComponentUrlRuleWithCache) {
            throw new InvalidParamException('Given record is not acceptable to refresh url cache.');
        }
        $factory = Yii::$app->get('cacheAdapterFactory');
        $adapter = $factory->createFromSource($rule);
        foreach (Language::getList() as $langId => $langName) {
            $uniqueKeyField = $rule->getUniqueKeyField();
            $keyValue['lang_id'] = $langId;
            unset($uniqueKeyField['lang_id']);
            foreach ($uniqueKeyField as $attribute => $key) {
                $keyValue[$key] = $record->getAttribute($attribute);
            }
            $adapter->upsert($rule, $keyValue);
        }
    }
    
    /**
     * Clears url cache state of given record
     * @param ObjectRecord $record target record
     * @return void
     */
    public function clearUrlCache(ObjectRecord $record)
    {
        $rule = $this->getRuleByClassName($record->getUrlRuleClassName());
        if (! $rule || ! $rule instanceOf ComponentUrlRuleWithCache) {
            throw new InvalidParamException('Given record is not acceptable to clear url cache.');
        }
        if ($rule instanceof ICacheableDataSource) {
            $factory = Yii::$app->get('cacheAdapterFactory');
            $adapter = $factory->create(CacheAdapterFactory::SQL_CACHE_ADAPTER);
            $adapter->delete($rule, $record->getPrimaryKey());
        }
    }
}