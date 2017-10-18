<?php

namespace backend\modules\pages\models;

use Yii;
use common\components\MetaDataBehavior;

/**
 * This is the model class for table "page_info".
 *
 * @property integer $id
 * @property integer $lang_id
 * @property integer $page_id
 * @property string  $title
 * @property string  $teaser
 * @property string  $text
 * @property string  $meta
 * @property string  $url
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
            [['title', 'teaser', 'url'], 'string', 'max' => 255],
            [['title'], 'unique'],
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            MetaDataBehavior::className()
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
