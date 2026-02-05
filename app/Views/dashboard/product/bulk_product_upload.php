<?= view("admin/includes/_load_dm_uploader"); ?>

<div class="row bulk-product-upload">
    <div class="col-sm-12 col-lg-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= $title; ?></h3><br>
                    <small><?= trans("bulk_product_upload_exp"); ?></small>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label"><?= trans("select_action"); ?></label>
                    <select id="select_bulk_action" name="bulk_action" class="form-control custom-select" required>
                        <option value=""><?= trans("select"); ?></option>
                        <option value="add_products"><?= trans("add_products"); ?></option>
                        <option value="edit_products"><?= trans("edit_products"); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("listing_type"); ?></label>
                    <select id="select_listing_type" name="listing_type" class="form-control custom-select" required>
                        <option value=""><?= trans("select"); ?></option>
                        <?php if ($generalSettings->marketplace_system == 1): ?>
                            <option value="sell_on_site"><?= trans('add_product_for_sale'); ?></option>
                        <?php endif;
                        if ($generalSettings->classified_ads_system == 1): ?>
                            <option value="ordinary_listing"><?= trans('add_product_services_listing'); ?></option>
                        <?php endif;
                        if ($generalSettings->bidding_system == 1): ?>
                            <option value="bidding"><?= trans('add_product_get_price_requests'); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans("currency"); ?></label>
                    <select id="select_currency" name="currency" class="form-control custom-select" required>
                        <option value=""><?= trans("select"); ?></option>
                        <?php if (!empty($currencies)):
                            foreach ($currencies as $key => $value): ?>
                                <option value="<?= $key; ?>" <?= $key == $defaultCurrency->code ? 'class="default"' : ''; ?>><?= $key . ' (' . $value->symbol . ')'; ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label"><?= trans('csv_file'); ?></label>
                    <div class="dm-uploader-container">
                        <div id="drag-and-drop-zone" class="dm-uploader dm-uploader-csv text-center">
                            <p class="dm-upload-icon">
                                <i class="fa fa-cloud-upload"></i>
                            </p>
                            <p class="dm-upload-text"><?= trans("drag_drop_file_here"); ?></p>
                            <p class="text-center">
                                <button class="btn btn-default btn-browse-files"><?= trans('browse_files'); ?></button>
                            </p>
                            <a class='btn btn-md dm-btn-select-files'>
                                <input type="file" name="file" size="40" multiple="multiple">
                            </a>
                            <ul class="dm-uploaded-files" id="files-file"></ul>
                            <button type="button" id="btn_reset_upload" class="btn btn-reset-upload"><?= trans("reset"); ?></button>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row">
                            <div id="csv_upload_spinner" class="csv-upload-spinner">
                                <strong class="text-csv-importing"><?= trans("processing"); ?></strong>
                                <strong class="text-csv-import-completed"><?= trans("completed"); ?>!</strong>
                                <div class="spinner">
                                    <div class="bounce1"></div>
                                    <div class="bounce2"></div>
                                    <div class="bounce3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="csv-uploaded-files-container">
                                <ul id="csv_uploaded_files" class="list-group csv-uploaded-files"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('help_documents'); ?></h3><br>
                    <small><?= trans("help_documents_exp"); ?></small>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <form action="<?= base_url('Bulk/downloadCsvFilesPost'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="type" value="product">
                        <button class="btn btn-success btn-block" name="submit" value="csv_template"><?= trans("download_csv_template"); ?></button>
                        <button class="btn btn-blue btn-block" name="submit" value="csv_example"><?= trans("download_csv_example"); ?></button>
                        <button type="button" class="btn btn-secondary btn-block" data-toggle="modal" data-target="#modalDocumentation"><?= trans("documentation"); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('category_id_finder'); ?></h3><br>
                    <small><?= trans("category_id_finder_exp"); ?></small>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group form-group-category">
                    <select id="categories" name="category_id[]" class="select2 form-control subcategory-select m-0" onchange="getSubCategoriesDashboard(this.value, 1, <?= selectedLangId(); ?>, true);" required>
                        <option value=""><?= trans('select_category'); ?></option>
                        <?php if (!empty($parentCategories)):
                            foreach ($parentCategories as $item): ?>
                                <option value="<?= esc($item->id); ?>"><?= esc($item->cat_name); ?>&nbsp;(ID: <?= $item->id; ?>)</option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                    <div id="category_select_container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalDocumentation" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 0;">
                <button type="button" class="close" data-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                <h4 class="modal-title"><?= trans('bulk_product_upload'); ?></h4>
            </div>
            <div class="modal-body">
                <?= $baseSettings->bulk_upload_documentation; ?>
            </div>
        </div>
    </div>
</div>

<script>
    var objCsv = {
        action: '',
        listingType: '',
        currency: ''
    };
    $(function () {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url('Bulk/uploadCsvFilePost'); ?>',
            multiple: false,
            extFilter: ['csv'],
            extraData: function (id) {
                return {
                    [MdsConfig.csrfTokenName]: $('meta[name="X-CSRF-TOKEN"]').attr('content')
                };
            },
            onDragEnter: function () {
                this.addClass('active');
            },
            onDragLeave: function () {
                this.removeClass('active');
            },
            onNewFile: function (id, file) {

                objCsv.action = $("#select_bulk_action").val();

                if (objCsv.action != 'add_products' && objCsv.action != 'edit_products') {
                    $('#select_bulk_action').addClass("is-invalid");
                    return false;
                } else {
                    $('#select_bulk_action').removeClass("is-invalid");
                }

                if (objCsv.action != 'edit_products') {
                    objCsv.listingType = $("#select_listing_type").val();
                    objCsv.currency = $("#select_currency").val();

                    if (objCsv.listingType.length < 1) {
                        $('#select_listing_type').addClass("is-invalid");
                        return false;
                    } else {
                        $('#select_listing_type').removeClass("is-invalid");
                    }

                    if (objCsv.currency.length < 1) {
                        $('#select_currency').addClass("is-invalid");
                        return false;
                    } else {
                        $('#select_currency').removeClass("is-invalid");
                    }
                }

                $("#csv_upload_spinner").show();
                $("#csv_upload_spinner .spinner").show();
                $("#csv_upload_spinner .text-csv-importing").show();
                $("#csv_upload_spinner .text-csv-import-completed").hide();
                $("#csv_uploaded_files").empty();
            },
            onUploadSuccess: function (id, data) {
                try {
                    if (data.result == 1 && data.file_name) {
                        handleCsvUpload(data.file_name);
                    } else {
                        $("#csv_upload_spinner").hide();
                    }
                } catch (e) {
                    alert("Invalid CSV file! Make sure there are no double quotes in your content.");
                }
            }
        });
    });

    function handleCsvUpload(fileName) {
        let currentIndex = 0;
        let total = 0;
        const chunkSize = 1;

        function processChunk() {
            $.ajax({
                type: 'POST',
                url: '<?= base_url('Bulk/processCsvChunk'); ?>',
                data: {
                    file_name: fileName,
                    start: currentIndex,
                    limit: chunkSize,
                    data_type: 'product',
                    bulk_action: objCsv.action,
                    listing_type: objCsv.listingType,
                    currency: objCsv.currency
                },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        currentIndex += chunkSize;

                        let percent = Math.round((Math.min(currentIndex, res.total) / res.total) * 100);
                        if (percent > 0 && percent < 100) {
                            $(".text-csv-importing").html(`
                        <div class="process">
                            <i class="fa-solid fa-cloud-arrow-up"></i>&nbsp;&nbsp;${percent}%
                        </div>`);
                        }

                        if (currentIndex < res.total) {
                            setTimeout(processChunk, 200);
                        } else {
                            $("#csv_upload_spinner .spinner").hide();
                            $("#csv_upload_spinner .text-csv-importing").hide();
                            $("#csv_upload_spinner .text-csv-import-completed").show();
                        }
                    } else {
                        $("#csv_upload_spinner .spinner").hide();
                        $("#csv_upload_spinner .text-csv-importing")
                            .text("Error: " + res.message);
                    }
                },
                error: function (xhr, status, error) {
                    $("#csv_upload_spinner .spinner").hide();
                    $("#csv_upload_spinner .text-csv-importing")
                        .text("AJAX error: " + error);
                }
            });
        }

        setTimeout(() => {
            processChunk();
        }, 500);
    }

    $(document).ready(function () {
        function toggleFields() {
            const selectedAction = $('#select_bulk_action').val();
            if (selectedAction === 'edit_products') {
                $('#select_listing_type').closest('.form-group').hide();
                $('#select_currency').closest('.form-group').hide();
            } else {
                $('#select_listing_type').closest('.form-group').show();
                $('#select_currency').closest('.form-group').show();
            }
        }

        toggleFields();

        $('#select_bulk_action').on('change', function () {
            toggleFields();
        });
    });

    $(document).on("change", "#select_listing_type", function () {
        var val = $(this).val();
        if (val == "ordinary_listing") {
            $("#select_currency").addClass("select-currency-all");
            $("#select_currency").removeClass("select-currency-default");
        } else {
            $("#select_currency").removeClass("select-currency-all");
            $("#select_currency").addClass("select-currency-default");
        }
        $("#select_listing_type").removeClass("is-invalid");
        $("#select_currency").prop('selectedIndex', 0);
    });
    $(document).on("change", "#select_currency", function () {
        $("#select_currency").removeClass("is-invalid");
    });

    $(document).on('click', '.nav-tabs-action li a', function () {
        var action = $(this).attr('data-action');
        $("#tabsBox").removeClass('tabs-box-add');
        $("#tabsBox").removeClass('tabs-box-edit');
        $("#tabsBox").addClass('tabs-box-' + action);
    });
</script>
