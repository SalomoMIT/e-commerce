<?php namespace App\Models;

class CartModel extends BaseModel
{
    protected $builderCarts;
    protected $builderCartItems;

    public function __construct()
    {
        parent::__construct();

        $this->builderCarts = $this->db->table('carts');
        $this->builderCartItems = $this->db->table('cart_items');
        $this->builderCheckouts = $this->db->table('checkouts');
        $this->builderPaymentMethods = $this->db->table('payment_method');
    }

    //finds an existing active cart or creates a new one
    public function createCart()
    {
        $userId = 0;
        $cartSessionId = '';
        $locationCountryId = 0;
        $locationStateId = 0;

        if (authCheck()) {
            $userId = user()->id;
            $locationCountryId = user()->country_id;
            $locationStateId = user()->state_id;

            $existingCart = $this->builderCarts->where('user_id', $userId)->get()->getRow();
            if ($existingCart) {
                return $existingCart->id;
            }
        } else {
            $cartSessionId = helperGetSession('cartSessionId');

            if (!empty($cartSessionId)) {
                $existingCart = $this->builderCarts->where('session_id', $cartSessionId)->get()->getRow();
                if ($existingCart) {
                    return $existingCart->id;
                }
            }
            $cartSessionId = generateToken();
        }

        $exchangeRate = 1;

        $currencyCode = $this->selectedCurrency->code ?? 'USD';
        $currency = getCurrencyByCode($currencyCode);
        if (!empty($currency)) {
            $exchangeRate = $currency->exchange_rate;
        }

        $data = [
            'user_id' => $userId,
            'session_id' => $cartSessionId,
            'currency_code' => $currencyCode,
            'currency_code_base' => $this->defaultCurrency->code ?? 'USD',
            'exchange_rate' => $exchangeRate,
            'shipping_data' => '',
            'shipping_cost' => 0,
            'shipping_cost_data' => '',
            'coupon_code' => '',
            'payment_method' => '',
            'location_country_id' => $locationCountryId,
            'location_state_id' => $locationStateId,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->builderCarts->insert($data)) {
            $id = $this->db->insertID();
            if (!empty($cartSessionId) && $userId === 0) {
                helperSetSession('cartSessionId', $cartSessionId);
            }
            return $id;
        } else {
            log_message('error', 'Failed to create a new cart. DB Error: ' . safeJsonEncode($this->db->error()));
        }

        return null;
    }
    public function getActivePaymentMethods()
    {
        return $this->builderPaymentMethods->where('active', 1)->get()->getResult();
    }
    public function saveCheckoutFromCart(object $cart): ?object
    {
        echo json_encode($cart);die();
        if (empty($cart) || empty($cart->is_valid) || empty($cart->payment_method)) {
            return null;
        }

        $this->db->transStart();

        $paymentMethod = $cart->payment_method;
        $checkoutToken = generateUuidV4();

        // Convert numbers to string before assigning safely
        try {
            foreach ($cart->totals as $key => $value) {
                if (is_numeric($value)) {
                    $cart->totals->$key = (string)$value;
                }
            }
        } catch (\Throwable $e) {
        }

        // Prepare checkout data from the validated cart object.
        $checkoutData = [
            'cart_id' => $cart->id,
            'user_id' => $cart->user_id,
            'session_id' => $cart->session_id,
            'checkout_token' => $checkoutToken,
            'checkout_type' => 'product',
            'payment_method' => $paymentMethod,
            'subtotal' => numToDecimal($cart->totals->subtotal),
            'shipping_cost' => numToDecimal($cart->totals->shipping_cost ?? 0),
            'grand_total' => numToDecimal($cart->totals->total),
            'grand_total_base' => numToDecimal(convertToDefaultCurrency($cart->totals->total, $cart->currency_code)),
            'currency_code' => $cart->currency_code,
            'currency_code_base' => $cart->currency_code_base,
            'exchange_rate' => $cart->exchange_rate ?? 1.0,
            'cart_totals_data' => safeJsonEncode($cart->totals),
            'shipping_data' => sanitizeJsonString($cart->shipping_data),
            'shipping_cost_data' => sanitizeJsonString($cart->shipping_cost_data),
            'coupon_code' => $cart->coupon_code,
            'has_physical_product' => $cart->has_physical_product,
            'has_digital_product' => $cart->has_digital_product,
            'status' => self::STATUS_PENDING,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($paymentMethod === 'bank_transfer') {
            $checkoutData['transaction_number'] = $this->generateTransactionNumber();
        } elseif ($paymentMethod === 'wallet_balance') {
            $checkoutData['transaction_number'] = $this->generateTransactionNumber('WLT');
        }

        $this->builderCheckouts->insert($checkoutData);
        $checkoutId = $this->db->insertID();

        if (!$checkoutId) {
            $this->db->transRollback();
            return null;
        }

        // Prepare checkout items
        $orderModel = new OrderModel();
        $checkoutItems = [];
        foreach ($cart->items as $item) {

            $productCommissionRate = $orderModel->getProductCommissionRate($item->product_id);

            $checkoutItems[] = [
                'checkout_id' => $checkoutId,
                'product_id' => $item->product_id,
                'seller_id' => $item->seller_id,
                'product_type' => $item->product_type,
                'listing_type' => $item->listing_type,
                'product_title' => $item->product_title,
                'product_sku' => $item->product_sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'product_vat' => $item->product_vat,
                'product_vat_rate' => $item->product_vat_rate,
                'product_image_id' => $item->product_image_id,
                'product_image_data' => $item->product_image_data,
                'quote_request_id' => $item->quote_request_id,
                'product_options_snapshot' => $item->product_options_snapshot,
                'product_options_summary' => $item->product_options_summary,
                'product_commission_rate' => !empty($productCommissionRate) ? $productCommissionRate : 0,
                'extra_options' => $item->extra_options,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($checkoutItems)) {
            $this->builderCheckoutItems->insertBatch($checkoutItems);
        }

        $this->db->transComplete();

        if ($this->db->transStatus()) {
            return $this->getCheckout($checkoutId);
        }
        return null;
    }
    //get raw cart from database
    public function fetchRawCartData()
    {
        $userId = 0;
        $cartSessionId = '';

        if (authCheck()) {
            $userId = user()->id;
        } else {
            $cartSessionId = helperGetSession('cartSessionId');
        }

        if (!$userId && !$cartSessionId) {
            return null;
        }

        $builder = $this->db->table("carts c")
            ->select("c.*, ci.*, c.id AS cart_id,p.shipping_dimensions,u.couriers,p.district_id as origin, ci.id AS item_id, (SELECT CONCAT(username, '|||', slug) FROM users WHERE users.id = ci.seller_id) AS seller_info")
            ->join("cart_items ci", "c.id = ci.cart_id", "left")
            ->join("users u", "ci.seller_id = u.id", "left")
            ->join("products p", "p.id = ci.product_id", "left");

        if ($userId) {
            $builder->where('c.user_id', $userId);
        } else {
            $builder->where('c.session_id', $cartSessionId);
        }

        $results = $builder->get()->getResultObject();

        if (empty($results)) {
            return null;
        }

        $cartId = clrNum($results[0]->cart_id ?? '');
        $currencyCodeBase = $results[0]->currency_code_base ?? null;
        $currencyCode = $results[0]->currency_code ?? null;

        if (empty($cartId)) {
            return null;
        }

        //check base currency
        if (empty($currencyCodeBase) || $currencyCodeBase != $this->defaultCurrency->code) {
            $this->builderCarts->where('id', clrNum($cartId))->delete();
            return null;
        }

        //set selected currency
        $selectedCurrencyCode = $this->selectedCurrency->code;
        $currency = getCurrencyByCode($this->selectedCurrency->code);
        if (empty($currency)) {
            $selectedCurrencyCode = $this->defaultCurrency->code;
        }

        if ($selectedCurrencyCode != $currencyCode) {
            helperSetSession('mds_cart_has_changed', 1);
            $this->builderCarts->where('id', $cartId)->update(['currency_code' => $currency->code]);
        }

        $shippingData = safeJsonDecode($results[0]->shipping_data ?? '');

        //set exchange rate and location
        $this->builderCarts->where('id', $cartId)->update([
            'exchange_rate' => $currency->exchange_rate
        ]);

        $cart = new \stdClass();
        $cart->id = $results[0]->cart_id;
        $cart->user_id = $results[0]->user_id;
        $cart->session_id = $results[0]->session_id;
        $cart->currency_code = $currencyCode;
        $cart->currency_code_base = $results[0]->currency_code_base;
        $cart->currency_exchange_rate = $currency->exchange_rate;
        $cart->shipping_data = $results[0]->shipping_data;
        $cart->shipping_cost = $results[0]->shipping_cost;
        $cart->shipping_cost_data = $results[0]->shipping_cost_data;
        $cart->location_country_id = $results[0]->location_country_id;
        $cart->location_state_id = $results[0]->location_state_id;
        $cart->coupon_code = $results[0]->coupon_code;
        $cart->payment_method = $results[0]->payment_method;
        $cart->num_items = 0;
        $cart->has_physical_product = 0;
        $cart->has_digital_product = 0;
        $cart->is_valid = 0;
        $cart->items = [];
        $cart->totals = [];

        //keys to exclude from the final item object to keep it clean
        $keysToExclude = ['cart_id', 'user_id', 'session_id'];

        foreach ($results as $row) {
            if ($row->item_id !== null) {

                $cart->num_items += 1;
                if ($row->product_type == 'physical') {
                    $cart->has_physical_product = 1;
                }
                if ($row->product_type == 'digital') {
                    $cart->has_digital_product = 1;
                }

                $item = new \stdClass();
                foreach ($row as $key => $value) {
                    if (!in_array($key, $keysToExclude)) {
                        $item->{$key} = $value;
                    }
                }
                $item->id = $row->item_id;
                $cart->items[] = $item;
            }
        }

        $cart->is_valid = $this->isCartValid($cart);

        return $cart;
    }

    //get cart
    public function getCart(bool $includeTaxes = false, bool $includeTransactionFee = false)
    {
        $cart = $this->fetchRawCartData();
        if (empty($cart) || empty($cart->items)) {            
            if (!empty($cart)) {
                $this->calculateCartTotalModified($cart);
            }
            return $cart;
        }        
        
        $cart->num_items = 0;
        foreach ($cart->items as $key => $cartItem) {
            $product = getActiveProduct($cartItem->product_id);
            if (empty($product)) {
                $this->removeCartItem($cartItem->id);
                unset($cart->items[$key]);
                continue;
            }

            $updatedItem = $this->syncCartItem($cart, $cartItem, $product, $includeTaxes);
            // print_r($updatedItem);
            if ($updatedItem === null) {
                unset($cart->items[$key]);
            } else {
                $cart->items[$key] = $updatedItem;
            }

            $cart->num_items += 1;
        }

        $cart->items = array_values($cart->items);

        $cart = $this->calculateCartTotalModified($cart, $includeTaxes, $includeTransactionFee);

        $cart->is_valid = $this->isCartValid($cart);

        return $cart;
    }

    //add to cart
    public function addToCart($product, $quantity, $variantId = null, $extraOptions = [])
    {
        if (empty($product)) {
            return null;
        }

        $cart = $this->fetchRawCartData();
        $cartId = $cart->id ?? $this->createCart();

        if (empty($cartId)) {
            return null;
        }

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($product->product_type == 'digital' || $product->listing_type == 'license_key') {
            $quantity = 1;
        }

        $productOptionsModel = new ProductOptionsModel();
        $variantHash = null;

        if (!empty($variantId)) {
            $variant = $productOptionsModel->getVariantById($variantId);
            if (empty($variant) || empty($variant->variant_hash)) {
                return null;
            }
            $variantHash = $variant->variant_hash;
        }

        if (empty($extraOptions) || !is_array($extraOptions)) {
            $extraOptions = [];
        }

        $extraOptionsHash = $productOptionsModel->getCanonicalExtraOptionsJson($extraOptions);
        $itemHash = md5($product->id . $variantHash . $extraOptionsHash);

        $existingItem = $this->builderCartItems->where('cart_id', $cartId)->where('item_hash', $itemHash)->get()->getRow();
        if (!empty($existingItem)) {
            if ($product->product_type != 'digital' && $product->listing_type != 'license_key') {
                $newQuantity = $existingItem->quantity + $quantity;
                $this->updateItemQuantity($existingItem->id, $newQuantity);
            }
            return $existingItem->id;
        }

        $cartItem = [
            'cart_id' => $cartId,
            'item_hash' => $itemHash,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'purchase_type' => 'product',
            'quote_request_id' => 0,
            'variant_hash' => $variantHash,
            'extra_options' => !empty($extraOptions) ? safeJsonEncode($extraOptions) : null,
            'extra_options_hash' => $extraOptionsHash ?: null,
            'product_options_snapshot' => !empty($variantId) ? $productOptionsModel->getVariantSnapshot($variantId, $extraOptions) : ''
        ];

        if ($this->builderCartItems->insert($cartItem)) {
            return $this->db->insertID();
        }

        return null;
    }

    //add to cart quote
    public function addToCartQuote($quoteRequestId)
    {
        $biddingModel = new BiddingModel();
        $quoteRequest = $biddingModel->getQuoteRequest($quoteRequestId);
        if (empty($quoteRequest)) {
            return false;
        }

        $product = getActiveProduct($quoteRequest->product_id);
        if (empty($product)) {
            return false;
        }

        $cart = $this->fetchRawCartData();
        $cartId = $cart->id ?? $this->createCart();
        if (empty($cartId)) {
            return null;
        }

        $existingItem = $this->builderCartItems->where('cart_id', $cartId)->where('quote_request_id', $quoteRequest->id)->get()->getRow();
        if (!empty($existingItem)) {
            return false;
        }

        //apply currency conversion to the unit price
        $totalPrice = $quoteRequest->price_offered;
        $unitPrice = number_format($totalPrice / $quoteRequest->product_quantity, 4);
        $unitPriceBase = $unitPrice;
        if ($this->paymentSettings->currency_converter == 1 && !empty($cart->currency_code) && !empty($cart->currency_exchange_rate)) {
            $unitPrice = convertCurrencyByExchangeRate($unitPrice, $cart->currency_exchange_rate);
            $totalPrice = convertCurrencyByExchangeRate($totalPrice, $cart->currency_exchange_rate);
        }

        $cartItem = [
            'cart_id' => $cartId,
            'item_hash' => '',
            'product_id' => $product->id,
            'quantity' => $quoteRequest->product_quantity,
            'purchase_type' => 'bidding',
            'quote_request_id' => $quoteRequest->id,
            'product_type' => $product->product_type,
            'listing_type' => $product->listing_type,
            'product_title' => $product->title,
            'unit_price_base' => numToDecimal($unitPriceBase),
            'unit_price' => numToDecimal($unitPrice),
            'total_price' => numToDecimal($totalPrice),
            'product_vat' => 0,
            'product_vat_rate' => 0,
            'seller_id' => $product->user_id,
            'is_stock_available' => 1,
            'product_sku' => $quoteRequest->product_sku,
            'variant_hash' => $quoteRequest->variant_hash,
            'extra_options' => $quoteRequest->extra_options,
            'extra_options_hash' => $quoteRequest->extra_options_hash,
            'product_options_snapshot' => $quoteRequest->product_options_snapshot,
            'product_options_summary' => $quoteRequest->product_options_summary,
            'product_image_id' => $quoteRequest->product_image_id,
            'product_image_data' => $quoteRequest->product_image_data
        ];

        if ($this->builderCartItems->insert($cartItem)) {
            return $this->db->insertID();
        }

        return false;
    }

    //sync cart item
    private function syncCartItem($cart, $cartItem, $product, $includeTaxes)
    {
        if (empty($product) || empty($cartItem)) {
            return null;
        }

        $originalCartItemJson = safeJsonEncode($cartItem);

        $fileModel = new FileModel();
        $productOptionsModel = new ProductOptionsModel();
        $stock = $product->stock;
        $variant = null;
        $variantId = null;

        //validate variant if set before
        if (!empty($cartItem->variant_hash)) {
            $variant = $productOptionsModel->getVariantByHash($cartItem->variant_hash);
            if (empty($variant) || $variant->is_active != 1) {
                $this->removeCartItem($cartItem->id);
                return null;
            }
            $variantId = $variant->id;
            $stock = $variant->quantity ?? $stock;
        }

        $isStockAvailable = 1;
        if ($product->product_type !== 'digital' && $product->listing_type !== 'license_key') {
            if ($stock <= 0) {
                $isStockAvailable = 0;
            }
            if ($cartItem->quantity > $stock) {
                $isStockAvailable = 0;
            }
        }

        //apply currency conversion to the unit price
        if ($cartItem->purchase_type == 'bidding') {
            $totalPrice = $cartItem->total_price;
            $unitPrice = $cartItem->unit_price;
            $unitPriceBase = $unitPrice;
            if ($this->paymentSettings->currency_converter == 1 && !empty($cart->currency_code) && !empty($cart->currency_exchange_rate)) {
                $unitPrice = convertCurrencyByExchangeRate($unitPrice, $cart->currency_exchange_rate);
                $totalPrice = convertCurrencyByExchangeRate($totalPrice, $cart->currency_exchange_rate);
            }
        } else {
            $unitPrice = $product->price_discounted ?? $product->price;
            if (!empty($variant)) {

                if (!empty($variant->price_discounted) && $variant->price_discounted > 0) {
                    $variantPrice = $variant->price_discounted;
                } else {
                    $variantPrice = $variant->price;
                }
                if (!empty($variantPrice) && $variantPrice > 0) {
                    $unitPrice = $variantPrice;
                }
            }

            $unitPriceBase = $unitPrice;
            if ($this->paymentSettings->currency_converter == 1 && !empty($cart->currency_code) && !empty($cart->currency_exchange_rate)) {
                $unitPrice = convertCurrencyByExchangeRate($unitPrice, $cart->currency_exchange_rate);
            }
            $totalPrice = $unitPrice * $cartItem->quantity;
        }

        //calculate tax based on the definitive unit price
        $vatAmount = 0;
        $vatRate = 0;
        if ($includeTaxes) {
            $location = (object)[
                'country_id' => $cart->location_country_id,
                'state_id' => $cart->location_state_id
            ];
            $vat = $this->calculateProductVat($location, $unitPrice, $product, $cartItem->quantity);
            $vatAmount = $vat['vat'] ?? 0;
            $vatRate = $vat['vatRate'] ?? 0;
        }

        $cartItem->product_type = $product->product_type;
        $cartItem->listing_type = $product->listing_type;
        $cartItem->product_title = $product->title;
        $cartItem->product_url = generateProductUrl($product);
        $cartItem->unit_price_base = numToDecimal($unitPriceBase);
        $cartItem->unit_price = numToDecimal($unitPrice);
        $cartItem->total_price = numToDecimal($totalPrice);
        $cartItem->product_vat = $vatAmount;
        $cartItem->product_vat_rate = $vatRate;
        $cartItem->seller_id = $product->user_id;
        $cartItem->chargeable_weight = !empty($variant) && isset($variant->weight) && $variant->weight > 0 ? $variant->weight : $product->chargeable_weight;
        $cartItem->is_stock_available = $isStockAvailable;
        //variant data
        $extraOptions = !empty($cartItem->extra_options) ? safeJsonDecode($cartItem->extra_options, true) : [];
        $optionsSnapshot = $productOptionsModel->getVariantSnapshot($variantId, $extraOptions);
        $cartItem->product_options_snapshot = $optionsSnapshot;
        $cartItem->product_options_summary = formatCartOptionsSummary($cartItem->product_options_snapshot, $this->activeLang->short_form, true);
        $cartItem->product_sku = $variant->sku ?? $product->sku;
        //set product image
        $cartItem->product_image_id = '';
        $cartItem->product_image_data = '';

        $optionsArray = safeJsonDecode($optionsSnapshot ?? '');
        if (!empty($optionsArray)) {
            $cartItem->product_sku = $optionsArray->sku ?? '';
            $imageId = $optionsArray->image_id ?? null;
            if (!empty($imageId)) {
                $image = $fileModel->getImage($imageId);
                if (!empty($image)) {
                    $cartItem->product_image_id = $image->id;
                    $data = [
                        'path' => $image->image_small,
                        'storage' => $image->storage
                    ];
                    $cartItem->product_image_data = safeJsonEncode($data);
                }
            }
        }
        if (empty($cartItem->product_image_id) || empty($cartItem->product_image_data)) {
            $image = $fileModel->getProductMainImage($product->id);
            if (!empty($image)) {
                $cartItem->product_image_id = $image->id;
                $data = [
                    'path' => $image->image_small,
                    'storage' => $image->storage
                ];
                $cartItem->product_image_data = safeJsonEncode($data);
            }
        }

        if ($originalCartItemJson !== safeJsonEncode($cartItem)) {
            $dbFields = $this->db->getFieldNames('cart_items');
            $updateArray = [];
            foreach ($dbFields as $field) {
                if (property_exists($cartItem, $field)) {
                    $updateArray[$field] = $cartItem->{$field};
                }
            }

            unset($updateArray['id'], $updateArray['cart_id'], $updateArray['created_at']);

            $this->builderCartItems->where('id', $cartItem->id)->update($updateArray);
        }

        //set seller info
        $cartItem->seller_username = '';
        $cartItem->seller_slug = '';
        $sellerInfo = explode('|||', $cartItem->seller_info ?? '');
        if (count($sellerInfo) >= 2) {
            $cartItem->seller_username = $sellerInfo[0];
            $cartItem->seller_slug = $sellerInfo[1];
        }

        return $cartItem;
    }

    // merge guest cart into a user's cart upon login
    public function mergeGuestCartToUser(int $userId): bool
    {
        $cartSessionId = helperGetSession('cartSessionId');
        if (empty($cartSessionId)) {
            return true;
        }

        // Find the guest cart in the database.
        $guestCart = $this->builderCarts->where('session_id', $cartSessionId)->get()->getRow();
        if (empty($guestCart)) {
            helperDeleteSession('cartSessionId');
            return true;
        }

        // Find the user's own cart.
        $userCart = $this->builderCarts->where('user_id', $userId)->get()->getRow();
        if (empty($userCart)) {
            // If user has no cart, assign the guest cart to them.
            $this->builderCarts->where('id', $guestCart->id)->update(['user_id' => $userId, 'session_id' => '']);
            helperDeleteSession('cartSessionId');
            return true;
        }

        $guestItems = $this->builderCartItems->where('cart_id', $guestCart->id)->get()->getResult();
        if (empty($guestItems)) {
            $this->builderCarts->where('id', $guestCart->id)->delete();
            helperDeleteSession('cartSessionId');
            return true;
        }

        $this->db->transStart();

        $productModel = new ProductModel();

        foreach ($guestItems as $guestItem) {
            $existingItem = $this->builderCartItems->where('cart_id', $userCart->id)->where('item_hash', $guestItem->item_hash)->get()->getRow();

            if (!empty($existingItem)) {
                $product = $productModel->getActiveProduct($guestItem->product_id);

                // Merge quantities only for non-digital products.
                if ($product && $product->product_type != 'digital' && $product->listing_type != 'license_key') {
                    $newQuantity = $existingItem->quantity + $guestItem->quantity;
                    $this->builderCartItems->where('id', $existingItem->id)->update(['quantity' => $newQuantity]);
                }

                // The original guest item is now merged, so it can be deleted.
                $this->builderCartItems->where('id', $guestItem->id)->delete();
            } else {
                // If item does not exist, move it to the user's cart by updating its cart_id.
                $this->builderCartItems->where('id', $guestItem->id)->update(['cart_id' => $userCart->id]);
            }
        }

        $this->builderCarts->where('id', $guestCart->id)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'Failed to merge guest cart for user ID: ' . $userId);
            return false;
        }

        helperDeleteSession('cartSessionId');
        return true;
    }

    //update cart item quantity
    public function updateItemQuantity($cartItemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        return $this->builderCartItems->where('id', clrNum($cartItemId))->update(['quantity' => $quantity]);
    }

    //remove cart item
    public function removeCartItem($cartItemId)
    {
        $cartItemId = clrNum($cartItemId);

        $cartItem = $this->builderCartItems->where('id', $cartItemId)->get()->getRow();
        if (!$cartItem) {
            return false;
        }

        $cartId = $cartItem->cart_id;

        if ($this->builderCartItems->delete(['id' => $cartItemId])) {

            $remainingItems = $this->builderCartItems->where('cart_id', $cartId)->countAllResults();
            if ($remainingItems === 0) {
                $this->builderCarts->delete(['id' => $cartId]);
            }

        }

        return true;
    }

    //get cart item
    public function getCartItem($id)
    {
        return $this->builderCartItems->where('id', clrNum($id))->get()->getRow();
    }
    public function calculateCartTotalModified($cart)
    {
        $cartTotal = new \stdClass();
        $cartTotal->subtotal = 0;
        $cartTotal->total = 0;

        if (!empty($cart->items)) {
            foreach ($cart->items as $item) {
                $cartTotal->subtotal += $item->total_price;
            }
        }

        $cartTotal->total = $cartTotal->subtotal;

        $cart->totals = $cartTotal;

        return $cart;
    }
    //calculate cart total
    public function calculateCartTotal($cart, $includeTaxes = false, $includeTransactionFee = false)
    {
        $cartTotal = new \stdClass();
        $cartTotal->subtotal = 0;
        $cartTotal->vat = 0;
        $cartTotal->shipping_cost = 0;
        $cartTotal->total_before_shipping = 0;
        $cartTotal->total = 0;
        $cartTotal->transaction_fee = 0;
        $cartTotal->transaction_fee_rate = 0;
        if (!empty($cart->items)) {
            foreach ($cart->items as $item) {
                if ($item->purchase_type == 'bidding') {
                    $cartTotal->subtotal += $item->total_price;
                } else {
                    $cartTotal->subtotal += $item->total_price;
                    $cartTotal->vat += $item->product_vat;
                }
            }
        }
        //set shipping cost
        $cartTotal->shipping_cost = $cart->shipping_cost;
        $cartTotal->total_before_shipping = $cartTotal->subtotal + $cartTotal->vat;
        $cartTotal->total = $cartTotal->subtotal + $cartTotal->vat + $cartTotal->shipping_cost;

        //calculate affiliate discount
        $affiliateDiscount = $this->calculateAffiliateDiscount($cart->items);
        $cartTotal->affiliate_id = $affiliateDiscount['id'];
        $cartTotal->affiliate_referrer_id = $affiliateDiscount['referrerId'];
        $cartTotal->affiliate_seller_id = $affiliateDiscount['sellerId'];
        $cartTotal->affiliate_product_id = $affiliateDiscount['productId'];
        $cartTotal->affiliate_commission_rate = $affiliateDiscount['commissionRate'];
        $cartTotal->affiliate_commission = $affiliateDiscount['commission'];
        $cartTotal->affiliate_discount_rate = $affiliateDiscount['discountRate'];
        $cartTotal->affiliate_discount = $affiliateDiscount['discount'];
        $cartTotal->total_before_shipping = $cartTotal->total_before_shipping - $cartTotal->affiliate_discount;
        $cartTotal->total = $cartTotal->total - $cartTotal->affiliate_discount;

        //discount coupon
        $arrayDiscount = $this->calculateCouponDiscount($cart);
        $cartTotal->coupon_discount_products = $arrayDiscount['product_ids'];
        if (!empty($cartTotal->coupon_discount_products)) {
            $cartTotal->coupon_discount_products = trim($cartTotal->coupon_discount_products, ',');
        }
        $cartTotal->coupon_discount_rate = $arrayDiscount['discount_rate'];
        $cartTotal->coupon_discount = $arrayDiscount['total_discount'];
        $cartTotal->coupon_seller_id = $arrayDiscount['seller_id'];
        $cartTotal->total_before_shipping = $cartTotal->total_before_shipping - $cartTotal->coupon_discount;
        $cartTotal->total = $cartTotal->total - $cartTotal->coupon_discount;

        //set global taxes
        if ($includeTaxes && !empty($cart->location_country_id) && !empty($cart->location_state_id)) {
            $location = (object)[
                'country_id' => $cart->location_country_id,
                'state_id' => $cart->location_state_id
            ];
            $cartTotal->global_taxes_array = $this->getGlobalTaxArray($location, $cartTotal->subtotal, 'product_sales');
            if (!empty($cartTotal->global_taxes_array) && countItems($cartTotal->global_taxes_array) > 0) {
                foreach ($cartTotal->global_taxes_array as $tax) {
                    if (!empty($tax['taxTotal'])) {
                        $cartTotal->total = numToDecimal($cartTotal->total + $tax['taxTotal']);
                        $cartTotal->total_before_shipping = numToDecimal($cartTotal->total_before_shipping + $tax['taxTotal']);
                    }
                }
            }
        }
        //set transaction fee
        if ($includeTransactionFee) {
            $cartTotal = $this->setTransactionFee($cartTotal, $cart->payment_method);
        }

        $cart->totals = $cartTotal;

        return $cart;
    }

    //get global tax array
    public function getGlobalTaxArray($location, $total, $taxType)
    {
        $taxes = $this->db->table('taxes')->get()->getResult();
        $taxArray = array();
        if (!empty($taxes)) {
            foreach ($taxes as $tax) {
                $taxTypeStatus = false;
                if ($taxType == 'product_sales' && $tax->product_sales == 1) {
                    $taxTypeStatus = true;
                } elseif ($taxType == 'service_payments' && $tax->service_payments == 1) {
                    $taxTypeStatus = true;
                }
                if (!empty($tax) && $tax->status == 1 && $taxTypeStatus) {
                    $applyTax = false;
                    if ($tax->is_all_countries == 1) {
                        $applyTax = true;
                    } else {
                        $taxCountryIds = !empty($tax->country_ids) ? unserializeData($tax->country_ids) : array();
                        $taxStateIds = !empty($tax->state_ids) ? unserializeData($tax->state_ids) : array();
                        if (!empty($taxCountryIds) && countItems($taxCountryIds) && in_array($location->country_id, $taxCountryIds)) {
                            $applyTax = true;
                        }
                        if (!empty($taxStateIds) && countItems($taxStateIds) && in_array($location->state_id, $taxStateIds)) {
                            $applyTax = true;
                        }
                    }
                    if ($applyTax == true && $tax->tax_rate > 0) {
                        $taxTotal = ($total * $tax->tax_rate) / 100;
                        if (!empty($taxTotal)) {
                            $taxItem = [
                                'taxNameArray' => unserializeData($tax->name_data),
                                'taxRate' => $tax->tax_rate,
                                'taxTotal' => numToDecimal($taxTotal)
                            ];
                            array_push($taxArray, $taxItem);
                        }
                    }
                }
            }
        }
        return $taxArray;
    }

    //add service payments taxes
    public function setServicePaymentsTaxes($servicePayment, $location)
    {
        $servicePayment->globalTaxesArray = $this->getGlobalTaxArray($location, $servicePayment->subtotal, 'service_payments');
        if (!empty($servicePayment->globalTaxesArray) && countItems($servicePayment->globalTaxesArray) > 0) {
            $taxTotal = 0;
            foreach ($servicePayment->globalTaxesArray as $tax) {
                $taxTotal += $tax['taxTotal'];
            }
            $servicePayment->grandTotal = $servicePayment->subtotal + $taxTotal;
        }

        return $servicePayment;
    }

    //convert service taxes currency
    public function convertServiceTaxesCurrency($globalTaxesArray, $currencyCode)
    {
        if ($this->defaultCurrency->code == $currencyCode) {
            return $globalTaxesArray;
        }
        if (!empty($globalTaxesArray)) {
            $currency = getCurrencyByCode($currencyCode);
            if (!empty($currency)) {
                for ($i = 0; $i < countItems($globalTaxesArray); $i++) {
                    $total = $globalTaxesArray[$i]['taxTotal'];
                    $total = convertCurrencyByExchangeRate($total, $currency->exchange_rate);
                    $globalTaxesArray[$i]['taxTotal'] = $total;
                }
            }
        }
        return $globalTaxesArray;
    }

    //calculate product vat
    public function calculateProductVat($location, $price, $product, $quantity)
    {
        if ($this->paymentSettings->vat_status != 1) {
            return ['vat' => 0, 'vatRate' => 0];
        }
        $vat = 0;
        $vatRate = 0;
        if (!empty($price)) {
            if (!empty($product->vat_rate)) {
                $vatRate = $product->vat_rate;
            } else {
                $user = getUser($product->user_id);
                if ($user->is_fixed_vat == 1) {
                    $vatRate = $user->fixed_vat_rate;
                } else {
                    $stateVat = 0;
                    $countryVat = 0;
                    if (!empty($user->vat_rates_data_state)) {
                        $vatArray = unserializeData($user->vat_rates_data_state);
                        if (!empty($vatArray) && !empty($location->state_id) && !empty($vatArray[$location->state_id])) {
                            $stateVat = $vatArray[$location->state_id];
                        }
                    }
                    if (!empty($user->vat_rates_data)) {
                        $vatArray = unserializeData($user->vat_rates_data);
                        if (!empty($vatArray) && !empty($location->country_id) && !empty($vatArray[$location->country_id])) {
                            $countryVat = $vatArray[$location->country_id];
                        }
                    }
                    if (!empty($stateVat)) {
                        $vatRate = $stateVat;
                    } else {
                        $vatRate = $countryVat;
                    }
                }
            }
            if (!empty($vatRate)) {
                $vat = (($price * $vatRate) / 100) * $quantity;
                if (filter_var($vat, FILTER_VALIDATE_INT) === false) {
                    $vat = number_format($vat, 2, '.', '');
                }
            }
        }
        return ['vat' => $vat, 'vatRate' => $vatRate];
    }

    //calculate affiliate discount
    public function calculateAffiliateDiscount($cartItems)
    {
        $data = [
            'id' => '',
            'referrerId' => '',
            'sellerId' => '',
            'productId' => '',
            'commissionRate' => 0,
            'commission' => 0,
            'discountRate' => 0,
            'discount' => 0
        ];
        $affiliateSettings = getSettingsUnserialized('affiliate');
        if ($affiliateSettings->status == 1) {
            $affId = helperGetCookie(AFFILIATE_COOKIE_NAME);
            if (!empty($affId)) {
                $affiliate = $this->db->table('affiliate_links')->where('id', clrNum($affId))->get()->getRow();
                if (!empty($affiliate)) {
                    $user = getUser($affiliate->referrer_id);
                    if (!empty($user) && $user->is_affiliate == 1) {
                        if (!empty($cartItems)) {
                            foreach ($cartItems as $cartItem) {
                                if ($affiliate->product_id == $cartItem->product_id) {
                                    $product = getProduct($cartItem->product_id);
                                    if (!empty($product)) {
                                        $data['id'] = $affiliate->id;
                                        $data['referrerId'] = $affiliate->referrer_id;
                                        $data['sellerId'] = $affiliate->seller_id;
                                        $data['productId'] = $affiliate->product_id;
                                        $data['commissionRate'] = getAffiliateRates($product)->commissionRate;
                                        if (!empty($data['commissionRate']) && $data['commissionRate'] > 0 && $data['commissionRate'] < 100) {
                                            $commission = ($cartItem->total_price * $data['commissionRate']) / 100;
                                            $data['commission'] = numToDecimal($commission);
                                        }
                                        $data['discountRate'] = getAffiliateRates($product)->discountRate;
                                        if (!empty($data['discountRate']) && $data['discountRate'] > 0 && $data['discountRate'] < 100) {
                                            $discount = ($cartItem->total_price * $data['discountRate']) / 100;
                                            $data['discount'] = numToDecimal($discount);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    //validate cart
    public function isCartValid($cart)
    {
        if (empty($cart) || empty($cart->totals) || empty($cart->items)) {
            return false;
        }

        if ($cart->totals->total <= 0) {
            return false;
        }

        foreach ($cart->items as $cartItem) {
            if ($cartItem->is_stock_available != 1) {
                return false;
            }
        }

        return true;
    }

    //set cart payment method
    public function setCartPaymentMethod($cartId, $paymentOption)
    {
        if (empty($cartId)) {
            return false;
        }

        if (empty($paymentOption)) {
            $paymentOption = '';
        }

        return $this->builderCarts->where('id', clrNum($cartId))->update(['payment_method' => $paymentOption]);
    }

    //apply coupon
    public function applyCoupon($couponCode)
    {
        $couponCode = removeSpecialCharacters($couponCode);

        $cart = $this->fetchRawCartData();
        if (empty($cart)) {
            return false;
        }

        if ($this->verifyCouponCode($cart, $couponCode, true)) {
            return $this->builderCarts->where('id', $cart->id)->update(['coupon_code' => $couponCode]);
        }
        return false;
    }

    //calculate coupon discount
    public function calculateCouponDiscount($cart)
    {
        $couponCode = $cart->coupon_code;
        $totalDiscount = 0;
        $discountRate = 0;
        $sellerId = 0;
        $productIds = '';
        if (!empty($couponCode)) {
            $coupon = $this->verifyCouponCode($cart, $couponCode, false);
            if (!empty($coupon)) {
                $sellerId = $coupon->seller_id;
                if (!empty($coupon) && !empty($coupon->product_ids)) {
                    $discountRate = $coupon->discount_rate;
                    if (is_array($coupon->product_ids) && countItems($coupon->product_ids) > 0) {
                        if (!empty($cart->items)) {
                            foreach ($cart->items as $cartItem) {
                                if (!empty($cartItem->product_id) && in_array($cartItem->product_id, $coupon->product_ids)) {
                                    $productIds .= $cartItem->product_id . ',';
                                    $discount = ($cartItem->total_price * $coupon->discount_rate) / 100;
                                    $discount = numToDecimal($discount);
                                    $totalDiscount += $discount;
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['discount_rate' => $discountRate, 'total_discount' => $totalDiscount, 'seller_id' => $sellerId, 'product_ids' => $productIds];
    }

    //verify coupon code
    public function verifyCouponCode($cart, $couponCode, $setMessage)
    {
        if (empty($cart)) {
            return false;
        }

        $cartProductIds = [];
        if (!empty($cart->items)) {
            foreach ($cart->items as $item) {
                $cartProductIds[] = $item->product_id;
            }
        }

        $couponModel = new CouponModel();
        $coupon = $couponModel->getCouponByCodeCart($couponCode, $cartProductIds);

        $error = null;

        if (empty($coupon)) {
            $error = trans("msg_invalid_coupon");
        } elseif (date('Y-m-d H:i:s') > $coupon->expiry_date) {
            $error = trans("msg_invalid_coupon");
        } elseif ($coupon->coupon_count <= $coupon->used_coupon_count) {
            $error = trans("msg_coupon_limit");
        } elseif ($coupon->usage_type == 'single') {
            if (!authCheck()) {
                $error = trans("msg_coupon_auth");
            } elseif ($couponModel->isCouponUsed(user()->id, $couponCode) > 0) {
                $error = trans("msg_coupon_used");
            }
        }

        if ($error === null) {
            $sellerCartTotal = 0;
            if (!empty($cart->items)) {
                foreach ($cart->items as $item) {
                    if ($item->seller_id == $coupon->seller_id) {
                        $sellerCartTotal += $item->total_price;
                    }
                }
            }

            $minAmount = numToDecimal($coupon->minimum_order_amount);
            if (!empty($cart->currency_exchange_rate)) {
                $minAmount = convertCurrencyByExchangeRate($minAmount, $cart->currency_exchange_rate);
            }

            if ($sellerCartTotal < $minAmount) {
                $error = trans("msg_coupon_cart_total") . " " . priceCurrencyFormat($minAmount, $cart->currency_code);
            }
        }

        if ($error !== null) {
            $this->removeCoupon($cart->id);
            if ($setMessage) {
                $this->session->setFlashdata('error_coupon_code', $error);
            }
            return false;
        }

        return $coupon;
    }

    //remove coupon
    public function removeCoupon($cartId)
    {
        if (empty($cartId)) {
            return false;
        }

        $this->builderCarts->where('id', clrNum($cartId))->update(['coupon_code' => '']);
    }

    //get cart seller ids
    public function getCartSellerIds($cartItems, $onlyPhysicalProducts = true)
    {
        $sellerIds = [];

        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                if ($onlyPhysicalProducts && $item->product_type !== 'physical') {
                    continue;
                }
                if (!in_array($item->seller_id, $sellerIds)) {
                    $sellerIds[] = $item->seller_id;
                }
            }
        }

        return $sellerIds;
    }

    //get cart seller products weight sum
    public function getCartSellerChargeableWeightSum($cartItems, $sellerId)
    {
        $totalWeight = 0;

        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                if ($item->seller_id == $sellerId && $item->product_type === 'physical' && isset($item->quantity)) {
                    $totalWeight += ($item->chargeable_weight * $item->quantity);
                }
            }
        }

        return $totalWeight;
    }

    //get cart total amount by seller
    public function getCartTotalAmountBySeller($cartItems, $sellerId, $onlyPhysicalProducts = true)
    {
        $total = 0;

        foreach ($cartItems as $item) {
            if ($item->seller_id == $sellerId) {
                if ($onlyPhysicalProducts && $item->product_type !== 'physical') {
                    continue;
                }
                $total += $item->total_price;
            }
        }

        return $total;
    }

    //get the total quantity of items for a specific seller in the cart
    public function getCartSellerItemCount($cartItems, $sellerId)
    {
        $totalItems = 0;

        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                if ($item->seller_id == $sellerId && isset($item->quantity)) {
                    $totalItems += $item->quantity;
                }
            }
        }

        return $totalItems;
    }
    
    //set shipping data
    public function setShippingData($cartRaw)
    {
        if (empty($cartRaw)) {
            return false;
        }
        // $isSame = !empty(inputPost('use_same_address_for_billing')) ? 1 : 0;
        $data = new \stdClass();
        // $data->useSameAddressForBilling = $isSame;
        $data->isGuest = 0;
        if (authCheck()) {
            $profileModel = new ProfileModel();
            // $sAddressId = inputPost('shipping_address_id');
            // $bAddressId = inputPost('billing_address_id');
            // $sAddress = $profileModel->getShippingAddressById($sAddressId, user()->id);
            // if ($isSame) {
            //     $bAddressId = 0;
            //     $bAddress = $sAddress;
            // } else {
            //     $bAddress = $profileModel->getShippingAddressById($bAddressId, user()->id);
            //     if (empty($bAddress)) {
            //         $bAddress = $sAddress;
            //         $data->useSameAddressForBilling = 1;
            //     }
            // }
            if (!empty($sAddress)) {
                $country = getCountry($sAddress->country_id);
                $state = getState($sAddress->state_id);
                $data->shippingAddressId = $sAddressId;
                $data->shippingStateId = $sAddress->state_id;
                $data->sTitle = $sAddress->title;
                $data->sFirstName = $sAddress->first_name;
                $data->sLastName = $sAddress->last_name;
                $data->sEmail = $sAddress->email;
                $data->sPhoneNumber = $sAddress->phone_number;
                $data->sAddress = $sAddress->address;
                $data->sCountryId = !empty($country) ? $country->id : 0;
                $data->sCountry = !empty($country) ? $country->name : '';
                $data->sStateId = !empty($state) ? $state->id : 0;
                $data->sState = !empty($state) ? $state->name : '';
                $data->sCity = $sAddress->city;
                $data->sZipCode = $sAddress->zip_code;
                $data->province_id = inputPost('province_id');
                $data->province_name = inputPost('province_name');
                $data->city_id = inputPost('city_id');
                $data->city_name = inputPost('city_name');
                $data->district_id = inputPost('district_id');
                $data->district_name = inputPost('district_name');
            }
            // if (!empty($bAddress)) {
            //     $country = getCountry($bAddress->country_id);
            //     $state = getState($bAddress->state_id);
            //     $data->billingAddressId = $bAddressId;
            //     $data->bTitle = $bAddress->title;
            //     $data->bFirstName = $bAddress->first_name;
            //     $data->bLastName = $bAddress->last_name;
            //     $data->bEmail = $bAddress->email;
            //     $data->bPhoneNumber = $bAddress->phone_number;
            //     $data->bAddress = $bAddress->address;
            //     $data->bCountryId = !empty($country) ? $country->id : 0;
            //     $data->bCountry = !empty($country) ? $country->name : '';
            //     $data->bStateId = !empty($state) ? $state->id : 0;
            //     $data->bState = !empty($state) ? $state->name : '';
            //     $data->bCity = $bAddress->city;
            //     $data->bZipCode = $bAddress->zip_code;
            // }
        } else {
            // $sCountry = getCountry(inputPost('shipping_country_id'));
            // $sState = getState(inputPost('shipping_state_id'));
            // $bCountry = $sCountry;
            // $bState = $sState;
            // if (!$isSame) {
            //     $bCountry = getCountry(inputPost('billing_country_id'));
            //     $bState = getState(inputPost('billing_state_id'));
            // }
            $data->isGuest = 1;
            $data->shippingAddressId = 0;
            $data->shippingStateId = !empty($sState) ? $sState->id : 0;
            $data->sTitle = 'Main';
            $data->sFirstName = inputPost('shipping_first_name');
            $data->sLastName = inputPost('shipping_last_name');
            $data->sEmail = inputPost('shipping_email');
            $data->sPhoneNumber = inputPost('shipping_phone_number');
            $data->sAddress = inputPost('shipping_address');
            // $data->sCountryId = !empty($sCountry) ? $sCountry->id : '';
            // $data->sCountry = !empty($sCountry) ? $sCountry->name : '';
            // $data->sStateId = !empty($sState) ? $sState->id : '';
            // $data->sState = !empty($sState) ? $sState->name : '';
            $data->sCity = inputPost('shipping_city');
            $data->sZipCode = inputPost('shipping_zip_code');
            $data->province_id = inputPost('province_id');
            $data->province_name = inputPost('province_name');
            $data->city_id = inputPost('city_id');
            $data->city_name = inputPost('city_name');
            $data->district_id = inputPost('district_id');
            $data->district_name = inputPost('district_name');
            // $data->bTitle = 'Main';
            // $data->bFirstName = $isSame ? $data->sFirstName : inputPost('billing_first_name');
            // $data->bLastName = $isSame ? $data->sLastName : inputPost('billing_last_name');
            // $data->bEmail = $isSame ? $data->sEmail : inputPost('billing_email');
            // $data->bPhoneNumber = $isSame ? $data->sPhoneNumber : inputPost('billing_phone_number');
            // $data->bAddress = $isSame ? $data->sAddress : inputPost('billing_address');
            // $data->bCountryId = !empty($bCountry) ? $bCountry->id : '';
            // $data->bCountry = !empty($bCountry) ? $bCountry->name : '';
            // $data->bStateId = !empty($bState) ? $bState->id : '';
            // $data->bState = !empty($bState) ? $bState->name : '';
            // $data->bCity = $isSame ? $data->sCity : inputPost('billing_city');
            // $data->bZipCode = $isSame ? $data->sZipCode : inputPost('billing_zip_code');
        }

        $this->builderCarts->where('id', $cartRaw->id)->update(['shipping_data' => safeJsonEncode($data)]);
    }

    //set shipping cost
    public function setShippingCost($cartRaw)
    {
        if(empty($cartRaw)){
            return false;
        }

        $totalCost = 0;

        $cart = $this->getCart();
        if (empty($cart)) {
            return false;
        }

        $countryId = null;
        $stateId = null;
        if (!empty($cart->shipping_data)) {
            $shippingData = safeJsonDecode($cart->shipping_data);

            if (!empty($shippingData->sCountryId)) {
                $countryId = $shippingData->sCountryId;
            }

            if (!empty($shippingData->shippingStateId)) {
                $stateId = $shippingData->shippingStateId;
            }
        }

        $shippingModel = new ShippingModel();
        $shippingMethodsArray = $shippingModel->getSellerShippingMethodsArray($cart->items, $stateId, $cart->currency_code);

        $mapShippingMethods = [];
        foreach ($shippingMethodsArray as $shippingMethod) {
            $methods = $shippingMethod->methods;
            if (!empty($methods)) {
                foreach ($methods as $method) {
                    $mapShippingMethods[$method->id] = $method;
                }
            }
        }

        $shippingMethods = [];

        $cartSellerIds = $this->getCartSellerIds($cart->items);
        if (!empty($cartSellerIds)) {
            foreach ($cartSellerIds as $sellerId) {
                $methodId = inputPost('shipping_method_' . $sellerId);

                if (empty($methodId) || !array_key_exists($methodId, $mapShippingMethods)) {
                    return false;
                }

                if (empty($mapShippingMethods[$methodId])) {
                    return false;
                }

                $cost = $mapShippingMethods[$methodId]->cost;

                if($cartRaw->currency_code != $cartRaw->currency_code_base){
                    $cost=  convertCurrencyByExchangeRate($cost, $cartRaw->currency_exchange_rate);
                }

                $totalCost += $cost;

                $shippingMethods[] = (object)[
                    'shop_id' => $sellerId,
                    'shipping_method' => $mapShippingMethods[$methodId]->method,
                    'shipping_cost' => $cost
                ];
            }
        }

        $shippingCostDataJson = safeJsonEncode($shippingMethods);
        $this->builderCarts->where('id', $cart->id)->update([
            'shipping_cost' => numToDecimal($totalCost),
            'shipping_cost_data' => $shippingCostDataJson,
            'location_country_id' => (int)$countryId,
            'location_state_id' => (int)$stateId
        ]);

        return true;
    }

    //set transaction fee
    public function setTransactionFee($cartTotal, $paymentMethod)
    {
        if (!empty($cartTotal) && !empty($paymentMethod)) {
            $transactionFee = 0;
            $transactionFeeRate = 0;
            if ($paymentMethod != 'bank_transfer' && $paymentMethod != 'cash_on_delivery') {
                $paymentGateway = getPaymentGateway($paymentMethod);
                if (!empty($paymentGateway) && !empty($paymentGateway->transaction_fee) && $paymentGateway->transaction_fee > 0) {
                    $transactionFee = numToDecimal(($cartTotal->total * $paymentGateway->transaction_fee) / 100);
                    $transactionFeeRate = $paymentGateway->transaction_fee;
                }
            }

            if ($transactionFee > 0) {
                $cartTotal->transaction_fee = $transactionFee;
                $cartTotal->transaction_fee_rate = $transactionFeeRate;
                $cartTotal->total = numToDecimal($cartTotal->total + $transactionFee);
                $cartTotal->total_before_shipping = numToDecimal($cartTotal->total_before_shipping + $transactionFee);
            }
        }

        return $cartTotal;
    }

    //get cart customer location
    public function getCartCustomerLocation($shippingData)
    {
        $countryId = 0;
        $stateId = 0;


        if (!empty($shippingData)) {
            if (!empty($shippingData->sCountryId)) {
                $countryId = $shippingData->sCountryId;
            }
            if (!empty($shippingData->sStateId)) {
                $stateId = $shippingData->sStateId;
            }
        }

        if (empty($countryId) || empty($stateId)) {
            if (authCheck()) {
                $countryId = user()->country_id;
                $stateId = user()->state_id;
            }
        }

        return ['country_id' => $countryId, 'state_id' => $stateId];
    }

    //set cart location
    public function setCartLocation($cart, $countryId, $stateId)
    {
        if (authCheck()) {
            $this->db->table('users')->where('id', user()->id)->update([
                'country_id' => clrNum($countryId),
                'state_id' => clrNum($stateId),
            ]);
        }

        if (!empty($cart)) {
            $this->db->table('carts')->where('id', $cart->id)->update([
                'location_country_id' => $countryId,
                'location_state_id' => $stateId
            ]);
        }
    }

    //clear cart
    public function clearCart($checkout, $onlyService = false)
    {
        if (!$onlyService) {
            // Check if $checkout is valid
            if (empty($checkout) || (!isset($checkout->user_id) && !isset($checkout->session_id))) {
                return;
            }

            $row = null;
            if (!empty($checkout->user_id)) {
                // Get the single cart record for the user
                $row = $this->builderCarts->where('user_id', $checkout->user_id)->get()->getRow();
            } else {
                // Get the single cart record for the session
                $row = $this->builderCarts->where('session_id', $checkout->session_id)->get()->getRow();
            }

            if (!empty($row)) {
                $this->builderCartItems->where('cart_id', $row->id)->delete();
                $this->builderCarts->where('id', $row->id)->delete();
            }
        }

        // Handle the session part regardless of cart deletion
        if (helperGetSession('mds_service_payment')) {
            helperdeleteSession('mds_service_payment');
        }
    }
}
