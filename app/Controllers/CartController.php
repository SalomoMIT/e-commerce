<?php

namespace App\Controllers;

use App\Libraries\DlocalGo;
use App\Libraries\Iyzico;
use App\Libraries\MercadoPago;
use App\Libraries\Midtrans;
use App\Libraries\PayTabs;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Models\CartModel;
use App\Models\CheckoutModel;
use App\Models\ProfileModel;
use App\Models\ShippingModel;
use App\Services\OngkirService;

class CartController extends BaseController
{
    protected $cartModel;
    protected $checkoutModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->cartModel = new CartModel();
        $this->checkoutModel = new CheckoutModel();

        $router = service('router');
        $methodName = $router->methodName();
        if ($methodName !== 'payment') {
            helperSetSession('mds_cart_has_changed', 1);
        }
    }

    /**
     * Cart
     */
    public function cart()
    {
        $data = setPageMeta(trans("shopping_cart"));
        $data['isTranslatable'] = true;
        $data['cart'] = $this->cartModel->getCart();
        $data['userSession'] = getUserSession();

        helperDeleteSession('mds_service_payment');

        echo view('partials/_header', $data);
        echo view('cart/cart', $data);
        echo view('partials/_footer');
    }

    /**
     * Add to Cart
     */
    public function addToCart()
    {
        $productId = inputPost('product_id');
        $quantity = inputPost('product_quantity');
        $variantId = inputPost('variant_id');
        $extraOptions = inputPost('extra_options');
        $product = $this->productModel->getActiveProduct($productId);
        $data = ['result' => 0];
        if (!empty($product) && $product->status == 1) {
            $cartItemId = $this->cartModel->addToCart($product, $quantity, $variantId, $extraOptions);
            if (!empty($cartItemId)) {
                $cart = $this->cartModel->getCart();
                $cartItem = $this->cartModel->getCartItem($cartItemId);
                if (empty($cart) || empty($cartItem)) {
                    return jsonResponse(['result' => 0]);
                }
                $cartHasPhysicalProduct = !empty($cart) && !empty($cart->has_physical_product) ? true : false;
                $relatedProducts = $this->productModel->getRelatedProducts($product->id, $product->category_id);
                $data = [
                    'result' => 1,
                    'productCount' => 1,
                    'htmlCartProduct' => view('cart/_modal_cart_product', ['cartItem' => $cartItem, 'product' => $product, 'relatedProducts' => $relatedProducts, 'cartHasPhysicalProduct' => $cartHasPhysicalProduct])
                ];
            }
        }
        return jsonResponse($data);
    }

    /**
     * Add to Cart qQuote
     */
    public function addToCartQuote()
    {
        $quoteRequestId = inputPost('id');
        if (!empty($this->cartModel->addToCartQuote($quoteRequestId))) {
            return redirect()->to(generateUrl('cart'));
        }
        return redirect()->back();
    }

    /**
     * Remove from Cart
     */
    public function removeFromCart()
    {
        $cartItemId = inputPost('cart_item_id');
        $this->cartModel->removeCartItem($cartItemId);
        return jsonResponse();
    }

    /**
     * Remove Cart Discount Coupon
     */
    public function removeCartDiscountCoupon()
    {
        $cartRaw = $this->cartModel->fetchRawCartData();
        if (!empty($cartRaw)) {
            $this->cartModel->removeCoupon($cartRaw->id);
        }

        return jsonResponse();
    }

    /**
     * Update Cart Item Quantity
     */
    public function updateCartItemQuantity()
    {
        $cartItemId = inputPost('cart_item_id');
        $quantity = clrNum(inputPost('quantity'));
        $this->cartModel->updateItemQuantity($cartItemId, $quantity);
        return jsonResponse();
    }

    /**
     * Coupon Code Post
     */
    public function couponCodePost()
    {
        $couponCode = inputPost('coupon_code');
        $this->cartModel->applyCoupon($couponCode);
        return redirect()->to(generateUrl('cart'));
    }

    /**
     * Shipping
     */
    public function shipping()
    {
        $data = setPageMeta(trans("shopping_cart"));
        $data['isTranslatable'] = true;

        helperDeleteSession('mds_service_payment');

        $profileModel = new ProfileModel();
        $shippingModel = new ShippingModel();
        $ongkirService = new OngkirService();
        $cart = $this->cartModel->getCart();
        if (empty($cart) || !$cart->is_valid) {
            return redirect()->to(generateUrl('cart'));
        }

        //check shipping status
        if ($this->productSettings->marketplace_shipping != 1) {
            return redirect()->to(generateUrl('cart'));
        }
        //check guest checkout
        if (empty(authCheck()) && $this->generalSettings->guest_checkout != 1) {
            return redirect()->to(generateUrl('cart'));
        }

        //check auth for digital products
        // if (!authCheck() && $cart->has_digital_product == true) {
        //     setErrorMessage(trans("msg_digital_product_register_error"));
        //     return redirect()->to(generateUrl('register'));
        // }

        //check physical products
        if ($cart->has_physical_product == false) {
            return redirect()->to(generateUrl('cart'));
        }

        $data['cartShippingData'] = !empty($cart->shipping_data) ? json_decode($cart->shipping_data) : [];
        // print_r($cart->shipping_data);die();
        $data['shippingAddresses'] = array();
        $data['selectedShippingAddressId'] = 0;
        $data['selectedBillingAddressId'] = 0;
        $data['selectedSameAddressForBilling'] = 1;
        $stateId = null;
        if (!empty($data['cartShippingData'])) {
            if (!empty($data['cartShippingData']->shippingStateId)) {
                $stateId = $data['cartShippingData']->shippingStateId;
            }
            if (!empty($data['cartShippingData']->shippingAddressId)) {
                $data['selectedShippingAddressId'] = $data['cartShippingData']->shippingAddressId;
            }
            if (!empty($data['cartShippingData']->billingAddressId)) {
                $data['selectedBillingAddressId'] = $data['cartShippingData']->billingAddressId;
            }
            if (isset($data['cartShippingData']->useSameAddressForBilling)) {
                $data['selectedSameAddressForBilling'] = $data['cartShippingData']->useSameAddressForBilling;
            }
        }

        if (authCheck()) {
            $data['shippingAddresses'] = $profileModel->getShippingAddresses(user()->id);
            $isCorrectShippingAddress = false;
            $isCorrectBillingAddress = false;
            if (!empty($data['shippingAddresses'])) {
                foreach ($data['shippingAddresses'] as $item) {
                    if ($data['selectedShippingAddressId'] == $item->id && $item->address_type == 'shipping') {
                        $isCorrectShippingAddress = true;
                    }
                    if ($data['selectedBillingAddressId'] == $item->id && $item->address_type == 'billing') {
                        $isCorrectBillingAddress = true;
                    }
                }
            }
            //reset selected addresses
            if ($isCorrectShippingAddress == false || $isCorrectBillingAddress == false) {
                $data['selectedShippingAddressId'] = 0;
                $data['selectedBillingAddressId'] = 0;
                $stateId = null;
            }
            if (empty($data['selectedShippingAddressId']) || empty($stateId)) {
                $address = $profileModel->getFirstShippingAddress(user()->id, 'shipping');
                if (!empty($address)) {
                    $data['selectedShippingAddressId'] = $address->id;
                    $stateId = $address->state_id;
                }
            }
            if (empty($data['selectedBillingAddressId'])) {
                $addressBilling = $profileModel->getFirstShippingAddress(user()->id, 'billing');
                if (!empty($addressBilling)) {
                    $data['selectedBillingAddressId'] = $addressBilling->id;
                }
            }
        }
        $data['stateId'] = null;
        $data['selectedShippingMethodIds'] = [];
        if (!empty(helperGetSession('mds_selected_shipping_methods'))) {
            $data['selectedShippingMethodIds'] = helperGetSession('mds_selected_shipping_methods');
        }
        // echo json_encode($data['shippingAddresses']);
        // die();
        //cart seller ids
        $data['cartSellerIds'] = null;
        if (!empty(helperGetSession('mds_array_cart_seller_ids'))) {
            $data['cartSellerIds'] = helperGetSession('mds_array_cart_seller_ids');
        }
        // print_r($data['shippingAddresses'][0]->district_id);die();
        $data['selectedDestination']=count($data['shippingAddresses'])>0?$data['shippingAddresses'][0]->district_id:0;
        
        $data['cart'] = $cart;
        $data['groupedSellers'] = $this->groupCartBySeller($cart);
        $data['states'] = json_decode('[{"id":1,"name":"NUSA TENGGARA BARAT (NTB)"},{"id":2,"name":"MALUKU"},{"id":3,"name":"KALIMANTAN SELATAN"},{"id":4,"name":"KALIMANTAN TENGAH"},{"id":5,"name":"JAWA BARAT"},{"id":6,"name":"BENGKULU"},{"id":7,"name":"KALIMANTAN TIMUR"},{"id":8,"name":"KEPULAUAN RIAU"},{"id":9,"name":"NANGGROE ACEH DARUSSALAM (NAD)"},{"id":10,"name":"DKI JAKARTA"},{"id":11,"name":"BANTEN"},{"id":12,"name":"JAWA TENGAH"},{"id":13,"name":"JAMBI"},{"id":14,"name":"PAPUA"},{"id":15,"name":"BALI"},{"id":16,"name":"SUMATERA UTARA"},{"id":17,"name":"GORONTALO"},{"id":18,"name":"JAWA TIMUR"},{"id":19,"name":"DI YOGYAKARTA"},{"id":20,"name":"SULAWESI TENGGARA"},{"id":21,"name":"NUSA TENGGARA TIMUR (NTT)"},{"id":22,"name":"SULAWESI UTARA"},{"id":23,"name":"SUMATERA BARAT"},{"id":24,"name":"BANGKA BELITUNG"},{"id":25,"name":"RIAU"},{"id":26,"name":"SUMATERA SELATAN"},{"id":27,"name":"SULAWESI TENGAH"},{"id":28,"name":"KALIMANTAN BARAT"},{"id":29,"name":"PAPUA BARAT"},{"id":30,"name":"LAMPUNG"},{"id":31,"name":"KALIMANTAN UTARA"},{"id":32,"name":"MALUKU UTARA"},{"id":33,"name":"SULAWESI SELATAN"},{"id":34,"name":"SULAWESI BARAT"}]');
        foreach ($data['groupedSellers'] as $seller) {
            // $ccc=$this->calculateShippingPerSeller($data['groupedSellers'],$data['selectedDestination'],$this->joinCourierCodes(json_encode($seller->couriers)));         
        }
        echo view('partials/_header', $data);

        if (authCheck()) {
            echo view('cart/shipping_information', $data);
        } else {
            echo view('cart/shipping_information_guest', $data);
        }
        echo view('partials/_footer');
    }
    function groupCartBySeller(object $cart): array
    {
        $result = [];

        if (!isset($cart->items) || !is_array($cart->items)) {
            return $result;
        }
        // print_r($cart->items[0]); die();
        foreach ($cart->items as $item) {
            $sellerId = $item->seller_id;
            if (!isset($result[$sellerId])) {
                $result[$sellerId] = (object) [
                    'seller_id' => $sellerId,
                    'seller_username' => $item->seller_username ?? null,
                    'seller_slug' => $item->seller_slug ?? null,
                    'items' => [],
                    'subtotal' => 0,
                    'origin' => $item->origin,
                    'shipping_dimensions'=>$item->shipping_dimensions,
                    'berat'=>$this->calculateChargeableWeightFromJson($item->shipping_dimensions),
                    'origin'=>$item->origin,
                    'total_weight' => 0,
                    'couriers' => [] // hanya di seller
                ];
            }

            // ===== ITEM =====
            $result[$sellerId]->items[] = $item;

            // ===== SUBTOTAL =====
            $result[$sellerId]->subtotal += (float) $item->total_price;

            // ===== WEIGHT =====
            $weight = 0;
            if (isset($item->berat)) {
                $weight = (float) $item->berat;
            } elseif (isset($item->chargeable_weight)) {
                $weight = (float) $item->chargeable_weight;
            }
            $result[$sellerId]->total_weight += $weight;

            // ===== COURIERS PER SELLER =====
            if (!empty($item->couriers)) {
                $couriersArr = json_decode($item->couriers, true);

                if (is_array($couriersArr)) {
                    foreach ($couriersArr as $c) {
                        if (!empty($c['code'])) {
                            $code = strtolower(trim($c['code']));

                            // pakai key code supaya unique
                            $result[$sellerId]->couriers[$code] = [
                                'code' => $code,
                                'name' => $c['name'] ?? strtoupper($code)
                            ];
                        }
                    }
                }
            }
        }

        // rapikan index couriers
        foreach ($result as &$seller) {
            $seller->couriers = array_values($seller->couriers);
        }

        return array_values($result);
    }
    function joinCourierCodes(string $json): string
    {
        $arr = json_decode($json, true) ?? [];
        return implode(':', array_column($arr, 'code'));
    }

    function calculateChargeableWeightFromJson($json, $divider = 6000)
    {
        $dimensi = json_decode($json, true);

        if (!$dimensi) {
            return 0;
        }

        $weight = floatval($dimensi['weight'] ?? 0);
        $length = floatval($dimensi['length'] ?? 0);
        $width  = floatval($dimensi['width'] ?? 0);
        $height = floatval($dimensi['height'] ?? 0);

        // Jika dimensi kosong → pakai berat aktual
        if ($length <= 0 || $width <= 0 || $height <= 0) {
            return $weight*1000;
        }

        // Berat volumetrik
        $volumetric = ($length * $width * $height) / $divider;

        // Ambil terbesar
        $chargeable = max($weight, $volumetric);

        return $chargeable*1000;
    }

    /**
     * Shipping Post
     */
    public function shippingPost()
    {
        $cartRaw = $this->cartModel->fetchRawCartData();
        if (empty($cartRaw)) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(generateUrl('cart', 'shipping'));
        }

        $this->cartModel->setShippingData($cartRaw);

        if (!$this->cartModel->setShippingCost($cartRaw)) {
            setErrorMessage(trans("msg_error"));
            return redirect()->to(generateUrl('cart', 'shipping'));
        }

        helperDeleteSession('mds_service_payment');

        return redirect()->to(generateUrl('cart', 'payment_method'));
    }

    /**
     * Payment Method
     */
    public function paymentMethod()
    {
        $data = setPageMeta(trans("shopping_cart"));
        $data['isTranslatable'] = true;

        $servicePayment = helperGetSession('mds_service_payment');

        $checkoutType = !empty($servicePayment) ? 'service' : 'product';

        $showLocationSelection = $this->paymentSettings->cart_location_selection;

        $payWithBalance = new \stdClass();
        $payWithBalance->total = 0;
        $payWithBalance->currency = 'USD';

        if ($checkoutType == 'product') {

            //validate cart
            $cart = $this->cartModel->getCart(true, false);
            if (empty($cart) || empty($cart->is_valid)) {
                return redirect()->to(generateUrl('cart'));
            }

            //check auth for digital products
            if (!authCheck() && $cart->has_digital_product == true) {
                setErrorMessage(trans("msg_digital_product_register_error"));
                return redirect()->to(generateUrl('cart'));
            }

            //show location selection if it is required
            if ($showLocationSelection == true && !empty($cart->location_country_id) && !empty($cart->location_state_id)) {
                $showLocationSelection = false;
            }

            //reset previous payment method
            $this->cartModel->setCartPaymentMethod($cart->id, '');

            $data['vendorCashOnDelivery'] = 0;
            foreach ($cart->items as $item) {
                $vendor = getUser($item->seller_id);
                if (!empty($vendor)) {
                    if ($vendor->cash_on_delivery == 1 && $vendor->commission_debt < $this->paymentSettings->cash_on_delivery_debt_limit) {
                        $data['vendorCashOnDelivery'] = 1;
                    }
                }
            }

            if (!empty($cart->totals) && !empty($cart->totals->total)) {
                $payWithBalance->total = $cart->totals->total;
                $payWithBalance->currency = $cart->currency_code;
            }

            $data['cart'] = $cart;

        } elseif ($checkoutType == 'service') {
            $data['servicePayment'] = $servicePayment;
            $servicePayment->globalTaxesArray = [];

            //do not show location selection for deposit
            if ($showLocationSelection == true && $servicePayment->serviceType === 'add_funds') {
                $showLocationSelection = false;
            }

            if (!empty($servicePayment->subtotal) && $servicePayment->serviceType != 'add_funds') {

                $location = (object)[
                    'country_id' => authCheck() ? user()->country_id : null,
                    'state_id' => authCheck() ? user()->state_id : null
                ];

                //show location selection if it is required
                if ($showLocationSelection == true && !empty($location->country_id) && !empty($location->state_id)) {
                    $showLocationSelection = false;
                }

                if (!empty($location->country_id) && !empty($location->state_id)) {
                    $servicePayment = $this->cartModel->setServicePaymentsTaxes($servicePayment, $location);
                }

                $payWithBalance->total = $servicePayment->grandTotal;
                $payWithBalance->currencyCode = $servicePayment->currencyCode;
            }
        }

        $data['payWithBalance'] = $payWithBalance;
        $data['checkoutType'] = $checkoutType;
        $data['showLocationSelection'] = $showLocationSelection;

        echo view('partials/_header', $data);
        echo view('cart/payment_method', $data);
        echo view('partials/_footer');
    }

    /**
     * Handle payment method selection, validate it, set on cart, and create checkout
     */
    public function paymentMethodPost()
    {
        //bot verification
        if (!authCheck()) {
            verifyTurnstile();
        }

        $servicePayment = helperGetSession('mds_service_payment');
        $cart = $this->cartModel->getCart();

        //set cart location if location form submit
        $submit = inputPost('submit');
        if ($submit === 'location') {
            $countryId = inputPost('country_id');
            $stateId = inputPost('state_id');
            $this->cartModel->setCartLocation($cart, $countryId, $stateId);

            return redirect()->to(generateUrl('cart', 'payment_method'));
        }

        if (!empty($servicePayment)) {

            $availableMethods = $this->getAvailablePaymentMethods(null, true);
            $selectedPaymentOption = inputPost('payment_option');

            //validate selected payment method
            if (!in_array($selectedPaymentOption, $availableMethods)) {
                setErrorMessage(trans("msg_error"));
                return redirect()->to(generateUrl('cart', 'payment_method'));
            }

            //set payment method
            $servicePayment->payment_method = $selectedPaymentOption;
            helperSetSession('mds_service_payment', $servicePayment);

        } else {

            //validate cart
            if (empty($cart) || !$cart->is_valid) {
                return redirect()->to(generateUrl('cart'));
            }

            $availableMethods = $this->getAvailablePaymentMethods($cart);
            $selectedPaymentOption = inputPost('payment_option');

            //validate selected payment method
            if (!in_array($selectedPaymentOption, $availableMethods)) {
                setErrorMessage(trans("msg_error"));
                return redirect()->to(generateUrl('cart', 'payment_method'));
            }

            //set payment method
            $this->cartModel->setCartPaymentMethod($cart->id, $selectedPaymentOption);

        }

        return redirect()->to(generateUrl('cart', 'payment'));
    }

    /**
     * Prepare and display the payment page.
     */
    public function payment()
    {
        $data = setPageMeta(trans("shopping_cart"));

        //check for guest checkout
        if (empty(authCheck()) && $this->generalSettings->guest_checkout != 1) {
            setErrorMessage(trans("msg_cart_login_error"));
            return redirect()->to(generateUrl('cart'));
        }

        $servicePayment = helperGetSession('mds_service_payment');

        if (!empty($servicePayment)) {

            if (!authCheck()) {
                return redirect()->to(generateUrl('cart'));
            }

            if ($this->paymentSettings->cart_location_selection && $servicePayment->serviceType != 'add_funds'
                && (empty(user()->country_id) || empty(user()->state_id))) {
                return redirect()->to(generateUrl('cart', 'payment_method'));
            }

            $data['cart'] = null;

        } else {
            $data['cart'] = $this->cartModel->getCart(true, true);

            $cartIsInvalid = empty($data['cart']) || empty($data['cart']->is_valid);
            if (empty($servicePayment) && $cartIsInvalid) {
                return redirect()->to(generateUrl('cart'));
            }

            if ($this->paymentSettings->cart_location_selection && (empty($data['cart']->location_country_id) || empty($data['cart']->location_state_id))) {
                return redirect()->to(generateUrl('cart', 'payment_method'));
            }
        }

        $checkout = $this->checkoutModel->createCheckout($data['cart'], $servicePayment);
        if (empty($checkout) || empty($checkout->checkout_token)) {
            return redirect()->to(generateUrl('cart', 'payment_method'));
        }

        if ($checkout->currency_code !== $this->selectedCurrency->code) {
            $checkout = $this->checkoutModel->createCheckout($data['cart'], $servicePayment, true);
        }

        $data['servicePayment'] = $servicePayment;
        $data['customer'] = getCartCustomerData($checkout);
        $data['totalAmount'] = numToDecimal($checkout->grand_total);
        $data['totalAmountInSubunits'] = (int)round($data['totalAmount'] * 100);
        $data['currencyCode'] = $checkout->currency_code;
        $data['checkout'] = $checkout;
        $data['checkoutItems'] = $this->checkoutModel->getCheckoutItems($checkout->id);
        $data['initPaymentGateway'] = false;

        if ($checkout->payment_method != 'bank_transfer' && $checkout->payment_method != 'cash_on_delivery' && $checkout->payment_method != 'wallet_balance') {
            $data['paymentGateway'] = getPaymentGateway($checkout->payment_method);
            if (empty($data['paymentGateway'])) {
                return redirect()->to(generateUrl('cart', 'payment_method'));
            }

            $gatewayKey = $data['paymentGateway']->name_key ?? null;
            $gatewayCurrenciesArray = [];
            if ($gatewayKey && isset(\Config\PaymentGateways::$currencies[$gatewayKey])) {
                $gatewayCurrenciesArray = \Config\PaymentGateways::$currencies[$gatewayKey];
            }

            $currencyCode = is_string($checkout->currency_code) ? $checkout->currency_code : '';

            if ($currencyCode === '' || !in_array($currencyCode, $gatewayCurrenciesArray, true)) {
                setErrorMessage(trans("currency_not_supported"));
            } else {
                $data['initPaymentGateway'] = true;
                $data = $this->initiatePayment($data['paymentGateway'], $data['checkout'], $data['checkoutItems'], $data);
            }
        }

        if ($checkout->payment_method == 'wallet_balance') {
            $data['initPaymentGateway'] = true;
        }

        echo view('partials/_header', $data);
        echo view('cart/payment', $data);
        echo view('partials/_footer');
    }

    /**
     * Initiates the payment process based on the selected payment method.
     */
    private function initiatePayment(object $paymentGateway, object $checkout, array $items, array $data): array
    {
        try {
            switch ($paymentGateway->name_key) {
                case 'stripe':
                    $stripe = new Stripe($paymentGateway, $this->baseVars->appName);
                    $data['stripeCheckoutUrl'] = $stripe->createCheckoutSessionFromOrder($checkout, $items);
                    if (empty($data['stripeCheckoutUrl'])) {
                        throw new \Exception(trans("payment_option_load_error"));
                    }
                    break;

                case 'razorpay':
                    $razorpay = new Razorpay($paymentGateway);
                    $orderData = [
                        'receipt' => $checkout->checkout_token,
                        'amount' => $data['totalAmountInSubunits'],
                        'currency' => $data['currencyCode'],
                        'notes' => ['checkout_token' => $checkout->checkout_token]
                    ];
                    $data['razorpayOrderId'] = $razorpay->createOrder($orderData);
                    if (empty($data['razorpayOrderId'])) {
                        throw new \Exception(trans("payment_option_load_error"));
                    }
                    break;

                case 'dlocalgo':
                    $dlocalGo = new DlocalGo($paymentGateway);
                    $data['dlocalRedirectUrl'] = $checkout->payment_url;
                    if (empty($data['dlocalRedirectUrl'])) {
                        $data['dlocalRedirectUrl'] = $dlocalGo->generatePaymentRedirectUrl($checkout);
                        if (!empty($data['dlocalRedirectUrl'])) {
                            $this->checkoutModel->setPaymentUrl($checkout->id, $data['dlocalRedirectUrl']);
                        }
                    }
                    break;

                case 'midtrans':
                    $midtrans = new Midtrans($paymentGateway);
                    $customerDetails = getCartCustomerData($checkout);
                    $grossAmount = intval($data['totalAmount']);
                    $data['midtransSnapToken'] = $midtrans->createSnapToken($checkout->checkout_token, $grossAmount, $customerDetails);
                    if (empty($data['midtransSnapToken'])) {
                        throw new \Exception(trans("payment_option_load_error"));
                    }
                    break;

                case 'paytabs':
                    $payTabs = new PayTabs($paymentGateway);
                    $data['paytabsCheckoutUrl'] = $checkout->payment_url;
                    if (empty($data['paytabsCheckoutUrl'])) {
                        $customer = getCartCustomerData($checkout);
                        $data['paytabsCheckoutUrl'] = $payTabs->createPayPage($checkout, $customer, $this->generalSettings->application_name);
                        if (!empty($data['paytabsCheckoutUrl'])) {
                            $this->checkoutModel->setPaymentUrl($checkout->id, $data['paytabsCheckoutUrl']);
                        }
                    }
                    break;

                case 'iyzico':
                    $iyzico = new Iyzico($paymentGateway);
                    $data['checkoutForm'] = $iyzico->createCheckoutForm($checkout);
                    break;

                case 'mercado_pago':
                    $mercadoPago = new MercadoPago($paymentGateway, $this->baseVars->appName);
                    $data['preferenceId'] = $mercadoPago->createPreferenceFromCheckout($checkout);
                    if (empty($data['preferenceId'])) {
                        throw new \Exception(trans("payment_option_load_error"));
                    }
                    break;
            }

        } catch (\Exception $e) {
            setErrorMessage($e->getMessage());
        }

        return $data;
    }

    /**
     * Return active payment methods based on cart and settings
     */
    private function getAvailablePaymentMethods($cart, $isServicePayment = false): array
    {
        $methods = [];

        foreach (getActivePaymentGateways() as $gateway) {
            $methods[] = esc($gateway->name_key);
        }

        if ($this->paymentSettings->bank_transfer_enabled == 1) {
            $methods[] = 'bank_transfer';
        }

        if ($this->paymentSettings->pay_with_wallet_balance == 1) {
            $methods[] = 'wallet_balance';
        }

        if ($this->paymentSettings->cash_on_delivery_enabled
            && !empty($cart)
            && $isServicePayment == false
            && empty($cart->has_digital_product)
            && $this->isAnyVendorEligibleForCOD($cart->items)) {
            $methods[] = 'cash_on_delivery';
        }

        return $methods;
    }

    /**
     * Check if any vendor allows cash on delivery
     */
    private function isAnyVendorEligibleForCOD(array $items): bool
    {
        foreach ($items as $item) {
            $vendor = getUser($item->seller_id);
            if (!empty($vendor)
                && $vendor->cash_on_delivery == 1
                && $vendor->commission_debt < $this->paymentSettings->cash_on_delivery_debt_limit) {
                return true;
            }
        }
        return false;
    }

    /**
     * AJAX Endpoint: Get shipping method by location
     *
     * @method POST
     */
    public function getShippingFeeByDistrict() //Fungsi Baru
    {
        $data = [
            'result' => 0,
            'htmlContent' => ''
        ];

        $cart = $this->cartModel->getCart();
        if (empty($cart) || !$cart->is_valid) {
            return jsonResponse($data);
        }
        
        $districtId = inputPost('districtId');

        if (!empty($districtId)) {
            $shippingModel = new ShippingModel();

            $vars = [
                'districtId' => $districtId,
                'delivery_fee'=>$shippingModel->getCourierFee($districtId)
                // 'shippingMethods' => $shippingModel->getSellerShippingMethodsArray($cart->items, $districtId, $cart->currency_code)
            ];

            $data['result'] = 1;
            $data['htmlContent'] = view('cart/_shipping_methods', $vars);
        }
        return jsonResponse($data);
    }

    public function getShippingMethodsByLocation() //Fungsi Lama
    {
        $data = [
            'result' => 0,
            'htmlContent' => ''
        ];

        $cart = $this->cartModel->getCart();
        if (empty($cart) || !$cart->is_valid) {
            return jsonResponse($data);
        }

        $stateId = inputPost('state_id');

        if (!empty($stateId)) {
            $shippingModel = new ShippingModel();

            $vars = [
                'stateId' => $stateId,
                'shippingMethods' => $shippingModel->getSellerShippingMethodsArray($cart->items, $stateId, $cart->currency_code)
            ];

            $data['result'] = 1;
            $data['htmlContent'] = view('cart/_shipping_methods', $vars);
        }
        return jsonResponse($data);
    }
}