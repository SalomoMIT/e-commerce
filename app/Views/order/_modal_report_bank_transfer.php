<div class="modal fade" id="<?= $modalBankTransferId; ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-custom">
            <form action="<?= base_url('Home/bankTransferPaymentReportPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= trans("report_bank_transfer"); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"><i class="icon-close"></i> </span>
                    </button>
                </div>
                <div class="modal-body pb-0">
                    <input type="hidden" name="report_type" class="form-control form-input" value="<?= esc($reportType); ?>">
                    <input type="hidden" name="report_item_id" class="form-control form-input" value="<?= esc($reportItemId); ?>">
                    <input type="hidden" name="order_number" class="form-control form-input" value="<?= esc($orderNumber); ?>">
                    <input type="hidden" name="back_url" class="form-control" value="<?= getCurrentUrl(); ?>">

                    <?php if (empty($hideBankAccounts)): ?>
                        <div class="form-group m-b-0">
                            <p class="text-muted"><?= trans("bank_accounts_exp"); ?></p>
                            <?= $paymentSettings->bank_transfer_accounts; ?>
                            <hr>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="control-label"><?= trans("payment_note"); ?></label>
                        <textarea name="payment_note" class="form-control form-textarea" maxlength="499" style="min-height: 60px;" placeholder="<?= trans("payment_note"); ?>"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("receipt"); ?>
                            <small>(.png, .jpg, .jpeg, .pdf)</small>
                        </label>
                        <div>
                            <a class='btn btn-md btn-secondary btn-file-upload'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-file-earmark-fill" viewBox="0 0 16 16">
                                    <path d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2z"/>
                                </svg>&nbsp;&nbsp;<?= trans('select_file'); ?>
                                <input type="file" name="file" size="40" accept=".png, .jpg, .jpeg, .pdf" onchange="$('#upload-file-info').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            <br>
                            <span class='badge badge-info' id="upload-file-info"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-custom btn-submit-bank-transfer float-right"><?= trans("submit"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>