<?php
$cssOutput = '';
$cssOutput .= ':root{--mds-color-main:' . esc($generalSettings->site_color) . ';--mds-object-fit-mode:' . ($generalSettings->product_img_display_mode == 'full_image' ? 'contain' : 'cover') . ';}';
$cssOutput .= '.logo{width:' . esc($baseVars->logoWidth) . 'px;height:' . esc($baseVars->logoHeight) . 'px;}';

if (!empty($indexBannersArray)) {
    foreach ($indexBannersArray as $bannerSet) {
        foreach ($bannerSet as $banner) {
            $cssOutput .= '.index_bn_' . $banner->id . '{-ms-flex:0 0 ' . $banner->banner_width . '%;flex:0 0 ' . $banner->banner_width . '%;max-width:' . $banner->banner_width . '%;}';
        }
    }
}

if (!empty($adSpaces)) {
    foreach ($adSpaces as $item) {
        if (!empty($item->desktop_width) && !empty($item->desktop_height)) {
            $cssOutput .= '.bn-ds-' . $item->id . '{width:' . $item->desktop_width . 'px;height:' . $item->desktop_height . 'px;}';
            $cssOutput .= '.bn-mb-' . $item->id . '{width:' . $item->mobile_width . 'px;height:' . $item->mobile_height . 'px;}';
        }
    }
}
$cssOutput .= '.product-card .price {white-space: nowrap;font-size: 0.938rem;}.product-card .discount-original-price {color: #868e96 !important;white-space: nowrap;font-size: 0.875rem !important;}.btn-cart-remove-mobile{display:none}@media (max-width:767px){.shopping-cart .item .list-item .product-title{line-height:24px}.shopping-cart .item{display:block!important;width:100%!important}.shopping-cart .item .cart-item-quantity{display:flex!important;width:100%!important;padding-left:80px;margin-top:10px;gap:15px}.product-image-box-md{height:70px;width:70px}.shopping-cart .number-spinner{height:40px;width:120px}.shopping-cart .number-spinner input{height:40px;padding:8px 4px!important}.shopping-cart .number-spinner button{height:40px;width:40px}.btn-cart-remove{display:none!important}.btn-cart-remove-mobile{padding:0;display:flex;align-items:center;justify-content:center;width:40px;height:40px;margin-top:0!important}.btn-cart-remove-mobile i{margin:0!important}.btn-cart-remove-mobile span{display:none}.product-delivery-est .item{margin-top:5px}[dir=rtl] .shopping-cart .item .cart-item-quantity{padding-left:0;padding-right:80px}}';
$cssOutput .= '.mega-menu .mega-menu-content,.mega-menu .dropdown-menu-large{max-height: 600px;overflow-y:auto !important;} .is-invalid-stars i {color: #EF4444 !important;} .btn-edit-product{display: inline-flex;align-items: center;justify-content: center;font-size: 10px;padding: 0;width: 20px;height: 20px;color: #666 !important;border-radius: 5px;position:relative;top:-1px;}.profile-actions-shipping a {padding: 8px 12px;}';
echo '<style>' . $cssOutput . '</style>';

$jsConfig = [
    'baseUrl' => base_url(),
    'langBaseUrl' => langBaseUrl(),
    'isloggedIn' => authCheck() ? 1 : 0,
    'sysLangId' => $activeLang->id,
    'langShort' => $activeLang->short_form,
    'decimalSeparator' => $baseVars->decimalSeparator,
    'csrfTokenName' => csrf_token(),
    'chatUpdateTime' => (int)CHAT_UPDATE_TIME,
    'reviewsLoadLimit' => (int)REVIEWS_LOAD_LIMIT,
    'commentsLoadLimit' => (int)COMMENTS_LOAD_LIMIT,
    'cartRoute' => !empty($this->routes) && !empty($this->routes->cart) ? $this->routes->cart : '',
    'sliderFadeEffect' => $generalSettings->slider_effect == 'fade' ? 1 : 0,
    'indexProductsPerRow' => (int)$generalSettings->index_products_per_row,
    'isTurnstileEnabled' => !empty($generalSettings->turnstile_status),
    'rtl' => (bool)$baseVars->rtl,
    'text' => [
        'viewAll' => esc(trans("view_all")),
        'noResultsFound' => esc(trans("no_results_found")),
        'ok' => esc(trans("ok")),
        'cancel' => esc(trans("cancel")),
        'acceptTerms' => esc(trans("msg_accept_terms")),
        'addToCart' => esc(trans("add_to_cart")),
        'addedToCart' => esc(trans("added_to_cart")),
        'copyLink' => esc(trans("copy_link")),
        'copied' => esc(trans("copied")),
        'addToWishlist' => esc(trans("add_to_wishlist")),
        'removeFromWishlist' => esc(trans("remove_from_wishlist")),
        'processing' => esc(trans("processing")),
    ]
]; ?>

<script>window.MdsConfig = <?= json_encode($jsConfig, JSON_UNESCAPED_SLASHES); ?>;</script>