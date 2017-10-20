<?php

namespace backend\modules\pages\models;

use Yii;
use yii\base\Model;
use common\models\Language;

class PageForm extends Model
{
    public $id;
    public $name;
    public $status;
    public $infos = [];
    public $meta = [];
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $pageInfo = new PageInfo();
        foreach (Language::getList() as $id => $name) {
            $this->infos[$name] = $pageInfo->getAttributes(null, ['page_id', 'lang_id']);
        }
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
                'targetClass' => '\backend\modules\pages\models\Page',
                'message' => Yii::t('app', 'This page name has already been taken.')
            ],
            
            ['status', 'in', 'range' => array_keys(Page::getAvailableStatuses())],
            [['infos', 'meta'], 'each', 'rule' => ['safe']],
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [
            'insert' => ['name', 'status', 'infos', 'meta'],
            'update' => ['name', 'status', 'infos', 'meta']
        ];
    }
}