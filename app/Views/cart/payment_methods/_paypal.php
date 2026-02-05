<?php if (!empty($paymentGateway) && $paymentGateway->name_key == 'paypal'): ?>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12">
                <div id="page-loader" class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only"></span>
                    </div>
                    <p class="mt-3 text-muted"><?= trans("loading_payment_options"); ?></p>
                </div>

                <div id="payment-container" class="payment-button-cnt d-none">
                    <h2 class="title-complete-payment"><?= trans("confirm_and_pay"); ?></h2>
                    <div id="paypal-button-container" class="mx-auto" style="max-width: 500px;"></div>

                    <div class="paypal-loader d-none text-center mt-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only"></span>
                        </div>
                        <br>
                        <strong class="payment-loader-text d-block mt-2"><?= trans("processing"); ?></strong>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const configPaypal = {
            totalAmount: '<?= numToDecimal($checkout->grand_total); ?>',
            currencyCode: '<?= esc($checkout->currency_code); ?>',
            checkoutToken: '<?= esc($checkout->checkout_token); ?>',
            paymentPostUrl: '<?= generateUrl('checkout/complete-paypal-payment') ?>'
        }
    </script>
<?php endif; ?>