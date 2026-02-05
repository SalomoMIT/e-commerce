<div class="sticky-lg-top hidden-scrollbar product-filters-container">
    <div id="collapseFilters" class="product-filters">

        <!-- Category Filter -->
        <?php if (!empty($category) || !empty($categories)): ?>
            <div class="filter-item filter-item-categories p-t-0">
                <h4 class="title"><?= trans("category"); ?></h4>
                <?php
                $isProfilePage = $isProfilePage ?? false;
                $backUrl = '';

                if (!empty($category)) {
                    if ($isProfilePage) {
                        if (!empty($parentCategory)) {
                            $backUrl = generateProfileFilterUrl(['p_cat' => $parentCategory->id]) . '#products';
                        } else {
                            $backUrl = generateProfileFilterUrl([], ['p_cat']) . '#products';
                        }
                    } else {
                        if (!empty($parentCategory)) {
                            $path = $parentCategory->parent_id == 0 ? $parentCategory->slug : $parentCategory->parent_slug . '/' . $parentCategory->slug;
                            $backUrl = generateUrlWithExistingParams($path);
                        } else {
                            $backUrl = generateUrlWithExistingParams(getRoute('products'));
                        }
                    }
                }

                if (!empty($backUrl)):?>
                    <a href="<?= esc($backUrl); ?>" class="filter-list-categories-parent">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-left-short" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"/>
                        </svg>
                        <span><?= esc($category->cat_name); ?></span>
                    </a>
                <?php endif;

                if (countItems($categories) > 0): ?>
                    <div class="filter-list-container">
                        <ul class="filter-list mds-scrollbar<?= !empty($category) ? ' filter-list-subcategories' : ' filter-list-categories'; ?>">
                            <?php foreach ($categories as $item):
                                if (!empty($category) && $category->id == $item->id) {
                                    continue;
                                }

                                $link = '';
                                if ($isProfilePage) {
                                    $link = generateProfileFilterUrl(['p_cat' => $item->id]) . '#products';
                                } else {
                                    $categoryPath = $item->parent_id == 0 ? $item->slug : $item->parent_slug . '/' . $item->slug;
                                    $link = generateUrlWithExistingParams($categoryPath);
                                } ?>
                                <li><a href="<?= $link; ?>"><?= esc($item->cat_name); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- Custom Field Filters -->
        <?php
        $filterCollapseCount = 0;
        $ajaxLoadLimit = CUSTOM_FILTERS_LOAD_LIMIT;

        if (!empty($customFilters)):
            foreach ($customFilters as $customFilter):
                $initialOptionCount = countItems($customFilter->options);
                if ($initialOptionCount > 0):
                    $filterName = $customFilter->id == 'brand' ? trans("brand") : $customFilter->field_name;
                    $collapseId = 'filter_' . $customFilter->id;
                    $isCollapsed = $filterCollapseCount >= CUSTOM_FILTERS_COLLAPSE_LIMIT && empty(inputGet($customFilter->product_filter_key)); ?>
                    <div class="filter-item">
                        <div class="collapse-title">
                            <button class="btn <?= $isCollapsed ? 'collapsed' : ''; ?>" type="button" data-toggle="collapse" data-target="#<?= $collapseId; ?>"><?= esc($filterName); ?></button>
                        </div>
                        <div id="<?= $collapseId; ?>" class="filter-list-container collapse <?= !$isCollapsed ? 'show' : ''; ?>">
                            <?php if ($initialOptionCount > 11): ?>
                                <input type="text" class="form-control filter-search-input" placeholder="<?= trans("search") . ' ' . esc($filterName); ?>" data-target-list="#product_filter_<?= $customFilter->id; ?>">
                            <?php endif; ?>

                            <ul id="product_filter_<?= $customFilter->id; ?>" class="filter-list filter-options-list mds-scrollbar"
                                data-filter-type="custom"
                                data-filter-id="<?= $customFilter->id; ?>"
                                data-category-id="<?= !empty($category) ? $category->id : ''; ?>"
                                data-offset="<?= $initialOptionCount; ?>"
                                data-limit="<?= $ajaxLoadLimit; ?>"
                                data-loading="false"
                                data-has-more="<?= $customFilter->has_more_options ? 'true' : 'false'; ?>"
                                data-behavior="<?= ($initialOptionCount < $ajaxLoadLimit) ? 'local' : 'ajax'; ?>">

                                <?php foreach ($customFilter->options as $option):
                                    $optionKey = $customFilter->id == 'brand' ? $option->id : $option->option_key;
                                    $optionName = $customFilter->id == 'brand' ? $option->brand_name : $option->name; ?>
                                    <li data-key="<?= esc($customFilter->product_filter_key); ?>" data-value="<?= esc($optionKey); ?>">
                                        <div class="custom-control custom-checkbox custom-checkbox-sm">
                                            <input type="checkbox" class="custom-control-input" <?= isFilterOptionSelected($customFilter->product_filter_key, $optionKey) ? 'checked' : ''; ?> readonly>
                                            <label class="custom-control-label"><?= esc($optionName); ?></label>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php $filterCollapseCount++; endif;
            endforeach;
        endif; ?>


        <!-- Price Range Filter -->
        <?php if ($generalSettings->marketplace_system == 1 || $generalSettings->bidding_system == 1 || $productSettings->classified_price == 1):
            $filterPmin = esc(inputGet('p_min'));
            $filterPmax = esc(inputGet('p_max')); ?>
            <div class="filter-item border-0">
                <h4 class="title"><?= trans("price"); ?></h4>
                <div class="d-flex justify-content-between inputs-filter-price">
                    <input type="number" id="price_min" value="<?= $filterPmin; ?>" class="min-price form-control form-input" placeholder="<?= trans("min"); ?>" min="0" step="0.01">
                    <span>-</span>
                    <input type="number" id="price_max" value="<?= $filterPmax; ?>" class="max-price form-control form-input" placeholder="<?= trans("max"); ?>" min="0" step="0.01">
                </div>
            </div>
        <?php endif; ?>

        <!-- Keyword Filter -->
        <div class="filter-item m-b-0">
            <h4 class="title"><?= trans("filter_by_keyword"); ?></h4>
            <input type="text" id="input_filter_keyword" value="<?= esc(removeSpecialCharacters(urldecode(inputGet('search') ?? ''))); ?>" class="form-control form-input" placeholder="<?= trans("keyword"); ?>" maxlength="255">
            <button type="button" id="btnFilterByKeyword" class="btn btn-md btn-filter-product"><i class="icon-search"></i>&nbsp;<?= trans("filter"); ?></button>
        </div>
    </div>

    <div class="row-custom">
        <?= view('partials/_ad_spaces', ['adSpace' => 'products_sidebar', 'class' => 'm-b-15']); ?>
    </div>
</div>
