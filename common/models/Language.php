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
     * @var array
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
}
