<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('payment_settings'); ?></h3>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form action="<?= base_url('Admin/paymentGatewaySettingsPost'); ?>" method="post">
            <?= csrf_field(); ?>
            <input type="hidden" name="active_tab" id="input_active_tab" value="<?= clrNum($activeTab); ?>">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <?php if (!empty($paymentGateways)):
                        foreach ($paymentGateways as $gateway):?>
                            <li class="<?= $activeTab == $gateway->name_key ? ' active' : ''; ?>"><a href="<?= adminUrl('payment-settings'); ?>?gateway=<?= $gateway->name_key; ?>"><?= esc($gateway->name); ?></a></li>
                        <?php endforeach;
                    endif; ?>
                    <li class="<?= $activeTab == 'bank_transfer' ? ' active' : ''; ?>"><a href="<?= adminUrl('payment-settings'); ?>?gateway=bank_transfer"><?= trans("bank_transfer"); ?></a></li>
                    <li class="<?= $activeTab == 'cash_on_delivery' ? ' active' : ''; ?>"><a href="<?= adminUrl('payment-settings'); ?>?gateway=cash_on_delivery"><?= trans("cash_on_delivery"); ?></a></li>
                </ul>
                <form action="<?= base_url('Admin/paymentGatewaySettingsPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <div class="tab-content settings-tab-content">
                        <div class="tab-pane<?= $activeTab == 'paypal' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'paypal'):
                                $paypal = getPaymentGateway('paypal');
                                if (!empty($paypal)):?>
                                    <input type="hidden" name="name_key" value="paypal">
                                    <img src="<?= base_url('assets/img/payment/paypal.svg'); ?>" alt="paypal" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $paypal->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?= trans("mode"); ?></label>
                                        <?= formRadio('environment', 'production', 'sandbox', trans("production"), trans("sandbox"), $paypal->environment, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("client_id"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("client_id"); ?>" value="<?= esc($paypal->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($paypal->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $paypal->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'stripe' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'stripe'):
                                $stripe = getPaymentGateway('stripe');
                                if (!empty($stripe)):?>
                                    <input type="hidden" name="name_key" value="stripe">
                                    <img src="<?= base_url('assets/img/payment/stripe.svg'); ?>" alt="stripe" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $stripe->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("publishable_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("publishable_key"); ?>" value="<?= esc($stripe->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($stripe->secret_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("webhook_secret"); ?></label>
                                        <input type="text" class="form-control" name="webhook_secret" placeholder="<?= trans("webhook_secret"); ?>" value="<?= esc($stripe->webhook_secret); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $stripe->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'paystack' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'paystack'):
                                $paystack = getPaymentGateway('paystack');
                                if (!empty($paystack)):?>
                                    <input type="hidden" name="name_key" value="paystack">
                                    <img src="<?= base_url('assets/img/payment/paystack.svg'); ?>" alt="paystack" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $paystack->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("public_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("public_key"); ?>" value="<?= esc($paystack->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($paystack->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $paystack->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'razorpay' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'razorpay'):
                                $razorpay = getPaymentGateway('razorpay');
                                if (!empty($razorpay)):?>
                                    <input type="hidden" name="name_key" value="razorpay">
                                    <img src="<?= base_url('assets/img/payment/razorpay.svg'); ?>" alt="razorpay" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $razorpay->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("api_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("api_key"); ?>" value="<?= esc($razorpay->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($razorpay->secret_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("webhook_secret"); ?></label>
                                        <input type="text" class="form-control" name="webhook_secret" placeholder="<?= trans("webhook_secret"); ?>" value="<?= esc($razorpay->webhook_secret); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $razorpay->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'flutterwave' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'flutterwave'):
                                $flutterwave = getPaymentGateway('flutterwave');
                                if (!empty($flutterwave)):?>
                                    <input type="hidden" name="name_key" value="flutterwave">
                                    <img src="<?= base_url('assets/img/payment/flutterwave.svg'); ?>" alt="flutterwave" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $flutterwave->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("public_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("public_key"); ?>" value="<?= esc($flutterwave->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($flutterwave->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $flutterwave->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'iyzico' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'iyzico'):
                                $iyzico = getPaymentGateway('iyzico');
                                if (!empty($iyzico)):?>
                                    <input type="hidden" name="name_key" value="iyzico">
                                    <img src="<?= base_url('assets/img/payment/iyzico.svg'); ?>" alt="iyzico" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $iyzico->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?= trans("mode"); ?></label>
                                        <?= formRadio('environment', 'production', 'sandbox', trans("production"), trans("sandbox"), $iyzico->environment, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("api_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("api_key"); ?>" value="<?= esc($iyzico->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($iyzico->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $iyzico->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif; ?>
                                <div class="alert alert-info alert-large">
                                    <strong><?= trans("warning"); ?>!</strong>&nbsp;&nbsp;<?= trans("iyzico_warning"); ?> <a href="https://dev.iyzipay.com/en/checkout-form" target="_blank" style="color: #0c5460;font-weight: bold">Iyzico Checkout Form</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'midtrans' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'midtrans'):
                                $midtrans = getPaymentGateway('midtrans');
                                if (!empty($midtrans)):?>
                                    <input type="hidden" name="name_key" value="midtrans">
                                    <img src="<?= base_url('assets/img/payment/midtrans.svg'); ?>" alt="midtrans" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $midtrans->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?= trans("mode"); ?></label>
                                        <?= formRadio('environment', 'production', 'sandbox', trans("production"), trans("sandbox"), $midtrans->environment, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("api_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("api_key"); ?>" value="<?= esc($midtrans->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("server_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("server_key"); ?>" value="<?= esc($midtrans->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $midtrans->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'dlocalgo' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'dlocalgo'):
                                $dLocalGo = getPaymentGateway('dlocalgo');
                                if (!empty($dLocalGo)):?>
                                    <input type="hidden" name="name_key" value="dlocalgo">
                                    <img src="<?= base_url('assets/img/payment/d-local-go.svg'); ?>" alt="dlocalgo" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $dLocalGo->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?= trans("mode"); ?></label>
                                        <?= formRadio('environment', 'production', 'sandbox', trans("production"), trans("sandbox"), $dLocalGo->environment, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("api_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("api_key"); ?>" value="<?= esc($dLocalGo->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?> (Token)</label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($dLocalGo->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $dLocalGo->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'paytabs' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'paytabs'):
                                $payTabs = getPaymentGateway('paytabs');
                                if (!empty($payTabs)): ?>
                                    <input type="hidden" name="name_key" value="paytabs">
                                    <img src="<?= base_url('assets/img/payment/paytabs.svg'); ?>" alt="paytabs" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $payTabs->status, 'col-md-4'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("profile_id"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("profile_id"); ?>" value="<?= esc($payTabs->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("server_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($payTabs->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $payTabs->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'yoomoney' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'yoomoney'):
                                $yooMoney = getPaymentGateway('yoomoney');
                                if (!empty($yooMoney)):?>
                                    <input type="hidden" name="name_key" value="yoomoney">
                                    <img src="<?= base_url('assets/img/payment/yoomoney.svg'); ?>" alt="yoomoney" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $yooMoney->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label><?= trans("mode"); ?></label>
                                        <?= formRadio('environment', 'production', 'sandbox', trans("production"), trans("sandbox"), $yooMoney->environment, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("shop_id"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("shop_id"); ?>" value="<?= esc($yooMoney->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($yooMoney->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $yooMoney->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'mercado_pago' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'mercado_pago'):
                                $mercadoPago = getPaymentGateway('mercado_pago');
                                if (!empty($mercadoPago)):?>
                                    <input type="hidden" name="name_key" value="mercado_pago">
                                    <img src="<?= base_url('assets/img/payment/mercado_pago.svg'); ?>" alt="mercado_pago" class="img-payment-logo">
                                    <div class="form-group">
                                        <label><?= trans("status"); ?></label>
                                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $mercadoPago->status, 'col-lg-3'); ?>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("public_key"); ?></label>
                                        <input type="text" class="form-control" name="public_key" placeholder="<?= trans("public_key"); ?>" value="<?= esc($mercadoPago->public_key); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans("secret_key"); ?></label>
                                        <input type="text" class="form-control" name="secret_key" placeholder="<?= trans("secret_key"); ?>" value="<?= esc($mercadoPago->secret_key); ?>">
                                    </div>
                                    <div class="form-group max-400">
                                        <label><?= trans('transaction_fee'); ?>&nbsp;(%)</label>
                                        <input type="number" name="transaction_fee" class="form-control" min="0" max="100" step="0.01" value="<?= $mercadoPago->transaction_fee; ?>" placeholder="0.00">
                                        <small>* <?= trans("transaction_fee_exp"); ?></small>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'bank_transfer' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'bank_transfer'): ?>
                                <input type="hidden" name="name_key" value="bank_transfer">
                                <div class="form-group">
                                    <label><?= trans("status"); ?></label>
                                    <?= formRadio('bank_transfer_enabled', 1, 0, trans("enable"), trans("disable"), $paymentSettings->bank_transfer_enabled, 'col-lg-3'); ?>
                                </div>
                                <div class="form-group">
                                    <?= renderTextEditorAdmin('bank_transfer_accounts', trans("bank_accounts"), $paymentSettings->bank_transfer_accounts, false, false, 'tinyMCEsmall'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane<?= $activeTab == 'cash_on_delivery' ? ' active' : ''; ?>">
                            <?php if ($activeTab == 'cash_on_delivery'): ?>
                                <input type="hidden" name="name_key" value="cash_on_delivery">
                                <div class="form-group">
                                    <label><?= trans("status"); ?></label>
                                    <?= formRadio('cash_on_delivery_enabled', 1, 0, trans("enable"), trans("disable"), $paymentSettings->cash_on_delivery_enabled, 'col-lg-3'); ?>
                                </div>
                                <div class="form-group">
                                    <label class="control-label"><?= trans('commission_debt_limit'); ?></label>
                                    <div class="max-600">
                                        <?= renderPriceInput('cash_on_delivery_debt_limit', $paymentSettings->cash_on_delivery_debt_limit, ['required' => true]); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                    </div>
                </form>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('commission'); ?>&nbsp;&&nbsp;<?= trans('tax_settings'); ?></h3><br>
            </div>
            <form action="<?= base_url('Admin/commissionSettingsPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 m-b-10">
                                <label><?= trans('commission'); ?></label>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="commission" value="1" id="commission_1" class="custom-control-input radio-commission" <?= $paymentSettings->commission_rate > 0 ? 'checked' : ''; ?>>
                                    <label for="commission_1" class="custom-control-label"><?= trans("enable"); ?></label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="commission" value="0" id="commission_2" class="custom-control-input radio-commission" <?= $paymentSettings->commission_rate <= 0 ? 'checked' : ''; ?>>
                                    <label for="commission_2" class="custom-control-label"><?= trans("disable"); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="commissionRateContainer" class="form-group" <?= $paymentSettings->commission_rate <= 0 ? 'style="display:none;"' : ''; ?>>
                        <label><?= trans('commission_rate'); ?>(%)</label>
                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01" value="<?= $paymentSettings->commission_rate; ?>">
                    </div>
                    <div class="form-group">
                        <div class="m-b-10">
                            <label><?= trans('vat'); ?>&nbsp;(<?= trans("vat_exp"); ?>)</label><br>
                            <small style="font-size: 13px;"><?= trans("vat_vendor_exp"); ?></small>
                        </div>
                        <?= formRadio('vat_status', 1, 0, trans("enable"), trans("disable"), $paymentSettings->vat_status); ?>
                    </div>

                    <div class="form-group">
                        <div class="m-b-10">
                            <label><?= trans('cart_location_selection'); ?></label><br>
                            <small style="font-size: 13px;"><?= trans("cart_location_selection_exp"); ?></small>
                        </div>
                        <?= formRadio('cart_location_selection', 1, 0, trans("enable"), trans("disable"), $paymentSettings->cart_location_selection); ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('featured_product_fees'); ?></h3>
            </div>
            <form action="<?= base_url('Product/featuredProductsPricingPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('price_per_day'); ?></label>
                        <?= renderPriceInput('price_per_day', $paymentSettings->price_per_day, ['required' => true]); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('price_per_month'); ?></label>
                        <?= renderPriceInput('price_per_month', $paymentSettings->price_per_month, ['required' => true]); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("free_promotion"); ?></label>
                        <?= formRadio('free_product_promotion', 1, 0, trans("enable"), trans("disable"), $paymentSettings->free_product_promotion); ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title">
                        <?= trans('global_taxes'); ?><br>
                        <small><?= trans("global_taxes_exp"); ?></small>
                    </h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('add-tax'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans('add_tax'); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group" style="max-height: 500px; overflow-y: scroll;">
                    <table class="table">
                        <tr>
                            <th><?= trans('id'); ?></th>
                            <th><?= trans('tax_name'); ?></th>
                            <th><?= trans('tax_rate'); ?></th>
                            <th><?= trans('status'); ?></th>
                            <th><?= trans('options'); ?></th>
                        </tr>
                        <?php if (!empty($taxes)):
                            foreach ($taxes as $tax):
                                $nameArray = !empty($tax->name_data) ? unserializeData($tax->name_data) : null; ?>
                                <tr>
                                    <td style="width: 50px;"><?= esc($tax->id); ?></td>
                                    <td><?= esc(getTaxName($nameArray, selectedLangId())); ?></td>
                                    <td><?= esc($tax->tax_rate); ?>%</td>
                                    <td>
                                        <?php if ($tax->status == 1): ?>
                                            <label class="label label-success"><?= trans("active"); ?></label>
                                        <?php else: ?>
                                            <label class="label label-danger"><?= trans("inactive"); ?></label>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 100px;">
                                        <div class="btn-group btn-group-option">
                                            <a href="<?= adminUrl('edit-tax/' . $tax->id); ?>" class="btn btn-sm btn-default btn-edit"><i class="fa fa-edit"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-default btn-delete" onclick="deleteItem('Admin/deleteTaxPost','<?= $tax->id; ?>','<?= trans("confirm_delete"); ?>');"><i class="fa fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                    </table>
                    <?php if (empty($taxes)): ?>
                        <p class="text-center m-t-30"><?= trans("no_records_found"); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('invoice'); ?></h3><br>
            </div>
            <form action="<?= base_url('Admin/additionalInvoiceInfoPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group" style="max-height: 300px; overflow-y: scroll">
                        <div class="m-b-15">
                            <label class="m-0"><?= trans('additional_invoice_information'); ?></label>
                            <br><small><?= trans("additional_invoice_information_exp"); ?></small>
                        </div>
                        <?php foreach ($activeLanguages as $language):
                            $infoInvoice = getAdditionalInvoiceInfo($language->id); ?>
                            <textarea name="info_<?= $language->id; ?>" class="form-control form-textarea m-b-15" placeholder="<?= esc($language->name); ?>"><?= !empty($infoInvoice) ? esc(str_replace("<br>", "\n", $infoInvoice)) : ''; ?></textarea>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
    .tab-pane {
        position: relative;
        padding-top: 30px;
    }

    .nav-tabs li a {
        font-weight: 600;
        padding: 12px 24px !important;
    }

    .nav-tabs li a:hover {
        color: #111 !important;
    }

    .img-payment-logo {
        height: 40px;
        max-height: 40px;
        position: absolute;
        right: 15px;
        top: 15px;
    }
</style>

<script>
    $(document).on("change", ".radio-commission", function () {
        var val = $('input[name="commission"]:checked').val();
        if (val == '1') {
            $('#commissionRateContainer').show();
        } else {
            $('#commissionRateContainer').hide();
        }
    });
</script>