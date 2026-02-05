<div class="product-list-header">
    <div class="filter-reset-tag-container">
        <?php $showResetLink = false;

        $fieldNames = $customFiltersDisplayNames['fieldNames'] ?? [];
        $optionNames = $customFiltersDisplayNames['optionNames'] ?? [];

        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $valueString) {
                if (in_array($key, ['sort', 'page', 'p_cat']) || empty($valueString)) {
                    continue;
                }
                $showResetLink = true;
                $values = explode(',', $valueString);

                foreach ($values as $value) {
                    if (empty($value)) continue;

                    $title = '';
                    $displayValue = esc($value);

                    switch ($key) {
                        case 'p_min':
                            $title = trans("price") . ' (' . $selectedCurrency->symbol . ')';
                            $displayValue = trans("min") . ': ' . $displayValue;
                            break;
                        case 'p_max':
                            $title = trans("price") . ' (' . $selectedCurrency->symbol . ')';
                            $displayValue = trans("max") . ': ' . $displayValue;
                            break;
                        case 'search':
                            $title = trans("search");
                            break;
                        case 'brand':
                            $title = trans("brand");
                            $displayValue = !empty($brandNameArray[$value]) ? $brandNameArray[$value] : 'brand';
                            break;
                        default:
                            $title = $fieldNames[$key] ?? ucfirst($key);
                            $displayValue = $optionNames[$key . '_' . $value] ?? $displayValue;
                            break;
                    }

                    if (empty($displayValue)) continue; ?>
                    <div class="filter-reset-tag">
                        <div class="left">
                            <button type="button" class="btn-remove-active-product-filter" data-key="<?= esc($key); ?>" data-value="<?= esc($value); ?>" aria-label="remove filter <?= esc($key); ?>">
                                <i class="icon-close"></i>
                            </button>
                        </div>
                        <div class="right">
                            <span class="reset-tag-title"><?= $title; ?></span>
                            <span><?= esc($displayValue); ?></span>
                        </div>
                    </div>
                <?php }
            }
        }

        if ($showResetLink): ?>
            <a href="<?= current_url(); ?>" class="link-reset-filters" rel="nofollow"><?= trans("reset_filters"); ?></a>
        <?php endif; ?>
    </div>


    <div class="container-filter-products">
        <div class="product-sort-by">
            <?= view('product/_product_sort_dropdown'); ?>
        </div>
    </div>

</div>

<?php if (!empty($products) && getValidPageNumber(inputGet('page')) > 1): ?>
    <div class="row">
        <div class="col-12 text-center">
            <button type="button" id="btnShowPreviousProducts" class="btn btn-lg btn-show-previous-products">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                </svg>&nbsp;
                <?= trans("show_previous_products"); ?>
            </button>
        </div>
    </div>
<?php endif; ?>

<div id="productListContent" class="product-list-content" data-category="<?= !empty($category) ? $category->id : ''; ?>" data-has-more="<?= countItems($products) > $productSettings->pagination_per_page ? 1 : 0; ?>" data-user-id="<?= !empty($user) ? $user->id : ''; ?>" data-coupon-id="<?= !empty($coupon) ? $coupon->id : ''; ?>">
    <div id="productListResultContainer" class="row row-product">
        <?php if (!empty($products)):
            $i = 0;
            foreach ($products as $product):
                if ($i >= $productSettings->pagination_per_page) break;
                if ($i == 8):
                    echo view('partials/_ad_spaces', ['adSpace' => 'products_1', 'class' => 'mb-4']);
                endif; ?>
                <div class="col-6 col-sm-4 col-md-4 col-lg-3 col-product">
                    <?= view('product/_product_item', ['product' => $product, 'promotedBadge' => true]); ?>
                </div>
                <?php $i++; endforeach;
        else: ?>
            <div class="col-12">
                <p class="no-records-found"><?= trans("no_products_found"); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div id="loadProductsSpinner" class="col-12 load-more-spinner">
        <div class="row">
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/_ad_spaces', ['adSpace' => 'products_2', 'class' => 'mt-3']); ?>