<?php
$url = generateProductUrl($product);
$img = getProductItemImage($product);
$imgSecond = getProductItemImage($product, true);
$hasSecondImg = !empty($imgSecond) && $img !== $imgSecond;
$imgContainerClass = 'product-image-container' . ($hasSecondImg ? ' multi-image-container' : '');
?>
<div class="product-card">
    <div class="<?= $imgContainerClass; ?>">
        <?php if (!empty($product->discount_rate) && !empty($discountLabel)): ?>
            <div class="product-card-badge product-card-badge-red">-<?= esc($product->discount_rate); ?>%</div>
        <?php endif; ?>

        <?php if ($product->is_promoted && $generalSettings->promoted_products && !empty($promotedBadge)): ?>
            <span class="product-card-badge product-card-badge-green"><?= trans("featured"); ?></span>
        <?php endif; ?>

        <a href="<?= $url; ?>">
            <img data-src="<?= $img; ?>" class="lazyload img-fluid product-image default-image" width="242" height="242" alt="<?= esc($product->title); ?>">
            <?php if ($hasSecondImg): ?>
                <img data-src="<?= $imgSecond; ?>" class="lazyload img-fluid product-image hover-image" width="242" height="242" alt="<?= esc($product->title); ?>">
            <?php endif; ?>
        </a>

        <div class="product-actions-overlay">
            <?php if (($product->listing_type != 'ordinary_listing') && $product->is_free_product != 1):
                if (!empty($product->has_options) || $product->listing_type == 'bidding'):?>
                    <a href="<?= $url; ?>" class="action-btn cart-btn" data-toggle="tooltip" data-placement="left" data-product-id="<?= $product->id; ?>" data-reload="0" title="<?= trans("view_options"); ?>" aria-label="<?= trans("view_options"); ?>"><i class="icon-cart"></i></a>
                <?php else:
                    $itemUniqueID = uniqid();
                    if ($product->stock > 0):?>
                        <button type="button" id="btn_add_cart_<?= $itemUniqueID; ?>" class="action-btn cart-btn btn-item-add-to-cart" data-id="<?= $itemUniqueID; ?>" data-toggle="tooltip" data-placement="left" data-product-id="<?= $product->id; ?>" data-reload="0" title="<?= trans("add_to_cart"); ?>" aria-label="add-to-cart"><i class="icon-cart"></i></button>
                    <?php endif;
                endif;
            endif; ?>
            <button type="button" class="action-btn wishlist-btn btn-add-remove-wishlist" data-toggle="tooltip" data-placement="left" data-product-id="<?= $product->id; ?>" data-type="list" title="<?= trans("wishlist"); ?>" aria-label="add-remove-wishlist">
                <?php if (isProductInWishlist($product) == 1): ?>
                    <i class="icon-heart"></i>
                <?php else: ?>
                    <i class="icon-heart-o"></i>
                <?php endif; ?>
            </button>
        </div>
    </div>

    <div class="card-body">
        <h3 class="product-title"><a href="<?= $url; ?>"><?= esc($product->title); ?></a></h3>
        <div class="product-seller text-truncate">
            <a href="<?= generateProfileUrl($product->user_slug, true); ?>"><?= esc($product->user_username); ?></a>
        </div>
        <div class="product-rating">
            <?php if ($generalSettings->reviews): ?>
                <?= view('partials/_review_stars', ['rating' => $product->rating]); ?>
            <?php endif; ?>
            <span class="item-wishlist"><i class="icon-heart-o"></i>&nbsp;&nbsp;<?= numberFormatShort($product->wishlist_count); ?></span>
        </div>
        <div class="product-footer">
            <?= view('product/_price_product_item', ['product' => $product]); ?>
        </div>
    </div>
</div>