<?php if (!empty($customFields) || ($productSettings->brand_status == 1 && !empty($brands['brands']))): ?>
    <div class="section-product-details">
        <div class="form-box form-box-last" style="padding-bottom: 0;">
            <div class="form-box-head">
                <h4 class="title"><?= trans('details'); ?></h4>
            </div>
            <div class="form-box-body">
                <div class="form-group">
                    <?php if ($productSettings->brand_status == 1 && !empty($brands) && !empty($brands['brands'])): ?>
                        <div class="row">
                            <div class="col-md-12 col-lg-6 col-custom-field m-b-30">
                                <label><?= trans("brands"); ?><?= $productSettings->is_brand_optional == 1 ? '(' . trans("optional") . ')' : ''; ?></label>
                                <div class="custom-options-container" style="border: 0 !important;">
                                    <div class="row">
                                        <select name="brand_id" class="select2 form-control" <?= $productSettings->is_brand_optional != 1 ? 'required' : ''; ?>>
                                            <option value=""><?= trans('select'); ?></option>
                                            <?php foreach ($brands['brands'] as $brand): ?>
                                                <option value="<?= esc($brand->id); ?>" <?= $product->brand_id == $brand->id ? 'selected' : ''; ?>><?= esc($brand->brand_name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row" id="custom_fields_container">
                        <?php if (!empty($customFields)):
                            foreach ($customFields as $customField):
                                if (!empty($customField)):
                                    if ($customField->field_type == 'text'):
                                        $inputValue = getProductCustomFieldInputValue($customField->id, $product->id); ?>
                                        <div class="col-sm-12 col-sm-6 col-custom-field">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <input type="text" name="field_<?= $customField->id; ?>" class="form-control form-input" value="<?= esc($inputValue); ?>" placeholder="<?= esc($customField->name); ?>" <?= $customField->is_required == 1 ? 'required' : ''; ?>>
                                        </div>
                                    <?php elseif ($customField->field_type == 'number'):
                                        $inputValue = getProductCustomFieldInputValue($customField->id, $product->id); ?>
                                        <div class="col-sm-12 col-sm-6 col-custom-field">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <input type="number" name="field_<?= $customField->id; ?>" class="form-control form-input" value="<?= esc($inputValue); ?>" placeholder="<?= esc($customField->name); ?>" min="0" max="999999999" <?= $customField->is_required == 1 ? 'required' : ''; ?>>
                                        </div>
                                    <?php elseif ($customField->field_type == 'textarea'):
                                        $inputValue = getProductCustomFieldInputValue($customField->id, $product->id); ?>
                                        <div class="col-sm-12 col-sm-6 col-custom-field">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <textarea class="form-control form-input custom-field-input" name="field_<?= $customField->id; ?>" placeholder="<?= esc($customField->name); ?>" <?= $customField->is_required == 1 ? 'required' : ''; ?> style="min-height: 80px;"><?= @esc($inputValue); ?></textarea>
                                        </div>
                                    <?php elseif ($customField->field_type == 'date'):
                                        $inputValue = getProductCustomFieldInputValue($customField->id, $product->id); ?>
                                        <div class="col-sm-12 col-sm-6 col-custom-field">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <div class="input-group date input-group-datepicker" data-provide="datepicker">
                                                <input type="text" name="field_<?= $customField->id; ?>" value="<?= esc($inputValue); ?>" class="datepicker form-control form-input" placeholder="<?= esc($customField->name); ?>" <?= $customField->is_required == 1 ? 'required' : ''; ?>>
                                                <div class="input-group-append input-group-addon cursor-pointer">
                                                    <span class="input-group-text input-group-text-date"><i class="fa fa-calendar-alt"></i> </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($customField->field_type == 'single_select'): ?>
                                        <div class="col-sm-12 col-sm-6 col-custom-field">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <select name="field_<?= $customField->id; ?>" class="select2 form-control custom-select" <?= $customField->is_required == 1 ? 'required' : ''; ?>>
                                                <option value=""><?= trans('select'); ?></option>
                                                <?php $fieldOptions = getCustomFieldOptions($customField, $activeLang->id);
                                                $fieldValues = getSelectedCustomFieldValuesForProduct($customField->id, $product->id, $activeLang->id);
                                                $selectedOptionIds = is_array($fieldValues) ? array_column($fieldValues, 'selected_option_id') : [];
                                                if (!empty($fieldOptions)):
                                                    foreach ($fieldOptions as $fieldOption):?>
                                                        <option value="<?= $fieldOption->id; ?>" <?= isItemInArray($fieldOption->id, $selectedOptionIds) ? 'selected' : ''; ?>><?= esc($fieldOption->name); ?></option>
                                                    <?php endforeach;
                                                endif; ?>
                                            </select>
                                        </div>
                                    <?php elseif ($customField->field_type == "multi_select"): ?>
                                        <div id="checkbox_options_container_<?= $customField->id; ?>" class="col-sm-12 col-sm-6 col-custom-field checkbox-options-container" data-custom-field-id="<?= $customField->id; ?>">
                                            <label><?= esc($customField->name); ?><?= $customField->is_required != 1 ? ' (' . trans("optional") . ')' : ''; ?></label>
                                            <div class="custom-options-container">
                                                <div class="row">
                                                    <?php $fieldOptions = getCustomFieldOptions($customField, $activeLang->id);
                                                    $fieldValues = getSelectedCustomFieldValuesForProduct($customField->id, $product->id, $activeLang->id);
                                                    $selectedOptionIds = is_array($fieldValues) ? array_column($fieldValues, 'selected_option_id') : [];
                                                    if (!empty($fieldOptions)):
                                                        foreach ($fieldOptions as $fieldOption): ?>
                                                            <div class="col-sm-12 col-sm-3">
                                                                <div class="custom-control custom-checkbox custom-control-validate-input label_validate_field_<?= $customField->id; ?>">
                                                                    <input type="checkbox" class="custom-control-input <?= $customField->is_required == 1 ? 'required-checkbox' : ''; ?>" id="form_checkbox_<?= $fieldOption->id; ?>" name="field_<?= $customField->id; ?>[]"
                                                                           value="<?= $fieldOption->id; ?>" <?= isItemInArray($fieldOption->id, $selectedOptionIds) ? 'checked' : ''; ?> <?= $customField->is_required == 1 ? 'required' : ''; ?>>
                                                                    <label class="custom-control-label font-weight-normal" for="form_checkbox_<?= $fieldOption->id; ?>"><?= esc($fieldOption->name); ?></label>
                                                                </div>
                                                            </div>
                                                        <?php endforeach;
                                                    endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif;
                                endif;
                            endforeach;
                        endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>