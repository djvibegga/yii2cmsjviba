<?php

namespace backend\modules\articles\controllers;

use Yii;
use backend\modules\articles\models\Article;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\UploadAction;
use common\models\Language;
use backend\modules\articles\models\ArticleForm;
use yii\web\BadRequestHttpException;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use backend\modules\articles\components\ArticleManager;
use yii\web\ServerErrorHttpException;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{
    /**
     * @var string
     */
    public $layout = '//admin';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => [ArticleManager::PERM_CREATE]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => [ArticleManager::PERM_LIST]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ArticleManager::PERM_VIEW,
                                ['article_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ArticleManager::PERM_UPDATE,
                                ['article_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ArticleManager::PERM_DELETE,
                                ['article_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * Returns article manager instance
     * @return ArticleManager
     */
    protected function getArticlesManager()
    {
        return $this->module->get('articleManager');
    }
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-photo' => [
                'class' => UploadAction::className(),
                'modelClass' => Article::className()
            ],
        ];
    }

    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->getArticlesManager()->getDataProvider();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        try {
            $model = $this->getArticlesManager()->loadArticleById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if ($model === null) {
            throw new NotFoundHttpException('Article has not found.');
        }
        $this->layout = '//main';
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new article.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleForm(['scenario' => 'insert']);
        $model->load(Yii::$app->request->post());
        $model->infos = Yii::$app->request->post('ArticleInfo');
        $model->meta = Yii::$app->request->post('MetaForm');
        
        if ($model->validate() && ($result = $this->getArticlesManager()->createArticle($model))) {
            if ($result instanceOf Article) {
                return $this->redirect(['view', 'id' => $result->id]);
            } else {
                $model->addErrors($result);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'statuses' => Article::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all(),
            'allCategories' => $this->loadCategories()
        ]);
    }

    /**
     * Updates an existing article.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new ArticleForm(['scenario' => 'update']);

        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());
            $model->infos = Yii::$app->request->post('ArticleInfo');
            $model->meta = Yii::$app->request->post('MetaForm');
            if ($model->validate()) {
                try {
                    if (($result = $this->getArticlesManager()->updateArticleById($id, $model)) &&
                            $result instanceOf Article) {
                                return $this->redirect(['view', 'id' => $id]);
                            } else {
                                $model->addErrors($result);
                            }
                } catch (\InvalidArgumentException $e) {
                    throw new BadRequestHttpException();
                } catch (InvalidParamException $e) {
                    throw new NotFoundHttpException($e->getMessage());
                }
            }
        } else {
            try {
                $this->module->get('articleManager')->loadArticleFormById($model, $id);
            } catch (\InvalidArgumentException $e) {
                throw new BadRequestHttpException();
            } catch (InvalidParamException $e) {
                throw new NotFoundHttpException($e->getMessage());
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            'statuses' => Article::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all(),
            'allCategories' => $this->loadCategories()
        ]);
    }

    /**
     * Deletes an existing article.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $result = $this->getArticlesManager()->deleteArticleById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException('Article has not found.');
        }
        if ($result) {
            return $this->redirect(['index']);
        } else {
            throw new ServerErrorHttpException('Unable to delete the article.');
        }
    }
    
    /**
     * Loads article categories list and group
     * into format needed especially for dropdown lists
     * @return array
     */
    protected function loadCategories()
    {
        $dataProvider = $this->module->get('categoryManager')->getDataProvider();
        $ret = [];
        foreach ($dataProvider->query->activeOnly()->all() as $category) {
            if ($category->depth == 0) {
                continue;
            }
            $ret[$category->id] = $category->name;
        }
        return $ret;
    }
}
