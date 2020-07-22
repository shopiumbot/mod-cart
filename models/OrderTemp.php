<?php

namespace shopium\mod\cart\models;

use core\modules\shop\models\Product;
use shopium\mod\telegram\models\User;
use Yii;
use panix\engine\Html;
use yii\behaviors\TimestampBehavior;
use core\components\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * Class OrderTemp
 *
 * @property double $total_price
 * @property integer $id
 *
 * @package shopium\mod\cart\models
 */
class OrderTemp extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_temp}}';
    }

    public function getProducts()
    {
        return $this->hasMany(OrderProductTemp::class, ['order_id' => 'id']);
    }

    public function updateTotalPrice()
    {

        $this->total_price = 0;
        $products = OrderProductTemp::find()->where(['order_id' => $this->id])->all();

        foreach ($products as $product) {
            /**
             * @var OrderProduct $product
             * @var Product $originalProduct
             **/
            $original=$product->originalProduct;
            if ($original) {
                $this->total_price += $original->price * $product->quantity;
            }

        }

        $this->save(false);
    }

    public function addProduct($product, $quantity)
    {

        if (!$this->isNewRecord) {
            $image = NULL;
            $ordered_product = new OrderProductTemp();
            $ordered_product->order_id = $this->id;
            $ordered_product->product_id = $product->id;
            $ordered_product->quantity = $quantity;
            return $ordered_product->save(false);
        }
        return false;
    }

}
