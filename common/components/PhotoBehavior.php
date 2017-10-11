<?php

namespace common\components;

use yii\behaviors\AttributeBehavior;

class PhotoBehavior extends AttributeBehavior
{
    /**
     * @var array
     */
    public $photoAttributes = [];
    
    /**
     * Storage base path
     * @var string
     */
    public $storageBasePath;
    
    /**
     * Storage base url
     * @var string
     */
    public $storageBaseUrl;
    
    /**
     * Formats configuration.
     * For example:
     * [
     *     'small' => [
     *         'width' => 120
     *     ],
     *     'medium' => [
     *         'width' => 250
     *     ],
     *     'big' => [
     *         'width' => 400
     *     ],
     *     ...
     * ]
     * @var array
     */
    public $formats = [];
    
    /**
     * Sets photo attribute from associative array
     * @param string $attribute the attribute name
     * @param array  $value     the assoc value
     * @return void
     */
    public function setPhotoAttribute($attribute, $value)
    {
        $owner = $this->owner;
        $owner->setAttribute($attribute, json_encode($value));
    }
    
    /**
     * Returns photo attribute
     * @param string $attribute the attribute name
     * @return string
     */
    public function getPhotoAttribute($attribute)
    {
        $owner = $this->owner;
        if (!empty($owner->$attribute)) {
            return json_decode($owner->getAttribute($attribute), true);
        }
    }
}