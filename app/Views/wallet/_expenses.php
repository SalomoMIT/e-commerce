<div class="wallet-container wallet-container-table">
    <div class="table-responsive table-custom">
        <table class="table">
            <thead>
            <tr role="row">
                <th scope="col"><?= trans("payment_id"); ?></th>
                <th scope="col"><?= trans("expense"); ?></th>
                <th scope="col"><?= trans("expense_amount"); ?></th>
                <th scope="col"><?= trans("date"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($expenses)): ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= esc($expense->payment_id); ?></td>
                        <td>
                            <?php if ($expense->expense_type == 'sale'): ?>
                                <?= trans("purchase"); ?>&nbsp;(<?= trans("order") . ' #' . esc($expense->expense_item_id); ?>)
                            <?php elseif ($expense->expense_type == 'membership'): ?>
                                <?= trans("membership_plan"); ?>&nbsp;(<?= esc($expense->expense_detail); ?>)
                            <?php elseif ($expense->expense_type == 'commission_debt'):
                                echo trans("commission_debt");
                            elseif ($expense->expense_type == 'promote'):
                                echo trans("product_promotion");
                                $promote = unserializeData($expense->expense_detail);
                                if (!empty($promote) && !empty($promote['product_id']) && !empty($promote['plan_type']) && !empty($promote['day_count'])) {
                                    echo '&nbsp;(' . trans("product") . ' #' . $promote['product_id'] . ' | ' . trans("purchased_plan") . ': ' . trans($promote['plan_type']) . ', ' . $promote['day_count'] . ' ' . trans("days") . ')';
                                } ?>
                            <?php endif; ?>
                        </td>
                        <td><strong class="text-danger font-600"><?= priceFormatted($expense->expense_amount, $expense->currency); ?>&nbsp;(<?= $expense->currency; ?>)</strong></td>
                        <td class="no-wrap"><?= formatDate($expense->created_at); ?>
                            <?php if ($expense->expense_type == 'sale'): ?>
                                <div><a href="<?= langBaseUrl('invoice/' . esc($expense->expense_item_id)); ?>?type=buyer" class="text-info link-underlined" target="_blank"><?= trans("view_invoice"); ?></a></div>
                            <?php elseif ($expense->expense_type == 'membership'): ?>
                                <div><a href="<?= langBaseUrl('invoice-membership/' . esc($expense->expense_item_id)); ?>" class="text-info link-underlined" target="_blank"><?= trans("view_invoice"); ?></a></div>
                            <?php elseif ($expense->expense_type == 'commission_debt'): ?>
                                <div><a href="<?= langBaseUrl('invoice-expense/' . esc($expense->id)); ?>" class="text-info link-underlined" target="_blank"><?= trans("view_invoice"); ?></a></div>
                            <?php elseif ($expense->expense_type == 'promote'): ?>
                                <div><a href="<?= langBaseUrl('invoice-promotion/' . esc($expense->expense_item_id)); ?>" class="text-info link-underlined" target="_blank"><?= trans("view_invoice"); ?></a></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach;
            endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($expenses)): ?>
        <p class="text-center m-t-15">
            <?= trans("no_records_found"); ?>
        </p>
    <?php endif; ?>
    <div class="d-flex justify-content-center m-t-30">
        <?= $pager->links; ?>
    </div>
</div>