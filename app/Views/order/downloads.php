<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title; ?></li>
                    </ol>
                </nav>
                <h1 class="page-title"><?= $title; ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-3">
                <?= view("order/_tabs"); ?>
            </div>
            <div class="col-12 col-md-9">
                <div class="sidebar-tabs-content page-downloads">
                    <?php if (!empty($items)):
                        foreach ($items as $item):
                            $product = getDownloadableProduct($item->product_id);
                            if (!empty($product)):?>
                                <div class="order-list-item">
                                    <div class="row align-items-start">
                                        <div class="col-12 col-lg-6 m-b-15-mobile">
                                            <div class="display-flex align-items-start product">
                                                <div class="flex-item">
                                                    <div class="product-image-box product-image-box-lg">
                                                        <a href="<?= generateProductUrl($product); ?>">
                                                            <img data-src="<?= getProductMainImage($product->id, 'image_small'); ?>" alt="<?= esc($product->title); ?>" class="lazyload img-fluid img-product" width="100" height="106">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="flex-item">
                                                    <h3 class="title">
                                                        <a href="<?= generateProductUrl($product); ?>"><?= esc($product->title); ?></a>
                                                    </h3>
                                                    <div class="user">
                                                        <a href="<?= generateProfileUrl($product->user_slug); ?>" class="text-muted"><?= esc($product->user_username); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3 m-b-15-mobile">
                                            <?php if ($generalSettings->reviews == 1 && $item->seller_id != $item->buyer_id): ?>
                                                <div class="rate-product rate-product-downloads">
                                                    <?php $review = getReview($item->product_id, user()->id); ?>
                                                    <p class="p-rate-product font-600 m-b-5"><?= trans("rate_this_product"); ?>:</p>
                                                    <div class="rating-widget" data-product-id="<?= esc($item->product_id); ?>" data-widget-id="product_<?= esc($item->id); ?>">
                                                        <div class="rating-stars">
                                                            <i class="icon-star-o" data-rating="1"></i>
                                                            <i class="icon-star-o" data-rating="2"></i>
                                                            <i class="icon-star-o" data-rating="3"></i>
                                                            <i class="icon-star-o" data-rating="4"></i>
                                                            <i class="icon-star-o" data-rating="5"></i>
                                                        </div>
                                                        <input type="hidden" name="product_<?= esc($item->id); ?>_rating" class="rating-value" value="<?= !empty($review) && !empty($review->rating) ? esc($review->rating) : 0; ?>">
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12 col-lg-3">
                                            <form action="<?= base_url('download-purchased-digital-file-post'); ?>" method="post">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="sale_id" value="<?= $item->id; ?>">
                                                <?php if ($product->listing_type == 'license_key'): ?>
                                                    <div class="btn-group w-100" role="group">
                                                        <button id="btnGroupDrop1" type="button" class="btn btn-md btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="icon-download-solid"></i>&nbsp;<?= trans("download"); ?>
                                                        </button>
                                                        <div class="dropdown-menu digital-download-dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                            <button type="submit" name="submit" value="license_certificate" class="dropdown-item"><?= trans("license_certificate"); ?></button>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="btn-group w-100" role="group">
                                                        <button id="btnGroupDrop2" type="button" class="btn btn-md btn-custom dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="icon-download-solid"></i>&nbsp;<?= trans("download"); ?>
                                                        </button>
                                                        <div class="dropdown-menu digital-download-dropdown-menu" aria-labelledby="btnGroupDrop2">
                                                            <?php if (!empty($product->digital_file_download_link)): ?>
                                                                <a href="<?= esc($product->digital_file_download_link); ?>" class="dropdown-item" target="_blank"><?= trans("main_files"); ?></a>
                                                            <?php else: ?>
                                                                <button type="submit" name="submit" value="main_files" class="dropdown-item"><?= trans("main_files"); ?></button>
                                                            <?php endif; ?>
                                                            <button type="submit" name="submit" value="license_certificate" class="dropdown-item"><?= trans("license_certificate"); ?></button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="order-list-item">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-lg-6 m-b-15-mobile">
                                            <div class="display-flex align-items-center product">
                                                <div class="flex-item">
                                                    <div class="product-image-box product-image-box-lg">
                                                        <img data-src="<?= base_url('assets/img/no-image.jpg'); ?>" alt="" class="lazyload img-fluid img-product">
                                                    </div>
                                                </div>
                                                <div class="flex-item">
                                                    <h3 class="title text-gray"><?= esc($item->product_title); ?></h3>
                                                    <label class="badge badge-secondary">Not Available</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach;
                    else:?>
                        <p class="text-center text-muted"><?= trans("msg_dont_have_downloadable_files"); ?></p>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center m-t-15">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/_modal_rate_product'); ?>