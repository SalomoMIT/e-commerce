<?= view('partials/_main_slider'); ?>

<div id="wrapper" class="index-wrapper">
    <div class="container">
        <div class="row">
            <h1 class="h1-title-nvs"><?= esc($baseSettings->site_title); ?></h1>
            <?php if (countItems($featuredCategories) > 0 && $generalSettings->featured_categories == 1):
                echo view('partials/_featured_categories');
            endif;
            echo view('product/_index_banners', ['bannerLocation' => 'featured_categories']);
            echo view('partials/_ad_spaces', ['adSpace' => 'index_1', 'class' => 'mb-3']);
            echo view('product/_special_offers', ['specialOffers' => $specialOffers]);
            echo view("product/_index_banners", ['bannerLocation' => 'special_offers']);
            if ($generalSettings->index_promoted_products == 1 && $generalSettings->promoted_products == 1 && !empty($promotedProducts)): ?>
                <div class="col-12 section section-promoted">
                    <?= view('product/_featured_products'); ?>
                </div>
            <?php endif;
            echo view('product/_index_banners', ['bannerLocation' => 'featured_products']);
            if ($generalSettings->index_latest_products == 1 && !empty($latestProducts)): ?>
                <div class="col-12 section section-latest-products">
                    <div class="section-header display-flex justify-content-between">
                        <h3 class="title"><a href="<?= generateUrl('products'); ?>"><?= trans("new_arrivals"); ?></a></h3>
                        <a href="<?= generateUrl('products'); ?>" class="font-600"><?= trans("view_all"); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                            </svg>
                        </a>
                    </div>
                    <div class="row row-product">
                        <?php foreach ($latestProducts as $item): ?>
                            <div class="col-6 col-sm-4 col-md-3 col-product <?= $generalSettings->index_products_per_row == 5 ? 'col-product-5' : 'col-product-6'; ?>">
                                <?= view('product/_product_item', ['product' => $item, 'promotedBadge' => false, 'discountLabel' => 0]); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif;
            echo view('product/_index_banners', ['bannerLocation' => 'new_arrivals']);
            echo renderIndexCategoryProducts($indexCategories, $activeLang->id);
            echo view('partials/_ad_spaces', ['adSpace' => 'index_2', 'class' => 'mb-3']); ?>

            <?php if ($productSettings->brand_status == 1 && !empty($brands) && !empty($brands['brands'])): ?>
                <div class="col-12 section">
                    <div class="section-header">
                        <h3 class="title"><?= trans("shop_by_brand"); ?></h3>
                    </div>
                    <div class="swiper swiper-carousel swiper-carousel-brand" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>
                        <div class="swiper-wrapper">
                            <?php foreach ($brands['brands'] as $item):
                                if (!empty($item->image_path)):?>
                                    <div class="swiper-slide">
                                        <a href="<?= generateUrl('products'); ?>?brand=<?= $item->id; ?>">
                                            <div class="brand-item">
                                                <div class="item">
                                                    <img data-src="<?= getStorageFileUrl($item->image_path, $item->storage); ?>" class="lazyload" alt="<?= esc($item->brand_name); ?>" width="112" height="52"/>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        </div>

                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?= view('blog/_blog_slider'); ?>
        </div>
    </div>
</div>
