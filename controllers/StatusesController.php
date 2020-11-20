<?php

namespace shopium\mod\cart\controllers;

use panix\engine\Html;
use Yii;
use core\components\controllers\AdminController;
use shopium\mod\cart\models\OrderStatus;
use shopium\mod\cart\models\search\OrderStatusSearch;
use yii\web\HttpException;

class StatusesController extends AdminController
{
    public function actions()
    {
        return [
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => OrderStatus::class,
            ],
        ];
    }

    /**
     * Display statuses list
     */
    public function actionIndex()
    {
        $this->pageName = Yii::t('cart/admin', 'STATUSES');
        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('cart/admin', 'ORDERS'),
            'url' => ['/cart/default/index']
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $this->buttons = [
            [
                'label' => Yii::t('app/default', 'CREATE'),
                'url' => ['create'],
                'icon' => 'add',
                'options' => ['class' => 'btn btn-success']
            ]
        ];

        $searchModel = new OrderStatusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Update status
     * @param bool $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id = false)
    {

        $model = OrderStatus::findModel($id, Yii::t('cart/admin', 'NO_STATUSES'));


        $title = ($model->isNewRecord) ? Yii::t('cart/admin', 'CREATE_STATUSES') :
            Yii::t('cart/admin', 'UPDATE_STATUSES');

        $this->pageName = $title;

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('cart/admin', 'ORDERS'),
            'url' => ['/cart/default/index']
        ];

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('cart/admin', 'STATUSES'),
            'url' => ['index']
        ];

        $this->view->params['breadcrumbs'][] = $this->pageName;

        $post = Yii::$app->request->post();

        $isNew = $model->isNewRecord;
        if ($model->load($post) && $model->validate()) {
            $model->save();
            return $this->redirectPage($isNew, $post);
        }


        return $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Delete status
     *
     * @param array $id
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionDelete($id = [])
    {
        if (Yii::$app->request->isPost) {
            $model = OrderStatus::find()->where(['id' => $_REQUEST['id']])->all();

            if (!empty($model)) {
                foreach ($model as $m) {
                    if ($m->countOrders() == 0 && $m->id != 1)
                        $m->delete();
                    else
                        throw new HttpException(409, Yii::t('cart/admin', 'ERR_DELETE_STATUS'));
                }
            }

            if (!Yii::$app->request->isAjax)
                return $this->redirect('index');
        }
    }

    /**
     * Дополнительное меню Контроллера.
     * @return array
     */
    public function getAddonsMenu22()
    {
        return array(
            array(
                'label' => Yii::t('cart/admin', 'ORDER', 0),
                'url' => ['/cart/default/index'],
                'icon' => Html::icon('cart'),

            ),
            array(
                'label' => Yii::t('cart/admin', 'STATS'),
                'url' => ['/cart/statistics/index'],
                'icon' => Html::icon('stats'),

            ),
            array(
                'label' => Yii::t('cart/admin', 'HISTORY'),
                'url' => ['/cart/history/index'],
                'icon' => Html::icon('history'),

            ),
        );
    }

}
