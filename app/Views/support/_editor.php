<script src="<?= base_url('assets/vendor/file-uploader/js/jquery.dm-uploader.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/file-uploader/js/ui.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js'); ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/vendor/file-uploader/css/jquery.dm-uploader.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/file-uploader/css/styles.css'); ?>">
<script>
    tinymce.init({
        selector: '.tinyMCEticket',
        height: 320,
        min_height: 320,
        valid_elements: '*[*]',
        entity_encoding: 'raw',
        relative_urls: false,
        remove_script_host: false,
        directionality: MdsConfig.rtl,
        language: '<?= $activeLang->text_editor_lang; ?>',
        menubar: false,
        plugins: [],
        toolbar: 'fullscreen code preview | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | numlist bullist | forecolor backcolor removeformat | image media link',
        content_css: ['<?= base_url('assets/vendor/tinymce/editor_content.css'); ?>'],
    });

    const safeExtensions = <?= json_encode(getAppDefault('safeExtensions')); ?>;
    const msgInvalid = "<?= esc(trans("invalid_file_type")); ?>";
    const msgSizeError = "<?= esc(trans("file_too_large")); ?>";

    $(function () {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url('Support/uploadSupportAttachment'); ?>',
            queue: false,
            extraData: function (id) {
                return {
                    'file_id': id,
                    'ticket_type': 'client',
                    [MdsConfig.csrfTokenName]: $('meta[name="X-CSRF-TOKEN"]').attr('content')
                };
            },
            onNewFile: function (id, file) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!safeExtensions.includes(ext)) {
                    Swal.fire({text: msgInvalid, icon: 'warning', confirmButtonText: MdsConfig.text.ok});
                    return false;
                }

                ui_multi_add_file(id, file, "file");
            },
            onBeforeUpload: function (id, file) {
                $('#uploaderFile' + id + ' .dm-progress-waiting').hide();
                ui_multi_update_file_progress(id, 0, '', true);
                ui_multi_update_file_status(id, 'uploading', 'Uploading...');
            },
            onUploadProgress: function (id, percent) {
                ui_multi_update_file_progress(id, percent);
            },
            onUploadSuccess: function (id, data) {
                if (data.result == 1) {
                    document.getElementById("response_uploaded_files").innerHTML = data.response;
                }
                document.getElementById("uploaderFile" + id).remove();
                ui_multi_update_file_status(id, 'success', 'Upload Complete');
                ui_multi_update_file_progress(id, 100, 'success', false);
            },
            onFileSizeError: function (file) {
                Swal.fire({text: msgSizeError, icon: 'warning', confirmButtonText: MdsConfig.text.ok});
            }
        });
    });
</script>