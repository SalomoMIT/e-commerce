<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="shopping-cart shopping-cart-shipping">
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <div class="left">
                                <h1 class="cart-section-title"><?= trans("checkout"); ?></h1>
                                <div id="checkoutAccordion">
                                    <div class="tab-checkout tab-checkout-open m-t-0">
                                        <h2 class="title" >1.&nbsp;&nbsp;<?= trans("shipping_information"); ?></h2>
                                        <?= view('partials/_messages'); ?>
                                        <?php $selectedStateId = 0;
                                        if (empty($shippingAddresses)): ?>
                                            <p class="text-muted"><?= trans("not_added_shipping_address"); ?></p>
                                        <?php else: ?>
                                            <form action="<?= base_url('Cart/shippingPost'); ?>" method="post" id="form_validate">
                                                <?= csrf_field(); ?>
                                                <p class="text-shipping-address"><?= trans("shipping_address"); ?></p>
                                                <div class="row">
                                                    <?php if (!empty($shippingAddresses)):
                                                        foreach ($shippingAddresses as $address):
                                                            if ($address->address_type == 'shipping'):
                                                                $selectedStateId = $address->isdefault==0;
                                                                $country = getCountry($address->country_id);
                                                                $state = getState($address->state_id); ?>
                                                                <div class="col-12 m-b-10">
                                                                    <div class="shipping-address-box shipping-address-box-cart">
                                                                        <div class="custom-control custom-radio">
                                                                            <input type="radio" class="custom-control-input" id="option_shipping_address_<?= $address->id; ?>" name="shipping_address_id" value="<?= $address->id; ?>"
                                                                                <?= $address->isdefault==1 ? 'checked' : ''; ?> onchange="getShippingFeeByDistrict('<?= $address->state_id; ?>');" required>
                                                                            <label class="custom-control-label" for="option_shipping_address_<?= $address->id; ?>">
                                                                                <strong class="m-b-5"><?= esc($address->title); ?></strong>
                                                                                <p>
                                                                                    <?= esc($address->address); ?>&nbsp;<?= esc($address->zip_code); ?>
                                                                                    <?php if (!empty($address->city)):
                                                                                        echo esc($address->city) . '/';
                                                                                    endif;
                                                                                    if (!empty($state->name)):
                                                                                        echo esc($state->name) . '/';
                                                                                    endif;
                                                                                    if (!empty($country->name)):
                                                                                        echo esc($country->name);
                                                                                    endif; ?>
                                                                                </p>
                                                                            </label>
                                                                        </div>
                                                                        <div class="profile-actions-shipping profile-actions-shipping-cart">
                                                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#modalAddress<?= $address->id; ?>">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#777777" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                                                                </svg>
                                                                            </a>
                                                                            <a href="javascript:void(0)" onclick='deleteShippingAddress("<?= $address->id; ?>","<?= trans("confirm_delete", true); ?>");'>
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#777777" class="bi bi-trash3" viewBox="0 0 16 16">
                                                                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                                                                                </svg>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif;
                                                        endforeach;
                                                    endif; ?>
                                                </div>
                                                <div id="cart_shipping_methods_container">
                                                    <?= view('cart/_shipping_methods'); ?>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tab-checkout tab-checkout-closed-bordered">
                                        <h2 class="title">2.&nbsp;&nbsp;<?= trans("payment_method"); ?></h2>
                                    </div>
                                    <div class="tab-checkout tab-checkout-closed-bordered border-top-0">
                                        <h2 class="title">3.&nbsp;&nbsp;<?= trans("payment"); ?></h2>
                                    </div>
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

<div id="modalAddAddress" class="modal fade modal-custom" role="dialog">
    <div class="modal-dialog modal-dialog-shipping-address">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="icon-close"></i></button>
                <h4 class="modal-title"><?= trans("add_new_address"); ?></h4>
            </div>
            <form action="<?= base_url('add-shipping-address-post'); ?>" method="post" id="form_add_shipping_address" class="validate-form">
                <?= csrf_field(); ?>
                <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans("address_type"); ?></label>
                        <select name="address_type" class="select2 form-control" data-minimum-results-for-search="-1" required>
                            <option value="shipping"><?= trans("shipping_address"); ?></option>
                            <option value="billing"><?= trans("billing_address"); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("address_title"); ?></label>
                        <input type="text" name="title" class="form-control form-input" placeholder="<?= trans("address_title"); ?>" maxlength="250" required>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 m-b-sm-15">
                                <label class="control-label"><?= trans("first_name"); ?></label>
                                <input required type="text" name="first_name" class="form-control form-input" placeholder="<?= trans("first_name"); ?>" maxlength="250" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="control-label"><?= trans("last_name"); ?></label>
                                <input required type="text" name="last_name" class="form-control form-input" placeholder="<?= trans("last_name"); ?>" maxlength="250" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 m-b-sm-15">
                                <label class="control-label"><?= trans("email"); ?></label>
                                <input required type="email" name="email" class="form-control form-input" placeholder="<?= trans("email"); ?>" maxlength="250" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="control-label"><?= trans("phone_number"); ?></label>
                                <input required type="text" name="phone_number" class="form-control form-input" placeholder="<?= trans("phone_number"); ?>" maxlength="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <?php if ($generalSettings->single_country_mode != 1): ?>
                                <div class="col-12 col-md-6 m-b-sm-15">
                                    <label class="control-label"><?= trans("country"); ?></label>
                                    <select id="select_countries_new_address" name="country_id" class="select2 select2-req form-control" data-placeholder="<?= trans("country"); ?>" onchange="getStates(this.value,'new_address');" required>
                                        <option></option>
                                        <?php if (!empty($activeCountries)):
                                            foreach ($activeCountries as $item): ?>
                                                <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
                                            <?php endforeach;
                                        endif; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
                                <?php $states = getStatesByCountry($generalSettings->single_country_id);
                            endif; ?>
                            <div class="col-12 <?= $generalSettings->single_country_mode == 1 ? 'col-md-12' : 'col-md-6'; ?>">
                                <label class="control-label"><?= trans("state"); ?></label>
                                <div id="get_states_container_new_address">
                                    <select id="select_states_new_address" name="state_id" class="select2 select2-req form-control" data-placeholder="<?= trans("state"); ?>" data-id="select_states_new_address" required>
                                        <option></option>
                                        <?php if (!empty($states)):
                                            foreach ($states as $item): ?>
                                                <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
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
                                <input type="text" name="city" class="form-control form-input" placeholder="<?= trans("city"); ?>" maxlength="250" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="control-label"><?= trans("zip_code"); ?></label>
                                <input type="text" name="zip_code" class="form-control form-input" placeholder="<?= trans("zip_code"); ?>" maxlength="90" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans("address"); ?></label>
                        <input type="text" name="address" class="form-control form-input" placeholder="<?= trans("address"); ?>" maxlength="490" required>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($shippingAddresses)):
    foreach ($shippingAddresses as $address): ?>
        <div id="modalAddress<?= $address->id; ?>" class="modal fade modal-custom" role="dialog">
            <div class="modal-dialog modal-dialog-shipping-address">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><i class="icon-close"></i></button>
                        <h4 class="modal-title"><?= trans("edit_address"); ?></h4>
                    </div>
                    <form action="<?= base_url('edit-shipping-address-post'); ?>" method="post" id="form_edit_shipping_address_<?= $address->id; ?>" class="validate-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $address->id; ?>">
                        <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label"><?= trans("address_type"); ?></label>
                                <select name="address_type" class="select2 form-control" data-minimum-results-for-search="-1" required>
                                    <option value="shipping" <?= $address->address_type != 'billing' ? 'selected' : ''; ?>><?= trans("shipping_address"); ?></option>
                                    <option value="billing" <?= $address->address_type == 'billing' ? 'selected' : ''; ?>><?= trans("billing_address"); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans("address_title"); ?></label>
                                <input type="text" name="title" class="form-control form-input" value="<?= esc($address->title); ?>" placeholder="<?= trans("address_title"); ?>" maxlength="250" required>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 m-b-sm-15">
                                        <label class="control-label"><?= trans("first_name"); ?></label>
                                        <input required type="text" name="first_name" class="form-control form-input" value="<?= esc($address->first_name); ?>" placeholder="<?= trans("first_name"); ?>" maxlength="250" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="control-label"><?= trans("last_name"); ?></label>
                                        <input required type="text" name="last_name" class="form-control form-input" value="<?= esc($address->last_name); ?>" placeholder="<?= trans("last_name"); ?>" maxlength="250" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 m-b-sm-15">
                                        <label class="control-label"><?= trans("email"); ?></label>
                                        <input required type="email" name="email" class="form-control form-input" value="<?= esc($address->email); ?>" placeholder="<?= trans("email"); ?>" maxlength="250" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="control-label"><?= trans("phone_number"); ?></label>
                                        <input required type="text" name="phone_number" class="form-control form-input" value="<?= esc($address->phone_number); ?>" placeholder="<?= trans("phone_number"); ?>" maxlength="100" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-12 m-b-sm-15">
                                        <label class="control-label"><?= trans("country"); ?></label>
                                        <input disabled class="form-control form-input" value="Indonesia" maxlength="250">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-12 m-b-sm-15">
                                        <label class="control-label">Province</label>
                                        <input type="hidden" name="province_id" value="<?= $address->province_id; ?>">
                                        <input type="hidden" name="province_name" value="<?= $address->province_name; ?>">
                                        <select id="select_provinces_<?= $address->id; ?>" class="select2 form-control" onchange="getCities(this.value,'<?= $address->id; ?>');" required>
                                            <option value="">Pilih Provinsi</option>
                                            <?php if (!empty($states)):                                            
                                                foreach ($states as $item): ?>                                                    
                                                    <option value="<?= $item->id; ?>" <?= $item->id == $address->province_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                <?php endforeach;
                                            endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-12 m-b-sm-15">
                                        <label class="control-label">City</label>
                                        <input type="hidden" name="city_id" value="<?= $address->city_id; ?>">
                                        <input type="hidden" name="city_name" value="<?= $address->city_name; ?>">
                                        <select id="select_cities_<?= $address->id; ?>" class="select2 form-control" onchange="getDistrict(this.value,'<?= $address->id; ?>');" required>
                                            <option value="<?= $address->city_id; ?>"><?= $address->city_name; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-12 m-b-sm-15">
                                        <label class="control-label">District</label>
                                        <input type="hidden" name="district_id" value="<?= $address->district_id; ?>">
                                        <input type="hidden" name="district_name" value="<?= $address->district_name; ?>">
                                        <select id="select_district_<?= $address->id; ?>" class="select2 form-control" onchange="setAddressPick(this.value,'<?= $address->id; ?>');" required>                                            
                                            <option value="<?= $address->district_id; ?>"><?= $address->district_name; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans("address"); ?></label>
                                <textarea
                                    name="address"
                                    class="form-control form-input"
                                    placeholder="<?= trans('address'); ?>"
                                    rows="3"
                                    maxlength="490"
                                    required
                                ><?= esc($address->address); ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer text-right">
                            <button type="submit" class="btn btn-md btn-custom" onclick="checkStateSelected('select_states_address_<?= $address->id; ?>');"><?= trans("save_changes"); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach;
endif; ?>
<!-- <?php if (!empty($shippingAddresses)):
    foreach ($shippingAddresses as $address):?>
        <div id="modalAddress<?= $address->id; ?>" class="modal fade modal-custom" role="dialog">
            <div class="modal-dialog modal-dialog-shipping-address">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><i class="icon-close"></i></button>
                        <h4 class="modal-title"><?= trans("edit_address"); ?></h4>
                    </div>
                    <form action="<?= base_url('edit-shipping-address-post'); ?>" method="post" id="form_edit_shipping_address_<?= $address->id; ?>" class="validate-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $address->id; ?>">
                        <input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label"><?= trans("address_type"); ?></label>
                                <select name="address_type" class="select2 form-control" data-minimum-results-for-search="-1" required>
                                    <option value="shipping" <?= $address->address_type != 'billing' ? 'selected' : ''; ?>><?= trans("shipping_address"); ?></option>
                                    <option value="billing" <?= $address->address_type == 'billing' ? 'selected' : ''; ?>><?= trans("billing_address"); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans("address_title"); ?></label>
                                <input type="text" name="title" class="form-control form-input" value="<?= esc($address->title); ?>" placeholder="<?= trans("address_title"); ?>" maxlength="250" required>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 m-b-sm-15">
                                        <label class="control-label"><?= trans("first_name"); ?></label>
                                        <input type="text" name="first_name" class="form-control form-input" value="<?= esc($address->first_name); ?>" placeholder="<?= trans("first_name"); ?>" maxlength="250" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="control-label"><?= trans("last_name"); ?></label>
                                        <input type="text" name="last_name" class="form-control form-input" value="<?= esc($address->last_name); ?>" placeholder="<?= trans("last_name"); ?>" maxlength="250" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 m-b-sm-15">
                                        <label class="control-label"><?= trans("email"); ?></label>
                                        <input type="email" name="email" class="form-control form-input" value="<?= esc($address->email); ?>" placeholder="<?= trans("email"); ?>" maxlength="250" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="control-label"><?= trans("phone_number"); ?></label>
                                        <input type="text" name="phone_number" class="form-control form-input" value="<?= esc($address->phone_number); ?>" placeholder="<?= trans("phone_number"); ?>" maxlength="100" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <?php if ($generalSettings->single_country_mode != 1): ?>
                                        <div class="col-12 col-md-6 m-b-sm-15">
                                            <label class="control-label"><?= trans("country"); ?></label>
                                            <select id="select_countries_address_<?= $address->id; ?>" name="country_id" class="select2 select2-req form-control" onchange="getStates(this.value, 'address_<?= $address->id; ?>');" required>
                                                <?php if (!empty($activeCountries)):
                                                    foreach ($activeCountries as $item): ?>
                                                        <option value="<?= $item->id; ?>" <?= $item->id == $address->country_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                                    <?php endforeach;
                                                endif; ?>
                                            </select>
                                        </div>
                                        <?php $states = getStatesByCountry($address->country_id);
                                    else: ?>
                                        <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
                                        <?php $states = getStatesByCountry($generalSettings->single_country_id);
                                    endif; ?>
                                    <div class="col-12 <?= $generalSettings->single_country_mode == 1 ? 'col-md-12' : 'col-md-6'; ?>">
                                        <label class="control-label"><?= trans("state"); ?></label>
                                        <div id="get_states_container_address_<?= $address->id; ?>">
                                            <select id="select_states_address_<?= $address->id; ?>" name="state_id" class="select2 select2-req form-control" required>
                                                <?php if (!empty($states)):
                                                    foreach ($states as $item): ?>
                                                        <option value="<?= $item->id; ?>" <?= $item->id == $address->state_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
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
                                        <input type="text" name="city" class="form-control form-input" value="<?= esc($address->city); ?>" placeholder="<?= trans("city"); ?>" maxlength="250" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="control-label"><?= trans("zip_code"); ?></label>
                                        <input type="text" name="zip_code" class="form-control form-input" value="<?= esc($address->zip_code); ?>" placeholder="<?= trans("zip_code"); ?>" maxlength="90" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= trans("address"); ?></label>
                                <input type="text" name="address" class="form-control form-input" value="<?= esc($address->address); ?>" placeholder="<?= trans("address"); ?>" maxlength="490" required>
                            </div>
                        </div>
                        <div class="modal-footer text-right">
                            <button type="submit" class="btn btn-md btn-custom"><?= trans("submit"); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach;
endif; ?> -->

<?php if (!empty($cartShippingData->sStateId)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            getShippingFeeByDistrict(<?= esc($selectedStateId); ?>);
        });
    </script>
<?php endif; ?>
