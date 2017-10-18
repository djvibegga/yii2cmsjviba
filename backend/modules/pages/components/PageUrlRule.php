<?php
/**
 * PageUrlRule class
 * 
 * PHP version 5
 * 
 * @category   YII2-CMS
 * @package    Module.articles
 * @subpackage Module.articles.component
 * @author     Alexander Melyakov <melyakov@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace backend\modules\pages\components;

use Yii;
use common\components\ComponentUrlRule;
use common\components\ComponentUrlRuleWithCache;
use common\CMS;
use common\models\Language;

/**
 * PageUrlRule class is the SEF building and parsing URL-manager rule class for
 * entity "page".
 * 
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.pages
 * @subpackage Module.pages.component
 * @author     Alexander Melyakov <melyakov@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
class PageUrlRule extends ComponentUrlRuleWithCache
{
    /**
     * Default model class name
     * @var string
     */
    public $modelClassName = '\backend\modules\pages\models\Page';
    
    /**
     * {@inheritDoc}
     * @see \common\components\ComponentUrlRule::getTemplateVars()
     */
    public function getTemplateVars($record, $langId)
    {
        $model = is_array($record)
            ? CMS::model('\backend\modules\pages\models\Page', 'findOne', [$record['id']])
            : $record;

        $pageInfo = $model->getTranslatedInfo($langId);
        $url = $pageInfo ? $pageInfo->url : '';
        if (empty($url)) {
            return false;
        }
        $language = Language::findById($langId);
        return array(
            '{sefPart}' => self::transformStringForUrl($url, true, $language['name']),
        );
    }
}