<?php if (!empty($indexCategories) && !empty($categoriesProductsArray)):
    foreach ($indexCategories as $category):
        $numItems = !empty($categoriesProductsArray[$category->id]) ? countItems($categoriesProductsArray[$category->id]) : 0; ?>
        <div class="col-12 section section-category-products">

            <div class="section-header display-flex justify-content-between">
                <h3 class="title"><a href="<?= generateCategoryUrl($category); ?>"><?= esc($category->cat_name); ?></a></h3>
                <a href="<?= generateCategoryUrl($category); ?>" class="font-600"><?= trans("view_all"); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                    </svg>
                </a>
            </div>

            <div class="swiper swiper-carousel swiper-carousel-product" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>
                <div class="swiper-wrapper">
                    <?php if (!empty($categoriesProductsArray[$category->id])):
                        foreach ($categoriesProductsArray[$category->id] as $product): ?>
                            <div class="swiper-slide <?= $generalSettings->index_products_per_row == 5 ? 'swiper-col-product-5' : 'swiper-col-product-6'; ?>">
                                <?= view('product/_product_item', ['product' => $product, 'promotedBadge' => false, 'discountLabel' => 0]); ?>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>

                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    <?php endforeach;
endif; ?>