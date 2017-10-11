<?php

namespace backend\modules\articles\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\components\PhotoBehavior;
use common\models\User;

/**
 * This is the model class for table "article".
 *
 * @property integer $id
 * @property string  $article_category_ids
 * @property integer $user_id
 * @property integer $object_id
 * @property string  $name
 * @property integer $status
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $published_at
 * @property string  $photo
 *
 * @property User $user
 * @property ArticleInfo[] $articleInfos
 */
class Article extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DELETED = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() {
                    return new \yii\db\Expression('NOW()');
                },
            ],
            'photos' => [
                'class' => PhotoBehavior::className(),
                'photoAttributes' => ['photo'],
                'storageBasePath' => Yii::getAlias('@backend/web') . '/upload/photos',
                'storageBaseUrl' => '/upload/photos',
                'formats' => [
                    'small' => [
                        'width' => 120
                    ],
                    'medium' => [
                        'width' => 250
                    ],
                    'big' => [
                        'width' => 400
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_category_ids', 'photo'], 'string'],
            [['user_id', 'name'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['created_at', 'updated_at', 'published_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_category_ids' => 'Article Category Ids',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'name' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'published_at' => 'Published At',
            'photo' => 'Photo',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleInfos()
    {
        return $this->hasMany(ArticleInfo::className(), ['article_id' => 'id']);
    }
    
    /**
     * Returns map of available statuses
     * @return string[]
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_DRAFT => Yii::t('app', 'Draft'),
            self::STATUS_PUBLISHED => Yii::t('app', 'Published'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }
}
