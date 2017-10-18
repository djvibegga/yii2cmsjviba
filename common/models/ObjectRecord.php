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
    
    /**
     * Force change SEF url part for object.
     * For all dependent entities SEF url should be recalculated
     * @param string $url new SEF url part
     * @return boolean whether operation success
     */
    public function changeSEFUrlPart($url)
    {
        $oldUrl = $this->url;
        $this->url = $url;
        if ($this->save()) {
            $this->afterChangeSEFUrlPart($oldUrl, $url);
            return true;
        }
        return false;
    }

    /**
     * @param unknown $oldUrl
     * @param unknown $newUrl
     */
    protected function afterChangeSEFUrlPart($oldUrl, $newUrl)
    {
        $sourceObject = $this->object;
        $entityClassNames = Yii::$app->get('urlManager')
            ->resolveDependentEntities($sourceObject->type);
        $event = new \yii\base\Event(
            $this,
            compact('sourceObject', 'oldUrl', 'newUrl')
        );
        foreach ($entityClassNames as $className) {
            CMS::model('new', $className)->onAfterChangeSEFUrlPart($event);
        }
    }
}