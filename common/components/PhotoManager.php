<?php

namespace common\components;

use yii\db\ActiveRecord;

class PhotoManager extends Component
{
    /**
     * Checks whether attribute is declared
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute the attribute name
     * @return bool
     */
    public function isAttributeDeclared(ActiveRecord $record, $attribute)
    {
        $behaviors = $record->behaviors();
        foreach ($behaviors as $id => $behavior) {
            $behavior = $record->getBehavior($id);
            if ($behavior instanceof PhotoBehavior && in_array($attribute, $behavior->photoAttributes)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns photo property of given format
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute attribute name
     * @param string       $format    photo format name
     * @return string photo property value
     */
    public function getPhotoProperty(ActiveRecord $record, $attribute, $property, $format = 'origin')
    {
        if (!$this->isAttributeDeclared($record, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $data = $record->getPhotoAttribute($attribute);
        if ($format == 'origin') {
            if (isset($data[$property])) {
                return $data[$property];
            } else {
                throw new \InvalidArgumentException('Property "' . $property . '" is undefined.');
            }
        } else {
            if (isset($data['formats'][$format])) {
                if (isset($data['formats'][$format][$property])) {
                    return $data['formats'][$format][$property];
                } else {
                    throw new \InvalidArgumentException('Property "' . $format . '" is undefined.');
                }
            } else {
                throw new \InvalidArgumentException('Photo format "' . $format . '" is undefined.');
            }
        }
    }
    
    /**
     * Returns photo file relative path of given format
     * @param ActiveRecord $record the photo record
     * @param string $attribute    attribute name
     * @param string $format       photo format name
     * @return string
     */
    public function getPhotoRelativePath(ActiveRecord $record, $attribute, $format = 'origin')
    {
        return $this->getPhotoProperty($record, $attribute, 'path', $format);
    }
    
    /**
     * Returns photo file absolute path of given format
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute attribute name
     * @param string       $format    photo format name
     * @return string
     */
    public function getPhotoAbsolutePath(ActiveRecord $record, $attribute, $format = 'origin')
    {
        $relativePath = $this->getPhotoRelativePath($attribute, $format);
        return rtrim($record->storageBasePath) . '/' . ltrim($relativePath);
    }
    
    /**
     * Returns photo file absolute path of given format
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute attribute name
     * @param string       $format    photo format name
     * @return string
     */
    public function getPhotoUrl(ActiveRecord $record, $attribute, $format = 'origin')
    {
        $relativePath = $this->getPhotoRelativePath($record, $attribute, $format);
        return rtrim($record->storageBaseUrl) . '/' . ltrim($relativePath);
    }
    
    /**
     * Returns photo size of given photo format
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute photo attribute
     * @param string       $format    photo format
     * @return string photo size
     */
    public function getPhotoSize(ActiveRecord $record, $attribute, $format = 'origin')
    {
        return $this->getPhotoProperty($record, $attribute, 'size', $format);
    }
    
    /**
     * Returns photo url of given format
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute the attribute name
     * @return string
     */
    public function getPhotoName(ActiveRecord $record, $attribute)
    {
        if (!$this->isAttributeDeclared($record, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $value = $record->$attribute;
        return $value['name'];
    }
    
    /**
     * Returns data creation of the photo
     * @param ActiveRecord $record    the photo record
     * @param string       $attribute the attribute name
     * @return string
     */
    public function getCreatedAt(ActiveRecord $record, $attribute)
    {
        if (!$this->isAttributeDeclared($record, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $value = $record->$attribute;
        return $value['created_at'];
    }
}