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
                                <?php if (!empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkout->checkout_type == 'product'): ?>
                                    <div class="tab-checkout tab-checkout-closed">
                                        <a href="<?= generateUrl('cart', 'shipping'); ?>"><h2 class=" title">1.&nbsp;&nbsp;<?= trans("shipping_information"); ?></h2></a>
                                        <a href="<?= generateUrl('cart', 'shipping'); ?>" class="link-underlined"><?= trans("edit"); ?></a>
                                    </div>
                                <?php endif; ?>
                                <div class="tab-checkout tab-checkout-closed">
                                    <?php if ($checkout->checkout_type == 'service'): ?>
                                        <a href="<?= generateUrl('cart', 'payment_method'); ?>">
                                            <h2 class="title">
                                                <?= !empty($cart->has_physical_product) && $checkout->checkout_type == 'product' ? '2. ' : '1. '; ?>
                                                &nbsp;<?= trans("payment_method"); ?>
                                            </h2>
                                        </a>
                                        <a href="<?= generateUrl('cart', 'payment_method'); ?>" class="link-underlined"><?= trans("edit"); ?></a>
                                    <?php else: ?>
                                        <a href="<?= generateUrl('cart', 'payment_method'); ?>">
                                            <h2 class=" title">
                                                <?= !empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkout->checkout_type == 'product' ? '2. ' : '1. '; ?>
                                                &nbsp;<?= trans("payment_method"); ?>
                                            </h2>
                                        </a>
                                        <a href="<?= generateUrl('cart', 'payment_method'); ?>" class="link-underlined"><?= trans("edit"); ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-checkout tab-checkout-open">
                                    <h2 class="title">
                                        <?= !empty($cart->has_physical_product) && $productSettings->marketplace_shipping == 1 && $checkout->checkout_type == 'product' ? '3. ' : '2. '; ?>
                                        <?= trans("payment"); ?>
                                    </h2>
                                    <div class="row">
                                        <div class="col-12">

                                            <div class="row">
                                                <div class="col-12">
                                                    <?= view('partials/_messages'); ?>
                                                </div>
                                            </div>

                                            <?php if ($checkout->payment_method == 'bank_transfer') {
                                                echo view('cart/payment_methods/_bank_transfer');
                                            } elseif (authCheck() && $checkout->payment_method == 'cash_on_delivery') {
                                                echo view('cart/payment_methods/_cash_on_delivery');
                                            } else {
                                                if (!empty($initPaymentGateway)) {
                                                    echo view('cart/payment_methods/_' . $checkout->payment_method);
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= $checkout->checkout_type == 'service' ? view('cart/_order_summary_service') : view('cart/_order_summary'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        var paymentContainer = document.getElementById('payment-button-container');
        if (paymentContainer) {
            paymentContainer.style.visibility = 'visible';
        }
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.btn-place-order').forEach(function (button) {
                button.disabled = true;
            });
        });
    });
</script>