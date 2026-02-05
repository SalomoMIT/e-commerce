<div class="product-options-container" <?= $optionsContainerMinHeight > 0 ? 'style="min-height: ' . $optionsContainerMinHeight . 'px"' : ''; ?>>
    <div id="variant-options-container"></div>
    <div id="extra-options-container"></div>
</div>
<div class="flex-grow-1"></div>
<input type="hidden" name="variant_id" id="selected-variant-id" value="">

<script>
    const configProductVariants = {
        initialProductData_json: <?= isset($initialProductData_json) ? $initialProductData_json : '{}'; ?>,
        allProductImages_json: <?= isset($allProductImages_json) ? $allProductImages_json : '[]'; ?>,
        activeLang: "<?= escJs($activeLang->short_form); ?>",
        txtInStock: "<?= escJs(trans("in_stock")); ?>",
        txtOutOfStock: "<?= escJs(trans("out_of_stock")); ?>",
        btnAddToCart: '<span class="btn-cart-icon"><i class="icon-cart-solid"></i></span><?= escJs(trans("add_to_cart")); ?>'
    };
</script>
<script src="<?= base_url('assets/js/variants.js'); ?>"></script>