<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-8">
        <div class="box">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= esc($title); ?></h3>
                </div>
            </div>
            <div class="box-body">
                <form action="<?= base_url('Dashboard/affiliateProgramPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <div class="form-group">
                        <label><?= trans("status"); ?></label>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="vendor_affiliate_status" value="1" id="affiliate_status_1" class="custom-control-input" <?= user()->vendor_affiliate_status == 1 ? 'checked' : ''; ?>>
                                    <label for="affiliate_status_1" class="custom-control-label"><?= trans("enable_for_all_products"); ?></label>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="vendor_affiliate_status" value="2" id="affiliate_status_2" class="custom-control-input" <?= user()->vendor_affiliate_status == 2 ? 'checked' : ''; ?>>
                                    <label for="affiliate_status_2" class="custom-control-label"><?= trans("enable_only_for_selected_products"); ?></label>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="vendor_affiliate_status" value="0" id="affiliate_status_3" class="custom-control-input" <?= user()->vendor_affiliate_status == 0 ? 'checked' : ''; ?>>
                                    <label for="affiliate_status_3" class="custom-control-label"><?= trans("disable"); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= trans('referrer_commission_rate'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">%</span>
                            <input type="number" name="affiliate_commission_rate" class="form-control" min="0" max="99" step="0.01" value="<?= user()->affiliate_commission_rate; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= trans('buyer_discount_rate'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">%</span>
                            <input type="number" name="affiliate_discount_rate" class="form-control" min="0" max="99" step="0.01" value="<?= user()->affiliate_discount_rate; ?>" required>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" name="submit" value="update" class="btn btn-md btn-success"><?= trans("save_changes") ?></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="alert alert-info alert-large">
            <strong><i class="fa fa-info-circle"></i></strong>&nbsp;&nbsp;<?= trans("affiliate_program_vendor_exp"); ?>
        </div>
    </div>
</div>