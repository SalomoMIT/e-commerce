<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php if (!empty($cart) && !empty($cart->items)): ?>
                    <div class="shopping-cart">
                        <div class="row">
                            <div class="col-sm-12 col-lg-8">
                                <div class="left">
                                    <h1 class="cart-section-title"><?= trans("my_cart"); ?> (<?= esc($cart->num_items); ?>)</h1>
                                    <?php if (!empty($cart->items)):
                                        foreach ($cart->items as $cartItem): ?>
                                            <div class="item">
                                                <div class="cart-item-image">
                                                    <a href="<?= esc($cartItem->product_url); ?>">
                                                        <div class="product-image-box product-image-box-md">
                                                            <img data-src="<?= getOrderImageUrl($cartItem->product_image_data, $cartItem->product_id); ?>" alt="<?= esc($cartItem->product_title); ?>" class="lazyload img-fluid img-product">
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="cart-item-details">
                                                    <?php if ($cartItem->product_type == 'digital'): ?>
                                                        <div class="list-item">
                                                            <label class="badge badge-success-light badge-instant-download">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                                                </svg>&nbsp;&nbsp;<?= trans("instant_download"); ?>
                                                            </label>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="list-item">
                                                        <div class="product-title m-b-5">
                                                            <a href="<?= esc($cartItem->product_url); ?>"><?= esc($cartItem->product_title); ?></a>
                                                        </div>
                                                        <?php if (!empty($cartItem->product_options_summary)): ?>
                                                            <div class="product-variant-info m-b-5">
                                                                <?= $cartItem->product_options_summary; ?>
                                                            </div>
                                                        <?php endif;
                                                        if (empty($cartItem->is_stock_available)): ?>
                                                            <div class="m-b-5">
                                                                <span class="badge badge-danger-light"><?= trans("out_of_stock"); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="list-item seller m-b-30">
                                                        <div class="badge badge-info-light">
                                                            <?= trans("seller"); ?>:&nbsp;<a href="<?= generateProfileUrl($cartItem->seller_slug); ?>"><?= esc($cartItem->seller_username); ?></a>
                                                        </div>
                                                    </div>
                                                    <?php if ($cartItem->purchase_type != 'bidding'): ?>
                                                        <div class="list-item">
                                                            <label><?= trans("unit_price"); ?>:</label>
                                                            <strong class="lbl-price">
                                                                <?= priceDecimal($cartItem->unit_price, $cart->currency_code); ?>
                                                            </strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="list-item">
                                                        <label><?= trans("total"); ?>:</label>
                                                        <strong class="lbl-price"><?= priceDecimal($cartItem->total_price, $cart->currency_code); ?></strong>
                                                    </div>

                                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-gray btn-cart-remove" onclick="removeFromCart('<?= $cartItem->id; ?>');"><i class="icon-close"></i><span>&nbsp;<?= trans("remove"); ?></span></a>
                                                </div>
                                                <div class="cart-item-quantity">
                                                    <?php if ($cartItem->purchase_type == 'bidding'): ?>
                                                        <span><?= trans("quantity") . ': ' . esc($cartItem->quantity); ?></span>
                                                    <?php else:
                                                        if ($cartItem->product_type != 'digital'):?>
                                                            <div class="number-spinner">
                                                                <div class="input-group">
                                                                        <span class="input-group-btn">
                                                                            <button type="button" class="btn btn-default btn-spinner-minus" data-cart-item-id="<?= $cartItem->id; ?>" data-dir="dwn">-</button>
                                                                        </span>
                                                                    <input type="text" id="q-<?= $cartItem->id; ?>" class="form-control text-center" value="<?= $cartItem->quantity; ?>" data-product-id="<?= $cartItem->product_id; ?>" data-cart-item-id="<?= $cartItem->id; ?>">
                                                                    <span class="input-group-btn">
                                                                            <button type="button" class="btn btn-default btn-spinner-plus" data-cart-item-id="<?= $cartItem->id; ?>" data-dir="up">+</button>
                                                                        </span>
                                                                </div>
                                                            </div>
                                                        <?php endif;
                                                    endif; ?>

                                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-gray btn-cart-remove-mobile" onclick="removeFromCart('<?= $cartItem->id; ?>');"><i class="icon-close"></i><span>&nbsp;<?= trans("remove"); ?></span></a>

                                                </div>
                                            </div>
                                        <?php endforeach;
                                    endif; ?>
                                </div>
                                <a href="<?= langBaseUrl(); ?>" class="btn btn-md btn-custom m-t-30"><i class="icon-arrow-left m-r-2"></i><?= trans("keep_shopping") ?></a>
                            </div>
                            <div class="col-sm-12 col-lg-4">
                                <div class="right">
                                    <div class="row-custom m-b-15">
                                        <strong><?= trans("subtotal"); ?><span class="float-right"><?= priceDecimal($cart->totals->subtotal, $cart->currency_code); ?></span></strong>
                                    </div>
                                    <?php if ($cart->totals->affiliate_discount > 0): ?>
                                        <div class="row-custom m-b-10">
                                            <strong><?= trans("referral_discount"); ?>&nbsp;(<?= $cart->totals->affiliate_discount_rate; ?>%)<span class="float-right">-&nbsp;<?= priceDecimal($cart->totals->affiliate_discount, $cart->currency_code); ?></span></strong>
                                        </div>
                                    <?php endif;
                                    if (!empty($cart->coupon_code)): ?>
                                        <div class="row-custom">
                                            <strong><?= trans("coupon"); ?>&nbsp;&nbsp;[<?= esc($cart->coupon_code); ?>]&nbsp;&nbsp;<a href="javascript:void(0)" class="font-weight-normal" onclick="removeCartDiscountCoupon();">[<?= trans("remove"); ?>]</a><span class="float-right">-&nbsp;<?= priceDecimal($cart->totals->coupon_discount, $cart->currency_code); ?></span></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row-custom">
                                        <p class="line-seperator"></p>
                                    </div>
                                    <div class="row-custom m-b-10">
                                        <strong><?= trans("total"); ?><span class="float-right"><?= priceDecimal($cart->totals->total_before_shipping, $cart->currency_code); ?></span></strong>
                                    </div>
                                    <div class="row-custom m-t-30 m-b-15">
                                        <?php if (empty($cart->is_valid)): ?>
                                            <a href="javascript:void(0)" class="btn btn-block"><?= trans("continue_to_checkout"); ?></a>
                                        <?php else:
                                            $showCheckoutBtn = false;
                                            if (authCheck()) {
                                                $showCheckoutBtn = true;
                                            } else {
                                                if ($generalSettings->guest_checkout == 1) {
                                                    $showCheckoutBtn = true;
                                                }
                                                if ($cart->has_digital_product) {
                                                    $showCheckoutBtn = false;
                                                }
                                            }
                                            if ($showCheckoutBtn):
                                                if ($cart->has_physical_product == true && $productSettings->marketplace_shipping == 1): ?>
                                                    <a href="<?= generateUrl('cart', 'shipping'); ?>" class="btn btn-block"><?= trans("continue_to_checkout"); ?></a>
                                                <?php else: ?>
                                                    <a href="<?= generateUrl('cart', 'payment_method'); ?>" class="btn btn-block"><?= trans("continue_to_checkout"); ?></a>
                                                <?php endif;
                                            else:?>
                                                <a href="#" class="btn btn-block" data-toggle="modal" data-target="#loginModal"><?= trans("continue_to_checkout"); ?></a>
                                            <?php endif;
                                        endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <hr class="m-t-30 m-b-30">
                                    <form action="<?= base_url('cart/coupon-code-post'); ?>" method="post" id="form_validate" class="m-0">
                                        <?= csrf_field(); ?>
                                        <label class="font-600"><?= trans("discount_coupon") ?></label>
                                        <div class="cart-discount-coupon">
                                            <input type="text" name="coupon_code" class="form-control form-input" value="<?= esc(old('coupon_code')); ?>" maxlength="254" placeholder="<?= trans("coupon_code") ?>" required>
                                            <button type="submit" class="btn btn-custom m-l-5"><?= trans("apply") ?></button>
                                        </div>
                                    </form>
                                    <div class="cart-coupon-error">
                                        <?php if (!empty(helperGetSession('error_coupon_code'))): ?>
                                            <div class="text-danger">
                                                <?= helperGetSession('error_coupon_code'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="shopping-cart-empty">
                        <p><strong class="font-600"><?= trans("your_cart_is_empty"); ?></strong></p>
                        <a href="<?= langBaseUrl(); ?>" class="btn btn-lg btn-custom"><i class="icon-arrow-left"></i>&nbsp;<?= trans("shop_now"); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>