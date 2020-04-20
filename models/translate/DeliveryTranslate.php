<?php

namespace shopium\mod\cart\models\translate;

use yii\db\ActiveRecord;

/**
 * Class DeliveryTranslate
 * @property string $name
 * @property string $description
 * @package shopium\mod\cart\models\translate
 */
class DeliveryTranslate extends ActiveRecord
{

    public static $translationAttributes = ['name', 'description'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order__delivery_translate}}';
    }

}
