<link rel="stylesheet" href="<?= base_url("assets/admin/vendor/tagify/tagify.css") ?>">
<script src="<?= base_url("assets/admin/vendor/tagify/tagify.js") ?>"></script>

<div class="row">
    <div class="col-sm-12 m-b-5">
        <label class="control-label"><?= trans('tags'); ?>&nbsp;<small>(<?= trans("tags_product_exp"); ?>)</small></label>
    </div>
    <div class="col-sm-12">
        <input name="tags_<?= $language->id; ?>" id="tagsInput<?= $language->id; ?>" class="form-control form-input tags-input" value="<?= !empty($tags) ? esc($tags) : ''; ?>" data-lang-id="<?= $language->id ?>" placeholder="<?= esc(trans('type_tag')); ?>"/>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[id^="tagsInput"]').forEach(function (inputElement) {

            if (inputElement.dataset.tagifyInitialized === '1') {
                return;
            }


            const tagify = new Tagify(inputElement, {
                enforceWhitelist: false,
                whitelist: [],
                maxTags: <?= PRODUCT_TAGS_LIMIT; ?>,
                dropdown: {
                    enabled: 1,
                    position: 'text',
                    closeOnSelect: false
                }
            });

            inputElement.dataset.tagifyInitialized = '1';

            tagify.on('input', function (e) {
                const searchTerm = e.detail.value;
                if (searchTerm.length < 2) return;

                const langId = inputElement.dataset.langId || '';
                const data = {
                    searchTerm: searchTerm,
                    lang_id: langId
                };

                $.ajax({
                    type: 'POST',
                    url: generateUrl('Ajax/getProductTagSuggestions'),
                    data: data,
                    success: function (response) {
                        if (response.result == 1) {
                            tagify.settings.whitelist = response.tags;
                            tagify.dropdown.show(e.detail.value);
                        }
                    }
                });
            });
        });
    });
</script>