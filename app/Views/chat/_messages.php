<?php if (!empty($chat)): ?>
    <div id="messagesContainer<?= $chat->id; ?>" class="messages-inner mds-scrollbar">
        <?php if (!empty($messages)):
            foreach ($messages as $item):
                if ($item->deleted_user_id == user()->id) continue;
                $isReceiver = (user()->id == $item->receiver_id);
                $messageClass = $isReceiver ? 'message' : 'message message-right'; ?>

                <div id="chatMessage<?= $item->id; ?>" data-message-id="<?= $item->id; ?>" class="<?= $messageClass; ?>">
                    <?php if ($isReceiver): ?>
                        <div class="flex-item item-user">
                            <div class="user-img">
                                <img src="<?= getUserAvatar($item->user_avatar, $item->user_storage_avatar); ?>" alt="" class="img-profile">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex-item">
                        <div class="message-text"><?= esc($item->message); ?></div>
                        <div class="time"><span><?= timeAgo($item->created_at); ?></span></div>
                    </div>

                    <?php if (!$isReceiver): ?>
                        <div class="flex-item item-user">
                            <div class="user-img">
                                <img src="<?= getUserAvatar($item->user_avatar, $item->user_storage_avatar); ?>" alt="" class="img-profile">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach;
        endif; ?>
    </div>
<?php endif; ?>