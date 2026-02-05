<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= escMeta($title); ?> - <?= trans("dashboard"); ?> - <?= escMeta($generalSettings->application_name); ?></title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="shortcut icon" type="image/png" href="<?= getFavicon(); ?>"/>
<?= csrf_meta(); ?>
<?= view('dashboard/includes/_fonts'); ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/fontawesome-6.7.2/css/all.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/bootstrap/css/bootstrap.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/datatables/dataTables.bootstrap.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/datatables/jquery.dataTables_themeroller.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/pace/pace.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/vendor/magnific-popup/magnific-popup.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/plugins-2.6.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/AdminLTE.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/skin-black-light.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/file-uploader/css/jquery.dm-uploader.min.css'); ?>"/>
<link rel="stylesheet" href="<?= base_url('assets/vendor/file-uploader/css/styles.css'); ?>"/>
<link rel="stylesheet" href="<?= base_url('assets/vendor/file-manager/file-manager.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/main-2.6.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/css/dashboard-2.6.min.css'); ?>">
<?php if ($baseVars->rtl == true): ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/rtl-2.6.css'); ?>">
<?php endif; ?>
<script src="<?= base_url('assets/admin/js/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/common/js/utils.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/file-uploader/js/jquery.dm-uploader.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/file-uploader/js/ui.js'); ?>"></script>
<?= view("admin/includes/_js_config"); ?>
<style>@media (max-width: 768px) {input {font-size: 16px !important;} input::placeholder {font-size: 14px !important;}}.main-header .sidebar-toggle {display: inline-block;color: #757a89 !important;font-size: 18px;line-height: 18px;}</style>
</head>
<body class="hold-transition skin-black-light sidebar-mini">
<div class="wrapper" style="overflow-x: hidden;">
    <header class="main-header">
        <div class="main-header-inner">
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a class="btn btn-sm btn-success pull-left btn-site-prev" target="_blank" href="<?= langBaseUrl(); ?>"><i class="fa fa-eye"></i> &nbsp;<span class="btn-site-prev-text"><?= trans("view_site"); ?></span></a>
                        </li>
                        <?php if ($generalSettings->multilingual_system == 1 && countItems($activeLanguages) > 1): ?>
                            <li class="nav-item dropdown language-dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
                                    <img src="<?= base_url($activeLang->flag_path); ?>" class="flag"><?= esc($activeLang->name); ?> <i class="fa fa-caret-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($activeLanguages as $language): ?>
                                        <li>
                                            <a href="<?= convertUrlByLanguage($language); ?>" class="dropdown-item <?= $language->id == $activeLang->id ? 'selected' : ''; ?>">
                                                <img src="<?= base_url($language->flag_path); ?>" class="flag" style="width: 18px; height: auto;" alt="<?= esc($language->name); ?>"><?= esc($language->name); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>" class="user-image" alt="">
                                <span class="hidden-xs"><?= esc(getUsername(user())); ?></span>&nbsp;<i class="fa fa-caret-down caret-profile"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-main pull-right" role="menu" aria-labelledby="user-options">
                                <?= view("nav/_profile_dropdown.php", ['profileMenuAdmin' => true, 'profileMenuDash' => false]); ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <div class="sidebar-scrollbar">
                <div class="logo">
                    <a href="<?= dashboardUrl(); ?>"><img src="<?= getLogo(); ?>" alt="logo"></a>
                </div>
                <div class="user-panel">
                    <div class="image">
                        <img src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>" class="img-circle" alt="">
                    </div>
                    <div class="username">
                        <p><?= trans("hi") . ', ' . esc(getUsername(user())); ?></p>
                    </div>
                </div>
                <?php if (isVendor()): ?>
                    <ul class="sidebar-menu" data-widget="tree">
                        <li class="header"><?= trans("navigation"); ?></li>
                        <li class="nav-home">
                            <a href="<?= dashboardUrl(); ?>">
                                <i class="fa fa-home"></i> <span><?= trans("dashboard"); ?></span>
                            </a>
                        </li>
                        <li class="header"><?= trans("products"); ?></li>
                        <li class="nav-add-product">
                            <a href="<?= generateDashUrl('add_product'); ?>">
                                <i class="fa fa-file"></i>
                                <span><?= trans("add_product"); ?></span>
                            </a>
                        </li>
                        <?php if (hasPermission('products') || (!hasPermission('products') && $generalSettings->vendor_bulk_product_upload == 1)): ?>
                            <li class="nav-bulk-product-upload">
                                <a href="<?= generateDashUrl("bulk_product_upload"); ?>">
                                    <i class="fa fa-cloud-upload"></i>
                                    <span><?= trans("bulk_product_upload"); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="treeview<?php isAdminNavActive(['products', 'pending-products', 'hidden-products', 'sold-products', 'drafts']); ?>">
                            <a href="#">
                                <i class="fa fa-shopping-basket"></i>
                                <span><?= trans("products"); ?></span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="nav-products"><a href="<?= generateDashUrl('products'); ?>"><?= trans("products"); ?></a></li>
                                <li class="nav-pending-products"><a href="<?= generateDashUrl('products'); ?>?st=pending"><?= trans("pending_products"); ?></a></li>
                                <li class="nav-hidden-products"><a href="<?= generateDashUrl('products'); ?>?st=hidden"><?= trans("hidden_products"); ?></a></li>
                                <li class="nav-sold-products"><a href="<?= generateDashUrl('products'); ?>?st=sold"><?= trans("sold_products"); ?></a></li>
                                <li class="nav-drafts"><a href="<?= generateDashUrl('products'); ?>?st=draft"><?= trans("drafts"); ?></a></li>
                            </ul>
                        </li>
                        <?php if ($baseVars->isSaleActive): ?>
                            <li class="header"><?= trans("sales"); ?></li>
                            <li class="treeview<?php isAdminNavActive(['sales', 'completed-sales', 'cancelled-sales', 'sale']); ?>">
                                <a href="#">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span><?= trans("sales"); ?></span>
                                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                                </a>
                                <ul class="treeview-menu">
                                    <li class="nav-sales"><a href="<?= generateDashUrl('sales'); ?>"><?= trans("active_sales"); ?></a></li>
                                    <li class="nav-completed-sales"><a href="<?= generateDashUrl('sales'); ?>?st=completed"><?= trans("completed_sales"); ?></a></li>
                                    <li class="nav-cancelled-sales"><a href="<?= generateDashUrl('sales'); ?>?st=cancelled"><?= trans("cancelled_sales"); ?></a></li>
                                </ul>
                            </li>
                        <?php endif;
                        if ($generalSettings->bidding_system == 1): ?>
                            <li class="nav-quote-requests">
                                <a href="<?= generateDashUrl('quote_requests'); ?>">
                                    <i class="fa fa-tag"></i>
                                    <span><?= trans("quote_requests"); ?></span>
                                    <?php $newQuoteCount = getNewQuoteRequestsCount(user()->id);
                                    if (!empty($newQuoteCount)):?>
                                        <span class="pull-right-container">
                              <small class="label label-success pull-right"><?= $newQuoteCount; ?></small>
                            </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif;
                        if ($baseVars->isSaleActive): ?>
                            <li class="nav-coupons">
                                <a href="<?= generateDashUrl("coupons"); ?>">
                                    <i class="fa fa-ticket"></i>
                                    <span><?= trans("coupons"); ?></span>
                                </a>
                            </li>
                            <?php if ($generalSettings->refund_system == 1): ?>
                                <li class="nav-refund-requests">
                                    <a href="<?= generateDashUrl("refund_requests"); ?>">
                                        <i class="fa fa-flag"></i>
                                        <span><?= trans("refund_requests"); ?></span>
                                        <?php $refundCount = getSellerActiveRefundRequestCount(user()->id);
                                        if (!empty($refundCount)):?>
                                            <span class="pull-right-container">
                              <small class="label label-success pull-right"><?= $refundCount; ?></small>
                            </span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endif;
                        endif;
                        if ($paymentSettings->cash_on_delivery_enabled == 1): ?>
                            <li class="nav-cash-on-delivery">
                                <a href="<?= generateDashUrl('cash_on_delivery'); ?>">
                                    <i class="fa fa-money-bill-wave"></i>
                                    <span><?= trans("cash_on_delivery"); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="header"><?= trans("payments"); ?></li>
                        <li class="treeview<?php isAdminNavActive(['payments']); ?>">
                            <a href="#">
                                <i class="fa fa-credit-card"></i>
                                <span><?= trans("payments"); ?></span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <?php if ($generalSettings->membership_plans_system == 1): ?>
                                    <li class="nav-payment-history"><a href="<?= generateDashUrl('payments'); ?>?payment=membership"><?= trans("membership_payments"); ?></a></li>
                                <?php endif; ?>
                                <li class="nav-payment-history"><a href="<?= generateDashUrl('payments'); ?>?payment=promotion"><?= trans("promotion_payments"); ?></a></li>
                            </ul>
                        </li>
                        <?php if ($affiliateSettings->status == 1 && $affiliateSettings->type == 'seller_based'): ?>
                            <li class="header"><?= trans("affiliate_program"); ?></li>
                            <li class="nav-affiliate-program">
                                <a href="<?= generateDashUrl('affiliate-program'); ?>"><i class="fa fa-link" aria-hidden="true"></i><span><?= trans("affiliate_program"); ?></span></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($generalSettings->product_comments == 1 || $generalSettings->reviews == 1): ?>
                            <li class="header"><?= trans("comments"); ?></li>
                            <?php if ($generalSettings->product_comments == 1): ?>
                                <li class="nav-comments">
                                    <a href="<?= generateDashUrl('comments'); ?>">
                                        <i class="fa fa-comments"></i>
                                        <span><?= trans("comments"); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($generalSettings->reviews == 1): ?>
                                <li class="nav-reviews">
                                    <a href="<?= generateDashUrl('reviews'); ?>">
                                        <i class="fa fa-star"></i>
                                        <span><?= trans("reviews"); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <li class="header"><?= trans("settings"); ?></li>
                        <li class="nav-shop-settings">
                            <a href="<?= generateDashUrl('shop_settings'); ?>">
                                <i class="fa fa-cog"></i>
                                <span><?= trans("shop_settings"); ?></span>
                            </a>
                        </li>
                        <li class="nav-shop-policies">
                            <a href="<?= generateDashUrl('shop_policies'); ?>">
                                <i class="fa fa-file-text"></i>
                                <span><?= trans("shop_policies"); ?></span></a>
                        </li>
                        <?php if ($baseVars->isSaleActive && $generalSettings->physical_products_system == 1): ?>
                            <li class="nav-shipping-settings">
                                <a href="<?= generateDashUrl('shipping_settings'); ?>">
                                    <i class="fa fa-truck"></i>
                                    <span><?= trans("shipping_settings"); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </aside>
    <?php
    $segment2 = $segment = getSegmentValue(2);
    $segment3 = $segment = getSegmentValue(3);
    $uriString = $segment2;
    if (!empty($segment3)) {
        $uriString .= '-' . $segment3;
    } ?>
    <style>
        <?php if(!empty($uriString)):
        echo '.nav-'.$uriString.' > a{color: #2C344C !important; background-color:#F7F8FC;}';
        else:
        echo '.nav-home > a{color: #2C344C !important; background-color:#F7F8FC;}';
        endif;?>
    </style>
    <div class="content-wrapper">
        <section class="content">

            <div class="row">
                <div class="col-sm-12">
                    <?= view('dashboard/includes/_messages'); ?>
                </div>
            </div>