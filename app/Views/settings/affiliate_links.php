<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= generateUrl('settings', 'edit_profile'); ?>"><?= trans("profile_settings"); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title; ?></li>
                    </ol>
                </nav>
                <h1 class="page-title"><?= trans("profile_settings"); ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="row-custom">
                    <?= view("settings/_tabs"); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="sidebar-tabs-content">
                    <div class="table-responsive table-custom">
                        <table class="table">
                            <thead>
                            <tr role="row">
                                <th scope="col"><?= trans("product"); ?></th>
                                <th scope="col"><?= trans("affiliate_link"); ?></th>
                                <th scope="col"><?= trans("date"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($links)): ?>
                                <?php foreach ($links as $link):
                                    $product = getProduct($link->product_id);
                                    if (!empty($product)): ?>
                                        <tr>
                                            <td><a href="<?= generateProductUrl($product); ?>"><?= esc($product->title); ?></a></td>
                                            <td style="width: 50%;"><?= esc(generateUrl('affiliate') . '/' . $link->link_short); ?></td>
                                            <td style="width: 120px;">
                                                <?= formatDate($link->created_at, false); ?>
                                                <div><a href="javascript:void(0)" class="text-danger link-underlined" onclick='deleteAffiliateLink(<?= $link->id; ?>,"<?= trans("confirm_action", true); ?>");'><?= trans("delete"); ?></a></div>
                                            </td>
                                        </tr>
                                    <?php endif; endforeach;
                            endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($links)): ?>
                        <p class="text-center m-t-15">
                            <?= trans("no_records_found"); ?>
                        </p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-center m-t-30">
                        <?= $pager->links; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>