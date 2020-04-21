<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use shopium\mod\cart\models\Payment;
use panix\ext\tinymce\TinyMce;

?>

<?php
$form = ActiveForm::begin();
?>
<div class="card">
    <div class="card-header">
        <h5><?= Html::encode($this->context->pageName) ?></h5>
    </div>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'price')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'free_from')->textInput(['maxlength' => 255]) ?>

        <div id="delivery_configuration"></div>

        <div id="payment_configuration"></div>

    </div>
    <div class="card-footer text-center">
        <?= $model->submitButton(); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
