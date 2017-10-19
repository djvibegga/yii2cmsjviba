<?php

namespace common\components;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\base\Model;

class MetaDataWidget extends Widget
{
    /**
     * Meta tag keywords key
     * @var string
     */
    const KEYWORDS = 'keywords';
    
    /**
     * Meta tag description key
     * @var string
     */
    const DESCRIPTION = 'description';
    
    /**
     * Meta keywords
     * @var array
     */
    private $_keywords = array();
    
    /**
     * Meta description
     * @var string
     */
    private $_description;
    
    /**
     * Processed meta title
     * @var string
     */
    private $_processedTitle;
    
    /**
     * Processed meta tags list
     * @var array
     */
    private $_processedTags;
    
    /**
     * Static meta tags
     * @var array
     */
    public $tags = array(
        array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8'),
    );
    
    /**
     * Is need add default meta-language
     * @var bool
     */
    public $needAddContentLanguage = true;
    
    /**
     * Initializes the widget
     * @return void
     */
    public function init()
    {
        parent::init();
        if ($this->needAddContentLanguage) {
            $this->tags[] = [
                'name' => 'language',
                'content' => $this->getContentLanguage()
            ];
        }
    }
    
    /**
     * Returns content language. Used in meta tags
     * @return string name of language
     */
    protected function getContentLanguage()
    {
        return Yii::$app->language;
    }
    
    /**
     * Adds meta keyworks
     * @param array|string $words meta keywords array or comma separated string
     * @return PageMetaHeader self object
     */
    public function addKeyword($words)
    {
        if (empty($words)) {
            return $this;
        }
        if (is_string($words)) {
            $words = explode(',', $words);
        }
        foreach ($words as $word) {
            $this->_keywords[] = trim($word);
        }
        return $this;
    }
    
    /**
     * Sets raw meta description
     * @param string $description meta description
     * @return \common\components\MetaDataWidget self object
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }
    
    /**
     * Returns meta description if it isn't empty
     * @return string meta description
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Sets title
     * @param strring $title title
     * @return \common\components\MetaDataWidget self object
     */
    public function setTitle($title)
    {
        Yii::$app->controller->view->title = $title;
        return $this;
    }
    
    /**
     * Sets meta data from raw array
     * @param array $value raw meta data
     * @return void
     */
    public function setDataFromMetaArray(array $value)
    {
        if (!empty($value['title'])) {
            $this->setTitle($value['title']);
        }
        if (!empty($value['description'])) {
            $this->setDescription($value['description']);
        }
        if (!empty($value['keywords'])) {
            $this->addKeyword($value['keywords']);
        }
    }
    
    /**
     * Sets meta data from the model's json attribute value
     * @param \yii\db\Model $model     the model
     * @param string        $attribute the attribute
     * @return void
     */
    public function setDataFromJsonMetaAttribute($model, $attribute)
    {
        $metaData = $model->getMetaAsArray($attribute);
        if (!empty($metaData)) {
            $this->setDataFromMetaArray($metaData);
        }
    }
    
    /**
     * Returns processed title
     * @return string title
     */
    public function getProcessedTitle()
    {
        if (!isset($this->_processedTitle)) {
            $title = $this->postProcessTitle(Yii::$app->controller->view->title);
            $this->_processedTitle = Html::encode($title);
        }
        return $this->_processedTitle;
    }
    
    /**
     * Returns post processed tags list
     * @return array post processed meta tags
     */
    public function getProcessedTags()
    {
        if (!isset($this->_processedTags)) {
            $this->_processedTags = [];
            if (!empty($this->_keywords)) {
                $content = implode(',', $this->_keywords);
                $this->_processedTags[] = array('name' => self::KEYWORDS, 'content' => $content);
            }
            if (isset($this->_description)) {
                $this->_processedTags[] = array('name' => self::DESCRIPTION, 'content' => $this->_description);
            }
            foreach ($this->_processedTags as $i => $tag) {
                $this->_processedTags[$i] = $this->postProcessTag($tag);
            }
        }
        return $this->_processedTags;
    }
    
    /**
     * Post-processes the title
     * @param string $inTitle Title to be processed
     * @return string The title. NULL means no title should be rendered
     */
    protected function postProcessTitle($inTitle)
    {
        return $inTitle;
    }
    
    /**
     * Post-processes the tag data
     * @param array $tag Tag data
     * @return array Processed tag
     */
    protected function postProcessTag($tag)
    {
        return $tag;
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Widget::run()
     */
    public function run()
    {
        foreach (array_merge($this->tags, $this->getProcessedTags()) as $tag) {
            $this->view->registerMetaTag($tag);
        }
        $title = $this->getProcessedTitle();
        Yii::$app->controller->view->title = $title;
    }
}