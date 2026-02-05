<input type="hidden" value="<?= $commentLimit; ?>" id="blog_comment_limit">
<ul class="blog-comments">
    <?php if (!empty($comments)):
        foreach ($comments as $comment): ?>
            <li>
                <div class="left">
                    <img src="<?= getUserAvatar($comment->user_avatar, $comment->user_storage_avatar); ?>" class="" alt="user">
                </div>
                <div class="right">
                    <p><span class="username"><?= esc($comment->name); ?></span></p>
                    <p class="comment"><?= esc($comment->comment); ?></p>
                    <p>
                        <span class="date"><?= timeAgo($comment->created_at); ?></span>
                        <?php if (authCheck()):
                            if ($comment->user_id == user()->id): ?>
                                <button type="button" class="button-link btn-delete-comment" onclick="deleteBlogComment('<?= $comment->id; ?>','<?= $commentPostId; ?>','<?= trans("confirm_comment", true); ?>');" aria-label="delete-blog-comment-<?= $comment->id; ?>">&nbsp;<i class="icon-trash"></i>&nbsp;<?= trans("delete"); ?></button>
                            <?php endif;
                        endif; ?>
                    </p>
                </div>
            </li>
        <?php endforeach;
    endif; ?>
</ul>
<?php if ($commentsCount > $commentLimit): ?>
    <div id="load_comment_spinner" class="col-12 load-more-spinner">
        <div class="row">
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="row">
            <button class="btn-load-more" onclick="loadMoreBlogComments('<?= $commentPostId; ?>');">
                <?= trans("load_more"); ?>
            </button>
        </div>
    </div>
<?php endif; ?>