<label class="control-label"><?= trans("categories"); ?></label>
<div id="selected_categories">
    <?php $strCategoryIds = '';
    if (!empty($brandCategoryIdsArray)):
        $strCategoryIds = implode(',', $brandCategoryIdsArray);
        $pickerCategories = getCategoriesByIdArray($brandCategoryIdsArray);
        if (!empty($pickerCategories)):
            foreach ($pickerCategories as $category): ?>
                <div id="cat-group-<?= esc($category->id); ?>" class="btn-group">
                    <button type="button" class="btn btn-sm btn-default btn-category-name"><?= esc($category->cat_name); ?></button>
                    <button type="button" class="btn btn-sm btn-default btn-category-delete" data-id="<?= esc($category->id); ?>"><i class="fa fa-times"></i></button>
                </div>
            <?php endforeach;
        endif;
    endif; ?>
</div>
<select id="categories" name="category_id[]" class="form-control custom-select select2" onchange="getSubCategories(this.value, 0);">
    <option value=""><?= trans('select_category'); ?></option>
    <?php if (!empty($parentCategories)):
        foreach ($parentCategories as $item): ?>
            <option value="<?= esc($item->id); ?>"><?= esc($item->cat_name); ?></option>
        <?php endforeach;
    endif; ?>
</select>
<div id="category_select_container"></div>
<button type="button" class="btn btn-sm btn-info m-t-10 btn-select-category"><i class="fa fa-check"></i>&nbsp;<?= trans("select") ?></button>
<input type="hidden" name="category_ids" id="input_selected_category_ids" value="<?= esc($strCategoryIds); ?>">


<style>
    .select2-container {
        z-index: 999 !important;
    }

    .btn-select-category {
        display: none;
    }

    .btn-category-name:hover, .btn-category-name:focus, .btn-category-name:active {
        background-color: #f4f4f4 !important;
        color: #444 !important;
        border-color: #ddd !important;
    }

    .btn-group {
        margin-bottom: 5px;
        margin-right: 5px;
    }
</style>

<script>
    $(document).on("change", "select", function () {
        $('.btn-select-category').show();
    });

    var strIds = '<?= clrQuotes(esc($strCategoryIds)); ?>';
    var arraySelectedIds = strIds.split(',');
    if (!Array.isArray(arraySelectedIds) || arraySelectedIds.some(isNaN)) {
        arraySelectedIds = [];
    }
    $(document).on("click", ".btn-select-category", function () {
        var selects = document.getElementsByName('category_id[]');
        var latestSelectedValue = '';
        var latestSelectedText = '';
        var previousSelectedValue = '';
        var previousSelectedText = '';
        for (var i = 0; i < selects.length; i++) {
            var selectedOption = selects[i].options[selects[i].selectedIndex];
            if (selectedOption.value !== '') {
                previousSelectedValue = latestSelectedValue;
                previousSelectedText = latestSelectedText;
                latestSelectedValue = selectedOption.value;
                latestSelectedText = selectedOption.text;
            }
        }
        if (latestSelectedValue === '') {
            latestSelectedValue = previousSelectedValue;
            latestSelectedText = previousSelectedText;
        }
        if (latestSelectedValue != '' && !arraySelectedIds.includes(latestSelectedValue)) {
            arraySelectedIds.push(latestSelectedValue);
            var newCategory = '<div id="cat-group-' + latestSelectedValue + '" class="btn-group">' +
                '<button type="button" class="btn btn-sm btn-default btn-category-name">' + latestSelectedText + '</button>' +
                '<button type="button" class="btn btn-sm btn-default btn-category-delete" data-id="' + latestSelectedValue + '"><i class="fa fa-times"></i></button>' +
                '</div>';
            $('#selected_categories').append(newCategory);
            $('#input_selected_category_ids').val(arraySelectedIds.toString());
            $('#categories').val(null).trigger('change');
            $('.btn-select-category').hide();
        }
    });
    $(document).on("click", ".btn-category-delete", function () {
        var id = $(this).attr('data-id');
        $('#cat-group-' + id).remove();
        var index = arraySelectedIds.indexOf(id);
        if (index !== -1) {
            arraySelectedIds.splice(index, 1);
        }
        $('#input_selected_category_ids').val(arraySelectedIds.toString());
    });
</script>