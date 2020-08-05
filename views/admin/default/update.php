<?php

use yii\helpers\Html;

use panix\ext\fancybox\Fancybox;

/**
 * @var \yii\web\View $this ;
 * @var \shopium\mod\cart\models\Order $model
 */

//$model->amoCRM($model);

/*
$amo = new \AmoCRM\Client('pixelion', 'andrew.panix@gmail.com', 'b58823639ceb496decfc9ec1ebfd4f963783bbf9');
$lead = $amo->custom_field->getValues();
$catalog_id = 3055;


$c = $amo->catalog;
$findCatalog = $c->apiList([
    'catalog_id' => $catalog_id,
    //'term' => $productName
]);

\panix\engine\CMS::dump($findCatalog);

$p = $amo->catalog_element;
$productName = '[123] test';

$findProduct = $p->apiList([
    'catalog_id' => $catalog_id,
    //'term' => $productName
]);
\panix\engine\CMS::dump($p);die;
$p['catalog_id'] = $catalog_id;
$p['name'] = $productName;
$p['SKU'] = 'zzzz';
//$p->addCustomField(182209, 'TEST SKU', false, 'subtype'); //sku
$p->addCustomField(182215, 5241, false, 'subtype'); // group
$p->addCustomField(182213, 100, false, 'subtype'); //цена
$pid = $p->apiAdd();
\panix\engine\CMS::dump($findProduct);



$field = $amo->custom_field;
//$field->debug(true); // Режим отладки
$field['name'] = 'Tracking ID [test]';
$field['type'] = \AmoCRM\Models\CustomField::TYPE_TEXT;
$field['element_type'] = \AmoCRM\Models\CustomField::ENTITY_CONTACT;
$field['origin'] = '528d0285c1f9180911159a9dc6f759b3_zendesk_widget';

//$id = $field->apiAdd();
//\panix\engine\CMS::dump($id);
*/
?>
<div class="row">
    <div class="col-sm-6">
        <div class="card">
            <div class="card-header">
                <h5 class="float-left"><?= Html::encode($this->context->pageName) ?></h5>
                <?php if (!$model->isNewRecord) { ?>
                    <span class="badge badge-secondary float-right"><?= \panix\engine\CMS::date($model->created_at); ?></span>
                <?php } ?>
            </div>
            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
    <div class="col-sm-6">
        <?= Fancybox::widget(['target' => '.image a']); ?>
        <?php

        //echo Html::a('add', 'javascript:openAddProductDialog(' . $model->id . ');', ['class' => 'btn btn-success']);
        if (!$model->isNewRecord) {
            ?>

            <div id="dialog-modal" style="display: none;" title="<?php echo Yii::t('cart/admin', 'CREATE_PRODUCT') ?>">
                <?php
                echo $this->render('_addProduct', array(
                    'model' => $model,
                ));
                ?>
            </div>
            <div id="orderedProducts">
                <?php
                if (!$model->isNewRecord) {
                    echo $this->render('_order-products', ['model' => $model]);
                }
                ?>
            </div>

        <?php } else { ?>
            <div class="alert alert-info">Товары можно будет добавить после создание заказа</div>
        <?php } ?>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h5>История действий заказа</h5>
    </div>
    <div class="card-body">
        <?php
        echo $this->render('_history', ['model' => $model]);
        ?>
    </div>
</div>

