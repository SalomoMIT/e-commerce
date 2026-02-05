<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= trans("wallet"); ?></li>
                    </ol>
                </nav>
                <h1 class="page-title"><?= trans("wallet"); ?></h1>
                <?= view('partials/_messages'); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="sidebar-tabs-content">
                    <div class="row justify-content-center mb-5">
                        <div class="col-md-6 col-sm-12">
                            <div class="card card-wallet-balance">
                                <div class="card-body">
                                    <?php if ($paymentSettings->wallet_deposit == 1): ?>
                                        <button type="button" class="btn btn-sm btn-light btn-add-funds" data-toggle="modal" data-target="#modalAddFunds">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9.09a5.5 5.5 0 0 1-.09-1a6 6 0 0 1 6-6a5.9 5.9 0 0 1 3 .81V6a2 2 0 0 0-2-2m0 7H4V8h16m0 7v3h3v2h-3v3h-2v-3h-3v-2h3v-3Z"/>
                                            </svg>&nbsp;&nbsp;<?= trans("add_funds"); ?>
                                        </button>
                                    <?php endif; ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24">
                                        <g id="wallet_3_fill" fill="none" fill-rule="evenodd">
                                            <path d="M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427c-.002-.01-.009-.017-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093c.012.004.023 0 .029-.008l.004-.014-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014-.034.614c0 .012.007.02.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z"/>
                                            <path fill="<?= esc($generalSettings->site_color); ?>" d="M5 6.5a.5.5 0 0 1 .5-.5H16a1 1 0 1 0 0-2H5.5A2.5 2.5 0 0 0 3 6.5V18a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5.5a.5.5 0 0 1-.5-.5M15.5 15a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                        </g>
                                    </svg>
                                    <div class="font-600 font-size-13"><?= trans("wallet_balance"); ?></div>
                                    <strong class="total"><?= priceFormatted(user()->balance, $selectedCurrency->code, true); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-wallet" role="tablist">
                        <?php if (isVendor()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'earnings' ? 'active' : ''; ?>" href="<?= generateUrl("wallet"); ?>"><?= trans("earnings"); ?></a>
                            </li>
                        <?php endif;
                        if ($affiliateSettings->status == 1): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'referral_earnings' ? 'active' : ''; ?>" href="<?= generateUrl("wallet") . '?tab=referral-earnings'; ?>"><?= trans("referral_earnings"); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab == 'deposits' ? 'active' : ''; ?>" href="<?= generateUrl("wallet") . '?tab=deposits'; ?>"><?= trans("deposits"); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab == 'expenses' ? 'active' : ''; ?>" href="<?= generateUrl("wallet") . '?tab=expenses'; ?>"><?= trans("expenses"); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab == 'payouts' ? 'active' : ''; ?>" href="<?= generateUrl("wallet") . '?tab=payouts'; ?>"><?= trans("payouts"); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab == 'set_payout_account' ? 'active' : ''; ?>" href="<?= generateUrl("wallet") . '?tab=set-payout-account'; ?>"><?= trans("set_payout_account"); ?></a>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-12">
                            <?php if ($activeTab == 'referral_earnings') {
                                echo view("wallet/_referral_earnings");
                            } elseif ($activeTab == 'deposits') {
                                echo view("wallet/_deposits");
                            } elseif ($activeTab == 'expenses') {
                                echo view("wallet/_expenses");
                            } elseif ($activeTab == 'payouts') {
                                echo view("wallet/_payouts");
                            } elseif ($activeTab == 'set_payout_account') {
                                echo view("wallet/_set_payout_account");
                            } else {
                                echo view("wallet/_earnings");
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalAddFunds" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("add_funds"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true"><i class="icon-close"></i> </span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('Profile/addFundsPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                    <div class="form-group">
                        <label class="control-label"><?= trans("enter_amount"); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon"><?= $selectedCurrency->symbol; ?></span>
                            <input type="text" name="amount" id="product_discounted_price_input" class="form-control form-input input-price" maxlength="13" placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>"
                                   placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>" inputmode="decimal" required>
                        </div>
                        <?php if ($paymentSettings->wallet_min_deposit > 0): ?>
                            <small class="display-block font-size-13 m-t-5"><?= trans("minimum_deposit_amount"); ?>:&nbsp;<strong><?= priceFormatted($paymentSettings->wallet_min_deposit, $selectedCurrency->code, true); ?></strong></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-md btn-custom btn-block"><?= trans("continue_to_checkout"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>