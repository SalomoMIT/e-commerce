<div class="modal fade" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-file-manager" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= trans("images"); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="file-manager">
                    <div class="file-manager-left">
                        <div class="dm-uploader-container">
                            <div id="drag-and-drop-zone-file-manager" class="dm-uploader text-center">
                                <p class="file-manager-file-types">
                                    <span>JPG</span>
                                    <span>JPEG</span>
                                    <span>WEBP</span>
                                    <span>PNG</span>
                                </p>
                                <p class="dm-upload-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                                        <path fill="currentColor"
                                              d="M14.702 28.838c-1.757 0-3.054-.031-4.248-.061c-1.014-.024-1.954-.047-3.043-.047a6.454 6.454 0 0 1-6.447-6.446a6.4 6.4 0 0 1 2.807-5.321a10.6 10.6 0 0 1-.217-2.138C3.554 8.983 8.307 4.23 14.15 4.23c3.912 0 7.495 2.164 9.332 5.574a6.4 6.4 0 0 1 4.599-1.929a6.454 6.454 0 0 1 6.258 8.008a6.45 6.45 0 0 1 4.699 6.207a6.455 6.455 0 0 1-6.447 6.448c-1.661 0-2.827.013-3.979.024c-1.126.012-2.239.024-3.784.024a.5.5 0 0 1 0-1c1.541 0 2.65-.012 3.773-.024c1.155-.012 2.325-.024 3.99-.024a5.447 5.447 0 0 0 1.025-10.798a.5.5 0 0 1-.379-.653a5.452 5.452 0 0 0-5.156-7.213a5.41 5.41 0 0 0-4.318 2.129a.498.498 0 0 1-.852-.101a9.62 9.62 0 0 0-8.76-5.674c-5.291 0-9.596 4.304-9.596 9.595c0 .76.09 1.518.267 2.252a.5.5 0 0 1-.227.545a5.41 5.41 0 0 0-2.63 4.662a5.453 5.453 0 0 0 5.447 5.446c1.098 0 2.045.022 3.067.048c1.188.028 2.477.06 4.224.06a.5.5 0 1 1-.001 1.002"/>
                                        <path fill="currentColor" d="M26.35 22.456a.5.5 0 0 1-.347-.14l-6.777-6.535l-6.746 6.508a.5.5 0 1 1-.694-.721l7.093-6.841a.5.5 0 0 1 .694-.001l7.123 6.869a.5.5 0 0 1-.346.861"/>
                                        <path fill="currentColor" d="M19.226 35.769a.5.5 0 0 1-.5-.5V15.087a.5.5 0 0 1 1 0V35.27a.5.5 0 0 1-.5.499"/>
                                    </svg>
                                </p>
                                <p class="dm-upload-text"><?= trans("drag_drop_images_here"); ?></p>
                                <p class="text-center">
                                    <button class="btn btn-default btn-browse-files"><?= trans('browse_files'); ?></button>
                                </p>
                                <a class='btn btn-md dm-btn-select-files'>
                                    <input type="file" name="file" size="40" multiple="multiple">
                                </a>
                                <ul class="dm-uploaded-files" id="files-file-manager"></ul>
                                <button type="button" id="btn_reset_upload_image" class="btn btn-reset-upload"><?= trans("reset"); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="file-manager-right">
                        <div class="file-manager-content">
                            <div id="ckimage_file_upload_response">
                                <?php if (!empty($fileManagerImages)):
                                    foreach ($fileManagerImages as $image): ?>
                                        <div class="col-file-manager" id="fm_img_col_id_<?= $image->id; ?>">
                                            <div class="file-box" data-file-id="<?= $image->id; ?>" data-file-path="<?= getFileManagerImageUrl($image); ?>">
                                                <div class="image-container">
                                                    <img src="<?= getFileManagerImageUrl($image); ?>" alt="" class="img-responsive">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="selected_fm_img_file_id">
                    <input type="hidden" id="selected_fm_img_file_path">
                </div>
            </div>
            <div class="modal-footer">
                <div class="file-manager-footer">
                    <button type="button" id="btn_fm_img_delete" class="btn btn-sm btn-danger color-white pull-left btn-file-delete m-r-3"><i class="fa fa-trash-can"></i>&nbsp;&nbsp;<?= trans('delete'); ?></button>
                    <button type="button" id="btn_fm_img_select" class="btn btn-sm btn-info color-white btn-file-select"><i class="fa fa-check"></i>&nbsp;&nbsp;<?= trans('select_image'); ?></button>
                    <button type="button" class="btn btn-sm btn-secondary color-white" data-dismiss="modal"><?= trans('close'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="files-template-file-manager">
    <li class="media">
        <img class="preview-img" alt="">
        <div class="media-body">
            <div class="progress">
                <div class="dm-progress-waiting"><?= trans("waiting"); ?></div>
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </li>
</script>

<script>
    window.addEventListener('keydown', function (e) {
        if (e.keyIdentifier === 'U+000A' || e.keyIdentifier === 'Enter' || e.keyCode === 13) {
            if (e.target.nodeName === 'INPUT' && e.target.type === 'text' && !e.target.closest('.bootstrap-tagsinput')) {
                e.preventDefault();
                return false;
            }
        }
    }, true);

    function initFileMaganerUploader() {
        $('#drag-and-drop-zone-file-manager').dmUploader({
            url: '<?= base_url('File/uploadFileManagerImagePost'); ?>',
            maxFileSize: <?= $productSettings->max_file_size_image; ?>,
            queue: true,
            allowedTypes: 'image/*',
            extFilter: ["jpg", "jpeg", "webp", "png", "gif"],
            extraData: function (id) {
                return {
                    'file_id': id,
                    [MdsConfig.csrfTokenName]: $('meta[name="X-CSRF-TOKEN"]').attr('content')
                };
            },
            onNewFile: function (id, file) {
                ui_multi_add_file(id, file, 'file-manager');
                if (typeof FileReader !== "undefined") {
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
                $("#btn_reset_upload_image").show();
            },
            onUploadProgress: function (id, percent) {
                ui_multi_update_file_progress(id, percent);
            },
            onUploadSuccess: function (id, data) {
                document.getElementById("uploaderFile" + id).remove();
                refreshFileManagerImages();
                ui_multi_update_file_status(id, 'success', 'Upload Complete');
                ui_multi_update_file_progress(id, 100, 'success', false);
                $("#btn_reset_upload_image").hide();
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
        initFileMaganerUploader();
    });

    $(document).ajaxStop(function () {
        initFileMaganerUploader();
    });

    $(document).on('click', '#btn_reset_upload_image', function () {
        $("#drag-and-drop-zone-file-manager").dmUploader("reset");
        $("#files-file-manager").empty();
        $(this).hide();
    });

</script>