<?php
$listingType = $product->listing_type;
$productType = $product->product_type;

// Default values
$formAction = '';
$formId = '';
$formButtonHtml = '';
$needsFormWrapper = false;

// Determine stock and disabled status
$isSold = $product->is_sold == 1;
$isOutOfStock = $productStock <= 0;
$disabledAttr = $isOutOfStock ? ' disabled' : '';

if ($listingType == 'sell_on_site' || $listingType == 'license_key') {
    $needsFormWrapper = true;
    if ($productType == 'digital' && $product->is_free_product == 1) {
        $formAction = base_url('download-free-digital-file-post');
    } else {
        $formAction = base_url('add-to-cart');
        $formId = 'form-add-to-cart';
        if (!$isSold) {
            $formButtonHtml = '<button id="add-to-cart-button" class="btn btn-md btn-custom btn-product-cart"' . $disabledAttr . '><span class="btn-cart-icon"><i class="icon-cart-solid"></i></span>' . trans("add_to_cart") . '</button>';
        }
    }
} elseif ($listingType == 'bidding') {
    $needsFormWrapper = true;
    $formAction = base_url('bidding/request-quote-post');
    $formId = 'form_request_quote';
    if (!$isSold) {
        if (!authCheck()) {
            $formButtonHtml = '<button type="button" data-toggle="modal" data-target="#loginModal" class="btn btn-md btn-custom btn-product-cart"' . $disabledAttr . '><span class="btn-cart-icon"><i class="icon-tag"></i></span>' . trans("request_a_quote") . '</button>';
        } else {
            $formButtonHtml = '<button id="add-to-cart-button" class="btn btn-md btn-custom btn-product-cart"' . $disabledAttr . '><span class="btn-cart-icon"><i class="icon-tag"></i></span>' . trans("request_a_quote") . '</button>';
        }
    }
} else { // 'ordinary_listing'
    $needsFormWrapper = false;
    if (!$isSold && !empty($product->external_link)) {
        $formButtonHtml = '<a href="' . $product->external_link . '" class="btn btn-md btn-custom btn-product-cart" target="_blank" rel="nofollow">' . trans("buy_now") . '</a>';
    } else if (!$isSold) {
        if ($showVendorContactInfo) {
            $formButtonHtml = '<button type="button" class="btn btn-md btn-custom btn-product-cart" data-toggle="modal" data-target="#messageModal">' . trans("contact_seller") . '</button>';
        } else {
            $formButtonHtml = '<button type="button" class="btn btn-md btn-custom btn-product-cart" data-toggle="modal" data-target="#loginModal">' . trans("contact_seller") . '</button>';
        }
    }
}

if ($needsFormWrapper): ?>
    <form action="<?= $formAction; ?>" method="post" <?= !empty($formId) ? 'id="' . $formId . '"' : ''; ?>>
    <?= csrf_field(); ?>
    <input type="hidden" name="product_id" value="<?= $product->id; ?>">
<?php endif; ?>

    <div class="row">
        <div class="col-12">
            <?= view('product/details/_product_options', ['initialProductData_json' => $initialProductData_json]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12"><?= view('product/details/_messages'); ?></div>
    </div>
    <div class="row">
        <div class="col-12 product-add-to-cart-container">
            <?php if (!$isSold && $listingType != 'ordinary_listing' && $productType != 'digital'): ?>
                <div class="number-spinner">
                    <div class="input-group">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-spinner-minus" data-dir="dwn">-</button>
                    </span>
                        <input type="text" id="input_product_quantity" class="form-control text-center" name="product_quantity" value="1" aria-label="Product quantity" min="1" max="<?= clrNum($productStock); ?>">
                        <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-spinner-plus" data-dir="up">+</button>
                    </span>
                    </div>
                </div>
            <?php endif;

            if (!empty($formButtonHtml)):?>
                <div class="button-container">
                    <?= $formButtonHtml; ?>
                </div>
            <?php endif; ?>

            <?php if ($productType == 'digital' && $product->is_free_product == 1):
                if (authCheck()):
                    if (!empty($product->digital_file_download_link)): ?>
                        <div class="button-container">
                            <a href="<?= esc($product->digital_file_download_link); ?>" class="btn btn-md btn-custom btn-product-cart" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                </svg>&nbsp;&nbsp;<?= trans("download") ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="button-container">
                            <button type="submit" class="btn btn-md btn-custom btn-product-cart">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                </svg>&nbsp;&nbsp;<?= trans("download") ?>
                            </button>
                        </div>
                    <?php endif;
                else: ?>
                    <div class="button-container">
                        <button type="button" class="btn btn-md btn-custom btn-product-cart" data-toggle="modal" data-target="#loginModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                            </svg>&nbsp;&nbsp;<?= trans("download") ?>
                        </button>
                    </div>
                <?php endif;
            endif; ?>

            <div class="button-container button-container-wishlist">
                <?php if ($isProductInWishlist == 1): ?>
                    <button type="button" class="button-link btn-wishlist btn-add-remove-wishlist" data-product-id="<?= $product->id; ?>" data-type="details"><i class="icon-heart" aria-label="add-remove-wishlist"></i><span><?= trans("remove_from_wishlist"); ?></span></button>
                <?php else: ?>
                    <button type="button" class="button-link btn-wishlist btn-add-remove-wishlist" data-product-id="<?= $product->id; ?>" data-type="details"><i class="icon-heart-o" aria-label="add-remove-wishlist"></i><span><?= trans("add_to_wishlist"); ?></span></button>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($product->demo_url)): ?>
            <div class="col-12 product-add-to-cart-container">
                <div class="button-container">
                    <a href="<?= $product->demo_url; ?>" target="_blank" class="btn btn-md btn-live-preview"><i class="icon-preview"></i><?= trans("live_preview") ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php if ($needsFormWrapper): ?>
    </form>
<?php endif; ?>