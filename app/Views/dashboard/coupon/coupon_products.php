<?php $currentPageLink = generateDashUrl('coupon_products') . '/' . $coupon->id;
$categoryIds = $coupon->category_ids;
$arraySelectedCategories = [];
if (!empty($categoryIds)) {
    $arraySelectedCategories = explode(',', $categoryIds);
} ?>
<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans("select_products"); ?><br><span class="text-muted" style="font-size: 13px;"><?= trans("coupon"); ?>:&nbsp;<?= esc($coupon->coupon_code); ?></span></h3>
    </div>
    <div class="col-md-12 col-lg-4">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans("categories"); ?></h3>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive" style="max-height: 925px; overflow-y: scroll">
                            <table class="table table-bordered table-striped table-products" role="grid">
                                <thead>
                                <tr role="row">
                                    <th><?= trans('category'); ?></th>
                                    <th class="max-width-120"><?= trans('select_for_coupon'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($sellerCategories)):
                                    foreach ($sellerCategories as $item): ?>
                                        <tr>
                                            <td>
                                                <?= esc($item['cat_name']); ?>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" value="<?= $item['id']; ?>" id="chCategory<?= $item['id']; ?>" class="custom-control-input checkbox-category" <?= in_array($item['id'], $arraySelectedCategories) ? 'checked' : ''; ?>>
                                                    <label for="chCategory<?= $item['id']; ?>" class="custom-control-label">&nbsp;</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($sellerCategories)): ?>
                            <p class="text-center">
                                <?= trans("no_records_found"); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-8">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans("products"); ?></h3>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row table-filter-container">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-default filter-toggle collapsed m-b-10" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false">
                                    <i class="fa fa-filter"></i>&nbsp;&nbsp;<?= trans("filter"); ?>
                                </button>
                                <div class="collapse navbar-collapse" id="collapseFilter">
                                    <form action="<?= $currentPageLink; ?>" method="get" id="formVendorProducts">
                                        <?php if (!empty(inputGet('st'))): ?>
                                            <input type="hidden" name="st" value="<?= strSlug(inputGet('st')); ?>">
                                        <?php endif; ?>
                                        <div class="item-table-filter">
                                            <label><?= trans('category'); ?></label>
                                            <select id="categories" name="category" class="form-control custom-select" onchange="getFilterSubCategoriesDashboard(this.value);">
                                                <option value=""><?= trans("all"); ?></option>
                                                <?php if (!empty($parentCategories)):
                                                    foreach ($parentCategories as $item): ?>
                                                        <option value="<?= $item->id; ?>" <?= inputGet('category', true) == $item->id ? 'selected' : ''; ?>><?= esc($item->cat_name); ?></option>
                                                    <?php endforeach;
                                                endif; ?>
                                            </select>
                                        </div>
                                        <div class="item-table-filter">
                                            <label class="control-label"><?= trans('subcategory'); ?></label>
                                            <select id="subcategories" name="subcategory" class="form-control custom-select">
                                                <option value=""><?= trans("all"); ?></option>
                                                <?php if (!empty(inputGet('category'))):
                                                    $subCategories = getSubCategories(inputGet('category'));
                                                    if (!empty($subCategories)):
                                                        foreach ($subCategories as $item):?>
                                                            <option value="<?= $item->id; ?>" <?= inputGet('subcategory', true) == $item->id ? 'selected' : ''; ?>><?= esc($item->cat_name); ?></option>
                                                        <?php endforeach;
                                                    endif;
                                                endif; ?>
                                            </select>
                                        </div>
                                        <div class="item-table-filter item-table-filter-large">
                                            <label><?= trans("search"); ?></label>
                                            <div class="item-table-filter-search">
                                                <input name="q" class="form-control" placeholder="<?= trans("search"); ?>" type="search" value="<?= esc(inputGet('q')); ?>">
                                                <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-products" role="grid">
                                <thead>
                                <tr role="row">
                                    <th width="20"><?= trans('id'); ?></th>
                                    <th><?= trans('product'); ?></th>
                                    <th><?= trans('category'); ?></th>
                                    <th class="max-width-120"><?= trans('select_for_coupon'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($products)):
                                    foreach ($products as $item): ?>
                                        <tr>
                                            <td><?= esc($item->id); ?></td>
                                            <td class="td-product">
                                                <a href="<?= generateProductUrl($item); ?>" target="_blank" class="table-product-title"><?= esc($item->title); ?></a>
                                            </td>
                                            <td>
                                                <?= esc($item->cat_name); ?>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" value="<?= $item->id; ?>" id="chProduct<?= $item->id; ?>" class="custom-control-input checkbox-product" <?= !empty($item->is_selected) ? 'checked' : ''; ?>>
                                                    <label for="chProduct<?= $item->id; ?>" class="custom-control-label">&nbsp;</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($products)): ?>
                            <p class="text-center">
                                <?= trans("no_records_found"); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?php if (!empty($products)): ?>
                            <div class="number-of-entries">
                                <span><?= trans("number_of_entries"); ?>:</span>&nbsp;&nbsp;<strong><?= $numRows; ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="table-pagination">
                            <?= $pager->links; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="couponModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="couponModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title text-center" id="couponModalLabel"><?= trans("please_wait"); ?></h4>
            </div>

            <div class="modal-body text-center">
                <p><?= trans("msg_processing_coupon_assignments"); ?></p>

                <div class="spinner"></div>
            </div>

        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        $('.checkbox-category').change(function () {
            var action = 'delete';
            var value = $(this).val();
            if ($(this).is(':checked')) {
                action = 'add';
            }
            var data = {
                'coupon_id': "<?= $coupon->id; ?>",
                'category_id': value,
                'action': action
            };
            $('#couponModal').modal('show');
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/selectCouponCategoryPost'),
                data: data,
                success: function (response) {
                    location.reload();
                },
                error: function() {
                    $('#couponModal').modal('hide');
                    alert('Something went wrong. Please try again.');
                }
            });
        });
        $('.checkbox-product').change(function () {
            var action = 'delete';
            var value = $(this).val();
            if ($(this).is(':checked')) {
                action = 'add';
            }
            var data = {
                'coupon_id': "<?= $coupon->id; ?>",
                'product_id': value,
                'action': action
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/selectCouponProductPost'),
                data: data,
                success: function (response) {
                }
            });
        });
    });
</script>

<style>
    .spinner {
        margin: 20px auto;
        width: 40px;
        height: 40px;
        border: 4px solid #ccc;
        border-top: 4px solid #428bca;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>