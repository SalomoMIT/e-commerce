<?= view("profile/_cover_image"); ?>
    <div id="wrapper">
        <div class="container">
            <?php if (empty($user->cover_image)): ?>
                <div class="row">
                    <div class="col-12">
                        <nav class="nav-breadcrumb" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= trans("followers"); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-12">
                    <div class="profile-page-top">
                        <?= view('profile/_profile_user_info'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= view('profile/_tabs'); ?>
                </div>
                <div class="col-12">
                    <div class="sidebar-tabs-content">
                        <div id="user-review-result" class="user-reviews">
                            <div class="reviews-container">
                                <div class="col-12">
                                    <div class="review-total">
                                        <label class="label-review"><?= trans("my_reviews"); ?>&nbsp;(<?= numberFormatShort($numRows); ?>)</label>
                                    </div>
                                    <?php if (empty($reviews)): ?>
                                        <p class="no-comments-found"><?= trans("no_reviews_found"); ?></p>
                                    <?php else: ?>
                                        <ul class="list-unstyled list-reviews">
                                            <?php foreach ($reviews as $review): ?>
                                                <li class="media">
                                                    <a href="<?= generateProfileUrl($review->user_slug); ?>">
                                                        <img src="<?= getUserAvatar($review->user_avatar, $review->user_storage_avatar); ?>" alt="<?= esc($review->user_username); ?>">
                                                    </a>
                                                    <div class="media-body">
                                                            <div class="row-custom m-b-10">
                                                                <a href="<?= generateProductUrlBySlug($review->product_slug); ?>"><strong><?= trans("product"); ?>:&nbsp;</strong><?= esc($review->product_title); ?></a>
                                                            </div>
                                                        <div class="row-custom">
                                                            <?= view('partials/_review_stars', ['rating' => $review->rating]); ?>
                                                        </div>
                                                        <div class="row-custom">
                                                            <a href="<?= generateProfileUrl($review->user_slug); ?>">
                                                                <h5 class="username"><?= esc($review->user_username); ?></h5>
                                                            </a>
                                                        </div>
                                                        <div class="row-custom">
                                                            <div class="review">
                                                                <?= esc($review->review); ?>
                                                            </div>
                                                        </div>
                                                        <div class="row-custom">
                                                            <span class="date"><?= timeAgo($review->created_at); ?></span>
                                                        </div>
                                                    </div>
                                                    <?php if (authCheck() && user()->id == $user->id): ?>
                                                        <button type="button" class="button-link text-muted link-abuse-report" onclick="deleteReview('<?= $review->id; ?>','<?= clrDoubleQuotes(trans("confirm_review", true)); ?>');"><?= trans("delete"); ?></button>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 m-t-15">
                                    <?= $pager->links; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row-custom">
                            <?= view('partials/_ad_spaces', ['adSpace' => 'profile', 'class' => 'm-t-30']); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php if (authCheck() && user()->id == $user->id): ?>
    <div class="modal fade" id="reportReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom">
                <form id="form_report_review" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= trans("report_review"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true"><i class="icon-close"></i> </span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="response_form_report_review" class="col-12"></div>
                            <div class="col-12">
                                <input type="hidden" id="report_review_id" name="id" value="">
                                <div class="form-group m-0">
                                    <label><?= trans("description"); ?></label>
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