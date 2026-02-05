<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="shopping-cart shopping-cart-shipping">
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="left">
                                <h1 class="cart-section-title"><?= trans("checkout"); ?></h1>
                                <div class="tab-checkout tab-checkout-open m-t-0">
                                    <p class="font-600 text-center m-b-30">
                                        <?= trans("checking_out_as_guest"); ?>.&nbsp;<?= trans("have_account"); ?>&nbsp;
                                        <a href="javascript:void(0)" class="link" data-toggle="modal" data-target="#loginModal">
                                            <strong class="link-underlined"><?= trans("login"); ?></strong>
                                        </a>
                                    </p>

                                    <h2 class="title">1.&nbsp;&nbsp;<?= trans("shipping_information"); ?></h2>
                                    <form action="<?= base_url('Cart/shippingPost'); ?>" method="post" id="form-guest-shipping" class="validate-form">
                                        <?= csrf_field(); ?>
                                        <div class="row">
                                            <div class="col-12 cart-form-shipping-address">
                                                <p class="text-shipping-address"><?= trans("shipping_address") ?></p>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("first_name"); ?></label>
                                                            <input type="text" name="shipping_first_name" class="form-control form-input" value="<?= !empty($cartShippingData->sFirstName) ? esc($cartShippingData->sFirstName) : ''; ?>" maxlength="250" placeholder="<?= trans("first_name"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("last_name"); ?></label>
                                                            <input type="text" name="shipping_last_name" class="form-control form-input" value="<?= !empty($cartShippingData->sLastName) ? esc($cartShippingData->sLastName) : ''; ?>" maxlength="250" placeholder="<?= trans("last_name"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("email"); ?></label>
                                                            <input type="email" name="shipping_email" class="form-control form-input" value="<?= !empty($cartShippingData->sEmail) ? esc($cartShippingData->sEmail) : ''; ?>" maxlength="250" placeholder="<?= trans("email"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("phone_number"); ?></label>
                                                            <input type="text" name="shipping_phone_number" class="form-control form-input" value="<?= !empty($cartShippingData->sPhoneNumber) ? esc($cartShippingData->sPhoneNumber) : ''; ?>" maxlength="100" placeholder="<?= trans("phone_number"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php if ($generalSettings->single_country_mode != 1): ?>
                                                            <div class="col-12 col-md-6 m-b-sm-15">
                                                                <label class="control-label"><?= trans("country"); ?></label>
                                                                <select id="select_countries_guest_address" name="shipping_country_id" class="select2 select2-req form-control form-input" data-placeholder="<?= trans("country"); ?>" onchange="getStates(this.value,'guest_address'); $('.cart-seller-shipping-options').empty();" required>
                                                                    <option></option>
                                                                    <?php if (!empty($activeCountries)):
                                                                        foreach ($activeCountries as $item): ?>
                                                                            <option value="<?= $item->id; ?>" <?= !empty($cartShippingData->sCountryId) && $cartShippingData->sCountryId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                                        <?php endforeach;
                                                                    endif; ?>
                                                                </select>
                                                            </div>
                                                            <?php if (!empty($cartShippingData->sCountryId)):
                                                                $states = getStatesByCountry($cartShippingData->sCountryId);
                                                            endif;
                                                        else: ?>
                                                            <input type="hidden" name="shipping_country_id" value="<?= $generalSettings->single_country_id; ?>">
                                                            <?php $states = getStatesByCountry($generalSettings->single_country_id);
                                                        endif; ?>
                                                        <div id="get_states_container_guest_address" class="col-12 <?= $generalSettings->single_country_mode == 1 ? 'col-md-12' : 'col-md-6'; ?>">
                                                            <label class="control-label"><?= trans("state"); ?></label>
                                                            <select id="select_states_guest_address" name="shipping_state_id" class="select2 select2-req form-control" data-placeholder="<?= trans("state"); ?>" onchange="getShippingFeeByDistrict(this.value);" required>
                                                                <option></option>
                                                                <?php if (!empty($states)):
                                                                    foreach ($states as $item): ?>
                                                                        <option value="<?= $item->id; ?>" <?= !empty($cartShippingData->sStateId) && $cartShippingData->sStateId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                                    <?php endforeach;
                                                                endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("city"); ?></label>
                                                            <input type="text" name="shipping_city" class="form-control form-input" value="<?= !empty($cartShippingData->sCity) ? esc($cartShippingData->sCity) : ''; ?>" maxlength="250" placeholder="<?= trans("city"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("zip_code"); ?></label>
                                                            <input type="text" name="shipping_zip_code" class="form-control form-input" value="<?= !empty($cartShippingData->sZipCode) ? esc($cartShippingData->sZipCode) : ''; ?>" maxlength="90" placeholder="<?= trans("zip_code"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"><?= trans("address"); ?></label>
                                                    <input type="text" name="shipping_address" class="form-control form-input" value="<?= !empty($cartShippingData->sAddress) ? esc($cartShippingData->sAddress) : ''; ?>" maxlength="250" placeholder="<?= trans("address"); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-12 cart-form-billing-address" <?= empty($selectedSameAddressForBilling) ? 'style="display: block;"' : ''; ?>>
                                                <p class="text-shipping-address"><?= trans("billing_address") ?></p>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("first_name"); ?></label>
                                                            <input type="text" name="billing_first_name" class="form-control form-input" value="<?= !empty($cartShippingData->bFirstName) ? esc($cartShippingData->bFirstName) : ''; ?>" maxlength="250" placeholder="<?= trans("first_name"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("last_name"); ?></label>
                                                            <input type="text" name="billing_last_name" class="form-control form-input" value="<?= !empty($cartShippingData->bLastName) ? esc($cartShippingData->bLastName) : ''; ?>" maxlength="250" placeholder="<?= trans("last_name"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("email"); ?></label>
                                                            <input type="email" name="billing_email" class="form-control form-input" value="<?= !empty($cartShippingData->bEmail) ? esc($cartShippingData->bEmail) : ''; ?>" maxlength="250" placeholder="<?= trans("email"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("phone_number"); ?></label>
                                                            <input type="text" name="billing_phone_number" class="form-control form-input" value="<?= !empty($cartShippingData->bPhoneNumber) ? esc($cartShippingData->bPhoneNumber) : ''; ?>" maxlength="100" placeholder="<?= trans("phone_number"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php if ($generalSettings->single_country_mode != 1): ?>
                                                            <div class="col-12 col-md-6 m-b-sm-15">
                                                                <label class="control-label"><?= trans("country"); ?></label>
                                                                <select id="select_countries_guest_billing" name="billing_country_id" class="select2 form-control <?= empty($selectedSameAddressForBilling) ? 'select2-req' : ''; ?>" data-placeholder="<?= trans("country"); ?>" onchange="getStates(this.value,'guest_billing');" required>
                                                                    <option></option>
                                                                    <?php if (!empty($activeCountries)):
                                                                        foreach ($activeCountries as $item): ?>
                                                                            <option value="<?= $item->id; ?>" <?= !empty($cartShippingData->bCountryId) && $cartShippingData->bCountryId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                                        <?php endforeach;
                                                                    endif; ?>
                                                                </select>
                                                            </div>
                                                            <?php if (!empty($cartShippingData->bCountryId)):
                                                                $states = getStatesByCountry($cartShippingData->bCountryId);
                                                            endif;
                                                        else: ?>
                                                            <input type="hidden" name="billing_country_id" value="<?= $generalSettings->single_country_id; ?>">
                                                            <?php $states = getStatesByCountry($generalSettings->single_country_id);
                                                        endif; ?>
                                                        <div class="col-12 <?= $generalSettings->single_country_mode == 1 ? 'col-md-12' : 'col-md-6'; ?>">
                                                            <label class="control-label"><?= trans("state"); ?></label>
                                                            <div id="get_states_container_guest_billing">
                                                                <select id="select_states_guest_billing" name="billing_state_id" class="select2 form-control <?= empty($selectedSameAddressForBilling) == 1 ? 'select2-req' : ''; ?>" data-placeholder="<?= trans("state"); ?>" required>
                                                                    <option></option>
                                                                    <?php if (!empty($states)):
                                                                        foreach ($states as $item): ?>
                                                                            <option value="<?= $item->id; ?>" <?= !empty($cartShippingData->bStateId) && $cartShippingData->bStateId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                                        <?php endforeach;
                                                                    endif; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 m-b-sm-15">
                                                            <label class="control-label"><?= trans("city"); ?></label>
                                                            <input type="text" name="billing_city" class="form-control form-input" value="<?= !empty($cartShippingData->bCity) ? esc($cartShippingData->bCity) : ''; ?>" maxlength="250" placeholder="<?= trans("city"); ?>" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="control-label"><?= trans("zip_code"); ?></label>
                                                            <input type="text" name="billing_zip_code" class="form-control form-input" value="<?= !empty($cartShippingData->bZipCode) ? esc($cartShippingData->bZipCode) : ''; ?>" maxlength="90" placeholder="<?= trans("zip_code"); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"><?= trans("address"); ?></label>
                                                    <input type="text" name="billing_address" class="form-control form-input" value="<?= !empty($cartShippingData->bAddress) ? esc($cartShippingData->bAddress) : ''; ?>" maxlength="250" placeholder="<?= trans("address"); ?>" required>
                                                </div>
                                            </div>
                                            <!-- <div class="col-12">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" name="use_same_address_for_billing" value="1" id="use_same_address_for_billing" <?= $selectedSameAddressForBilling == 1 ? 'checked' : ''; ?>>
                                                        <label for="use_same_address_for_billing" class="custom-control-label"><?= trans("use_same_address_for_billing"); ?></label>
                                                    </div>
                                                </div>
                                            </div> -->
                                            <div class="col-12">
                                                <div id="cart_shipping_methods_container" class="shipping-methods-container">
                                                    <?= view("cart/_shipping_methods"); ?>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="cart-shipping-loader">
                                                            <div class="spinner">
                                                                <div class="bounce1"></div>
                                                                <div class="bounce2"></div>
                                                                <div class="bounce3"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-checkout tab-checkout-closed-bordered">
                                    <h2 class="title">2.&nbsp;&nbsp;<?= trans("payment_method"); ?></h2>
                                </div>
                                <div class="tab-checkout tab-checkout-closed-bordered border-top-0">
                                    <h2 class="title">3.&nbsp;&nbsp;<?= trans("payment"); ?></h2>
                                </div>
                            </div>
                        </div>

                        <?= view('cart/_order_summary'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($cartShippingData->sStateId)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            getShippingFeeByDistrict(<?= esc($cartShippingData->sStateId); ?>);
        });
    </script>
<?php endif; ?>