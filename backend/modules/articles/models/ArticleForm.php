<?php

namespace backend\modules\articles\models;

use Yii;
use yii\base\Model;
use common\models\Language;
use common\components\PhotoBehavior;

class ArticleForm extends Model
{
    public $id;
    public $name;
    public $status;
    public $photo;
    public $infos = [];
    public $meta = [];
    public $categories = [];
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $articleInfo = new ArticleInfo();
        foreach (Language::getList() as $id => $name) {
            $this->infos[$name] = $articleInfo->getAttributes(null, ['article_id', 'lang_id']);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'photos' => [
                'class' => PhotoBehavior::className(),
                'photoAttributes' => ['photo'],
                'storageBasePath' => Yii::getAlias('@backend/web') . '/upload/photos',
                'storageBaseUrl' => '/upload/photos'
            ]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $form = $this;
        return [
            ['name', 'trim'],
            [['name', 'categories'], 'required'],
            ['name', 'string', 'max' => 255],
            [
                'name', 'unique',
                'targetClass' => '\backend\modules\articles\models\Article',
                'message' => Yii::t('app', 'This article name has already been taken.'),
                'filter' => function ($query) use ($form) {
                    if (! empty($form->id)) {
                        $query->andWhere('id != :id', [':id' => $this->id]);
                    }
                },
            ],
            
            ['status', 'in', 'range' => array_keys(Article::getAvailableStatuses())],
            [['infos', 'meta'], 'each', 'rule' => ['safe']],
            ['categories', 'each', 'rule' => ['integer']],
            ['photo', 'safe'],
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [
            'insert' => ['name', 'status', 'photo', 'infos', 'categories', 'meta'],
            'update' => ['name', 'status', 'photo', 'infos', 'categories', 'meta']
        ];
    }
}