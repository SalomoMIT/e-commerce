<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('seo_tools'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('sitemap'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/generateSitemapPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <label class="label-sitemap"><?= trans('frequency'); ?></label>
                        <small class="small-sitemap"> (<?= trans('frequency_exp'); ?>)</small>
                        <?= formRadio('frequency', 'auto', 'none', trans("automatically_calculated"), trans("none"), $productSettings->sitemap_frequency); ?>
                    </div>
                    <div class="form-group">
                        <label class="label-sitemap"><?= trans('last_modification'); ?></label>
                        <small class="small-sitemap"> (<?= trans('last_modification_exp'); ?>)</small>
                        <?= formRadio('last_modification', 'auto', 'none', trans("automatically_calculated"), trans("none"), $productSettings->sitemap_last_modification); ?>
                    </div>
                    <div class="form-group">
                        <label class="label-sitemap"><?= trans('priority'); ?></label>
                        <small class="small-sitemap"> (<?= trans('priority_exp'); ?>)</small>
                        <?= formRadio('priority', 'auto', 'none', trans("automatically_calculated"), trans("none"), $productSettings->sitemap_priority); ?>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('generate_sitemap'); ?></button>
                </div>
            </form>

            <?php $files = glob(FCPATH . '*.xml');
            if (!empty($files)): ?>
                <div style="padding: 20px">
                    <h3 style="font-size: 18px; font-weight: 500;"><?= trans("generated_sitemaps") ?></h3>
                    <hr>
                    <?php foreach ($files as $file):
                        if (strpos(basename($file), 'sitemap') !== false):?>
                            <div style="font-size: 16px; font-weight: 600;margin-bottom: 10px;">
                                <div style="display: inline-block; min-width: 250px;">
                                    <a href="<?= base_url(basename($file)); ?>" target="_blank"><?= basename($file); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <form action="<?= base_url('Admin/downloadSitemapPost'); ?>" method="post" style="display: inline-block">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="file_name" value="<?= basename($file); ?>">
                                    <button type="submit" name="file_type" value="sitemap" class="btn btn-xs btn-success"><i class="fa fa-cloud-download"></i></button>
                                    &nbsp;
                                </form>
                                <form action="<?= base_url('Admin/deleteSitemapPost'); ?>" method="post" style="display: inline-block">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="file_name" value="<?= basename($file); ?>">
                                    <button type="submit" name="file_type" value="sitemap" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></button>
                                </form>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="callout" style="margin-top: 30px;background-color: #fff; border-color:#00c0ef;max-width: 600px;">
            <h4>Cron Job</h4>
            <p><strong><?= base_url("cron/update-sitemap") ?></strong></p>
            <small><?= trans('msg_cron_sitemap'); ?></small>
        </div>
    </div>
    <form action="<?= base_url('Admin/seoToolsPost'); ?>" method="post">
        <?= csrf_field(); ?>
        <div class="col-lg-6 col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans('google_analytics'); ?></h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <textarea class="form-control text-area" name="google_analytics" placeholder="<?= trans('google_analytics'); ?>" style="min-height: 100px;"><?= esc($generalSettings->google_analytics); ?></textarea>
                    </div>
                    <div class="box-footer" style="padding-left: 0; padding-right: 0;">
                        <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>