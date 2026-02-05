<?php $session = session();
$userLoginSess = $session->getFlashdata('user-login-pass-wrong'); ?>
<div class="box">
    <div class="box-header with-border">
        <div class="left">
            <h3 class="box-title"><?= trans("users"); ?></h3>
        </div>
        <div class="right">
            <a href="<?= adminUrl('add-user'); ?>" class="btn btn-success btn-add-new">
                <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= trans('add_user'); ?>
            </a>
        </div>
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
                            <form action="<?= adminUrl('users'); ?>" method="get">
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
                                    <label><?= trans("role"); ?></label>
                                    <select name="role" class="form-control">
                                        <option value=""><?= trans("all"); ?></option>
                                        <?php if (!empty($roles)):
                                            foreach ($roles as $item):?>
                                                <option value="<?= $item->id; ?>" <?= inputGet('role') == $item->id ? 'selected' : ''; ?>><?= esc(getRoleName($item)); ?></option>
                                            <?php endforeach;
                                        endif; ?>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("status"); ?></label>
                                    <select name="status" class="form-control">
                                        <option value=""><?= trans("all"); ?></option>
                                        <option value="active" <?= inputGet('status') == 'active' ? 'selected' : ''; ?>><?= trans("active"); ?></option>
                                        <option value="banned" <?= inputGet('status') == 'banned' ? 'selected' : ''; ?>><?= trans("banned"); ?></option>
                                    </select>
                                </div>
                                <div class="item-table-filter">
                                    <label><?= trans("email_status"); ?></label>
                                    <select name="email_status" class="form-control">
                                        <option value=""><?= trans("all"); ?></option>
                                        <option value="confirmed" <?= inputGet('email_status') == 'confirmed' ? 'selected' : ''; ?>><?= trans("confirmed"); ?></option>
                                        <option value="unconfirmed" <?= inputGet('email_status') == 'unconfirmed' ? 'selected' : ''; ?>><?= trans("unconfirmed"); ?></option>
                                    </select>
                                </div>
                                <div class="item-table-filter item-table-filter-long">
                                    <label><?= trans("search"); ?></label>
                                    <input name="q" class="form-control" placeholder="<?= trans("search") ?>" type="search" value="<?= esc(inputGet('q')); ?>">
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
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr role="row">
                            <th width="20"><?= trans("id"); ?></th>
                            <th><?= trans("user"); ?></th>
                            <th><?= trans("email"); ?></th>
                            <th><?= trans("membership_plan"); ?></th>
                            <th><?= trans("status"); ?></th>
                            <th><?= str_replace(':', '', trans("last_seen")); ?></th>
                            <th><?= trans("date"); ?></th>
                            <th class="max-width-120"><?= trans("options"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $membershipModel = new \App\Models\MembershipModel();
                        if (!empty($users)):
                            foreach ($users as $user):
                                $membershipPlan = $membershipModel->getUserPlanByUserId($user->id, false);
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
                                                <?php if ($affiliateSettings->status == 1 && $user->is_affiliate == 1): ?>
                                                    &nbsp;&nbsp;<label class="label bg-blue"><?= trans("affiliate"); ?></label>
                                                <?php endif;
                                                if ($user->is_commission_set == 1): ?>
                                                    &nbsp;&nbsp;<label class="label bg-warning"><i class="fa fa-hand-holding-dollar"></i>&nbsp;<?= esc(formatDecimalClean($user->commission_rate)) ?>%</label>
                                                <?php endif; ?>
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
                                    <td style="max-width: 200px;"><?= !empty($membershipPlan) ? esc($membershipPlan->plan_title) : ''; ?></td>
                                    <td>
                                        <?php if ($user->banned == 0): ?>
                                            <label class="label label-success"><?= trans('active'); ?></label>
                                        <?php else: ?>
                                            <label class="label label-danger"><?= trans('banned'); ?></label>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= timeAgo($user->last_seen); ?></td>
                                    <td><?= formatDate($user->created_at); ?></td>
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
                                                    <?php if (isAdmin() && hasPermission('membership')): ?>
                                                        <li>
                                                            <button type="button" class="btn-list-button btn-change-role" data-toggle="modal" data-target="#loginModal" onclick="setLoginFormData(<?= $user->id; ?>, '<?= clrQuotes(getUsername($user)); ?>')">
                                                                <i class="fa fa-user option-icon"></i><?= trans('login'); ?>
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <button type="button" class="btn-list-button btn-change-role" data-toggle="modal" data-target="#modalRole<?= $user->id; ?>">
                                                            <i class="fa fa-key option-icon"></i><?= trans('change_user_role'); ?>
                                                        </button>
                                                    </li>
                                                    <?php if (!empty($membershipPlans) && $userRole->is_vendor): ?>
                                                        <li>
                                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#modalAssign<?= $user->id; ?>"><i class="fa fa-check-circle option-icon"></i><?= trans('assign_membership_plan'); ?></a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <?php if ($user->email_status != 1): ?>
                                                            <a href="javascript:void(0)" onclick="confirmUserEmail(<?= $user->id; ?>);"><i class="fa fa-check option-icon"></i><?= trans('confirm_user_email'); ?></a>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <?php if ($user->banned == 0): ?>
                                                            <a href="javascript:void(0)" onclick="banRemoveBanUser(<?= $user->id; ?>);"><i class="fa fa-stop-circle option-icon"></i><?= trans('ban_user'); ?></a>
                                                        <?php else: ?>
                                                            <a href="javascript:void(0)" onclick="banRemoveBanUser(<?= $user->id; ?>);"><i class="fa fa-circle option-icon"></i><?= trans('remove_user_ban'); ?></a>
                                                        <?php endif; ?>
                                                    </li>
                                                    <?php if ($affiliateSettings->status == 1): ?>
                                                        <li>
                                                            <?php if ($user->is_affiliate == 1): ?>
                                                                <a href="javascript:void(0)" onclick="addDeleteUserAffiliateProgram(<?= $user->id; ?>);"><i class="fa fa-eraser option-icon"></i><?= trans('delete_from_affiliate_program'); ?></a>
                                                            <?php else: ?>
                                                                <a href="javascript:void(0)" onclick="addDeleteUserAffiliateProgram(<?= $user->id; ?>);"><i class="fa fa-circle-plus option-icon"></i><?= trans('add_to_affiliate_program'); ?></a>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a href="<?= adminUrl('edit-user/' . $user->id); ?>"><i class="fa fa-edit option-icon"></i><?= trans('edit_user'); ?></a>
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
            <div class="col-sm-12">
                <div class="pull-right">
                    <?= $pager->links; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($users)):
    foreach ($users as $user): ?>
        <div id="modalAssign<?= $user->id; ?>" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= base_url('Membership/assignMembershipPlanPost'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="user_id" value="<?= $user->id; ?>">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"><?= trans("assign_membership_plan"); ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label><?= trans("membership_plan"); ?></label>
                                <?php if (!empty($membershipPlans)): ?>
                                    <select class="form-control" name="plan_id" required>
                                        <option value=""><?= trans("select"); ?></option>
                                        <?php foreach ($membershipPlans as $plan): ?>
                                            <option value="<?= $plan->id; ?>"><?= getMembershipPlanName($plan->title_array, selectedLangId()); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><?= trans("submit"); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="modalRole<?= $user->id; ?>" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?= trans('change_user_role'); ?></h4>
                    </div>
                    <form action="<?= base_url('Membership/changeUserRolePost'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="row">
                                    <input type="hidden" name="user_id" value="<?= $user->id; ?>">
                                    <?php if (!empty($roles)):
                                        foreach ($roles as $item):
                                            $rdId = uniqid(); ?>
                                            <div class="col-sm-6 m-b-15">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" name="role_id" value="<?= $item->id; ?>" id="<?= $rdId; ?>" class="custom-control-input" <?= $user->role_id == $item->id ? 'checked' : ''; ?> required>
                                                    <label for="<?= $rdId; ?>" class="custom-control-label"><?= esc(getRoleName($item)); ?></label>
                                                </div>
                                            </div>
                                        <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><?= trans('save_changes'); ?></button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?= trans('close'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach;
endif; ?>

<?php if (isAdmin() && hasPermission('membership')): ?>
    <div id="loginModal" class="modal fade" role="dialog">
        <div class="modal-dialog" style="max-width: 480px;">
            <div class="modal-content">
                <form action="<?= base_url('Membership/loginToUserAccountPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="user_id" id="formLoginUserId">
                    <div class="modal-header text-center">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?= trans("login"); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 30px;">
                            <div class="alert alert-info">
                                <i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;<?= trans("login_to_user_account_exp"); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans("user"); ?>:&nbsp;&nbsp;<span id="formLoginUsername"></span></label>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= trans("enter_your_password"); ?></label>
                            <input type="password" name="password" class="form-control auth-form-input" placeholder="<?= trans("password"); ?>" minlength="4" maxlength="255" required>
                        </div>
                        <?php if (!empty($userLoginSess)): ?>
                            <div class="form-group">
                                <div class="alert alert-danger" style="width: 100%;">
                                    <i class="fa fa-times"></i>&nbsp;&nbsp;<?= trans("wrong_password"); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-block"><?= trans("login"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function setLoginFormData(id, username) {
            $('#formLoginUserId').val(id);
            document.getElementById("formLoginUsername").innerHTML = username;
        }
    </script>
<?php endif; ?>

<?php if (!empty($userLoginSess)): ?>
    <script>
        $(document).ready(function () {
            $("#loginModal").modal();
        });
    </script>
<?php endif; ?>


