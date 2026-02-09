<div class="col-sm-12 col-lg-6 order-summary-container">
    <h2 style="border-bottom:2px solid #f0f0f0;padding-bottom:15px;margin-bottom:20px;">
        <i class="fas fa-receipt"></i>
        <?= trans("order_summary"); ?>
        <span class="badge ms-2" style="background:#00a99d">
            <strong><?= esc($cart->num_items); ?></strong>
        </span>
    </h2>

    <div style="background:transparent;">
        <!-- LIST ITEM -->
        <div style="max-height:400px;overflow-y:auto;padding:0 8px;">
            <?php if (!empty($groupedSellers)):
                foreach ($groupedSellers as $sellerGroup): ?>
                <div style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:15px;margin-bottom:20px;">
                    
                    <div style="display:flex;align-items:center;margin-bottom:15px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">
                        <i class="fas fa-store" style="color:#007bff;margin-right:8px;"></i>
                        <strong>
                            <?= trans("seller"); ?>:
                            <a href="<?= generateProfileUrl($sellerGroup->seller_slug); ?>" style="color:#007bff;text-decoration:none;">
                                <?= esc($sellerGroup->seller_username); ?>
                            </a>
                        </strong>
                    </div>

                    <?php foreach ($sellerGroup->items as $cartItem): ?>
                    <div style="display:flex;gap:12px;padding:12px;border:1px solid #f0f0f0;border-radius:6px;margin-bottom:12px;">
                        
                        <div style="width:80px;height:80px;flex-shrink:0;">
                            <img src="<?= getOrderImageUrl($cartItem->product_image_data, $cartItem->product_id); ?>"
                                 style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                        </div>

                        <div style="flex:1;">
                            <strong style="display:block;margin-bottom:6px;">
                                <?= esc($cartItem->product_title); ?>
                            </strong>

                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <div>
                                    <small><?= trans("quantity"); ?></small><br>
                                    <strong><?= $cartItem->quantity; ?></strong>
                                </div>
                                <div style="text-align:right;">
                                    <small><?= trans("price"); ?></small><br>
                                    <strong style="color:#00a99d;">
                                        <?= $cart->currency_code ?><?= priceDecimal($cartItem->total_price, $cart->currency_code); ?>
                                    </strong>
                                </div>
                            </div>

                            <?php if ($cartItem->product_type == 'physical'): ?>
                            <div style="background:#f0f7ff;border:1px solid #b3d9ff;border-radius:6px;padding:10px;">
                                <label style="font-weight:600;font-size:13px;">
                                    <i class="fas fa-truck"></i> <?= trans("Courier"); ?>
                                </label>
                                <select id="select_ongkir_<?= $cartItem->id ?>" class="form-select select2" onchange="ongkirOnChange(this)" style="width:100%;">
                                    <option value="">-- <?= trans("select"); ?> --</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- SUMMARY -->
        <div style="padding:15px;background:#fafafa;border:1px solid #e0e0e0;border-radius:8px;margin-top:10px;">
            
            <!-- SUBTOTAL -->
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">
                <span><?= trans("subtotal"); ?></span>
                <strong><?= priceDecimal($cart->totals->subtotal, $cart->currency_code); ?></strong>
            </div>

            <?php if (!empty($cart->totals->vat)): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;background:#e8f4f8;">
                <span><?= trans("vat"); ?></span>
                <strong><?= priceDecimal($cart->totals->vat, $cart->currency_code); ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($cart->totals->shipping_cost)): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;margin:8px 0;background:#e8f4f8;border-radius:4px;">
                <span style="color:#00a99d;">
                    <i class="fas fa-truck"></i> <?= trans("shipping"); ?>
                </span>
                <strong id="totalShipping" style="color:#00a99d;">
                    Rp. 0
                </strong>
            </div>
            <?php endif; ?>

            <!-- TOTAL -->
            <div style="display:flex;justify-content:space-between;align-items:center;border-top:2px solid #333;padding-top:15px;">
                <span style="font-size:16px;font-weight:700;">
                    <?= trans("total"); ?>
                </span>
                <strong style="font-size:18px;color:#00a99d;">
                    <?= priceDecimal($cart->totals->total, $cart->currency_code); ?>
                </strong>
            </div>

        </div>
    </div>
</div>

<script>
    var dataSummary = <?= json_encode($groupedSellers) ?>;
    var selectedKurir = [];
    var selectedDestination = <?= $selectedDestination ?>;
    var totalShip = 0;
</script>