<?php

namespace shopium\mod\cart\controllers;

use Yii;
use core\components\controllers\AdminController;
use shopium\mod\cart\models\PromoCode;
use shopium\mod\cart\models\search\PromoCodeSearch;

class PromoCodeController extends AdminController
{


    public function actionIndex()
    {
        $this->pageName = Yii::t('cart/admin', 'PROMOCODE');
        $this->buttons = [
            [
                'label' => Yii::t('cart/admin', 'CREATE_PROMOCODE'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success', 'target' => '_blank']
            ]
        ];

        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new PromoCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {
        $model = PromoCode::findModel($id);
        $this->pageName = Yii::t('cart/admin', 'PROMOCODE');
        $this->view->params['breadcrumbs'] = [
            $this->pageName
        ];
        $this->buttons = [
            [
                'label' => Yii::t('cart/admin', 'CREATE_PROMOCODE'),
                'url' => ['create'],
                'options' => ['class' => 'btn btn-success', 'target' => '_blank']
            ]
        ];
        $isNew = $model->isNewRecord;
        $post = Yii::$app->request->post();

        if (!isset($post['PromoCode']['manufacturers'])) {
           // $model->manufacturers = [];
        }
        if (!isset($post['PromoCode']['categories']))
          //  $model->categories = [];

            if (!$model->categories) {
                $model->categories = [];
            }
        if (!$model->manufacturers) {
            $model->manufacturers = [];
        }

        if ($model->load($post)) {
            if ($model->validate()) {
                //print_r($model->attributes);die;
                $model->save();
                return $this->redirectPage($isNew, $post);
            }
        }
        return $this->render('update', ['model' => $model]);
    }


}
