<?php
/**
 * ComponentUrlRule class file
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
use common\models\Language;
use common\CMS;

/**
 * ComponentUrlRule is a base class for any custom URL manager rule which implements
 * SEF links building and parsing.
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Dmitriy Cherepovskii <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
abstract class ComponentUrlRule extends \yii\web\UrlRule
{
    const EXCEPTION_RECORD_DOESNT_HAVE_SEF_RULE 
        = '{method}: Given record doesn\'t have custom SEF routing rule.';
    
    /**
     * Rule template property. Url is parsed and created through this template.
     * @var string
     */
    public $template;
    
    /**
     * Name of a YObjectRecord model class, which corresponds to an Object-based entity,
     * which owns the rule.
     * @var string
     */
    public $modelClassName;
    
    /**
     * Name of the model's table name
     * @var string
     */
    protected $tableName;
    
    /**
     * Request parameter name which contains record's
     * primary key value.
     * @var string
     */
    public $requestPrimaryParameter = 'id';
    
    /**
     * This parameters should be ignored on creating urls
     * @var array
     */
    public $ignoreParams;

    /**
     * {@inheritDoc}
     * @see \yii\web\UrlRule::init()
     */
    public function init()
    {
        parent::init();
        $this->tableName = CMS::model($this->modelClassName, 'tableName');
    }
    
    /**
     * Resolves active application language ID
     * @param array $params [optional] action parameters
     * @return integer active language ID
     */
    protected function resolveLanguageId($params = array())
    {
        if (isset($params['lang_id'])) {
            return $params['lang_id'];
        } else if (isset($params['lang'])) {
            return Language::getIdByName($params['lang']);
        }
        return Language::getIdByName(Yii::$app->language);
    }
    
    /**
     * Unsets all ignored parameters from params map
     * @param array &$params parameters map
     * @return void
     */
    protected function skipParameters(&$params)
    {
        if (!empty($this->ignoreParams)) {
            foreach ($this->ignoreParams as $name) {
                unset($params[$name]);
            }
        }
        unset($params['lang_id'], $params['lang']);
    }
    
    /**
     * Resolves the list of request primary parameters (used in SEF string building)
     * @return array list of primary parameter names
     */
    protected function resolvePrimaryParameterNames()
    {
        return is_string($this->requestPrimaryParameter)
            ? array($this->requestPrimaryParameter)
            : $this->requestPrimaryParameter;
    }
    
    /**
     * Creates a URL based on this rule.
     * @param \yii\web\UrlManager $manager the manager
     * @param string              $route   the route
     * @param array               $params  list of parameters
     * @return mixed the constructed URL or false on error
     */
    public function createUrl($manager, $route, $params)
    {
        $primaryParameters = $this->resolvePrimaryParameterNames();
        if ($this->route != $route) {
            return false;
        } else {
            foreach ($primaryParameters as $primaryParam) {
                if (!isset($params[$primaryParam])) {
                    return false;
                }
            }
        }
        $seoString = $this->fetchSeoString($params);
        $this->skipParameters($params);
        foreach ($primaryParameters as $primaryParam) {
            unset($params[$primaryParam]);
        }
        $pathinfo = $this->createPathInfo(
            $manager,
            $params,
            $manager->enablePrettyUrl ? '/' : '&'
        );
        $info = empty($pathinfo) ? '' : ('/' . trim($pathinfo, '/'));
        return empty($seoString) ? false : $seoString . $info;
    }
    
    /**
     * Fetchs full SEF url from database (using internal query)
     * @param array $params url building additional parameters
     * @return string SEF url
     */
    protected function fetchSeoString($params)
    {
        $query = new \yii\db\Query();
        $query->from = ['"' . $this->tableName . '" "t"'];
        $query->select = ['"os".url as "seo"'];
        $query->andFilterWhere(['"t".id' => $params[$this->requestPrimaryParameter]]);
        $query->leftJoin(
            'object_seo "os"',
            '"os".to_object_id = "t".object_id AND "os".lang_id = :lang',
            [':lang' => $this->resolveLanguageId()]
        );
        return $query->createCommand()->queryScalar();
    }
    
    /**
     * Checks SEF url with rule regular expression and returns matches
     * @param \yii\web\UrlManager $manager     the URL manager
     * @param \yii\web\Request    $request     the request object
     * @param string              $pathInfo    path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string              $rawPathInfo path info that contains the potential URL suffix
     * @return array parse SEF url matches
     */
    protected function matchSeoString($manager, $request, $pathInfo, $rawPathInfo)
    {
        $pathInfoLength = strlen($pathInfo);
        if ($pathInfoLength > 0) {
            $seoSearchString = rtrim($pathInfo, '/');
        } else {
            return false;
        }
        if (!preg_match($this->pattern . 'i', $pathInfo, $matches)) {
            return false;
        }
        return $matches;
    }
    
    /**
     * Creates additional parameters path info
     * @param \yii\web\UrlManager $manager   url manager instance
     * @param array               $params    additional url parameters
     * @param string              $ampersand optional ampersand
     * @return string created path info
     */
    protected function createPathInfo($manager, $params, $ampersand)
    {
        $suffix = $this->suffix === null ? $manager->suffix : $this->suffix;
        $ret = '';
        if ($ampersand === '/') {
            if ($infopath = $this->createPathInfoDefault($params, '/', '/')) {
                $ret = '/' . $infopath . $suffix;
            }
        } else {
            if ($infopath = $this->createPathInfoDefault($params, '=', $ampersand)) {
                $ret = $suffix . '?' . $infopath;
            }
        }
        return $ret;
    }
    
    /**
     * Builds path info using default behavior
     * @param array  $params
     * @param string $equal
     * @param string $ampersand
     * @return string
     */
    protected function createPathInfoDefault($params, $equal, $ampersand)
    {
        $pieces = [];
        foreach ($params as $name => $value) {
            $pieces[] = $name . $equal . $value;
        }
        return implode($ampersand, $pieces);
    }
    
    /**
     * Checks SEF url matches for presenting additional pathInfo parameters
     * and if they are present, then adds they into $_REQUEST.
     * @param array               $matches     the matches array with results of parse seo string method
     * @param \yii\web\UrlManager $manager     the URL manager
     * @param \yii\web\Request    $request     the request object
     * @param string              $pathInfo    path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string              $rawPathInfo path info that contains the potential URL suffix
     * @return array parse SEF url matches
     */
    protected function parsePathInfo($matches, $manager, $request, $pathInfo, $rawPathInfo)
    {
        $additional = substr($pathInfo, strlen($matches[0]));
        if (!empty($additional)) {
            $manager->parsePathInfo($additional);
        }
    }
    
    /**
     * Parses matched SEF url 
     * @param string              $seoSearchString parsed SEF url string
     * @param \yii\web\UrlManager $manager         the URL manager
     * @param \yii\web\Request    $request         the request object
     * @param string              $pathInfo        path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string              $rawPathInfo     path info that contains the potential URL suffix
     * @return mixed parsed route if found, otherwise false.
     */
    protected function parseSeoString($seoSearchString, $manager, $request, $pathInfo, $rawPathInfo)
    {
        $query = new \yii\db\Query();
        $query->from = ['"' . $this->tableName . '" "t"'];
        $query->select = ['"t".id'];
        $query->andFilterWhere(['"os".url' => $seoSearchString]);
        $query->leftJoin('object_seo "os"', '"os".to_object_id = "t".object_id');
        $connection = CMS::model($this->modelClassName, 'getDb');
        if ($recordId = $query->createCommand($connection)->queryScalar()) {
            return [$this->route, [$this->requestPrimaryParameter => $recordId]];
        }
        return false;
    }
    
    /**
     * Parses a URL based on this rule.
     * @param \yii\web\UrlManager $manager     the URL manager
     * @param \yii\web\Request    $request     the request object
     * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        $rawPathInfo = $pathInfo;
        $matches = $this->matchSeoString($manager, $request, $pathInfo, $rawPathInfo);
        if (empty($matches)) {
            return false;
        } else {
            $ret = $this->parseSeoString(
                rtrim($matches[0], '/'), $manager, $request, $pathInfo, $rawPathInfo
            );
            if (!empty($ret)) {
                $this->parsePathInfo($matches, $manager, $request, $pathInfo, $rawPathInfo);
                return $ret;
            }
        }
        return false;
    }
    
    /**
     * Builds a SEF (search engine friendly) part of a URL, using the template 
     * and the data from getTemplateVars().
     * @param CActiveRecord|array $record An Object-based entity record
     * @param integer             $langId language ID
     * @return string SEF part of a URL
     */
    public function buildUrl($record, $langId)
    {
        if (is_object($record) && ! ($record instanceof IHasSefUrl)) {
            throw new \Exception(
                Yii::t(
                    'app',
                    self::EXCEPTION_RECORD_DOESNT_HAVE_SEF_RULE,
                    array('{method}' => __METHOD__)
                )
            );
        }
        $vars = $this->getTemplateVars($record, $langId);
        if (!empty($vars)) {
            return strtr($this->template, $vars);
        }
        return null;
    }
    
    /**
     * This method must be overloaded in custom UrlRule classes.
     * Its purpose is to get an array of variables for pasting into
     * the template when SEF URL gets built.
     * @param CActiveRecord|array $record An Object-based entity record
     * @param integer             $langId language ID
     * @return array Data for pasting into the template.
     * @abstract
     */
    public abstract function getTemplateVars($record, $langId);
    
    /**
     * Transforms given text so it can be used as a part of a SEF URL.
     * @param mb_string $text          Incoming string
     * @param boolean   $transliterate [optional] TRUE if transliteration must be used, FALSE otherwise.
     * Defaults to TRUE.
     * @param string    $langName      [optional] name of the source language for transliteration.
     * Application language is used if it's omitted.
     * @return string Resulting string
     * @access protected
     */
    public static function transformStringForUrl($text, $transliterate = true, $langName = null)
    {
        if ($langName === null) {
            $langName = Yii::$app->language;
        }
        $text = mb_strtolower($text);
        if ($transliterate && $langName != 'en') {
            $text = StringUtils::transliteration($text, $langName . '_en', false);
            $text = rtrim($text, '-');
        }
        $text = preg_replace('/[^a-z0-9]/u', '-', $text);
        return rtrim($text, '-');
    }
}