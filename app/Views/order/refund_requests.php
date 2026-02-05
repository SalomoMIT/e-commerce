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
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-md btn-info color-white m-b-15" data-toggle="modal" data-target="#modalRefundRequest">
                            <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" fill="#fff">
                                <path d="M1600 736v192q0 40-28 68t-68 28h-416v416q0 40-28 68t-68 28h-192q-40 0-68-28t-28-68v-416h-416q-40 0-68-28t-28-68v-192q0-40 28-68t68-28h416v-416q0-40 28-68t68-28h192q40 0 68 28t28 68v416h416q40 0 68 28t28 68z"/>
                            </svg>&nbsp;<?= trans("submit_refund_request"); ?>
                        </button>
                    </div>
                    <?php if (!empty($refundRequests)): ?>
                        <?php foreach ($refundRequests as $request):
                            $orderProduct = getOrderProduct($request->order_product_id);
                            $product = getProduct($orderProduct->product_id);
                            if (!empty($orderProduct) && !empty($product)):?>
                                <div class="order-list-item">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-lg-6">
                                            <div class="display-flex align-items-center product">
                                                <div class="flex-item">
                                                    <div class="ratio ratio-product-box">
                                                        <a href="<?= generateUrl("order_details") . '/' . esc($request->order_number); ?>">
                                                            <img data-src="<?= getProductMainImage($product->id, 'image_small'); ?>" alt="<?= esc($product->title); ?>" class="lazyload img-fluid img-product" width="100" height="106">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="flex-item">
                                                    <div class="m-b-5">
                                                        <a href="<?= generateUrl("order_details") . '/' . esc($request->order_number); ?>">
                                                            <strong><?= trans("order"); ?>:&nbsp;#<?= $request->order_number; ?></strong>
                                                        </a>
                                                    </div>
                                                    <h3 class="title">
                                                        <a href="<?= generateUrl("order_details") . '/' . esc($request->order_number); ?>"><?= esc($product->title); ?></a>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-2 m-t-15-mobile">
                                            <?php if ($request->status == 1): ?>
                                                <span class="badge badge-success-light"><?= trans("approved"); ?></span>
                                            <?php elseif ($request->status == 2): ?>
                                                <span class="badge badge-danger-light"><?= trans("declined"); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary-light"><?= trans("order_processing"); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-12 col-lg-4 font-size-13 m-t-15-mobile">
                                            <div class="row align-items-center">
                                                <div class="col-6 col-lg-7 font-size-13 display-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14" height="14" fill="#6c757d">
                                                        <path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                                                    </svg>&nbsp;<?= timeAgo($request->updated_at); ?>
                                                </div>
                                                <div class="col-6 col-lg-5 text-right">
                                                    <a href="<?= generateUrl("refund_requests") . '/' . $request->id; ?>" class="btn btn-sm btn-light">
                                                        <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" fill="#5E6173">
                                                            <path d="M1152 1376v-160q0-14-9-23t-23-9h-96v-512q0-14-9-23t-23-9h-320q-14 0-23 9t-9 23v160q0 14 9 23t23 9h96v320h-96q-14 0-23 9t-9 23v160q0 14 9 23t23 9h448q14 0 23-9t9-23zm-128-896v-160q0-14-9-23t-23-9h-192q-14 0-23 9t-9 23v160q0 14 9 23t23 9h192q14 0 23-9t9-23zm640 416q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/>
                                                        </svg>&nbsp;<?= trans("details"); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach;
                    endif; ?>
                    <?php if (empty($refundRequests)): ?>
                        <p class="text-center text-muted">
                            <?= trans("no_records_found"); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center m-t-15">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRefundRequest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content modal-custom modal-refund">
            <form action="<?= base_url('submit-refund-request'); ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= trans("submit_refund_request"); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"><i class="icon-close"></i> </span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans("product"); ?></label>
                        <select class="form-control custom-select" name="order_product_id" required>
                            <option value=""><?= trans("select"); ?></option>
                            <?php if (!empty($userOrders)):
                                foreach ($userOrders as $order):
                                    $hideProducts = false;
                                    if ($order->payment_method == 'bank_transfer' && $order->payment_status == 'pending_payment') {
                                        $hideProducts = true;
                                    }
                                    if ($order->status != 2 && $hideProducts == false):
                                        $products = getOrderItems($order->id);
                                        if (!empty($products)):?>
                                            <option disabled><?= formatDate($order->created_at); ?></option>
                                            <?php foreach ($products as $product):
                                                if (!in_array($product->id, $activeRefundRequestIds)):?>
                                                    <option value="<?= $product->id; ?>">#<?= esc($order->order_number); ?>&nbsp;-&nbsp;<?= esc($product->product_title); ?></option>
                                                <?php endif;
                                            endforeach;
                                        endif;
                                    endif;
                                endforeach;
                            endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("refund_reason_explain"); ?></label>
                        <textarea name="message" class="form-control" aria-hidden="true" required><?= old('message'); ?></textarea>
                    </div>
                    <div class="form-group text-right m-0">
                        <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>