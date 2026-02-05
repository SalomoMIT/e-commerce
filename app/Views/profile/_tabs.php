<ul class="nav nav-tabs nav-tabs-horizontal nav-tabs-profile" role="tablist">
    <?php if (isVendor($user)):
        if (isAdmin() || $generalSettings->multi_vendor_system == 1):?>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab == 'products' ? 'active' : ''; ?>" href="<?= generateProfileUrl($user->slug); ?>"><?= trans("products"); ?>&nbsp;&nbsp;
                    <span>(<?= esc($profileStats->productCount); ?>)</span>
                </a>
            </li>
        <?php endif;
    endif; ?>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab == 'followers' ? 'active' : ''; ?>" href="<?= generateUrl('followers') . '/' . $user->slug; ?>"><?= trans("followers"); ?>&nbsp;&nbsp;<span>(<?= esc($profileStats->followersCount); ?>)</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab == 'following' ? 'active' : ''; ?>" href="<?= generateUrl('following') . '/' . $user->slug; ?>"><?= trans("following"); ?>&nbsp;&nbsp;<span>(<?= esc($profileStats->followingCount); ?>)</span></a>
    </li>
    <?php if (($generalSettings->reviews == 1) && isVendor($user) && $generalSettings->multi_vendor_system == 1): ?>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab == 'reviews' ? 'active' : ''; ?>" href="<?= generateUrl('reviews') . '/' . $user->slug; ?>"><?= trans("reviews"); ?>&nbsp;&nbsp;<span>(<?= esc($profileStats->userRatingCount); ?>)</span></a>
        </li>
    <?php endif;
    if (authCheck() && $user->id == user()->id):?>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab == 'my_reviews' ? 'active' : ''; ?>" href="<?= generateUrl('my_reviews') . '/' . $user->slug; ?>"><?= trans("my_reviews"); ?>&nbsp;&nbsp;<span>(<?= esc($profileStats->userReviewsCount); ?>)</span></a>
        </li>
    <?php endif;
    $vendorPages = getVendorPages($user->id);
    if (!empty($vendorPages)):
        if ($vendorPages->status_shop_policies == 1):?>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab == 'shop_policies' ? 'active' : ''; ?>" href="<?= generateUrl('shop_policies') . '/' . $user->slug; ?>"><?= trans("shop_policies"); ?></a>
            </li>
        <?php endif;
    endif; ?>
</ul>