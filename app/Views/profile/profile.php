<?= view('profile/_cover_image'); ?>
<div id="wrapper">
    <div class="container">
        <?php if (empty($user->cover_image)): ?>
            <div class="row">
                <div class="col-12">
                    <nav class="nav-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= trans("profile"); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-12">
                <div class="profile-page-top">
                    <?= view('profile/_profile_user_info'); ?>
                    <div class="row-custom report-seller-sidebar-mobile">
                        <?php if (authCheck()):
                            if ($user->id != user()->id):?>
                                <button type="button" class="button-link text-muted link-abuse-report link-abuse-report-button display-inline-flex align-items-center" data-toggle="modal" data-target="#reportSellerModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
                                    </svg>&nbsp;<?= trans("report_this_seller"); ?>
                                </button>
                            <?php endif;
                        else: ?>
                            <button type="button" class="button-link text-muted link-abuse-report link-abuse-report-button display-inline-flex align-items-center" data-toggle="modal" data-target="#loginModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512" fill="currentColor">
                                    <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
                                </svg>&nbsp;<?= trans("report_this_seller"); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?= view('profile/_tabs'); ?>
            </div>
            <?php if (isVendor($user)):
                if (isAdmin() || $generalSettings->multi_vendor_system == 1):?>
                    <div class="col-12">
                        <?php if ($user->vacation_mode == 1): ?>
                            <div class="sidebar-tabs-content">
                                <div class="alert alert-info alert-large">
                                    <strong><?= trans("vendor_on_vacation"); ?>!</strong>&nbsp;&nbsp;<?= trans("vendor_on_vacation_exp"); ?>
                                </div>
                                <div class="m-t-30">
                                    <?= $user->vacation_message; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="sidebar-tabs-content container-products-page">
                                <div class="row">

                                    <div class="col-12 m-b-20 container-filter-products-mobile">
                                        <?= view('product/_product_filters_mobile'); ?>
                                    </div>

                                    <div class="col-12 col-md-3 col-sidebar-products">
                                        <?= view('product/_product_filters'); ?>
                                    </div>

                                    <div id="productListProfile" class="col-12 col-md-9 col-content-products">
                                        <?= view('product/_product_list'); ?>
                                    </div>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif;
            else: ?>
                <div class="col-12">
                    <div class="sidebar-tabs-content"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (authCheck() && !empty($user) && $user->id != user()->id): ?>
    <div class="modal fade" id="reportSellerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom modal-report-abuse">
                <form id="form_report_seller" method="post">
                    <input type="hidden" name="id" value="<?= $user->id; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= trans("report_this_seller"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true"><i class="icon-close"></i> </span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="response_form_report_seller" class="col-12"></div>
                            <div class="col-12">
                                <div class="form-group m-0">
                                    <label class="control-label"><?= trans("description"); ?></label>
                                    <textarea name="description" class="form-control form-textarea" placeholder="<?= trans("abuse_report_exp"); ?>" minlength="5" maxlength="10000" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    var pagination_links = document.querySelectorAll(".pagination a");
    var i;
    for (i = 0; i < pagination_links.length; i++) {
        pagination_links[i].href = pagination_links[i].href + "#products";
    }
</script>

