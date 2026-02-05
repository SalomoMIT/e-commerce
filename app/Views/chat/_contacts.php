<div id="chatContactsContainer" class="chat-contacts">
    <?php foreach ($chats as $item):
        $username = $item->user_first_name . ' ' . $item->user_last_name;
        if (isVendorByRoleId($item->user_role_id)) {
            $username = $item->user_username;
        } ?>
        <div class="item">
            <div class="chat-contact" data-chat-id="<?= $item->id; ?>">
                <div class="flex-item">
                    <div class="item-img">
                        <img src="<?= getUserAvatar($item->user_avatar, $item->user_storage_avatar); ?>" alt="<?= esc($username); ?>">
                    </div>
                </div>
                <div class="flex-item flex-item-center">
                    <h6 class="username"><?= esc($username); ?></h6>
                    <p class="subject"><?= esc(characterLimiter($item->subject, 280, '...')); ?></p>
                    <?php if (!empty($item->updated_at)): ?>
                        <div class="time"><?= timeAgo($item->updated_at); ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($item->num_unread_messages > 0): ?>
                    <div class="flex-item">
                        <label id="chatBadge<?= $item->id; ?>" class="badge badge-success"><?= $item->num_unread_messages ?></label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>