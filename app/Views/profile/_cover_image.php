<?php if (!empty($user->cover_image)):
    if ($user->cover_image_type == 'boxed'):?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <img data-src="<?= getStorageFileUrl($user->cover_image, $user->storage_cover); ?>" class="lazyload img-profile-cover" width="1920" height="400">
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container-fluid">
            <div class="row">
                <img data-src="<?= getStorageFileUrl($user->cover_image, $user->storage_cover); ?>" class="lazyload img-profile-cover" width="1920" height="400">
            </div>
        </div>
    <?php endif;
endif; ?>