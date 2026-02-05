<div class="row">
    <div class="col-sm-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('update_custom_field'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('custom-fields'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('custom_fields'); ?>
                    </a>
                </div>
            </div>
            <form action="<?= base_url('Category/editCustomFieldPost'); ?>" method="post" onkeypress="return event.keyCode != 13;">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $field->id; ?>">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="form-group">
                                <label><?= trans("field_name"); ?></label>
                                <?php foreach ($activeLanguages as $language): ?>
                                    <input type="text" class="form-control m-b-5" name="name_<?= $language->id; ?>" value="<?= !empty($fieldNamesArray[$language->id]) ? esc($fieldNamesArray[$language->id]) : ''; ?>" placeholder="<?= esc($language->name); ?>" maxlength="255" required>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-group">
                                <label><?= trans("filter_key"); ?> <small>(<?= trans("filter_key_exp"); ?>)</small></label>
                                <input type="text" class="form-control" name="product_filter_key" placeholder="<?= trans("field_name"); ?>" value="<?= esc($field->product_filter_key); ?>" maxlength="255" required>
                            </div>

                            <div class="form-group">
                                <label><?= trans('type'); ?></label>
                                <select class="form-control" name="field_type">
                                    <option value="text" <?= $field->field_type == 'text' ? 'selected' : ''; ?>><?= trans('text'); ?></option>
                                    <option value="textarea" <?= $field->field_type == 'textarea' ? 'selected' : ''; ?>><?= trans('textarea'); ?></option>
                                    <option value="number" <?= $field->field_type == 'number' ? 'selected' : ''; ?>><?= trans('number'); ?></option>
                                    <option value="date" <?= $field->field_type == 'date' ? 'selected' : ''; ?>><?= trans('date'); ?></option>
                                    <option value="single_select" <?= $field->field_type == 'single_select' ? 'selected' : ''; ?>><?= trans("single_select"); ?></option>
                                    <option value="multi_select" <?= $field->field_type == 'multi_select' ? 'selected' : ''; ?>><?= trans("multi_select"); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><?= trans("where_to_display"); ?></label>
                                <?= formRadio('where_to_display', 2, 1, trans("additional_information"), trans("product_details"), $field->where_to_display); ?>
                            </div>

                            <div class="form-group">
                                <label><?= trans('order'); ?></label>
                                <input type="number" class="form-control max-400" name="field_order" value="<?= esc($field->field_order); ?>" placeholder="<?= trans('order'); ?>" min="1" max="99999" value="1" required>
                            </div>

                            <div class="form-group">
                                <?= formSwitch('is_required', trans('required'), $field->is_required); ?>
                            </div>

                            <div class="form-group">
                                <?= formSwitch('status', trans('status'), $field->status); ?>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                    <a href="<?= adminUrl('custom-field-options/' . $field->id); ?>" class="btn btn-warning pull-right m-r-5"><i class="fa fa-list"></i>&nbsp;&nbsp;<?= trans('edit_options'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>