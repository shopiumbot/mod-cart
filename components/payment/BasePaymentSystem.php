<?php

namespace shopium\mod\cart\components\payment;

use panix\engine\CMS;
use Yii;
use panix\engine\Html;

class BasePaymentSystem extends \yii\base\Component
{

    public function init()
    {
        parent::init();
        $path = '@cart/widgets/payment/liqpay/messages';
        Yii::$app->i18n->translations['liqpay/*'] = Yii::createObject([
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => $path,
            'fileMap' => Yii::$app->getTranslationsFileMap('liqpay', $path)
        ]);

    }


    /**
     * @param $paymentMethodId
     * @param $data
     */
    public function setSettings($paymentMethodId, $data)
    {

        Yii::$app->settings->set($paymentMethodId, $data);
    }

    /**
     * @param $paymentMethodId
     * @return mixed
     */
    public function getSettings($paymentMethodId)
    {
        return Yii::$app->settings->get($paymentMethodId);
    }

    /**
     * @param $message string
     */
    public function log($message)
    {
        Yii::info((new \ReflectionClass($this))->getShortName() . ': ' . $message);
    }
    /**
     * @param string $id
     * @param string $path
     * @return array
     */
    public function getTranslationsFileMap222($id, $path)
    {
        $lang = Yii::$app->language;
        $result = [];
        $basePath = realpath(Yii::getAlias("{$path}/{$lang}"));

        if (is_dir($basePath)) {
            $fileList = \yii\helpers\FileHelper::findFiles($basePath, [
                'only' => ['*.php'],
                'recursive' => false
            ]);
            foreach ($fileList as $path) {
                $result[$id . '/' . basename($path, '.php')] = basename($path);
            }
        }
        return $result;
    }


}