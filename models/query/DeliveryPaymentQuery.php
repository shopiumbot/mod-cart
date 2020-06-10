<?php

namespace shopium\mod\cart\models\query;

use core\components\traits\query\QueryTrait;
use yii\db\ActiveQuery;

class DeliveryPaymentQuery extends ActiveQuery
{

    use QueryTrait;

    public function orderByName($sort = SORT_ASC)
    {
        return $this->addOrderBy(['name' => $sort]);
    }

}
