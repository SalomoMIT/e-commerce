<div class="section-product-details">
    <div class="form-box">
        <div class="row">
            <?php if ($product->product_type != 'digital' && $product->listing_type != 'ordinary_listing'): ?>
                <div class="col-sm-12 col-lg-6">
                    <div class="form-box-head">
                        <h4 class="title"><?= trans('stock'); ?></h4>
                    </div>
                    <div class="form-box-body">
                        <div class="form-group">
                            <input type="number" name="stock" class="form-control form-input" min="0" max="999999999" value="<?= $product->stock; ?>" placeholder="<?= trans("stock"); ?>" required>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="stock" value="<?= $product->stock; ?>">
            <?php endif;
            if ($product->listing_type == 'ordinary_listing' || $productSettings->marketplace_sku == 1): ?>
                <div class="col-sm-12 col-lg-6">
                    <div class="form-box-head">
                        <h4 class="title">
                            <?= trans('sku'); ?>&nbsp;<small style="width: auto;display: inline-block;margin-bottom: 0;margin-top:0;">(<?= trans("product_code"); ?>)</small>
                        </h4>
                    </div>
                    <div class="form-box-body">
                        <div class="form-group">
                            <div class="position-relative">
                                <input type="text" name="sku" id="input_sku" class="form-control form-input" value="<?= $product->sku; ?>" placeholder="<?= trans("sku"); ?>&nbsp;(<?= trans("optional"); ?>)" maxlength="90">
                                <button type="button" class="btn btn-default btn-generate-sku" onclick="$('#input_sku').val(generateUniqueString()).trigger('input');"><?= trans("generate"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="sku" value="">
            <?php endif; ?>
        </div>
    </div>

    <?php if ($product->listing_type == 'sell_on_site' || $product->listing_type == 'license_key'): ?>
        <div class="form-box form-box-price form-box-last">
            <div class="form-box-head">
                <h4 class="title"><?= trans("product_price"); ?></h4>
            </div>
            <div class="form-box-body">

                <?php
                $thousandsSep = ($defaultCurrency->currency_format == 'european') ? '.' : ',';
                $decimalSep   = ($defaultCurrency->currency_format == 'european') ? ',' : '.';
                $spaceMoneySymbol = $defaultCurrency->space_money_symbol;
                ?>

                <div id="price_input_container" class="form-group"
                     data-commission-rate="<?= $commissionRate; ?>"
                     data-currency-symbol="<?= $defaultCurrency->symbol; ?>"
                     data-symbol-direction="<?= $defaultCurrency->symbol_direction; ?>"
                     data-thousands-separator="<?= $thousandsSep; ?>"
                     data-decimal-separator="<?= $decimalSep; ?>"
                     data-space-symbol="<?= $spaceMoneySymbol; ?>">

                    <div class="row">
                        <div class="col-xs-12 col-sm-4 m-b-sm-15">
                            <label class="font-600"><?= trans("price"); ?></label>
                            <?= renderPriceInput('price', $product->price, ['class' => 'form-control price-input', 'id' => 'product_price_input', 'placeholder' => $paymentSettings->default_currency, 'required' => ($product->is_free_product == 1 ? false : true)]); ?>
                        </div>

                        <div class="col-xs-12 col-sm-4 m-b-sm-15">
                            <div class="row align-items-center">
                                <div class="col-sm-12">
                                    <label class="font-600"><?= trans("discounted_price"); ?></label>
                                    <div id="discount_input_container" class="<?= $product->discount_rate == 0 ? 'display-none' : ''; ?>">
                                        <?= renderPriceInput('price_discounted', $product->price_discounted, ['class' => 'form-control price-input', 'id' => 'product_discounted_price_input', 'placeholder' => $paymentSettings->default_currency]); ?>
                                    </div>
                                </div>
                                <div class="col-sm-12 m-t-10">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="checkbox_has_discount" id="checkbox_discount_rate" <?= $product->discount_rate == 0 ? 'checked' : ''; ?>>
                                        <label for="checkbox_discount_rate" class="custom-control-label"><?= trans("no_discount"); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($paymentSettings->vat_status == 1): ?>
                            <div class="col-xs-12 col-sm-4">
                                <div class="row align-items-center">
                                    <div class="col-sm-12">
                                        <label class="font-600"><?= trans("product_based_vat"); ?><small>&nbsp;(<?= trans("vat_exp"); ?>)</small></label>
                                        <div id="vat_input_container" class="<?= $product->vat_rate == 0 ? 'display-none' : ''; ?>">
                                            <div class="input-group">
                                                <span class="input-group-addon">%</span>
                                                <input type="hidden" name="currency" value="<?= $paymentSettings->default_currency; ?>">
                                                <input type="number" name="vat_rate" id="input_vat_rate" aria-describedby="basic-addon-vat" class="form-control form-input" value="<?= $product->vat_rate; ?>" min="0" max="100" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 m-t-10">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="no_vat" id="checkbox_no_vat" <?= $product->vat_rate == 0 ? 'checked' : ''; ?>>
                                            <label for="checkbox_no_vat" class="custom-control-label"><?= trans("no_vat"); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-sm-12 m-t-30" id="calculated_amount_container">
                            <p class="calculated-price">
                                <strong><?= trans("discount_rate"); ?>:&nbsp;&nbsp;</strong>
                                <b id="span_discount_rate" class="earned-price"><?= $product->discount_rate; ?>%</b>
                            </p>
                            <p class="calculated-price">
                                <strong><?= trans("commission_rate"); ?>:&nbsp;&nbsp;</strong>
                                <b class="earned-price"><?= $commissionRate; ?>%</b>
                            </p>
                            <p class="calculated-price">
                                <strong><?= trans("you_will_earn"); ?> (<span id="currency_span"><?= $defaultCurrency->code; ?></span>):&nbsp;&nbsp;</strong>
                                <b id="span_earned_amount" class="earned-price">
                                    <?php
                                    $earnedAmount = 0;
                                    if (!empty($product->price)) {
                                        $price = $product->price;
                                        $discountedPrice = $product->price_discounted;

                                        if ($product->discount_rate == 0) {
                                            $finalPrice = $price;
                                        } else {
                                            $finalPrice = (!empty($discountedPrice) && $discountedPrice > 0) ? $discountedPrice : $price;
                                        }

                                        $earnedAmount = $finalPrice - (($finalPrice * $commissionRate) / 100);
                                    }
                                    echo esc(priceFormatted($earnedAmount, $defaultCurrency->code, true));
                                    ?>
                                </b>
                                &nbsp;&nbsp;<b>+&nbsp;&nbsp;&nbsp;<?= trans("vat"); ?></b>
                                <?php if ($product->product_type != 'digital'): ?>
                                    &nbsp;&nbsp;<b>+&nbsp;&nbsp;&nbsp;<?= trans("shipping_cost"); ?></b>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php if ($product->product_type == 'digital'): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="is_free_product" id="checkbox_free_product" <?= $product->is_free_product == 1 ? 'checked' : ''; ?>>
                                <label for="checkbox_free_product" class="custom-control-label text-danger"><?= trans("free_product"); ?></label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($product->listing_type == 'ordinary_listing'):
        if ($productSettings->classified_price == 1): ?>
            <div class="form-box form-box-last">
                <div class="form-box-head">
                    <h4 class="title"><?= trans('price'); ?></h4>
                </div>
                <div class="form-box-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12 col-md-4 col-lg-3 m-b-sm-15">
                                <label class="font-600"><?= trans("currency"); ?></label>
                                <select name="currency" class="form-control custom-select select2" required>
                                    <?php if (!empty($currencies)):
                                        $allowAllCurrencies = $paymentSettings->allow_all_currencies_for_classied == 1;
                                        foreach ($currencies as $key => $value):
                                            if ($allowAllCurrencies || ($key == $defaultCurrency->code)): ?>
                                                <option value="<?= $key; ?>" <?= $key == $product->currency ? 'selected' : ''; ?>>
                                                    <?= esc($value->name) . ' (' . $value->symbol . ')'; ?>
                                                </option>
                                            <?php endif;
                                        endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="col-xs-12 col-md-4 col-lg-3 m-b-sm-15">
                                <label class="font-600"><?= trans("price"); ?></label>
                                <?= renderPriceInput('price', $product->price, ['id' => 'product_price_input', 'required' => $product->is_free_product == 1 ? false : true], false); ?>
                            </div>
                            <div class="col-xs-12 col-md-4 col-lg-3">
                                <div class="row align-items-center">
                                    <div class="col-sm-12">
                                        <label class="font-600"><?= trans("discounted_price"); ?></label>
                                        <div id="discount_input_container" class="<?= $product->discount_rate == 0 ? 'display-none' : ''; ?>">
                                            <?= renderPriceInput('price_discounted', $product->price_discounted, ['id' => 'product_discounted_price_input'], false); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 m-t-10">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="checkbox_has_discount" id="checkbox_discount_rate" <?= $product->discount_rate == 0 ? 'checked' : ''; ?>>
                                            <label for="checkbox_discount_rate" class="custom-control-label"><?= trans("no_discount"); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;
    elseif ($product->listing_type == 'bidding'): ?>
        <input type="hidden" name="currency" value="<?= $paymentSettings->default_currency; ?>">
    <?php endif; ?>

</div>

<script>
    $(document).on('click', '#checkbox_free_product', function () {
        if ($(this).is(':checked')) {
            $('#price_input_container').hide();
            $(".price-input").prop('required', false);
        } else {
            $('#price_input_container').show();
            $(".price-input").prop('required', true);
        }
    });
</script>
<?php if ($product->is_free_product == 1): ?>
    <style>
        #price_input_container {
            display: none;
        }
    </style>
<?php endif;
if ($product->listing_type == 'sell_on_site' || $product->listing_type == 'license_key'): ?>
    <script>
        $(document).ready(function() {
            const $container = $('#price_input_container');
            const commissionRate = parseFloat($container.attr('data-commission-rate')) || 0;
            const currencySymbol = $container.attr('data-currency-symbol') || '$';
            const symbolDirection = $container.attr('data-symbol-direction') || 'left';
            const thousandsSep = $container.attr('data-thousands-separator') || ',';
            const decimalSep = $container.attr('data-decimal-separator') || '.';
            const useSpace = $container.attr('data-space-symbol') == '1';

            // Recalculate whenever the price inputs change
            $(document).on('input change keyup', '#product_price_input, #product_discounted_price_input', function() {
                calculateEarnings();
            });

            // Toggle visibility of the discounted price input based on checkbox state
            $(document).on('change', '#checkbox_discount_rate', function() {
                if ($(this).is(':checked')) {
                    $('#discount_input_container').hide();
                    $('#product_discounted_price_input').val('');
                } else {
                    $('#discount_input_container').show();
                }
                calculateEarnings();
            });

            // Main Calculation Logic
            function calculateEarnings() {
                let price = getNumericValue($('#product_price_input').val());
                let discountedPrice = getNumericValue($('#product_discounted_price_input').val());
                let isNoDiscount = $('#checkbox_discount_rate').is(':checked');

                let finalPrice = price;
                let discountRate = 0;

                // Apply discount logic
                if (!isNoDiscount && discountedPrice > 0 && discountedPrice < price) {
                    finalPrice = discountedPrice;
                    discountRate = Math.round(((price - discountedPrice) / price) * 100);
                }

                // Calculate Earnings
                let commissionAmount = (finalPrice * commissionRate) / 100;
                let earnedAmount = finalPrice - commissionAmount;

                // Prevent negative earnings
                if (earnedAmount < 0) earnedAmount = 0;

                $('#span_discount_rate').text(discountRate + '%');

                $('#span_earned_amount').text(formatMoney(earnedAmount));
            }

            // Custom Money Formatter (Manual Implementation)
            function formatMoney(amount) {
                let decimals = (amount % 1 === 0) ? 0 : 2;
                let fixedAmount = amount.toFixed(decimals).toString();

                // Separate integer and decimal parts based on standard '.' from toFixed()
                let parts = fixedAmount.split('.');
                let integerPart = parts[0];
                let decimalPart = parts.length > 1 ? parts[1] : '';

                // Add thousands separator regex
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

                // Construct the number string
                let result = integerPart;
                if (decimals > 0) {
                    result += decimalSep + decimalPart;
                }

                // Determine space string based on settings
                let spaceStr = useSpace ? ' ' : '';

                // Append/Prepend Symbol based on direction and spacing
                if (symbolDirection === 'left') {
                    return currencySymbol + spaceStr + result;
                } else {
                    return result + spaceStr + currencySymbol;
                }
            }

            // Smart Input Parser
            function getNumericValue(value) {
                if (!value) return 0;
                let clean = value.toString().replace(/[^0-9.,-]/g, '');
                if (clean === '') return 0;

                let lastDot = clean.lastIndexOf('.');
                let lastComma = clean.lastIndexOf(',');

                // Case: 1.250,50 (European)
                if (lastComma > lastDot) {
                    clean = clean.replace(/\./g, '').replace(',', '.');
                }
                // Case: 1,250.50 (US)
                else if (lastDot > lastComma) {
                    clean = clean.replace(/,/g, '');
                }
                // Case: 12,50 (Only comma)
                else if (lastComma > -1) {
                    clean = clean.replace(',', '.');
                }

                return parseFloat(clean) || 0;
            }
        });
    </script>
<?php endif; ?>
<script>
    $('#checkbox_discount_rate').change(function () {
        if (!this.checked) {
            $("#discount_input_container").show();
        } else {
            var priceStr = $('#product_price_input').val();
            price = parseFloat(priceStr.replace(',', '.'));
            $('#product_discounted_price_input').val(price);
            $("#discount_input_container").hide();
        }
    });
    $('#checkbox_no_vat').change(function () {
        if (!this.checked) {
            $("#vat_input_container").show();
        } else {
            $('#input_vat_rate').val("0");
            $("#vat_input_container").hide();
        }
    });
</script>

