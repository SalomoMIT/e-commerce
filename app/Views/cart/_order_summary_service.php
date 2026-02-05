<div class="col-sm-12 col-lg-4 order-summary-container">
    <h2 class="cart-section-title"><?= trans("summary"); ?></h2>
    <div class="right">
        <?php if (!empty($servicePayment)): ?>
            <div class="row-custom m-b-15"><strong><?= esc($servicePayment->paymentName); ?></strong></div>
            <?php
            if ($servicePayment->serviceType == 'membership'):
                $planId = null;
                if (!empty($servicePayment->data) && !empty($servicePayment->data->planId)) {
                    $planId = $servicePayment->data->planId;
                }
                $plan = !empty($planId) ? getMembershipPlan($planId) : null;
                if (!empty($plan)): ?>
                    <div class="cart-order-details">
                        <div class="item">
                            <div class="item-right">
                                <div class="list-item m-t-15">
                                    <label><?= trans("membership_plan"); ?>:</label>
                                    <strong class="lbl-price"><?= getMembershipPlanName($plan->title_array, selectedLangId()); ?></strong>
                                </div>
                                <div class="list-item">
                                    <label><?= trans("price"); ?>:</label>
                                    <strong class="lbl-price"><?= priceDecimal($servicePayment->subtotal, $selectedCurrency->code, true); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif;
            elseif ($servicePayment->serviceType == 'promote'):
                $serviceData = null;
                if (!empty($servicePayment->data)) {
                    $serviceData = $servicePayment->data;
                }
                if (!empty($serviceData)):
                    $product = getActiveProduct($serviceData->productId);
                    if (!empty($product)):?>
                        <div class="cart-order-details">
                            <div class="item">
                                <div class="item-left">
                                    <a href="<?= generateProductUrl($product); ?>">
                                        <div class="product-image-box product-image-box-xs">
                                            <img data-src="<?= getProductMainImage($product->id, 'image_small'); ?>" alt="<?= esc($product->title); ?>" class="lazyload img-fluid img-product">
                                        </div>
                                    </a>
                                </div>
                                <div class="item-right">
                                    <div class="list-item">
                                        <a href="<?= generateProductUrl($product); ?>"><?= esc($product->title); ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="item-right">
                                    <div class="list-item m-t-15">
                                        <label><?= trans("promote_plan"); ?>:</label>
                                        <strong class="lbl-price"><?= esc($serviceData->purchasedPlan); ?></strong>
                                    </div>
                                    <div class="list-item">
                                        <label><?= trans("price"); ?>:</label>
                                        <strong class="lbl-price"><?= priceDecimal($servicePayment->subtotal, $selectedCurrency->code, true); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif;
                endif;
            elseif ($servicePayment->serviceType == 'add_funds'): ?>
                <div class="cart-order-details">
                    <div class="item">
                        <div class="item-right">
                            <div class="list-item">
                                <label><?= trans("deposit_amount"); ?>:</label>
                                <strong class="lbl-price"><?= priceDecimal($servicePayment->grandTotal, $selectedCurrency->code, true); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;
        endif; ?>
        <div class="row-custom m-t-30 m-b-10">
            <strong><?= trans("subtotal"); ?><span class="float-right"><?= priceDecimal($servicePayment->subtotal, $selectedCurrency->code, true); ?></span></strong>
        </div>
        <?php if (!empty($servicePayment->globalTaxesArray)):
            foreach ($servicePayment->globalTaxesArray as $taxItem):?>
                <div class="row-custom m-b-10">
                    <strong><?= esc(getTaxName($taxItem['taxNameArray'], selectedLangId())); ?>&nbsp;(<?= $taxItem['taxRate']; ?>%)<span class="float-right"><?= priceDecimal($taxItem['taxTotal'], $selectedCurrency->code, true); ?></span></strong>
                </div>
            <?php endforeach;
        endif; ?>
        <div class="row-custom">
            <p class="line-seperator"></p>
        </div>
        <div class="row-custom">
            <strong><?= trans("total"); ?><span class="float-right"><?= priceDecimal($servicePayment->grandTotal, $selectedCurrency->code, true); ?></span></strong>
        </div>
    </div>
</div>