<?php

namespace common\components;

use yii\base\Model;

class PhotoManager extends Component
{
    /**
     * Checks whether attribute is declared
     * @param Model  $model    the photo model
     * @param string $attribute the attribute name
     * @return bool
     */
    public function isAttributeDeclared(Model $model, $attribute)
    {
        $behaviors = $model->behaviors();
        foreach ($behaviors as $id => $behavior) {
            $behavior = $model->getBehavior($id);
            if ($behavior instanceof PhotoBehavior && in_array($attribute, $behavior->photoAttributes)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns photo property of given format
     * @param Model  $model     the photo model
     * @param string $attribute attribute name
     * @param string $format    photo format name
     * @return string photo property value
     */
    public function getPhotoProperty(Model $model, $attribute, $property, $format = 'origin')
    {
        if (!$this->isAttributeDeclared($model, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $data = $model->getPhotoAttribute($attribute);
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
     * @param Model  $model     the photo model
     * @param string $attribute attribute name
     * @param string $format    photo format name
     * @return string
     */
    public function getPhotoRelativePath(Model $model, $attribute, $format = 'origin')
    {
        return $this->getPhotoProperty($model, $attribute, 'path', $format);
    }
    
    /**
     * Returns photo file absolute path of given format
     * @param Model  $model     the photo model
     * @param string $attribute attribute name
     * @param string $format    photo format name
     * @return string
     */
    public function getPhotoAbsolutePath(Model $model, $attribute, $format = 'origin')
    {
        $relativePath = $this->getPhotoRelativePath($model, $attribute, $format);
        return rtrim($model->storageBasePath) . '/' . ltrim($relativePath);
    }
    
    /**
     * Returns photo file absolute path of given format
     * @param Model  $model     the photo model
     * @param string $attribute attribute name
     * @param string $format    photo format name
     * @return string|false
     */
    public function getPhotoUrl(Model $model, $attribute, $format = 'origin')
    {
        try {
            $relativePath = $this->getPhotoRelativePath($model, $attribute, $format);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return rtrim($model->storageBaseUrl) . '/' . ltrim($relativePath);
    }
    
    /**
     * Returns photo size of given photo format
     * @param Model  $model     the photo model
     * @param string $attribute photo attribute
     * @param string $format    photo format
     * @return string photo size
     */
    public function getPhotoSize(Model $model, $attribute, $format = 'origin')
    {
        return $this->getPhotoProperty($model, $attribute, 'size', $format);
    }
    
    /**
     * Returns photo url of given format
     * @param Model  $model     the photo model
     * @param string $attribute the attribute name
     * @return string
     */
    public function getPhotoName(Model $model, $attribute)
    {
        if (!$this->isAttributeDeclared($model, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $value = $model->$attribute;
        return $value['name'];
    }
    
    /**
     * Returns data creation of the photo
     * @param Model  $model     the photo model
     * @param string $attribute the attribute name
     * @return string
     */
    public function getCreatedAt(Model $model, $attribute)
    {
        if (!$this->isAttributeDeclared($model, $attribute)) {
            throw new \InvalidArgumentException('Attribute "' . $attribute . '" is undeclared as photo attribute.');
        }
        $value = $model->$attribute;
        return $value['created_at'];
    }
}