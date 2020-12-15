<?php

namespace shopium\mod\cart\components\events;

use yii\base\ModelEvent;

class EventProduct extends ModelEvent
{

    public $product_model;
    public $ordered_product;
    public $quantity;
}