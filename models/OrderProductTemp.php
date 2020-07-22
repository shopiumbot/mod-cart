<?php

namespace shopium\mod\cart\models;

use core\modules\shop\models\Product;
use core\components\ActiveRecord;

/**
 * Class OrderProductTemp
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $product_id
 *
 * @property OrderTemp $order
 *
 * @package shopium\mod\cart\models
 */
class OrderProductTemp extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%order__product_temp}}';
    }

    public function getOrder()
    {
        return $this->hasOne(OrderTemp::class, ['id' => 'order_id']);
    }

    public function getOriginalProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function afterDelete()
    {
        $this->trigger(self::EVENT_AFTER_DELETE);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->order->updateTotalPrice();
        if ($this->isNewRecord) {
            $product = Product::findOne($this->product_id);
            $product->decreaseQuantity();
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
