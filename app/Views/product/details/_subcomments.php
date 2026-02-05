<?php $subComments = [];
if (!empty($commentsArray) && !empty($commentsArray[$parentComment->id]) && countItems($commentsArray[$parentComment->id]) > 0) {
    $subComments = $commentsArray[$parentComment->id];
}
if (!empty($subComments)): ?>
    <div class="row">
        <div class="col-12">
            <div class="comments">
                <ul class="comment-list comment-list-subcomments mds-scrollbar">
                    <?php foreach ($subComments as $subComment): ?>
                        <li id="li-subcomment-<?= $subComment->id; ?>">
                            <div class="left">
                                <?php if (!empty($subComment->user_slug)): ?>
                                    <a href="<?= generateProfileUrl($subComment->user_slug); ?>">
                                        <img src="<?= getUserAvatar($subComment->user_avatar, $subComment->user_storage_avatar); ?>" alt="<?= esc($subComment->name); ?>">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= getUserAvatar($subComment->user_avatar, $subComment->user_storage_avatar); ?>" alt="<?= esc($subComment->name); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="right">
                                <div class="row-custom">
                                    <p class="username">
                                        <?= (!empty($subComment->user_slug)) ? '<a href="' . generateProfileUrl($subComment->user_slug) . '">' : '';
                                        if (!empty($subComment->user_id)):
                                            echo !empty($subComment->user_username) ? esc($subComment->user_username) : esc($subComment->name);
                                        else:
                                            echo esc($subComment->name);
                                        endif;
                                        echo (!empty($subComment->user_slug)) ? '</a>' : ''; ?>
                                    </p>
                                </div>
                                <div class="row-custom comment">
                                    <?= esc($subComment->comment); ?>
                                </div>
                                <div class="row-custom">
                                    <span class="date"><?= timeAgo($subComment->created_at); ?></span>
                                    <?php if (authCheck()):
                                        if ($subComment->user_id == user()->id || hasPermission('comments')): ?>
                                            <button type="button" class="button-link" onclick="deleteComment('<?= $subComment->id; ?>','subcomment','<?= trans("confirm_comment", true); ?>');" aria-label="delete-comment-sub-<?= $subComment->id; ?>">&nbsp;<i class="icon-trash"></i>&nbsp;<?= trans("delete"); ?></button>
                                        <?php endif;
                                    endif;
                                    if (authCheck()):
                                        if ($subComment->user_id != user()->id):?>
                                            <button type="button" class="button-link link-abuse-report" data-toggle="modal" data-target="#reportCommentModal" aria-label="report-comment-sub-<?= $subComment->id; ?>" onclick="$('#report_comment_id').val('<?= $subComment->id; ?>');">
                                                <?= trans("report"); ?>
                                            </button>
                                        <?php endif;
                                    else: ?>
                                        <button type="button" class="button-link link-abuse-report" data-toggle="modal" data-target="#loginModal" aria-label="report-comment-sub-<?= $subComment->id; ?>"><?= trans("report"); ?></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>