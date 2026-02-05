<?php $activeTab = inputGet('tab');
if (empty($activeTab)):
    $activeTab = '1';
endif; ?>
<div class="row">
    <div class="col-md-12">
        <form action="<?= base_url('Admin/generalSettingsPost'); ?>" method="post">
            <?= csrf_field(); ?>
            <input type="hidden" name="active_tab" id="input_active_tab" value="<?= clrNum($activeTab); ?>">
            <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
            <div class="form-group">
                <label><?= trans("settings_language"); ?></label>
                <select name="lang_id" class="form-control" onchange="window.location.href = '<?= adminUrl('general-settings'); ?>?lang='+this.value+'&tab=<?= clrNum($activeTab); ?>';" style="max-width: 600px;">
                    <?php foreach ($activeLanguages as $language): ?>
                        <option value="<?= $language->id; ?>" <?= $language->id == $settingsLang ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?= $activeTab == '1' ? ' active' : ''; ?>"><a href="#tab_1" data-toggle="tab" onclick="$('#input_active_tab').val('1');"><?= trans('general_settings'); ?></a></li>
                    <li class="<?= $activeTab == '2' ? ' active' : ''; ?>"><a href="#tab_2" data-toggle="tab" onclick="$('#input_active_tab').val('2');"><?= trans('contact_settings'); ?></a></li>
                    <li class="<?= $activeTab == '3' ? ' active' : ''; ?>"><a href="#tab_3" data-toggle="tab" onclick="$('#input_active_tab').val('3');"><?= trans('social_media_settings'); ?></a></li>
                    <li class="<?= $activeTab == '4' ? ' active' : ''; ?>"><a href="#tab_4" data-toggle="tab" onclick="$('#input_active_tab').val('4');"><?= trans('facebook_comments'); ?></a></li>
                    <li class="<?= $activeTab == '5' ? ' active' : ''; ?>"><a href="#tab_5" data-toggle="tab" onclick="$('#input_active_tab').val('5');"><?= trans('custom_header_codes'); ?></a></li>
                    <li class="<?= $activeTab == '6' ? ' active' : ''; ?>"><a href="#tab_6" data-toggle="tab" onclick="$('#input_active_tab').val('6');"><?= trans('custom_footer_codes'); ?></a></li>
                    <li class="<?= $activeTab == '7' ? ' active' : ''; ?>"><a href="#tab_7" data-toggle="tab" onclick="$('#input_active_tab').val('7');"><?= trans('cookies_warning'); ?></a></li>
                    <li class="<?= $activeTab == '8' ? ' active' : ''; ?>"><a href="#tab_8" data-toggle="tab" onclick="$('#input_active_tab').val('8');"><?= trans('bulk_upload_documentation'); ?></a></li>
                </ul>
                <div class="tab-content settings-tab-content">
                    <div class="tab-pane<?= $activeTab == '1' ? ' active' : ''; ?>" id="tab_1">
                        <div class="form-group">
                            <label class="control-label"><?= trans('app_name'); ?></label>
                            <input type="text" class="form-control" name="application_name" placeholder="<?= trans('app_name'); ?>" value="<?= esc($generalSettings->application_name); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('site_title'); ?></label>
                            <input type="text" class="form-control" name="site_title" placeholder="<?= trans('site_title'); ?>" value="<?= esc($settings->site_title); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('homepage_title'); ?></label>
                            <input type="text" class="form-control" name="homepage_title" placeholder="<?= trans('homepage_title'); ?>" value="<?= esc($settings->homepage_title); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('site_description'); ?></label>
                            <input type="text" class="form-control" name="site_description" placeholder="<?= trans('site_description'); ?>" value="<?= esc($settings->site_description); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('keywords'); ?></label>
                            <input type="text" class="form-control" name="keywords" placeholder="<?= trans('keywords'); ?>" value="<?= esc($settings->keywords); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('copyright'); ?></label>
                            <input type="text" class="form-control" name="copyright" placeholder="<?= trans('copyright'); ?>" value="<?= esc($settings->copyright); ?>">
                        </div>
                        <div class="form-group">
                            <?= renderTextEditorAdmin('about_footer', trans("footer_about_section"), $settings->about_footer, false, false, 'tinyMCEsmall'); ?>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '2' ? ' active' : ''; ?>" id="tab_2">
                        <div class="form-group">
                            <label class="control-label"><?= trans('address'); ?></label>
                            <input type="text" class="form-control" name="contact_address" placeholder="<?= trans('address'); ?>" value="<?= esc($settings->contact_address); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('email_address'); ?></label>
                            <input type="text" class="form-control" name="contact_email" placeholder="<?= trans('email_address'); ?>" value="<?= esc($settings->contact_email); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('phone'); ?></label>
                            <input type="text" class="form-control" name="contact_phone" placeholder="<?= trans('phone'); ?>" value="<?= esc($settings->contact_phone); ?>">
                        </div>
                        <div class="form-group">
                            <?= renderTextEditorAdmin('contact_text', trans("contact_text"), $settings->contact_text, false, false, 'tinyMCEsmall'); ?>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '3' ? ' active' : ''; ?>" id="tab_3">
                        <?php $socialArray = getSocialLinksArray($settings, false);
                        foreach ($socialArray as $item):?>
                            <div class="form-group">
                                <label class="control-label"><?= trans($item['inputName']); ?></label>
                                <input type="text" class="form-control" name="<?= $item['inputName']; ?>" placeholder="<?= trans($item['inputName']); ?>" value="<?= esc($item['value']); ?>" maxlength="1000">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="tab-pane<?= $activeTab == '4' ? ' active' : ''; ?>" id="tab_4">
                        <div class="form-group">
                            <label><?= trans("facebook_comments"); ?></label>
                            <?= formRadio('facebook_comment_status', 1, 0, trans("enable"), trans("disable"), $generalSettings->facebook_comment_status); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('facebook_comments_code'); ?></label>
                            <textarea class="form-control text-area" name="facebook_comment" placeholder="<?= trans('facebook_comments_code'); ?>" style="min-height: 140px;"><?= $generalSettings->facebook_comment; ?></textarea>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '5' ? ' active' : ''; ?>" id="tab_5">
                        <div class="form-group">
                            <label class="control-label"><?= trans('custom_header_codes'); ?></label>&nbsp;<small class="small-title-inline">(<?= trans("custom_header_codes_exp"); ?>)</small>
                            <textarea class="form-control text-area" name="custom_header_codes" placeholder="<?= trans('custom_header_codes'); ?>" style="min-height: 200px;"><?= $generalSettings->custom_header_codes; ?></textarea>
                        </div>
                        E.g. <?= esc("<style> body {background-color: #00a65a;} </style>"); ?>
                    </div>
                    <div class="tab-pane<?= $activeTab == '6' ? ' active' : ''; ?>" id="tab_6">
                        <div class="form-group">
                            <label class="control-label"><?= trans('custom_footer_codes'); ?></label>&nbsp;<small class="small-title-inline">(<?= trans("custom_footer_codes_exp"); ?>)</small>
                            <textarea class="form-control text-area" name="custom_footer_codes" placeholder="<?= trans('custom_footer_codes'); ?>" style="min-height: 200px;"><?= $generalSettings->custom_footer_codes; ?></textarea>
                        </div>
                        E.g. <?= esc("<script> alert('Hello!'); </script>"); ?>
                    </div>
                    <div class="tab-pane<?= $activeTab == '7' ? ' active' : ''; ?>" id="tab_7">
                        <div class="form-group">
                            <label><?= trans("show_cookies_warning"); ?></label>
                            <?= formRadio('cookies_warning', 1, 0, trans("yes"), trans("no"), $settings->cookies_warning); ?>
                        </div>
                        <div class="form-group">
                            <?= renderTextEditorAdmin('cookies_warning_text', '', $settings->cookies_warning_text, false, false, 'tinyMCEsmall'); ?>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '8' ? ' active' : ''; ?>" id="tab_8">
                        <div class="form-group">
                            <?= renderTextEditorAdmin('bulk_upload_documentation', trans('bulk_upload_documentation'), $settings->bulk_upload_documentation, false, false); ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('cloudflare_turnstile'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/cloudflareTurnstileSettingsPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div class="form-group">
                        <?= formSwitch('turnstile_status', trans('status'), $generalSettings->turnstile_status); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('site_key'); ?></label>
                        <input type="text" class="form-control" name="turnstile_site_key" placeholder="<?= trans('site_key'); ?>" value="<?= esc($generalSettings->turnstile_site_key); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('secret_key'); ?></label>
                        <input type="text" class="form-control" name="turnstile_secret_key" placeholder="<?= trans('secret_key'); ?>" value="<?= esc($generalSettings->turnstile_secret_key); ?>">
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
                <h3 class="box-title"><?= trans('maintenance_mode'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/maintenanceModePost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('title'); ?></label>
                        <input type="text" class="form-control" name="maintenance_mode_title" placeholder="<?= trans('title'); ?>" value="<?= esc($generalSettings->maintenance_mode_title); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('description'); ?></label>
                        <textarea class="form-control text-area" name="maintenance_mode_description" placeholder="<?= trans('description'); ?>" style="min-height: 100px;"><?= esc($generalSettings->maintenance_mode_description); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= trans("status"); ?></label>
                        <?= formRadio('maintenance_mode_status', 1, 0, trans("enable"), trans("disable"), $generalSettings->maintenance_mode_status); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans('image'); ?></label>: assets/img/maintenance_bg.jpg
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>