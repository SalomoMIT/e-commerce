<?php if (!empty($paymentGateway) && $paymentGateway->name_key == "iyzico" && !empty($checkoutForm)):
    echo $checkoutForm->getcheckoutFormContent(); ?>
    <div id="iyzipay-checkout-form" class="responsive"></div>
<?php endif; ?>