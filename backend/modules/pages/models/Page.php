<?php

namespace backend\modules\pages\models;

use Yii;
use common\models\User;
use yii\behaviors\TimestampBehavior;
use common\models\ObjectRecord;
use common\interfaces\IHasSefUrl;
use common\components\caching\ICacheableDataSource;
use common\components\TranslationBehavior;
use common\CMS;
use common\components\caching\CacheAdapterFactory;

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
class Page extends ObjectRecord implements IHasSefUrl, ICacheableDataSource
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DELETED = 2;
    
    const DEFAULT_FETCH_BLOCK_SIZE = 256;
    const DEFAULT_CACHE_TIME_TO_LIVE = 5184000; //24 hours in seconds
    
    /**
     * The key value in {@link getCacheId()} gets prefixed by this or by it's class
     * name if no prefix given.
     * @var string
     */
    public $cacheIdPrefix;
    
    /**
     * Cache component used
     * @var string
     */
    public $cacheComponentName = 'memcache';
    
    /**
     * Fetch records block size (used in pull method)
     * @var integerhours
     */
    public $fetchBlockSize = self::DEFAULT_FETCH_BLOCK_SIZE;
    
    /**
     * Cache life time duration
     * @var integer
     */
    public $cacheLifeTime = self::DEFAULT_CACHE_TIME_TO_LIVE;
    
    /**
     * Unique key field name
     * @var string
     */
    public $uniqueKeyFieldName = array('url', 'lang_id');
    
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
            ],
            'translation' => [
                'class' => TranslationBehavior::className(),
                'modelClassName' => CMS::modelClass('\backend\modules\pages\models\PageInfo'),
                'foreignKey' => 'page_id'
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
     * {@inheritDoc}
     * @see \common\interfaces\IHasSefUrl::getUrlRuleClassName()
     */
    public function getUrlRuleClassName()
    {
        return 'backend\modules\pages\components\PageUrlRule';
    }
    
    /**
     * {@inheritDoc}
     * @see \common\interfaces\IHasSefUrl::getShouldRebuildSefUrl()
     */
    public function getShouldRebuildSefUrl()
    {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheComponent()
     */
    public function getCacheComponent()
    {
        return Yii::$app->get($this->cacheComponentName);
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheId()
     */
    public function getCacheId($keyValue)
    {
        if (is_array($keyValue)) {
            ksort($keyValue);
            $keyValue = serialize($keyValue);
        }
        return isset($this->cacheIdPrefix)
            ? $this->cacheIdPrefix . $keyValue
            : get_class($this) . '_' . $keyValue;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getFetchBlockSize()
     */
    public function getFetchBlockSize()
    {
        return $this->fetchBlockSize;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheLifeTime()
     */
    public function getCacheLifeTime()
    {
        return $this->cacheLifeTime;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheTableName()
     */
    public function getCacheTableName()
    {
        return self::tableName();
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getUniqueKeyField()
     */
    public function getUniqueKeyField()
    {
        return $this->uniqueKeyFieldName;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::createAdapter()
     */
    public function createAdapter(CacheAdapterFactory $factory)
    {
        return $factory->create(CacheAdapterFactory::SQL_CACHE_ADAPTER);
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::buildCriteria()
     */
    public function buildCriteria(\yii\db\Query $query, $keyValue = null)
    {
        $uniqueKeyField = $this->getUniqueKeyField();
        $query->select = array_merge(
            $query->select,
            [
                '\'' . self::tableName() . '\' as "type"',
                '"t"."id"',
                '"os".' . $uniqueKeyField[0],
                '"os".' . $uniqueKeyField[1]
            ]
        );
        $query->leftJoin('object_seo "os"', '"os".to_object_id = "t".object_id');
        if ($keyValue !== null) {
            foreach ($keyValue as $key => $value) {
                $query->andFilterWhere(['"os"."' . $key . '"' => $value]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getDbConnection()
     */
    public function getDbConnection()
    {
        return $this->getDb();
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
