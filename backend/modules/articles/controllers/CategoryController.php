<?php

namespace backend\modules\articles\controllers;

use Yii;
use backend\modules\articles\models\ArticleCategory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use backend\modules\articles\models\ArticleCategoryForm;
use common\models\Language;

/**
 * ArticleCategoryController implements the CRUD actions for ArticleCategory model.
 */
class CategoryController extends Controller
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
     * Lists all ArticleCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->module->get('categoryManager')->getDataProvider();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single article category.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        try {
            $model = $this->module->get('categoryManager')->loadCategoryById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if ($model === null) {
            throw new NotFoundHttpException('Article category has not found.');
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new article category.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleCategoryForm(['scenario' => 'insert']);
        $model->load(Yii::$app->request->post());
        $model->infos = Yii::$app->request->post('ArticleCategoryInfo', []);
        
        if ($model->validate() && ($result = $this->module->get('categoryManager')->createCategory($model))) {
            if ($result instanceOf ArticleCategory) {
                return $this->redirect(['view', 'id' => $result->id]);
            } else {
                $model->addErrors($result);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'statuses' => ArticleCategory::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all(),
            'parentItems' => $this->getCategoryDropdownList(),
        ]);
    }

    /**
     * Updates an existing article category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new ArticleCategoryForm(['scenario' => 'update']);
        
        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());
            $model->infos = Yii::$app->request->post('ArticleCategoryInfo', []);
            if (($result = $this->module->get('categoryManager')->updateCategoryById($id, $model)) &&
                $result instanceof ArticleCategory
            ) {
                return $this->redirect(['view', 'id' => $id]);
            } else {
                $model->addErrors($result);
            }
        } else {
            $this->module->get('categoryManager')->loadCategoryFormById($model, $id);
        }
        
        return $this->render('update', [
            'model' => $model,
            'statuses' => ArticleCategory::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all(),
            'parentItems' => $this->getCategoryDropdownList(),
        ]);
    }

    /**
     * Deletes an existing article category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $this->module->get('categoryManager')->deleteCategoryById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException('Article category has not found.');
        }

        return $this->redirect(['index']);
    }
    
    /**
     * Returns category dropdown list items
     * @return array
     */
    protected function getCategoryDropdownList()
    {
        $ret = [];
        foreach (ArticleCategory::find()->orderBy('id asc')->asArray()->all() as $category) {
            $ret[$category['id']] = $category['name'];
        }
        return $ret;
    }
}
