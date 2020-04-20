<?php

namespace shopium\mod\cart\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;

class DeliveryQuery extends ActiveQuery {

    use DefaultQueryTrait;

    public function orderByName($sort = SORT_ASC) {
        return $this->addOrderBy(['name' => $sort]);
    }

}
