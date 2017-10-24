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
use backend\modules\pages\components\PageManager;
use yii\filters\AccessControl;

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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => [PageManager::PERM_CREATE]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => [PageManager::PERM_LIST]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                PageManager::PERM_VIEW,
                                ['page_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                PageManager::PERM_UPDATE,
                                ['page_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                PageManager::PERM_DELETE,
                                ['page_id' => isset($_GET['id']) ? $_GET['id'] : null]
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
     * Returns page manager instance
     * @return PageManager
     */
    protected function getPageManager()
    {
        return $this->module->get('pageManager');
    }

    /**
     * Lists all pages.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->getPageManager()->getDataProvider();
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
            $model = $this->getPageManager()->loadPageById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if ($model === null) {
            throw new NotFoundHttpException('Page has not found.');
        }
        $this->layout = '//main';
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
        $model->meta = Yii::$app->request->post('MetaForm');
        
        if ($model->validate() && ($result = $this->getPageManager()->createPage($model))) {
            if ($result instanceOf Page) {
                return $this->redirect(['view', 'id' => $result->id]);
            } else {
                $model->addErrors($result);
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
        try {
            $this->getPageManager()->loadPageFormById($model, $id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        
        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());
            $model->infos = Yii::$app->request->post('PageInfo');
            $model->meta = Yii::$app->request->post('MetaForm');
            if ($model->validate()) {
                if (($result = $this->getPageManager()->updatePageById($id, $model)) &&
                    $result instanceOf Page
                ) {
                    return $this->redirect(['view', 'id' => $id]);
                } else {
                    $model->addErrors($result);
                }
            }
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
            $this->getPageManager()->deletePageById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException('Page has not found.');
        }

        return $this->redirect(['index']);
    }
}
