<div class="box">
    <div class="box-header with-border">
        <div class="left">
            <h3 class="box-title"><?= trans("account_deletion_requests"); ?></h3>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr role="row">
                            <th width="20"><?= trans("id"); ?></th>
                            <th><?= trans("user"); ?></th>
                            <th><?= trans("email"); ?></th>
                            <th><?= trans("status"); ?></th>
                            <th><?= str_replace(':', '', trans("last_seen")); ?></th>
                            <th><?= trans("date"); ?></th>
                            <th class="max-width-120"><?= trans("options"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($users)):
                            foreach ($users as $user):
                                $userRole = getRoleById($user->role_id);
                                $roleColor = 'bg-gray';
                                if (!empty($userRole)) {
                                    if ($userRole->is_super_admin) {
                                        $roleColor = 'bg-maroon';
                                    } elseif ($userRole->is_admin) {
                                        $roleColor = 'bg-info';
                                    } elseif ($userRole->is_vendor) {
                                        $roleColor = 'bg-purple';
                                    }
                                } ?>
                                <tr>
                                    <td><?= esc($user->id); ?></td>
                                    <td>
                                        <div class="tbl-table">
                                            <div class="left">
                                                <a href="<?= generateProfileUrl($user->slug); ?>" target="_blank" class="table-link">
                                                    <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="user" class="img-responsive">
                                                </a>
                                            </div>
                                            <div class="right">
                                                <div class="m-b-5" style="word-break: break-word">
                                                    <a href="<?= generateProfileUrl($user->slug); ?>" target="_blank" class="table-link">
                                                        <?= esc($user->first_name) . ' ' . esc($user->last_name); ?>&nbsp;<?= !empty($user->username) ? '(' . $user->username . ')' : ''; ?>
                                                    </a>
                                                </div>
                                                <label class="label <?= $roleColor; ?>">
                                                    <?= esc(getRoleName($userRole)); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= esc($user->email);
                                        if ($user->email_status == 1): ?>
                                            <small class="text-success">(<?= trans("confirmed"); ?>)</small>
                                        <?php else: ?>
                                            <small class="text-danger">(<?= trans("unconfirmed"); ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user->banned == 0): ?>
                                            <label class="label label-success"><?= trans('active'); ?></label>
                                        <?php else: ?>
                                            <label class="label label-danger"><?= trans('banned'); ?></label>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= timeAgo($user->last_seen); ?></td>
                                    <td><?= !empty($user->account_delete_req_date) ? formatDate($user->account_delete_req_date) : ''; ?></td>
                                    <td>
                                        <?php $showOptions = true;
                                        if ($userRole->is_super_admin) {
                                            $showOptions = false;
                                            $activeUserRole = getRoleById(user()->role_id);
                                            if (!empty($activeUserRole) && $activeUserRole->is_super_admin) {
                                                $showOptions = true;
                                            }
                                        }
                                        if ($showOptions): ?>
                                            <div class="dropdown">
                                                <button class="btn bg-purple dropdown-toggle btn-select-option" type="button" data-toggle="dropdown"><?= trans('select_option'); ?><span class="caret"></span></button>
                                                <ul class="dropdown-menu options-dropdown">
                                                    <li>
                                                        <a href="<?= adminUrl('user-details/' . $user->id); ?>"><i class="fa fa-info-circle option-icon"></i><?= trans('user_details'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)" onclick="performAction('Membership/cancelAccountDeleteRequestPost','<?= $user->id; ?>','<?= trans("confirm_action", true); ?>');"><i class="fa fa-times option-icon"></i><?= trans('cancel'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)" onclick="deleteItem('Membership/deleteUserPost','<?= $user->id; ?>','<?= trans("confirm_user", true); ?>');"><i class="fa fa-trash-can option-icon"></i><?= trans('delete'); ?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                    <?php if (empty($users)): ?>
                        <p class="text-center text-muted"><?= trans("no_records_found"); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>