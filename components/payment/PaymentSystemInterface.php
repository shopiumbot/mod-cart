<?php

namespace shopium\mod\cart\components\payment;

use shopium\mod\cart\models\Payment;

interface PaymentSystemInterface
{

    public function processPaymentRequest(Payment $method);

    public function saveAdminSettings($paymentMethodId, $postData);

    public function getSettingsKey($paymentMethodId);

    public function getConfigurationFormHtml($paymentMethodId);
}