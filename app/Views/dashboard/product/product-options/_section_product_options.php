<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.css"/>
<script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.js"></script>

<div class="section-product-details">
    <div class="form-box form-box-last">
        <div class="form-box-head">
            <h4 class="title">
                <?= trans('product_options'); ?><br>
                <small><?= trans("product_options_exp"); ?></small>
            </h4>
        </div>
        <div class="form-box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="product-options">
                        <input type="hidden" name="is_options_updated" id="inputIsOptionsUpdated">

                        <div id="options-container"></div>

                        <button type="button" class="btn btn-primary" id="add-option-btn"><i class="fas fa-plus"></i>&nbsp;<?= trans("add_product_option"); ?></button>

                        <div id="variants-preview-container" class="custom-sc" style="display: none; margin-top: 40px; padding-top: 40px; border-top: 2px solid #f5f7f9;">

                            <div class="display-flex align-items-center m-b-30" style="gap: 15px;">
                                <h4 class="title m-0"><?= trans("variants"); ?></h4>
                                <button type="button" class="btn btn-sm btn-default collapse-option-btn" data-toggle="collapse" data-target="#collapseVariants" title="Expand">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div id="collapseVariants" class="collapse in">

                                <div class="row default-settings-grid">
                                    <div class="col-md-12 col-lg-3">
                                        <div class="form-group">
                                            <label><?= trans("price"); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><?= $defaultCurrency->symbol; ?></span>
                                                <input type="text" id="default-price" class="form-control input-price" maxlength="13" placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>" inputmode="decimal">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-success" type="button" id="apply-all-price"><i class="fa fa-check"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-3">
                                        <div class="form-group">
                                            <label><?= trans("discounted_price"); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><?= $defaultCurrency->symbol; ?></span>
                                                <input type="text" id="default-discounted-price" class="form-control input-price" maxlength="13" placeholder="<?= $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00'; ?>" inputmode="decimal">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-success" type="button" id="apply-all-discounted-price"><i class="fa fa-check"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-3">
                                        <div class="form-group">
                                            <label><?= trans("quantity"); ?></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" placeholder="e.g. 100" id="default-quantity" min="0" max="9999999" step="1">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-success" type="button" id="apply-all-quantity"><i class="fa fa-check"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($product->product_type == 'physical'): ?>
                                        <div class="col-md-12 col-lg-3">
                                            <div class="form-group">
                                                <label><?= trans("weight"); ?>(<?= trans("kg"); ?>)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" placeholder="e.g. 0.5" id="default-weight" min="0" max="9999999.999" step="0.001">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-success" type="button" id="apply-all-weight"><i class="fa fa-check"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div id="bulk-actions-toolbar" style="display: none;">
                                    <button type="button" class="btn btn-sm btn-success" id="bulk-activate-variants"><i class="fas fa-check-circle"></i>&nbsp;<?= esc(trans("activate_selected")); ?></button>
                                    <button type="button" class="btn btn-sm btn-warning" id="bulk-deactivate-selected" title="Deactivate selected variants"><i class="fas fa-ban"></i>&nbsp;<?= esc(trans("deactivate_delected")); ?></button>
                                </div>

                                <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                                    <table class="table table-bordered table-hover table-variants">
                                        <thead>
                                        <tr>
                                            <th class="select-all-header">
                                                <input type="checkbox" id="select-all-variants">
                                            </th>
                                            <th class="variant-visual-col"><?= trans("image"); ?>/<?= trans("color"); ?></th>
                                            <th class="variant-name-col"><?= trans("variant_name"); ?></th>
                                            <th class="variant-sku-col"><?= trans("sku"); ?></th>
                                            <th class="variant-price-col"><?= trans("price"); ?>&nbsp;(<?= $defaultCurrency->symbol; ?>)</th>
                                            <th class="variant-discounted-price-col"><?= trans("discounted_price"); ?>&nbsp;(<?= $defaultCurrency->symbol; ?>)</th>
                                            <th class="variant-qty-col"><?= trans("quantity"); ?></th>
                                            <?php if ($product->product_type == 'physical'): ?>
                                                <th class="variant-weight-col"><?= trans("weight"); ?>(<?= trans("kg"); ?>)</th>
                                            <?php endif; ?>
                                            <th class="variant-status-col"><?= trans("default_variant"); ?></th>
                                            <th class="variant-status-col"><?= trans("status"); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody id="variants-table-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="submitted_base_sku" id="submitted_base_sku">
                        <input type="hidden" name="submitted_options_data" id="submitted_options_data">
                        <input type="hidden" name="submitted_variants_data" id="submitted_variants_data">

                        <?= view('dashboard/product/product-options/_modal_image'); ?>

                        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        <h4 class="modal-title" id="confirmationModalLabel"><?= trans("warning"); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?= trans("msg_change_product_option_type"); ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= trans("cancel"); ?></button>
                                        <button type="button" class="btn btn-primary" id="confirmationModalConfirmBtn"><?= trans("confirm"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="translationModal" tabindex="-1" role="dialog" aria-labelledby="translationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        <h4 class="modal-title" id="translationModalLabel"><?= trans("edit_translations"); ?></h4></div>
                                    <div class="modal-body" id="translationModalBody">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= trans("cancel"); ?></button>
                                        <button type="button" class="btn btn-primary" id="saveTranslationsBtn"><?= trans("save_changes"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div id="alertDefaultVariant" class="alert alert-info m-t-30" style="display: none">
            <strong><?= trans("warning"); ?>!</strong>&nbsp;&nbsp;<?= trans("msg_default_variant"); ?>
        </div>
    </div>
</div>

<?php $imageUrls = [];
foreach ($productImages as $productImage) {
    $imageUrls[] = [
        'id' => $productImage->id,
        'url' => getProductImageURL($productImage, 'image_small'),
        'is_option_image' => $productImage->is_option_image ? 1 : 0,
    ];
}

$supportedLanguages = [];
$supportedLanguages[] = [
    'code' => $activeLang->short_form,
    'name' => $activeLang->name
];

if ($generalSettings->multilingual_system == 1 && !empty($activeLanguages)) {
    foreach ($activeLanguages as $language) {
        if ($language->id === $activeLang->id) {
            continue;
        }
        $supportedLanguages[] = [
            'code' => $language->short_form,
            'name' => $language->name
        ];
    }
}
$supportedLanguagesJson = json_encode($supportedLanguages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
$currentAdminLanguage = $defaultLang->short_form;


$jsOptionsConfig = [
    // Product and System URLs/IDs
    'optionProductId' => $product->id,
    'loadUploadedOptionImagesUrl' => base_url("File/loadUploadedOptionImages"),
    'optionImageDeleteUrl' => base_url("File/deleteOptionImage"),
    'noImagePlaceholder' => base_url("assets/img/images.png"),
    'placeholderSwatchImg' => base_url("assets/img/image.png"),
    'errorPlaceholderSwatchImg' => base_url("assets/img/image.png"),

    // Booleans and Data Objects
    'isPhysicalProduct' => ($product->product_type == 'physical'),
    'imageUrls' => $imageUrls,
    'supportedLanguages' => json_decode($supportedLanguagesJson, true),

    // Language and Localization
    'currentAdminLanguage' => $currentAdminLanguage,
    'priceInputPlaceholder' => $defaultCurrency->currency_format == 'european' ? '0,00' : '0.00',
    'confirmDeleteMessage' => trans("confirm_delete"),

    // Placeholders
    'optionNamePlaceholder' => trans("option_name_placeholder"),
    'valuePlaceholder' => trans("value"),
    'pricePlaceholder' => trans("price"),
    'skuPlaceholder' => trans("sku"),
    'qtyPlaceholder' => trans("quantity"),
    'weightPlaceholder' => trans("weight"),
    'defaultPricePlaceholder' => "e.g. 40",
    'discountedPricePlaceholder' => 'e.g. 20',
    'defaultQuantityPlaceholder' => "e.g. 100",

    // UI Labels and Titles
    'defaultPriceLabel' => trans("default_price"),
    'defaultQuantityLabel' => trans("default_quantity"),
    'optionTypeLabel' => trans("option_type"),
    'optionName' => trans("option_name"),
    'addValueButton' => trans("add_value"),
    'manageImagesModalTitle' => trans("manage_images"),
    'collapseOptionTooltip' => "Collapse",
    'expandOptionTooltip' => "Expand",
    'dragReorderOptionTitle' => "Drag to reorder option",
    'dragReorderValueTitle' => "Drag to reorder value",

    // Option Type Names
    'optGroupNameCreateVariants' => trans("create_variants"),
    'optGroupNameCreateExtraOptions' => trans("create_extra_options"),
    'optTypeNameRadioButtons' => trans("radio_buttons") . "&nbsp;(" . trans("single_select") . ")",
    'optTypeNameDropdown' => trans("dropdown") . "&nbsp;(" . trans("single_select") . ")",
    'optTypeNameSwatchColor' => trans("swatch_color") . "&nbsp;(" . trans("single_select") . ")",
    'optTypeNameSwatchImage' => trans("swatch_image") . "&nbsp;(" . trans("single_select") . ")",
    'optTypeNameCheckbox' => trans("checkbox") . "&nbsp;(" . trans("multi_select") . ")",
    'optTypeNameTextInput' => trans("text_input"),
    'optTypeNameNumberInput' => trans("number_input"),

    // Static Messages and Placeholders
    'noVariantsAddOptions' => trans("no_records_found"),
    'noVariantsGeneratedBase' => "No variants generated.",
    'errorImagePlaceholder' => "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",
    'variantNoImagePlaceholder' => '<span class="variant-image-preview no-image"><i class="fas fa-image fa-fw"></i></span>',
    'applyPriceButton' => '<i class="fa fa fa-check"></i>',
]; ?>

<script>
    var initialProductData = <?= $initialProductData_json; ?>;
    window.configOptions = <?= json_encode($jsOptionsConfig, JSON_UNESCAPED_SLASHES); ?>;
</script>

<script src="<?= base_url('assets/admin/js/product-options.js'); ?>"></script>

<script>
    $(document).on("click", "#add-option-btn", function () {
        const sku = $('#input_sku').val().trim();
        if (sku === '') {
            const generatedSku = generateUniqueString();
            $('#input_sku').val(generatedSku);
            $('#input_sku').trigger('input');
        }
    });
</script>

