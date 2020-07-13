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
]);
?>
<?php Pjax::end(); ?>
