<?php
/**
 * CMS class file
 * 
 * PHP version 5
 *
 * @category YII2-CMS
 * @package  Core
 * @author   Marilev Evgeniy <marilev@jviba.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common;

use Yii;
/**
 * CMS is the cms core helper
 *
 * @category YII2-CMS
 * @package  Core
 * @author   Marilev Evgeniy <marilev@jviba.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://jviba.com/display/PhpDoc/yii-cms
 */
class CMS
{
    /**
     * Model factory cache
     * @var \common\components\ModelFactory
     */
    private static $_modelFactory;

    /**
     * Gets the model factory and caches it in the static property
     * @return \common\components\ModelFactory The factory
     * @throw \Exception If can't find the model factory component
     */
    public static function getModelFactory()
    {
        if (! isset(self::$_modelFactory)) {
            $factory = Yii::$app->get('modelFactory');
            self::$_modelFactory = $factory;
            if (! $factory) {
                throw new \Exception(Yii::t('ycms', 'No model factory found.'));
            }
        }
        return self::$_modelFactory;
    }

    /**
     * Creates and returns new instance of cms model. This is short alias of
     * Yii::$app->get('modelFactory')->create()
     * @param string $className       model class name or string presentation of operator 'new'
     * (for direct creating new model instance).
     * @param string $singletonMethod singleton like model instantiation method name or
     * model class name. Specify singleton method name used for instantiation.
     * For example for active records you can use 'model'.
     * @param array  $params          additional params used for instantiation.
     * 
     * @return CModel model instance
     * @throws Exception
     */
    public static function model($className, $singletonMethod = null, array $params = null)
    {
        return self::getModelFactory()->create($className, $singletonMethod, $params);
    }
    
    /**
     * Resolves real model class name. This is short alias of
     * Yii::$app->get('modelFactory')->resolveModelClassName()
     * @param string $className model class name to check.
     * @return string real model class name used.
     */
    public static function modelClass($className)
    {
        return self::getModelFactory()->resolveModelClassName($className);
    }

    /**
     * Resolves CMS default model class name. This is the shorthand method for
     * Yii::$app->get('modelFactory')->resolveModelClassAlias()
     * @param string $className Real model class name
     * @return string CMS default model class name (alias)
     */
    public static function modelClassAlias($className)
    {
        return self::getModelFactory()->resolveModelClassAlias($className);
    }

    /**
     * Checks if the model is an instance of the given CMS class alias
     * @param \yii\base\Model $model      Model instance
     * @param string          $classAlias CMS class alias
     * @return boolean Whether the model is an instance of the given class alias
     */
    public static function isInstanceofModelClass($model, $classAlias)
    {
        $className = self::modelClass($classAlias);
        return $model instanceof $className;
    }
}