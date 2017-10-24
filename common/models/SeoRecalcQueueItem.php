<?php

namespace common\models;

use Yii;
use common\components\PgAttributeBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "seo_recalc_queue".
 *
 * @property integer $id
 * @property string  $type
 * @property string  $ids
 * @property string  $created_at
 * @property boolean $b_processed
 */
class SeoRecalcQueueItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'seo_recalc_queue';
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'pgattrs' => [
                'class' => PgAttributeBehavior::className()
            ],
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
                'value' => function() {
                    return new \yii\db\Expression('NOW()');
                },
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'ids'], 'required'],
            [['ids'], 'string'],
            [['created_at'], 'safe'],
            [['b_processed'], 'boolean'],
            [['type'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'ids' => 'Ids',
            'created_at' => 'Created At',
            'b_processed' => 'Processed',
        ];
    }
}
