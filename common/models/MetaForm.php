<?php

namespace common\models;

use yii\base\Model;

class MetaForm extends Model
{
    /**
     * Title
     * @var string
     */
    public $title;
    
    /**
     * Description
     * @var string
     */
    public $description;
    
    /**
     * Keywords
     * @var string
     */
    public $keywords;
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::rules()
     */
    public function rules()
    {
        return [
            [['title', 'description', 'keywords'], 'string']
        ];
    }
}