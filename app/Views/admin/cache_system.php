<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans("product_cache_system"); ?></h3>
            </div>
            <form action="<?= base_url('Admin/cacheSystemPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <?= formSwitch('cache_system', trans('status'), $generalSettings->cache_system); ?>
                    </div>
                    <div class="form-group">
                        <?= formSwitch('refresh_cache_database_changes', trans('refresh_cache_database_changes'), $generalSettings->refresh_cache_database_changes); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('cache_refresh_time'); ?></label>&nbsp;
                        <small>(<?= trans("cache_refresh_time_exp"); ?>)</small>
                        <input type="number" class="form-control" name="cache_refresh_time" placeholder="<?= trans('cache_refresh_time'); ?>" value="<?= $generalSettings->cache_refresh_time / 60; ?>">
                    </div>
                    <div class="box-footer" style="padding-left: 0; padding-right: 0;">
                        <button type="submit" name="action" value="save" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                        <button type="submit" name="action" value="reset" class="btn btn-warning pull-right m-r-10"><?= trans('reset_cache'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans("static_cache_system"); ?></h3>
            </div>
            <form action="<?= base_url('Admin/cacheSystemPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <?php $label = trans('static_content_cache') . ' (' . trans("settings") .', '.trans("languages").', '.trans("pages"). '...)'; ?>
                        <?= formSwitch('static_cache_system', $label, $generalSettings->static_cache_system); ?>
                    </div>

                    <div class="form-group">
                        <?php $label = trans('category_cache') . ' (' . trans("categories") .', '.trans("brands").', '.trans("custom_fields"). ')'; ?>
                        <?= formSwitch('category_cache_system', $label, $generalSettings->category_cache_system); ?>
                    </div>

                    <div class="box-footer" style="padding-left: 0; padding-right: 0;">
                        <button type="submit" name="action" value="save_static" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                        <button type="submit" name="action" value="reset_static" class="btn btn-warning pull-right m-r-10"><?= trans('reset_cache'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="alert alert-info">
            <strong><?= trans("warning"); ?>!</strong>&nbsp;<?= trans("static_cache_system_exp"); ?>
        </div>
    </div>
</div>