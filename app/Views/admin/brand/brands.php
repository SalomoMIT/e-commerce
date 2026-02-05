<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('brands'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('add-brand'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans('add_brand'); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row table-filter-container">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-default filter-toggle collapsed m-b-10" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false">
                                    <i class="fa fa-filter"></i>&nbsp;&nbsp;<?= trans("filter"); ?>
                                </button>
                                <div class="collapse navbar-collapse" id="collapseFilter">
                                    <form action="<?= adminUrl('brands'); ?>" method="get">
                                        <div class="item-table-filter" style="width: 80px; min-width: 80px;">
                                            <label><?= trans("show"); ?></label>
                                            <select name="show" class="form-control">
                                                <option value="15" <?= inputGet('show', true) == '15' ? 'selected' : ''; ?>>15</option>
                                                <option value="30" <?= inputGet('show', true) == '30' ? 'selected' : ''; ?>>30</option>
                                                <option value="60" <?= inputGet('show', true) == '60' ? 'selected' : ''; ?>>60</option>
                                                <option value="100" <?= inputGet('show', true) == '100' ? 'selected' : ''; ?>>100</option>
                                            </select>
                                        </div>
                                        <div class="item-table-filter">
                                            <label><?= trans("search"); ?></label>
                                            <input name="q" class="form-control" placeholder="<?= trans("search") ?>" type="search" value="<?= esc(inputGet('q', true)); ?>">
                                        </div>
                                        <div class="item-table-filter md-top-10" style="width: 65px; min-width: 65px;">
                                            <label style="display: block">&nbsp;</label>
                                            <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped cs_datatable_lang" role="grid">
                                <thead>
                                <tr role="row">
                                    <th width="20"><?= trans('id'); ?></th>
                                    <th><?= trans('name'); ?></th>
                                    <th><?= trans('logo'); ?></th>
                                    <th><?= trans('categories'); ?></th>
                                    <th><?= trans('date'); ?></th>
                                    <th class="th-options"><?= trans('options'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($brands)):
                                    foreach ($brands as $brand): ?>
                                        <tr>
                                            <td><?= esc($brand->id); ?></td>
                                            <td><?= esc($brand->brand_name); ?></td>
                                            <td>
                                                <?php if (!empty($brand->image_path)): ?>
                                                    <div style="width: 64px; height: 64px;">
                                                        <img data-src="<?= getStorageFileUrl($brand->image_path, $brand->storage); ?>" class="lazyload" style="width: 100%; height: 100%; object-fit: contain">
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($brand->category_names)) {
                                                    $catNamesArray = explode('|||', $brand->category_names);
                                                    if (!empty($catNamesArray)) {
                                                        foreach ($catNamesArray as $catName) { ?>
                                                            <label class="label label-default"><?= esc($catName); ?></label>
                                                        <?php }
                                                    }
                                                } ?>
                                            </td>
                                            <td style="width: 200px;"><?= formatDate($brand->created_at); ?></td>
                                            <td style="width: 200px;">
                                                <div class="dropdown">
                                                    <button class="btn bg-purple dropdown-toggle btn-select-option" type="button" data-toggle="dropdown"><?= trans('select_option'); ?>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu options-dropdown">
                                                        <li><a href="<?= adminUrl('edit-brand/' . $brand->id); ?>"><i class="fa fa-edit option-icon"></i><?= trans('edit'); ?></a></li>
                                                        <li><a href="javascript:void(0)" onclick="deleteItem('Category/deleteBrandPost','<?= $brand->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can option-icon"></i><?= trans('delete'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($brands)): ?>
                                <p class="text-center">
                                    <?= trans("no_records_found"); ?>
                                </p>
                            <?php endif; ?>
                            <div class="col-sm-12 table-ft">
                                <div class="row">
                                    <div class="pull-right">
                                        <?= $pager->links; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-sm-12 col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= trans("settings"); ?></h3>
                    </div>
                    <form action="<?= base_url('Category/brandSettingsPost'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <div class="box-body">
                            <div class="form-group">
                                <label><?= trans("status"); ?></label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="brand_status" value="1" id="brand_status_1" class="custom-control-input" <?= $productSettings->brand_status == 1 ? 'checked' : ''; ?>>
                                            <label for="brand_status_1" class="custom-control-label"><?= trans("enable"); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="brand_status" value="0" id="brand_status_2" class="custom-control-input" <?= $productSettings->brand_status != 1 ? 'checked' : ''; ?>>
                                            <label for="brand_status_2" class="custom-control-label"><?= trans("disable"); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?= trans("optional"); ?></label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="is_brand_optional" value="1" id="is_brand_optional_1" class="custom-control-input" <?= $productSettings->is_brand_optional == 1 ? 'checked' : ''; ?>>
                                            <label for="is_brand_optional_1" class="custom-control-label"><?= trans("yes"); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="is_brand_optional" value="0" id="is_brand_optional_2" class="custom-control-input" <?= $productSettings->is_brand_optional != 1 ? 'checked' : ''; ?>>
                                            <label for="is_brand_optional_2" class="custom-control-label"><?= trans("no"); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?= trans("where_to_display"); ?></label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="brand_where_to_display" value="2" id="where_to_display_1" class="custom-control-input" <?= $productSettings->brand_where_to_display != 1 ? 'checked' : ''; ?>>
                                            <label for="where_to_display_1" class="custom-control-label"><?= trans("additional_information"); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="brand_where_to_display" value="1" id="where_to_display_2" class="custom-control-input" <?= $productSettings->brand_where_to_display == 1 ? 'checked' : ''; ?>>
                                            <label for="where_to_display_2" class="custom-control-label"><?= trans("product_details"); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right"><?= trans("save_changes"); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>