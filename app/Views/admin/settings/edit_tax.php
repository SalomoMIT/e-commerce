<div class="row">
    <div class="col-sm-12 col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('edit_tax'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl("payment-settings"); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('payment_settings'); ?>
                    </a>
                </div>
            </div>
            <form action="<?= base_url('Admin/editTaxPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $tax->id; ?>">
                <div class="box-body">
                    <div class="form-group">
                        <label><?= trans("tax_name"); ?></label>
                        <?php foreach ($activeLanguages as $language):
                            $nameArray = !empty($tax->name_data) ? unserializeData($tax->name_data) : null; ?>
                            <input type="text" name="tax_name_<?= $language->id; ?>" value="<?= esc(getTaxName($nameArray, $language->id)); ?>" class="form-control m-b-5" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans('tax_rate'); ?>(%)</label>
                        <input type="number" name="tax_rate" class="form-control" min="0" max="100" step="0.01" value="<?= esc($tax->tax_rate); ?>" required>
                    </div>
                    <div class="form-group">
                        <?= formCheckbox('product_sales', 1, trans('apply_for_product_sales'), $tax->product_sales); ?>
                        <?= formCheckbox('service_payments', 1, trans('apply_for_service_payments') . ' (' . trans("membership_payments") . ', ' . trans("promotion_payments") . ')', $tax->service_payments); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans("status"); ?></label>
                        <?= formRadio('status', 1, 0, trans("enable"), trans("disable"), $tax->status); ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans('location'); ?></label>
                        <div class="custom-control custom-checkbox" style="margin-bottom: 20px;">
                            <input type="checkbox" name="all_countries" value="1" id="checkboxAllCountries" class="custom-control-input" <?= !empty($tax->is_all_countries) ? 'checked' : ''; ?>>
                            <label for="checkboxAllCountries" class="custom-control-label"><?= trans("all_locations"); ?></label>
                        </div>
                        <?= view('admin/includes/_tax_location_picker', ['tax' => $tax]); ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>