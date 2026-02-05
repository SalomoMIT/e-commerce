<form id="formAddSubcomment<?= $parentComment->id; ?>">
    <div class="form-group">
        <textarea name="comment" class="form-control form-input form-textarea form-comment-text" placeholder="<?= trans("comment"); ?>" maxlength="<?= COMMENT_CHARACTER_LIMIT; ?>"></textarea>
    </div>

    <input type="hidden" name="product_id" value="<?= $parentComment->product_id; ?>">
    <input type="hidden" name="parent_id" value="<?= $parentComment->id; ?>">
    <input type="text" name="comment_name">
    <button type="button" class="btn btn-md btn-custom btn-submit-subcomment" data-comment-id="<?= $parentComment->id; ?>"><?= trans("submit"); ?></button>
</form>