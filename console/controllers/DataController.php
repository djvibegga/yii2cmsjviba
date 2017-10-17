<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use common\models\User;
use backend\modules\articles\models\Article;
use app\models\ObjectSeo;

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
            $article = new Article();
            $article->user_id = $user->id;
            $article->article_category_ids = '{}';
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
                Console::output('Test article has been created.');
                
                $article->refresh();
                
                $objectSeo = new ObjectSeo();
                $objectSeo->to_object_id = $article->object_id;
                $objectSeo->lang_id = 1;
                $objectSeo->url = 'testcategory1/article1';
                $objectSeo->type = 'article';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "en" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                }
                
                $objectSeo = new ObjectSeo();
                $objectSeo->to_object_id = $article->object_id;
                $objectSeo->lang_id = 2;
                $objectSeo->url = 'testcategory1/statya1';
                $objectSeo->type = 'article';
                if ($objectSeo->save()) {
                    Console::output('Test object seo "ru" has been created.');
                } else {
                    Console::output('Validation error: ' . VarDumper::dumpAsString($objectSeo->errors));
                }
                
                $transaction->commit();
            } else {
                Console::output(
                    'Unable to create an article because of error: ' . VarDumper::dumpAsString($article->getErrors())
                );
                $transaction->rollBack();
            }
        } else {
            Console::output(
                'Unable to create a user because of error: ' . VarDumper::dumpAsString($user->getErrors())
            );
            $transaction->rollBack();
        }
    }
}