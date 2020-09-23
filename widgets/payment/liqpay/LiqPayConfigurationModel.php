<?php

namespace shopium\mod\cart\widgets\payment\liqpay;

use panix\engine\base\Model;
use Yii;

class LiqPayConfigurationModel extends Model
{

    const commission = 2.75;

    public $key;
    public $commission_check;

    public function rules()
    {
        return [
            [['key', 'commission_check'], 'required'],
            [['key'], 'string'],
            [['key'], 'trim'],
            [['commission_check'], 'safe'],
           [['commission_check'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'key' => Yii::t('liqpay/default', 'KEY'),
            'commission_check' => Yii::t('liqpay/default', 'COMMISSION_CHECK', [self::commission.'%']),
        ];
    }

}
