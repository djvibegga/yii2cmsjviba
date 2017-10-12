<?php

namespace common\components;

use yii\behaviors\AttributeBehavior;
use common\models\Language;

class MetaDataBehavior extends AttributeBehavior
{
    /**
     * @var array
     */
    public $metaAttributes = ['meta'];
    
    /**
     * Sets meta data attribute from associative array
     * @param string $attribute the attribute name
     * @param array  $value     the assoc value
     * @return void
     */
    public function setMetaFromArray($attribute, $value)
    {
        $owner = $this->owner;
        $owner->setAttribute($attribute, json_encode($value));
    }
    
    /**
     * Returns meta data attribute as associative array
     * @param string $attribute the attribute name
     * @return array raw meta data
     */
    public function getMetaAsArray($attribute)
    {
        $owner = $this->owner;
        if (!empty($owner->$attribute)) {
            return json_decode($owner->getAttribute($attribute), true);
        }
    }
    
    /**
     * Returns metadata by language name
     * @param string $attribute meta data attribute name
     * @param string $lang      selected language
     * @return array metadata
     * @throws \InvalidArgumentException if meta data for selected language
     * has not found or language is not defined.
     */
    public function getMetaByLanguageName($attribute, $lang)
    {
        $rawMeta = $this->getMetaAsArray($attribute);
        $langs = Language::getList();
        if (array_search($lang, $langs) === false) {
            throw new \InvalidArgumentException(
                'Language "' . $lang . ' is not defined.'
            );
        }
        if (! isset($rawMeta[$lang])) {
            throw new \InvalidArgumentException(
                'Meta data for language "' . $lang . ' has not found.'
            );
        }
        return $rawMeta[$lang];
    }
}