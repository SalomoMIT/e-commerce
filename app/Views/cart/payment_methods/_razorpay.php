<?php if (!empty($paymentGateway) && $paymentGateway->name_key == 'razorpay' && !empty($razorpayOrderId)): ?>
    <div id="payment-button-container" class="payment-button-cnt">

        <div class="payment-icons-container">
            <label class="payment-icons">
                <?php $logos = @explode(',', $paymentGateway->logos);
                if (!empty($logos) && countItems($logos) > 0):
                    foreach ($logos as $logo): ?>
                        <img src="<?= base_url('assets/img/payment/' . esc(trim($logo ?? '')) . '.svg'); ?>" alt="<?= esc(trim($logo ?? '')); ?>">
                    <?php endforeach;
                endif; ?>
            </label>
        </div>

        <p class="p-complete-payment"><?= trans("msg_complete_payment"); ?></p>

        <button type="button" id="rzp-button1" class="btn btn-lg btn-payment" style="background-color: #528FF0;border-color: #528FF0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
            </svg>&nbsp;&nbsp;&nbsp;<?= trans("confirm_and_pay"); ?>
        </button>

        <div class="payment-loader d-none" style="margin-top: 10px;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <script>
        const configRazorpay = {
            key: "<?= escJs($paymentGateway->public_key); ?>",
            amount: "<?= escJs($totalAmountInSubunits); ?>",
            currency: "<?= escJs($currencyCode); ?>",
            name: "<?= escJs($generalSettings->application_name); ?>",
            description: "<?= escJs(trans("pay")); ?>",
            image: "<?= escJs(getLogoEmail()); ?>",
            orderId: "<?= escJs($razorpayOrderId); ?>",
            paymentPostUrl: "<?= generateUrl('checkout/complete-razorpay-payment'); ?>",
            checkoutToken: "<?= escJs($checkout->checkout_token); ?>"
        };
    </script>
<?php endif; ?>