<?php if ($checkout->payment_method == 'cash_on_delivery'): ?>
    <form action="<?= base_url('checkout/complete-cash-on-delivery-payment'); ?>" method="post">
        <?= csrf_field(); ?>
        <input type="hidden" name="checkout_token" value="<?= esc($checkout->checkout_token); ?>">

        <div id="payment-button-container" class="payment-button-cnt">
            <h2 class="title-complete-payment"><?= trans("cash_on_delivery"); ?></h2>

            <p class="p-payment-method-description m-b-30">
                <?= trans("cash_on_delivery_warning"); ?>
            </p>
            <button type="submit" name="submit" value="update" class="btn btn-lg btn-custom btn-payment btn-place-order">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">
                    <path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"/>
                </svg>&nbsp;&nbsp;<?= trans("place_order") ?>
            </button>
        </div>
    </form>
<?php endif; ?>