<div class="row-custom">
    <div class="profile-details">
        <div class="left">
            <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="<?= esc(getUsername($user)); ?>" class="img-profile" width="180" height="180">
        </div>
        <div class="right">
            <div class="row-custom row-profile-username">
                <h1 class="username"><?= esc(getUsername($user)); ?></h1>
                <?php if (isVendor($user)): ?>
                    <i class="icon-verified icon-verified-member"></i>
                <?php endif; ?>
            </div>
            <div class="row-custom">
                <p class="p-last-seen">
                    <span class="last-seen <?= isUserOnline($user->last_seen) ? 'last-seen-online' : ''; ?>"> <i class="icon-circle"></i> <?= trans("last_seen"); ?>&nbsp;<?= timeAgo($user->last_seen); ?></span>
                </p>
            </div>
            <div class="row-custom">
                <?php if ($generalSettings->reviews == 1):
                    if ($profileStats->userRatingCount > 0): ?>
                        <div class="profile-rating m-b-10">
                            <?php echo view('partials/_review_stars', ['rating' => $profileStats->userRating]); ?>
                            &nbsp;&nbsp;<span>(<?= esc($profileStats->userRatingCount); ?>)</span>
                        </div>
                    <?php endif;
                endif;
                if ($generalSettings->profile_number_of_sales == 1 && $user->number_of_sales > 0):?>
                    <div class="profile-number-sales m-b-10 display-inline-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#777777" width="20" height="20" viewBox="0 0 512 512">
                            <circle cx="176" cy="416" r="32"/>
                            <circle cx="400" cy="416" r="32"/>
                            <path d="M456.8 120.78a23.92 23.92 0 00-18.56-8.78H133.89l-6.13-34.78A16 16 0 00112 64H48a16 16 0 000 32h50.58l45.66 258.78A16 16 0 00160 368h256a16 16 0 000-32H173.42l-5.64-32h241.66A24.07 24.07 0 00433 284.71l28.8-144a24 24 0 00-5-19.93z"/>
                        </svg>&nbsp;<?= esc($user->number_of_sales); ?>&nbsp;<?= trans("sales_number"); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="row-custom">
                <p class="description"><?= esc($user->about_me); ?></p>
            </div>
            <div class="row-custom user-contact">
                <span class="info"><?= trans("member_since"); ?>&nbsp;<?= formatDateLong($user->created_at, false); ?></span>
                <?php if ($generalSettings->show_vendor_contact_information == 1):
                    if (!empty($user->phone_number) && $user->show_phone == 1): ?>
                        <div class="info"><i class="icon-phone"></i>
                            <button type="button" id="show_phone_number_profile" class="button-link" aria-label="show phone number"><?= trans("show"); ?></button>
                            <a href="tel:<?= esc($user->phone_number); ?>" id="phone_number_profile" class="display-none"><?= esc($user->phone_number); ?></a>
                        </div>
                    <?php endif;
                    if (!empty($user->email) && $user->show_email == 1): ?>
                        <span class="info"><i class="icon-envelope"></i><?= esc($user->email); ?></span>
                    <?php endif;
                endif;
                if (!empty(getLocation($user)) && $user->show_location == 1): ?>
                    <span class="info"><i class="icon-map-marker"></i><?= getLocation($user); ?></span>
                <?php endif; ?>
            </div>
            <div class="row-custom profile-buttons">
                <div class="buttons">
                    <?php if (authCheck()):
                        if (user()->id != $user->id): ?>
                            <?php if ($user->vacation_mode == 0): ?>
                                <button class="btn btn-md btn-outline-gray" data-toggle="modal" data-target="#messageModal"><i class="icon-envelope"></i>&nbsp;<?= trans("ask_question") ?></button>
                            <?php endif; ?>
                            <form action="<?= base_url('follow-unfollow-user-post'); ?>" method="post" class="form-inline">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                                <input type="hidden" name="user_id" value="<?= $user->id; ?>">
                                <?php if (isUserFollows($user->id, user()->id)): ?>
                                    <button class="btn btn-md btn-outline-gray"><i class="icon-user-minus"></i>&nbsp;<?= trans("unfollow"); ?></button>
                                <?php else: ?>
                                    <button class="btn btn-md btn-outline-gray"><i class="icon-user-plus"></i>&nbsp;<?= trans("follow"); ?></button>
                                <?php endif; ?>
                            </form>
                        <?php endif;
                    else: ?>
                        <button class="btn btn-md btn-outline-gray" data-toggle="modal" data-target="#loginModal"><i class="icon-envelope"></i>&nbsp;<?= trans("ask_question") ?></button>
                        <button class="btn btn-md btn-outline-gray" data-toggle="modal" data-target="#loginModal"><i class="icon-user-plus"></i>&nbsp;<?= trans("follow"); ?></button>
                    <?php endif; ?>
                </div>
                <?php if ($generalSettings->show_vendor_contact_information == 1): ?>
                    <div class="social">
                        <ul>
                            <?php $socialLinks = getSocialLinksArray($user, true);
                            foreach ($socialLinks as $socialLink):
                                if (!empty($socialLink['value'])): ?>
                                    <li id="icn<?= esc($socialLink['name']); ?>"><a href="<?= esc($socialLink['value']); ?>" target="_blank"><i class="icon-<?= esc($socialLink['name']); ?>"></i></a></li>
                                <?php endif;
                            endforeach;
                            if ($generalSettings->rss_system == 1 && $user->show_rss_feeds == 1): ?>
                                <li><a href="<?= langBaseUrl() . 'rss/' . getRoute('seller', true) . $user->slug; ?>" target="_blank"><i class="icon-rss"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div id="products" class="row-custom"></div>

<?= view('partials/_modal_send_message', ['subject' => null]); ?>