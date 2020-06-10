<?php

namespace shopium\mod\cart\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;

class DeliveryPaymentQuery extends ActiveQuery
{

    use DefaultQueryTrait;

    public function init()
    {
        /** @var \yii\db\ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();
        if (isset($modelClass::getTableSchema()->columns['ordern'])) {
            $this->addOrderBy(["{$tableName}.ordern" => SORT_DESC]);
        }
        parent::init();
    }

    public function orderByName($sort = SORT_ASC)
    {
        return $this->addOrderBy(['name' => $sort]);
    }

}
