<?php if (!empty($comments)):
    foreach ($comments as $comment): ?>
        <li id="li-comment-<?= $comment->id; ?>">
            <div class="left">
                <?php if (!empty($comment->user_slug)): ?>
                    <a href="<?= generateProfileUrl($comment->user_slug); ?>">
                        <img data-src="<?= getUserAvatar($comment->user_avatar, $comment->user_storage_avatar); ?>" alt="<?= esc($comment->name); ?>" class="lazyload">
                    </a>
                <?php else: ?>
                    <img data-src="<?= getUserAvatar($comment->user_avatar, $comment->user_storage_avatar); ?>" alt="<?= esc($comment->name); ?>" class="lazyload">
                <?php endif; ?>
            </div>
            <div class="right">
                <div class="row-custom">
                    <p class="username">
                        <?= (!empty($comment->user_slug)) ? '<a href="' . generateProfileUrl($comment->user_slug) . '">' : '';
                        if (!empty($comment->user_id)):
                            echo !empty($comment->user_username) ? esc($comment->user_username) : esc($comment->name);
                        else:
                            echo esc($comment->name);
                        endif;
                        echo (!empty($comment->user_slug)) ? '</a>' : ''; ?>
                    </p>
                </div>
                <div class="row-custom comment">
                    <?= esc($comment->comment); ?>
                </div>
                <div class="row-custom">
                    <span class="date"><?= timeAgo($comment->created_at); ?></span>

                    <?php if(authCheck()):?>
                        <button type="button" class="button-link" onclick="showCommentForm('<?= $comment->id; ?>');" aria-label="reply-comment-<?= $comment->id; ?>"><i class="icon-reply"></i> <?= trans('reply'); ?></button>
                    <?php else: ?>
                        <button type="button" class="button-link" data-toggle="modal" data-target="#loginModal" aria-label="reply-comment-<?= $comment->id; ?>"><i class="icon-reply"></i> <?= trans('reply'); ?></button>
                    <?php endif; ?>

                    <?php if (authCheck()):
                        if ($comment->user_id == user()->id || hasPermission('comments')): ?>
                            <button type="button" class="button-link" aria-label="delete-comment-<?= $comment->id; ?>" onclick="deleteComment('<?= $comment->id; ?>','comment','<?= trans("confirm_comment", true); ?>');">&nbsp;<i class="icon-trash"></i>&nbsp;<?= trans("delete"); ?></button>
                        <?php endif;
                    endif;
                    if (authCheck()): ?>
                        <?php if ($comment->user_id != user()->id): ?>
                            <button type="button" class="button-link link-abuse-report" data-toggle="modal" data-target="#reportCommentModal" aria-label="about-report-<?= $comment->id; ?>" onclick="$('#report_comment_id').val('<?= $comment->id; ?>');"><?= trans("report"); ?></button>
                        <?php endif;
                    else: ?>
                        <button type="button" class="button-link link-abuse-report" data-toggle="modal" data-target="#loginModal" aria-label="about-report-<?= $comment->id; ?>"><?= trans("report"); ?></button>
                    <?php endif; ?>
                </div>
                <div id="subCommentForm<?= $comment->id; ?>" class="row-custom row-sub-comment visible-sub-comment">
                </div>
                <div class="row-custom row-sub-comment">
                    <?= view('product/details/_subcomments', ['parentComment' => $comment, 'commentsArray' => $commentsArray]); ?>
                </div>
            </div>
        </li>
    <?php endforeach;
endif; ?>
