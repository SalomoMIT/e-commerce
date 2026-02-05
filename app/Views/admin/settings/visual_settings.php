<div class="row">
    <div class="col-sm-12 col-xs-12 col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('visual_settings'); ?></h3>
                </div>
            </div>
            <form action="<?= base_url('Admin/visualSettingsPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <label><?= trans('color'); ?></label>
                        <div>
                            <input type="text" class="form-control" id="inputSiteColor" name="site_color" maxlength="200" placeholder="<?= trans('color_code'); ?>" value="<?= esc($generalSettings->site_color); ?>" data-coloris required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('logo'); ?></label>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getLogo(); ?>" alt="logo" style="max-width: 160px; max-height: 160px;">
                        </div>
                        <div class="display-block">
                            <a class='btn btn-success btn-sm btn-file-upload'>
                                <?= trans('select_logo'); ?>
                                <input type="file" name="logo" size="40" accept=".png, .jpg, .jpeg, .gif, .svg" onchange="$('#upload-file-info1').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            (.png, .jpg, .jpeg, .gif, .svg)
                        </div>
                        <span class='label label-info' id="upload-file-info1"></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('logo_email'); ?></label>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getLogoEmail(); ?>" alt="logo" style="max-width: 160px; max-height: 160px;">
                        </div>
                        <div class="display-block">
                            <a class='btn btn-success btn-sm btn-file-upload'>
                                <?= trans('select_logo'); ?>
                                <input type="file" name="logo_email" size="40" accept=".png, .jpg, .jpeg" onchange="$('#upload-file-info3').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            (.png, .jpg, .jpeg)
                        </div>
                        <span class='label label-info' id="upload-file-info3"></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('favicon'); ?> (16x16px)</label>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getFavicon(); ?>" alt="favicon" style="max-width: 100px; max-height: 100px;">
                        </div>
                        <div class="display-block">
                            <a class='btn btn-success btn-sm btn-file-upload'>
                                <?= trans('select_favicon'); ?>
                                <input type="file" name="favicon" size="40" accept=".png" onchange="$('#upload-file-info2').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            (.png)
                        </div>
                        <span class='label label-info' id="upload-file-info2"></span>
                    </div>
                    <div class="form-group">
                        <label class="m-b-10"><?= trans("logo_size"); ?></label>
                        <div class="row" style="max-width: 400px; margin-bottom: 15px;">
                            <div class="col-sm-12 col-md-6">
                                <label class="control-label"><?= trans("width"); ?>&nbsp;(px)</label>
                                <input type="number" name="logo_width" class="form-control" value="<?= getLogoSize($generalSettings, 'width'); ?>" min="10" max="300">
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <label class="control-label"><?= trans("height"); ?>&nbsp;(px)</label>
                                <input type="number" name="logo_height" class="form-control" value="<?= getLogoSize($generalSettings, 'height'); ?>" min="10" max="300">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-sm-12 col-xs-12 col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('watermark'); ?></h3>
                </div>
            </div>
            <form action="<?= base_url('Admin/updateWatermarkSettingsPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('watermark_text'); ?></label>
                        <input type="text" class="form-control" name="w_text" value="<?= !empty($watermarkSettings->w_text) ? esc($watermarkSettings->w_text) : 'Modesy'; ?>" placeholder="<?= trans('watermark_text'); ?>">
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label"><?= trans('font_size'); ?></label>
                                <input type="number" class="form-control" name="w_font_size" value="<?= !empty($watermarkSettings->w_font_size) ? esc($watermarkSettings->w_font_size) : '48'; ?>" min="1" max="500" placeholder="<?= trans('font_size'); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label"><?= trans('vertical_alignment'); ?></label>
                                <select class="form-control" name="w_vrt_alignment" required>
                                    <option value="top" <?= $watermarkSettings->w_vrt_alignment == 'top' ? 'selected' : ''; ?>><?= trans('top'); ?></option>
                                    <option value="center" <?= $watermarkSettings->w_vrt_alignment == 'center' ? 'selected' : ''; ?>><?= trans('center'); ?></option>
                                    <option value="bottom" <?= $watermarkSettings->w_vrt_alignment == 'bottom' ? 'selected' : ''; ?>><?= trans('bottom'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label"><?= trans('horizontal_alignment'); ?></label>
                                <select class="form-control" name="w_hor_alignment" required>
                                    <option value="left" <?= $watermarkSettings->w_hor_alignment == 'left' ? 'selected' : ''; ?>><?= trans('left'); ?></option>
                                    <option value="center" <?= $watermarkSettings->w_hor_alignment == 'center' ? 'selected' : ''; ?>><?= trans('center'); ?></option>
                                    <option value="right" <?= $watermarkSettings->w_hor_alignment == 'right' ? 'selected' : ''; ?>><?= trans('right'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('w_product_images', trans('add_watermark_product_images'), $watermarkSettings->w_product_images); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('w_blog_images', trans('add_watermark_blog_images'), $watermarkSettings->w_blog_images); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('w_thumbnail_images', trans('add_watermark_thumbnail_images'), $watermarkSettings->w_thumbnail_images); ?>
                    </div>

                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= base_url('assets/vendor/coloris-0.24/coloris.min.css'); ?>"/>
<script src="<?= base_url('assets/vendor/coloris-0.24/coloris.min.js'); ?>"></script>
<script>
    Coloris({
        theme: 'polaroid',
        swatches: ['#00a99d', '#00ab88', '#222222', '#6366f1', '#264653', '#2a9d8f', '#e9c46a', '#e76f51', '#d62828', '#023e8a', '#0077b6', '#0096c7', '#F39C12', '#9B51E0', '#E84393', '#F1C40F', '#1C1C1C']
    });
</script>