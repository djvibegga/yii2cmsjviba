<?php

namespace backend\modules\articles\models;

use Yii;
use common\components\MetaDataBehavior;

/**
 * This is the model class for table "article_info".
 *
 * @property integer $id
 * @property integer $lang_id
 * @property integer $article_id
 * @property string  $title
 * @property string  $teaser
 * @property string  $text
 * @property string  $meta
 * @property string  $url
 *
 * @property Article $article
 */
class ArticleInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lang_id', 'article_id', 'title'], 'required'],
            [['lang_id', 'article_id'], 'integer'],
            [['text', 'meta'], 'string'],
            [['title', 'teaser'], 'string', 'max' => 255],
            ['url', 'string', 'max' => 255],
            [['title'], 'unique'],
            [
                'article_id', 'exist',
                'skipOnError' => true,
                'targetClass' => Article::className(),
                'targetAttribute' => ['article_id' => 'id']
            ]
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
            'article_id' => 'Article ID',
            'title' => 'Title',
            'teaser' => 'Teaser',
            'text' => 'Text',
            'meta' => 'Meta',
            'url' => 'Url'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(Article::className(), ['id' => 'article_id']);
    }
}
