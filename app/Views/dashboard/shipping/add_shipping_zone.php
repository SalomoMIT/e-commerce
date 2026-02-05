<div class="row">
    <div class="col-sm-10">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('add_shipping_zone'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= generateDashUrl('shipping_settings'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('shipping_zones'); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <form action="<?= base_url('add-shipping-zone-post'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <div class="form-group">
                        <label class="control-label"><?= trans("zone_name"); ?></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" name="zone_name_lang_<?= $language->id; ?>" class="form-control form-input m-b-5" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("estimated_delivery"); ?>&nbsp;<small>(<?= trans("example"); ?>:&nbsp;3-5&nbsp;<?= trans("days"); ?>)</small></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" name="estimated_delivery_lang_<?= $language->id; ?>" class="form-control form-input m-b-5" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("regions"); ?></label>
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="selected_regions_container" class="selected-regions"></div>
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
                    <div class="form-group text-right">
                        <button type="submit" name="submit" value="update" class="btn btn-md btn-success"><?= trans("submit") ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('dashboard/shipping/_js_shipping'); ?>