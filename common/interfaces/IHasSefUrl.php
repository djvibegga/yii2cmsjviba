<?php
/**
 * IHasSefUrl interface file
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.interface
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common\interfaces;
/**
 * IHasSefUrl is an interface which any Object-based model class must implement
 * if it has a SEF-URL constructing capabilities and a custom routing rule class.
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.interface
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
interface IHasSefUrl
{
    /**
     * Returns a name of an urlManager-rule of an object-based entity.
     * @return string Name of a URL rule class
     */
    public function getUrlRuleClassName();
    
    /**
     * Returns whether it is able to rebuild SEF url
     * @return bool whether it is able
     */
    public function getShouldRebuildSefUrl();
}