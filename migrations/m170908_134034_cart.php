<?php

namespace shopium\mod\cart\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m170908_134034_cart
 */
use Yii;
use panix\engine\db\Migration;
use shopium\mod\cart\models\Order;
use shopium\mod\cart\models\OrderStatus;
use shopium\mod\cart\models\OrderProduct;
use shopium\mod\cart\models\OrderHistory;
use shopium\mod\cart\models\OrderProductHistroy;
use shopium\mod\cart\models\Delivery;
use shopium\mod\cart\models\Payment;
use shopium\mod\cart\models\DeliveryPayment;

/**
 * Class m170908_134034_cart
 */
class m170908_134034_cart extends Migration
{
    public $settingsForm = 'shopium\mod\cart\models\forms\SettingsForm';

    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        // create table order
        $this->createTable(Order::tableName(), [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned(),
            'secret_key' => $this->string(10)->notNull(),
            'delivery_id' => $this->integer()->notNull()->unsigned(),
            'payment_id' => $this->integer()->notNull()->unsigned(),
            'status_id' => $this->integer()->notNull()->unsigned(),
            'promocode_id' => $this->integer()->null()->unsigned(),
            'delivery_price' => $this->money(10, 2),
            'total_price' => $this->money(10, 2),
            'total_price_purchase' => $this->money(10, 2),
            'firstname' => $this->string(100),
            'lastname' => $this->string(100),
            'user_name' => $this->string(100),
            'user_address' => $this->string(255),
            'user_phone' => $this->phone(),
            'user_comment' => $this->text(),
            'admin_comment' => $this->text()->comment('Admin Comment'),
            'invoice' => $this->string(50)->null(),
            'discount' => $this->string(10),
            'created_at' => $this->integer(11)->null(),
            'updated_at' => $this->integer(11)->null(),
            'paid' => $this->boolean()->defaultValue(0),
            'checkout' => $this->boolean()->defaultValue(0),
            'city_id' => $this->string(36)->null(),
            'area_id' => $this->string(36)->null(),
            'warehouse_id' => $this->string(36)->null(),
            'city' => $this->string(255)->null(),
            'area' => $this->string(255)->null(),
            'warehouse' => $this->string(255)->null(),

        ], $this->tableOptions);

        // create table order status
        $this->createTable(OrderStatus::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(100),
            'color' => $this->string(7),
            'ordern' => $this->integer(),
        ], $this->tableOptions);


        // create table order products
        $this->createTable(OrderProduct::tableName(), [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'order_id' => $this->bigInteger()->notNull()->unsigned(),
            'product_id' => $this->integer()->notNull()->unsigned(),
            'currency_id' => $this->integer()->unsigned(),
            'supplier_id' => $this->integer()->unsigned(),
            'manufacturer_id' => $this->integer()->unsigned(),
            'configurable_id' => $this->integer()->unsigned(),
            'name' => $this->string(255),
            'image' => $this->string(255),
            'discount' => $this->string(25)->null(),
            'configurable_name' => $this->text(),
            'configurable_data' => $this->text(),
            'variants' => $this->text(),
            'quantity' => $this->smallInteger(8),
            'sku' => $this->string(100),
            'price' => $this->money(10, 2),
            'price_purchase' => $this->money(10, 2)->comment('Цена закупки'),
        ], $this->tableOptions);


        // create table order history
        $this->createTable(OrderHistory::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->notNull()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'username' => $this->string(255),
            'handler' => $this->string(255),
            'data_before' => $this->text(),
            'data_after' => $this->text(),
            'date_create' => $this->datetime(),
        ], $this->tableOptions);

        // create table order history product
        $this->createTable(OrderProductHistroy::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->notNull()->unsigned(),
            'product_id' => $this->integer()->notNull()->unsigned(),
            'date_create' => $this->datetime(),
        ], $this->tableOptions);


        // create table order history product
        $this->createTable(Payment::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'currency_id' => $this->integer()->unsigned(),
            'name' => $this->string(255),
            'switch' => $this->boolean()->defaultValue(1),
            'system' => $this->string(100),
            'ordern' => $this->integer()->unsigned(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');


        $this->createTable(Delivery::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'price' => $this->money(10, 2)->null(),
            'free_from' => $this->money(10, 2)->null(),
            'system' => $this->string(100),
            'name' => $this->string(255),
            'switch' => $this->boolean()->defaultValue(1),
            'ordern' => $this->integer()->unsigned(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->createTable(DeliveryPayment::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'delivery_id' => $this->integer()->unsigned(),
            'payment_id' => $this->integer()->unsigned(),
        ], $this->tableOptions);


        $this->addIndexes();


        $this->batchInsert(OrderStatus::tableName(), ['name', 'color', 'ordern'], [
            ['Новый', '#67bf3b', 1],
            ['Отправлен', '#cссссс', 2],
        ]);


        $this->batchInsert(Payment::tableName(), ['currency_id', 'ordern', 'name'], [
            [1, 1, 'Наличными'],
            [1, 2, 'Кредитная карта'],
        ]);


        $this->batchInsert(Delivery::tableName(), ['ordern', 'name','system'], [
            [1, 'Самовывоз',NULL],
            [2, 'Новая почта','novaposhta'],
        ]);


        if ($this->db->driverName != "sqlite") {
            // $this->addForeignKey('{{%fk_order_status}}', Order::tableName(), 'status_id', OrderStatus::tableName(), 'id', "NO ACTION", "NO ACTION");
            // $this->addForeignKey('{{%fk_order_payment}}', Order::tableName(), 'payment_id', Payment::tableName(), 'id', "NO ACTION", "NO ACTION");
            // $this->addForeignKey('{{%fk_order_delivery}}', Order::tableName(), 'delivery_id', Delivery::tableName(), 'id', "NO ACTION", "NO ACTION");
            //$this->addForeignKey('{{%fk_product_order}}', OrderProduct::tableName(), 'order_id', Order::tableName(), 'id', "CASCADE", "CASCADE");
        }

        $this->loadSettings();
    }

    public function down()
    {
        if ($this->db->driverName != "sqlite") {
            //$this->dropForeignKey('{{%fk_order__status}}', Order::tableName());
            //$this->dropForeignKey('{{%fk_order__payment}}', Order::tableName());
            //$this->dropForeignKey('{{%fk_order__delivery}}', Order::tableName());
            //$this->dropForeignKey('{{%fk_product__order}}', OrderProduct::tableName());
        }
        $this->dropTable(Order::tableName());
        $this->dropTable(OrderStatus::tableName());
        $this->dropTable(OrderProduct::tableName());
        $this->dropTable(OrderHistory::tableName());
        $this->dropTable(OrderProductHistroy::tableName());
        $this->dropTable(Payment::tableName());
        $this->dropTable(Delivery::tableName());
        $this->dropTable(DeliveryPayment::tableName());

    }

    private function addIndexes()
    {
        // order indexes
        $this->createIndex('user_id', Order::tableName(), 'user_id');
        $this->createIndex('secret_key', Order::tableName(), 'secret_key');
        $this->createIndex('delivery_id', Order::tableName(), 'delivery_id');
        $this->createIndex('payment_id', Order::tableName(), 'payment_id');
        $this->createIndex('status_id', Order::tableName(), 'status_id');

        // order status indexes
        $this->createIndex('ordern', OrderStatus::tableName(), 'ordern');


        // order products indexes
        $this->createIndex('order_id', OrderProduct::tableName(), 'order_id');
        $this->createIndex('product_id', OrderProduct::tableName(), 'product_id');
        $this->createIndex('currency_id', OrderProduct::tableName(), 'currency_id');
        $this->createIndex('supplier_id', OrderProduct::tableName(), 'supplier_id');
        $this->createIndex('configurable_id', OrderProduct::tableName(), 'configurable_id');
        $this->createIndex('manufacturer_id', OrderProduct::tableName(), 'manufacturer_id');

        // order history indexes
        $this->createIndex('order_id', OrderHistory::tableName(), 'order_id');
        $this->createIndex('user_id', OrderHistory::tableName(), 'user_id');
        $this->createIndex('date_create', OrderHistory::tableName(), 'date_create');


        // order history product indexes
        $this->createIndex('order_id', OrderProductHistroy::tableName(), 'order_id');
        $this->createIndex('product_id', OrderProductHistroy::tableName(), 'product_id');

        // order_payment_method indexes
        $this->createIndex('ordern', Payment::tableName(), 'ordern');
        $this->createIndex('switch', Payment::tableName(), 'switch');


        $this->createIndex('delivery_id', DeliveryPayment::tableName(), 'delivery_id');
        $this->createIndex('payment_id', DeliveryPayment::tableName(), 'payment_id');
    }

}
