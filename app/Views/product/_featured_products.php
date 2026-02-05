<div id="promoted_posts">
    <div class="section-header">
        <h3 class="title"><?= trans("featured_products"); ?></h3>
    </div>
    <div id="row_promoted_products" class="row row-product">
        <?php $i = 0;
        if (!empty($promotedProducts)):
            foreach ($promotedProducts as $product):
                if ($i < $generalSettings->index_promoted_products_count):?>
                    <div class="col-6 col-sm-4 col-md-3 col-product <?= $generalSettings->index_products_per_row == 5 ? 'col-product-5' : 'col-product-6'; ?>">
                        <?= view('product/_product_item', ['product' => $product, 'promotedBadge' => false, 'discountLabel' => 0]); ?>
                    </div>
                <?php endif;
                $i++;
            endforeach;
        endif; ?>
    </div>
    <?php if ($i > $generalSettings->index_promoted_products_count): ?>
        <div id="load_promoted_spinner" class="col-12 load-more-spinner">
            <div class="row">
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>
        </div>
        <div class="row-custom text-center promoted-load-more-container">
            <button type="button" class="button-link link-see-more" onclick="loadMorePromotedProducts();" aria-label="load-more-promoted"><span><?= trans("load_more"); ?>&nbsp;<i class="icon-arrow-down"></i></span></button>
        </div>
    <?php endif; ?>
</div>