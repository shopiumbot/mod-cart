<div class="cart">
   <a href="/cart" class="cart-info">
        <?php if ($count > 0) { ?>
            (<span class=""><?=$count?></span>) / <span><?= $total; ?></span> <small><?= $currency['symbol']; ?></small>
        <?php } else { ?>
            <span class=""><?= Yii::t('cart/default', 'CART_EMPTY') ?></span>
        <?php } ?>
    </a>
</div>
