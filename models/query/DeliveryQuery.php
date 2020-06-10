<?php

namespace shopium\mod\cart\models\query;

use yii\db\ActiveQuery;
use core\components\traits\query\QueryTrait;

class DeliveryQuery extends ActiveQuery
{

    use QueryTrait;

    public function orderByName($sort = SORT_ASC)
    {
        return $this->addOrderBy(['name' => $sort]);
    }

}
