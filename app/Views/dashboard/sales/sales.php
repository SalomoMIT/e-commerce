<div class="box">
    <div class="box-header with-border">
        <div class="left">
            <h3 class="box-title"><?= esc($title); ?></h3>
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
                            <form action="<?= generateDashUrl('sales'); ?>" method="get" id="formVendorSales">
                                <?php if (!empty(inputGet('st'))): ?>
                                    <input type="hidden" name="st" value="<?= esc(inputGet('st')); ?>">
                                <?php endif;
                                if ($page == 'sales'): ?>
                                    <div class="item-table-filter">
                                        <label><?= trans("payment_status"); ?></label>
                                        <select name="payment_status" class="form-control custom-select">
                                            <option value="" selected><?= trans("all"); ?></option>
                                            <option value="payment_received" <?= inputGet('payment_status') == 'payment_received' ? 'selected' : ''; ?>><?= trans("payment_received"); ?></option>
                                            <option value="pending_payment" <?= inputGet('payment_status') == 'pending_payment' ? 'selected' : ''; ?>><?= trans("pending_payment"); ?></option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div class="item-table-filter item-table-filter-large">
                                    <label><?= trans("search"); ?></label>
                                    <div class="item-table-filter-search">
                                        <input name="q" class="form-control" placeholder="<?= trans("sale_id"); ?>" type="search" value="<?= strSlug(esc(inputGet('q'))); ?>">
                                        <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                        <div class="btn-group table-export">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-table-export" data-toggle="dropdown"><?= trans("export"); ?>&nbsp;&nbsp;<i class="fa fa-caret-down"></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formVendorSales" data-export-type="vendor_sales" data-export-file-type="csv" data-section="vn">CSV</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formVendorSales" data-export-type="vendor_sales" data-export-file-type="xml" data-section="vn">XML</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="btn-export-data" data-export-form="formVendorSales" data-export-type="vendor_sales" data-export-file-type="excel" data-section="vn"><?= trans("excel"); ?>&nbsp;(.xlsx)</button>
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
                    <table class="table table-striped" role="grid">
                        <thead>
                        <tr role="row">
                            <th scope="col"><?= trans("sale"); ?></th>
                            <th scope="col"><?= trans("total"); ?></th>
                            <th scope="col"><?= trans("payment_status"); ?></th>
                            <th scope="col"><?= trans("status"); ?></th>
                            <th scope="col"><?= trans("date"); ?></th>
                            <th scope="col"><?= trans("options"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($sales)): ?>
                            <?php foreach ($sales as $sale):
                                $finalPrice = getSellerFinalPrice($sale->id);
                                if (!empty($sale)):?>
                                    <tr>
                                        <td>#<?= $sale->order_number; ?></td>
                                        <td><?= priceFormatted($finalPrice, $sale->price_currency); ?></td>
                                        <td>
                                            <?php if ($sale->payment_status == 'payment_received'):
                                                echo trans("payment_received");
                                            else:
                                                echo trans("pending_payment");
                                            endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sale->status == 2): ?>
                                                <label class="label label-danger"><?= trans("cancelled"); ?></label>
                                            <?php else:
                                                if ($page == 'sales'): ?>
                                                    <label class="label label-success"><?= trans("order_processing"); ?></label>
                                                <?php else: ?>
                                                    <label class="label label-default"><?= trans("completed"); ?></label>
                                                <?php endif;
                                            endif; ?>
                                        </td>
                                        <td><?= formatDate($sale->created_at); ?></td>
                                        <td>
                                            <a href="<?= generateDashUrl('sale'); ?>/<?= esc($sale->order_number); ?>" class="btn btn-sm btn-default btn-details"><i class="fa fa-info-circle" aria-hidden="true"></i><?= trans("details"); ?></a>
                                        </td>
                                    </tr>
                                <?php endif;
                            endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($sales)): ?>
                    <p class="text-center">
                        <?= trans("no_records_found"); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php if (!empty($sales)): ?>
                    <div class="number-of-entries">
                        <span><?= trans("number_of_entries"); ?>:</span>&nbsp;&nbsp;<strong><?= $numRows; ?></strong>
                    </div>
                <?php endif; ?>
                <div class="table-pagination">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>