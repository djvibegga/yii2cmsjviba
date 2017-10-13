<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "page_info".
 *
 * @property integer $id
 * @property integer $lang_id
 * @property integer $page_id
 * @property string $title
 * @property string $teaser
 * @property string $text
 * @property string $meta
 */
class PageInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lang_id', 'page_id', 'title'], 'required'],
            [['lang_id', 'page_id'], 'integer'],
            [['text', 'meta'], 'string'],
            [['title', 'teaser'], 'string', 'max' => 255],
            [['title'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lang_id' => 'Lang ID',
            'page_id' => 'Page ID',
            'title' => 'Title',
            'teaser' => 'Teaser',
            'text' => 'Text',
            'meta' => 'Meta',
        ];
    }
}
