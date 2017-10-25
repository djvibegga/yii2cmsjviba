<?php
/**
 * TranslationBehavior class file
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.model
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common\components;

use Yii;
use yii\base\Behavior;
use common\models\Language;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * TranslationBehavior provides an owner model with additional relations
 * and methods, which can be used to find a specific translation or to check,
 * if an object has any translations at all.
 *
 * This behavior provides following relations to its owner model (when it's enabled):
 * <ul>
 *  <li>infos</li>
 *  <li>info - this relation needs a parameter with the key ':langId' set when it's used.</li>
 * </ul>
 * It also provides following properties to its owner model:
 * <ul>
 *  <li>isTranslated</li>
 * </ul>
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.model
 * @author     Dmitry Cherepovsky <cherep@jviba.com>
 * @author     Eugeniy Marilev <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
class TranslationBehavior extends Behavior
{
    const EXCEPTION_NO_SUCH_LANGUAGE = '{method}: No such language found.';
    const EXCEPTION_EMPTY_MODEL_CLASS_NAME = '{class}: Model class name must be given.';
    const EXCEPTION_WRONG_MODEL_CLASS_GIVEN = '{class}: Wrong model class given.';
    const EXCEPTION_WRONG_CONFIG_GIVEN = '{method}: Wrong config given.';
    const EXCEPTION_EMPTY_FOREIGN_KEY = 'Foreign key is not configured.';
    
    const DEFAULT_LANGUAGE_KEY = 'lang_id';
    
    /**
     * Name of an attribute of an owner model which contains language ID
     * @var string
     */
    public $languageKey = self::DEFAULT_LANGUAGE_KEY;
    
    /**
     * Name of an attribute of an owner model which is foreign key to behavior's owner table
     * @var string
     */
    public $foreignKey;
    
    /**
     * Name of a model class which implements multi-language
     * information storage for an owner model.
     * @var string
     */
    public $modelClassName;
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Behavior::events()
     */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }
    
    /**
     * Returns info records list
     * @return \yii\db\ActiveRecord[]
     */
    public function getInfos()
    {
        $query = call_user_func([$this->modelClassName, 'find']);
        $query->andFilterWhere([$this->foreignKey => $this->owner->id]);
        return $query->all();
    }
    
    /**
     * Returns info record
     * @param int|null $langId the language id
     * @return \yii\db\ActiveRecord
     */
    public function getInfo($langId = null)
    {
        $query = call_user_func([$this->modelClassName, 'find']);
        $query->andFilterWhere([$this->foreignKey => $this->owner->id]);
        if ($langId) {
            $query->andFilterWhere([$this->languageKey => $langId]);
        }
        return $query->one();
    }
   
    
    /**
     * Attaches the behavior object to a model.
     *
     * @param \yii\base\Model $owner Owner instance
     *
     * @return void
     * @throws \Exception If modelClassName is not set before attachment.
     */
    public function attach($owner)
    {
        if (empty($this->modelClassName)) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_EMPTY_MODEL_CLASS_NAME, array('{class}' => __CLASS__))
            );
        }
        if (empty($this->foreignKey)) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_EMPTY_FOREIGN_KEY, array('{class}' => __CLASS__))
            );
        }
        parent::attach($owner);
    }
    
    /**
     * Checks if given objects have any translations in given language or any
     * translations at all. Takes an array of maps. Each map contains revision-object attributes.
     *
     * Configuration array takes following options:
     *   - modelClassName
     *   - foreignKey  [optional] Default is 'revision_id'
     *   - languageKey [optional] Default is 'lang_id'
     *   - connection  [optional] Connection to a database
     * modelClassName, foreignKey, languageKey correspond to the options of this behavior.
     *
     * @param array[]      &$objects An array of data. If an object has no ID, its
     * results won't be present in the returned array.
     * @param integer|null $lang     Checks for translations in selected language.
     * Null means any translations omitted.
     * @param array        $config   Method configuration.
     *
     * @return array|boolean An array of the following format:
     * array(<object_id> => <boolean_check_result>, ...).
     *
     * @static
     * @throws \Exception When wrong properties given to the behavior
     */
    public static function isCollectionTranslated(&$objects, $lang, $config)
    {
        $lang = self::resolveLanguageId($lang);
        
        if (!isset($config['modelClassName'])) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_WRONG_CONFIG_GIVEN, array('{method}' => __METHOD__))
            );
        }
        if (!class_exists($config['modelClassName'])) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_WRONG_MODEL_CLASS_GIVEN, array('{class}' => __CLASS__))
            );
        }
        if (!isset($config['foreignKey'])) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_EMPTY_FOREIGN_KEY, array('{class}' => __CLASS__))
            );
        }
        
        $foreignKey = $config['foreignKey'];
        $languageKey = isset($config['languageKey'])
            ? $config['languageKey']
            : self::DEFAULT_LANGUAGE_KEY;
        $modelClassName = $config['modelClassName'];
        $tableName = call_user_func([$modelClassName, 'tableName']);
        
        $query = call_user_func([$modelClassName, 'find']);
        $query->from = [$tableName];
        $query->select = [$foreignKey];
        if ($lang) {
            $query->andFilterWhere([$languageKey => $lang]);
        } else {
            $query->group = $foreignKey;
        }
        $query->andFilterWhere([$foreignKey => ArrayHelper::map($objects, 'id', 'id')]);
        $connection = isset($config['connection']) ? $config['connection'] : Yii::$app->getDb();
        
        $command = $query->createCommand($connection);
        $data = ArrayHelper::map($command->queryAll(), $foreignKey, $foreignKey);
        
        $result = [];
        foreach ($objects as $object) {
            $result[$object['id']] = in_array($object['id'], $data);
        }
        return $result;
    }
    
    /**
     * Getter for "isTranslated" property.
     * Returns TRUE if the owner object has translations in the given language
     * @param integer $lang Language ID. Null means any translations omitted
     * @return boolean
     * @throws \Exception When wrong properties given to the behavior
     */
    public function getIsTranslated($lang = null)
    {
        $lang = self::resolveLanguageId($lang);
        
        if (!class_exists($this->modelClassName)) {
            throw new \Exception(
                Yii::t('app', self::EXCEPTION_WRONG_MODEL_CLASS_GIVEN, array('{class}' => __CLASS__))
            );
        }

        $query = call_user_func([$this->modelClassName, 'find'])
            ->andFilterWhere([
                $this->foreignKey => $this->owner->id
            ]);
        if ($lang) {
            $query->andFilterWhere([$this->languageKey => $lang]);
        }
        
        return $query->exists();
    }
    
    /**
     * Gets an information record which is translated to the given language.
     * @param integer|null $lang [optional] language ID. Null means any translations omitted.
     * @return CActiveRecord|null An information record or NULL, if couldn't find
     * any through the 'info' relation.
     */
    public function getTranslatedInfo($lang = null)
    {
        $lang = self::resolveLanguageId($lang);
        if ($lang) {
            return $this->getInfo($lang);
        }
        return $this->getInfo();
    }
    
    /**
     * Deletes all info relations before deleting
     *
     * @param \yii\base\Event $event event parameter
     *
     * @return void
     */
    public function beforeDelete($event)
    {
        foreach ($this->getInfos() as $info) {
            $info->delete();
        }
    }
    
    /**
     * Resolves language ID by given language name or ID
     *
     * @param string|integer $lang Language name or ID.
     * Null means the current application language should been used.
     *
     * @return integer language ID
     * @throws CException
     */
    public static function resolveLanguageId($lang)
    {
        $lang = $lang === null ? Yii::$app->language : $lang;
        if (is_string($lang)) {
            $langId = Language::getIdByName($lang);
        } else if (is_int($lang)) {
            $langId = $lang;
        }
        if (!$langId) {
            throw new \Exception(
                Yii::t('TranslationBehavior', self::EXCEPTION_NO_SUCH_LANGUAGE, array('{method}' => __METHOD__))
            );
        }
        return $langId;
    }
}