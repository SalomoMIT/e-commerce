<?php if (!empty($paymentGateway) && $paymentGateway->name_key == 'flutterwave'): ?>

    <?php $customer = getCartCustomerData($checkout);
    $ipAddress = getIPAddress();
    $consumerMac = !empty($ipAddress) ? $ipAddress : uniqid();
    $consumerId = authCheck() ? user()->id : 0; ?>

    <div id="payment-button-container" class="payment-button-cnt">

        <div class="payment-icons-container">
            <label class="payment-icons">
                <?php $logos = @explode(',', $paymentGateway->logos);
                if (!empty($logos) && is_array($logos)):
                    foreach ($logos as $logo): ?>
                        <img src="<?= base_url('assets/img/payment/' . esc(trim($logo ?? '')) . '.svg'); ?>" alt="<?= esc(trim($logo ?? '')); ?>">
                    <?php endforeach;
                endif; ?>
            </label>
        </div>

        <p class="p-complete-payment text-muted"><?= trans("msg_complete_payment"); ?></p>

        <button type="button" id="btn-flutterwave" class="btn btn-lg btn-payment" style="background-color: #f5a623;border-color: #f5a623;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
            </svg>&nbsp;&nbsp;&nbsp;<?= trans("confirm_and_pay"); ?>
        </button>
    </div>

    <script>
        const configFlutterwave = {
            publicKey: "<?= escJs($paymentGateway->public_key); ?>",
            tx_ref: "<?= escJs($checkout->checkout_token); ?>",
            amount: <?= $totalAmount; ?>,
            currency: "<?= escJs($currencyCode); ?>",
            redirectUrl: "<?= base_url('checkout/complete-flutterwave-payment'); ?>",
            meta: {
                consumer_id: <?= escJs($consumerId); ?>,
                consumer_mac: "<?= escJs($consumerMac); ?>",
            },
            customer: {
                email: "<?= !empty($customer) ? escJs($customer->email) : ''; ?>",
                phone_number: "<?= !empty($customer) ? escJs($customer->phone_number) : ''; ?>",
                name: "<?= !empty($customer) ? escJs($customer->first_name . ' ' . $customer->last_name) : ''; ?>"
            },
            customizations: {
                title: "<?= escJs($generalSettings->application_name); ?>",
                description: "<?= escJs(trans('cart_payment')); ?>",
                logo: "<?= escJs(getLogo()); ?>",
            }
        };
    </script>

<?php endif; ?>