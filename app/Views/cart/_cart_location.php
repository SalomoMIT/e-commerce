<form action="<?= base_url('cart/payment-method-post'); ?>" method="post" id="form_validate">
    <?= csrf_field(); ?>
    <input type="hidden" name="checkout_type" value="<?= esc($checkoutType); ?>">
    <?php if ($generalSettings->single_country_mode != 1): ?>
        <div class="m-b-5">
            <select id="select_countries_cart" name="country_id" class="select2 form-control" onchange="getStates(this.value, 'cart');" required>
                <option value=""><?= trans('country'); ?></option>
                <?php if (!empty($activeCountries)):
                    foreach ($activeCountries as $item): ?>
                        <option value="<?= $item->id; ?>" <?= $item->id == $baseVars->defaultLocation->country_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>
    <?php else: ?>
        <input type="hidden" name="country_id" value="<?= $generalSettings->single_country_id; ?>">
        <?php $states = getStatesByCountry($generalSettings->single_country_id); ?>
    <?php endif; ?>
    <div id="get_states_container_cart" class="m-b-5 <?= !empty($states) ? '' : 'display-none'; ?>">
        <select id="select_states_cart" name="state_id" class="select2 form-control" required>
            <option value=""><?= trans('state'); ?></option>
            <?php if (!empty($states)):
                foreach ($states as $item): ?>
                    <option value="<?= $item->id; ?>" <?= $item->id == $baseVars->defaultLocation->state_id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                <?php endforeach;
            endif; ?>
        </select>
    </div>

    <div class="form-group m-t-30 text-right">
        <button type="submit" name="submit" value="location" class="btn btn-lg btn-custom btn-continue-payment"><?= trans("continue_to_payment") ?>&nbsp;&nbsp;<i class="icon-arrow-right m-0"></i></button>
    </div>

    <?php if ($checkoutType == 'product'): ?>
        <div class="row">
            <div class="col-12 m-t-30">
                <a href="<?= generateUrl('cart'); ?>" class="link-underlined link-return-cart"><&nbsp;<?= trans("return_to_cart"); ?></a>
            </div>
        </div>
    <?php endif; ?>
</form>
