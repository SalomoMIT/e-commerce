<div class="row">
    <div class="col-sm-10">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('edit_shipping_zone'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= generateDashUrl('shipping_settings'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('shipping_zones'); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <form action="<?= base_url('edit-shipping-zone-post'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="zone_id" value="<?= esc($shippingZone->id); ?>">
                    <div class="form-group">
                        <label class="control-label"><?= trans("zone_name"); ?></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" name="zone_name_lang_<?= $language->id; ?>" class="form-control form-input m-b-5" value="<?= @parseSerializedNameArray($shippingZone->name_array, $language->id); ?>" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("estimated_delivery"); ?>&nbsp;<small>(<?= trans("example"); ?>:&nbsp;3-5&nbsp;<?= trans("days"); ?>)</small></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" name="estimated_delivery_lang_<?= $language->id; ?>" class="form-control form-input m-b-5" value="<?= @parseSerializedNameArray($shippingZone->estimated_delivery, $language->id); ?>" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("regions"); ?></label>
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="selected_regions_container" class="selected-regions m-b-10">
                                    <?php $locations = getShippingLocationsByZone($shippingZone->id);
                                    if (!empty($locations)):
                                        $i = 0;
                                        $arrayRegions = array();
                                        foreach ($locations as $location):
                                            $continentText = esc(getContinentNameByKey($location->continent_code)) . '/';
                                            if ($generalSettings->single_country_mode == 1) {
                                                $continentText = '';
                                            }
                                            if (!empty($location->country_name) && !empty($location->state_name)):
                                                array_push($arrayRegions, 'state-' . $location->state_id); ?>
                                                <div id="lc-state-<?= $location->state_id; ?>" class="region"><?= $continentText . esc($location->country_name) . '/' . esc($location->state_name); ?><a href="javascript:void(0)" onclick="deleteShippingLocation('<?= $location->id; ?>');"><i class="fa fa-times"></i></a><input type="hidden" value="<?= $location->state_id; ?>" name="state[]"></div>
                                            <?php elseif (!empty($location->country_name) && empty($location->state_name)):
                                                array_push($arrayRegions, 'country-' . $location->country_id); ?>
                                                <div id="lc-country-<?= $location->country_id; ?>" class="region"><?= $continentText . esc($location->country_name); ?><a href="javascript:void(0)"><i class="fa fa-times" onclick="deleteShippingLocation('<?= $location->id; ?>');"></i></a><input type="hidden" value="<?= $location->country_id; ?>" name="country[]"></div>
                                            <?php else:
                                                array_push($arrayRegions, 'continent-' . $location->continent_code); ?>
                                                <div id="lc-continent-<?= $location->continent_code; ?>" class="region"><?= getContinentNameByKey($location->continent_code); ?><a href="javascript:void(0)" onclick="deleteShippingLocation('<?= $location->id; ?>');"><i class="fa fa-times"></i></a><input type="hidden" value="<?= $location->continent_code; ?>" name="continent[]"></div>
                                            <?php endif;
                                            $i++;
                                        endforeach;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?php if ($generalSettings->single_country_mode != 1): ?>
                                    <div class="form-group m-b-5">
                                        <select id="select_continents" class="select2 form-control" data-placeholder="<?= trans("continent"); ?>">
                                            <option></option>
                                            <?php if (!empty($continents)):
                                                foreach ($continents as $key => $continent):?>
                                                    <option value="<?= $key; ?>"><?= esc($continent); ?></option>
                                                <?php endforeach;
                                            endif; ?>
                                        </select>
                                    </div>
                                    <div id="form_group_countries" class="form-group m-b-5" style="display: none;">
                                        <select id="select_countries" class="select2 form-control" data-placeholder="<?= trans("country"); ?>">
                                            <option></option>
                                        </select>
                                    </div>
                                <?php else: ?>
                                    <div id="form_group_countries" class="form-group m-b-5">
                                        <select id="select_countries" class="select2 form-control" data-placeholder="<?= trans("country"); ?>">
                                            <option></option>
                                            <?php foreach ($countries as $item):
                                                if ($item->status == 1 && $item->id == $generalSettings->single_country_id): ?>
                                                    <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
                                                <?php endif;
                                            endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div id="form_group_states" class="form-group m-b-5" style="display: none;">
                                    <select id="select_states" class="select2 form-control" data-placeholder="<?= trans("state"); ?>">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            <div id="btn_select_region_container" class="col-sm-12" style="display: none;">
                                <a href="javascript:void(0)" id="btn_select_region" class="btn btn-sm btn-info"><i class="fa fa-check"></i>&nbsp;<?= trans("select_region") ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label m-b-10"><?= trans("shipping_methods"); ?></label>
                        <?= view("dashboard/shipping/_shipping_methods", ['shippingZone' => $shippingZone]); ?>
                    </div>

                    <div class="form-group text-right">
                        <button type="submit" name="submit" value="update" class="btn btn-md btn-success"><?= trans("save_changes") ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalShippingMethod" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('Dashboard/addShippingMethodPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="zone_id" value="<?= esc($shippingZone->id); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                    <h4 class="modal-title"><?= trans("shipping_methods"); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans("shipping_method"); ?></label>
                        <select name="shipping_method" class="form-control custom-select">
                            <option value=""><?= trans("select"); ?></option>
                            <option value="flat_rate"><?= trans("flat_rate"); ?></option>
                            <option value="local_pickup"><?= trans("local_pickup"); ?></option>
                            <option value="free_shipping"><?= trans("free_shipping"); ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><?= trans("add_shipping_method"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($methods)):
    foreach ($methods as $method):
        echo view("dashboard/shipping/_edit_shipping_method", ['method' => $method]);
    endforeach;
endif; ?>

<?= view('dashboard/shipping/_js_shipping'); ?>