<?php

namespace backend\modules\articles\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use paulzi\nestedsets\NestedSetsBehavior;
use paulzi\nestedsets\NestedSetsQueryTrait;
use common\components\TranslationBehavior;
use common\CMS;
use common\models\ObjectRecord;
use common\interfaces\IHasSefUrl;
use common\components\caching\CacheAdapterFactory;
use common\components\caching\ICacheableDataSource;

/**
 * This is the model class for table "article_category".
 *
 * @property integer $id
 * @property integer $tree
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property integer $object_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 */
class ArticleCategory extends ObjectRecord implements IHasSefUrl, ICacheableDataSource
{
    const STATUS_ACTIVE = 0;
    const STATUS_DELETED = 1;
    
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
        return 'article_category';
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
            'nestedset' => [
                'class' => NestedSetsBehavior::className(),
                //'treeAttribute' => 'tree',
            ],
            'translation' => array(
                'class' => TranslationBehavior::className(),
                'modelClassName' => CMS::modelClass('\backend\modules\articles\models\ArticleCategoryInfo'),
                'foreignKey' => 'article_category_id'
            ),
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\db\ActiveRecord::transactions()
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree', 'lft', 'rgt', 'depth'], 'integer'],
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 64],
            ['status', 'in', 'range' => array_keys(self::getAvailableStatuses())]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tree' => 'Tree',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'depth' => 'Depth',
            'object_id' => 'Object ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \common\interfaces\IHasSefUrl::getUrlRuleClassName()
     */
    public function getUrlRuleClassName()
    {
        return 'backend\modules\articles\components\CategoryUrlRule';
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
                '\'article_category\' as "type"',
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
     * Returns available statuses map
     * @return string[]
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted')
        ];
    }
    
    /**
     * @return \backend\modules\articles\models\ArticleCategoryQuery
     */
    public static function find()
    {
        return new ArticleCategoryQuery(get_called_class());
    }
}

class ArticleCategoryQuery extends \yii\db\ActiveQuery
{
    use NestedSetsQueryTrait;
    
    /**
     * @return \backend\modules\articles\models\ArticleCategoryQuery
     */
    public function activeOnly()
    {
        return $this->andWhere(['status' => ArticleCategory::STATUS_ACTIVE]);
    }
}
