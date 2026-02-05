<?php if ($product->product_type == 'digital'): ?>
    <div class="section-product-details">

        <div class="form-box <?= $product->listing_type == 'license_key' ? 'form-box-last' : ''; ?>">
            <div class="form-box-head">
                <h4 class="title">
                    <?= trans('license_keys'); ?><br>
                    <small><?= trans("license_keys_system_exp"); ?></small>
                </h4>
            </div>
            <div class="form-box-body">
                <button type="button" class="btn btn-md btn-info" data-toggle="modal" data-target="#addLicenseKeysModal"><?= trans("add_license_keys"); ?></button>
                <button type="button" class="btn btn-md btn-secondary" data-toggle="modal" data-target="#viewLicenseKeysModal"><?= trans("view_license_keys"); ?></button>
            </div>
        </div>

        <?php if ($product->listing_type != 'license_key'): ?>
            <div class="form-box">
                <div class="form-box-head">
                    <h4 class="title"><?= trans('multiple_sale'); ?><br></h4>
                </div>
                <div class="form-box-body">
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="multiple_sale" value="1" id="multiple_sale_1" class="custom-control-input" <?= $product->multiple_sale == 1 ? 'checked' : ''; ?> required>
                                <label for="multiple_sale_1" class="custom-control-label"><?= trans('multiple_sale_option_1'); ?></label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 listing_ordinary_listing">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="multiple_sale" value="0" id="multiple_sale_2" class="custom-control-input" <?= $product->multiple_sale != 1 ? 'checked' : ''; ?> required>
                                <label for="multiple_sale_2" class="custom-control-label"><?= trans('multiple_sale_option_2'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-box form-box-last">
                <div class="form-box-head">
                    <h4 class="title">
                        <?= trans('files_included'); ?><br>
                        <small><?= trans("files_included_ext"); ?></small>
                    </h4>
                </div>
                <div class="form-box-body">
                    <input type="text" name="files_included" class="form-control form-input" value="<?= esc($product->files_included); ?>" placeholder="<?= trans("files_included"); ?>" required maxlength="250">
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif;
if ($product->listing_type == 'license_key'): ?>
    <input type="hidden" name="multiple_sale" value="1">
<?php endif; ?>

<div class="modal fade" id="addLicenseKeysModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-custom modal-variation" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("add_license_keys"); ?></h5>
                <p class="modal-title-exp"><?= trans("add_license_keys_exp"); ?></p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div id="result-add-license-keys" class="text-success m-b-5"></div>
                <div class="form-group">
                    <textarea name="license_keys" id="textarea_license_keys" class="form-control form-textarea" placeholder="<?= trans("license_keys"); ?>"></textarea>
                </div>
                <div class="form-group m-0">
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <label class="control-label-small"><?= trans('allow_duplicate_license_keys'); ?></label>
                        </div>
                        <div class="col-sm-3 col-xs-12">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="allow_duplicate_license_keys" value="1" id="allow_duplicate_1" class="custom-control-input">
                                <label for="allow_duplicate_1" class="custom-control-label"><?= trans('yes'); ?></label>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-12">
                            <div class="custom-control custom-radio">
                                <input type="radio" name="allow_duplicate_license_keys" value="0" id="allow_duplicate_2" class="custom-control-input" checked>
                                <label for="allow_duplicate_2" class="custom-control-label"><?= trans('no'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group m-0 m-t-10">
                    <div class="loader-license-keys">
                        <div class="spinner">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-md btn-success btn-add-license-keys" onclick="addLicenseKeys('<?= $product->id; ?>');"><?= trans("add_license_keys"); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewLicenseKeysModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-custom modal-variation" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("license_keys"); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="license_key_list_product_id" value="<?= $product->id; ?>">
                <div id="response_license_key" class="modal-license-key-list">
                    <?= view("dashboard/product/license/_license_keys_list", ['product' => $product, 'licenseKeys' => $licenseKeys]); ?>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
