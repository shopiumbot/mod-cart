<?php
use panix\engine\CMS;
use panix\engine\Html;
use panix\engine\jui\DatePicker;

?>
<div class="card">
    <div class="card-header">
        <h5><?= Yii::t('cart/admin', 'FILTERS'); ?></h5>
    </div>
    <div class="card-body">
        <?= Html::beginForm(['/cart/default/pdf-orders'], 'GET'); ?>

        <div class="form-group mb-0">
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text"><?= Yii::t('cart/admin','FROM'); ?></span>
                </div>
                <?php
                echo DatePicker::widget([
                    'name' => 'start',
                    'value' => (Yii::$app->request->get('start')) ? Yii::$app->request->get('start') : date('Y-m-d'),
                    //'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control']
                ]);
                ?>
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= Yii::t('cart/admin','TO'); ?></span>
                </div>
                <?php
                echo DatePicker::widget([
                    'name' => 'end',
                    'value' => (Yii::$app->request->get('end')) ? Yii::$app->request->get('end') : date('Y-m-d'),
                    //'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'options' => ['class' => 'form-control']
                ]);
                ?>
                <?php

                echo Html::dropDownList('render', 'delivery', [
                    'delivery' => Yii::t('cart/admin','RENDER_DELIVERY'),
                    'manufacturer' => Yii::t('cart/admin','RENDER_MANUFACTURER'),
                ], ['class' => 'custom-select']);
                ?>
                <?php
                echo Html::dropDownList('type', 1, [1 => 'PDF', 0 => 'Html'], ['class' => 'custom-select']);

                ?>

                <div class="input-group-prepend">
                        <span class="input-group-text">
                            <div class="custom-control custom-checkbox">
                        <?= Html::checkBox('image', true, ['id' => 'image', 'class' => 'custom-control-input']); ?>
                        <?= Html::label(Yii::t('cart/admin', 'IMAGES'), 'image', ['class' => 'custom-control-label']); ?>
                                </div>
                            </span>
                </div>
                <div class="input-group-prepend">
                    <?= Html::submitButton(Yii::t('cart/admin', 'SHOW'), ['class' => 'btn btn-success', 'name' => '']); ?>
                </div>
            </div>
        </div>

        <?= Html::endForm(); ?>
    </div>
</div>


