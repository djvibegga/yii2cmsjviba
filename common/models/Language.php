<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lang".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string  $name
 * @property string  $label
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lang';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 2],
            [['label'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'name' => 'Name',
            'label' => 'Label',
        ];
    }
    
    /**
     * Returns map of languages. Keys are ids,
     * values are names
     * @return array
     * @static
     * @todo: implement caching of languages
     */
    public static function getList()
    {
        $ret = [];
        foreach (self::find()->asArray()->all() as $lang) {
            $ret[$lang['id']] = $lang['name'];
        }
        return $ret;
    }
    
    /**
     * Returns language ID by given language name
     * @param string $name language name
     * @return mixed language ID, false if not found
     * @static
     */
    public static function getIdByName($name)
    {
        return array_search($name, self::getList());
    }
    
    /**
     * Gets Language record attributes by the given ID
     * @param integer $id ID of the language
     * @return array Attributes found. Null if no such language found.
     */
    public static function findById($id)
    {
        $list = self::getList();
        foreach ($list as $language) {
            if ($language['id'] == $id) {
                return $language;
            }
        }
    }
}
