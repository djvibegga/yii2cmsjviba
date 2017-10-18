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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
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
        $dataProvider = $this->module->get('articleManager')->getDataProvider();
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
            $model = $this->module->get('articleManager')->loadArticleById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if ($model === null) {
            throw new NotFoundHttpException('Article has not found.');
        }
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
        
        if ($model->validate() && ($result = $this->module->get('articleManager')->createArticle($model))) {
            if ($result instanceOf Article) {
                return $this->redirect(['view', 'id' => $result->id]);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'statuses' => Article::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all()
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
            try {
                if ($result = $this->module->get('articleManager')->updateArticleById($id, $model)) {
                    return $this->redirect(['view', 'id' => $id]);
                }
            } catch (\InvalidArgumentException $e) {
                throw new BadRequestHttpException();
            } catch (InvalidParamException $e) {
                throw new NotFoundHttpException($e->getMessage());
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
            $this->module->get('articleManager')->deleteArticleById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException('Article has not found.');
        }
        return $this->redirect(['index']);
    }
}
