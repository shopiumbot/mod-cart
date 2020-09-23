<h5 class="text-center mb-4">Настройки</h5>
<?php
use panix\engine\bootstrap\ActiveForm;
use panix\engine\Html;

$form = ActiveForm::begin([]);
?>
<?= $form->field($model, 'public_key')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'private_key')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'commission_user')->dropDownList(['user'=>'User','self'=>'Мы']) ?>

<?php ActiveForm::end(); ?>
