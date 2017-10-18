<?php
/**
 * CategoryUrlRule class
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
namespace backend\modules\articles\components;

use Yii;
use common\components\ComponentUrlRule;
use common\components\ComponentUrlRuleWithCache;
use common\CMS;
use common\models\Language;

/**
 * CategoryUrlRule class is the SEF building and parsing URL-manager rule class for
 * entity "article category".
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
class CategoryUrlRule extends ComponentUrlRuleWithCache
{
    /**
     * Default model class name
     * @var string
     */
    public $modelClassName = '\backend\modules\articles\models\ArticleCategory';
    
    /**
     * {@inheritDoc}
     * @see \common\components\ComponentUrlRule::getTemplateVars()
     */
    public function getTemplateVars($record, $langId)
    {
        $record = is_array($record)
            ? CMS::model('\backend\modules\articles\models\ArticleCategory', 'findOne', [$record['id']])
            : $record;
        
        $categoryInfo = $record->getTranslatedInfo($langId);
        $language = Language::findById($langId);
        return array(
            '{sefPart}' => empty($categoryInfo)
                ? ''
                : self::transformStringForUrl(
                    $categoryInfo->url,
                    true,
                    $language['name']
                )
        );
    }
}