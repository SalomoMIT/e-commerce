<?php if (!empty($reviews)):
    foreach ($reviews as $review):?>
        <li class="media">
            <a href="<?= generateProfileUrl($review->user_slug); ?>">
                <img data-src="<?= getUserAvatar($review->user_avatar, $review->user_storage_avatar); ?>" alt="<?= esc($review->user_username); ?>" class="lazyload">
            </a>
            <div class="media-body">
                <div class="row-custom">
                    <?= view('partials/_review_stars', ['rating' => $review->rating]); ?>
                </div>
                <div class="row-custom">
                    <a href="<?= generateProfileUrl($review->user_slug); ?>">
                        <strong class="username"><?= esc($review->user_username); ?></strong>
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
            <?php if (authCheck() && user()->id == $product->user_id): ?>
                <button type="button" class="button-link text-muted link-abuse-report" data-toggle="modal" data-target="#reportReviewModal" onclick="$('#report_review_id').val('<?= $review->id; ?>');"><?= trans("report"); ?></button>
            <?php endif; ?>
        </li>
    <?php endforeach;
endif; ?>