<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $title; ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="row table-filter-container">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-default filter-toggle collapsed m-b-10" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false">
                            <i class="fa fa-filter"></i>&nbsp;&nbsp;<?= trans("filter"); ?>
                        </button>
                        <div class="collapse navbar-collapse" id="collapseFilter">
                            <form action="<?= adminUrl('chat-messages') ?>" method="get">
                                <div class="item-table-filter" style="width: 80px; min-width: 80px;">
                                    <label><?= trans("show"); ?></label>
                                    <select name="show" class="form-control">
                                        <option value="15" <?= inputGet('show') == '15' ? 'selected' : ''; ?>>15</option>
                                        <option value="30" <?= inputGet('show') == '30' ? 'selected' : ''; ?>>30</option>
                                        <option value="60" <?= inputGet('show') == '60' ? 'selected' : ''; ?>>60</option>
                                        <option value="100" <?= inputGet('show') == '100' ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("search"); ?></label>
                                    <input name="q" class="form-control" placeholder="<?= trans("search"); ?>" type="search" value="<?= esc(inputGet('q')); ?>">
                                </div>
                                <div class="item-table-filter md-top-10" style="width: 65px; min-width: 65px;">
                                    <label style="display: block">&nbsp;</label>
                                    <button type="submit" class="btn bg-purple"><?= trans("filter"); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" role="grid">
                        <thead>
                        <tr role="row">
                            <th><?= trans('id'); ?></th>
                            <th><?= trans('subject'); ?></th>
                            <th><?= trans('product'); ?></th>
                            <th><?= trans('sender'); ?></th>
                            <th><?= trans('receiver'); ?></th>
                            <th><?= trans('updated'); ?></th>
                            <th><?= trans('date'); ?></th>
                            <th class="max-width-120"><?= trans('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($chats)):
                            foreach ($chats as $item): ?>
                                <tr>
                                    <td><?= $item->id; ?></td>
                                    <td><?= esc($item->subject); ?></td>
                                    <td class="td-product">
                                        <?php $product = getProduct($item->product_id);
                                        if (!empty($product)):?>
                                            <a href="<?= generateProductUrl($product); ?>" target="_blank" class="table-product-title">
                                                <strong class="font-600"><?= esc($product->title); ?></strong>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $user = getUser($item->sender_id);
                                        if (!empty($user)):?>
                                            <a href="<?= generateProfileUrl($user->slug); ?>" target="_blank" class="link-black">
                                                <strong class="font-600"><?= esc(getUsername($user)); ?></strong>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $user = getUser($item->receiver_id);
                                        if (!empty($user)):?>
                                            <a href="<?= generateProfileUrl($user->slug); ?>" target="_blank" class="link-black">
                                                <strong class="font-600"><?= esc(getUsername($user)); ?></strong>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= timeAgo($item->updated_at); ?></td>
                                    <td><?= formatDate($item->created_at); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-option">
                                            <a href="javascript:void(0)" class="btn btn-sm btn-default btn-edit" data-toggle="modal" data-target="#chatModal<?= $item->id; ?>"><i class="fa fa-comments-o"></i>&nbsp;&nbsp;<?= trans("messages"); ?></a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-default btn-delete" onclick="deleteItem('Admin/deleteChatPost','<?= $item->id; ?>','<?= clrQuotes(trans("confirm_delete")); ?>');"><i class="fa fa-trash-can"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                    <?php if (empty($chats)): ?>
                        <p class="text-center">
                            <?= trans("no_records_found"); ?>
                        </p>
                    <?php endif; ?>
                    <div class="col-sm-12 table-ft">
                        <div class="row">
                            <div class="pull-right">
                                <?= $pager->links; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $model = new \App\Models\ChatModel();
if (!empty($chats)):
    foreach ($chats as $item):
        $messages = $model->getMessagesAdmin($item->id); ?>
        <div id="chatModal<?= $item->id; ?>" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title font-600">#<?= $item->id; ?>&nbsp;<?= esc($item->subject); ?></h4>
                    </div>
                    <div class="modal-body chat-admin">
                        <?php if (!empty($messages)):
                            foreach ($messages as $message):?>
                                <div id="message-row-<?= $message->id; ?>" class="message-row">
                                    <div class="message <?= $item->sender_id == $message->sender_id ? 'message-sender' : 'message-receiver'; ?>">
                                        <strong class="font-600"><?= esc($message->sender_username); ?></strong>
                                        <div class="message-text m-b-5">
                                            <?= esc($message->message); ?>
                                        </div>
                                        <small><?= timeAgo($item->created_at); ?></small>
                                        <a href="javascript:void(0)" class="message-delete" onclick="deleteChatMessage('<?= $message->id; ?>','<?= clrQuotes(trans("confirm_delete")); ?>');"><i class="fa fa-trash-can"></i></a>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach;
endif; ?>