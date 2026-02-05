<div class="box">
    <div class="box-header with-border">
        <div class="left">
            <h3 class="box-title"><?= esc($title); ?></h3>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-striped" role="grid">
                        <thead>
                        <tr role="row">
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("payment_id"); ?></th>
                            <th><?= trans("payment_method"); ?></th>
                            <th><?= trans("product_id"); ?></th>
                            <th><?= trans("payment_amount"); ?></th>
                            <th><?= trans("payment_status"); ?></th>
                            <th><?= trans("purchased_plan"); ?></th>
                            <th><?= trans("date"); ?></th>
                            <th class="max-width-120"><?= trans('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td style="width: 5%;"><?= $transaction->id; ?></td>
                                    <td><?= esc($transaction->payment_id); ?></td>
                                    <td><?= getPaymentMethod($transaction->payment_method); ?></td>
                                    <td><?= esc($transaction->product_id); ?></td>
                                    <td><?= esc($transaction->payment_amount); ?>&nbsp;(<?= esc($transaction->currency); ?>)</td>
                                    <td>
                                        <?= getPaymentStatus($transaction->payment_status); ?><br>
                                        <?php if ($transaction->payment_method == 'bank_transfer' && $transaction->payment_status == 'pending_payment'):
                                            $showReportButton = true;
                                            $lastBankTransfer = getLastBankTransfer('promote', $transaction->id);
                                            if (!empty($lastBankTransfer)):
                                                if ($lastBankTransfer->status == 'pending'):
                                                    $showReportButton = false; ?>
                                                    <span class="text-primary">(<?= trans("pending"); ?>)</span>
                                                <?php elseif ($lastBankTransfer->status == 'declined'): ?>
                                                    <span class="text-danger">(<?= trans("bank_transfer_declined"); ?>)</span>
                                                <?php endif; ?>
                                            <?php endif;
                                            if ($showReportButton):?>
                                                <button type="button" class="btn btn-sm btn-primary color-white m-t-5" data-toggle="modal" data-target="#reportBankTransferModal<?= $transaction->id; ?>">
                                                    <svg width="14" height="14" viewBox="0 0 1792 1792" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1764 11q33 24 27 64l-256 1536q-5 29-32 45-14 8-31 8-11 0-24-5l-453-185-242 295q-18 23-49 23-13 0-22-4-19-7-30.5-23.5t-11.5-36.5v-349l864-1059-1069 925-395-162q-37-14-40-55-2-40 32-59l1664-960q15-9 32-9 20 0 36 11z"/>
                                                    </svg>&nbsp;&nbsp;<?= trans("report_bank_transfer"); ?>
                                                </button>
                                                <?= view('order/_modal_report_bank_transfer', ['modalBankTransferId' => 'reportBankTransferModal' . $transaction->id, 'reportType' => 'promote', 'reportItemId' => $transaction->id, 'orderNumber' => '']); ?>
                                            <?php endif;
                                        endif; ?>
                                    </td>
                                    <td><?= esc($transaction->purchased_plan); ?></td>
                                    <td class="white-space-nowrap" style="width: 15%"><?= formatDate($transaction->created_at); ?></td>
                                    <td><a href="<?= langBaseUrl('invoice-promotion/' . $transaction->id); ?>" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-file-text"></i>&nbsp;&nbsp;<?= trans("view_invoice"); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($transactions)): ?>
                    <p class="text-center">
                        <?= trans("no_records_found"); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php if (!empty($transactions)): ?>
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