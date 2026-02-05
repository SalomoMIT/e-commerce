<?php if (!empty($featuredCategories)):
    if ($generalSettings->fea_categories_design == 'grid_layout'): ?>
        <div class="col-12 section section-categories">
            <div class="featured-categories">
                <div class="card-columns">
                    <?php foreach ($featuredCategories as $category): ?>
                        <div class="card lazyload" data-bg="<?= getStorageFileUrl($category->image, $category->storage); ?>">
                            <a href="<?= generateCategoryUrl($category); ?>">
                                <div class="caption text-truncate">
                                    <span><?= esc($category->cat_name); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-12 section section-categories">
            <div class="section-header display-flex justify-content-between">
                <h3 class="title"><?= trans("shop_by_category"); ?></h3>
                <a href="<?= generateUrl('products'); ?>" class="font-600"><?= trans("view_all"); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                    </svg>
                </a>
            </div>
            <div class="featured-categories">
                <div class="row row-fa-cat-round">
                    <?php foreach ($featuredCategories as $category): ?>
                        <div class="col-4 col-sm-4 col-md-3 col-lg-2 col-fa-cat-round">
                            <div class="item-category-round">
                                <div class="ratio ratio-1x1 category-image">
                                    <a href="<?= generateCategoryUrl($category); ?>">
                                        <img data-src="<?= getStorageFileUrl($category->image, $category->storage); ?>" alt="<?= esc($category->cat_name); ?>" width="190" height="190" class="lazyload img-fluid">
                                    </a>
                                    <div class="overlay">
                                        <div class="text-shop-now"><?= trans("shop_now"); ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="category-name"><a href="<?= generateCategoryUrl($category); ?>"><?= esc($category->cat_name); ?></a></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif;
endif; ?>

