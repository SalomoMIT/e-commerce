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
                            <form action="<?= adminUrl('membership-payments'); ?>" method="get">
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
                                    <label><?= trans('payment_status'); ?></label>
                                    <select name="payment_status" class="form-control custom-select">
                                        <option value="" selected><?= trans("all"); ?></option>
                                        <option value="payment_received" <?= inputGet('payment_status') == 'payment_received' ? 'selected' : ''; ?>><?= trans("payment_received"); ?></option>
                                        <option value="pending_payment" <?= inputGet('payment_status') == 'pending_payment' ? 'selected' : ''; ?>><?= trans("pending_payment"); ?></option>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("payment_id"); ?></label>
                                    <input name="q" class="form-control" placeholder="<?= trans("payment_id"); ?>" type="search" value="<?= esc(inputGet('q')); ?>">
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
                    <table class="table table-bordered table-striped" role="grid">
                        <thead>
                        <tr role="row">
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("payment_id"); ?></th>
                            <th><?= trans("payment_method"); ?></th>
                            <th><?= trans("payment_amount"); ?></th>
                            <th><?= trans("payment_status"); ?></th>
                            <th><?= trans("user"); ?></th>
                            <th><?= trans("membership_plan"); ?></th>
                            <th><?= trans("ip_address"); ?></th>
                            <th><?= trans("date"); ?></th>
                            <th class="max-width-120"><?= trans("options"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($transactions)):
                            foreach ($transactions as $item): ?>
                                <tr>
                                    <td><?= $item->id; ?></td>
                                    <td><?= esc($item->payment_id); ?></td>
                                    <td><?= getPaymentMethod($item->payment_method); ?></td>
                                    <td> <?= priceCurrencyFormat($item->payment_amount, $item->currency); ?>&nbsp;(<?= esc($item->currency); ?>)</td>
                                    <td>
                                        <?= getPaymentStatus($item->payment_status); ?>
                                        <?php if ($item->payment_status == "pending_payment"): ?>
                                            <form action="<?= base_url('Admin/approveMembershipPaymentPost'); ?>" method="post">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= $item->id; ?>">
                                                <button type="submit" class="btn btn-sm btn-success m-t-5"><i class="fa fa-check"></i>&nbsp;<?= trans("approve"); ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= generateProfileUrl($item->user_slug); ?>" target="_blank" class="table-link">
                                            <?= esc($item->user_username); ?>
                                        </a>
                                    </td>
                                    <td><?= esc($item->plan_title); ?></td>
                                    <td><?= $item->ip_address; ?></td>
                                    <td><?= formatDate($item->created_at); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-option">
                                            <a href="<?= base_url('invoice-membership/' . $item->id); ?>" class="btn btn-sm btn-default btn-edit" target="_blank"><i class="fa fa-file-text"></i>&nbsp;&nbsp;<?= trans("view_invoice"); ?></a>
                                            <a href="javascript:void(0)" class="btn btn-default btn-delete" onclick="deleteItem('Admin/deleteMembershipPaymentPost','<?= $item->id; ?>','<?= trans("confirm_delete"); ?>');"><i class="fa fa-trash-can"></i></a>
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
                </div>
            </div>
            <div class="col-sm-12">
                <?php if (!empty($transactions)): ?>
                    <div class="number-of-entries">
                        <span><?= trans("number_of_entries"); ?>:</span>&nbsp;&nbsp;<strong><?= $numRows; ?></strong>
                    </div>
                <?php endif; ?>
                <div class="pull-right">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>