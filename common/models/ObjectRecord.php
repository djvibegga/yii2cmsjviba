<?php

namespace common\models;

use Yii;

/**
 * This is the basic model class for all object-based records
 *
 * @property integer $id
 * @property string  $object_id
 */
class ObjectRecord extends \yii\db\ActiveRecord
{
    /**
     * Finds record by given object ID
     * @param int $objectId object unique ID
     * @return YObjectRecord
     * @static
     */
    public static function findOneByObjectId($objectId)
    {
        $attributes = ['object_id' => $objectId];
        return static::findOne($attributes);
    }
}