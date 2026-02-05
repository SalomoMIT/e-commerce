<div class="modal fade" id="modalBankAccounts" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("bank_accounts"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true"><i class="icon-close"></i> </span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group m-b-0">
                    <p class="text-muted"><?= trans("bank_accounts_exp"); ?></p>
                    <?= $paymentSettings->bank_transfer_accounts; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-md btn-light float-right" data-dismiss="modal"><?= trans("close"); ?></button>
            </div>
        </div>
    </div>
</div>