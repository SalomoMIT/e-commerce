<div class="row">
    <div class="col-12">
        <?php if ($product->product_type == 'digital'): ?>
            <label class="badge badge-success-light badge-instant-download">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                </svg>&nbsp;&nbsp;<?= trans("instant_download"); ?>
            </label>
        <?php endif; ?>
        <h1 class="product-title">
            <?= esc($title); ?>
            <?php if (authCheck() && user()->id == $product->user_id): ?>
                <a href="<?= generateDashUrl('edit_product') . '/' . $product->id; ?>" class="btn btn-default btn-edit-product"><i class="icon-edit m-0"></i></a>
            <?php endif; ?>
        </h1>
        <?php if ($product->status == 0): ?>
            <label class="badge badge-warning badge-product-status"><?= trans("pending"); ?></label>
        <?php elseif ($product->visibility == 0): ?>
            <label class="badge badge-danger badge-product-status"><?= trans("hidden"); ?></label>
        <?php endif; ?>

        <div class="row-custom meta">
            <div class="d-flex justify-content-between align-items-center flex-wrap product-meta-info">
                <div class="d-flex align-items-center flex-wrap">
                    <span><?= trans("seller"); ?>:&nbsp;<a href="<?= generateProfileUrl($product->user_slug); ?>"><?= characterLimiter(esc($product->user_username), 30, '..'); ?></a></span>
                    <span class="mx-2">|</span>
                    <?php if ($generalSettings->reviews == 1): ?>
                        <div class="product-details-review">
                            <?= view('partials/_review_stars', ['rating' => $product->rating]); ?>
                            <?php if ($product->rating > 0): ?>
                                <button type="button" id="btnGoToReviews" class="button-link review-text" aria-label="go-to-reviews"><?= trans("reviews"); ?>&nbsp;(<?= numberFormatShort($reviewsCount); ?>)</button>
                            <?php else: ?>
                                <span class="review-text"><?= trans("reviews"); ?>&nbsp;(<?= numberFormatShort($reviewsCount); ?>)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center product-analytics">
                    <?php if ($generalSettings->product_comments == 1): ?>
                        <span><i class="icon-comment"></i>&nbsp;<?= esc($commentsCount); ?></span>
                    <?php endif; ?>
                    <span><i class="icon-heart"></i>&nbsp;<?= numberFormatShort($wishlistCount); ?></span>
                    <span><i class="icon-eye"></i>&nbsp;<?= numberFormatShort($product->pageviews); ?></span>
                </div>
            </div>
        </div>

        <div class="row-custom">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div class="flex-item">
                    <?= view('product/details/_price', ['product' => $product, 'price' => $product->price, 'priceDiscounted' => $product->price_discounted, 'discountRate' => $product->discount_rate]); ?>
                </div>

                <div class="flex-item">
                    <?php $showVendorContactInfo = false;
                    if (authCheck() || !empty($generalSettings->show_vendor_contact_info_guests)) {
                        $showVendorContactInfo = true;
                    }
                    $showAsk = true;
                    if ($product->listing_type == 'ordinary_listing' && empty($product->external_link)):
                        $showAsk = false;
                    endif;
                    if ($showAsk == true): ?>
                        <?php if ($showVendorContactInfo): ?>
                            <button class="btn btn-contact-seller" data-toggle="modal" data-target="#messageModal"><i class="icon-envelope"></i> <?= trans("ask_question") ?></button>
                        <?php else: ?>
                            <button class="btn btn-contact-seller" data-toggle="modal" data-target="#loginModal"><i class="icon-envelope"></i> <?= trans("ask_question") ?></button>
                        <?php endif;
                    endif; ?>
                </div>
            </div>
        </div>

        <div class="row-custom details">
            <div class="item-details<?= $product->listing_type == 'ordinary_listing' || $product->product_type == 'digital' ? ' hidden' : ''; ?>">
                <div class="left">
                    <label><?= trans("status"); ?></label>
                </div>
                <div class="right">
                    <span id="span-product-stock-status" class="status-in-stock <?= $productStock > 0 ? 'text-success' : 'text-danger'; ?>"><?= $productStock > 0 ? trans("in_stock") : trans("out_of_stock"); ?></span>
                </div>
            </div>
            <?php if ($productSettings->marketplace_sku == 1 && !empty($product->sku)): ?>
                <div class="item-details">
                    <div class="left">
                        <label><?= trans("sku"); ?></label>
                    </div>
                    <div class="right">
                        <span id="product-sku"><?= esc($productSku); ?></span>
                    </div>
                </div>
            <?php endif;
            if ($product->product_type == 'digital' && !empty($product->files_included)): ?>
                <div class="item-details">
                    <div class="left">
                        <label><?= trans("files_included"); ?></label>
                    </div>
                    <div class="right">
                        <span><?= esc($product->files_included); ?></span>
                    </div>
                </div>
            <?php endif;
            if ($product->listing_type == 'ordinary_listing'): ?>
                <div class="item-details">
                    <div class="left">
                        <label><?= trans("uploaded"); ?></label>
                    </div>
                    <div class="right">
                        <span><?= timeAgo($product->created_at); ?></span>
                    </div>
                </div>
            <?php endif;
            if (!empty($productCustomFieldsValues) && !empty($productCustomFieldsValues['top']) && countItems($productCustomFieldsValues['top']) > 0):
                foreach ($productCustomFieldsValues['top'] as $item):?>
                    <div class="item-details">
                        <div class="left">
                            <label><?= esc($item['name']); ?></label>
                        </div>
                        <div class="right">
                            <span><?= esc($item['value']); ?></span>
                        </div>
                    </div>
                <?php endforeach;
            endif; ?>
        </div>
    </div>
</div>

<?php echo view('product/details/_product_details_form', [
    'product' => $product,
    'productStock' => $productStock,
    'initialProductData_json' => $initialProductData_json,
    'isProductInWishlist' => $isProductInWishlist,
    'showVendorContactInfo' => $showVendorContactInfo
]); ?>

<?php if (!empty($digitalSale) && $product->is_free_product != 1): ?>
    <div class="row">
        <div class="col-12 product-already-purchased text-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check-fill" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5v-.5zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0zm-.646 5.354a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
            </svg>&nbsp;<?= trans("msg_product_already_purchased") ?>&nbsp;
            <?php if (!empty($product->digital_file_download_link)): ?>
                <a href="<?= esc($product->digital_file_download_link); ?>" class="text-success" target="_blank">
                    <?= trans("download"); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </a>
            <?php else: ?>
                <form action="<?= base_url('download-purchased-digital-file-post'); ?>" method="post" class="d-inline-block">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="sale_id" value="<?= $digitalSale->id; ?>">
                    <button type="submit" name="submit" value="<?= $product->listing_type == 'license_key' ? 'license_certificate' : 'main_files'; ?>" class="btn-product-download">
                        <?= trans("download"); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="product-delivery-est">
    <?php if ($shippingStatus == 1):
        if (!empty($deliveryTime)): ?>
            <div class="item">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32">
                        <path fill="#7c818b" d="M0 6v2h19v15h-6.156c-.446-1.719-1.992-3-3.844-3s-3.398 1.281-3.844 3H4v-5H2v7h3.156c.446 1.719 1.992 3 3.844 3s3.398-1.281 3.844-3h8.312c.446 1.719 1.992 3 3.844 3s3.398-1.281 3.844-3H32v-8.156l-.063-.157l-2-6L29.72 10H21V6zm1 4v2h9v-2zm20 2h7.281L30 17.125V23h-1.156c-.446-1.719-1.992-3-3.844-3s-3.398 1.281-3.844 3H21zM2 14v2h6v-2zm7 8c1.117 0 2 .883 2 2s-.883 2-2 2s-2-.883-2-2s.883-2 2-2m16 0c1.117 0 2 .883 2 2s-.883 2-2 2s-2-.883-2-2s.883-2 2-2"/>
                    </svg>&nbsp;&nbsp;<span><?= @parseSerializedOptionArray($deliveryTime->option_array, selectedLangId()); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <div class="item">
            <div class="title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 32 32">
                    <path fill="#7c818b" d="M16 4C9.383 4 4 9.383 4 16s5.383 12 12 12s12-5.383 12-12S22.617 4 16 4m0 2c5.535 0 10 4.465 10 10s-4.465 10-10 10S6 21.535 6 16S10.465 6 16 6m-1 2v9h7v-2h-5V8z"/>
                </svg>&nbsp;&nbsp;<span><?= trans("estimated_delivery"); ?>:</span>
            </div>&nbsp;
            <?php $estLocation = getEstimatedDeliveryLocation();
            if (!empty($estLocation)): ?>
                <div class="display-flex align-items-center flex-wrap">
                    <?= $estimatedDelivery; ?>
                    <button type="button" data-toggle="modal" data-target="#locationModal" class="nav-link btn-modal-location button-link btn-modal-location-product" aria-label="location-modal">
                        <div class="badge badge-info-light">
                            <?= esc($estLocation); ?>&nbsp;<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="#15a0b6" viewBox="0 0 256 256">
                                <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                            </svg>
                        </div>
                    </button>
                </div>
            <?php else: ?>
                <button type="button" data-toggle="modal" data-target="#locationModal" class="nav-link btn-modal-location button-link link-underlined btn-modal-location-product" aria-label="location-modal"><?= trans("select_location") ?></button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="item">
        <strong><?= trans("share"); ?>:</strong>&nbsp;<?= view("product/details/_product_share"); ?>
    </div>
</div>