<?php

namespace shopium\mod\cart\models;

use Yii;
use core\components\ActiveRecord;

/**
 * Class NovaPoshtaCities
 *
 * @property float $price
 * @property float $free_from
 * @property string $system
 * @property string $name
 *
 * @package shopium\mod\cart\models
 */
class NovaPoshtaCities extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->serverDb;
    }

    const MODULE_ID = 'cart';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%novaposhta_cities}}';
    }

    public static function find()
    {
        return new query\DeliveryQuery(get_called_class());
    }

}
