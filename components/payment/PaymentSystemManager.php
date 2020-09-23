<?php

namespace shopium\mod\cart\components\payment;

class PaymentSystemManager extends \yii\base\Component {

    /**
     * @var array
     */
    private $_systems = [];

    /**
     * Find all payment systems installed
     * @return array
     */
    public function getSystems() {
        $pattern = \Yii::getAlias('@cart/widgets/payment') . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'config.xml';

        foreach (glob($pattern, GLOB_BRACE) as $file) {
            $config = simplexml_load_file($file);
            $this->_systems[(string) $config->id] = $config;
        }
        return $this->_systems;
    }

    /**
     * Read and return system config.xml
     * @param $name
     */
    public function getSystemInfo($name) {
        return $this->systems[$name];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getSystemClass($id) {
        $systemInfo = $this->getSystemInfo($id);
        $className = (string) $systemInfo->class;

        $systemArray = $this->getDefaultModelClasses();

        return new $systemArray[$className];
    }

    protected function getDefaultModelClasses() {
        return [
           // 'QiwiPaymentSystem' => 'shopium\mod\cart\widgets\payment\qiwi\QiwiPaymentSystem',
            'LiqPayPaymentSystem' => 'shopium\mod\cart\widgets\payment\liqpay\LiqPayPaymentSystem',
           // 'Privat24PaymentSystem' => 'shopium\mod\cart\widgets\payment\privat24\Privat24PaymentSystem',
           // 'RobokassaPaymentSystem' => 'shopium\mod\cart\widgets\payment\robokassa\RobokassaPaymentSystem',
           // 'WebMoneyPaymentSystem' => 'shopium\mod\cart\widgets\payment\webmoney\WebMoneyPaymentSystem',
            //'YandexMoneyPaymentSystem' => 'shopium\mod\cart\widgets\payment\yandexmoney\YandexMoneyPaymentSystem',
        ];
    }

}
