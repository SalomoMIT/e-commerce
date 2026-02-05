<div class="btn-filter-products-mobile">
    <button class="btn btn-md" type="button" data-toggle="collapse" data-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#666666" viewBox="0 0 256 256">
            <path d="M200,136a8,8,0,0,1-8,8H64a8,8,0,0,1,0-16H192A8,8,0,0,1,200,136Zm32-56H24a8,8,0,0,0,0,16H232a8,8,0,0,0,0-16Zm-80,96H104a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16Z"></path>
        </svg>
        &nbsp;&nbsp;<span class="text"><?= trans("filter_products"); ?></span>
    </button>
</div>
<div class="product-sort-by">
    <?= view('product/_product_sort_dropdown'); ?>
</div>