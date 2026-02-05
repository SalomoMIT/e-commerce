<div class="wallet-container wallet-container-table">
    <div class="table-responsive table-custom">
        <table class="table">
            <thead>
            <tr role="row">
                <th scope="col"><?= trans("payment_id"); ?></th>
                <th scope="col"><?= trans("payment_method"); ?></th>
                <th scope="col"><?= trans("deposit_amount"); ?></th>
                <th scope="col"><?= trans("date"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($deposits)): ?>
                <?php foreach ($deposits as $deposit): ?>
                    <tr>
                        <td><?= esc($deposit->payment_id); ?></td>
                        <td><?= getPaymentMethod($deposit->payment_method); ?></td>
                        <td>
                            <?php if ($deposit->payment_status == 1): ?>
                                <strong class="text-success font-600"><?= priceCurrencyFormat($deposit->deposit_amount, $deposit->currency); ?>&nbsp;(<?= $deposit->currency; ?>)</strong>
                            <?php else:
                                $showReportButton = $deposit->payment_method == 'bank_transfer' ? true : false; ?>
                                <?= priceCurrencyFormat($deposit->deposit_amount, $deposit->currency); ?>&nbsp;(<?= $deposit->currency; ?>)&nbsp;-&nbsp;<?= trans("pending_payment"); ?><br>
                                <?php $lastBankTransfer = getLastBankTransfer('wallet_deposit', $deposit->id);
                                if (!empty($lastBankTransfer)):
                                    if ($lastBankTransfer->status == 'pending'):
                                        $showReportButton = false; ?>
                                        <span class="text-info">(<?= trans("pending"); ?>)</span>
                                    <?php elseif ($lastBankTransfer->status == 'declined'): ?>
                                        <span class="text-danger">(<?= trans("bank_transfer_declined"); ?>)</span><br>
                                    <?php endif; ?>
                                <?php endif;
                                if ($showReportButton):?>
                                    <button type="button" class="btn btn-sm btn-info color-white m-t-5" data-toggle="modal" data-target="#reportBankTransferModal<?= $deposit->id; ?>">
                                        <svg width="14" height="14" viewBox="0 0 1792 1792" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1764 11q33 24 27 64l-256 1536q-5 29-32 45-14 8-31 8-11 0-24-5l-453-185-242 295q-18 23-49 23-13 0-22-4-19-7-30.5-23.5t-11.5-36.5v-349l864-1059-1069 925-395-162q-37-14-40-55-2-40 32-59l1664-960q15-9 32-9 20 0 36 11z"/>
                                        </svg>&nbsp;&nbsp;<?= trans("report_bank_transfer"); ?>
                                    </button>
                                    <?= view('order/_modal_report_bank_transfer', ['modalBankTransferId' => 'reportBankTransferModal' . $deposit->id, 'reportType' => 'wallet_deposit', 'reportItemId' => $deposit->id, 'orderNumber' => '']); ?>
                                <?php endif;
                            endif; ?>
                        </td>
                        <td class="no-wrap">
                            <?= formatDate($deposit->created_at); ?>
                            <div><a href="<?= langBaseUrl('invoice-wallet-deposit/' . $deposit->id); ?>" class="text-info link-underlined" target="_blank"><?= trans("view_invoice"); ?></a></div>
                        </td>
                    </tr>
                <?php endforeach;
            endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($deposits)): ?>
        <p class="text-center m-t-15">
            <?= trans("no_records_found"); ?>
        </p>
    <?php endif; ?>
    <div class="d-flex justify-content-center m-t-30">
        <?= $pager->links; ?>
    </div>
</div>