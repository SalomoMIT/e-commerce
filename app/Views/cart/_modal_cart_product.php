<div class="row align-items-center row-cart-product-modal">
    <div class="col-6 col-left">
        <div class="d-flex flex-row align-items-center">
            <div class="flex-item">
                <a href="<?= generateProductUrl($product); ?>">
                    <div class="product-image-box product-image-box-lg">
                        <img data-src="<?= getOrderImageUrl($cartItem->product_image_data, $product->id); ?>" alt="<?= esc($cartItem->product_title); ?>" class="lazyload img-fluid img-product">
                    </div>
                </a>
            </div>
            <div class="flex-item">
                <div class="details">
                    <a href="<?= generateProductUrl($product); ?>"><h4 class="title"><?= esc($cartItem->product_title); ?></h4></a>
                    <div class="price"><?= priceFormatted($cartItem->unit_price, $selectedCurrency->code); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-right">
        <div class="text-muted mb-2"><?= trans("product_cart_summary"); ?>:</div>
        <div class="d-flex justify-content-between mb-1">
            <strong><?= trans("quantity"); ?>:</strong>
            <strong><?= $cartItem->quantity; ?></strong>
        </div>
        <div class="d-flex justify-content-between mb-5">
            <strong><?= trans("subtotal"); ?>:</strong>
            <?php $total = numToDecimal($cartItem->unit_price * $cartItem->quantity); ?>
            <strong><?= priceFormatted($total, $selectedCurrency->code); ?></strong>
        </div>
        <a href="<?= generateUrl('cart'); ?>" class="btn btn-block btn-custom"><?= trans("view_cart"); ?></a>
        <?php if ($cartHasPhysicalProduct == true && $productSettings->marketplace_shipping == 1): ?>
            <a href="<?= generateUrl('cart', 'shipping'); ?>" class="btn btn-block btn-custom btn-custom-outline"><?= trans("checkout"); ?></a>
        <?php else: ?>
            <a href="<?= generateUrl('cart', 'payment_method'); ?>" class="btn btn-block btn-custom btn-custom-outline"><?= trans("checkout"); ?></a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <div class="row">
        <div class="col-12 cart-related-products">
            <h3 class="title"><?= trans("you_may_also_like"); ?></h3>
            <div class="row row-product">
                <?php $i = 0;
                foreach ($relatedProducts as $item):
                    if ($i <= 3):?>
                        <div class="col-6 col-sm-4 col-md-3 col-product">
                            <?= view('product/_product_item', ['product' => $item]); ?>
                        </div>
                    <?php endif;
                    $i++;
                endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

