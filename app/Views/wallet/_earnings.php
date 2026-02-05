<div class="wallet-container wallet-container-table">
    <div class="table-responsive table-custom">
        <table class="table">
            <thead>
            <tr role="row">
                <th scope="col"><?= trans("order"); ?></th>
                <th scope="col"><?= trans("total"); ?></th>
                <th scope="col"><?= trans("vat"); ?></th>
                <th scope="col"><?= trans("commissions_discounts"); ?></th>
                <th scope="col"><?= trans("shipping_cost"); ?></th>
                <th scope="col"><?= trans("earned_amount"); ?></th>
                <th scope="col"><?= trans("date"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($earnings)): ?>
                <?php foreach ($earnings as $earning):
                    $order = getOrderByOrderNumber($earning->order_number); ?>
                    <tr>
                        <td>#<?= $earning->order_number; ?></td>
                        <td><?= priceFormatted($earning->sale_amount, $earning->currency); ?>&nbsp;(<?= esc($earning->currency); ?>)</td>
                        <td><?= priceFormatted($earning->vat_amount, $earning->currency); ?>&nbsp;<?= !empty($earning->vat_rate) ? '(' . $earning->vat_rate . '%)' : ''; ?></td>
                        <td>
                            <div class="font-size-13">
                                <?= trans("commission"); ?>:&nbsp;<span class="text-danger"><?= priceFormatted($earning->commission, $earning->currency); ?>&nbsp;<?= !empty($earning->commission_rate) ? '(' . $earning->commission_rate . '%)' : ''; ?></span>
                            </div>
                            <?php if (!empty($earning->affiliate_commission)): ?>
                                <div class="font-size-13 m-t-5">
                                    <?= trans("referrer_commission"); ?>:&nbsp;<span class="text-danger"><?= priceFormatted($earning->affiliate_commission, $earning->currency); ?>&nbsp;(<?= $earning->affiliate_commission_rate; ?>%)</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($earning->affiliate_discount)): ?>
                                <div class="font-size-13 m-t-5">
                                    <?= trans("referral_discount"); ?>:&nbsp;<span class="text-danger"><?= priceFormatted($earning->affiliate_discount, $earning->currency); ?>&nbsp;(<?= $earning->affiliate_discount_rate; ?>%)</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($earning->coupon_discount)): ?>
                                <div class="font-size-13 m-t-5">
                                    <?= trans("discount_coupon"); ?>:&nbsp;<span class="text-danger"><?= priceFormatted($earning->coupon_discount, $earning->currency); ?></span>
                                    <?php if (!empty($order) && !empty($order->coupon_code)):
                                        echo ' (' . $order->coupon_code . ')';
                                    endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= priceFormatted($earning->shipping_cost, $earning->currency); ?></td>
                        <td>
                            <?php if (!empty($order) && $order->payment_method == 'cash_on_delivery'): ?>
                                <span class="text-danger"><?= trans("cash_on_delivery"); ?></span>
                            <?php else: ?>
                                <strong class="text-success font-600"><?= priceFormatted($earning->earned_amount, $earning->currency); ?>&nbsp;(<?= $earning->currency; ?>)</strong>

                                <?php if ($paymentSettings->currency_converter == 1 && $earning->exchange_rate > 0 && $earning->exchange_rate != 1):
                                    $totalEarned = numToDecimal($earning->earned_amount);
                                    $totalEarned = $totalEarned / $earning->exchange_rate;
                                    $totalEarned = number_format($totalEarned, 2, '.', ''); ?>
                                    <span>(<?= $totalEarned . ' ' . $defaultCurrency->code; ?>)</span>
                                <?php endif;
                            endif;
                            if ($earning->is_refunded == 1): ?>
                                <br><span class="text-danger">(<?= trans("refund"); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-wrap"><?= formatDate($earning->created_at); ?></td>
                    </tr>
                <?php endforeach;
            endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($earnings)): ?>
        <p class="text-center m-t-15">
            <?= trans("no_records_found"); ?>
        </p>
    <?php endif; ?>
    <div class="d-flex justify-content-center m-t-30">
        <?= $pager->links; ?>
    </div>
</div>