<div class="row">
    <div class="col-sm-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('add_custom_field'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('custom-fields'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('custom_fields'); ?>
                    </a>
                </div>
            </div>
            <form action="<?= base_url('Category/addCustomFieldPost'); ?>" method="post" onkeypress="return event.keyCode != 13;">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="form-group">
                                <label><?= trans("field_name"); ?></label>
                                <?php foreach ($activeLanguages as $language): ?>
                                    <input type="text" class="form-control m-b-5" name="name_<?= $language->id; ?>" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-group">
                                <label><?= trans('type'); ?></label>
                                <select class="form-control" name="field_type">
                                    <option value="text"><?= trans('text'); ?></option>
                                    <option value="textarea"><?= trans('textarea'); ?></option>
                                    <option value="number"><?= trans('number'); ?></option>
                                    <option value="date"><?= trans('date'); ?></option>
                                    <option value="single_select"><?= trans("single_select"); ?></option>
                                    <option value="multi_select"><?= trans("multi_select"); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><?= trans("where_to_display"); ?></label>
                                <?= formRadio('where_to_display', 2, 1, trans("additional_information"), trans("product_details"), 2); ?>
                            </div>

                            <div class="form-group">
                                <label><?= trans('order'); ?></label>
                                <input type="number" class="form-control max-400" name="field_order" placeholder="<?= trans('order'); ?>" min="1" max="99999" value="1" required>
                            </div>

                            <div class="form-group">
                                <?= formSwitch('is_required', trans('required'), 1); ?>
                            </div>

                            <div class="form-group">
                                <?= formSwitch('status', trans('status'), 1); ?>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_and_continue'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>