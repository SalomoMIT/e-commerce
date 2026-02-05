<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('custom_fields'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('add-custom-field'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans('add_custom_field'); ?>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= adminUrl('bulk-custom-field-upload'); ?>" class="btn btn-info btn-add-new">
                            <i class="fa fa-upload"></i>&nbsp;&nbsp;<?= trans('bulk_custom_field_upload'); ?>
                        </a>
                    <?php endif; ?>
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
                                    <form action="<?= adminUrl('custom-fields'); ?>" method="get">
                                        <div class="item-table-filter" style="width: 80px; min-width: 80px;">
                                            <label><?= trans("show"); ?></label>
                                            <select name="show" class="form-control">
                                                <option value="15" <?= inputGet('show') == '15' ? 'selected' : ''; ?>>15</option>
                                                <option value="30" <?= inputGet('show') == '30' ? 'selected' : ''; ?>>30</option>
                                                <option value="60" <?= inputGet('show') == '60' ? 'selected' : ''; ?>>60</option>
                                                <option value="100" <?= inputGet('show') == '100' ? 'selected' : ''; ?>>100</option>
                                            </select>
                                        </div>
                                        <div class="item-table-filter" style="width: 320px;">
                                            <label><?= trans("search"); ?></label>
                                            <div class="item-table-filter-search">
                                                <input name="q" class="form-control" placeholder="<?= esc(trans("search")); ?>" type="search" value="<?= esc(inputGet('q')); ?>">
                                                <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" role="grid" aria-describedby="example1_info">
                                <thead>
                                <tr role="row">
                                    <th width="20"><?= trans('id'); ?></th>
                                    <th><?= trans('name'); ?></th>
                                    <th><?= trans('type'); ?></th>
                                    <th>&nbsp;</th>
                                    <th><?= trans('required'); ?></th>
                                    <th><?= trans('order'); ?></th>
                                    <th><?= trans('status'); ?></th>
                                    <th class="th-options"><?= trans('options'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($fields)):
                                    foreach ($fields as $item): ?>
                                        <tr>
                                            <td><?= esc($item->id); ?></td>
                                            <td><?= esc($item->field_name); ?></td>
                                            <td><?= trans($item->field_type); ?></td>
                                            <td>
                                                <form action="<?= base_url('Category/addRemoveCustomFieldFiltersPost'); ?>" method="post">
                                                    <?= csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?= $item->id; ?>">
                                                    <?php if ($item->field_type == 'single_select' || $item->field_type == 'multi_select'):
                                                        if ($item->is_product_filter == 1):?>
                                                            <button class="btn btn-sm btn-danger"><i class="fa fa-times"></i>&nbsp;<?= trans('remove_from_product_filters'); ?></button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-success"><i class="fa fa-plus"></i>&nbsp;<?= trans('add_to_product_filters'); ?></button>
                                                        <?php endif;
                                                    endif; ?>
                                                </form>
                                            </td>
                                            <td> <?= $item->is_required == 1 ? trans("yes") : trans("no"); ?></td>
                                            <td><?= esc($item->field_order); ?></td>
                                            <td>
                                                <?php if ($item->status == 1): ?>
                                                    <label class="label bg-olive label-table"><?= trans('active'); ?></label>
                                                <?php else: ?>
                                                    <label class="label bg-danger label-table"><?= trans('inactive'); ?></label>
                                                <?php endif; ?>
                                            </td>
                                            <td style="width: 200px;">
                                                <div class="dropdown">
                                                    <button class="btn bg-purple dropdown-toggle btn-select-option" type="button" data-toggle="dropdown"><?= trans('select_option'); ?>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu options-dropdown">
                                                        <li><a href="<?= adminUrl('edit-custom-field/' . $item->id); ?>"><i class="fa fa-edit option-icon"></i><?= trans('edit'); ?></a></li>
                                                        <li><a href="javascript:void(0)" onclick="deleteItem('Category/deleteCustomFieldPost','<?= $item->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can option-icon"></i><?= trans('delete'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($fields)): ?>
                                <p class="text-center"><?= trans("no_records_found"); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-12">
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