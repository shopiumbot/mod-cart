<?php
use panix\engine\CMS;
use panix\engine\Html;
use panix\engine\jui\DatePicker;

?>
<div class="card">
    <div class="card-header">
        <h5>Фильтры</h5>
    </div>
    <div class="card-body">
        <?= Html::beginForm(['/admin/cart/default/pdf-orders'], 'GET'); ?>

        <div class="form-group mb-0">
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">с</span>
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
                    <span class="input-group-text">по</span>
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

                echo Html::dropDownList('render', 'delivery', ['delivery' => 'Распределить по доставке', 'manufacturer' => 'Распределить по производителю', 'supplier' => 'Распределить по поставщику'], ['class' => 'custom-select']);
                ?>
                <?php
                echo Html::dropDownList('type', 1, [1 => 'PDF', 0 => 'Html'], ['class' => 'custom-select']);

                ?>

                <div class="input-group-prepend">
                        <span class="input-group-text">
                            <div class="custom-control custom-checkbox">
                        <?= Html::checkBox('image', true, ['id' => 'image', 'class' => 'custom-control-input']); ?>
                        <?= Html::label('Картинки', 'image', ['class' => 'custom-control-label']); ?>
                                </div>
                            </span>
                </div>
                <div class="input-group-prepend">
                    <?= Html::submitButton('Показать', ['class' => 'btn btn-success', 'name' => '']); ?>
                </div>
            </div>
        </div>

        <?= Html::endForm(); ?>
    </div>
</div>


