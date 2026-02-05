<?php if (!empty($payout) && !empty($user)):
    $userPayout = getUserPayoutInfo($user); ?>
    <div id="accountDetailsModel_<?= $payout->id; ?>" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?= trans($payout->payout_method); ?></h4>
                </div>
                <div class="modal-body">
                    <?php if ($payout->payout_method == 'paypal'): ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("user"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc(getUsername($user)); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("paypal_email_address"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->paypal_email); ?></strong>
                            </div>
                        </div>
                    <?php elseif ($payout->payout_method == 'bitcoin'): ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("user"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc(getUsername($user)); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("btc_address"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->btc_address); ?></strong>
                            </div>
                        </div>
                    <?php elseif ($payout->payout_method == 'iban'): ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("user"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc(getUsername($user)); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("full_name"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->iban_full_name); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("country"); ?>
                            </div>
                            <div class="col-sm-8">
                                <?php $country = getCountry($userPayout->iban_country_id);
                                if (!empty($country)):?>
                                    <strong>&nbsp;<?= esc($country->name); ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("bank_name"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->iban_bank_name); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("iban"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->iban_number); ?></strong>
                            </div>
                        </div>
                    <?php elseif ($payout->payout_method == 'swift'): ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("user"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc(getUsername($user)); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("full_name"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_full_name); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("address"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_address); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("state"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_state); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("city"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_city); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("postcode"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_postcode); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("country"); ?>
                            </div>
                            <div class="col-sm-8">
                                <?php $branchCountry = getCountry($userPayout->swift_country_id);
                                if (!empty($branchCountry)):?>
                                    <strong>&nbsp;<?= esc($branchCountry->name); ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("bank_account_holder_name"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_bank_account_holder_name); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("iban"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_iban); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("swift_code"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_code); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("bank_name"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_bank_name); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("bank_branch_city"); ?>
                            </div>
                            <div class="col-sm-8">
                                <strong>&nbsp;<?= esc($userPayout->swift_bank_branch_city); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <?= trans("bank_branch_country"); ?>
                            </div>
                            <div class="col-sm-8">
                                <?php $branchCountry = getCountry($userPayout->swift_bank_branch_country_id);
                                if (!empty($branchCountry)):?>
                                    <strong>&nbsp;<?= esc($branchCountry->name); ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= trans("close"); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>