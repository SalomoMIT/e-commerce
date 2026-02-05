<link rel="stylesheet" href="<?= base_url('assets/vendor/datepicker/css/bootstrap-datepicker.standalone.css'); ?>">
<script src="<?= base_url('assets/vendor/datepicker/js/bootstrap-datepicker.min.js'); ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/vendor/plyr/plyr.css'); ?>">
<script src="<?= base_url('assets/vendor/plyr/plyr.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/plyr/plyr.polyfilled.min.js'); ?>"></script>

<?php $backUrl = generateDashUrl('edit_product') . '/' . $product->id; ?>
<script type="text/javascript">
    history.pushState(null, null, '<?= $_SERVER["REQUEST_URI"]; ?>');
    window.addEventListener('popstate', function (event) {
        window.location.assign('<?= $backUrl; ?>');
    });
</script>

<?php if ($product->is_draft == 1): ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="wizard-product">
                <h1 class="product-form-title"><?= trans("add_product"); ?></h1>
                <div class="row">
                    <div class="col-md-12 wizard-add-product">
                        <ul class="wizard-progress">
                            <li class="active" id="step_general"><strong><?= trans("general_information"); ?></strong></li>
                            <li class="active" id="step_dedails"><strong><?= trans("details"); ?></strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif;
if ($showShippingOptionsWarning): ?>
    <div class="alert alert-danger alert-large">
        <i class="fa fa-warning"></i>&nbsp;&nbsp;<?= trans("vendor_no_shipping_option_warning"); ?>&nbsp;<a href="<?= generateDashUrl('shipping_settings'); ?>" target="_blank" class="link-blue"><?= trans("shipping_settings"); ?></a>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12">
        <?php if ($product->is_draft != 1): ?>
            <h1 class="product-form-title"><?= trans("edit_product"); ?></h1>
        <?php endif; ?>

        <?= view('dashboard/product/_section_digital'); ?>

        <form action="<?= base_url('edit-product-details-post'); ?>" method="post" id="form_product_details" class="validate_price validate_terms" onkeypress="return event.keyCode != 13;">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= $product->id; ?>">
            <input type="hidden" name="digital_file_download_link" id="inputDigitalFileLink" value="<?= esc($product->digital_file_download_link); ?>">

            <?= view('dashboard/product/_section_license'); ?>
            <?= view('dashboard/product/_section_details'); ?>
            <?= view('dashboard/product/_section_price_stock'); ?>
            <?php if ($productSettings->marketplace_variations && $product->listing_type !== 'ordinary_listing'): ?>
                <?= view('dashboard/product/product-options/_section_product_options'); ?>
            <?php endif; ?>
            <?= view('dashboard/product/_section_preview'); ?>
            <?= view('dashboard/product/_section_location_shipping'); ?>

            <div class="row">
                <div class="col-sm-12 text-left m-t-15 m-b-15">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox custom-control-validate-input">
                            <?php if ($product->is_draft == 1): ?>
                                <input type="checkbox" class="custom-control-input" name="terms_conditions" id="terms_conditions" value="1" required>
                            <?php else: ?>
                                <input type="checkbox" class="custom-control-input" name="terms_conditions" id="terms_conditions" value="1" checked>
                            <?php endif; ?>
                            <label for="terms_conditions" class="custom-control-label"><?= trans("terms_conditions_exp"); ?>&nbsp;
                                <?php $pageTerms = getPageByDefaultName('terms_conditions', selectedLangId());
                                if (!empty($pageTerms)): ?>
                                    <a href="<?= generateUrl($pageTerms->page_default_name); ?>" class="link-terms" target="_blank"><strong><?= esc($pageTerms->title); ?></strong></a>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group m-t-15 buttons-product-form">
                        <a href="<?= generateDashUrl('edit_product') . '/' . $product->id; ?>" class="btn btn-lg btn-dark pull-left"><i class="fa fa-long-arrow-left"></i>&nbsp;&nbsp;<?= trans("back"); ?></a>
                        <?php if ($product->is_draft == 1): ?>
                            <button type="submit" name="submit" value="submit" class="btn btn-lg btn-success btn-form-product-details pull-right"><i class="fa fa-check"></i>&nbsp;&nbsp;<?= trans("submit"); ?></button>
                            <button type="submit" name="submit" value="save_as_draft" class="btn btn-lg btn-secondary btn-form-product-details m-r-10 pull-right"><i class="fa fa-file"></i>&nbsp;&nbsp;<?= trans("save_as_draft"); ?></button>
                        <?php else: ?>
                            <button type="submit" name="submit" value="save_changes" class="btn btn-lg btn-success btn-form-product-details pull-right"><i class="fa fa-check"></i>&nbsp;&nbsp;<?= trans("save_changes"); ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>


</div>


<style>
    .section-product-details {
        background-color: #fff;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0px 3px 4px 0px rgba(0, 0, 0, 0.03);
    }

    .form-box-last {
        padding: 0;
        margin: 0;
        border: 0;
        padding-bottom: 30px;
    }

    .custom-options-container {
        border: 1px solid #f1f1f1;
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
    }

    .datepicker td.day.active,
    .datepicker td.day.active:hover {
        background: #c6c6c6 !important;
        color: #333 !important;
    }
</style>

<script>
    const player = new Plyr('#player');
    $(document).ajaxStop(function () {
        const player = new Plyr('#player');
    });
    const audio_player = new Plyr('#audio_player');
    $(document).ajaxStop(function () {
        const player = new Plyr('#audio_player');
    });
    $(window).on("load", function () {
        $(".li-dm-media-preview").css("visibility", "visible");
    });

    $.fn.datepicker.dates['en'] = {
        days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        daysMin: ["<?= substr(trans("monday", true), 0, 3); ?>",
            "<?= substr(trans("tuesday", true), 0, 3); ?>",
            "<?= substr(trans("wednesday", true), 0, 3); ?>",
            "<?= substr(trans("thursday", true), 0, 3); ?>",
            "<?= substr(trans("friday", true), 0, 3); ?>",
            "<?= substr(trans("saturday", true), 0, 3); ?>",
            "<?= substr(trans("sunday", true), 0, 3); ?>"],
        months: ['<?= trans("january", true); ?>',
            "<?= trans("february", true); ?>",
            "<?= trans("march", true); ?>",
            "<?= trans("april", true); ?>",
            "<?= trans("may", true); ?>",
            "<?= trans("june", true); ?>",
            "<?= trans("july", true); ?>",
            "<?= trans("august", true); ?>",
            "<?= trans("september", true); ?>",
            "<?= trans("october", true); ?>",
            "<?= trans("november", true); ?>",
            "<?= trans("december", true); ?>"],
        monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        today: "Today",
        clear: "Clear",
        format: "mm/dd/yyyy",
        titleFormat: "MM yyyy",
        weekStart: 0
    };
    $('.datepicker').datepicker({
        language: 'en'
    });
    //validate checkbox
    $(document).on("click", ".btn-form-product-details ", function () {
        $('.checkbox-options-container').each(function () {
            var fieldId = $(this).attr('data-custom-field-id');
            var element = "#checkbox_options_container_" + fieldId + " .required-checkbox";
            if (!$(element).is(':checked')) {
                $(element).prop('required', true);
            } else {
                $(element).prop('required', false);
            }
        });
    });

    $(document).ready(function () {
        $('.validate_terms').submit(function (e) {
            $('.custom-control-validate-input p').remove();
            if (!$('.custom-control-validate-input input').is(":checked")) {
                e.preventDefault();
                $('.custom-control-validate-input').addClass('custom-control-validate-error');
                $('.custom-control-validate-input').append("<p class='text-danger'>" + MdsConfig.text.acceptTerms + "</p>");
            } else {
                $('.custom-control-validate-input').removeClass('custom-control-validate-error');
            }
        });
    });

    window.addEventListener('keydown', function (e) {
        if (e.keyIdentifier == 'U+000A' || e.keyIdentifier == 'Enter' || e.keyCode == 13) {
            if (e.target.nodeName == 'INPUT' && e.target.type == 'text') {
                e.preventDefault();
                return false;
            }
        }
    }, true);
</script>
