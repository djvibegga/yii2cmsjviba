<?php

namespace common\models;

use Yii;
use common\interfaces\IHasSefUrl;
use common\CMS;
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
    
    /**
     * {@inheritDoc}
     * @see \yii\db\BaseActiveRecord::afterSave()
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this instanceof IHasSefUrl) {
            Yii::$app->urlManager->buildSefUrl($this);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\db\BaseActiveRecord::afterDelete()
     */
    public function afterDelete()
    {
        if ($this instanceof IHasSefUrl) {
            $urlManager = Yii::$app->urlManager;
            $urlManager->clearRuleCache($this);
            $urlManager->deleteObjectSeoByObjectId($this->object_id);
        }
        parent::afterDelete();
    }
}