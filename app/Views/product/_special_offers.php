<?php if (!empty($specialOffers)): ?>
    <div class="col-12 section">
        <div class="section-header">
            <h3 class="title"><?= trans("special_offers"); ?></h3>
        </div>
        <div class="swiper swiper-carousel swiper-carousel-product" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>

            <div class="swiper-wrapper">
                <?php foreach ($specialOffers as $product): ?>
                    <div class="swiper-slide <?= $generalSettings->index_products_per_row == 5 ? 'swiper-col-product-5' : 'swiper-col-product-6'; ?>">
                        <?= view('product/_product_item', ['product' => $product, 'promotedBadge' => false, 'discountLabel' => 1]); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

    </div>
<?php endif; ?>