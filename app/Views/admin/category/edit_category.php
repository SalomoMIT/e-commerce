<div class="row">
    <div class="col-lg-10 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('update_category'); ?></h3>
                </div>
                <div class="right">
                    <a href="<?= adminUrl('categories'); ?>" class="btn btn-success btn-add-new">
                        <i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?= trans('categories'); ?>
                    </a>
                </div>
            </div>
            <form action="<?= base_url('Category/editCategoryPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $category->id; ?>">
                <div class="box-body">

                    <div class="form-group">
                        <label><?= trans("category_name"); ?></label>
                        <?php foreach ($activeLanguages as $language): ?>
                            <input type="text" class="form-control m-b-5" name="name_<?= $language->id; ?>" value="<?= !empty($categoryDetails) && !empty($categoryDetails[$language->id]) ? esc($categoryDetails[$language->id]->name) : ''; ?>" placeholder="<?= countItems($activeLanguages) > 1 ? esc($language->name) : esc(trans("category_name")); ?>" maxlength="255" required>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label"><?= trans("slug"); ?>
                            <small>(<?= trans("slug_exp"); ?>)</small>
                        </label>
                        <input type="text" class="form-control" name="slug" value="<?= esc($category->slug); ?>" placeholder="<?= trans("slug"); ?>">
                    </div>

                    <div class="form-group">
                        <label><?= trans('order'); ?></label>
                        <input type="number" class="form-control" name="category_order" value="<?= esc($category->category_order); ?>" placeholder="<?= trans('order'); ?>" value="1" min="1" max="99999" required>
                    </div>

                    <div class="form-group">
                        <label><?= trans('parent_category'); ?></label>
                        <div id="category_select_container">
                            <?php
                            $parentArray = [];
                            $parentTree = getCategoryParentTree($category->id);
                            if (!empty($parentTree)) {
                                $parentArray = array_column($parentTree, 'id');
                            }
                            $level = 1;
                            foreach ($parentArray as $parentId):
                                $parentItem = getCategory($parentId);
                                if (!empty($parentItem)):
                                    $subCategories = getSubCategoriesByParentId($parentItem->parent_id);
                                    if (!empty($subCategories)): ?>
                                        <div class="subcategory-select-container" data-level="<?= $level; ?>">
                                            <select name="category_id[]" class="form-control select2" data-level="<?= $level; ?>" onchange="getSubCategories(this.value,'<?= $level; ?>');">
                                                <option value=""><?= trans('none'); ?></option>
                                                <?php foreach ($subCategories as $subCategory):
                                                    if ($subCategory->id != $category->id):?>
                                                        <option value="<?= $subCategory->id; ?>" <?= $subCategory->id == $parentItem->id ? 'selected' : ''; ?>><?= esc($subCategory->cat_name); ?></option>
                                                    <?php endif;
                                                endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif;
                                endif;
                                $level++;
                            endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="commission_mode"><?= trans("commission"); ?></label>
                        <select name="commission_mode" id="commission_mode" class="form-control">
                            <option value="default" <?= $category->is_commission_set == 0 ? 'selected' : ''; ?>><?= trans("default"); ?></option>
                            <option value="custom" <?= $category->is_commission_set == 1 && $category->commission_rate > 0 ? 'selected' : ''; ?>><?= trans("custom"); ?></option>
                            <option value="none" <?= $category->is_commission_set == 1 && $category->commission_rate == 0 ? 'selected' : ''; ?>><?= trans("none"); ?> (0%)</option>
                        </select>
                    </div>

                    <div class="form-group" id="custom_commission_input" style="<?= $category->is_commission_set == 1 && $category->commission_rate > 0 ? '' : 'display: none;'; ?>">
                        <label><?= trans('commission_rate'); ?>&nbsp;(%)</label>
                        <input type="number" name="commission_rate" id="commission_rate" class="form-control" min="0" max="99.99" step="0.01" value="<?= $category->commission_rate > 0 ? esc(formatDecimalClean($category->commission_rate)) : ''; ?>" placeholder="E.g. 5">
                    </div>

                    <div class="form-group">
                        <?= formSwitch('status', trans('status'), $category->status); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('show_on_main_menu', trans('show_on_main_menu'), $category->show_on_main_menu); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('show_image_on_main_menu', trans('show_image_on_main_menu'), $category->show_image_on_main_menu); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('show_description', trans('show_description_category_page'), $category->show_description); ?>
                    </div>

                    <div class="form-group m-b-30">
                        <label class="control-label display-block"><?= trans('image'); ?></label>
                        <div class='btn btn-success btn-sm btn-file-upload'>
                            <?= trans('select_image'); ?>
                            <input type="file" name="file" class="image-input" accept=".jpg, .jpeg, .webp, .png, .gif" data-preview="#preview1">
                        </div>
                        <?php if (!empty($category->image)): ?>
                            <a href="#" class="btn btn-sm btn-danger btn-delete-category-img" onclick="deleteCategoryImage('<?= $category->id; ?>');"><?= trans("delete"); ?></a>
                        <?php endif; ?>
                        <img src="" id="preview1" class="img-thumbnail img-preview">
                        <div>
                            <?php $imgUrl = getStorageFileUrl($category->image, $category->storage); ?>
                            <img src="<?= !empty($imgUrl) ? esc($imgUrl) : ''; ?>" id="preview1" class="img-thumbnail img-preview img-category">
                        </div>
                    </div>

                    <div class="nav-tabs-custom nav-tabs-multi-lang tab-default">
                        <p class="font-600"><?= trans("seo_metadata"); ?></p>
                        <ul class="nav nav-tabs">
                            <?php foreach ($activeLanguages as $language): ?>
                                <li class="<?= $language->id == selectedLangId() ? 'active' : ''; ?>"><a href="#tabLang<?= $language->id; ?>" data-toggle="tab" aria-expanded="true"><?= esc($language->name); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="tab-content">
                            <?php foreach ($activeLanguages as $language): ?>
                                <div class="tab-pane <?= $language->id == selectedLangId() ? 'active' : ''; ?>" id="tabLang<?= $language->id; ?>">
                                    <div class="form-group m-b-10">
                                        <input type="text" name="meta_title_<?= $language->id; ?>" value="<?= !empty($categoryDetails) && !empty($categoryDetails[$language->id]) ? esc($categoryDetails[$language->id]->meta_title) : ''; ?>" class="form-control" placeholder="<?= esc(trans("meta_title")); ?>">
                                    </div>
                                    <div class="form-group m-b-10">
                                        <textarea class="form-control form-textarea" name="meta_description_<?= $language->id; ?>" placeholder="<?= esc(trans("meta_description")); ?>"><?= !empty($categoryDetails) && !empty($categoryDetails[$language->id]) ? esc($categoryDetails[$language->id]->meta_description) : ''; ?></textarea>
                                    </div>
                                    <div class="form-group m-b-10">
                                        <input type="text" class="form-control" name="meta_keywords_<?= $language->id; ?>" value="<?= !empty($categoryDetails) && !empty($categoryDetails[$language->id]) ? esc($categoryDetails[$language->id]->meta_keywords) : ''; ?>" placeholder="<?= esc(trans('meta_keywords')); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>