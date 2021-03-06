<?php

namespace shopium\mod\cart\models\forms;

use Yii;
use shopium\mod\cart\models\Delivery;
use shopium\mod\cart\models\Payment;
use panix\engine\base\Model;
use panix\engine\CMS;
use shopium\mod\cart\models\PromoCode;
use shopium\mod\user\models\User;

/**
 * Class OrderCreateForm
 * @package shopium\mod\cart\models\forms
 */
class OrderCreateForm extends Model
{

    public static $category = 'cart';
    protected $module = 'cart';
    public $user_name;
    public $user_phone;
    public $user_address;
    public $user_comment;
    public $delivery_id;
    public $payment_id;
    public $promocode_id;

    //delivery
    public $delivery_city; //for delivery systems;
    public $delivery_address; //for delivery systems;
    public $delivery_type; //for delivery systems;
    public function init()
    {
        $user = Yii::$app->user;
        if (!$user->isGuest && Yii::$app->controller instanceof \panix\engine\controllers\WebController) {
            // NEED CONFINGURE
            $this->user_name = $user->getDisplayName();
            $this->user_phone = $user->phone;
            //$this->user_address = Yii::app()->user->address; //comment for april
            //$this->user_email = $user->getEmail();

        } else {
            //  $this->_password = User::encodePassword(CMS::gen((int) Yii::$app->settings->get('users', 'min_password') + 2));
        }

        parent::init();
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        // $scenarios['create-form-order'] = ['payment_id','user_phone','delivery_id','promocode_id','user_comment'];//Scenario Values Only Accepted
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['user_name', 'user_phone', 'user_address'], 'required'],
            [['delivery_id', 'payment_id'], 'required'],
            [['delivery_id', 'payment_id', 'promocode_id'], 'integer'],//
            ['user_comment', 'string'],
            [['user_address','delivery_city','delivery_address'], 'string', 'max' => 255],
            [['user_phone'], 'string', 'max' => 30],
            ['delivery_id', 'validateDelivery'],
            ['payment_id', 'validatePayment'],
			['user_phone', 'panix\ext\telinput\PhoneInputValidator'],
            //['promocode_id', 'validatePromoCode','on'=>['create-form-order']],
        ];
    }

    public function beforeValidate()
    {
        $p = PromoCode::find()->where(['code' => $this->promocode_id])->one();
        if ($p) {
            $this->promocode_id = $p->id;
        }
        return parent::beforeValidate();
    }

    public function afterValidate()
    {

        parent::afterValidate();
    }

    public function validatePromoCode()
    {


    }

    public function validateDelivery()
    {
        if (Delivery::find()->where(['id' => $this->delivery_id])->count() == 0)
            $this->addError('delivery_id', Yii::t('cart/OrderCreateForm', 'VALID_DELIVERY'));
    }

    public function validatePayment()
    {
        if (Payment::find()->where(['id' => $this->payment_id])->count() == 0)
            $this->addError('payment_id', Yii::t('cart/OrderCreateForm', 'VALID_PAYMENT'));
    }

}
