<?php

namespace common\components;

use yii\base\Behavior;

class PgAttributeBehavior extends Behavior
{
    /**
     * Sets json attribute value from array (only numeric values)
     * @param string $attribute the attribute name
     * @param array  $value     the attribute value
     * @return array
     */
    public function setJsonAttributeFromArray($attribute, $value)
    {
        $this->owner->$attribute = '{' . implode(',', $value) . '}';
    }
    
    /**
     * Sets json attribute value from json array
     * @param string $attribute the attribute name
     * @param array  $value     the attribute value
     * @return array
     */
    public function setJsonAttributeFromJson($attribute, $value)
    {
        $this->owner->$attribute = empty($value) ? '{}' : json_encode($value);
    }
    
    /**
     * Returns json attribute value as array 
     * @param string $attribute the attribute name
     * @return array
     */
    public function getJsonAttributeAsArray($attribute)
    {
        $raw = $this->owner->$attribute;
        $str = trim($raw, '{}');
        if (empty($str)) {
            return [];
        }
        if (strpos($str, '{') === false) {
            return explode(',', $str);
        } else {
            return json_decode($raw, true);
        }
    }
}