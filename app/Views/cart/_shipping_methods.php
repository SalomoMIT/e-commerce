<?php $hasMethods = false;
$showButton = true;

if (!empty($shippingMethods)) {
    foreach ($shippingMethods as $shippingMethod) {
        if (!empty($shippingMethod->methods) && countItems($shippingMethod->methods) > 0) {
            $hasMethods = true;
        } else {
            $showButton = false;
        }
    }
}

if ($hasMethods == false):
    if (!empty($stateId) && $stateId > 0):?>
        <p class="msg-no-delivery text-danger"><?= trans("no_delivery_is_made_to_address"); ?></p>
    <?php endif;
else: ?>
    <div class="row">
        <div class="col-12 m-t-60">
            <p class="text-shipping-address"><?= trans("shipping_method"); ?></p>
        </div>
        <?php if (countItems($shippingMethods) > 1 && !empty($shippingMethods[0]->methods)): ?>
            <div class="col-12">
                <p><?= trans("products_sent_different_stores"); ?></p>
            </div>
        <?php endif; ?>
    </div>


    <?php if (!empty($shippingMethods)):
        foreach ($shippingMethods as $shippingMethod): ?>
            <div class="row">
                <div class="col-12 cart-seller-shipping-options">
                    <p class="p-cart-shop">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 640 512">
                            <path fill="#666666" d="M320 384H128V224H64v256c0 17.7 14.3 32 32 32h256c17.7 0 32-14.3 32-32V224h-64zm314.6-241.8l-85.3-128c-6-8.9-16-14.2-26.7-14.2H117.4c-10.7 0-20.7 5.3-26.6 14.2l-85.3 128c-14.2 21.3 1 49.8 26.6 49.8H608c25.5 0 40.7-28.5 26.6-49.8M512 496c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V224h-64z"/>
                        </svg>&nbsp;&nbsp;
                        <strong><?= esc($shippingMethod->shop_name); ?></strong>
                    </p>
                    <?php if (empty($shippingMethod->methods)): ?>
                        <p class="text-danger font-700"><?= trans("seller_does_not_ship_to_address"); ?></p>
                    <?php else: ?>
                        <div class="cart-options-list">
                            <?php $i = 0;
                            foreach ($shippingMethod->methods as $method): ?>

                                <div class="option-card <?= $i == 0 ? 'is-selected' : ''; ?>">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input option-radio" name="shipping_method_<?= $shippingMethod->shop_id; ?>" value="<?= $method->id; ?>" id="shipping_method_<?= $method->id; ?>" <?= $i == 0 ? 'checked' : ''; ?> required>
                                        <label class="custom-control-label" for="shipping_method_<?= $method->id; ?>"></label>
                                    </div>
                                    <div class="option-details">
                                        <div class="method-name"><?= esc($method->name); ?></div>
                                        <div class="method-desc"><?php
                                            if ($method->method == 'free_shipping') {
                                                echo trans("free_shipping_exp");
                                            } elseif ($method->method == 'local_pickup') {
                                                echo trans("local_pickup_exp");
                                            } elseif ($method->method == 'flat_rate') {
                                                echo trans("flat_rate_exp");
                                            } ?>
                                        </div>
                                    </div>
                                    <div class="option-cost">
                                        <?= priceDecimal($method->cost, $selectedCurrency->code, true); ?>
                                    </div>
                                </div>

                                <?php $i++;
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach;
    endif; ?>

    <?php if ($showButton == true): ?>
        <div class="row">
            <div class="col-12 m-t-30 text-right">
                <button type="submit" name="submit" value="update" class="btn btn-lg btn-custom btn-cart-shipping"><?= trans("continue_to_payment_method") ?>&nbsp;&nbsp;<i class="icon-arrow-right m-0"></i></button>
            </div>
        </div>
    <?php endif;
endif; ?>


<?php if (empty($stateId)): ?>
    <div id="cartShippingError" class="m-b-15" style="display: none;">
        <div class="alert alert-danger alert-message">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg><?= trans("msg_cart_shipping"); ?>
        </div>
    </div>

    <div class="form-group m-t-60 text-right">
        <button type="button" id="btnShowCartShippingError" class="btn btn-lg btn-custom btn-cart-shipping"><?= trans("continue_to_payment_method") ?>&nbsp;&nbsp;<i class="icon-arrow-right m-0"></i></button>
    </div>
<?php endif; ?>


<div class="row">
    <div class="col-12 m-t-30">
        <a href="<?= generateUrl('cart'); ?>" class="link-underlined link-return-cart"><&nbsp;<?= trans("return_to_cart"); ?></a>
    </div>
</div>
