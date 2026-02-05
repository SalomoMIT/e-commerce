<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-products breadcrumb-mobile-scroll">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= generateUrl('products'); ?>"><?= trans("products"); ?></a></li>
                        <?php if (!empty($parentCategoriesTree)):
                            foreach ($parentCategoriesTree as $item):
                                if ($item->id == $category->id):?>
                                    <li class="breadcrumb-item active"><?= esc($item->cat_name); ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item"><a href="<?= generateCategoryUrl($item); ?>"><?= esc($item->cat_name); ?></a></li>
                                <?php endif;
                            endforeach;
                        endif; ?>
                    </ol>
                </nav>
            </div>
        </div>

        <?php $search = cleanStr(inputGet('search'));
        if (!empty($search)):?>
            <input type="hidden" name="search" value="<?= esc($search); ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <?php if (!empty($category)): ?>
                    <h1 class="h1-title-nvs"><?= esc($category->cat_name); ?></h1>
                    <?php if ($category->show_description == 1 && !empty($description)): ?>
                        <p class="category-description"><?= esc($description); ?></p>
                    <?php endif;
                else: ?>
                    <h1 class="h1-title-nvs"><?= trans("products"); ?></h1>
                <?php endif; ?>
            </div>
        </div>

        <div class="container-products-page">
            <div class="row">

                <div class="col-12 m-b-20 container-filter-products-mobile">
                    <?= view('product/_product_filters_mobile'); ?>
                </div>

                <div class="col-12 col-md-3 col-sidebar-products">
                    <?= view('product/_product_filters'); ?>
                </div>

                <div class="col-12 col-md-9 col-content-products">
                    <?= view('product/_product_list'); ?>
                </div>

            </div>
        </div>
    </div>
</div>