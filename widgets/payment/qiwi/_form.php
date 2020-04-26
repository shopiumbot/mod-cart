<?php
use panix\engine\bootstrap\ActiveForm;
use panix\engine\Html;

$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal']
]);
?>
<?php


?>
<?= $form->field($model, 'shop_id')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'password')->textInput(['maxlength' => 255]) ?>




<div class="form-group text-center">
    <?= Html::submitButton(Yii::t('app/default', 'UPDATE'), ['class' => 'btn btn-success']) ?>
</div>


<?php ActiveForm::end(); ?>
