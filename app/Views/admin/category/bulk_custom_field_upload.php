<?= view("admin/includes/_load_dm_uploader"); ?>

<div class="row">
    <div class="col-sm-12 col-lg-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= $title; ?></h3><br>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('custom-fields'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-bars"></i>
                        <?= trans('custom_fields'); ?>
                    </a>
                </div>
            </div>
            <div class="box-body">
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

                    <div class="row">
                        <div class="col-sm-12">
                            <div id="csv_upload_spinner" class="csv-upload-spinner">
                                <div class="clearfix"></div>
                                <div class="text-csv-importing"><strong><?= trans("processing"); ?></strong></div>
                                <strong class="text-csv-import-completed"><?= trans("completed"); ?>!</strong>
                                <div class="spinner">
                                    <div class="bounce1"></div>
                                    <div class="bounce2"></div>
                                    <div class="bounce3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
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
                        <input type="hidden" name="type" value="custom_field">
                        <button class="btn btn-default btn-block" name="submit" value="csv_template"><?= trans("download_csv_template"); ?></button>
                        <button class="btn btn-default btn-block" name="submit" value="csv_example"><?= trans("download_csv_example"); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
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
        const chunkSize = 50;

        function processChunk() {
            $.ajax({
                type: 'POST',
                url: '<?= base_url('Bulk/processCsvChunk'); ?>',
                data: {
                    file_name: fileName,
                    start: currentIndex,
                    limit: chunkSize,
                    data_type: 'custom_field'
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
</script>