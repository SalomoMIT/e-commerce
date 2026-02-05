<div class="wallet-container">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-md btn-custom m-b-15" data-toggle="modal" data-target="#modalPayoutRequest">
            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" fill="#fff">
                <path d="M1600 736v192q0 40-28 68t-68 28h-416v416q0 40-28 68t-68 28h-192q-40 0-68-28t-28-68v-416h-416q-40 0-68-28t-28-68v-192q0-40 28-68t68-28h416v-416q0-40 28-68t68-28h192q40 0 68 28t28 68v416h416q40 0 68 28t28 68z"/>
            </svg>&nbsp;<?= trans("new_payout_request"); ?>
        </button>
    </div>
    <div class="table-responsive table-custom">
        <table class="table" role="grid">
            <thead>
            <tr role="row">
                <th scope="col"><?= trans("withdraw_method"); ?></th>
                <th scope="col"><?= trans("withdraw_amount"); ?></th>
                <th scope="col"><?= trans("status"); ?></th>
                <th scope="col"><?= trans("date"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($payouts)):
                foreach ($payouts as $payout): ?>
                    <tr>
                        <td><?= trans($payout->payout_method); ?></td>
                        <td><strong class="text-danger font-600"><?= priceFormatted($payout->amount, $payout->currency); ?>&nbsp;(<?= $payout->currency; ?>)</strong></td>
                        <td>
                            <?php if ($payout->status == 1): ?>
                                <span class="badge badge-success-light"><?= trans("completed"); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary-light"><?= trans("pending"); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="no-wrap"><?= formatDate($payout->created_at); ?></td>
                    </tr>
                <?php endforeach;
            endif; ?>
            </tbody>
        </table>
        <?php if (empty($payouts)): ?>
            <p class="text-center m-t-15">
                <?= trans("no_records_found"); ?>
            </p>
        <?php endif; ?>
    </div>
    <div class="d-flex justify-content-center m-t-15">
        <?= $pager->links; ?>
    </div>
</div>

<div class="modal fade" id="modalPayoutRequest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content modal-custom modal-refund">
            <form action="<?= base_url('wallet/new-payout-request-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="back_url" value="<?= esc(getCurrentUrl()); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= trans("new_payout_request"); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"><i class="icon-close"></i> </span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="new-payout-request">
                                <div class="form-group">
                                    <label class="control-label"><?= trans("withdraw_amount"); ?></label>
                                    <?php $minValue = 0;
                                    if ($paymentSettings->payout_paypal_enabled) {
                                        $minValue = $paymentSettings->min_payout_paypal;
                                    } elseif ($paymentSettings->payout_bitcoin_enabled) {
                                        $minValue = $paymentSettings->min_payout_bitcoin;
                                    } elseif ($paymentSettings->payout_iban_enabled) {
                                        $minValue = $paymentSettings->min_payout_iban;
                                    } elseif ($paymentSettings->payout_swift_enabled) {
                                        $minValue = $paymentSettings->min_payout_swift;
                                    } ?>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><?= esc($defaultCurrency->symbol); ?></div>
                                            <input type="hidden" name="currency" value="<?= esc($defaultCurrency->code); ?>">
                                        </div>
                                        <input type="text" name="amount" class="form-control form-input input-price" maxlength="13" placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>"
                                               placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>" inputmode="decimal" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label"><?= trans("withdraw_method"); ?></label>
                                    <select name="payout_method" class="form-control custom-select" onchange="update_payout_input(this.value);" required>
                                        <?php $payoutOptions = getActivePayoutOptions();
                                        if (!empty($payoutOptions)):
                                            foreach ($payoutOptions as $option):?>
                                                <option value="<?= $option; ?>"><?= trans($option); ?></option>
                                            <?php endforeach;
                                        endif; ?>
                                    </select>
                                </div>
                                <div class="form-group text-right m-b-0">
                                    <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="minimum-payout-container">
                                <div class="font-700 m-b-15"><?= trans("min_poyout_amounts"); ?></div>
                                <?php if ($paymentSettings->payout_paypal_enabled): ?>
                                    <span><?= trans("paypal"); ?></span>:&nbsp;<strong><?= priceFormatted($paymentSettings->min_payout_paypal, $paymentSettings->default_currency) ?></strong>&nbsp;&nbsp;
                                <?php endif;
                                if ($paymentSettings->payout_bitcoin_enabled): ?>
                                    <span><?= trans("bitcoin"); ?></span>:&nbsp;<strong><?= priceFormatted($paymentSettings->min_payout_bitcoin, $paymentSettings->default_currency) ?></strong>&nbsp;&nbsp;
                                <?php endif;
                                if ($paymentSettings->payout_iban_enabled): ?>
                                    <span><?= trans("iban"); ?></span>:&nbsp;<strong><?= priceFormatted($paymentSettings->min_payout_iban, $paymentSettings->default_currency) ?></strong>&nbsp;&nbsp;
                                <?php endif;
                                if ($paymentSettings->payout_swift_enabled): ?>
                                    <span><?= trans("swift"); ?></span>:&nbsp;<strong><?= priceFormatted($paymentSettings->min_payout_swift, $paymentSettings->default_currency) ?></strong>
                                <?php endif; ?>
                                <hr>
                                <strong><?= trans("your_balance"); ?>:&nbsp;<span class="balance text-success"><?= priceFormatted(user()->balance, $paymentSettings->default_currency) ?></span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>