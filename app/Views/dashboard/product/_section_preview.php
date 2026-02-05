<div class="section-product-details">
    <?php if (($product->product_type == 'physical' && $productSettings->physical_demo_url == 1) || ($product->product_type == 'digital' && $productSettings->digital_demo_url == 1)): ?>
        <div class="form-box">
            <div class="form-box-head">
                <h4 class="title">
                    <?= trans('demo_url'); ?><br>
                    <small><?= trans("demo_url_exp"); ?></small>
                </h4>
            </div>
            <div class="form-box-body">
                <input type="text" name="demo_url" class="form-control form-input" value="<?= esc($product->demo_url); ?>" placeholder="<?= trans("demo_url"); ?>" maxlength="990">
            </div>
        </div>
    <?php endif;
    $showVideoPrev = false;
    $showAudioPrev = false;
    if (($product->product_type == 'physical' && $productSettings->physical_video_preview == 1) || ($product->product_type == 'digital' && $productSettings->digital_video_preview == 1)):
        $showVideoPrev = true;
    endif;
    if (($product->product_type == 'physical' && $productSettings->physical_audio_preview == 1) || ($product->product_type == 'digital' && $productSettings->digital_audio_preview == 1)):
        $showAudioPrev = true;
    endif; ?>
    <?php if ($showVideoPrev || $showAudioPrev): ?>
        <div class="form-box form-box-preview form-box-last" style="padding-bottom: 0;">
            <div class="form-box-head">
                <h4 class="title"><?= trans('preview'); ?></h4>
            </div>
            <div class="form-box-body">
                <div class="row">
                    <?php if ($showVideoPrev): ?>
                        <div class="col-sm-12 col-md-6 m-b-30">
                            <label><?= trans("video_preview"); ?></label>
                            <small>(<?= trans("video_preview_exp"); ?>)</small>
                            <?= view('dashboard/product/_video_upload', ['productVideo' => $productVideo]); ?>
                        </div>
                    <?php endif;
                    if ($showAudioPrev):?>
                        <div class="col-sm-12 col-md-6 m-b-30">
                            <label><?= trans("audio_preview"); ?></label>
                            <small>(<?= trans("audio_preview_exp"); ?>)</small>
                            <?= view('dashboard/product/_audio_upload', ['productAudio' => $productAudio]); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php if ($product->listing_type == 'ordinary_listing' && $productSettings->classified_external_link == 1): ?>
    <div class="section-product-details">
        <div class="form-box form-box-last">
            <div class="form-box-head">
                <h4 class="title">
                    <?= trans('external_link'); ?><br>
                    <small><?= trans("external_link_exp"); ?></small>
                </h4>
            </div>
            <div class="form-box-body">
                <input type="text" name="external_link" class="form-control form-input" value="<?= esc($product->external_link); ?>" placeholder="<?= trans("external_link"); ?>" maxlength="990">
            </div>
        </div>
    </div>
<?php endif; ?>

