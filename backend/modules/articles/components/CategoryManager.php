<?php

namespace backend\modules\articles\components;

use Yii;
use common\models\Language;
use yii\data\ActiveDataProvider;
use yii\base\InvalidParamException;
use backend\modules\articles\models\ArticleCategory;
use backend\modules\articles\models\ArticleCategoryForm;
use backend\modules\articles\models\ArticleCategoryInfo;

class CategoryManager extends \common\components\Component
{
    const PERM_CREATE = 'categoryCreate';
    const PERM_UPDATE = 'categoryUpdate';
    const PERM_DELETE = 'categoryDelete';
    const PERM_LIST = 'categoryList';
    const PERM_VIEW = 'categoryView';
    
    /**
     * Returns built data provider to fetch list of articles
     * @param array $params request parameters
     * @return \backend\modules\articles\components\ActiveDataProvider
     */
    public function getDataProvider(array $params = [])
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ArticleCategory::find(),
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];
        return $dataProvider;
    }
    
    /**
     * Loads article category by it's id
     * @param int $articleCategoryId the article category id
     * @throws \InvalidArgumentException if article category id is invalid
     * @return \backend\modules\articles\models\ArticleCategory loaded article category record
     */
    public function loadCategoryById($articleCategoryId)
    {
        try {
            $articleCategoryId = self::toPositiveInt($articleCategoryId);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Article category id is invalid.');
        }
        try {
            return ArticleCategory::findOne($articleCategoryId);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to find the article category because of db error: ' . $e->getMessage());
        }
    }
    
    /**
     * Loads data from the source article into article data form
     * @param ArticleCategoryForm $model             the target to load into
     * @param int                 $articleCategoryId the article category id to load from
     * @return void
     * @throws \InvalidArgumentException if the category id is invalid
     * @throws InvalidParamException     if the category has not found
     */
    public function loadCategoryFormById(ArticleCategoryForm $model, $articleCategoryId)
    {
        if (! $category = $this->loadCategoryById($articleCategoryId)) {
            throw new InvalidParamException('Category has not found.');
        }
        $model->setAttributes($category->getAttributes());
        $model->id = $articleCategoryId;
        $model->parent_id = $category->getParent()->one()->id;
        $langs = Language::getList();
        $existingInfos = [];
        foreach ($category->infos as $info) {
            $existingInfos[$langs[$info->lang_id]] = $info;
        }
        foreach ($langs as $id => $name) {
            $infoAttributes = isset($existingInfos[$name])
                ? $existingInfos[$name]->getAttributes(
                      ['url']
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
     * Deletes the article category by it's id
     * @param int $articleCategoryId the article category id
     * @throws InvalidParamException if article category has not found
     * @return boolean whether operation has successfully completed
     */
    public function deleteCategoryById($articleCategoryId)
    {
        if (! $category = $this->loadCategoryById($articleCategoryId)) {
            throw new InvalidParamException('Category has not found.');
        }
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($category->delete()) {
                $urlManager = Yii::$app->urlManager;
                $urlManager->clearUrlCache($category);
                $urlManager->deleteObjectSeoByObjectId($category->object_id);
                ArticleCategoryInfo::deleteAll(['article_category_id' => $category->id]);
                $transaction->commit();
                return true;
            }
        } catch (\yii\db\Exception $e) {
            Yii::error(
                'Unable to delete the article category. ID: ' . $category->id .
                '. Cause is a db error: ' . $e->getMessage()
            );
        }
        $transaction->rollBack();
        return false;
    }
    
    /**
     * Creates a new article category.
     * @param ArticleCategoryForm $from article category data form
     * @return ArticleCategory|array article category record on success,
     * array of errors otherwise
     */
    public function createCategory(ArticleCategoryForm $form)
    {
        if ($form->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        $parentNode = ArticleCategory::findOne($form->parent_id);
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $category = new ArticleCategory();
            $category->attributes = $form->getAttributes(['name', 'status']);
            if (! $category->appendTo($parentNode)->save()) {
                $transaction->rollBack();
                return [
                    'ArticleCategory' => $category->getErrors()
                ];
            }
            
            foreach ($form->infos as $lang => $infoAttributes) {
                $categoryInfo = new ArticleCategoryInfo();
                $categoryInfo->attributes = $infoAttributes;
                $categoryInfo->article_category_id = $category->id;
                $categoryInfo->setMetaFromArray(
                    'meta',
                    isset($form->meta[$lang])
                        ? $form->meta[$lang]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (($langId = array_search($lang, $langs)) === false) {
                    $transaction->rollBack();
                    return [
                        'infos[' . $lang . '][lang_id]' => 'Unknown language name.'
                    ];
                }
                $categoryInfo->lang_id = $langId;
                if (! $categoryInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($categoryInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $lang . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            $category->refresh();
            Yii::$app->urlManager->buildSefUrl($category);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to create an article category because of db error: ' . $e->getMessage());
        }
        
        $transaction->commit();
        return $category;
    }
    
    /**
     * Updates the article category and it's related data regarding
     * to the data set in the article category form object
     * @param int                 $articleCategoryId the article category id
     * @param ArticleCategoryForm $model             the article category model
     * @return bool whether operation has successfully completed
     * @throws \InvalidArgumentException if the article category id is invalid
     * @throws InvalidParamException     if the article category has not found
     */
    public function updateCategoryById($articleCategoryId, ArticleCategoryForm $model)
    {
        if ($model->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        $parentNode = ArticleCategory::findOne($model->parent_id);
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        $category = $this->loadCategoryById($articleCategoryId);
        if (! $category) {
            throw new InvalidParamException('Article category has not found.');
        }
        
        try {
            $category->attributes = $model->attributes;
            if ($category->getParent()->one()->id != $parentNode->id) {
                $category->appendTo($parentNode);
            }
            if (! $category->save()) {
                $transaction->rollBack();
                return [
                    'ArticleCategory' => $category->getErrors()
                ];
            }
            
            $existingInfos = [];
            foreach ($category->infos as $categoryInfo) {
                $existingInfos[$langs[$categoryInfo->lang_id]] = $categoryInfo;
            }
            
            foreach ($model->infos as $lang => $infoAttributes) {
                if (isset($existingInfos[$lang])) {
                    $categoryInfo = $existingInfos[$lang];
                } else {
                    $categoryInfo = new ArticleCategoryInfo();
                    $categoryInfo->lang_id = array_search($lang, $langs);
                    $categoryInfo->article_category_id = $category->id;
                }
                $categoryInfo->attributes = $infoAttributes;
                $categoryInfo->setMetaFromArray(
                    'meta',
                    isset($model->meta[$lang])
                        ? $model->meta[$lang]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (! $categoryInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($categoryInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $lang . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            Yii::$app->urlManager->buildSefUrl($category);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to update the article category because of db error: ' . $e->getMessage());
        }
        
        $transaction->commit();
        return $category;
    }
}