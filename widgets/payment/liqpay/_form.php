<h5 class="text-center mb-4">Настройки</h5>
<?php
use panix\engine\bootstrap\ActiveForm;
use panix\engine\Html;

$form = ActiveForm::begin([]);
?>
<?= $form->field($model, 'key')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'commission_check')->checkbox() ?>

<?php ActiveForm::end(); ?>
