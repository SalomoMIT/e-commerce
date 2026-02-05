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
                            <form action="<?= adminUrl('orders'); ?>" method="get" id="formFilterOrders">
                                <div class="item-table-filter" style="width: 80px; min-width: 80px;">
                                    <label><?= trans("show"); ?></label>
                                    <select name="show" class="form-control">
                                        <option value="15" <?= inputGet('show') == '15' ? 'selected' : ''; ?>>15</option>
                                        <option value="30" <?= inputGet('show') == '30' ? 'selected' : ''; ?>>30</option>
                                        <option value="60" <?= inputGet('show') == '60' ? 'selected' : ''; ?>>60</option>
                                        <option value="100" <?= inputGet('show') == '100' ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("status"); ?></label>
                                    <select name="status" class="form-control">
                                        <option value="" selected><?= trans("all"); ?></option>
                                        <option value="processing" <?= inputGet('status') == 'processing' ? 'selected' : ''; ?>><?= trans("order_processing"); ?></option>
                                        <option value="completed" <?= inputGet('status') == 'completed' ? 'selected' : ''; ?>><?= trans("completed"); ?></option>
                                        <option value="cancelled" <?= inputGet('status') == 'cancelled' ? 'selected' : ''; ?>><?= trans("cancelled"); ?></option>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("payment_status"); ?></label>
                                    <select name="payment_status" class="form-control">
                                        <option value="" selected><?= trans("all"); ?></option>
                                        <option value="payment_received" <?= inputGet('payment_status') == 'payment_received' ? 'selected' : ''; ?>><?= trans("payment_received"); ?></option>
                                        <option value="pending_payment" <?= inputGet('payment_status') == 'pending_payment' ? 'selected' : ''; ?>><?= trans("pending_payment"); ?></option>
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
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterOrders" data-export-type="orders" data-export-file-type="csv">CSV</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterOrders" data-export-type="orders" data-export-file-type="xml">XML</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formFilterOrders" data-export-type="orders" data-export-file-type="excel"><?= trans("excel"); ?>&nbsp;(.xlsx)</button>
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
                            <th><?= trans('order'); ?></th>
                            <th><?= trans('buyer'); ?></th>
                            <th><?= trans('total'); ?></th>
                            <th><?= trans('currency'); ?></th>
                            <th><?= trans('status'); ?></th>
                            <th><?= trans('payment_status'); ?></th>
                            <th><?= trans('updated'); ?></th>
                            <th><?= trans('date'); ?></th>
                            <th class="max-width-120"><?= trans('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($orders)):
                            foreach ($orders as $item): ?>
                                <tr>
                                    <td class="order-number-table">
                                        <a href="<?= adminUrl('order-details/' . $item->id); ?>" class="table-link">
                                            #<?= esc($item->order_number); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($item->buyer_id == 0): ?>
                                            <div class="table-orders-user">
                                                <?php $shipping = unserializeData($item->shipping);
                                                if (!empty($shipping)): ?>
                                                    <div><?= !empty($shipping->sFirstName) ? esc($shipping->sFirstName) : ''; ?>&nbsp;<?= !empty($shipping->sLastName) ? esc($shipping->sLastName) : ''; ?></div>
                                                <?php endif; ?>
                                                <label class="label bg-olive label-order-guest"><?= trans("guest"); ?></label>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-orders-user">
                                                <a href="<?= generateProfileUrl($item->buyer_slug); ?>" target="_blank">
                                                    <?= esc($item->buyer_username); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= priceFormatted($item->price_total, $item->price_currency); ?></strong></td>
                                    <td><?= $item->price_currency; ?></td>
                                    <td>
                                        <?php if ($item->status == 1): ?>
                                            <label class="label label-success"><?= trans("completed"); ?></label>
                                        <?php elseif ($item->status == 2): ?>
                                            <label class="label label-danger"><?= trans("cancelled"); ?></label>
                                        <?php else: ?>
                                            <label class="label label-default"><?= trans("order_processing"); ?></label>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="m-b-5">
                                            <?= trans($item->payment_status); ?>
                                        </div>
                                        <a href="<?= base_url('invoice/' . $item->order_number . '?type=admin'); ?>" class="btn btn-sm btn-default" target="_blank"><i class="fa fa-file-text"></i>&nbsp;&nbsp;<?= trans("view_invoice"); ?></a>
                                    </td>
                                    <td><?= timeAgo($item->updated_at); ?></td>
                                    <td> <?= formatDate($item->created_at); ?></td>
                                    <td>
                                        <form action="<?= base_url('OrderAdmin/orderPaymentReceivedPost'); ?>" method="post">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= $item->id; ?>">
                                            <div class="dropdown">
                                                <button class="btn bg-purple dropdown-toggle btn-select-option" type="button" data-toggle="dropdown"><?= trans('select_option'); ?>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu options-dropdown" style="min-width: 190px;">
                                                    <li><a href="<?= adminUrl('order-details/' . $item->id); ?>"><i class="fa fa-info option-icon"></i><?= trans('view_details'); ?></a></li>
                                                    <?php if ($item->status != 2):
                                                        if ($item->payment_status != 'payment_received'): ?>
                                                            <li>
                                                                <button type="submit" name="option" value="payment_received" class="btn-list-button"><i class="fa fa-check option-icon"></i><?= trans('payment_received'); ?></button>
                                                            </li>
                                                            <li><a href="javascript:void(0)" onclick='cancelOrder(<?= $item->id; ?>,"<?= trans("confirm_action", true); ?>");'><i class="fa fa-ban option-icon"></i><?= trans('cancel_order'); ?></a></li>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <li><a href="javascript:void(0)" onclick="deleteItem('OrderAdmin/deleteOrderPost','<?= $item->id; ?>','<?= trans("confirm_delete", true); ?>');"><i class="fa fa-trash-can option-icon"></i><?= trans('delete'); ?></a></li>
                                                </ul>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                    <?php if (empty($orders)): ?>
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