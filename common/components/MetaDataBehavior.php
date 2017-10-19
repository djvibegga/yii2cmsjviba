<?php

namespace common\components;

use yii\behaviors\AttributeBehavior;

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
}