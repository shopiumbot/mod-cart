<?php

namespace shopium\mod\cart\widgets\payment\liqpay;

use panix\engine\base\Model;
use Yii;

class LiqPayConfigurationModel extends Model
{

    const commission = 2.75;

    public $public_key;
    public $private_key;
    public $commission_user;

    public function rules()
    {
        return [
            [['public_key', 'private_key', 'commission_user'], 'required'],
            [['public_key', 'private_key', 'commission_user'], 'string'],
            [['public_key', 'private_key', 'commission_user'], 'trim'],
            //[['commission_user'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'public_key' => Yii::t('liqpay/default', 'PUBLIC_KEY'),
            'private_key' => Yii::t('liqpay/default', 'PRIVATE_KEY'),
            'commission_user' => Yii::t('liqpay/default', 'COMMISSION_USER', [self::commission.'%']),
        ];
    }

}
