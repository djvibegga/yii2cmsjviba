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
        return [
            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'max' => 255],
            [
                'name', 'unique',
                'targetClass' => '\backend\modules\articles\models\Article',
                'message' => Yii::t('app', 'This article name has already been taken.')
            ],
            
            ['status', 'in', 'range' => array_keys(Article::getAvailableStatuses())],
            ['infos', 'each', 'rule' => ['safe']],
            ['photo', 'safe']
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [
            'insert' => ['name', 'status', 'photo', 'infos'],
            'update' => ['name', 'status', 'photo', 'infos']
        ];
    }
}