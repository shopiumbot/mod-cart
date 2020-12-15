<?php

namespace shopium\mod\cart\components\events;

use yii\base\ModelEvent;

class EventOrderStatus extends ModelEvent
{

    public $old_status_id;
    public $new_status_id;
}