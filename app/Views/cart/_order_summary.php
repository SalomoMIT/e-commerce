<div class="col-sm-12 col-lg-6 order-summary-container">
    <h2 class="cart-section-title"><?= trans("order_summary"); ?> (<?= esc($cart->num_items); ?>)</h2>
    <div class="right">
        <div class="cart-order-details">
            <?php if (!empty($groupedSellers)):
                foreach ($groupedSellers as $sellerGroup): ?>

                    <!-- ROW TOKO -->
                    <div class="row-custom m-t-15 m-b-10">
                        <strong>
                            <?= trans("seller"); ?> :
                            <a href="<?= generateProfileUrl($sellerGroup->seller_slug); ?>">
                                <?= esc($sellerGroup->seller_username); ?>
                            </a>
                        </strong>
                    </div>

                    <?php foreach ($sellerGroup->items as $cartItem): ?>                    
                        <div class="item">
                            <div class="item-left">
                                <a href="<?= esc($cartItem->product_url); ?>">
                                    <div class="product-image-box product-image-box-xs">
                                        <img data-src="<?= getOrderImageUrl($cartItem->product_image_data, $cartItem->product_id); ?>" 
                                            alt="<?= esc($cartItem->product_title); ?>" 
                                            class="lazyload img-fluid img-product">
                                    </div>
                                </a>
                            </div>

                            <div class="item-right">
                                <?php if ($cartItem->product_type == 'digital'): ?>
                                    <div class="list-item">
                                        <label class="badge badge-success-light badge-instant-download">
                                            ...svg...
                                            <?= trans("instant_download"); ?>
                                        </label>
                                    </div>
                                <?php endif; ?>

                                <div class="list-item">
                                    <a href="<?= esc($cartItem->product_url); ?>">
                                        <?= esc($cartItem->product_title); ?>
                                    </a>
                                </div>

                                <?php if (!empty($cartItem->product_options_summary)): ?>
                                    <div class="product-variant-info">
                                        <?= $cartItem->product_options_summary; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="list-item m-t-15">
                                    <label><?= trans("quantity"); ?>:</label>
                                    <strong class="lbl-price"><?= $cartItem->quantity; ?></strong>
                                </div>

                                <div class="list-item">
                                    <label><?= trans("price"); ?>:</label>
                                    <strong class="lbl-price">
                                        <?= priceDecimal($cartItem->total_price, $cart->currency_code); ?>
                                    </strong>
                                </div>

                                <?php if (!empty($cartItem->product_vat) && $cartItem->product_vat > 0): ?>
                                    <div class="list-item">
                                        <label><?= trans("vat"); ?>&nbsp;(<?= $cartItem->product_vat_rate; ?>%):</label>
                                        <strong><?= priceDecimal($cartItem->product_vat, $cart->currency_code); ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

            <?php endforeach;
            endif; ?>
        </div>
        <div class="row-custom m-t-30 m-b-10">
            <strong><?= trans("subtotal"); ?><span class="float-right"><?= priceDecimal($cart->totals->subtotal, $cart->currency_code); ?></span></strong>
        </div>
        <?php if ($cart->totals->affiliate_discount > 0): ?>
            <div class="row-custom m-b-10">
                <strong><?= trans("referral_discount"); ?>&nbsp;(<?= $cart->totals->affiliate_discount_rate; ?>%)<span class="float-right">-&nbsp;<?= priceDecimal($cart->totals->affiliate_discount, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif;
        if (!empty($cart->totals->vat) && $cart->totals->vat > 0):?>
            <div class="row-custom m-b-10">
                <strong><?= trans("vat"); ?><span class="float-right"><?= priceDecimal($cart->totals->vat, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif;
        if (!empty($cart->totals->shipping_cost) && $cart->totals->shipping_cost > 0): ?>
            <div class="row-custom m-b-10">
                <strong><?= trans("shipping"); ?><span class="float-right"><?= priceDecimal($cart->totals->shipping_cost, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif;
        if (!empty($cart->coupon_code)): ?>
            <div class="row-custom m-b-10">
                <strong><?= trans("coupon"); ?>&nbsp;&nbsp;[<?= esc($cart->coupon_code); ?>]&nbsp;&nbsp;<a href="javascript:void(0)" class="font-weight-normal" onclick="removeCartDiscountCoupon();">[<?= trans("remove"); ?>]</a><span class="float-right">-&nbsp;<?= priceDecimal($cart->totals->coupon_discount, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif;
        if (!empty($cart->totals->global_taxes_array)):
            foreach ($cart->totals->global_taxes_array as $taxItem):?>
                <div class="row-custom m-b-10">
                    <strong><?= esc(getTaxName($taxItem['taxNameArray'], selectedLangId())); ?>&nbsp;(<?= $taxItem['taxRate']; ?>%)<span class="float-right"><?= priceDecimal($taxItem['taxTotal'], $cart->currency_code); ?></span></strong>
                </div>
            <?php endforeach;
        endif;
        if (!empty($cart->totals->transaction_fee)): ?>
            <div class="row-custom m-b-15">
                <strong><?= trans("transaction_fee"); ?><?= $cart->totals->transaction_fee_rate ? ' (' . numToDecimal($cart->totals->transaction_fee_rate) . '%)' : ''; ?><span class="float-right"><?= priceDecimal($cart->totals->transaction_fee, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif; ?>
        <div class="row-custom">
            <p class="line-seperator"></p>
        </div>
        <?php if (!empty($cart->totals->shipping_cost)): ?>
            <div class="row-custom">
                <strong><?= trans("total"); ?><span class="float-right"><?= priceDecimal($cart->totals->total, $cart->currency_code); ?></span></strong>
            </div>
        <?php else: ?>
            <div class="row-custom">
                <strong><?= trans("total"); ?><span class="float-right"><?= priceDecimal($cart->totals->total_before_shipping, $cart->currency_code); ?></span></strong>
            </div>
        <?php endif; ?>
    </div>
</div>