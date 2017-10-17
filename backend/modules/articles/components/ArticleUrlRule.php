<?php
/**
 * ArticleUrlRule class
 * 
 * PHP version 5
 * 
 * @category   YII2-CMS
 * @package    Module.articles
 * @subpackage Module.articles.component
 * @author     Alexander Melyakov <melyakov@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace backend\modules\articles\components;

use Yii;
use common\components\ComponentUrlRule;
use common\components\ComponentUrlRuleWithCache;
use common\CMS;
use common\models\Language;

/**
 * ArticleUrlRule class is the SEF building and parsing URL-manager rule class for
 * module Articles.
 * 
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.articles
 * @subpackage Module.articles.component
 * @author     Alexander Melyakov <melyakov@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
class ArticleUrlRule extends ComponentUrlRuleWithCache
{
    const EXCEPTION_NOT_PUBLISHED = '{method}: given record has not published yet.';
    const EXCEPTION_NO_PARENT_PUBLISHED = '{method}: parent of the given record has not published yet.';
    
    /**
     * Default model class name
     * @var string
     */
    public $modelClassName = '\backend\modules\articles\models\Article';

    public $cacheComponentName = 'memcache';
    
    /**
     * {@inheritDoc}
     * @see \common\components\ComponentUrlRule::getTemplateVars()
     */
    public function getTemplateVars($record, $langId)
    {
        $model = is_array($record)
            ? CMS::model('\backend\modules\articles\models\Article', 'findOne', [$record['id']])
            : $record;

        $pageInfo = $model->getTranslatedInfo($langId);
        $categories = $model->categories;
        $category = array_shift($categories);
        $title = $pageInfo ? $pageInfo->title : '';
        if (empty($title)) {
            return false;
        }
        $language = Language::findById($langId);
        return array(
            '{category}' => empty($category) 
                ? ''
                : self::transformStringForUrl(
                   $category->object->getTranslatedInfo($langId)->url,
                   true,
                   $language['name']
               ),
            '{sefPart}' => self::transformStringForUrl($title, true, $language['name']),
        );
    }
}