<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "object_seo".
 *
 * @property integer $id
 * @property integer $to_object_id
 * @property string  $url
 * @property integer $lang_id
 * @property string  $type
 */
class ObjectSeo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'object_seo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_object_id', 'url', 'lang_id', 'type'], 'required'],
            [['to_object_id', 'lang_id'], 'integer'],
            [['url'], 'string', 'max' => 256],
            [['type'], 'string', 'max' => 16],
            [['url'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_object_id' => 'To Object ID',
            'url' => 'Url',
            'lang_id' => 'Lang ID',
            'type' => 'Type',
        ];
    }
}
