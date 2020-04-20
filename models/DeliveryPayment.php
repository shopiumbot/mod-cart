<?php

namespace shopium\mod\cart\models;

use yii\db\ActiveRecord;

/**
 * Class DeliveryPayment
 *
 * @property integer $delivery_id
 * @property integer $payment_id
 *
 * @package shopium\mod\cart\models
 */
class DeliveryPayment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order__delivery_payment}}';
    }

}
