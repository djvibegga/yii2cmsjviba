<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\UserForm;
use backend\components\ProfileManager;
use yii\web\BadRequestHttpException;
use yii\base\InvalidParamException;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
                        'roles' => [ProfileManager::PERM_CREATE]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => [ProfileManager::PERM_LIST]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ProfileManager::PERM_VIEW,
                                ['user_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ProfileManager::PERM_UPDATE,
                                ['user_id' => isset($_GET['id']) ? $_GET['id'] : null]
                            );
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can(
                                ProfileManager::PERM_DELETE,
                                ['user_id' => isset($_GET['id']) ? $_GET['id'] : null]
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
     * Returns profile manager DI
     * @return ProfileManager
     */
    protected function getProfileManager()
    {
        return Yii::$app->get('profileManager');
    }

    /**
     * Lists all users.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->getProfileManager()->getDataProvider();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single user.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        try {
            $model = $this->getProfileManager()->loadUserById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        }
        if (! $model) {
            throw new NotFoundHttpException('User has not found.');
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserForm(['scenario' => 'insert']);
        $model->load(Yii::$app->request->post());

        if ($model->validate()) {
            $result = $this->getProfileManager()->createUser($model);
            if ($result instanceof User) {
                return $this->redirect(['view', 'id' => $result->id]);
            } else {
                $model->addErrors($result);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'statuses' => User::getAvailableStatuses(),
            'roles' => User::getAvailableRoles()
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new UserForm(['scenario' => 'update']);
        $this->getProfileManager()->loadUserFormById($model, $id);
        
        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());
            if ($model->validate()) {
                $result = $this->getProfileManager()->updateUserById($id, $model);
                if ($result instanceof User) {
                    return $this->redirect(['view', 'id' => $result->id]);
                } else {
                    $model->addErrors($result);
                }
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            'statuses' => User::getAvailableStatuses(),
            'roles' => User::getAvailableRoles()
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $result = $this->getProfileManager()->deleteUserById($id);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException();
        } catch (InvalidParamException $e) {
            throw new NotFoundHttpException();
        }
        
        if ($result) {
            return $this->redirect(['index']);
        } else {
            throw new ServerErrorHttpException('Unable to delete the user.');
        }
    }
}
