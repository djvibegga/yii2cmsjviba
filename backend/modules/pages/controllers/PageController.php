<?php

namespace backend\modules\pages\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Language;
use backend\modules\pages\models\Page;
use backend\modules\pages\models\PageForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;

/**
 * PageController implements the CRUD actions for Page model.
 */
class PageController extends Controller
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
     * Lists all pages.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->module->get('pageManager')->getDataProvider();
        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single page.
     * @param integer $id the page id
     * @return mixed
     */
    public function actionView($id)
    {
        try {
            $model = $this->module->get('pageManager')->loadPageById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if ($model === null) {
            throw new NotFoundHttpException('Page has not found.');
        }
        return $this->render('view', [
            'model' => $model
        ]);
    }

    /**
     * Creates a new page.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PageForm(['scenario' => 'insert']);
        $model->load(Yii::$app->request->post());
        $model->infos = Yii::$app->request->post('PageInfo');
        
        if ($model->validate() && ($result = $this->module->get('pageManager')->createPage($model))) {
            if ($result instanceOf Page) {
                return $this->redirect(['view', 'id' => $result->id]);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'statuses' => Page::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all()
        ]);
    }

    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new PageForm(['scenario' => 'update']);
        
        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());
            $model->infos = Yii::$app->request->post('PageInfo');
            if ($result = $this->module->get('pageManager')->updatePageById($id, $model)) {
                return $this->redirect(['view', 'id' => $id]);
            }
        } else {
            $this->module->get('pageManager')->loadPageFormById($model, $id);
        }
        
        return $this->render('update', [
            'model' => $model,
            'statuses' => Page::getAvailableStatuses(),
            'langs' => Language::find()->asArray()->all(),
        ]);
    }

    /**
     * Deletes an existing page.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id the page id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $this->module->get('pageManager')->deletePageById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException('Page has not found.');
        }

        return $this->redirect(['index']);
    }
}
