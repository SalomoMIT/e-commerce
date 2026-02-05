<?php if (!authCheck()): ?>
    <div class="modal fade" id="loginModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered login-modal" role="document">
            <div class="modal-content">
                <div class="auth-box">
                    <button type="button" class="close" data-dismiss="modal"><i class="icon-close"></i></button>
                    <div class="title"><?= trans("login"); ?></div>
                    <form id="form_login" novalidate="novalidate">
                        <div class="social-login">
                            <?= view('auth/_social_login', ["orText" => trans("login_with_email")]); ?>
                        </div>
                        <div id="result-login" class="font-size-13"></div>
                        <div id="confirmation-result-login" class="font-size-13"></div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control auth-form-input" placeholder="<?= trans("email_address"); ?>" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control auth-form-input" placeholder="<?= trans("password"); ?>" minlength="4" maxlength="255" required>
                        </div>
                        <div class="form-group text-right">
                            <a href="<?= generateUrl("forgot_password"); ?>" class="link-forgot-password"><?= trans("forgot_password"); ?></a>
                        </div>
                        <div class="form-group m-t-20">
                            <button type="submit" class="btn btn-md btn-custom btn-block"><?= trans("login"); ?></button>
                        </div>
                        <p class="p-social-media m-0 m-t-5"><?= trans("dont_have_account"); ?>&nbsp;<a href="<?= generateUrl("register"); ?>" class="link font-600"><?= trans("register"); ?></a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif;

$defaultCountryId = $generalSettings->single_country_mode == 1 ? $generalSettings->single_country_id : $baseVars->defaultLocation->country_id;
$filterStates = !empty($defaultCountryId) ? getStatesByCountry($defaultCountryId) : array();
$filterCities = !empty($baseVars->defaultLocation->state_id) ? getCitiesByState($baseVars->defaultLocation->state_id) : array(); ?>
<div class="modal fade" id="locationModal" role="dialog" data-country-id="<?= esc($defaultCountryId); ?>">
    <div class="modal-dialog modal-dialog-centered login-modal location-modal" role="document">
        <div class="modal-content">
            <div class="auth-box">
                <button type="button" class="close" data-dismiss="modal"><i class="icon-close"></i></button>
                <div class="title"><?= trans("select_location"); ?></div>
                <p class="location-modal-description"><?= trans("filter_products_location"); ?></p>
                <form action="<?= base_url('Home/setDefaultLocationPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="form_type">
                    <div class="form-group m-b-20 <?= $generalSettings->single_country_mode != 1 ? 'form-group-location-selects' : ''; ?>">
                        <?php if ($generalSettings->single_country_mode != 1): ?>
                            <div class="m-b-5">
                                <select id="select_countries_filter" name="country_id" class="select2 form-control" onchange="getStates(this.value, 'filter');">
                                    <option value=""><?= trans("country"); ?></option>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
                        <?php endif; ?>
                        <div id="get_states_container_filter" class="m-b-5 <?= !empty($filterStates) ? '' : 'display-none'; ?>">
                            <select id="select_states_filter" name="state_id" class="select2 form-control" onchange="getCities(this.value, 'filter');">
                                <option value=""><?= trans('state'); ?></option>
                                <?php if (!empty($filterStates)):
                                    foreach ($filterStates as $item): ?>
                                        <option value="<?= $item->id; ?>" <?= $item->id == $baseVars->defaultLocation->state_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <div id="get_cities_container_filter" class="m-b-5 <?= empty($filterCities) ? 'display-none' : ''; ?>">
                            <select id="select_cities_filter" name="city_id" class="select2 form-control">
                                <option value=""><?= trans('city'); ?></option>
                                <?php if (!empty($filterCities)):
                                    foreach ($filterCities as $item):?>
                                        <option value="<?= $item->id; ?>" <?= $item->id == $baseVars->defaultLocation->city_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit" value="set" class="btn btn-md btn-custom btn-block"><?= trans("select_location"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($newsletterSettings->status == 1 && $newsletterSettings->is_popup_active == 1): ?>
    <div id="modal_newsletter" class="modal fade modal-center modal-newsletter" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="close modal-close-rounded" data-dismiss="modal"><i class="icon-close"></i></button>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 col-left">
                            <img src="<?= getStorageFileUrl($newsletterSettings->image, $newsletterSettings->storage, 'newsletter_bg'); ?>" alt="<?= trans("newsletter") ?>" class="newsletter-img" width="394" height="394">
                        </div>
                        <div class="col-6 col-right">
                            <div class="newsletter-form-container">
                                <div class="newsletter-form">
                                    <div class="modal-title"><?= trans("join_newsletter"); ?></div>
                                    <p class="modal-desc"><?= trans("newsletter_desc"); ?></p>
                                    <form id="form_newsletter_modal" class="form-newsletter" data-form-type="modal">
                                        <div class="form-group">
                                            <div class="modal-newsletter-inputs">
                                                <input type="email" name="email" class="form-control form-input newsletter-input" placeholder="<?= trans('enter_email') ?>">
                                                <button type="submit" class="btn"><?= trans("subscribe"); ?></button>
                                            </div>
                                        </div>
                                        <input type="text" name="url">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<div id="modalAddToCart" class="modal fade modal-product-cart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <strong class="font-600 text-success" style="font-size: 16px;"> <i class="icon-check"></i>&nbsp;<?= trans("product_added_to_cart"); ?></strong>
                <button type="button" class="close modal-close-rounded" data-dismiss="modal"><i class="icon-close"></i></button>
            </div>
            <div id="contentModalCartProduct" class="modal-body"></div>
        </div>
    </div>
</div>