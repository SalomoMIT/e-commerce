<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-products breadcrumb-mobile-scroll">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <?php if (!empty($parentCategoriesTree)):
                            foreach ($parentCategoriesTree as $item):?>
                                <li class="breadcrumb-item"><a href="<?= generateCategoryUrl($item); ?>"><?= esc($item->cat_name); ?></a></li>
                            <?php endforeach;
                        endif; ?>
                        <li class="breadcrumb-item active"><?= esc($title); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="col-12">
                <div class="product-details-container <?= (!empty($video) || !empty($audio)) && countItems($productImages) < 2 ? 'product-details-container-digital' : ''; ?>">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-lg-6 col-product-details-left">
                            <div id="product_slider_container">
                                <?= view("product/details/_preview"); ?>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-lg-6 col-product-details-right">
                            <div id="response_product_details" class="product-content-details">
                                <?= view("product/details/_product_details"); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div id="product_description_content" class="product-description post-text-responsive">
                            <?php $session = session();
                            $isReviewTabActive = false;
                            if (!empty($session->getFlashdata('review_added'))) {
                                $isReviewTabActive = true;
                            } ?>
                            <ul class="nav nav-tabs nav-tabs-horizontal">
                                <li class="nav-item">
                                    <a class="nav-link <?= $isReviewTabActive == true ? '' : 'active'; ?>" id="tab_description" data-toggle="tab" href="#tab_description_content"><?= trans("description"); ?></a>
                                </li>
                                <?php if (!empty($productCustomFieldsValues) && !empty($productCustomFieldsValues['bottom']) && countItems($productCustomFieldsValues['bottom']) > 0): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab_additional_information" data-toggle="tab" href="#tab_additional_information_content"><?= trans("additional_information"); ?></a>
                                    </li>
                                <?php endif;
                                if ($shippingStatus == 1 || $productLocationStatus == 1): ?>
                                    <li class="nav-item">
                                        <?php if ($shippingStatus == 1 && $productLocationStatus != 1): ?>
                                            <a class="nav-link" id="tab_shipping" data-toggle="tab" href="#tab_shipping_content"><?= trans("shipping"); ?></a>
                                        <?php elseif ($shippingStatus != 1 && $productLocationStatus == 1): ?>
                                            <a class="nav-link" id="tab_shipping" data-toggle="tab" href="#tab_shipping_content" onclick="loadProductShopLocationMap();"><?= trans("location"); ?></a>
                                        <?php else: ?>
                                            <a class="nav-link" id="tab_shipping" data-toggle="tab" href="#tab_shipping_content" onclick="loadProductShopLocationMap();"><?= trans("shipping_location"); ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endif;
                                if ($generalSettings->reviews == 1): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $isReviewTabActive == true ? 'active' : ''; ?>" id="tab_reviews" data-toggle="tab" href="#tab_reviews_content"><?= trans("reviews"); ?>&nbsp;(<?= $reviewsCount; ?>)</a>
                                    </li>
                                <?php endif;
                                if ($generalSettings->product_comments == 1): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab_comments" data-toggle="tab" href="#tab_comments_content"><?= trans("comments"); ?>&nbsp;(<?= $commentsCount; ?>)</a>
                                    </li>
                                <?php endif;
                                if ($generalSettings->facebook_comment_status == 1): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab_facebook_comments" data-toggle="tab" href="#tab_facebook_comments_content"><?= trans("facebook_comments"); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div id="accordion" class="tab-content">
                                <div class="tab-pane fade <?= $isReviewTabActive == true ? '' : 'show active'; ?>" id="tab_description_content">
                                    <div class="card">
                                        <div class="card-header">
                                            <a class="card-link" data-toggle="collapse" href="#collapse_description_content">
                                                <?= trans("description"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i>
                                            </a>
                                        </div>
                                        <div id="collapse_description_content" class="collapse-description-content collapse show">
                                            <div class="tab-content-inner">
                                                <div class="description">
                                                    <?= !empty($productDetails->description) ? $productDetails->description : ''; ?>
                                                </div>

                                                <div class="row-custom text-right m-b-10">
                                                    <?php if (authCheck()):
                                                        if (isActiveAffiliateProduct($product, $user)): ?>
                                                            <button type="button" id="btnCreateAffiliateLink" class="button-link text-muted link-abuse-report link-abuse-report-button display-inline-flex align-items-center" data-id="<?= $product->id; ?>">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 640 512" fill="currentColor">
                                                                    <path d="M579.8 267.7c56.5-56.5 56.5-148 0-204.5c-50-50-128.8-56.5-186.3-15.4l-1.6 1.1c-14.4 10.3-17.7 30.3-7.4 44.6s30.3 17.7 44.6 7.4l1.6-1.1c32.1-22.9 76-19.3 103.8 8.6c31.5 31.5 31.5 82.5 0 114L422.3 334.8c-31.5 31.5-82.5 31.5-114 0c-27.9-27.9-31.5-71.8-8.6-103.8l1.1-1.6c10.3-14.4 6.9-34.4-7.4-44.6s-34.4-6.9-44.6 7.4l-1.1 1.6C206.5 251.2 213 330 263 380c56.5 56.5 148 56.5 204.5 0L579.8 267.7zM60.2 244.3c-56.5 56.5-56.5 148 0 204.5c50 50 128.8 56.5 186.3 15.4l1.6-1.1c14.4-10.3 17.7-30.3 7.4-44.6s-30.3-17.7-44.6-7.4l-1.6 1.1c-32.1 22.9-76 19.3-103.8-8.6C74 372 74 321 105.5 289.5L217.7 177.2c31.5-31.5 82.5-31.5 114 0c27.9 27.9 31.5 71.8 8.6 103.9l-1.1 1.6c-10.3 14.4-6.9 34.4 7.4 44.6s34.4 6.9 44.6-7.4l1.1-1.6C433.5 260.8 427 182 377 132c-56.5-56.5-148-56.5-204.5 0L60.2 244.3z"/>
                                                                </svg>&nbsp;<?= trans("create_affiliate_link"); ?>
                                                            </button>&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <?php endif;
                                                        if ($product->user_id != user()->id): ?>
                                                            <button type="button" class="button-link text-muted link-abuse-report link-abuse-report-button display-inline-flex align-items-center" data-toggle="modal" data-target="#reportProductModal">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512" fill="currentColor">
                                                                    <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
                                                                </svg>&nbsp;<?= trans("report_this_product"); ?>
                                                            </button>
                                                        <?php endif;
                                                    else: ?>
                                                        <button type="button" class="button-link text-muted link-abuse-report link-abuse-report-product display-inline-flex align-items-center" data-toggle="modal" data-target="#loginModal">
                                                            <?= trans("report_this_product"); ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($productCustomFieldsValues) && !empty($productCustomFieldsValues['bottom']) && countItems($productCustomFieldsValues['bottom']) > 0): ?>
                                    <div class="tab-pane fade" id="tab_additional_information_content">
                                        <div class="card">
                                            <div class="card-header">
                                                <a class="card-link collapsed" data-toggle="collapse" href="#collapse_additional_information_content">
                                                    <?= trans("additional_information"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i>
                                                </a>
                                            </div>
                                            <div id="collapse_additional_information_content" class="collapse-description-content collapse">
                                                <div class="tab-content-inner">
                                                    <table class="table table-striped table-product-additional-information">
                                                        <tbody>
                                                        <?php foreach ($productCustomFieldsValues['bottom'] as $item): ?>
                                                            <tr>
                                                                <td class="td-left"><?= esc($item['name']); ?></td>
                                                                <td class="td-right"><?= esc($item['value']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;
                                if ($shippingStatus == 1 || $productLocationStatus == 1): ?>
                                    <div class="tab-pane fade" id="tab_shipping_content">
                                        <div class="card">
                                            <div class="card-header">
                                                <?php if ($shippingStatus == 1 && $productLocationStatus != 1): ?>
                                                    <a class="card-link collapsed" data-toggle="collapse" href="#collapse_shipping_content"><?= trans("shipping"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i></a>
                                                <?php elseif ($shippingStatus != 1 && $productLocationStatus == 1): ?>
                                                    <a class="card-link collapsed" data-toggle="collapse" href="#collapse_shipping_content" onclick="loadProductShopLocationMap();"><?= trans("location"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i></a>
                                                <?php else: ?>
                                                    <a class="card-link collapsed" data-toggle="collapse" href="#collapse_shipping_content" onclick="loadProductShopLocationMap();"><?= trans("shipping_location"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i></a>
                                                <?php endif; ?>
                                            </div>
                                            <div id="collapse_shipping_content" class="collapse-description-content collapse">
                                                <div class="tab-content-inner">
                                                    <table class="table table-product-shipping">
                                                        <tbody>
                                                        <?php if ($shippingStatus == 1): ?>
                                                            <tr>
                                                                <td class="td-left"><?= trans("shipping_cost"); ?></td>
                                                                <td class="td-right">
                                                                    <div class="form-group">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <label class="control-label"><?= trans("select_your_location"); ?></label>
                                                                            </div>
                                                                            <?php $defaultCountryId = $generalSettings->single_country_mode == 1 ? $generalSettings->single_country_id : $baseVars->defaultLocation->country_id;
                                                                            $shippingStates = !empty($defaultCountryId) ? getStatesByCountry($defaultCountryId) : array(); ?>
                                                                            <?php if ($generalSettings->single_country_mode != 1): ?>
                                                                                <div class="col-12 col-md-4 m-b-sm-15">
                                                                                    <select id="select_countries_product" name="country_id" class="select2 form-control" data-placeholder="<?= trans("country"); ?>" onchange="getStates(this.value, 'product'); $('#product_shipping_cost_container').empty();">
                                                                                        <option></option>
                                                                                        <?php if (!empty($activeCountries)):
                                                                                            foreach ($activeCountries as $item): ?>
                                                                                                <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
                                                                                            <?php endforeach;
                                                                                        endif; ?>
                                                                                    </select>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
                                                                            <?php endif; ?>
                                                                            <div class="col-12 col-md-4 m-b-sm-15">
                                                                                <div id="get_states_container_product">
                                                                                    <select id="select_states_product" name="state_id" class="select2 form-control" data-placeholder="<?= trans("state"); ?>" onchange="getProductShippingCost(this.value, '<?= $product->id; ?>');">
                                                                                        <option></option>
                                                                                        <?php if (!empty($shippingStates)):
                                                                                            foreach ($shippingStates as $item): ?>
                                                                                                <option value="<?= $item->id; ?>" <?= $item->id == $baseVars->defaultLocation->state_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                                                            <?php endforeach;
                                                                                        endif; ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="product_shipping_cost_container" class="product-shipping-methods"></div>
                                                                    <div class="row-custom">
                                                                        <div class="product-shipping-loader">
                                                                            <div class="spinner">
                                                                                <div class="bounce1"></div>
                                                                                <div class="bounce2"></div>
                                                                                <div class="bounce3"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php if (!empty($deliveryTime)): ?>
                                                                <tr>
                                                                    <td class="td-left"><?= trans("shipping"); ?></td>
                                                                    <td class="td-right"><span><?= @parseSerializedOptionArray($deliveryTime->option_array, selectedLangId()); ?></span></td>
                                                                </tr>
                                                            <?php endif;
                                                        endif;
                                                        if ($productLocationStatus == 1):
                                                            if (!empty($product->country_id)):?>
                                                                <tr>
                                                                    <td class="td-left"><?= trans("product_location"); ?></td>
                                                                    <td class="td-right"><span id="span_shop_location_address"><?= getLocation($product); ?></span></td>
                                                                </tr>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td class="td-left"><?= trans("shop_location"); ?></td>
                                                                    <td class="td-right"><span id="span_shop_location_address"><?= getLocation($user); ?></span></td>
                                                                </tr>
                                                            <?php endif;
                                                        endif; ?>
                                                        </tbody>
                                                    </table>
                                                    <?php if ($productLocationStatus == 1): ?>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="product-location-map">
                                                                    <iframe id="iframe_shop_location_address" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;
                                if ($generalSettings->reviews == 1): ?>
                                    <div class="tab-pane fade <?= $isReviewTabActive == true ? 'show active' : ''; ?>" id="tab_reviews_content">
                                        <div class="card">
                                            <div class="card-header">
                                                <a class="card-link collapsed" data-toggle="collapse" href="#collapse_reviews_content">
                                                    <?= trans("reviews"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i>
                                                </a>
                                            </div>
                                            <div id="collapse_reviews_content" class="collapse-description-content collapse">
                                                <div id="review-result">
                                                    <?= view('product/details/_reviews'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;
                                if ($generalSettings->product_comments == 1): ?>
                                    <div class="tab-pane fade" id="tab_comments_content">
                                        <div class="card">
                                            <div class="card-header">
                                                <a class="card-link collapsed" data-toggle="collapse" href="#collapse_comments_content">
                                                    <?= trans("comments"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i>
                                                </a>
                                            </div>
                                            <div id="collapse_comments_content" class="collapse-description-content collapse">
                                                <?= view('product/details/_comments', ['commentsArray' => $commentsArray]); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;
                                if ($generalSettings->facebook_comment_status == 1): ?>
                                    <div class="tab-pane fade" id="tab_facebook_comments_content">
                                        <div class="card">
                                            <div class="card-header">
                                                <a class="card-link collapsed" data-toggle="collapse" href="#collapse_facebook_comments_content">
                                                    <?= trans("facebook_comments"); ?><i class="icon-arrow-down"></i><i class="icon-arrow-up"></i>
                                                </a>
                                            </div>
                                            <div id="collapse_facebook_comments_content" class="collapse-description-content collapse">
                                                <div class="fb-comments" data-href="<?= current_url(); ?>" data-width="100%" data-numposts="5" data-colorscheme="light"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= view('partials/_ad_spaces', ['adSpace' => 'product_1', 'class' => 'mb-4']); ?>
            <?php if (!empty($userProducts) && $generalSettings->multi_vendor_system == 1): ?>
                <div class="col-12 section section-related-products m-t-30">
                    <strong class="title"><?= trans("more_from"); ?>&nbsp;<a href="<?= generateProfileUrl($user->slug); ?>"><?= esc(getUsername($user)); ?></a></strong>
                    <div class="row row-product">
                        <?php $count = 0;
                        $maxCount = $generalSettings->index_products_per_row == 6 ? 6 : 5;
                        foreach ($userProducts as $item):
                            if ($count < $maxCount):?>
                                <div class="col-6 col-sm-4 col-md-3 col-product <?= $generalSettings->index_products_per_row == 5 ? 'col-product-5' : 'col-product-6'; ?>">
                                    <?= view('product/_product_item', ['product' => $item]); ?>
                                </div>
                            <?php endif;
                            $count++;
                        endforeach; ?>
                    </div>
                    <?php if (countItems($userProducts) > 5): ?>
                        <div class="row-custom text-center">
                            <a href="<?= generateProfileUrl($product->user_slug); ?>" class="link-see-more"><span><?= trans("view_all"); ?>&nbsp;</span><i class="icon-arrow-right"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif;
            if (!empty($relatedProducts) && countItems($relatedProducts) > 0):
                shuffle($relatedProducts); ?>
                <div class="col-12 section section-related-products">
                    <strong class="title"><?= trans("you_may_also_like"); ?></strong>
                    <div class="row row-product">
                        <?php $i = 0;
                        $maxCount = $generalSettings->index_products_per_row == 6 ? 12 : 10;
                        foreach ($relatedProducts as $item):
                            if ($i < $maxCount):?>
                                <div class="col-6 col-sm-4 col-md-3 col-product <?= $generalSettings->index_products_per_row == 5 ? 'col-product-5' : 'col-product-6'; ?>">
                                    <?= view('product/_product_item', ['product' => $item]); ?>
                                </div>
                            <?php endif;
                            $i++;
                        endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?= view('partials/_ad_spaces', ['adSpace' => 'product_2', 'class' => 'mb-4']); ?>
        </div>
    </div>
</div>

<?= view('partials/_modal_send_message', ['subject' => esc($title), 'productId' => $product->id]); ?>

<?php if (isActiveAffiliateProduct($product, $user)): ?>
    <div class="modal fade" id="affliateLinkModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom modal-affiliate-link">
                <div class="modal-header">
                    <h5 class="modal-title m-b-15"><?= trans("affiliate_link"); ?></h5>
                    <div class="affiliate-link-exp"><?= trans("affiliate_link_exp"); ?></div>
                    <button type="button" class="close" data-dismiss="modal">
                        <span><i class="icon-close"></i> </span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <?php
                            $affCommissionRate = 0;
                            $affDiscountRate = 0;
                            if ($affiliateSettings->type == 'site_based') {
                                $affCommissionRate = !empty($affiliateSettings->commission_rate) ? $affiliateSettings->commission_rate : 0;
                                $affDiscountRate = !empty($affiliateSettings->discount_rate) ? $affiliateSettings->discount_rate : 0;
                            } else {
                                $affCommissionRate = !empty($user->affiliate_commission_rate) ? $user->affiliate_commission_rate : 0;
                                $affDiscountRate = !empty($user->affiliate_discount_rate) ? $user->affiliate_discount_rate : 0;
                            } ?>

                            <?php if (!empty($affCommissionRate)): ?>
                                <div class="m-b-15"><?= trans("referrer_commission_rate"); ?>:&nbsp;<strong><?= esc($affCommissionRate); ?>%</strong></div>
                            <?php endif; ?>
                            <?php if (!empty($affDiscountRate)): ?>
                                <div class="m-b-15"><?= trans("buyer_discount_rate"); ?>:&nbsp;<strong><?= esc($affDiscountRate); ?>%</strong></div>
                            <?php endif; ?>
                            <div class="copy-code-container copy-code-container-link">
                                <span class="code" id="spanAffLink"></span>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="button" id="btnCopyAffLink" class="btn btn-block"><span><?= trans("copy_link"); ?></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (authCheck() && $product->user_id != user()->id): ?>
    <div class="modal fade" id="reportProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom modal-report-abuse">
                <form id="form_report_product" method="post">
                    <input type="hidden" name="id" value="<?= $product->id; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= trans("report_this_product"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span><i class="icon-close"></i> </span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="response_form_report_product" class="col-12"></div>
                            <div class="col-12">
                                <div class="form-group m-0">
                                    <label class="control-label"><?= trans("description"); ?></label>
                                    <textarea name="description" class="form-control form-textarea" placeholder="<?= trans("abuse_report_exp"); ?>" minlength="5" maxlength="10000" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="reportCommentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-custom">
            <form id="form_report_comment" method="post">
                <div class="modal-header">
                    <h5 class="modal-title"><?= trans("report_comment"); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span><i class="icon-close"></i> </span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="response_form_report_comment" class="col-12"></div>
                        <div class="col-12">
                            <input type="hidden" id="report_comment_id" name="id" value="">
                            <div class="form-group m-0">
                                <label class="control-label"><?= trans("description"); ?></label>
                                <textarea name="description" class="form-control form-textarea" placeholder="<?= trans("abuse_report_exp"); ?>" minlength="5" maxlength="10000" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($generalSettings->facebook_comment_status == 1):
    echo $generalSettings->facebook_comment;
endif; ?>

<style>.product-location-map .embed-responsive {
        overflow: visible;
    }</style>
