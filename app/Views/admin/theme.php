<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('theme'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('navigation'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/themePost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <label><?= trans('menu_limit'); ?>&nbsp;(<?= trans("number_of_links_in_menu"); ?>)</label>
                        <input type="number" class="form-control" name="menu_limit" placeholder="<?= trans('menu_limit'); ?>" value="<?= $generalSettings->menu_limit; ?>" min="1" max="100" style="max-width: 400px;" required>
                    </div>

                    <div class="form-group">
                        <label><?= trans('navigation_template'); ?></label>
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="selected_navigation" value="1" id="selected_navigation_1" class="custom-control-input" <?= $generalSettings->selected_navigation == 1 ? 'checked' : ''; ?>>
                                    <label for="selected_navigation_1" class="custom-control-label"><?= trans("navigation"); ?>&nbsp;1</label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/nav_1.jpg'); ?>" alt="" class="img-responsive img-thumbnail">
                            </div>
                            <div class="col-sm-6 col-xs-12 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="selected_navigation" value="2" id="selected_navigation_2" class="custom-control-input" <?= $generalSettings->selected_navigation == 2 ? 'checked' : ''; ?>>
                                    <label for="selected_navigation_2" class="custom-control-label"><?= trans("navigation"); ?>&nbsp;2</label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/nav_2.jpg'); ?>" alt="" class="img-responsive img-thumbnail">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="nav" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-12 col-lg-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('featured_categories'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/themePost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="fea_categories_design" value="grid_layout" id="fea_categories_design_1" class="custom-control-input" <?= $generalSettings->fea_categories_design == 'grid_layout' ? 'checked' : ''; ?>>
                                    <label for="fea_categories_design_1" class="custom-control-label"><?= trans("grid_layout"); ?></label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/categories_1.jpg'); ?>" alt="" class="img-responsive img-thumbnail">
                            </div>
                            <div class="col-sm-6 col-xs-12 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="fea_categories_design" value="round_boxes" id="fea_categories_design_2" class="custom-control-input" <?= $generalSettings->fea_categories_design == 'round_boxes' ? 'checked' : ''; ?>>
                                    <label for="fea_categories_design_2" class="custom-control-label"><?= trans("round_boxes"); ?></label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/categories_2.jpg'); ?>" alt="" class="img-responsive img-thumbnail">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="cat" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('product_image_display_mode'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/themePost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 col-lg-4 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="product_img_display_mode" value="cover" id="product_img_display_mode_1" class="custom-control-input" <?= $generalSettings->product_img_display_mode == 'cover' ? 'checked' : ''; ?>>
                                    <label for="product_img_display_mode_1" class="custom-control-label"><?= trans("img_display_fill_container"); ?></label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/img_mode_1.jpg'); ?>" alt="" class="img-responsive img-thumbnail" width="180">
                            </div>
                            <div class="col-sm-12 col-xs-12 col-lg-4 m-b-5">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="product_img_display_mode" value="full_image" id="product_img_display_mode_2" class="custom-control-input" <?= $generalSettings->product_img_display_mode != 'cover' ? 'checked' : ''; ?>>
                                    <label for="product_img_display_mode_2" class="custom-control-label"><?= trans("img_display_show_full_image"); ?></label>
                                </div>
                                <img src="<?= base_url('assets/admin/img/img_mode_2.jpg'); ?>" alt="" class="img-responsive img-thumbnail" width="180">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="img_mode" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-control {
        margin-bottom: 15px;
    }
</style>