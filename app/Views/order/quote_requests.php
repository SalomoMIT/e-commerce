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
                <div class="sidebar-tabs-content">
                    <?= view('partials/_messages'); ?>
                    <?php if (!empty($quoteRequests)): ?>
                        <?php foreach ($quoteRequests as $quoteRequest):
                            $product = getProduct($quoteRequest->product_id);
                            if (!empty($product)): ?>
                                <div class="order-list-item">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-lg-4">
                                            <div class="display-flex align-items-center product">
                                                <div class="flex-item">
                                                    <div class="ratio ratio-product-box">
                                                        <a href="<?= generateProductUrl($product); ?>">
                                                            <img data-src="<?= getOrderImageUrl($quoteRequest->product_image_data, $product->id); ?>" alt="<?= esc($product->title); ?>" class="lazyload img-fluid img-product">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="flex-item">
                                                    <div class="m-b-5"><strong><?= trans("quote"); ?>:&nbsp;#<?= $quoteRequest->id; ?></strong></div>
                                                    <h3 class="title">
                                                        <a href="<?= generateProductUrl($product); ?>"><?= esc($quoteRequest->product_title); ?></a>
                                                    </h3>
                                                    <?php if (!empty($quoteRequest->product_options_summary)): ?>
                                                        <div class="product-variant-info m-b-5">
                                                            <?= $quoteRequest->product_options_summary; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <small class="font-size-13"><?= trans("quantity") . ': ' . $quoteRequest->product_quantity; ?></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-2 m-t-15-mobile">
                                            <?php if ($quoteRequest->status == "new_quote_request"): ?>
                                                <span class="badge badge-primary-light"><?= trans($quoteRequest->status); ?></span>
                                            <?php elseif ($quoteRequest->status == "pending_quote" || $quoteRequest->status == "pending_payment"): ?>
                                                <span class="badge badge-warning-light"><?= trans($quoteRequest->status); ?></span>
                                            <?php elseif ($quoteRequest->status == "rejected_quote"): ?>
                                                <span class="badge badge-danger-light"><?= trans($quoteRequest->status); ?></span>
                                            <?php elseif ($quoteRequest->status == "closed"): ?>
                                                <span class="badge badge-secondary-light"><?= trans($quoteRequest->status); ?></span>
                                            <?php elseif ($quoteRequest->status == "completed"): ?>
                                                <span class="badge badge-success-light"><?= trans($quoteRequest->status); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12 col-lg-3 m-t-15-mobile">
                                            <?php if ($quoteRequest->status != 'new_quote_request' && $quoteRequest->price_offered != 0): ?>
                                                <div class="m-b-5"><?= trans("sellers_bid"); ?>:&nbsp;<strong><?= priceFormatted(@convertCurrencyByExchangeRate($quoteRequest->price_offered, $selectedCurrency->exchange_rate), $selectedCurrency->code); ?></strong></div>
                                            <?php endif; ?>
                                            <div class="display-flex align-items-center font-size-13">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="13" height="13" fill="#6c757d">
                                                    <path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                                                </svg>&nbsp;<?= timeAgo($quoteRequest->updated_at); ?>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3 col-buttons m-t-15-mobile">

                                            <?php if (!empty($quoteRequest->variant_hash)):
                                                $variant = getVariantByHash($quoteRequest->variant_hash);
                                                if (empty($variant) || $variant->is_active != 1): ?>
                                                    <div class="m-b-5 text-center">
                                                        <span class="badge badge-danger-light"><?= trans("expired"); ?></span>
                                                    </div>
                                                <?php else: ?>

                                                    <?php if ($quoteRequest->status == 'pending_quote'): ?>
                                                        <form action="<?= base_url('bidding/accept-quote-post'); ?>" method="post">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" class="form-control" value="<?= $quoteRequest->id; ?>">
                                                            <input type="hidden" name="back_url" class="form-control" value="<?= getCurrentUrl(); ?>">
                                                            <button type="submit" class="btn btn-sm btn-success color-white m-b-5">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#ffffff" width="14" height="14">
                                                                    <path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/>
                                                                </svg>&nbsp;<?= trans("accept_quote"); ?>
                                                            </button>
                                                        </form>
                                                        <form action="<?= base_url('bidding/reject-quote-post'); ?>" method="post">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" class="form-control" value="<?= $quoteRequest->id; ?>">
                                                            <input type="hidden" name="back_url" class="form-control" value="<?= getCurrentUrl(); ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger color-white m-b-5">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#ffffff" width="14" height="14">
                                                                    <path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"/>
                                                                </svg>&nbsp;<?= trans("reject_quote"); ?>
                                                            </button>
                                                        </form>
                                                    <?php elseif ($quoteRequest->status == 'pending_payment'): ?>
                                                        <form action="<?= base_url('add-to-cart-quote'); ?>" method="post">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" class="form-control" value="<?= $quoteRequest->id; ?>">
                                                            <button type="submit" class="btn btn-sm btn-info color-white m-b-5"><i class="icon-cart-solid"></i>&nbsp;<?= trans("add_to_cart"); ?></button>
                                                        </form>
                                                    <?php endif; ?>

                                                <?php endif;
                                            endif; ?>

                                            <button type="button" class="btn btn-sm btn-light" onclick="deleteQuoteRequest(<?= $quoteRequest->id; ?>,'<?= trans("confirm_quote_request", true); ?>');">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="#6c757d" width="14" height="14">
                                                    <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                                                </svg>&nbsp;<?= trans("delete_quote"); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach;
                    endif; ?>
                    <?php if (empty($quoteRequests)): ?>
                        <p class="text-center text-muted"><?= trans("no_records_found"); ?></p>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center m-t-15">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>