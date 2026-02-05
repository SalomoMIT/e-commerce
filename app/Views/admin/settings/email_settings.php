<div class="row">
    <div class="col-sm-12 col-lg-6">
        <form action="<?= base_url('Admin/emailSettingsPost'); ?>" method="post">
            <?= csrf_field(); ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans('email_settings'); ?></h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('mail_service'); ?></label>
                        <select name="mail_service" class="form-control" onchange="window.location.href = '<?= adminUrl('email-settings'); ?>?service='+this.value+'&protocol=<?= esc($protocol); ?>';">
                            <?php foreach ($emailServices as $key => $label): ?>
                                <option value="<?= esc($key); ?>" <?= $service == $key ? 'selected' : ''; ?>>
                                    <?= esc($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($service == 'brevo'): ?>
                        <div class="form-group">
                            <label class="control-label"><?= trans('api_key'); ?></label>
                            <input type="password" class="form-control" name="brevo_api_key" placeholder="<?= trans('api_key'); ?>" value="<?= esc($emailSettings->brevo_api_key); ?>">
                        </div>
                    <?php elseif ($service == 'mailgun'): ?>
                        <div class="form-group">
                            <label class="control-label"><?= trans('api_key'); ?></label>
                            <input type="password" class="form-control" name="mailgun_api_key" placeholder="<?= trans('api_key'); ?>" value="<?= esc($emailSettings->mailgun_api_key); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('region'); ?></label>
                            <select name="mailgun_region" class="form-control">
                                <option value="us" <?= $emailSettings->mailgun_region == 'us' ? "selected" : ""; ?>>US</option>
                                <option value="eu" <?= $emailSettings->mailgun_region == 'eu' ? "selected" : ""; ?>>EU</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('domain'); ?></label>
                            <input type="text" class="form-control" name="mailgun_domain" placeholder="e.g. mg.example.com" value="<?= esc($emailSettings->mailgun_domain); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('sender_email_address'); ?></label>
                            <input type="text" class="form-control" name="mailgun_sender_email" placeholder="e.g. info@mg.example.com" value="<?= esc($emailSettings->mailgun_sender_email); ?>">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="control-label"><?= trans('mail_protocol'); ?></label>
                            <select name="mail_protocol" class="form-control" onchange="window.location.href = '<?= adminUrl('email-settings'); ?>?service=<?= esc($service); ?>&protocol='+this.value;">
                                <option value="smtp" <?= $protocol == 'smtp' ? "selected" : ""; ?>><?= trans('smtp'); ?></option>
                                <option value="mail" <?= $protocol == 'mail' ? "selected" : ""; ?>><?= trans('mail'); ?></option>
                            </select>
                        </div>
                        <?php if ($protocol == 'smtp'): ?>
                            <div class="form-group">
                                <label class="control-label"><?= trans('encryption'); ?></label>
                                <select name="mail_encryption" class="form-control">
                                    <option value="tls" <?= $emailSettings->mail_encryption == "tls" ? "selected" : ""; ?>>TLS</option>
                                    <option value="ssl" <?= $emailSettings->mail_encryption == "ssl" ? "selected" : ""; ?>>SSL</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans('mail_host'); ?></label>
                                <input type="text" class="form-control" name="mail_host" placeholder="<?= trans('mail_host'); ?>" value="<?= esc($emailSettings->mail_host); ?>">
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans('mail_port'); ?></label>
                                <input type="text" class="form-control" name="mail_port" placeholder="<?= trans('mail_port'); ?>" value="<?= esc($emailSettings->mail_port); ?>">
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans('mail_username'); ?></label>
                                <input type="text" class="form-control" name="mail_username" placeholder="<?= trans('mail_username'); ?>" value="<?= esc($emailSettings->mail_username); ?>">
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans('mail_password'); ?></label>
                                <input type="password" class="form-control" name="mail_password" placeholder="<?= trans('mail_password'); ?>" value="<?= esc($emailSettings->mail_password); ?>">
                            </div>
                        <?php endif;
                    endif; ?>
                    <div class="form-group">
                        <label class="control-label"><?= trans('mail_title'); ?></label>
                        <input type="text" class="form-control" name="mail_title" placeholder="<?= trans('mail_title'); ?>" value="<?= esc($emailSettings->mail_title); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('reply_to'); ?></label>
                        <input type="email" class="form-control" name="mail_reply_to" placeholder="<?= trans('reply_to'); ?>" value="<?= esc($emailSettings->mail_reply_to); ?>">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="email" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </div>
        </form>

        <form action="<?= base_url('Admin/sendTestEmailPost'); ?>" method="post">
            <?= csrf_field(); ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans('send_test_email'); ?></h3><br>
                    <small class="small-title"><?= trans('send_test_email_exp'); ?></small>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('email_address'); ?></label>
                        <input type="text" class="form-control" name="email" placeholder="<?= trans('email_address'); ?>" required>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="contact" class="btn btn-primary pull-right"><?= trans('send_email'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-sm-12 col-lg-6">
        <form action="<?= base_url('Admin/emailOptionsPost'); ?>" method="post">
            <?= csrf_field(); ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans('email_options'); ?></h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label><?= trans("email_verification"); ?></label>
                        <?= formRadio('email_verification', 1, 0, trans("enable"), trans("disable"), $generalSettings->email_verification); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("email_option_product_added"); ?></label>
                        <?= formRadio('new_product', 1, 0, trans("yes"), trans("no"), getEmailOptionStatus($generalSettings, 'new_product')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("email_option_send_order_to_buyer"); ?></label>
                        <?= formRadio('new_order', 1, 0, trans("yes"), trans("no"), getEmailOptionStatus($generalSettings, 'new_order')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("email_option_send_email_order_shipped"); ?></label>
                        <?= formRadio('order_shipped', 1, 0, trans("yes"), trans("no"), getEmailOptionStatus($generalSettings, 'order_shipped')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("email_option_contact_messages"); ?></label>
                        <?= formRadio('contact_messages', 1, 0, trans("yes"), trans("no"), getEmailOptionStatus($generalSettings, 'contact_messages')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("shop_opening_request_emails"); ?></label>
                        <?= formRadio('shop_opening_request', 1, 0, trans("yes"), trans("no"), getEmailOptionStatus($generalSettings, 'shop_opening_request')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("bidding_system_emails"); ?></label>
                        <?= formRadio('bidding_system', 1, 0, trans("enable"), trans("disable"), getEmailOptionStatus($generalSettings, 'bidding_system')); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("support_system_emails"); ?></label>
                        <?= formRadio('support_system', 1, 0, trans("enable"), trans("disable"), getEmailOptionStatus($generalSettings, 'support_system')); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('email_address'); ?> (<?= trans("admin_emails_will_send"); ?>)</label>
                        <input type="email" class="form-control" name="mail_options_account" placeholder="<?= trans('email_address'); ?>" value="<?= esc($generalSettings->mail_options_account); ?>">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="verification" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>