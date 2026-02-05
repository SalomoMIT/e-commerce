<div class="row">
    <div class="col-sm-12">
        <div class="wizard-product">
            <h1 class="product-form-title"><?= esc($title); ?></h1>
            <div class="row">
                <div class="col-md-12 wizard-add-product">
                    <ul class="wizard-progress">
                        <li class="active" id="step_general"><strong><?= trans("general_information"); ?></strong></li>
                        <li id="step_dedails"><strong><?= trans("details"); ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-add-product">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12 clearfix m-b-30">
                        <label class="control-label"><?= trans("images"); ?></label>
                        <?= view('dashboard/product/_image_upload'); ?>
                    </div>
                </div>
                <form action="<?= base_url('add-product-post'); ?>" method="post" id="form_validate">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                    <div class="form-group">
                        <label class="control-label"><?= trans('product_type'); ?></label>
                        <div class="row">
                            <?php if ($generalSettings->physical_products_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="product_type" value="physical" id="product_type_1" class="custom-control-input" required <?= $generalSettings->digital_products_system != 1 ? 'checked' : ''; ?>>
                                        <label for="product_type_1" class="custom-control-label"><?= trans('physical'); ?></label>
                                        <p class="form-element-exp"><?= trans('physical_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif;
                            if ($generalSettings->digital_products_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="product_type" value="digital" id="product_type_2" class="custom-control-input" required <?= $generalSettings->physical_products_system != 1 ? 'checked' : ''; ?>>
                                        <label for="product_type_2" class="custom-control-label"><?= trans('digital'); ?></label>
                                        <p class="form-element-exp"><?= trans('digital_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label"><?= trans('listing_type'); ?></label>
                        <div class="row">
                            <?php if ($generalSettings->marketplace_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field listing_sell_on_site">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="listing_type" value="sell_on_site" id="listing_type_1" class="custom-control-input" required>
                                        <label for="listing_type_1" class="custom-control-label"><?= trans('add_product_for_sale'); ?></label><br>
                                        <p class="form-element-exp"><?= trans('add_product_for_sale_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif;
                            if ($generalSettings->classified_ads_system == 1 && $generalSettings->physical_products_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field listing_ordinary_listing">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="listing_type" value="ordinary_listing" id="listing_type_2" class="custom-control-input" required>
                                        <label for="listing_type_2" class="custom-control-label"><?= trans('add_product_services_listing'); ?></label>
                                        <p class="form-element-exp"><?= trans('add_product_services_listing_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif;
                            if ($generalSettings->bidding_system == 1 && $generalSettings->physical_products_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field listing_bidding">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="listing_type" value="bidding" id="listing_type_3" class="custom-control-input" required>
                                        <label for="listing_type_3" class="custom-control-label"><?= trans('add_product_get_price_requests'); ?></label>
                                        <p class="form-element-exp"><?= trans('add_product_get_price_requests_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif;
                            if ($generalSettings->digital_products_system == 1 && $generalSettings->selling_license_keys_system == 1): ?>
                                <div class="col-12 col-sm-6 col-custom-field listing_license_keys">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" name="listing_type" value="license_key" id="listing_type_4" class="custom-control-input" required>
                                        <label for="listing_type_4" class="custom-control-label"><?= trans('add_product_sell_license_keys'); ?></label>
                                        <p class="form-element-exp"><?= trans('add_product_sell_license_keys_exp'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group form-group-category">
                        <label class="control-label"><?= trans("category"); ?></label>
                        <select id="categories" name="category_id[]" class="select2 form-control subcategory-select m-0" onchange="getSubCategoriesDashboard(this.value, 1, <?= selectedLangId(); ?>);" required>
                            <option value=""><?= trans('select_category'); ?></option>
                            <?php if (!empty($parentCategories)):
                                foreach ($parentCategories as $item): ?>
                                    <option value="<?= esc($item->id); ?>"><?= esc($item->cat_name); ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                        <div id="category_select_container"></div>
                    </div>

                    <div class="panel-group panel-group-product">
                        <?php $languages = array();
                        array_push($languages, $activeLang);
                        if (!empty($activeLanguages)):
                            foreach ($activeLanguages as $language):
                                if (!empty($language->id != selectedLangId())) {
                                    array_push($languages, $language);
                                }
                            endforeach;
                        endif;
                        if (!empty($languages)):
                            foreach ($languages as $language):
                                $editorId = $language->id == selectedLangId() ? 'editor_main' : 'editor_' . $language->id; ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" href="#collapse_<?= $language->id; ?>"><?= trans("details"); ?><?= $activeLanguages > 1 ? ':&nbsp;' . esc($language->name) : ''; ?>&nbsp;<?= selectedLangId() != $language->id ? '(' . trans("optional") . ')' : ''; ?><i class="fa fa-caret-down pull-right"></i></a>
                                        </h4>
                                    </div>
                                    <div id="collapse_<?= $language->id; ?>" class="panel-collapse collapse <?= selectedLangId() == $language->id ? 'in' : ''; ?>">
                                        <div class="panel-body">
                                            <div class="form-group m-b-15">
                                                <label class="control-label"><?= trans("title"); ?></label>
                                                <input type="text" name="title_<?= $language->id; ?>" class="form-control form-input" placeholder="<?= trans("title"); ?>" <?= selectedLangId() == $language->id ? 'required' : ''; ?> maxlength="499">
                                            </div>
                                            <div class="form-group m-b-15">
                                                <label class="control-label"><?= trans("short_description"); ?></label>
                                                <input type="text" name="short_description_<?= $language->id; ?>" class="form-control form-input" placeholder="<?= trans("short_description"); ?>" maxlength="499">
                                            </div>
                                            <div class="form-group m-b-15">
                                                <?= view("dashboard/product/_tags_input", ['tags' => '', 'language' => $language]); ?>
                                            </div>
                                            <div class="form-group m-b-15">
                                                <label class="control-label"><?= trans("description"); ?></label>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="button" id="btn_add_image_editor" class="btn btn-md btn-info m-b-10" data-editor-id="<?= $editorId; ?>" data-toggle="modal" data-target="#fileManagerModal"><i class="fa fa-image"></i>&nbsp;&nbsp;<?= trans("add_image"); ?></button>
                                                        <?php if (aiWriter()->status && hasPermission('ai_writer') && $editorId == 'editor_main'): ?>
                                                            <button type="button" class="btn btn-md btn-default btn-open-ai-writer m-b-10" data-toggle="modal" data-target="#modalAiWriter"><i class="fa fa-pencil"></i>&nbsp;&nbsp;&nbsp;<?= trans("ai_writer"); ?></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <textarea name="description_<?= $language->id; ?>" id="<?= $editorId; ?>" class="tinyMCE text-editor"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 m-t-30 buttons-product-form">
                            <button type="submit" class="btn btn-lg btn-success pull-right"><i class="fa fa-check"></i>&nbsp;&nbsp;<?= trans("save_and_continue"); ?></button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-file-manager" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("images"); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="file-manager">
                    <div class="file-manager-left">
                        <div class="dm-uploader-container">
                            <div id="drag-and-drop-zone-file-manager" class="dm-uploader text-center">
                                <p class="file-manager-file-types">
                                    <span>JPG</span>
                                    <span>JPEG</span>
                                    <span>PNG</span>
                                </p>
                                <p class="dm-upload-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                                        <path fill="currentColor"
                                              d="M14.702 28.838c-1.757 0-3.054-.031-4.248-.061c-1.014-.024-1.954-.047-3.043-.047a6.454 6.454 0 0 1-6.447-6.446a6.4 6.4 0 0 1 2.807-5.321a10.6 10.6 0 0 1-.217-2.138C3.554 8.983 8.307 4.23 14.15 4.23c3.912 0 7.495 2.164 9.332 5.574a6.4 6.4 0 0 1 4.599-1.929a6.454 6.454 0 0 1 6.258 8.008a6.45 6.45 0 0 1 4.699 6.207a6.455 6.455 0 0 1-6.447 6.448c-1.661 0-2.827.013-3.979.024c-1.126.012-2.239.024-3.784.024a.5.5 0 0 1 0-1c1.541 0 2.65-.012 3.773-.024c1.155-.012 2.325-.024 3.99-.024a5.447 5.447 0 0 0 1.025-10.798a.5.5 0 0 1-.379-.653a5.452 5.452 0 0 0-5.156-7.213a5.41 5.41 0 0 0-4.318 2.129a.498.498 0 0 1-.852-.101a9.62 9.62 0 0 0-8.76-5.674c-5.291 0-9.596 4.304-9.596 9.595c0 .76.09 1.518.267 2.252a.5.5 0 0 1-.227.545a5.41 5.41 0 0 0-2.63 4.662a5.453 5.453 0 0 0 5.447 5.446c1.098 0 2.045.022 3.067.048c1.188.028 2.477.06 4.224.06a.5.5 0 1 1-.001 1.002"/>
                                        <path fill="currentColor" d="M26.35 22.456a.5.5 0 0 1-.347-.14l-6.777-6.535l-6.746 6.508a.5.5 0 1 1-.694-.721l7.093-6.841a.5.5 0 0 1 .694-.001l7.123 6.869a.5.5 0 0 1-.346.861"/>
                                        <path fill="currentColor" d="M19.226 35.769a.5.5 0 0 1-.5-.5V15.087a.5.5 0 0 1 1 0V35.27a.5.5 0 0 1-.5.499"/>
                                    </svg>
                                </p>
                                <p class="dm-upload-text"><?= trans("drag_drop_images_here"); ?></p>
                                <p class="text-center">
                                    <button class="btn btn-default btn-browse-files"><?= trans('browse_files'); ?></button>
                                </p>
                                <a class='btn btn-md dm-btn-select-files'>
                                    <input type="file" name="file" size="40" multiple="multiple">
                                </a>
                                <ul class="dm-uploaded-files" id="files-file-manager"></ul>
                                <button type="button" id="btn_reset_upload_image" class="btn btn-reset-upload"><?= trans("reset"); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="file-manager-right">
                        <div class="file-manager-content">
                            <div id="ckimage_file_upload_response">
                                <?php if (!empty($fileManagerImages)):
                                    foreach ($fileManagerImages as $image): ?>
                                        <div class="col-file-manager" id="fm_img_col_id_<?= $image->id; ?>">
                                            <div class="file-box" data-file-id="<?= $image->id; ?>" data-file-path="<?= getFileManagerImageUrl($image); ?>">
                                                <div class="image-container">
                                                    <img src="<?= getFileManagerImageUrl($image); ?>" alt="" class="img-responsive">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="selected_fm_img_file_id">
                    <input type="hidden" id="selected_fm_img_file_path">
                </div>
            </div>
            <div class="modal-footer">
                <div class="file-manager-footer">
                    <button type="button" id="btn_fm_img_delete" class="btn btn-sm btn-danger color-white pull-left btn-file-delete m-r-3"><i class="fa fa-trash-can"></i>&nbsp;&nbsp;<?= trans('delete'); ?></button>
                    <button type="button" id="btn_fm_img_select" class="btn btn-sm btn-info color-white btn-file-select"><i class="fa fa-check"></i>&nbsp;&nbsp;<?= trans('select_image'); ?></button>
                    <button type="button" class="btn btn-sm btn-secondary color-white" data-dismiss="modal"><?= trans('close'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('dashboard/product/_product_part'); ?>