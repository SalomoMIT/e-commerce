<div class="reviews-container">
    <div class="row">
        <div class="col-12">
            <div class="review-total">
                <?php if (!empty($reviews)):
                    echo view('partials/_review_stars', ['rating' => $product->rating]);
                    echo '&nbsp;&nbsp;&nbsp;';
                endif; ?>
                <label class="label-review"><?= trans("reviews"); ?>&nbsp;(<?= $reviewsCount; ?>)</label>
                <?php $btnAddReview = false;
                if (authCheck() && $product->user_id != user()->id) {
                    if ($product->listing_type == 'ordinary_listing') {
                        $btnAddReview = true;
                    } else {
                        if ($product->is_free_product) {
                            $btnAddReview = true;
                        } else {
                            if (checkUserBoughtProduct(user()->id, $product->id)) {
                                $btnAddReview = true;
                            }
                        }
                    }
                } ?>
                <?php if ($btnAddReview): ?>
                    <button type="button" data-product-id="<?= $product->id; ?>" class="btn btn-md btn-custom display-flex align-items-center m-l-15 js-open-review-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 640 640" fill="currentColor">
                            <path d="M267.7 576.9C267.7 576.9 267.7 576.9 267.7 576.9L229.9 603.6C222.6 608.8 213 609.4 205 605.3C197 601.2 192 593 192 584L192 512L160 512C107 512 64 469 64 416L64 192C64 139 107 96 160 96L480 96C533 96 576 139 576 192L576 416C576 469 533 512 480 512L359.6 512L267.7 576.9zM332 472.8C340.1 467.1 349.8 464 359.7 464L480 464C506.5 464 528 442.5 528 416L528 192C528 165.5 506.5 144 480 144L160 144C133.5 144 112 165.5 112 192L112 416C112 442.5 133.5 464 160 464L216 464C226.4 464 235.3 470.6 238.6 479.9C239.5 482.4 240 485.1 240 488L240 537.7C272.7 514.6 303.3 493 331.9 472.8z"/>
                        </svg>&nbsp;&nbsp;<?= trans("add_review"); ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php if (empty($reviews)): ?>
                <p class="no-comments-found"><?= trans("no_reviews_found"); ?></p>
            <?php else: ?>
                <ul id="productReviewsListContainer" class="list-unstyled list-reviews">
                    <?= view('product/details/_reviews_list', ['reviews' => $reviews]); ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if (REVIEWS_LOAD_LIMIT < $reviewsCount): ?>
            <div class="col-12 text-center">
                <button type="button" id="btnLoadMoreProductReviews" data-product="<?= $product->id; ?>" data-total="<?= $reviewsCount; ?>" class="btn-load-more btn-load-more-product">
                    <?= trans("load_more_reviews"); ?>&nbsp;
                    <svg width="14" height="14" viewBox="0 0 1792 1792" fill="#333" class="m-l-5" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z"></path>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (authCheck() && user()->id == $product->user_id): ?>
    <div class="modal fade" id="reportReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom">
                <form id="form_report_review" method="post">
                    <div class="modal-header">
                        <h2 class="modal-title"><?= trans("report_review"); ?></h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <span><i class="icon-close"></i> </span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="response_form_report_review" class="col-12"></div>
                            <div class="col-12">
                                <input type="hidden" id="report_review_id" name="id" value="">
                                <div class="form-group m-0">
                                    <label class="control-label"><?= trans("description"); ?></label>
                                    <textarea name="description" class="form-control form-textarea" placeholder="<?= trans("abuse_report_exp"); ?>" minlength="5" maxlength="<?= REVIEW_CHARACTER_LIMIT; ?>" required></textarea>
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
<?php endif;

echo view('partials/_modal_rate_product'); ?>