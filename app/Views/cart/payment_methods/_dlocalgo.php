<?php if (!empty($paymentGateway) && $paymentGateway->name_key == 'dlocalgo' && !empty($dlocalRedirectUrl)): ?>
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

        <a href="<?= $dlocalRedirectUrl; ?>" class="btn btn-lg btn-payment" style="background-color: #635BFF; border-color: #635BFF">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
            </svg>&nbsp;&nbsp;&nbsp;<?= trans("confirm_and_pay"); ?>
        </a>
    </div>
<?php endif; ?>