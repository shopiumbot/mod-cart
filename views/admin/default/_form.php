<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use shopium\mod\cart\models\OrderStatus;
use shopium\mod\cart\models\Payment;
use shopium\mod\cart\models\Delivery;
use panix\engine\bootstrap\ActiveForm;

?>
<?php
$form = ActiveForm::begin([
    'fieldConfig' => [
        'template' => "<div class=\"col-sm-5 col-md-5 col-lg-4 col-xl-4\">{label}</div>\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-form-label',
            'offset' => 'offset-sm-5 offset-lg-4 offset-xl-4',
            'wrapper' => 'col-sm-7 col-md-7 col-lg-8 col-xl-8',
            'error' => '',
            'hint' => '',
        ],
    ]
]);
?>
    <div class="card-body">

        <?=
        $form->field($model, 'status_id')->dropDownList(ArrayHelper::map(OrderStatus::find()->all(), 'id', 'name'));
        ?>
        <?=
        $form->field($model, 'payment_id')->dropDownList(ArrayHelper::map(Payment::find()->all(), 'id', 'name'), [
            'prompt' => html_entity_decode(Yii::t('cart/Order','SELECT_PAYMENT'))
        ]);
        ?>
        <?=
        $form->field($model, 'delivery_id')->dropDownList(ArrayHelper::map(Delivery::find()->all(), 'id', 'name'), [
            'prompt' => html_entity_decode(Yii::t('cart/Order','SELECT_DELIVERY'))
        ]);
        ?>
        <?= $form->field($model, 'ttn')->textInput()->hint('После заполнение ТТН, клиенту будет отправлено уведомление на почту.'); ?>
        <?= $form->field($model, 'paid')->checkbox(); ?>
        <?= $form->field($model, 'user_name')->textInput(); ?>
        <?= $form->field($model, 'user_address')->textInput(); ?>
        <?= $form->field($model, 'user_email')->textInput(); ?>
        <?= $form->field($model, 'user_phone')->widget(\panix\ext\telinput\PhoneInput::class); ?>
        <?= $form->field($model, 'user_comment')->textArea(); ?>
        <?= $form->field($model, 'admin_comment')->textArea(); ?>

        <?= $form->field($model, 'discount')->textInput(); ?>
        <?= $form->field($model, 'invoice')->textInput(['maxlength' => 50]); ?>
    </div>
    <div class="card-footer text-center">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/default', 'CREATE') : Yii::t('app/default', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>