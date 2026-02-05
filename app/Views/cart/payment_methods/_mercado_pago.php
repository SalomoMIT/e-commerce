<div class="row">
    <div class="col-12">
        <?= view('partials/_messages'); ?>
    </div>
</div>

<?php if (!empty($preferenceId)): ?>
    <div id="payment-button-container" class="payment-button-cnt">
        <div class="payment-icons-container">
            <label class="payment-icons">
                <?php $logos = @explode(',', $paymentGateway->logos ?? '');
                if (!empty($logos) && is_array($logos)):
                    foreach ($logos as $logo): ?>
                        <img src="<?= base_url('assets/img/payment/' . esc(trim($logo)) . '.svg'); ?>" alt="<?= esc(trim($logo)); ?>">
                    <?php endforeach;
                endif; ?>
            </label>
        </div>

        <p class="p-complete-payment"><?= trans("msg_complete_payment"); ?></p>

        <div id="wallet_container"></div>

        <script>
            const configMercadoPago = {
                publicKey: '<?= esc($paymentGateway->public_key); ?>',
                preferenceId: '<?= $preferenceId; ?>',
                locale: '<?= esc($activeLang->language_code); ?>',
                containerId: 'wallet_container'
            };
        </script>
    </div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <?php if (!empty($errorMessage)): ?>
            <?= esc($errorMessage); ?>
        <?php else: ?>
            <?= trans("payment_option_load_error"); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>