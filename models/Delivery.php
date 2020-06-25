<?php

namespace shopium\mod\cart\models;


use shopium\mod\cart\components\delivery\DeliverySystemManager;
use core\components\ActiveRecord;

/**
 * Class Delivery
 *
 * @property float $price
 * @property float $free_from
 * @property string $system
 * @property string $name
 *
 * @package shopium\mod\cart\models
 */
class Delivery extends ActiveRecord
{

    const MODULE_ID = 'cart';
    public $_payment_methods;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order__delivery}}';
    }

    public static function find()
    {
        return new query\DeliveryQuery(get_called_class());
    }

    public function getCategorization()
    {
        return $this->hasMany(DeliveryPayment::class, ['delivery_id' => 'id']);
    }

    public function getPaymentMethods()
    {
        return $this->hasMany(Payment::class, ['id' => 'payment_id'])->via('categorization');
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            [['system'], 'default'],
            // ['price, free_from', 'number'],

            //['payment_methods', 'validatePaymentMethods'],
            ['name', 'string', 'max' => 255],
            [['price', 'free_from', 'system'], 'string'],
        ];
    }

    /**
     * Validate payment method exists
     * @param $attr
     * @return mixed
     */
    public function validatePaymentMethods($attr)
    {
        if (!is_array($this->$attr))
            return;

        foreach ($this->$attr as $id) {
            if (Payment::find()->where(['id' => $id])->count() == 0)
                $this->addError($attr, self::t('ERROR_PAYMENT'));
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {


        // Clear payment relations
        DeliveryPayment::deleteAll(['delivery_id' => $this->id]);

        foreach ($this->getPayment_methods() as $pid) {
            //  if($this->getPayment_methods()){
            $model = new DeliveryPayment;
            $model->delivery_id = $this->id;
            $model->payment_id = $pid;
            $model->save(false);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $data array ids of payment methods
     */
    public function setPayment_methods($data)
    {
        $this->_payment_methods = $data;
    }


    /**
     * @return array
     */
    public function getPayment_methods()
    {


        if ($this->_payment_methods)
            return $this->_payment_methods;

        $this->_payment_methods = [];
        foreach ($this->categorization as $row)
            $this->_payment_methods[] = $row->payment_id;


        return $this->_payment_methods;
    }

    /**
     * @return string order used delivery method
     */
    public function countOrders()
    {
        return Order::find()->where(['delivery_id' => $this->id])->count();
    }

}
