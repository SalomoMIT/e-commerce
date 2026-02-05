<div class="modal fade" id="manageOptionImagesModal" tabindex="-1" role="dialog" aria-labelledby="manageOptionImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                <h4 class="modal-title" id="manageOptionImagesModalLabel"></h4></div>
            <div class="modal-body">

                <h6 class="font-600"><?= trans("selected_images"); ?>:</h6>
                <div id="currentOptionImagesList" class="option-image-list"></div>
                <hr>

                <h6 class="font-600"><?= trans("images"); ?>:</h6>
                <div id="uploadedOptionImagesList" class="option-image-list"></div>
                <hr>

                <div class="dm-uploader-container">
                    <div id="drag-and-drop-zone" class="dm-uploader text-center">
                        <p class="dm-upload-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                                <path fill="currentColor"
                                      d="M14.702 28.838c-1.757 0-3.054-.031-4.248-.061c-1.014-.024-1.954-.047-3.043-.047a6.454 6.454 0 0 1-6.447-6.446a6.4 6.4 0 0 1 2.807-5.321a10.6 10.6 0 0 1-.217-2.138C3.554 8.983 8.307 4.23 14.15 4.23c3.912 0 7.495 2.164 9.332 5.574a6.4 6.4 0 0 1 4.599-1.929a6.454 6.454 0 0 1 6.258 8.008a6.45 6.45 0 0 1 4.699 6.207a6.455 6.455 0 0 1-6.447 6.448c-1.661 0-2.827.013-3.979.024c-1.126.012-2.239.024-3.784.024a.5.5 0 0 1 0-1c1.541 0 2.65-.012 3.773-.024c1.155-.012 2.325-.024 3.99-.024a5.447 5.447 0 0 0 1.025-10.798a.5.5 0 0 1-.379-.653a5.452 5.452 0 0 0-5.156-7.213a5.41 5.41 0 0 0-4.318 2.129a.498.498 0 0 1-.852-.101a9.62 9.62 0 0 0-8.76-5.674c-5.291 0-9.596 4.304-9.596 9.595c0 .76.09 1.518.267 2.252a.5.5 0 0 1-.227.545a5.41 5.41 0 0 0-2.63 4.662a5.453 5.453 0 0 0 5.447 5.446c1.098 0 2.045.022 3.067.048c1.188.028 2.477.06 4.224.06a.5.5 0 1 1-.001 1.002"/>
                                <path fill="currentColor" d="M26.35 22.456a.5.5 0 0 1-.347-.14l-6.777-6.535l-6.746 6.508a.5.5 0 1 1-.694-.721l7.093-6.841a.5.5 0 0 1 .694-.001l7.123 6.869a.5.5 0 0 1-.346.861"/>
                                <path fill="currentColor" d="M19.226 35.769a.5.5 0 0 1-.5-.5V15.087a.5.5 0 0 1 1 0V35.27a.5.5 0 0 1-.5.499"/>
                            </svg>
                        </p>
                        <p class="dm-upload-text"><?= trans("drag_drop_images_here"); ?>&nbsp;<span style="text-decoration: underline"><?= trans('browse_files'); ?></span></p>
                        <a class='btn btn-md dm-btn-select-files'>
                            <input type="file" name="file" size="40" multiple="multiple">
                        </a>
                        <ul class="dm-uploaded-files option-image-list option-image-list-uploading" id="files-image"></ul>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= trans("cancel"); ?></button>
                <button type="button" class="btn btn-primary" id="saveOptionImagesButton"><?= trans("save_changes"); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="files-template-image">
    <li class="media">
        <img class="preview-img" alt="bg">
        <div class="media-body">
            <div class="progress">
                <div class="dm-progress-waiting"><?= trans("waiting"); ?></div>
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </li>
</script>


<script>
    function initImageUploader() {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url('File/uploadImage'); ?>',
            maxFileSize: <?= $productSettings->max_file_size_image; ?>,
            queue: true,
            allowedTypes: 'image/*',
            extFilter: ["jpg", "jpeg", "webp", "png", "gif"],
            extraData: function (id) {
                return {
                    'product_id': <?= $product->id; ?>,
                    'is_option_image': 1,
                    [MdsConfig.csrfTokenName]: $('meta[name="X-CSRF-TOKEN"]').attr('content')
                };
            },
            onNewFile: function (id, file) {
                ui_multi_add_file(id, file, 'image');
                if (typeof FileReader !== 'undefined') {
                    var reader = new FileReader();
                    var img = $('#uploaderFile' + id).find('img');
                    reader.onload = function (e) {
                        img.attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            },
            onBeforeUpload: function (id) {
                $('#uploaderFile' + id + ' .dm-progress-waiting').hide();
                ui_multi_update_file_progress(id, 0, '', true);
                ui_multi_update_file_status(id, 'uploading', 'Uploading...');
            },
            onUploadProgress: function (id, percent) {
                ui_multi_update_file_progress(id, percent);
            },
            onUploadSuccess: function (id, data) {
                loadUploadedOptionImages(id);
            },
            onUploadError: function (id, xhr, status, message) {
                if (message == 'Not Acceptable') {
                    $("#uploaderFile" + id).remove();
                    $(".error-message-img-upload").show();
                    setTimeout(function () {
                        $(".error-message-img-upload").fadeOut("slow");
                    }, 4000)
                }
            },
            onFileSizeError: function (file) {
                Swal.fire({
                    text: "<?= trans('file_too_large', true) . ' ' . formatSizeUnits($productSettings->max_file_size_image); ?>",
                    icon: 'warning',
                    confirmButtonText: MdsConfig.text.ok
                });
            },
            onFileTypeError: function (file) {
                Swal.fire({
                    text: "<?= trans('invalid_file_type', true); ?>",
                    icon: 'warning',
                    confirmButtonText: MdsConfig.text.ok
                });
            },
            onFileExtError: function (file) {
                Swal.fire({
                    text: "<?= trans('invalid_file_type', true); ?>",
                    icon: 'warning',
                    confirmButtonText: MdsConfig.text.ok
                });
            }
        });
    }

    $(document).ready(function () {
        initImageUploader();
    });

    $(document).ajaxStop(function () {
        initImageUploader();
    });
</script>

