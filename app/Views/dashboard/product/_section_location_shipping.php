<?php $isLocationEnabled = true;
if ($product->listing_type == 'ordinary_listing') {
    if ($productSettings->classified_product_location != 1) {
        $isLocationEnabled = false;
    }
} else {
    if ($productSettings->marketplace_product_location != 1) {
        $isLocationEnabled = false;
    }
}
if ($product->product_type != 'digital' && $isLocationEnabled == true):?>
    <div class="section-product-details">
        <div class="form-box form-box-last">
            <div class="form-box-head">
                <h4 class="title">
                    <?= trans('location'); ?>
                    <small><?= trans("product_location_exp"); ?></small>
                </h4>
            </div>
            <div class="form-box-body">
                <div class="row">
                    <?php 
                    // $countries = getCountries();
                    // $countryId = $product->country_id;
                    // $states = !empty($countryId) ? getStatesByCountry($countryId) : array();
                    $cities = !empty($product->state_id) ? getCitiesByState($product->state_id) : array(); ?>
                    <?php if ($generalSettings->single_country_mode != 1): ?>
                        <div class="col-md-12 col-lg-2 m-b-15">
                            <input disabled class="form-control form-input" value="Indonesia" maxlength="250">
                        </div>
                        <div class="col-md-12 col-lg-2 m-b-15">
                            <input type="hidden" name="province_id" value="<?= $product->province_id; ?>">
                            <input type="hidden" name="province_name" value="<?= $product->province_name; ?>">
                            <select id="select_provinces_<?= $product->id??"0"; ?>" class="select2 form-control" onchange="getCities(this.value,'<?= $product->id; ?>');" required>
                                <option value="">Pilih Provinsi</option>
                                <?php if (!empty($states)):                                            
                                    foreach ($states as $item): ?>                                               
                                        <option value="<?= $item->id; ?>" <?= $item->id == $product->province_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
                        <?php $countryId = $generalSettings->single_country_id;
                    endif; ?>

                    <div id="get_cities_container" class="col-md-12 col-lg-2 m-b-15 <?= empty($cities) ? 'display-none' : ''; ?>">
                        <input type="hidden" name="city_id_modif" value="<?= $product->city_id_modif; ?>">
                        <input type="hidden" name="city_name_modif" value="<?= $product->city_name_modif; ?>">
                        <select id="select_cities_<?= $product->id; ?>" class="select2 form-control" onchange="getDistrict(this.value,'<?= $product->id; ?>');" required>
                            <option value="<?= $product->city_id_modif; ?>"><?= $product->city_name_modif; ?></option>
                        </select>
                    </div>
                    <div class="col-md-12 col-lg-4 m-b-15">
                        <input type="hidden" name="district_id" value="<?= $product->district_id; ?>">
                        <input type="hidden" name="district_name" value="<?= $product->district_name; ?>">
                        <select id="select_district_<?= $product->id; ?>" class="select2 form-control" onchange="setAddressPick(this.value,'<?= $product->id; ?>');" required> 
                            <option value="<?= $product->district_id; ?>"><?= $product->district_name; ?></option>
                        </select>
                    </div>
                    <div class="col-md-12 col-lg-4 m-b-15">
                        <input type="text" name="address" id="address_input" class="form-control form-input" value="<?= !empty($product->address) ? esc($product->address) : ''; ?>" placeholder="<?= trans("address") ?>" maxlength="499">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<?php if ($shippingStatus == 1):
    $shippingDimensions = json_decode($product->shipping_dimensions ?? ''); ?>
    <div class="section-product-details">
        <div class="form-box form-box-last">
            <div class="form-box-head">
                <h4 class="title"><?= trans('shipping'); ?>&nbsp;(<?= trans("optional"); ?>)</h4>
            </div>


            <div class="row">
                <div class="col-sm-12 col-md-4 col-lg-2">
                    <div class="form-group">
                        <label><?= trans("weight"); ?>&nbsp;(<?= trans("kg"); ?>)</label>
                        <input type="number" class="form-control" name="product_weight" value="<?= !empty($shippingDimensions->weight) ? esc($shippingDimensions->weight) : ''; ?>" placeholder="e.g., 1.5" min="0" max="999.99" step="0.01">
                    </div>
                </div>

                <div class="col-sm-12 col-md-8 col-lg-6">
                    <div class="form-group m-b-0">
                        <label for="product_length" style="word-break: break-word; white-space: pre-wrap"><?= trans("dimensions"); ?>&nbsp;(<?= trans("length"); ?>&nbsp;/&nbsp;<?= trans("width"); ?>&nbsp;/&nbsp;<?= trans("height"); ?>)&nbsp;(<?= trans("cm"); ?>)</label>
                        <div style="display: flex; gap: 5px; max-width: 500px;">
                            <input type="number" class="form-control" name="product_length" value="<?= !empty($shippingDimensions->length) ? esc($shippingDimensions->length) : ''; ?>" placeholder="<?= esc(trans("length")); ?>" min="0" max="500" step="0.01">
                            <input type="number" class="form-control" name="product_width" value="<?= !empty($shippingDimensions->width) ? esc($shippingDimensions->width) : ''; ?>" placeholder="<?= esc(trans("width")); ?>" min="0" max="500" step="0.01">
                            <input type="number" class="form-control" name="product_height" value="<?= !empty($shippingDimensions->height) ? esc($shippingDimensions->height) : ''; ?>" placeholder="<?= esc(trans("height")); ?>" min="0" max="500" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <?php if (!empty($product->chargeable_weight)): ?>
                        <label class="label label-default" style="font-size: 13px; !important; margin-bottom: 25px; margin-top: 5px; display: inline-block">
                            <?= trans("calculated_weight") ?>(<?= trans("kg"); ?>):&nbsp;<strong><?= $product->chargeable_weight; ?></strong>
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <label><?= trans('delivery_time'); ?></label>
                    <select name="shipping_delivery_time_id" class="select2 form-control custom-select">
                        <option value=""><?= trans("select"); ?></option>
                        <?php if (!empty($shippingDeliveryTimes)): ?>
                            <?php foreach ($shippingDeliveryTimes as $deliveryTime): ?>
                                <option value="<?= $deliveryTime->id; ?>" <?= $product->shipping_delivery_time_id == $deliveryTime->id ? 'selected' : ''; ?>><?= @parseSerializedOptionArray($deliveryTime->option_array, selectedLangId()); ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
