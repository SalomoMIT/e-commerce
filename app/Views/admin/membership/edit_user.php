<div class="row">
    <div class="col-sm-10">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('edit_user'); ?></h3>
                </div>
            </div>
            <form action="<?= base_url('Membership/editUserPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= esc($user->id); ?>">
                <div class="box-body">
                    <p>
                        <strong><?= esc($user->first_name) . ' ' . esc($user->last_name); ?>&nbsp;<?= !empty($user->username) ? '(' . $user->username . ')' : ''; ?></strong>
                    </p>
                    <?php $role = getRoleById($user->role_id);
                    if (!empty($role)): ?>
                        <div class="form-group">
                            <label class="label label-success"><?= esc(getRoleName($role)); ?></label>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-profile">
                                <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="avatar" class="thumbnail img-responsive img-update" style="max-width: 200px;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-profile">
                                <p>
                                    <a class="btn btn-success btn-sm btn-file-upload">
                                        <?= trans('select_image'); ?>
                                        <input name="file" size="40" accept=".png, .jpg, .jpeg, .webp" onchange="$('#upload-file-info').html($(this).val().replace(/.*[\/\\]/, ''));" type="file">
                                    </a>
                                </p>
                                <p class='label label-info' id="upload-file-info"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= trans('email'); ?></label>
                        <input type="email" class="form-control form-input" name="email" placeholder="<?= trans('email'); ?>" value="<?= esc($user->email); ?>">
                    </div>
                    <div class="form-group">
                        <label><?= trans('shop_name'); ?>&nbsp;(<?= trans("username"); ?>)</label>
                        <input type="text" class="form-control form-input" name="username" placeholder="<?= trans('shop_name'); ?>" value="<?= esc($user->username); ?>">
                    </div>
                    <div class="form-group">
                        <label><?= trans('slug'); ?></label>
                        <input type="text" class="form-control form-input" name="slug" placeholder="<?= trans('slug'); ?>" value="<?= esc($user->slug); ?>">
                    </div>
                    <div class="form-group">
                        <label><?= trans('first_name'); ?></label>
                        <input type="text" class="form-control form-input" name="first_name" placeholder="<?= trans('first_name'); ?>" value="<?= esc($user->first_name); ?>">
                    </div>
                    <div class="form-group">
                        <label><?= trans('last_name'); ?></label>
                        <input type="text" class="form-control form-input" name="last_name" placeholder="<?= trans('last_name'); ?>" value="<?= esc($user->last_name); ?>">
                    </div>
                    <div class="form-group">
                        <label><?= trans('phone_number'); ?></label>
                        <input type="text" class="form-control form-input" name="phone_number" placeholder="<?= trans('phone_number'); ?>" value="<?= esc($user->phone_number); ?>">
                    </div>
                    <?php if (isVendor($user)): ?>
                        <div class="form-group">
                            <label for="commission_mode"><?= trans("commission"); ?></label>
                            <select name="commission_mode" id="commission_mode" class="form-control">
                                <option value="default" <?= $user->is_commission_set == 0 ? 'selected' : ''; ?>><?= trans("default"); ?></option>
                                <option value="custom" <?= $user->is_commission_set == 1 && $user->commission_rate > 0 ? 'selected' : ''; ?>><?= trans("custom"); ?></option>
                                <option value="none" <?= $user->is_commission_set == 1 && $user->commission_rate == 0 ? 'selected' : ''; ?>><?= trans("none"); ?> (0%)</option>
                            </select>
                        </div>

                        <div class="form-group" id="custom_commission_input" style="<?= $user->is_commission_set == 1 && $user->commission_rate > 0 ? '' : 'display: none;'; ?>">
                            <label><?= trans('commission_rate'); ?>&nbsp;(%)</label>
                            <input type="number" name="commission_rate" id="commission_rate" class="form-control" min="0" max="99.99" step="0.01" value="<?= $user->commission_rate > 0 ? esc(formatDecimalClean($user->commission_rate)) : ''; ?>" placeholder="E.g. 5">
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="control-label"><?= trans('shop_description'); ?></label>
                        <textarea class="form-control text-area" name="about_me" placeholder="<?= trans('shop_description'); ?>"><?= esc($user->about_me); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('location'); ?></label>
                        <?= view('partials/_location', ['countries' => $countries, 'countryId' => $user->country_id, 'stateId' => $user->state_id, 'cityId' => $user->city_id, 'isLocationOptional' => true]); ?>
                        <div class="row">
                            <div class="col-12 col-sm-6 m-b-sm-15">
                                <input type="text" name="address" class="form-control form-input" value="<?= esc($user->address); ?>" placeholder="<?= trans("address") ?>">
                            </div>
                            <div class="col-12 col-sm-3">
                                <input type="text" name="zip_code" class="form-control form-input" value="<?= esc($user->zip_code); ?>" placeholder="<?= trans("zip_code") ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("social_media"); ?></label>
                        <?php $socialArray = getSocialLinksArray($user, true);
                        foreach ($socialArray as $item):?>
                            <input type="text" class="form-control m-b-10" name="<?= $item['inputName']; ?>" placeholder="<?= trans($item['inputName']); ?>" value="<?= esc($item['value']); ?>" maxlength="1000">
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>