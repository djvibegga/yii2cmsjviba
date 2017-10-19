<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use common\models\User;
use backend\modules\articles\models\Article;
use backend\modules\articles\models\ArticleCategory;
use backend\modules\articles\models\ArticleCategoryInfo;
use common\models\Language;
use backend\modules\articles\models\ArticleInfo;
use backend\modules\pages\models\Page;
use backend\modules\pages\models\PageInfo;

class DataController extends Controller
{
    /**
     * Fills test posts
     * @param int         $amount
     * @param string|null $dateTime
     * @return void
     */
    public function actionSeed()
    {
        $transaction = Yii::$app->getDb()->beginTransaction();
        
        $user = new User();
        $user->username = 'test user 1';
        $user->email = 'testuser1@gmail.com';
        $user->generateAuthKey();
        $user->setPassword('testuser1');
        $user->generateActivationCode();
        
        if ($user->save()) {
            Console::output('Test user has been created.');
            
            $rootCategory = new ArticleCategory();
            $rootCategory->name = 'root';
            if ($rootCategory->makeRoot()->save()) {
                Console::output('Root article category has been created.');
            } else {
                Console::output(
                    'Unable to create root article category because of errors: ' .
                    VarDumper::dumpAsString($rootCategory->errors)
                );
            }
            
            $category = new ArticleCategory();
            $category->name = 'category1.1';
            if ($category->appendTo($rootCategory)->save()) {
                $category->refresh();
                
                $categoryInfo = new ArticleCategoryInfo();
                $categoryInfo->article_category_id = $category->id;
                $categoryInfo->lang_id = Language::getIdByName('en');
                $categoryInfo->url = 'categoryoneone';
                if ($categoryInfo->save()) {
                    $categoryInfo = new ArticleCategoryInfo();
                    $categoryInfo->article_category_id = $category->id;
                    $categoryInfo->lang_id = Language::getIdByName('ru');
                    $categoryInfo->url = 'categoryodinodin';
                    if ($categoryInfo->save()) {
                        Console::output('Test article category has been created.');
                    }
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $category->object_id;
                $objectSeo->lang_id = Language::getIdByName('en');
                $objectSeo->url = 'categoryoneone';
                $objectSeo->type = 'article_category';
                if ($objectSeo->save()) {
                    Console::output('Article category object seo "en" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $category->object_id;
                $objectSeo->lang_id = Language::getIdByName('ru');
                $objectSeo->url = 'categoryodinodin';
                $objectSeo->type = 'article_category';
                if ($objectSeo->save()) {
                    Console::output('Article category object seo "ru" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
                
            } else {
                Console::output(
                    'Unable to create test article category because of errors: ' .
                    VarDumper::dumpAsString($category->errors)
                );
                $transaction->rollBack();
                return;
            }
            
            
            $article = new Article();
            $article->user_id = $user->id;
            $article->article_category_ids = '{' . $category->id . '}';
            $article->name = 'test article 1';
            $article->setPhotoAttribute('photo', [
                'name' => 'Картинка 1.jpg',
                'path' => 'path/to/image.jpg',
                'size' => 10000,
                'created_at' => 132367233,
                'formats' => [
                    'big' => [
                        'path' => 'path/to/image_big.jpg',
                        'size' => 8096
                    ],
                    'medium' => [
                        'path' => 'path/to/image_medium.jpg',
                        'size' => 5078
                    ]
                ]
            ]);
            if ($article->save()) {
                
                $article->refresh();
                
                $articleInfo = new ArticleInfo();
                $articleInfo->title = 'article1';
                $articleInfo->article_id = $article->id;
                $articleInfo->lang_id = Language::getIdByName('en');
                if ($articleInfo->save()) {
                    $articleInfo = new ArticleInfo();
                    $articleInfo->title = 'statya1';
                    $articleInfo->article_id = $article->id;
                    $articleInfo->lang_id = Language::getIdByName('ru');
                    if ($articleInfo->save()) {
                        Console::output('Test article has been created.');
                    } else {
                        $transaction->rollBack();
                        return;
                    }
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $article->object_id;
                $objectSeo->lang_id = 1;
                $objectSeo->url = 'categoryoneone/article1';
                $objectSeo->type = 'article';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "en" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $article->object_id;
                $objectSeo->lang_id = 2;
                $objectSeo->url = 'categoryodinodin/statya1';
                $objectSeo->type = 'article';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "ru" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
            } else {
                Console::output(
                    'Unable to create an article because of error: ' . VarDumper::dumpAsString($article->getErrors())
                );
                $transaction->rollBack();
            }
            
            $page = new Page();
            $page->user_id = $user->id;
            $page->name = 'test page 1';
            if ($page->save()) {
                $page->refresh();
                
                $pageInfo = new PageInfo();
                $pageInfo->title = 'page1';
                $pageInfo->url = 'page1';
                $pageInfo->page_id = $page->id;
                $pageInfo->lang_id = Language::getIdByName('en');
                if ($pageInfo->save()) {
                    $pageInfo = new PageInfo();
                    $pageInfo->title = 'stranitsa1';
                    $pageInfo->url = 'stranitsa1';
                    $pageInfo->page_id = $page->id;
                    $pageInfo->lang_id = Language::getIdByName('ru');
                    if ($pageInfo->save()) {
                        Console::output('Test page has been created.');
                    } else {
                        $transaction->rollBack();
                        return;
                    }
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $article->object_id;
                $objectSeo->lang_id = 1;
                $objectSeo->url = 'pages/page1';
                $objectSeo->type = 'page';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "en" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
                
                $objectSeo = new \common\models\ObjectSeo();
                $objectSeo->to_object_id = $page->object_id;
                $objectSeo->lang_id = 2;
                $objectSeo->url = 'pages/stranitsa1';
                $objectSeo->type = 'page';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "ru" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                    $transaction->rollBack();
                    return;
                }
            } else {
                Console::output(
                    'Unable to create a page because of error: ' . VarDumper::dumpAsString($page->getErrors())
                );
                $transaction->rollBack();
            }
            
            $transaction->commit();
            
        } else {
            Console::output(
                'Unable to create a user because of error: ' . VarDumper::dumpAsString($user->getErrors())
            );
            $transaction->rollBack();
        }
    }
}