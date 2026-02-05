<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel"><?= trans("rate_this_product"); ?></h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="icon-close"></i> </span></button>
            </div>
            <div class="modal-body">
                <div id="reviewModalMessage"></div>
                <p class="font-600 m-b-0"><?= trans("your_rating"); ?></p>
                <div id="modal-rating-display" class="mb-3">
                    <i class="icon-star-o" data-rating="1"></i>
                    <i class="icon-star-o" data-rating="2"></i>
                    <i class="icon-star-o" data-rating="3"></i>
                    <i class="icon-star-o" data-rating="4"></i>
                    <i class="icon-star-o" data-rating="5"></i>
                </div>
                <div class="form-group">
                    <textarea name="review" id="reviewText" class="form-control form-input form-textarea m-b-5" rows="4" placeholder="<?= trans("write_review"); ?>" required></textarea>
                    <small class="text-muted">*<?= trans("if_review_already_added"); ?></small>
                </div>
                <input type="hidden" id="modal-product-id" value="">
                <input type="hidden" id="modal-rating-value" value="0">
            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-md btn-gray" data-dismiss="modal"><?= trans("close"); ?></button>
                <button type="submit" id="submitReviewBtn" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
            </div>
        </div>
    </div>
</div>