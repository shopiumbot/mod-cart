<?php

namespace shopium\mod\cart\widgets\payment\robokassa;

use panix\engine\Html;
use Yii;
use shopium\mod\cart\widgets\payment\robokassa\RobokassaConfigurationModel;
use shopium\mod\cart\models\Payment;
use shopium\mod\cart\models\Order;
use shopium\mod\cart\components\payment\BasePaymentSystem;
/**
 * Robokassa payment system
 */
class RobokassaPaymentSystem extends BasePaymentSystem {

    /**
     * @var bool
     */
    public $testingMode = true; //YII_DEBUG

    /**
     * This method will be triggered after redirection from payment system site.
     * If payment accepted method must return Order model to make redirection to order view.
     * @param Payment $method
     * @return boolean|Order
     */

    public function processPaymentRequest(Payment $method) {
        $request = Yii::$app->request;
        $settings = $this->getSettings($method->id);
        $order = Order::model()->findByAttributes(array('secret_key' => $request->getParam('Shp_orderKey')));

        if ($order->paid)
            return false;

        $mrh_pass2 = $settings['password2'];
        $shp_order_key = $order->secret_key;
        $shp_payment_id = $method->id;

        $out_sum = $request->getParam("OutSum");
        $inv_id = $request->getParam("InvId");
        $crc = strtoupper($request->getParam("SignatureValue"));
        $my_crc = strtoupper(md5("$out_sum:$inv_id:$mrh_pass2:Shp_orderKey=$shp_order_key:Shp_pmId=$shp_payment_id"));

        // Check sum
        if ($out_sum != Yii::$app->currency->convert($order->full_price, $method->currency_id))
            return ERROR_SUM;

        // Check sign
        if ($my_crc != $crc)
            return "bad sign $out_sum:$inv_id:Shp_orderKey=$shp_order_key:Shp_pmId=$shp_payment_id";

        // Set order paid
        $order->paid = 1;
        $order->save();

        // Show answer for Robokassa API service
        if (isset($_REQUEST['getResult']) && $_REQUEST['getResult'] == 'true')
            exit("OK" . $order->id);

        return $order;
    }

    /**
     * Generate robokassa payment form.
     * @param Payment $method
     * @param Order $order
     * @return string
     */
    public function renderPaymentForm(Payment $method, Order $order) {
        $settings = $this->getSettings($method->id);

        // Registration data
        $mrh_login = $settings['login'];
        $mrh_pass1 = $settings['password1'];
        $shp_order_key = $order->secret_key;
        $shp_payment_id = $method->id;

        // Order number
        $inv_id = $order->id;
        // Order description
        $inv_desc = Yii::t('app/default', "Оплата заказа #") . $order->id;
        // Order sum
        $out_sum = Yii::$app->currency->convert($order->full_price, $method->currency_id);
        // currency
        $in_curr = "PCR";
        // Language
        $culture = "ru";
        // Signature
        $crc = md5("$mrh_login:$out_sum:$inv_id:$mrh_pass1:Shp_orderKey=$shp_order_key:Shp_pmId=$shp_payment_id");

        if ($this->testingMode)
            $html = Html::beginForm('http://test.robokassa.ru/Index.aspx');
        else
            $html = Html::beginForm('https://merchant.roboxchange.com/Index.aspx');

        $html .= Html::hiddenField('MrchLogin', $mrh_login);
        $html .= Html::hiddenField('OutSum', $out_sum);
        $html .= Html::hiddenField('InvId', $inv_id);
        $html .= Html::hiddenField('Desc', $inv_desc);
        $html .= Html::hiddenField('SignatureValue', $crc);
        $html .= Html::hiddenField('Shp_orderKey', $shp_order_key);
        $html .= Html::hiddenField('Shp_pmId', $shp_payment_id);
        $html .= Html::hiddenField('IncCurrLabel', $in_curr);
        $html .= Html::hiddenField('Culture', $culture);
        $html .= $this->renderSubmit();
        $html .= Html::endForm();

        return $html;
    }

    /**
     * This method will be triggered after payment method saved in admin panel
     * @param $paymentMethodId
     * @param $postData
     */
    public function saveAdminSettings($paymentMethodId, $postData) {
        $this->setSettings($paymentMethodId, $postData['RobokassaConfigurationModel']);
    }

    /**
     * @param $paymentMethodId
     * @return string
     */
    public function getSettingsKey($paymentMethodId) {
        return $paymentMethodId . '_RobokassaPaymentSystem';
    }

    /**
     * Get configuration form to display in admin panel
     * @param string $paymentMethodId
     * @return CForm
     */
    public function getConfigurationFormHtml($paymentMethodId) {
        $model = new RobokassaConfigurationModel();
        $model->attributes = $this->getSettings($paymentMethodId);
        $form = new BasePaymentForm($model->getForm(), $model);
        return $form;
    }

}
