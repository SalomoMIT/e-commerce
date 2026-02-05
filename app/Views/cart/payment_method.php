<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="shopping-cart shopping-cart-shipping">
                    <div class="row">
                        <div class="col-sm-12 col-lg-8">
                            <div class="left">
                                <h1 class="cart-section-title"><?= trans("checkout"); ?></h1>
                                <?php if (!authCheck()): ?>
                                    <p class="font-600 text-center m-b-30">
                                        <?= trans("checking_out_as_guest"); ?>.&nbsp;<?= trans("have_account"); ?>&nbsp;
                                        <a href="javascript:void(0)" class="link" data-toggle="modal" data-target="#loginModal">
                                            <strong class="link-underlined"><?= trans("login"); ?></strong>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?= view('partials/_messages'); ?>

                                <?php if ($showLocationSelection): ?>
                                    <div class="tab-checkout tab-checkout-open">
                                        <h2 class="title"><?= trans("location"); ?></h2>
                                        <?= view('cart/_cart_location'); ?>
                                    </div>
                                <?php else: ?>

                                <form action="<?= base_url('cart/payment-method-post'); ?>" method="post" id="form_validate" class="validate_terms">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="checkout_type" value="<?= esc($checkoutType); ?>">

                                    <?php if (!empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkoutType == 'product'): ?>
                                        <div class="tab-checkout tab-checkout-closed">
                                            <a href="<?= generateUrl('cart', 'shipping'); ?>"><h2 class="title">1.&nbsp;&nbsp;<?= trans("shipping_information"); ?></h2></a>
                                            <a href="<?= generateUrl('cart', 'shipping'); ?>" class="link-underlined edit-link"><?= trans("edit"); ?></a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="tab-checkout tab-checkout-open">
                                        <h2 class="title">
                                            <?= !empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkoutType == 'product' ? '2.' : '1.'; ?>
                                            &nbsp;<?= trans("payment_method"); ?>
                                        </h2>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="cart-options-list">

                                                    <?php $gateways = getActivePaymentGateways();
                                                    $i = 0;
                                                    if (!empty($gateways)):
                                                        foreach ($gateways as $gateway):?>

                                                            <div class="option-card<?= $i == 0 ? ' is-selected' : ''; ?>">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" name="payment_option" value="<?= esc($gateway->name_key); ?>" id="option_<?= $gateway->id; ?>" class="custom-control-input option-radio" <?= $i == 0 ? 'checked' : ''; ?> required>
                                                                    <label class="custom-control-label" for="option_<?= $gateway->id; ?>"></label>
                                                                </div>
                                                                <div class="option-details">
                                                                    <div class="method-name"><?= esc($gateway->name); ?></div>
                                                                </div>
                                                                <div class="payment-logos">
                                                                    <?php $logos = @explode(',', $gateway->logos);
                                                                    if (!empty($logos) && countItems($logos) > 0):
                                                                        foreach ($logos as $logo): ?>
                                                                            <img src="<?= base_url('assets/img/payment/' . esc(trim($logo ?? '')) . '.svg'); ?>" alt="<?= esc(trim($logo ?? '')); ?>" height="22">
                                                                        <?php endforeach;
                                                                    endif; ?>
                                                                </div>
                                                            </div>

                                                            <?php $i++;
                                                        endforeach;
                                                    endif; ?>

                                                    <?php if ($paymentSettings->bank_transfer_enabled): ?>
                                                        <div class="option-card">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" name="payment_option" value="bank_transfer" id="option_bank_transfer" class="custom-control-input option-radio" <?= $i == 0 ? 'checked' : ''; ?> required>
                                                                <label class="custom-control-label" for="option_bank_transfer"></label>
                                                            </div>
                                                            <div class="option-details">
                                                                <div class="method-name"><?= trans("bank_transfer"); ?></div>
                                                                <div class="method-desc"><?= trans("bank_transfer_exp"); ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endif;
                                                    if (authCheck() && $paymentSettings->cash_on_delivery_enabled && empty($cart->has_digital_product) && $checkoutType == 'product' && $vendorCashOnDelivery == 1 && empty($cartTotal->affiliate_commission)): ?>
                                                        <div class="option-card">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" name="payment_option" value="cash_on_delivery" id="option_cash_on_delivery" class="custom-control-input option-radio" <?= $i == 0 ? 'checked' : ''; ?> required>
                                                                <label class="custom-control-label" for="option_cash_on_delivery"></label>
                                                            </div>
                                                            <div class="option-details">
                                                                <div class="method-name"><?= trans("cash_on_delivery"); ?></div>
                                                                <div class="method-desc"><?= trans("cash_on_delivery_exp"); ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endif;
                                                    if ($paymentSettings->wallet_status == 1 && !empty($payWithBalance) && canPayWithBalance($payWithBalance->total, $payWithBalance->currency)): ?>
                                                        <div class="option-card">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" name="payment_option" value="wallet_balance" id="option_wallet_balance" class="custom-control-input option-radio" <?= $i == 0 ? 'checked' : ''; ?> required>
                                                                <label class="custom-control-label" for="option_wallet_balance"></label>
                                                            </div>
                                                            <div class="option-details">
                                                                <div class="method-name"><?= trans("wallet_balance"); ?>&nbsp;(<b><?= trans("balance"); ?>:&nbsp;<?= priceFormatted(user()->balance, $payWithBalance->currency, true); ?></b>)</div>
                                                                <div class="method-desc"><?= trans("pay_wallet_balance_exp"); ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="form-group m-t-30">
                                                    <div class="custom-control custom-checkbox custom-control-validate-input">
                                                        <input type="checkbox" class="custom-control-input" name="terms" id="checkbox_terms" required>
                                                        <label for="checkbox_terms" class="custom-control-label"><?= trans("terms_conditions_exp"); ?>&nbsp;
                                                            <?php $pageTerms = getPageByDefaultName('terms_conditions', selectedLangId());
                                                            if (!empty($pageTerms)): ?>
                                                                <a href="<?= generateUrl($pageTerms->page_default_name); ?>" class="link-terms" target="_blank"><strong><?= esc($pageTerms->title); ?></strong></a>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                </div>

                                                <?php if (!authCheck()) {
                                                    echo view('partials/_cf_turnstile');
                                                } ?>

                                                <div class="form-group m-t-30 text-right">
                                                    <button type="submit" name="submit" value="update" class="btn btn-lg btn-custom btn-continue-payment"><?= trans("continue_to_payment") ?>&nbsp;&nbsp;<i class="icon-arrow-right m-0"></i></button>
                                                </div>
                                            </div>

                                            <?php if ($checkoutType == 'product'): ?>
                                                <div class="row">
                                                    <div class="col-12 m-t-30">
                                                        <a href="<?= generateUrl('cart'); ?>" class="link-underlined link-return-cart"><&nbsp;<?= trans("return_to_cart"); ?></a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                    <div class="tab-checkout tab-checkout-closed-bordered">
                                        <h2 class="title">
                                            <?= !empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkoutType == 'product' ? '3.' : '2.'; ?>
                                            &nbsp;<?= trans("payment"); ?>
                                        </h2>
                                    </div>

                                    <?php endif; ?>
                                </form>

                            </div>
                        </div>
                        <?php if ($checkoutType == 'service') {
                            echo view('cart/_order_summary_service');
                        } else {
                            echo view('cart/_order_summary');
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>