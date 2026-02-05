<div class="product-price-container">
    <?php if ($product->is_free_product): ?>
        <div id="div-product-price" class="text-product-discounted">
            <span class="final-price final-price-free"><?= trans("free"); ?></span>
        </div>
    <?php elseif ($product->listing_type == 'ordinary_listing' && $product->is_sold): ?>
        <div id="div-product-price">
            <span class="final-price text-muted"><?= trans("sold"); ?></span>
        </div>
    <?php else:
        if (!empty($productPrice)):?>
            <div id="div-product-discounted-price" class="<?= $productDiscountRate > 0 ? 'text-product-discounted' : ''; ?>">
                <span class="final-price"><?= $productPriceDiscounted; ?></span>
            </div>
            <div id="div-product-price">
                <?php if ($productDiscountRate > 0): ?>
                    <span class="original-price"><?= $productPrice; ?></span>
                <?php endif; ?>
            </div>
            <div id="div-product-discount-rate">
                <?php if ($productDiscountRate > 0): ?>
                    <span class="discount-rate">-<?= discountRateFormat($productDiscountRate); ?></span>
                <?php endif; ?>
            </div>
        <?php endif;
    endif; ?>
</div>