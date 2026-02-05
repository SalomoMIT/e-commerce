<?php if (!empty($paymentGateway) && $paymentGateway->name_key == 'yoomoney'): ?>

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

        <form id="yoomoney-payment-form" method="POST" action="https://yoomoney.ru/quickpay/confirm.xml">
            <input type="hidden" name="receiver" value="<?= esc($paymentGateway->public_key); ?>">
            <input type="hidden" name="label" value="<?= esc($checkout->checkout_token); ?>">
            <input type="hidden" name="quickpay-form" value="shop">
            <input type="hidden" name="targets" value="<?= esc(getCheckoutPaymentTitle($checkout)); ?>">
            <input type="hidden" name="sum" value="<?= numToDecimal($checkout->grand_total); ?>" data-type="number">
            <input type="hidden" name="successURL" value="<?= base_url('checkout/complete-yoomoney-payment'); ?>?token=<?= esc($checkout->checkout_token); ?>">
            <input type="hidden" name="paymentType" value="PC">

            <button id="custom-yoomoney-button" class="btn btn-lg btn-payment" style="background-color: #7A33FF">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                    <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                </svg>&nbsp;&nbsp;&nbsp;<?= trans("confirm_and_pay"); ?>
            </button>
        </form>
    </div>
<?php endif; ?>