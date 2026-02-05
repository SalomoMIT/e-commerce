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
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-list-item">
                                <div class="row align-items-center">
                                    <div class="col-4 col-lg-3">
                                        <?= trans("order"); ?>: <strong>#<?= esc($order->order_number); ?></strong>
                                    </div>
                                    <div class="col-4 col-lg-2">
                                        Total: <strong><?= priceFormatted($order->price_total, $order->price_currency); ?></strong>
                                    </div>
                                    <div class="col-4 col-lg-3 text-align-right-mobile">
                                        <?php if ($order->status == 2): ?>
                                            <span class="badge badge-danger-light"><?= trans("cancelled"); ?></span>
                                        <?php else: ?>
                                            <strong class="font-600">
                                                <?php if ($order->payment_status == 'pending_payment'):
                                                    if ($order->payment_method == 'cash_on_delivery'): ?>
                                                        <span class="badge badge-info-light"><?= trans("order_processing"); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary-light"><?= trans("pending_payment"); ?></span>
                                                    <?php endif;
                                                else:
                                                    if ($order->status == 1):?>
                                                        <span class="badge badge-success-light"><?= trans("completed"); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info-light"><?= trans("order_processing"); ?></span>
                                                    <?php endif;
                                                endif; ?>
                                            </strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4 font-size-13 m-t-15-mobile">
                                        <div class="row align-items-center">
                                            <div class="col-6 col-lg-7 font-size-13">
                                                <div class="display-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="13" height="13" fill="#6c757d">
                                                        <path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                                                    </svg>&nbsp;<?= formatDate($order->created_at); ?>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-5 text-right">
                                                <a href="<?= generateUrl('order_details') . '/' . esc($order->order_number); ?>" class="btn btn-sm btn-light">
                                                    <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" fill="#5E6173">
                                                        <path d="M1152 1376v-160q0-14-9-23t-23-9h-96v-512q0-14-9-23t-23-9h-320q-14 0-23 9t-9 23v160q0 14 9 23t23 9h96v320h-96q-14 0-23 9t-9 23v160q0 14 9 23t23 9h448q14 0 23-9t9-23zm-128-896v-160q0-14-9-23t-23-9h-192q-14 0-23 9t-9 23v160q0 14 9 23t23 9h192q14 0 23-9t9-23zm640 416q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/>
                                                    </svg>&nbsp;&nbsp;<?= trans("details"); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    endif; ?>
                    <?php if (empty($orders)): ?>
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