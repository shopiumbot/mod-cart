<?php

namespace shopium\mod\cart\models;

use core\components\ActiveRecord;
use shopium\mod\cart\components\payment\PaymentSystemManager;

/**
 * Class Payment
 * @package shopium\mod\cart\models
 *
 * @property string $name
 * @property integer $id
 * @property string $system
 */
class Payment extends ActiveRecord
{

    const MODULE_ID = 'cart';

    public static function tableName()
    {
        return '{{%order__payment}}';
    }

    public static function find()
    {
        return new query\DeliveryPaymentQuery(get_called_class());
    }


    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'trim'],
            [['name', 'system'], 'string', 'max' => 255],
            [['id', 'name', 'switch'], 'safe'],
        ];
    }


    public function getPaymentSystemClass() {
        if ($this->system) {
            $manager = new PaymentSystemManager();
            return $manager->getSystemClass($this->system);
        }
    }

}
