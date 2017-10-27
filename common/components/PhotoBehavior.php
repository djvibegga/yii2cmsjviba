<?php

namespace common\components;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\base\Event;

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
        $owner->$attribute = json_encode($value);
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
            return json_decode($owner->$attribute, true);
        }
    }
    
    /**
     * Sets photo attribute
     * @param string|array $value photo data or photo data encoded via base64
     * @return void
     */
    public function setPhotoAttributeWise($attr, $value)
    {
        if (is_string($value)) {
            $data = base64_encode(base64_decode($value));
            if ($data === $value) {
                $this->owner->setAttribute(
                    $attr,
                    empty($value) ? '{}' : base64_decode($value)
                );
            } else {
                $this->owner->setAttribute($attr, $value);
            }
        } elseif (is_array($value)) {
            $this->owner->setAttribute($attr, json_encode($value));
        } else if ($value === null) {
            $this->owner->setAttribute($attr, '{}');
        } else {
            throw new \InvalidArgumentException('Value has bad format.');
        }
    }
    
    /**
     * Deletes all attached files
     * @return void
     */
    public function deleteAllAttachedFiles()
    {
        foreach ($this->photoAttributes as $attribute) {
            $this->deleteAttachedFiles($attribute);
        }
    }
    
    /**
     * Deletes files attached only for given attribute
     * @param string $attribute the attribute name
     * @return void
     */
    public function deleteAttachedFiles($attribute)
    {
        $photoManager = Yii::$app->photoManager;
        try {
            $path = $photoManager->getPhotoAbsolutePath($this->owner, $attribute);
            @unlink($path);
        } catch (\InvalidArgumentException $e) {
            //comment: do nothing because there are no attached photos
        }
        foreach ($this->formats as $name => $config) {
            try {
                $path = $photoManager->getPhotoAbsolutePath($this->owner, $attribute, $name);
                @unlink($path);
            } catch (\InvalidArgumentException $e) {
                //comment: do nothing because there are no attached photos
            }
        }
    }
}