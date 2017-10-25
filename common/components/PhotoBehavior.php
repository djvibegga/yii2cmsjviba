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