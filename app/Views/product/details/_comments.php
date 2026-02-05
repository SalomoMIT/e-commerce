<?php $parentComments = [];
if (!empty($commentsArray) && !empty($commentsArray[0]) && countItems($commentsArray[0]) > 0) {
    $parentComments = $commentsArray[0];
} ?>
<div class="comments-container">
    <div class="row">
        <div class="col-12">
            <div id="comment-result">
                <div class="row">
                    <div class="col-12">
                        <div class="comments">
                            <div class="row-custom m-b-30">
                                <div class="row-comments-tab-title">
                                    <label class="label-comment"><?= trans("comments"); ?>&nbsp;(<?= $commentsCount; ?>)</label>
                                </div>
                            </div>
                            <?php if (empty($parentComments)): ?>
                                <p class="no-comments-found"><?= trans("no_comments_found"); ?></p>
                            <?php endif; ?>
                            <ul id="productCommentsListContainer" class="comment-list">
                                <?= view('product/details/_comments_list', ['comments' => $parentComments, 'commentsArray' => $commentsArray]); ?>
                            </ul>
                        </div>
                    </div>
                    <?php if (COMMENTS_LOAD_LIMIT < $commentsCount): ?>
                        <div class="col-12 text-center">
                            <button type="button" id="btnLoadMoreProductComments" data-product="<?= $product->id; ?>" data-total="<?= $commentsCount; ?>" class="btn-load-more btn-load-more-product">
                                <?= trans("load_more_comments"); ?>&nbsp;
                                <svg width="14" height="14" viewBox="0 0 1792 1792" fill="#333" class="m-l-5" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z"></path>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-add-comment">
            <label class="font-600 m-b-15"><?= trans("add_comment"); ?></label>
            <form id="formAddComment">
                <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                <input type="text" name="comment_name">
                <?php if (!authCheck()): ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" name="name" class="form-control form-input" placeholder="<?= trans("name"); ?>" maxlength="255">
                        </div>
                        <div class="form-group col-md-6">
                            <input type="email" name="email" class="form-control form-input" placeholder="<?= trans("email_address"); ?>" maxlength="255">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <textarea name="comment" id="comment_text" class="form-control form-input form-textarea" placeholder="<?= trans("comment"); ?>..." maxlength="<?= COMMENT_CHARACTER_LIMIT; ?>"></textarea>
                </div>

                <?= view('partials/_cf_turnstile'); ?>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-md btn-custom"><?= trans("post_comment"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>