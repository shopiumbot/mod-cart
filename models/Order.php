<?php

namespace shopium\mod\cart\models;

use shopium\mod\cart\components\events\EventOrderStatus;
use shopium\mod\cart\components\HistoricalBehavior;
use shopium\mod\cart\components\events\EventProduct;
use shopium\mod\telegram\models\User;
use Yii;
use panix\engine\Html;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;
use core\components\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * Class Order
 * @property integer $id
 * @property integer $user_id
 * @property integer $status_id
 * @property integer $payment_id
 * @property integer $delivery_id
 * @property integer $promocode_id
 * @property string $secret_key
 * @property float $total_price
 * @property float $delivery_price
 * @property float $full_price
 * @property string $user_name
 * @property string $user_address
 * @property string $user_phone
 * @property string $user_comment
 * @property string $admin_comment
 * @property string $user_agent
 * @property integer $created_at
 * @property integer $updated_at
 * @property boolean $paid
 * @property OrderStatus $status
 * @property OrderProduct[] $products
 * @property Delivery $deliveryMethod
 * @property Payment $paymentMethod
 * @property PromoCode $promoCode
 *
 * @package shopium\mod\cart\models
 */
class Order extends ActiveRecord
{

    const MODULE_ID = 'cart';
    const route = '/admin/cart/default';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item[$fieldName];
        }

        return Yii::$app->currency->number_format($total) . ' ' . Yii::$app->currency->main['symbol'];
    }

    public static function find()
    {
        return new query\OrderQuery(get_called_class());
    }

    public function getPromoCode()
    {
        return $this->hasOne(PromoCode::class, ['id' => 'promocode_id']);
    }

    /**
     * Relation
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryMethod()
    {
        return $this->hasOne(Delivery::class, ['id' => 'delivery_id']);
    }

    /**
     * Relation
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
    }

    /**
     * Relation
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(OrderStatus::class, ['id' => 'status_id']);
    }


    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Relation
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id']);
    }

    /**
     * Relation
     * @return int|string
     */
    public function getProductsCount()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id'])->count();
    }

    public function getUrl()
    {
        return ['/cart/default/view', 'secret_key' => $this->secret_key];
    }

    public function rules()
    {
        return [
            ['user_phone', 'panix\ext\telinput\PhoneInputValidator'],
            [['user_name', 'delivery_id', 'payment_id', 'user_phone'], 'required'],
            [['user_comment', 'admin_comment'], 'string', 'max' => 500],
            [['user_address'], 'string', 'max' => 255],
            [['user_phone'], 'string', 'max' => 30],
            [['user_name', 'discount'], 'string', 'max' => 100],
            [['invoice'], 'default'],
            [['invoice'], 'string', 'max' => 50],
            [['paid', 'checkout'], 'boolean'],
            ['delivery_id', 'validateDelivery'],
            ['payment_id', 'validatePayment'],
            ['status_id', 'validateStatus'],
            ['promocode_id', 'validatePromoCode'],
        ];
    }

    public function validatePromoCode($attribute)
    {
        $value = $this->{$attribute};

        if (is_string($value)) {
            $promo = PromoCode::find()->where(['code' => $value])->one();
            if ($promo) {
                $this->{$attribute} = $promo->id;
            } else {
                $this->addError($attribute, 'Error promocode');
            }
        }

    }

    /**
     * Check if delivery method exists
     */
    public function validateDelivery()
    {
        if (Delivery::find()->where(['id' => $this->delivery_id])->count() == 0)
            $this->addError('delivery_id', Yii::t('cart/admin', 'Необходимо выбрать способ доставки.'));
    }

    public function validatePayment()
    {
        if (Payment::find()->where(['id' => $this->payment_id])->count() == 0)
            $this->addError('payment_id', Yii::t('cart/admin', 'Необходимо выбрать способ оплаты.'));
    }

    /**
     * Check if status exists
     */
    public function validateStatus()
    {
        if ($this->status_id && OrderStatus::find()->where(['id' => $this->status_id])->count() == 0)
            $this->addError('status_id', Yii::t('cart/admin', 'Ошибка проверки статуса.'));
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {

        if ($this->isNewRecord) {
            $this->secret_key = $this->createSecretKey();
        }


        // Set `New` status
        if (!$this->status_id)
            $this->status_id = 0;

        return parent::beforeSave($insert);
    }


    /**
     * @return bool
     */
    public function afterDelete()
    {
        foreach ($this->products as $ordered_product)
            $ordered_product->delete();

        return parent::afterDelete();
    }

    /**
     * Create unique key to view orders
     * @param int $size
     * @return string
     */
    public function createSecretKey($size = 10)
    {

        $result = '';
        $chars = '1234567890qweasdzxcrtyfghvbnuioplkjnm';
        while (mb_strlen($result, 'utf8') < $size) {
            $result .= mb_substr($chars, rand(0, mb_strlen($chars, 'utf8')), 1);
        }

        if (static::find()->where(['secret_key' => $result])->count() > 0)
            $this->createSecretKey($size);

        return $result;
    }

    /**
     * Update total
     */
    public function updateTotalPrice()
    {

        $this->total_price = 0;
        $products = OrderProduct::find()->where(['order_id' => $this->id])->all();

        foreach ($products as $product) {
            /** @var OrderProduct $product */

            // $currency_rate = Yii::$app->currency->active['rate'];
            // if ($product->originalProduct) {
            //     $this->total_price += $product->price * $currency_rate * $product->quantity;
            // }

            if ($product->originalProduct) {
                $this->total_price += $product->price * $product->quantity;
            }

        }

        /*if($this->promoCode){
            if ('%' === substr($this->promoCode->discount, -1, 1)) {
                $this->total_price -= $this->total_price * ((double) $this->promoCode->discount) / 100;
            }

        }*/

        $this->save(false);
    }

    /**
     * @return int
     */
    public function updateDeliveryPrice()
    {
        if ($this->delivery_id) {
            $result = 0;
            $deliveryMethod = Delivery::findOne($this->delivery_id);

            if ($deliveryMethod) {
                if ($deliveryMethod->price > 0) {
                    if ($deliveryMethod->free_from > 0 && $this->total_price > $deliveryMethod->free_from)
                        $result = 0;
                    else
                        $result = $deliveryMethod->price;
                }
            }

            $this->delivery_price = $result;
            $this->save(false);
        }
    }

    public function getGridStatus()
    {
        return Html::tag('span', $this->getStatusName(), ['class' => 'badge', 'style' => 'background:' . $this->getStatusColor()]);
    }

    /**
     * @return mixed
     */
    public function getStatusName()
    {
        if ($this->status)
            return $this->status->name;
    }

    /**
     * @return mixed
     */
    public function getStatusColor()
    {
        if ($this->status)
            return $this->status->color;
    }

    public function behaviors()
    {
        $b = [];
        $b['historical'] = [
            'class' => HistoricalBehavior::class,
        ];

        return $b;
    }


    /**
     * @param Order $order
     */
    public function amoCRM($order)
    {
        $amo = new \AmoCRM\Client('pixelion', 'andrew.panix@gmail.com', 'b58823639ceb496decfc9ec1ebfd4f963783bbf9');
        $account = $amo->account;
        $catalog_id = 3055;
        // Содание сделки
        $lead = $amo->lead;

        $lead['name'] = Yii::t('cart/default', 'MAIL_ADMIN_SUBJECT', $order->id);
        //$lead['price'] = $order->total_price;
        $lead->addCustomField(454699, $order->user_address, false, 'subtype');
        $lead->addCustomField(454707, $order->user_comment, false, 'subtype');

        $lead->addCustomField(454639, $order->paymentMethod->name, false, 'subtype');
        $lead->addCustomField(454697, $order->deliveryMethod->name, false, 'subtype');
        $lead->setTags('ShopiumBot Order');
        $leadId = $lead->apiAdd();

        // Содание контакта
        $contact = $amo->contact;


        //$findContactByEmail = $contact->apiList(['limit_rows' => 1, 'query' => $order->user_email]);
        $findContactByPhone = $contact->apiList(['limit_rows' => 1, 'query' => $order->user_phone]);

        if(!isset($findContactByPhone[0])){
            // Заполнение полей контакта
            $contact['name'] = $order->user_name;
            $contact['tags'] = ['Покупатель'];
            $contact->addCustomField(181801, $order->user_phone, 'WORK');
           // $contact->addCustomField(181803, $order->user_email, 'WORK');
            $contact->addCustomField(181799, 'Покупатель', false, 'subtype');
            $contactId = $contact->apiAdd();
        }else{
            $contactId = $findContactByPhone[0]['id'];
        }


        //Связываем сделку с контактом
        if ($leadId && $contactId) {
            $link = $amo->links;
            $link['from'] = 'leads';
            $link['from_id'] = $leadId;
            $link['to'] = 'contacts';
            $link['to_id'] = $contactId;
            $link->apiLink();
        }


        /** @var OrderProduct $product */
        foreach ($order->products as $k => $product) {
            $p = $amo->catalog_element;
            $productName = '[' . $product->product_id . '] ' . $product->name;

            $findProduct = $p->apiList([
                'catalog_id' => $catalog_id,
                'term' => $productName
            ]);
            // CMS::dump($findProduct);die;
            //Если товар не найден, то сздаем
            if (!isset($findProduct[0])) {
                // echo 'create product';
                $p['catalog_id'] = $catalog_id;
                $p['name'] = $productName;

                $p->addCustomField(182209, $product->sku, false, 'subtype'); //sku
                $p->addCustomField(182215, 5241, false, 'subtype'); // group
                $p->addCustomField(182213, $product->price, false, 'subtype'); //цена

                $pid = $p->apiAdd();
            } else {
                // echo 'update product';
                $pid = $findProduct[0]['id'];
                $element = $amo->catalog_element;

                $element['name'] = $productName;
                $element['catalog_id'] = $catalog_id; // без catalog_id amocrm не обновит

                $element->addCustomField(182209, $product->sku, false, 'subtype'); //sku
                $element->addCustomField(182215, 5241, false, 'subtype'); // group
                // $element->addCustomField(182213, str_replace('.', '', $product->price), false, 'subtype'); //цена
                $element->addCustomField(182213, $product->price, false, 'subtype'); //цена
                $element->apiUpdate((int)$findProduct[0]['id']);


            }


            //Связываем товар со сделкой
            if ($leadId && $pid) {
                $link = $amo->links;
                $link['from'] = 'leads';
                $link['from_id'] = $leadId;
                $link['to'] = 'catalog_elements';
                $link['to_id'] = $pid;

                $link['to_catalog_id'] = $catalog_id;
                $link["quantity"] = $product->quantity;
                $link->apiLink();
            }
        }

        /*
                $catalogList = $amo->catalog->apiList();
                foreach ($catalogList as $catalog) {
                    if ($catalog['type'] == 'products') {
                        echo $catalog['id'];
                        $catalogElementsList = $amo->catalog_element->apiList([
                            'catalog_id' => $catalog['id'],
                            'term' => 'test'
                        ]);
                        CMS::dump($catalogElementsList);
                        die;
                    }
                }*/

    }
    /**
     * @return mixed
     */
    public function getDelivery_name()
    {
        $model = Delivery::findOne($this->delivery_id);
        if ($model)
            return $model->name;
    }

    public function getPayment_name()
    {
        $model = Payment::findOne($this->payment_id);
        if ($model)
            return $model->name;
    }

    /**
     * @return mixed
     */
    public function getFull_price()
    {
        if (!$this->isNewRecord) {
            $result = $this->total_price + $this->delivery_price;
            if ($this->discount) {
                $sum = $this->discount;
                if ('%' === substr($this->discount, -1, 1))
                    $sum = $result * (int)$this->discount / 100;
                $result -= $sum;
            }
            return $result;
        }
    }

    /**
     * Add product to existing order
     *
     * @param /panix/mod/shop/models/Product $product
     * @param integer $quantity
     * @param float $price
     */
    public function addProduct2($product, $quantity, $price)
    {

        if (!$this->isNewRecord) {
            $image = NULL;

            if ($product->getImage()) {
                $image = "/uploads/store/product/{$product->id}/" . basename($product->getImage()->getPathToOrigin());
            }
            $ordered_product = new OrderProduct;
            $ordered_product->order_id = $this->id;
            $ordered_product->image = $image;
            $ordered_product->product_id = $product->id;
            $ordered_product->currency_id = $product->currency_id;
            $ordered_product->name = $product->name;
            $ordered_product->quantity = $quantity;
            $ordered_product->sku = $product->sku;
            $ordered_product->price = $price;

            return $ordered_product->save();


        }
        return false;
    }

    public function addProduct($product, $quantity, $price)
    {

        if (!$this->isNewRecord) {
            $image = NULL;

            $imageData = $product->getImage();
            if($imageData){
                $image = "/uploads/store/product/{$product->id}/".basename($imageData->getPathToOrigin());
            }else{
                $image = '/uploads/no-image.jpg';
            }
            $ordered_product = new OrderProduct();
            $ordered_product->order_id = $this->id;
            $ordered_product->product_id = $product->id;
            $ordered_product->image = $image;
            //$ordered_product->client_id = $this->client_id;
            // $ordered_product->currency_id = $product->currency_id;
            $ordered_product->name = $product->name;
            $ordered_product->quantity = $quantity;
            //   $ordered_product->sku = $product->sku;
            $ordered_product->price = $price;


            // Raise event
            $event = new EventProduct([
                'product_model' => $product,
                'ordered_product' => $ordered_product,
                'quantity' => $quantity
            ]);
            $this->eventProductAdded($event);

            return $ordered_product->save();
        }
        return false;
    }

    /**
     * Delete ordered product from order
     *
     * @param $id
     */
    public function deleteProduct($id)
    {

        $model = OrderProduct::findOne($id);

        if ($model) {
            $model->delete();

            $event = new EventProduct([
                'ordered_product' => $model
            ]);
            $this->eventProductDeleted($event);
        }
    }

    /**
     * @return \panix\engine\data\ActiveDataProvider
     */
    public function getOrderedProducts()
    {
        $products = new search\OrderProductSearch();
        return $products->search([$products->formName() => ['order_id' => $this->id]]);
    }

    /**
     * @param $event
     */
    public function eventProductAdded($event)
    {
        $this->trigger(HistoricalBehavior::EVENT_PRODUCT_ADDED, $event);
    }

    /**
     * @param $event
     */
    public function eventProductQuantityChanged($event)
    {
        $this->trigger(HistoricalBehavior::EVENT_PRODUCT_QUANTITY_CHANGED, $event);
    }

    public function eventProductDeleted($event)
    {
        $this->trigger(HistoricalBehavior::EVENT_PRODUCT_DELETED, $event);
    }
    /**
     * @param array $data
     */
    public function setProductQuantities(array $data)
    {
        foreach ($this->products as $product) {
            if (isset($data[$product->id])) {
                if ((int)$product->quantity !== (int)$data[$product->id]) {
                    $event = new ModelEvent($this, [
                        'ordered_product' => $product,
                        'new_quantity' => (int)$data[$product->id]
                    ]);
                    $this->eventProductQuantityChanged($event);
                }

                $product->quantity = (int)$data[$product->id];
                $product->save(false);
            }
        }
    }

    public function getRelativeUrl()
    {
        return Yii::$app->urlManager->createUrl(['/cart/default/view', 'secret_key' => $this->secret_key]);
    }

    public function getAbsoluteUrl()
    {
        return Yii::$app->urlManager->createAbsoluteUrl(['/cart/default/view', 'secret_key' => $this->secret_key]);
    }

    /**
     * Load history
     *
     * @return array
     */
    public function getHistory()
    {
        return OrderHistory::find()
            ->where(['order_id' => $this->id])
            ->orderBy(['date_create' => SORT_ASC])
            ->all();
    }

    /**
     * @return \yii\mail\MailerInterface
     */
    public function sendAdminEmail()
    {
        $mailer = Yii::$app->mailer;
        $mailer->compose(['html' => '@cart/mail/order.tpl'], ['order' => $this])
            ->setFrom(['noreply@' . Yii::$app->request->serverName => Yii::$app->name . ' robot'])
            ->setTo([Yii::$app->settings->get('app', 'email') => Yii::$app->name])
            ->setSubject(Yii::t('cart/default', 'MAIL_ADMIN_SUBJECT', $this->id))
            ->send();
        return $mailer;
    }

    public function attributeLabels2()
    {

        return [
            'status_id' => Yii::t('cart/Order', 'STATUS_ID'),
            'delivery_id' => Yii::t('cart/Order', 'DELIVERY_ID'),
            'payment_id' => Yii::t('cart/Order', 'PAYMENT_ID'),
            'total_price' => Yii::t('cart/Order', 'TOTAL_PRICE'),
            'user_phone' => Yii::t('cart/Order', 'USER_PHONE'),
            'invoice' => Yii::t('cart/Order', 'INVOICE'),
        ];
    }

    /**
     * @return \yii\mail\MailerInterface

    public function sendClientEmail()
    {
        if ($this->user_email) {
            $mailer = Yii::$app->mailer;
            $mailer->htmlLayout = '@cart/mail/layouts/client';
            $mailer->compose('@cart/mail/order.tpl', ['order' => $this])
                ->setFrom('noreply@' . Yii::$app->request->serverName)
                ->setTo($this->user_email)
                ->setSubject(Yii::t('cart/default', 'MAIL_CLIENT_SUBJECT', $this->id))
                ->send();

            return $mailer;
        }
    }*/

    public static function findModel($id, $message = null)
    {

        if (($model = static::findOne($id)) !== null) {
            //if (($model = static::find()->one((int)$id)) !== null) {
            return $model;
        } else {
            if (!$id)
                return new static();
            throw new NotFoundHttpException($message ? $message : Yii::t('app/error', 404));
        }
    }



    public function getGridColumns()
    {


        $columns = [];
        $columns['id']=[
            'attribute' => 'id',
            'header' => Yii::t('cart/Order', 'ORDER_ID'),
            'format' => 'html',
            'contentOptions' => ['class' => 'text-left'],
            'value' => function ($model) {
                /** @var $model Order */
                return $model->getGridStatus() . ' ' . Yii::t('cart/Order','NEW_ORDER_ID', ['id' => \panix\engine\CMS::idToNumber($model->id)]);
            }
        ];
        $columns['user_name']=[
            'attribute' => 'user_name',
            'format' => 'raw',
            'value' => function ($model) {
                /** @var $model Order */
                return $model->user_name;
            }
        ];
         $columns['user_phone']=[
            'attribute' => 'user_phone',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                /** @var $model Order */
                return Html::tel($model->user_phone);
            }
        ];
        $columns['total_price']=[
            'attribute' => 'total_price',
            'format' => 'html',
            'class' => 'panix\engine\grid\columns\jui\SliderColumn',
            'max' => (int)Order::find()->aggregateTotalPrice('MAX'),
            'min' => (int)Order::find()->aggregateTotalPrice('MIN'),
            'prefix' => '<sup>' . Yii::$app->currency->main['symbol'] . '</sup>',
            'contentOptions' => ['class' => 'text-center'],
            'minCallback' => function ($value) {
                return Yii::$app->currency->number_format($value);
            },
            'maxCallback' => function ($value) {
                return Yii::$app->currency->number_format($value);
            },
            'value' => function ($model) {
                /** @var $model Order */
                $priceHtml = Yii::$app->currency->number_format(Yii::$app->currency->convert($model->total_price));
                $symbol = Html::tag('sup', Yii::$app->currency->main['symbol']);
                return Html::tag('span', $priceHtml, ['class' => 'text-success font-weight-bold']) . ' ' . $symbol;
            }
        ];

        $columns['created_at'] = [
            'attribute' => 'created_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ];
        $columns['updated_at'] = [
            'attribute' => 'updated_at',
            'class' => 'panix\engine\grid\columns\jui\DatepickerColumn',
        ];

        $columns['DEFAULT_CONTROL'] = [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{print} {update} {delete}',
            'buttons' => [
                'print' => function ($url, $model, $key) {
                    return Html::a(Html::icon('print'), ['print', 'id' => $model->id], [
                        'title' => Yii::t('cart/admin', 'ORDER_PRINT'),
                        'class' => 'btn btn-sm btn-info',
                        'data-pjax' => 0,
                        'target' => '_blank'
                    ]);
                },
            ]
        ];
        $columns['DEFAULT_COLUMNS'] = [
            [
                'class' => \panix\engine\grid\sortable\Column::class,
            ],
            [
                'class' => 'panix\engine\grid\columns\CheckboxColumn',
            ]
        ];

        return $columns;
    }
}
