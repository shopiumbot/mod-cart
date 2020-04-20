<?php

use panix\engine\Html;
use panix\engine\widgets\Pjax;
use panix\engine\grid\GridView;
use shopium\mod\cart\models\Order;

?>

<?php echo $this->render('_filter_pdf'); ?>
<?php

Pjax::begin(['dataProvider'=>$dataProvider]);

echo GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'showFooter' => true,
    'footerRowOptions' => ['style' => 'font-weight:bold;', 'class' => 'text-center'],
    //'rowOptions' => function ($model, $index, $widget, $grid) {
    //    return ['style' => 'background-color:' . $model->status->color . ';'];
    //},
    'layoutOptions' => ['title' => $this->context->pageName],
    'columns' => [
        [
            'attribute' => 'id',
            'header' => Yii::t('cart/Order', 'ORDER_ID'),
            'format' => 'html',
            'contentOptions' => ['class' => 'text-left'],
            'value' => function ($model) {
                /** @var $model Order */
                return $model->getGridStatus() . ' ' . $model::t('NEW_ORDER_ID', ['id' => \panix\engine\CMS::idToNumber($model->id)]);
            }
        ],
        'user_name',
        [
            'attribute' => 'user_email',
            'format' => 'email',
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'attribute' => 'user_phone',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                /** @var $model Order */
                return Html::tel($model->user_phone);
            }
        ],
        [
            'attribute' => 'total_price',
            'format' => 'html',
            'class' => 'panix\engine\grid\columns\jui\SliderColumn',
            'max' => (int)Order::find()->aggregateTotalPrice('MAX'),
            'min' => (int)Order::find()->aggregateTotalPrice('MIN'),
            'prefix' => '<sup>' . Yii::$app->currency->main['symbol'] . '</sup>',
            'contentOptions' => ['class' => 'text-center'],
            'minCallback' => function ($value) {
                return Yii::$app->currency->number_format($value);
            },
            'maxCallback' => function ($value) {
                return Yii::$app->currency->number_format($value);
            },
            'value' => function ($model) {
                /** @var $model Order */
                $priceHtml = Yii::$app->currency->number_format(Yii::$app->currency->convert($model->total_price));
                $symbol = Html::tag('sup', Yii::$app->currency->main['symbol']);
                return Html::tag('span', $priceHtml, ['class' => 'text-success font-weight-bold']) . ' ' . $symbol;
            }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{view} {print} {update}',
            'buttons' => [
                'print' => function ($url, $model, $key) {
                    return Html::a(Html::icon('print'), ['print', 'id' => $model->id], [
                        'title' => Yii::t('cart/admin', 'ORDER_PRINT'),
                        'class' => 'btn btn-sm btn-info',
                        'data-pjax' => 0,
                        'target' => '_blank'
                    ]);
                },
                'view' => function ($url, $model, $key) {
                    return Html::a(Html::icon('search'), $model->getUrl(), [
                        'title' => Yii::t('cart/admin', 'ORDER_VIEW'),
                        'class' => 'btn btn-sm btn-outline-secondary',
                        'data-pjax' => 0,
                        'target' => '_blank'
                    ]);
                }
            ]
        ]
    ]
]);
?>
<?php Pjax::end(); ?>
