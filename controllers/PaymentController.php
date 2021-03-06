<?php

namespace shopium\mod\cart\controllers;

use panix\engine\CMS;
use Yii;
use shopium\mod\cart\models\search\PaymentSearch;
use shopium\mod\cart\models\Payment;
use shopium\mod\cart\components\payment\PaymentSystemManager;
use panix\engine\Html;
use core\components\controllers\AdminController;

class PaymentController extends AdminController
{

    public $icon = 'creditcard';

    public function actions()
    {
        return [
            'sortable' => [
                'class' => 'panix\engine\grid\sortable\Action',
                'modelClass' => Payment::class,
            ],
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => Payment::class,
            ],
        ];
    }

    /*
      public function actions() {
      return array(
      'order' => array(
      'class' => 'ext.adminList.actions.SortingAction',
      ),
      'switch' => array(
      'class' => 'ext.adminList.actions.SwitchAction',
      ),
      'sortable' => array(
      'class' => 'ext.sortable.SortableAction',
      'model' => Delivery::model(),
      )
      );
      }
     */

    public function actionIndex()
    {
        $this->pageName = Yii::t('cart/admin', 'PAYMENTS');
        $this->buttons = [
            [
                'icon' => 'add',
                'label' => Yii::t('app/default', 'CREATE'),
                'url' => ['/cart/payment/create'],
                'options' => ['class' => 'btn btn-success']
            ]
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('cart/default', 'MODULE_NAME'),
            'url' => ['/cart/default/index']
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new PaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {
        $model = Payment::findModel($id);
        $isNew = $model->isNewRecord;
        $this->pageName = Yii::t('cart/admin', 'PAYMENTS');

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('cart/admin', 'ORDERS'),
            'url' => ['/cart/default/index']
        ];
        $this->view->params['breadcrumbs'][] = [
            'label' => $this->pageName,
            'url' => ['index']
        ];
        $this->view->params['breadcrumbs'][] = Yii::t('app/default', ($isNew) ? 'CREATE' : 'UPDATE');
        \shopium\mod\cart\CartPaymentAsset::register($this->view);

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            $model->save();

            if ($model->system) {

                $manager = new PaymentSystemManager;
                $system = $manager->getSystemClass($model->system);

                $system->saveAdminSettings($model->id, $_POST);

            }

            return $this->redirectPage($isNew, $post);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * Renders payment system configuration form
     */
    public function actionRenderConfigurationForm()
    {

        $systemId = Yii::$app->request->get('system');
        $paymentMethodId = Yii::$app->request->get('payment_method_id');
        if (empty($systemId))
            exit;
        $manager = new PaymentSystemManager;
        $system = $manager->getSystemClass($systemId);

        // print_r($system->getConfigurationFormHtml($paymentMethodId));
        return $this->renderPartial('@cart/widgets/payment/' . $systemId . '/_form', ['model' => $system->getConfigurationFormHtml($paymentMethodId)]);
    }

    /**
     * Дополнительное меню Контроллера.
     * @return array
     */
    public function getAddonsMenu()
    {
        return array(
            array(
                'label' => Yii::t('cart/admin', 'STATUSES'),
                'url' => array('/cart/statuses/index'),
            ),
            array(
                'label' => Yii::t('cart/admin', 'STATS'),
                'url' => array('/cart/statistics/index'),
                'icon' => Html::icon('stats'),
            ),
            array(
                'label' => Yii::t('cart/admin', 'HISTORY'),
                'url' => array('/cart/history/index'),
                'icon' => Html::icon('history'),
            ),
            array(
                'label' => Yii::t('app/default', 'SETTINGS'),
                'url' => array('/cart/settings/index'),
                'icon' => Html::icon('settings'),
            ),
        );
    }

}
