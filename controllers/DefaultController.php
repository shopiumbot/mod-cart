<?php

namespace shopium\mod\cart\controllers;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;
use panix\engine\CMS;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use core\components\controllers\AdminController;
use core\modules\shop\models\Product;
use shopium\mod\cart\models\Order;
use shopium\mod\cart\models\OrderProduct;
use core\modules\shop\models\search\ProductSearch;
use shopium\mod\cart\models\search\OrderSearch;
use Mpdf\Mpdf;

class DefaultController extends AdminController
{

    public function actions()
    {
        return [
            'delete' => [
                'class' => 'panix\engine\actions\DeleteAction',
                'modelClass' => Order::class,
            ],
        ];
    }

    public function actionPrint($id)
    {
        $currentDate = CMS::date(time());
        $model = Order::findModel($id);
        $title = Yii::t('cart/Order', 'NEW_ORDER_ID', ['id' => CMS::idToNumber($model->id)]);
        $mpdf = new Mpdf([
            // 'debug' => true,
            //'mode' => 'utf-8',
            'default_font_size' => 9,
            'default_font' => 'times',
            'margin_top' => 25,
            'margin_bottom' => 9,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_footer' => 5,
            'margin_header' => 5,
        ]);

        $mpdf->SetCreator(Yii::$app->name);
        $mpdf->SetAuthor(Yii::$app->user->getDisplayName());

        //$mpdf->SetProtection(['copy','print'], 'asdsad', 'MyPassword');
        $mpdf->SetTitle($title);
        $mpdf->SetHTMLFooter($this->renderPartial('@theme/views/pdf/footer', ['currentDate' => $currentDate]));
        $mpdf->SetHTMLHeader($this->renderPartial('pdf/_header_order', [

            'model' => $model
        ]));
        $mpdf->WriteHTML(file_get_contents(Yii::getAlias('@vendor/panix/engine/pdf/assets/mpdf-bootstrap.min.css')), 1);
        $mpdf->WriteHTML($this->renderPartial('_pdf_order', ['model' => $model]), 2);
        echo $mpdf->Output(Yii::t('cart/Order', 'NEW_ORDER_ID', ['id' => CMS::idToNumber($model->id)]) . ".pdf", 'I');
        die;
    }

    public function actionIndex()
    {
        $this->pageName = Yii::t('cart/admin', 'ORDERS');
        $this->buttons = [
            [
                'label' => Yii::t('cart/admin', 'CREATE_ORDER'),
                'url' => ['create'],
                'icon' => 'add',
                'options' => ['class' => 'btn btn-success']
            ]
        ];

        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id = false)
    {
        $model = Order::findModel($id, Yii::t('cart/admin', 'ORDER_NOT_FOUND'));
        $isNew = $model->isNewRecord;
        $this->pageName = ($isNew) ? Yii::t('cart/Order', 'CREATE_ORDER') : Yii::t('cart/Order', 'NEW_ORDER_ID', ['id' => CMS::idToNumber($model->id)]);
        $this->view->params['breadcrumbs'] = [
            [
                'label' => Yii::t('cart/admin', 'ORDERS'),
                'url' => ['index']
            ],
            $this->pageName
        ];
        \shopium\mod\cart\OrderAsset::register($this->view);
        $this->view->registerJs('
            var deleteQuestion = "' . Yii::t('cart/admin', 'Вы действительно удалить запись?') . '";
            var productSuccessAddedToOrder = "' . Yii::t('cart/admin', 'Продукт успешно добавлен к заказу.') . '";', \yii\web\View::POS_HEAD, 'myid'
        );

        if (!$isNew) {
            $this->buttons = [
                [
                    'label' => Yii::t('cart/admin', 'PRINT_PDF'),
                    'icon' => 'print',
                    'url' => ['print', 'id' => $model->id],
                    'options' => ['class' => 'btn btn-primary', 'target' => '_blank']
                ]
            ];
        }
        $old = $model->oldAttributes;
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $model->save();
            $api = Yii::$app->telegram;
            if ($old['status_id'] != $model->status_id) {
                $data['chat_id'] = $model->user_id;
                $data['parse_mode'] = 'Markdown';
                $data['text'] = "Ваш заказ *№" . CMS::idToNumber($model->id) . "*" . PHP_EOL;
                $data['text'] .= "Статус: *{$model->status->name}*";
                $response = Request::sendMessage($data);
                if ($response->isOk()) {
                    $db = DB::insertMessageRequest($response->getResult());
                }
            }


            if ($old['invoice'] != $model->invoice && !empty($model->invoice)) {
                $data['chat_id'] = $model->user_id;
                $data['parse_mode'] = 'Markdown';
                $data['text'] = "Ваш заказ *№" . CMS::idToNumber($model->id) . "*" . PHP_EOL;
                $data['text'] .= "ТТН: *{$model->invoice}*";
                $response = Request::sendMessage($data);
                if ($response->isOk()) {
                    $db = DB::insertMessageRequest($response->getResult());
                }
            }


            if (sizeof(Yii::$app->request->post('quantity', [])))
                $model->setProductQuantities(Yii::$app->request->post('quantity'));

            return $this->redirectPage($isNew, $post);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionAddProductList()
    {

        $request = Yii::$app->request;
        $order_id = $request->post('id');

        $model = Order::findModel($order_id, Yii::t('cart/admin', 'ORDER_NOT_FOUND'));

        if ($order_id) {
            if (!$request->isAjax) {
                return $this->redirect(['/cart/default/update', 'id' => $order_id]);
            }
        }
        if (!$request->isAjax) {
            return $this->redirect(['/cart/default/index']);
        }


        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($request->getQueryParams());


        return $this->renderAjax('_addProduct', [
            'dataProvider' => $dataProvider,
            'order_id' => $order_id,
            'model' => $model,
        ]);
    }

    /**
     * Add product to order
     */
    public function actionAddProduct()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $result = [];
        $request = Yii::$app->request;
        if ($request->isPost) {
            if ($request->isAjax) {
                $order = Order::findModel($request->post('order_id'), Yii::t('cart/admin', 'ORDER_NOT_FOUND'));

                $product = Product::findModel($request->post('product_id'));

                $find = OrderProduct::find()->where(['order_id' => $order->id, 'product_id' => $product->id])->one();

                if ($find) {
                    if ($request->isAjax) {
                        $result = [
                            'success' => false,
                            'message' => Yii::t('cart/admin', 'ERR_ORDER_PRODUCT_EXISTS'),
                        ];

                    }
                }

                if ($request->isAjax) {
                    $result = [
                        'success' => false,
                        'message' => Yii::t('cart/default', 'ERROR_PRODUCT_NO_FIND'),
                    ];

                }


                $order->addProduct($product, $request->post('quantity'), $request->post('price'));
                $result = [
                    'success' => true,
                    'message' => Yii::t('cart/admin', 'SUCCESS_ADD_PRODUCT_ORDER'),
                ];
            } else {
                //throw new CHttpException(500, Yii::t('error', '500'));
            }
        } else {
            //throw new CHttpException(500, Yii::t('error', '500'));
        }

        return $result;
    }

    /**
     * Delete product from order
     */
    public function actionDeleteProduct()
    {
        $order = Order::findModel(Yii::$app->request->post('order_id'), Yii::t('cart/admin', 'ORDER_NOT_FOUND'));

        //if ($order->is_deleted)
        //    throw new NotFoundHttpException(Yii::t('cart/admin', 'ORDER_ISDELETED'));

        $order->deleteProduct(Yii::$app->request->post('id'));
    }

    public function actionRenderOrderedProducts($order_id)
    {
        $this->pageName = Yii::t('cart/default', 'MODULE_NAME');
        return $this->renderAjax('_order-products', array(
            'model' => Order::findModel($order_id)
        ));
    }

    public function actionPdfOrders($render = 'delivery', $type = 0, $start, $end)
    {


        $dateStart = strtotime($start);

        $dateEnd = strtotime($end) + 86400;
        $mpdf = new Mpdf([
            // 'debug' => true,
            //'mode' => 'utf-8',
            'default_font_size' => 9,
            'default_font' => 'times',
            'margin_top' => 35,
            'margin_bottom' => 10,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_footer' => 5,
            'margin_header' => 5,
        ]);
        if ($type) {
            /*Yii::import('ext.tcpdf.TCPDF');
            $contact = Yii::app()->settings->get('contacts');
            $phones = explode(',', $contact['phone']);
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetHeaderData("", "", Yii::app()->settings->get('app', 'site_name'), $phones[0].', '.$phones[1].', 3 konteynernaya, rolet 460');
            //$pdf->SetHeaderData("", "", Yii::app()->settings->get('app', 'site_name'), "phone " . $phones[0]);
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(0, PDF_MARGIN_TOP, 0); //PDF_MARGIN_TOP
            $pdf->SetMargins(10, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(0); //PDF_MARGIN_FOOTER
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); //PDF_MARGIN_BOTTOM
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setJPEGQuality(100);
            $pdf->AddPage();
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('freeserif', '', 12);
            $pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);*/

            $mpdf->use_kwt = true;
            $mpdf->SetCreator(Yii::$app->name);
            $mpdf->SetAuthor(Yii::$app->user->getDisplayName());

            // $mpdf->SetProtection(['print','copy'], 'admin', '111');
            //$mpdf->SetTitle($title);
            $mpdf->SetHTMLFooter($this->renderPartial('pdf/_footer_delivery', ['currentDate' => 'dsadsa']));

            $mpdf->WriteHTML(file_get_contents(Yii::getAlias('@vendor/panix/engine/pdf/assets/mpdf-bootstrap.min.css')), 1);
            // $mpdf->WriteHTML($this->renderPartial('_pdf_order', ['model' => $model]), 2);
            // return $mpdf->Output($model::t('NEW_ORDER_ID', ['id' => CMS::idToNumber($model->id)]) . ".pdf", 'I');

        }


        /* $model = Order::find()->with([
          'products' => function (\yii\db\ActiveQuery $query) {
               $query->andWhere(['not', ['manufacturer_id' => null]]);
           },
       ]);*/


        $model = Order::find()->with('products');
        // $model->where(['status_id' => 1]);
        if ($render == 'delivery') {

            $view = 'pdf/delivery';
            $model->between($dateStart, $dateEnd);
            $model->orderBy(['delivery_id' => SORT_DESC]);

            $mpdf->SetHTMLHeader($this->renderPartial('pdf/_header_delivery', [
                'start_date' => CMS::date($dateStart, false),
                'end_date' => CMS::date($dateEnd, false),
            ]));
        } else {
            $model->joinWith(['products p']);
            $model->between($dateStart, $dateEnd);
            if (Yii::$app->request->get('render') == 'manufacturer') {
                $view = 'pdf/manufacturer';
                $model->andWhere(['not', ['p.manufacturer_id' => null]]);
                $model->orderBy(['p.manufacturer_id' => SORT_DESC]);

            }

            $mpdf->SetHTMLHeader($this->renderPartial('pdf/_header_products', [
                'start_date' => CMS::date($dateStart, false),
                'end_date' => CMS::date($dateEnd, false),
            ]));
        }
        $model = $model->all();


        $array = [];
        if ($type) {
            $mpdf->WriteHTML($this->renderPartial($view, [
                'array' => $array,
                'model' => $model,
                'dateStart' => CMS::date($dateStart),
                //'dateStart' => date('Y-m-d', $dateStart),
                'dateEnd' => date('Y-m-d', $dateEnd - 86400)

            ]), 2);
            $mpdf->Ln();
            echo $mpdf->Output($this->action->id . ".pdf", 'I');
            die;
        } else {
            $this->layout = 'mod.admin.views.layouts.print';
            $this->render($view, [
                'array' => $array,
                'model' => $model,
                'dateStart' => date('Y-m-d', strtotime($dateStart)),
                'dateEnd' => date('Y-m-d', strtotime($dateEnd) - 86400)
            ]);
        }

    }

    public function titleSort($a, $b)
    {
        return strnatcmp($a['title'], $b['title']);
    }
}
