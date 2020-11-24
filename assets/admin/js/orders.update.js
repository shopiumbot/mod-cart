/**
 * Show dialog
 * @param order_id
 */
function openAddProductDialog(order_id) {
    $("#dialog-modal").dialog({
        width: '80%',
        modal: true,
        responsive: true,
        resizable: false,
        height: 'auto',
        draggable: false,
        open: function () {
            $('.ui-widget-overlay').bind('click', function () {
                $('#dialog-modal').dialog('close');
            });
        },
        close: function () {
            $('#dialog-modal').dialog('close');
        }
    });
    $('.ui-dialog').position({
        my: 'center',
        at: 'center',
        of: window,
        collision: 'fit'
    });
}

/**
 * Add product to order
 * @param el
 * @param order_id
 * @returns {boolean}
 */
function addProductToOrder(el, order_id) {
    var product_id = $(el).attr('href');

    var quantity = $('#count_' + product_id).val();
    var price = $('#price_' + product_id).val();
    var csrfParam = yii.getCsrfParam();
    var csrfToken = yii.getCsrfToken();
    console.log(csrfToken,csrfParam);
    $.ajax({
        url: common.baseUrl+"/cart/default/add-product",
        type: "POST",
        data: {
           // '"+csrfParam+"': csrfToken,
            order_id: order_id,
            product_id: product_id,
            quantity: quantity,
            price: price
        },
        dataType: "json",
        success: function (data) {
            if (data.success) {
                reloadOrderedProducts(order_id);
                common.notify(data.message, 'success');
            } else {
                common.notify(data.message, 'error');
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            if (xhr.status !== 200) {
                common.notify(xhr.status, 'error');
            }
        }
    });

    return false;
}

/**
 * Delete ordered product
 * @param id
 * @param order_id
 */
function deleteOrderedProduct(id, order_id) {
    if (confirm(deleteQuestion)) {
        $.ajax({
            url: common.baseUrl+"/cart/default/delete-product",
            type: "POST",
            data: {
                token: common.token,
                id: id,
                order_id: order_id
            },
            dataType: "html",
            success: function () {
                reloadOrderedProducts(order_id);
            }
        });
    }
}

/**
 * Update products list
 */
function reloadOrderedProducts(order_id) {
    $('#orderedProducts').load(common.baseUrl+'/cart/default/render-ordered-products?order_id=' + order_id);
}

/**
 * Recount total price on change delivery method
 * @param el
 */
function recountOrderTotalPrice(el) {
    var deliveryMethod = searchDeliveryMethodById($(el).val());

    if (!deliveryMethod) {
        return;
    }

    var total = parseFloat(orderTotalPrice);
    var delivery_price = parseFloat(deliveryMethod.price);
    var free_from = parseFloat(deliveryMethod.free_from);

    if (delivery_price > 0) {
        if (free_from > 0 && total > free_from) {
            $("#orderDeliveryPrice").html('0.00');
        } else {
            total = total + delivery_price;
            $("#orderDeliveryPrice").html(delivery_price.toFixed(2));
        }
    } else {
        $("#orderDeliveryPrice").html('0.00');
    }

    $('#orderSummary').html(total.toFixed(2));
}

/**
 * @param id
 */
function searchDeliveryMethodById(id) {
    var result = false;
    $(deliveryMethods).each(function () {
        if (parseInt(this.id) === parseInt(id)) {
            result = this;
        }
    });

    return result;
}

