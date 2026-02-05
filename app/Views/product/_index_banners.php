<?php if (!empty($indexBannersArray) && !empty($bannerLocation) && !empty($indexBannersArray[$bannerLocation])): ?>
    <div class="col-12 section section-index-bn">
        <div class="row">
            <?php foreach ($indexBannersArray[$bannerLocation] as $banner):
                $width = 1000;
                $height = 400;
                if ($banner->banner_width < 100 && $banner->banner_width >= 50) {
                    $width = 635;
                    $height = 332;
                } elseif ($banner->banner_width < 50) {
                    $width = 417;
                    $height = 218;
                }
                if ($banner->banner_location == $bannerLocation):?>
                    <div class="col-6 col-index-bn index_bn_<?= $banner->id; ?>">
                        <a href="<?= $banner->banner_url; ?>">
                            <img data-src="<?= getStorageFileUrl($banner->banner_image_path, $banner->storage); ?>" alt="banner" width="<?= $width; ?>" height="<?= $height; ?>" class="lazyload img-fluid">
                        </a>
                    </div>
                <?php endif;
            endforeach; ?>
        </div>
    </div>
<?php endif; ?>