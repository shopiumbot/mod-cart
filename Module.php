<?php

namespace shopium\mod\cart;

use panix\mod\admin\widgets\sidebar\BackendNav;
use Yii;
use panix\engine\WebModule;
use shopium\mod\cart\models\Order;
use yii\base\BootstrapInterface;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'cart';

    public function init()
    {
        if (!(Yii::$app instanceof yii\console\Application) && !Yii::$app->user->isGuest) {
            $count = Order::find()->where(['status_id' => 1])->count();
            $this->count['num'] = (int) $count;
            $this->count['label'] = Yii::t('cart/default', 'WP_COUNT', ['num' => $this->count['num']]);
            $this->count['url'] = ['/admin/cart', 'OrderSearch[status_id]' => 1];
        }
        parent::init();
    }

    public function getCountByUser()
    {
        if (!Yii::$app->user->isGuest)
            return Order::find()->where([
                'status_id' => 1,
                'user_id' => Yii::$app->user->id
            ])->count();
    }

    public function bootstrap($app)
    {

        if (!(Yii::$app instanceof yii\console\Application)) {
            if ($this->count)
                $app->counters[$this->id] = $this->count['num'];
        }

    }

    public function getInfo()
    {
        return [
            'label' => Yii::t('cart/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('cart/default', 'MODULE_DESC'),
            'url' => ['/admin/cart'],
        ];
    }

    public function getAdminMenu()
    {
        return [
            'cart' => [
                'label' => Yii::t('cart/admin', 'ORDERS'),
                'icon' => $this->icon,
                'badge' => (isset($this->count['num'])) ? $this->count['num'] : 0,
                'badgeOptions' => ['id' => 'navbar-badge-cart','class' => 'badge badge-success badge-pulse-success'],
                'items' => [
                    [
                        'label' => Yii::t('cart/admin', 'ORDERS_LIST'),
                        'url' => ['/admin/cart'],
                        'badge' => (isset($this->count['num'])) ? $this->count['num'] : 0,
                        'badgeOptions' => ['class' => 'badge badge-success badge-pulse'],
                        'icon' => $this->icon,
                    ],
                    [
                        'label' => Yii::t('cart/admin', 'PROMOCODE'),
                        'url' => ['/admin/cart/promo-code'],
                        'icon' => $this->icon,
                        'visible'=>false
                    ],
                    [
                        'label' => Yii::t('cart/admin', 'STATUSES'),
                        "url" => ['/admin/cart/statuses'],
                        'icon' => 'stats'
                    ],
                    [
                        'label' => Yii::t('cart/admin', 'DELIVERY'),
                        "url" => ['/admin/cart/delivery'],
                        'icon' => 'delivery'
                    ],
                    [
                        'label' => Yii::t('cart/admin', 'PAYMENTS'),
                        "url" => ['/admin/cart/payment'],
                        'icon' => 'creditcard'
                    ],
                    [
                        'label' => Yii::t('app/default', 'SETTINGS'),
                        "url" => ['/admin/cart/settings'],
                        'icon' => 'settings'
                    ]
                ],
            ],
        ];
    }

    public function getAdminSidebar()
    {
        return (new BackendNav())->findMenu($this->id)['items'];
    }

}
