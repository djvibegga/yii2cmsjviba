<?php

namespace backend\modules\pages\models;

use Yii;
use common\models\User;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $object_id
 * @property string  $name
 * @property integer $status
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $published_at
 */
class Page extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DELETED = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at', 'published_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['status'], 'in', 'range' => array_keys(self::getAvailableStatuses())]
        ];
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
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'name' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'published_at' => 'Published At',
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
    public function getInfos()
    {
        return $this->hasMany(PageInfo::className(), ['page_id' => 'id']);
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
