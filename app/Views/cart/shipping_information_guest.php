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
                                        <p class="font-600 text-center m-b-30">
                                            <?= trans("checking_out_as_guest"); ?>.&nbsp;<?= trans("have_account"); ?>&nbsp;
                                            <a href="javascript:void(0)" class="link" data-toggle="modal" data-target="#loginModal">
                                                <strong class="link-underlined"><?= trans("login"); ?></strong>
                                            </a>
                                        </p>

                                        <h2 class="title"
                                            data-toggle="collapse"
                                            data-target="#shippingBody"
                                            style="cursor:pointer;">
                                            1.&nbsp;&nbsp;<?= trans("shipping_information"); ?>
                                        </h2>

                                        <div id="shippingBody"
                                             class="collapse <?= empty($cartShippingData->sFirstName) ? 'show': ''; ?>"
                                             data-parent="#checkoutAccordion">

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
                                                                    <input type="hidden" name="province_id" value="">
                                                                    <input type="hidden" name="province_name" value="">
                                                                    <select id="select_province_guest" class="select2 form-control" onchange="getCitiesGuest(this.value,'');" required>
                                                                        <option value="">Pilih Provinsi </option>
                                                                        <?php if (!empty($states)):                                            
                                                                            foreach ($states as $item): ?>                                                    
                                                                                <option value="<?= $item->id; ?>" <?= !empty($cartShippingData)? $cartShippingData->province_id == $item->id?'selected':"" :''?>><?= esc($item->name); ?></option>
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
                                                                    <input type="hidden" name="city_id" value="">
                                                                    <input type="hidden" name="city_name" value="">
                                                                    <select id="select_cities_guest" class="select2 form-control" onchange="getDistrictGuest(this.value,'');" required>
                                                                        <option value="">Pilih Kabupaten / Kota</option>
                                                                        <?php if (!empty($cartShippingData)):  ?>
                                                                        <option value="<?= $cartShippingData->city_id; ?>" selected><?= esc($cartShippingData->city_name); ?></option>
                                                                        <?php endif; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-12 col-md-12 m-b-sm-15">
                                                                    <label class="control-label">District</label>
                                                                    <input type="hidden" name="district_id" value="">
                                                                    <input type="hidden" name="district_name" value="">
                                                                    <select id="select_district_guest" class="select2 form-control" onchange="setAddressPickGuest(this.value,'');" required>                                            
                                                                        <option value="">Pilih Distrik</option>
                                                                        <?php if (!empty($cartShippingData)):  ?>
                                                                            <option value="<?= $cartShippingData->city_id; ?>" selected><?= esc($cartShippingData->city_name); ?></option>
                                                                        <?php endif; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label"><?= trans("address"); ?></label>
                                                            <input type="text" name="shipping_address" class="form-control form-input" value="<?= !empty($cartShippingData->sAddress) ? esc($cartShippingData->sAddress) : ''; ?>" maxlength="250" placeholder="<?= trans("address"); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div id="cart_shipping_methods_container" class="shipping-methods-container">
                                                            <div class="form-group m-t-60 text-right">
                                                                <button type="submit" id="btnShowCartShippingError" class="btn btn-lg btn-custom btn-cart-shipping"><?= trans("Save") ?>&nbsp;&nbsp;<i class="icon-arrow-right m-0"></i></button>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-12 m-t-30">
                                                                    <a href="<?= generateUrl('cart'); ?>" class="link-underlined link-return-cart"><&nbsp;<?= trans("return_to_cart"); ?></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                        <!-- END BODY STEP 1 -->
                                    </div>

                                    <!-- ================= STEP 2 ================= -->
                                    <div class="tab-checkout tab-checkout-closed-bordered">
                                        <h2 class="title"
                                            data-toggle="collapse"
                                            data-target="#paymentMethodBody"
                                            style="cursor:pointer;">
                                            2.&nbsp;&nbsp;<?= trans("payment_method"); ?>
                                        </h2>

                                        <div id="paymentMethodBody"
                                             class="collapse"
                                             data-parent="#checkoutAccordion">

                                            <!-- ISI PAYMENT METHOD -->
                                            <div class="p-3">
                                                Payment Method Content
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= STEP 3 ================= -->
                                    <div class="tab-checkout tab-checkout-closed-bordered border-top-0">
                                        <h2 class="title"
                                            data-toggle="collapse"
                                            data-target="#paymentBody"
                                            style="cursor:pointer;">
                                            3.&nbsp;&nbsp;<?= trans("payment"); ?>
                                        </h2>

                                        <div id="paymentBody"
                                             class="collapse"
                                             data-parent="#checkoutAccordion">

                                            <!-- ISI PAYMENT FINAL -->
                                            <div class="p-3">
                                                Payment Final Content
                                            </div>

                                        </div>
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


<?php if (!empty($cartShippingData->sStateId)): ?>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            getShippingFeeByDistrict(<?= esc($cartShippingData->sStateId); ?>);
        });
    </script> -->
<?php endif; ?>