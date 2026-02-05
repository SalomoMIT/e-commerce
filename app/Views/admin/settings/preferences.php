<?php $activeTab = inputGet('tab');
if (empty($activeTab)):
    $activeTab = '1';
endif; ?>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-sm-12">
        <h3 style="font-size: 18px; font-weight: 600;margin-top: 10px;"><?= trans('preferences'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-6">
        <form action="<?= base_url('Admin/preferencesPost'); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <input type="hidden" name="active_tab" id="input_active_tab" value="<?= clrNum($activeTab); ?>">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?= $activeTab == '1' ? ' active' : ''; ?>"><a href="#tab_1" data-toggle="tab" onclick="$('#input_active_tab').val('1');"><?= trans('system'); ?></a></li>
                    <li class="<?= $activeTab == '2' ? ' active' : ''; ?>"><a href="#tab_2" data-toggle="tab" onclick="$('#input_active_tab').val('2');"><?= trans('general'); ?></a></li>
                    <li class="<?= $activeTab == '3' ? ' active' : ''; ?>"><a href="#tab_3" data-toggle="tab" onclick="$('#input_active_tab').val('3');"><?= trans('products'); ?></a></li>
                    <li class="<?= $activeTab == '4' ? ' active' : ''; ?>"><a href="#tab_4" data-toggle="tab" onclick="$('#input_active_tab').val('4');"><?= trans('shop'); ?></a></li>
                    <li class="<?= $activeTab == '5' ? ' active' : ''; ?>"><a href="#tab_5" data-toggle="tab" onclick="$('#input_active_tab').val('5');"><?= trans('wallet'); ?></a></li>
                    <li class="<?= $activeTab == '6' ? ' active' : ''; ?>"><a href="#tab_6" data-toggle="tab" onclick="$('#input_active_tab').val('6');"><?= trans('file_upload'); ?></a></li>
                </ul>
                <div class="tab-content settings-tab-content">
                    <div class="tab-pane<?= $activeTab == '1' ? ' active' : ''; ?>" id="tab_1">
                        <div class="form-group">
                            <label><?= trans("physical_products"); ?></label>
                            <?= formRadio('physical_products_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->physical_products_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("digital_products"); ?></label>
                            <?= formRadio('digital_products_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->digital_products_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("marketplace_selling_product_on_the_site"); ?></label>
                            <?= formRadio('marketplace_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->marketplace_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("classified_ads_adding_product_as_listing"); ?></label>
                            <?= formRadio('classified_ads_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->classified_ads_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("bidding_system_request_quote"); ?></label>
                            <?= formRadio('bidding_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->bidding_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("selling_license_keys"); ?></label>
                            <?= formRadio('selling_license_keys_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->selling_license_keys_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans('multi_vendor_system'); ?></label>
                            <small style="font-size: 13px;">(<?= trans("multi_vendor_system_exp"); ?>)</small>
                            <?= formRadio('multi_vendor_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->multi_vendor_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('timezone'); ?></label>
                            <select name="timezone" class="form-control max-700">
                                <?php $timezones = timezone_identifiers_list();
                                if (!empty($timezones)):
                                    foreach ($timezones as $timezone):?>
                                        <option value="<?= $timezone; ?>" <?= $timezone == $generalSettings->timezone ? 'selected' : ''; ?>><?= $timezone; ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="system" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '2' ? ' active' : ''; ?>" id="tab_2">
                        <div class="form-group">
                            <label><?= trans("multilingual_system"); ?></label>
                            <?= formRadio('multilingual_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->multilingual_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("rss_system"); ?></label>
                            <?= formRadio('rss_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->rss_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans('vendor_verification_system'); ?></label>
                            <small><?= "(" . trans('vendor_verification_system_exp') . ")"; ?></small>
                            <?= formRadio('vendor_verification_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->vendor_verification_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_vendor_contact_information"); ?></label>
                            <?= formRadio('show_vendor_contact_information', 1, 0, trans("yes"), trans("no"), $generalSettings->show_vendor_contact_information, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_vendor_contact_info_guests"); ?></label>
                            <?= formRadio('show_vendor_contact_info_guests', 1, 0, trans("yes"), trans("no"), $generalSettings->show_vendor_contact_info_guests, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("guest_checkout"); ?></label>
                            <?= formRadio('guest_checkout', 1, 0, trans("enable"), trans("disable"), $generalSettings->guest_checkout, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("search_by_location"); ?></label>
                            <?= formRadio('location_search_header', 1, 0, trans("enable"), trans("disable"), $generalSettings->location_search_header, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("pwa"); ?></label>
                            <?= formRadio('pwa_status', 1, 0, trans("enable"), trans("disable"), $generalSettings->pwa_status, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans("pwa_logo"); ?></label>
                            <?php if (!empty(getPwaLogo($generalSettings, 'md'))): ?>
                                <div class="display-block m-b-10">
                                    <img src="<?= base_url(getPwaLogo($generalSettings, 'md')); ?>?t=<?= uniqid(); ?>" width="100">
                                </div>
                            <?php endif; ?>
                            <div class="display-block">
                                <a class='btn btn-success btn-sm btn-file-upload'>
                                    <?= trans('select_logo'); ?>
                                    <input type="file" name="pwa_logo" size="40" accept=".png, .jpg, .jpeg, .gif" onchange="$('#upload-file-info-pwa').html($(this).val().replace(/.*[\/\\]/, ''));">
                                </a>
                                (PNG, 512x512 px)
                            </div>
                            <span class='label label-info' id="upload-file-info-pwa"></span>
                        </div>
                        <div class="alert alert-info alert-large m-t-10">
                            <strong><?= trans("warning"); ?>!</strong>&nbsp;&nbsp;<?= trans("pwa_warning"); ?>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="general" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '3' ? ' active' : ''; ?>" id="tab_3">
                        <div class="form-group">
                            <label><?= trans("product_approval_new_products"); ?></label>
                            <?= formRadio('approve_before_publishing', 1, 0, trans("enable"), trans("disable"), $generalSettings->approve_before_publishing, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("product_approval_edited_products"); ?></label>
                            <div class="row">
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="approve_after_editing" value="1" id="pr_app_edited_products_1" class="custom-control-input" <?= $generalSettings->approve_after_editing == 1 ? 'checked' : ''; ?>>
                                        <label for="pr_app_edited_products_1" class="custom-control-label"><?= trans("enable_dont_hide_products"); ?></label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="approve_after_editing" value="2" id="pr_app_edited_products_2" class="custom-control-input" <?= $generalSettings->approve_after_editing == 2 ? 'checked' : ''; ?>>
                                        <label for="pr_app_edited_products_2" class="custom-control-label"><?= trans("enable_hide_products"); ?></label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="approve_after_editing" value="0" id="pr_app_edited_products_3" class="custom-control-input" <?= empty($generalSettings->approve_after_editing) ? 'checked' : ''; ?>>
                                        <label for="pr_app_edited_products_3" class="custom-control-label"><?= trans("disable"); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= trans("featured_products_system"); ?></label>
                            <?= formRadio('promoted_products', 1, 0, trans("enable"), trans("disable"), $generalSettings->promoted_products, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("vendor_bulk_product_upload"); ?></label>
                            <?= formRadio('vendor_bulk_product_upload', 1, 0, trans("enable"), trans("disable"), $generalSettings->vendor_bulk_product_upload, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_sold_products_on_site"); ?></label>
                            <?= formRadio('show_sold_products', 1, 0, trans("yes"), trans("no"), $generalSettings->show_sold_products, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("product_link_structure"); ?></label>
                            <?= formRadio('product_link_structure', 'slug-id', 'id-slug', 'domain.com/slug-id', 'domain.com/id-slug', $generalSettings->product_link_structure, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("reviews"); ?></label>
                            <?= formRadio('reviews', 1, 0, trans("enable"), trans("disable"), $generalSettings->reviews, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("product_comments"); ?></label>
                            <?= formRadio('product_comments', 1, 0, trans("enable"), trans("disable"), $generalSettings->product_comments, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("blog_comments"); ?></label>
                            <?= formRadio('blog_comments', 1, 0, trans("enable"), trans("disable"), $generalSettings->blog_comments, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("comment_approval_system"); ?></label>
                            <?= formRadio('comment_approval_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->comment_approval_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="products" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '4' ? ' active' : ''; ?>" id="tab_4">
                        <div class="form-group">
                            <label><?= trans("refund_system"); ?></label>
                            <?= formRadio('refund_system', 1, 0, trans("enable"), trans("disable"), $generalSettings->refund_system, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_number_sales_profile"); ?></label>
                            <?= formRadio('profile_number_of_sales', 1, 0, trans("yes"), trans("no"), $generalSettings->profile_number_of_sales, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("allow_vendors_change_shop_name"); ?></label>
                            <?= formRadio('vendors_change_shop_name', 1, 0, trans("yes"), trans("no"), $generalSettings->vendors_change_shop_name, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_customer_email_seller"); ?></label>
                            <?= formRadio('show_customer_email_seller', 1, 0, trans("yes"), trans("no"), $generalSettings->show_customer_email_seller, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("show_customer_phone_number_seller"); ?></label>
                            <?= formRadio('show_customer_phone_seller', 1, 0, trans("yes"), trans("no"), $generalSettings->show_customer_phone_seller, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group radio-affiliate-approve">
                            <label><?= trans("auto_approve_orders"); ?></label>
                            <?= formRadio('auto_approve_orders', 1, 0, trans("yes"), trans("no"), $generalSettings->auto_approve_orders, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group" id="auto_approve_orders_days_cont" style="<?= $generalSettings->auto_approve_orders == 1 ? '' : 'display:none;'; ?>">
                            <label><?= trans("number_of_days"); ?></label>
                            <input type="number" name="auto_approve_orders_days" value="<?= esc($generalSettings->auto_approve_orders_days); ?>" class="form-control max-700" min="1" max="9999" required>
                            <hr>
                        </div>
                        <div class="form-group request_documents_vendors">
                            <label><?= trans("request_documents_vendors"); ?></label>
                            <?= formRadio('request_documents_vendors', 1, 0, trans("yes"), trans("no"), $generalSettings->request_documents_vendors, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group" id="request_documents_vendors_cont" style="<?= $generalSettings->request_documents_vendors == 1 ? '' : 'display:none;'; ?>">
                            <label class="control-label"><?= trans("input_explanation"); ?>&nbsp;(E.g. ID Card)</label>
                            <textarea class="form-control max-700" name="explanation_documents_vendors"><?= str_replace('<br/>', '\n', $generalSettings->explanation_documents_vendors); ?></textarea>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="shop" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '5' ? ' active' : ''; ?>" id="tab_5">
                        <div class="form-group">
                            <label><?= trans("wallet"); ?></label>
                            <?= formRadio('wallet_status', 1, 0, trans("enable"), trans("disable"), $paymentSettings->wallet_status, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("wallet_deposit"); ?></label>
                            <?= formRadio('wallet_deposit', 1, 0, trans("enable"), trans("disable"), $paymentSettings->wallet_deposit, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label><?= trans("paying_wallet_balance"); ?></label>
                            <?= formRadio('pay_with_wallet_balance', 1, 0, trans("enable"), trans("disable"), $paymentSettings->pay_with_wallet_balance, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('minimum_deposit_amount'); ?></label>
                            <?= renderPriceInput('wallet_min_deposit', $paymentSettings->wallet_min_deposit); ?>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="wallet" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane<?= $activeTab == '6' ? ' active' : ''; ?>" id="tab_6">
                        <div class="form-group">
                            <label><?= trans("image_file_format"); ?></label>
                            <div class="row">
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="image_file_format" value="JPG" id="image_file_format_1" class="custom-control-input" <?= $productSettings->image_file_format == 'JPG' ? 'checked' : ''; ?>>
                                        <label for="image_file_format_1" class="custom-control-label">JPG</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="image_file_format" value="WEBP" id="image_file_format_2" class="custom-control-input" <?= $productSettings->image_file_format == 'WEBP' ? 'checked' : ''; ?>>
                                        <label for="image_file_format_2" class="custom-control-label">WEBP</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="image_file_format" value="PNG" id="image_file_format_3" class="custom-control-input" <?= $productSettings->image_file_format == 'PNG' ? 'checked' : ''; ?>>
                                        <label for="image_file_format_3" class="custom-control-label">PNG</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12 col-lg-4 m-b-5">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="image_file_format" value="original" id="image_file_format_4" class="custom-control-input" <?= $productSettings->image_file_format == 'original' ? 'checked' : ''; ?>>
                                        <label for="image_file_format_4" class="custom-control-label"><?= trans("keep_original_file_format"); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= trans("product_image_upload"); ?></label>
                            <?= formRadio('is_product_image_required', 1, 0, trans("required"), trans("optional"), $productSettings->is_product_image_required, 'col-lg-4'); ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('product_image_upload_limit'); ?></label>
                            <input type="number" name="product_image_limit" class="form-control max-700" value="<?= $productSettings->product_image_limit; ?>" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('max_file_size') . ' (' . trans("image") . ')'; ?></label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group max-700">
                                        <input type="number" name="max_file_size_image" value="<?= round(($productSettings->max_file_size_image / 1048576), 2); ?>" min="1" class="form-control" aria-describedby="basic-addon1" required>
                                        <span class="input-group-addon" id="basic-addon1">MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('max_file_size') . ' (' . trans("video") . ')'; ?></label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group max-700">
                                        <input type="number" name="max_file_size_video" value="<?= round(($productSettings->max_file_size_video / 1048576), 2); ?>" min="1" class="form-control" aria-describedby="basic-addon2" required>
                                        <span class="input-group-addon" id="basic-addon2">MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('max_file_size') . ' (' . trans("audio") . ')'; ?></label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group max-700">
                                        <input type="number" name="max_file_size_audio" value="<?= round(($productSettings->max_file_size_audio / 1048576), 2); ?>" min="1" class="form-control" aria-describedby="basic-addon3" required>
                                        <span class="input-group-addon" id="basic-addon3">MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right m-t-30">
                            <button type="submit" name="submit" value="file_upload" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('storage'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/storageSettingsPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-lg-3 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="storage" value="local" id="rd_storage_1" class="custom-control-input" <?= $activeStorage == 'local' ? 'checked' : ''; ?> required>
                                    <label for="rd_storage_1" class="custom-control-label"><?= trans("local_storage"); ?></label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-lg-3 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="storage" value="aws_s3" id="rd_storage_2" class="custom-control-input" <?= $activeStorage == 'aws_s3' ? 'checked' : ''; ?> required>
                                    <label for="rd_storage_2" class="custom-control-label">AWS S3</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-lg-3 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="storage" value="cloudflare_r2" id="rd_storage_3" class="custom-control-input" <?= $activeStorage == 'cloudflare_r2' ? 'checked' : ''; ?> required>
                                    <label for="rd_storage_3" class="custom-control-label">Cloudflare R2</label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-lg-3 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="storage" value="backblaze_b2" id="rd_storage_4" class="custom-control-input" <?= $activeStorage == 'backblaze_b2' ? 'checked' : ''; ?> required>
                                    <label for="rd_storage_4" class="custom-control-label">Backblaze B2</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="formAwsS3">
                        <div class="form-group">
                            <label class="control-label"><?= trans('access_key'); ?></label>
                            <input type="text" class="form-control" name="aws_key" placeholder="<?= trans('access_key'); ?>" value="<?= esc($storageSettings->aws_key); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('secret_key'); ?></label>
                            <input type="text" class="form-control" name="aws_secret" placeholder="<?= trans('secret_key'); ?>" value="<?= esc($storageSettings->aws_secret); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('bucket_name'); ?></label>
                            <input type="text" class="form-control" name="aws_bucket" placeholder="<?= trans('bucket_name'); ?>" value="<?= esc($storageSettings->aws_bucket); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('region'); ?></label>
                            <input type="text" class="form-control" name="aws_region" placeholder="E.g: us-east-1" value="<?= esc($storageSettings->aws_region); ?>">
                        </div>
                    </div>

                    <div id="formCloudflareR2">
                        <div class="form-group">
                            <label class="control-label"><?= trans('access_key'); ?></label>
                            <input type="text" class="form-control" name="r2_key" placeholder="<?= trans('access_key'); ?>" value="<?= esc($storageSettings->r2_key); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('secret_key'); ?></label>
                            <input type="text" class="form-control" name="r2_secret" placeholder="<?= trans('secret_key'); ?>" value="<?= esc($storageSettings->r2_secret); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('bucket_name'); ?></label>
                            <input type="text" class="form-control" name="r2_bucket" placeholder="<?= trans('bucket_name'); ?>" value="<?= esc($storageSettings->r2_bucket); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('endpoint_url'); ?></label>
                            <input type="text" class="form-control" name="r2_endpoint_url" placeholder="<?= trans('endpoint_url'); ?>" value="<?= esc($storageSettings->r2_endpoint_url); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('public_url'); ?></label>
                            <input type="text" class="form-control" name="r2_public_url" placeholder="<?= trans('public_url'); ?>" value="<?= esc($storageSettings->r2_public_url); ?>">
                        </div>
                    </div>

                    <div id="formBackblazeB2">
                        <div class="form-group">
                            <label class="control-label"><?= trans("key_id"); ?></label>
                            <input type="text" class="form-control" name="b2_key" placeholder="<?= trans("key_id"); ?>" value="<?= esc($storageSettings->b2_key); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('application_key'); ?></label>
                            <input type="text" class="form-control" name="b2_secret" placeholder="<?= trans('application_key'); ?>" value="<?= esc($storageSettings->b2_secret); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('bucket_name'); ?></label>
                            <input type="text" class="form-control" name="b2_bucket" placeholder="<?= trans('bucket_name'); ?>" value="<?= esc($storageSettings->b2_bucket); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('endpoint_url'); ?></label>
                            <input type="text" class="form-control" name="b2_endpoint_url" placeholder="<?= trans('endpoint_url'); ?>" value="<?= esc($storageSettings->b2_endpoint_url); ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans('public_url'); ?></label>
                            <input type="text" class="form-control" name="b2_public_url" placeholder="<?= trans('public_url'); ?>" value="<?= esc($storageSettings->b2_public_url); ?>">
                        </div>
                    </div>


                </div>

                <div class="box-footer">
                    <button type="submit" name="submit" value="post_deletion" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('ai_content_creator'); ?>&nbsp;(OpenAI - ChatGPT)</h3>
            </div>
            <form action="<?= base_url('Admin/aiWriterPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <?php $aiWriter = aiWriter(); ?>
                    <div class="form-group">
                        <label><?= trans("status"); ?></label>
                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $aiWriter->status); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans('api_key'); ?></label>
                        <input type="text" class="form-control" name="api_key" placeholder="<?= trans('api_key'); ?>" value="<?= esc($aiWriter->apiKey); ?>" required>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="post_deletion" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on("change", ".radio-affiliate-approve input", function () {
        var val = $('input[name="auto_approve_orders"]:checked').val();
        if (val == 1) {
            $('#auto_approve_orders_days_cont').show();
        } else {
            $('#auto_approve_orders_days_cont').hide();
        }
    });
    $(document).on("change", ".request_documents_vendors input", function () {
        var val = $('input[name="request_documents_vendors"]:checked').val();
        if (val == 1) {
            $('#request_documents_vendors_cont').show();
        } else {
            $('#request_documents_vendors_cont').hide();
        }
    });

    function toggleStorageForms() {
        const selected = document.querySelector('input[name="storage"]:checked').value;

        document.getElementById('formAwsS3').style.display = (selected === 'aws_s3') ? 'block' : 'none';
        document.getElementById('formCloudflareR2').style.display = (selected === 'cloudflare_r2') ? 'block' : 'none';
        document.getElementById('formBackblazeB2').style.display = (selected === 'backblaze_b2') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleStorageForms();
        const radios = document.querySelectorAll('input[name="storage"]');
        radios.forEach(radio => {
            radio.addEventListener('change', toggleStorageForms);
        });
    });
</script>