<?php

namespace backend\modules\pages\components;

use Yii;
use backend\modules\pages\models\Page;
use backend\modules\pages\models\PageForm;
use backend\modules\pages\models\PageInfo;
use common\models\Language;
use yii\data\ActiveDataProvider;
use yii\base\InvalidParamException;

class PageManager extends \common\components\Component
{
    const PERM_CREATE = 'pageCreate';
    const PERM_UPDATE = 'pageUpdate';
    const PERM_DELETE = 'pageDelete';
    const PERM_LIST = 'pageList';
    const PERM_VIEW = 'pageView';
    
    /**
     * Returns built data provider to fetch list of pages
     * @param array $params request parameters
     * @return @return \yii\data\ActiveDataProvider
     */
    public function getDataProvider(array $params = [])
    {
        return new ActiveDataProvider([
            'query' => Page::find(),
        ]);
    }
    
    /**
     * Loads page by it's id
     * @param int $pageId the page id
     * @throws \InvalidArgumentException if page id is invalid
     * @return \backend\modules\pages\models\Page loaded page record
     */
    public function loadPageById($pageId)
    {
        try {
            $pageId = self::toPositiveInt($pageId);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Page id is invalid.');
        }
        try {
            return Page::findOne($pageId);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to find the page because of db error: ' . $e->getMessage());
        }
    }
    
    /**
     * Loads data from the source page into page data form
     * @param PageForm $model   the target to load into
     * @param int         $page the page id to load from
     * @return void
     * @throws \InvalidArgumentException if the page id is invalid
     * @throws InvalidParamException     if the page has not found
     */
    public function loadPageFormById(PageForm $model, $pageId)
    {
        if (! $page = $this->loadPageById($pageId)) {
            throw new InvalidParamException('Page has not found.');
        }
        $model->setAttributes($page->attributes);
        $model->id = $pageId;
        $langs = Language::getList();
        $existingInfos = [];
        foreach ($page->infos as $info) {
            $existingInfos[$langs[$info->lang_id]] = $info;
        }
        foreach ($langs as $id => $name) {
            $infoAttributes = isset($existingInfos[$name])
                ? $existingInfos[$name]->getAttributes(
                      ['title', 'url', 'teaser', 'text']
                  )
                : [];
            $model->infos[$name] = $infoAttributes;
            $model->meta[$name] = isset($existingInfos[$name])
                ? $existingInfos[$name]->getMetaAsArray('meta')
                : [
                    'title' => '',
                    'description' => '',
                    'keywords'
                ];
        }
    }
    
    /**
     * Deletes the page by it's id
     * @param int $pageId the page id
     * @return boolean whether operation has successfully completed
     * @throws InvalidParamException if page has not found
     */
    public function deletePageById($pageId)
    {
        if (! $page = $this->loadPageById($pageId)) {
            throw new InvalidParamException('Page has not found.');
        }
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($page->delete()) {
                $urlManager = Yii::$app->urlManager;
                $urlManager->clearUrlCache($page);
                $urlManager->deleteObjectSeoByObjectId($page->object_id);
                PageInfo::deleteAll(['page_id' => $page->id]);
                $transaction->commit();
                return true;
            }
        } catch (\yii\db\Exception $e) {
            Yii::error(
                'Unable to delete the page. ID: ' . $page->id .
                '. Cause is a db error: ' . $e->getMessage()
            );
        }
        $transaction->rollBack();
        return false;
    }
    
    /**
     * Creates a new page.
     * @param PageForm $from page data form
     * @return Page|array page record on success,
     * array of errors otherwise
     */
    public function createPage(PageForm $form)
    {
        if ($form->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $page = new Page();
            $page->attributes = $form->attributes;
            $page->user_id = Yii::$app->user->getId();
            if (! $page->save()) {
                $transaction->rollBack();
                return [
                    'Page' => $page->getErrors()
                ];
            }
            
            foreach ($form->infos as $lang => $infoAttributes) {
                $pageInfo = new PageInfo();
                $pageInfo->attributes = $infoAttributes;
                $pageInfo->page_id = $page->id;
                if (($langId = array_search($lang, $langs)) === false) {
                    $transaction->rollBack();
                    return [
                        'infos[' . $lang . '][lang_id]'  => ['Unknown language name.']
                    ];
                }
                $pageInfo->lang_id = $langId;
                $pageInfo->setMetaFromArray(
                    'meta',
                    isset($form->meta[$lang])
                        ? $form->meta[$lang]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (! $pageInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($pageInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $lang . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            $page->refresh();
            Yii::$app->urlManager->buildSefUrl($page);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to create an page because of db error: ' . $e->getMessage());
        }
        
        $transaction->commit();
        return $page;
    }
    
    /**
     * Updates the page and it's related data regarding
     * to the data set in the page form object
     * @param int      $pageId the page id
     * @param PageForm $model  the page model
     * @return bool whether operation has successfully completed
     * @throws \InvalidArgumentException if the page id is invalid
     * @throws InvalidParamException     if the page has not found
     */
    public function updatePageById($pageId, PageForm $model)
    {
        if ($model->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        $page = $this->loadPageById($pageId);
        if (! $page) {
            throw new InvalidParamException('Page has not found.');
        }
        
        try {
            $page->attributes = $model->attributes;
            if (! $page->save()) {
                $transaction->rollBack();
                return [
                    'Page' => $page->getErrors()
                ];
            }
            
            $existingInfos = [];
            foreach ($page->infos as $pageInfo) {
                $existingInfos[$langs[$pageInfo->lang_id]] = $pageInfo;
            }
            
            foreach ($model->infos as $lang => $infoAttributes) {
                if (isset($existingInfos[$lang])) {
                    $pageInfo = $existingInfos[$lang];
                } else {
                    $pageInfo = new PageInfo();
                    $pageInfo->lang_id = array_search($lang, $langs);
                    $pageInfo->page_id = $page->id;
                }
                $pageInfo->attributes = $infoAttributes;
                $pageInfo->setMetaFromArray(
                    'meta',
                    isset($model->meta[$lang])
                        ? $model->meta[$lang]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (! $pageInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($pageInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $lang . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            Yii::$app->urlManager->buildSefUrl($page);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to update the page because of db error: ' . $e->getMessage());
        }
        
        $transaction->commit();
        return $page;
    }
}