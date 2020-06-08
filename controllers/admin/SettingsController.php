<?php

namespace shopium\mod\cart\controllers\admin;

use Yii;
use core\components\controllers\AdminController;
use shopium\mod\cart\models\forms\SettingsForm;

class SettingsController extends AdminController
{

    public $icon = 'settings';

    public function actionIndex()
    {
        $this->pageName = Yii::t('app/default', 'SETTINGS');
        $this->breadcrumbs[] =
            [
                'label' => $this->module->info['label'],
                'url' => $this->module->info['url'],

            ];


        $this->breadcrumbs[] = $this->pageName;

        $model = new SettingsForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $model->save();
                Yii::$app->session->setFlash("success", Yii::t('app/default', 'SUCCESS_UPDATE'));
            }
            return $this->refresh();
        }
        return $this->render('index', [
            'model' => $model
        ]);
    }

}
