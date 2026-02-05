<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans("shipping_zones"); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= generateDashUrl('add_shipping_zone'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans("add_shipping_zone"); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dataTableNoSort" role="grid">
                                <thead>
                                <tr role="row">
                                    <th scope="col"><?= trans("zone_name"); ?></th>
                                    <th scope="col"><?= trans("regions"); ?></th>
                                    <th scope="col"><?= trans("shipping_methods"); ?></th>
                                    <th scope="col"><?= trans("options"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($shippingZones)): ?>
                                    <?php foreach ($shippingZones as $shippingZone): ?>
                                        <tr>
                                            <td><?= @parseSerializedNameArray($shippingZone->name_array, selectedLangId()); ?></td>
                                            <td>
                                                <?php $locations = getShippingLocationsByZone($shippingZone->id);
                                                if (!empty($locations)):
                                                    $i = 0;
                                                    foreach ($locations as $location):
                                                        $continentText = esc(getContinentNameByKey($location->continent_code)) . '/';
                                                        if ($generalSettings->single_country_mode == 1) {
                                                            $continentText = '';
                                                        }
                                                        if (!empty($location->country_name) && !empty($location->state_name)):?>
                                                            <label class="badge badge-light badge-shipping-loc pull-left"><?= $continentText . esc($location->country_name) . '/' . esc($location->state_name); ?></label>
                                                        <?php
                                                        elseif (!empty($location->country_name) && empty($location->state_name)):?>
                                                            <label class="badge badge-light badge-shipping-loc pull-left"><?= $continentText . esc($location->country_name); ?></label>
                                                        <?php else: ?>
                                                            <label class="badge badge-light badge-shipping-loc pull-left"><?= getContinentNameByKey($location->continent_code); ?></label>
                                                        <?php endif;
                                                        $i++;
                                                    endforeach;
                                                endif; ?>
                                            </td>
                                            <td>
                                                <?php $methods = getShippingPaymentMethodsByZone($shippingZone->id);
                                                $i = 0;
                                                if (!empty($methods)):
                                                    foreach ($methods as $method): ?>
                                                        <span class="pull-left"><?= $i != 0 ? ', ' : ''; ?><?= @parseSerializedNameArray($method->name_array, selectedLangId()); ?></span>
                                                        <?php $i++;
                                                    endforeach;
                                                endif; ?>
                                            </td>
                                            <td style="width: 120px;">
                                                <div class="btn-group btn-group-option">
                                                    <a href="<?= generateDashUrl('edit_shipping_zone') . '/' . $shippingZone->id; ?>" class="btn btn-sm btn-default btn-edit" data-toggle="tooltip" title="<?= trans('edit'); ?>"><i class="fa fa-edit"></i></a>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-default btn-delete" data-toggle="tooltip" title="<?= trans('delete'); ?>" onclick="deleteItem('Dashboard/deleteShippingZonePost','<?= $shippingZone->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="box box-sm">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans("shipping_delivery_times"); ?></h3>
                </div>
                <div class="right">
                    <a href="javascript:void(0)" class="btn btn-success btn-add-new" data-toggle="modal" data-target="#modalAddDeliveryTime">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans("add_delivery_time"); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive table-delivery-times">
                            <table class="table table-bordered table-striped dataTableNoSort" role="grid">
                                <thead>
                                <tr role="row">
                                    <th scope="col"><?= trans("option"); ?></th>
                                    <th scope="col"><?= trans("options"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($shippingDeliveryTimes)): ?>
                                    <?php foreach ($shippingDeliveryTimes as $deliveryTime): ?>
                                        <tr>
                                            <td><?= @parseSerializedOptionArray($deliveryTime->option_array, selectedLangId()); ?></td>
                                            <td style="width: 120px;">
                                                <div class="btn-group btn-group-option">
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-default btn-edit" data-toggle="modal" data-target="#modalEditDeliveryTime<?= $deliveryTime->id; ?>"><span data-toggle="tooltip" title="<?= trans('edit'); ?>"><i class="fa fa-edit"></i></span></a>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-default btn-delete" data-toggle="tooltip" title="<?= trans('delete'); ?>" onclick="deleteItem('Dashboard/deleteShippingDeliveryTimePost','<?= $deliveryTime->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <div id="modalEditDeliveryTime<?= $deliveryTime->id; ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                                                        <h4 class="modal-title"><?= trans("edit_delivery_time"); ?></h4>
                                                    </div>
                                                    <form action="<?= base_url('edit-shipping-delivery-time-post'); ?>" method="post">
                                                        <?= csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?= $deliveryTime->id; ?>">
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label"><?= trans("option"); ?></label>
                                                                <?php foreach ($activeLanguages as $language): ?>
                                                                    <input type="text" name="option_lang_<?= $language->id; ?>" value="<?= @parseSerializedOptionArray($deliveryTime->option_array, $language->id); ?>" class="form-control form-input m-b-5" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success"><?= trans("submit"); ?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-info alert-large">
            <?= trans("shipping_delivery_times_exp"); ?>
        </div>
    </div>
</div>

<div id="modalAddDeliveryTime" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                <h4 class="modal-title"><?= trans("add_delivery_time"); ?></h4>
            </div>
            <form action="<?= base_url('add-shipping-delivery-time-post'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans("option"); ?></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" name="option_lang_<?= $language->id; ?>" class="form-control form-input m-b-5" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><?= trans("submit"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .table-delivery-times .dataTables_length, .table-delivery-times .dataTables_filter {
        display: none;
    }
</style>


