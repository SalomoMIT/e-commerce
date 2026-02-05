<div class="wallet-container clearfix">
    <ul class="nav nav-tabs nav-payout-accounts">
        <?php $payoutOptions = getActivePayoutOptions();
        if (!empty($payoutOptions)):
            foreach ($payoutOptions as $option):?>
                <li><a class="btn btn-md <?= $payoutTab == $option ? 'active' : ''; ?>" data-toggle="pill" href="#tab_<?= $option; ?>"><?= trans($option); ?></a></li>
            <?php endforeach;
        endif; ?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane <?= $payoutTab == 'paypal' ? 'active' : 'fade'; ?>" id="tab_paypal">
            <form action="<?= base_url('wallet/set-payout-account-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="back_url" value="<?= esc(getCurrentUrl()); ?>">
                <div class="form-group">
                    <label class="control-label"><?= trans("paypal_email_address"); ?>*</label>
                    <input type="email" name="paypal_email" class="form-control form-input" value="<?= esc($userPayout->paypal_email); ?>" required>
                </div>
                <div class="form-group text-right">
                    <button type="submit" name="submit" value="paypal" class="btn btn-md btn-custom"><?= trans("save_changes"); ?></button>
                </div>
            </form>
        </div>
        <div class="tab-pane <?= $payoutTab == 'bitcoin' ? 'active' : 'fade'; ?>" id="tab_bitcoin">
            <form action="<?= base_url('wallet/set-payout-account-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="form-group">
                    <label class="control-label"><?= trans("btc_address"); ?>*</label>
                    <input type="text" name="btc_address" class="form-control form-input" value="<?= esc($userPayout->btc_address); ?>" required>
                </div>
                <div class="form-group text-right">
                    <button type="submit" name="submit" value="bitcoin" class="btn btn-md btn-custom"><?= trans("save_changes"); ?></button>
                </div>
            </form>
        </div>
        <div class="tab-pane <?= $payoutTab == 'iban' ? 'active' : 'fade'; ?>" id="tab_iban">
            <form action="<?= base_url('wallet/set-payout-account-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="form-group">
                    <label class="control-label"><?= trans("full_name"); ?>*</label>
                    <input type="text" name="iban_full_name" class="form-control form-input" value="<?= esc($userPayout->iban_full_name); ?>" required>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 m-b-sm-15">
                            <label class="control-label"><?= trans("country"); ?>*</label>
                            <select name="iban_country_id" class="select2 form-control" required>
                                <option value="" selected><?= trans("select_country"); ?></option>
                                <?php foreach ($activeCountries as $item): ?>
                                    <option value="<?= $item->id; ?>" <?= $userPayout->iban_country_id == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="control-label"><?= trans("bank_name"); ?>*</label>
                            <input type="text" name="iban_bank_name" class="form-control form-input" value="<?= esc($userPayout->iban_bank_name); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("iban_long"); ?>(<?= trans("iban"); ?>)*</label>
                    <input type="text" name="iban_number" class="form-control form-input" value="<?= esc($userPayout->iban_number); ?>" required>
                </div>
                <div class="form-group text-right">
                    <button type="submit" name="submit" value="iban" class="btn btn-md btn-custom"><?= trans("save_changes"); ?></button>
                </div>
            </form>
        </div>
        <div class="tab-pane <?= $payoutTab == 'swift' ? 'active' : 'fade'; ?>" id="tab_swift">
            <form action="<?= base_url('wallet/set-payout-account-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="form-group">
                    <label class="control-label"><?= trans("full_name"); ?>*</label>
                    <input type="text" name="swift_full_name" class="form-control form-input" value="<?= esc($userPayout->swift_full_name); ?>" required>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 m-b-sm-15">
                            <label class="control-label"><?= trans("country"); ?>*</label>
                            <select name="swift_country_id" class="select2 form-control" required>
                                <option value="" selected><?= trans("select_country"); ?></option>
                                <?php foreach ($activeCountries as $item): ?>
                                    <option value="<?= $item->id; ?>" <?= $userPayout->swift_country_id == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="control-label"><?= trans("state"); ?>*</label>
                            <input type="text" name="swift_state" class="form-control form-input" value="<?= esc($userPayout->swift_state); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 m-b-sm-15">
                            <label class="control-label"><?= trans("city"); ?>*</label>
                            <input type="text" name="swift_city" class="form-control form-input" value="<?= esc($userPayout->swift_city); ?>" required>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="control-label"><?= trans("postcode"); ?>*</label>
                            <input type="text" name="swift_postcode" class="form-control form-input" value="<?= esc($userPayout->swift_postcode); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("address"); ?>*</label>
                    <input type="text" name="swift_address" class="form-control form-input" value="<?= esc($userPayout->swift_address); ?>" required>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 m-b-sm-15">
                            <label class="control-label"><?= trans("bank_account_holder_name"); ?>*</label>
                            <input type="text" name="swift_bank_account_holder_name" class="form-control form-input" value="<?= esc($userPayout->swift_bank_account_holder_name); ?>" required>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="control-label"><?= trans("bank_name"); ?>*</label>
                            <input type="text" name="swift_bank_name" class="form-control form-input" value="<?= esc($userPayout->swift_bank_name); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 m-b-sm-15">
                            <label class="control-label"><?= trans("bank_branch_country"); ?>*</label>
                            <select name="swift_bank_branch_country_id" class="select2 form-control" required>
                                <option value="" selected><?= trans("select_country"); ?></option>
                                <?php foreach ($activeCountries as $item): ?>
                                    <option value="<?= $item->id; ?>" <?= $userPayout->swift_bank_branch_country_id == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label class="control-label"><?= trans("bank_branch_city"); ?>*</label>
                            <input type="text" name="swift_bank_branch_city" class="form-control form-input" value="<?= esc($userPayout->swift_bank_branch_city); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("swift_iban"); ?>*</label>
                    <input type="text" name="swift_iban" class="form-control form-input" value="<?= esc($userPayout->swift_iban); ?>" required>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("swift_code"); ?>*</label>
                    <input type="text" name="swift_code" class="form-control form-input" value="<?= esc($userPayout->swift_code); ?>" required>
                </div>
                <div class="form-group text-right">
                    <button type="submit" name="submit" value="swift" class="btn btn-md btn-custom"><?= trans("save_changes"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>