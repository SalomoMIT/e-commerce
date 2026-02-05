<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $title; ?></h3>
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
                            <form action="<?= adminUrl('transactions'); ?>" method="get" id="formFilterTransactions">
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
                                        <input name="q" class="form-control" placeholder="<?= trans("order_number"); ?>" type="search" value="<?= esc(inputGet('q')); ?>">
                                        <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                        <div class="btn-group table-export">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-table-export" data-toggle="dropdown"><?= trans("export"); ?>&nbsp;&nbsp;<i class="fa fa-caret-down"></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterTransactions" data-export-type="transactions" data-export-file-type="csv">CSV</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterTransactions" data-export-type="transactions" data-export-file-type="xml">XML</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterTransactions" data-export-type="transactions" data-export-file-type="excel"><?= trans("excel"); ?>&nbsp;(.xlsx)</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" role="grid">
                        <thead>
                        <tr role="row">
                            <th><?= trans('id'); ?></th>
                            <th><?= trans('order'); ?></th>
                            <th><?= trans('payment_method'); ?></th>
                            <th><?= trans('payment_id'); ?></th>
                            <th><?= trans('user'); ?></th>
                            <th><?= trans('currency'); ?></th>
                            <th><?= trans('payment_amount'); ?></th>
                            <th><?= trans('payment_status'); ?></th>
                            <th><?= trans('ip_address'); ?></th>
                            <th><?= trans('date'); ?></th>
                            <th class="max-width-120"><?= trans('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($transactions)):
                            foreach ($transactions as $item): ?>
                                <tr>
                                    <td><?= $item->id; ?></td>
                                    <td class="order-number-table">
                                        #<?= $item->order_number; ?>
                                    </td>
                                    <td><?= getPaymentMethod($item->payment_method); ?></td>
                                    <td><?= $item->payment_id; ?></td>
                                    <td>
                                        <?php if ($item->user_id == 0): ?>
                                            <label class="label bg-olive"><?= trans("guest"); ?></label>
                                        <?php else: ?>
                                            <div class="table-orders-user">
                                                <a href="<?= generateProfileUrl($item->user_slug); ?>" target="_blank" class="table-link"><?= esc($item->user_username); ?></a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $item->currency; ?></td>
                                    <td><?= $item->payment_amount; ?></td>
                                    <td><?= getPaymentStatus($item->payment_status); ?></td>
                                    <td><?= $item->ip_address; ?></td>
                                    <td><?= formatDate($item->created_at); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn bg-purple dropdown-toggle btn-select-option" type="button" data-toggle="dropdown"><?= trans('select_option'); ?>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu options-dropdown">
                                                <li>
                                                    <a href="javascript:void(0)" onclick="deleteItem('OrderAdmin/deleteTransactionPost','<?= $item->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can option-icon"></i><?= trans('delete'); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                    <?php if (empty($transactions)): ?>
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