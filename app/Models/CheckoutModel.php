<?php

namespace App\Models;

class CheckoutModel extends BaseModel
{
    protected $builderCheckouts;
    protected $builderCheckoutItems;

    // Status constants for clarity and to avoid magic strings
    const STATUS_PENDING = 'pending';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PAID = 'paid';

    public function __construct()
    {
        parent::__construct();
        $this->builderCheckouts = $this->db->table('checkouts');
        $this->builderCheckoutItems = $this->db->table('checkout_items');
    }

    /**
     * Creates or retrieves the active checkout session for the payment page.
     *
     * @param object|null $cart Cart object containing product items (if product checkout).
     * @param object|null $servicePayment Object containing service payment details (if service checkout).
     * @param bool $refreshCheckout Re-create checkout
     * @return object|null Returns the existing or newly created checkout object, or null on failure.
     */
    public function createCheckout($cart, $servicePayment, $refreshCheckout = false): ?object
    {
        $checkoutType = !empty($servicePayment) ? 'service' : 'product';

        $resetCheckout = !empty(helperGetSession('mds_cart_has_changed'));

        if ($refreshCheckout == true) {
            $resetCheckout = true;
        }

        $activeCheckout = $this->getActiveCheckout($checkoutType);

        if ($resetCheckout || empty($activeCheckout)) {
            $this->expirePreviousPendingCheckouts($checkoutType);

            if ($checkoutType == 'service') {
                $newCheckout = $this->createCheckoutForService($servicePayment);
            } else {
                $newCheckout = $this->createCheckoutFromCart($cart);
            }

            if (!empty($newCheckout)) {
                helperDeleteSession('mds_cart_has_changed');
                return $newCheckout;
            } else {
                return null;
            }
        }

        return $activeCheckout;
    }

    /**
     * Creates a checkout snapshot from a fully validated cart object.
     *
     * @param object $cart A fully populated cart object from CartModel->getValidatedCart(), which includes the payment method.
     * @return object|null A simple object containing the new checkout's ID and public token, or null on failure.
     */
    private function createCheckoutFromCart(object $cart): ?object
    {
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
            'status' => 'pending',
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

    /**
     * Creates a checkout record directly for a dynamic service
     *
     * @param object $servicePayment An object containing all necessary service payment details.
     * Expected properties: paymentType, paymentName, paymentAmount, currency, payment_method.
     * @return object|null A simple object containing the new checkout's ID and public token, or null on failure.
     */
    private function createCheckoutForService(object $servicePayment): ?object
    {
        //validate the incoming service data object
        if (empty($servicePayment->serviceType) || empty($servicePayment->paymentName) ||
            !isset($servicePayment->grandTotal) || !is_numeric($servicePayment->grandTotal) ||
            $servicePayment->grandTotal <= 0) {
            helperDeleteSession('mds_service_payment');
            return null;
        }

        $this->db->transStart();

        $checkoutToken = generateUuidV4();

        $currency = $this->selectedCurrency;

        $checkoutData = [
            'cart_id' => 0,
            'user_id' => user()->id,
            'session_id' => session_id(),
            'checkout_token' => $checkoutToken,
            'checkout_type' => 'service',
            'payment_method' => $servicePayment->payment_method ?? '',
            'subtotal' => numToDecimal($servicePayment->subtotal),
            'shipping_cost' => 0,
            'grand_total' => numToDecimal(convertCurrencyByExchangeRate($servicePayment->grandTotal, $this->selectedCurrency->exchange_rate)),
            'grand_total_base' => numToDecimal($servicePayment->grandTotal),
            'currency_code' => $this->selectedCurrency->code,
            'currency_code_base' => $servicePayment->currencyCode,
            'exchange_rate' => $this->selectedCurrency->exchange_rate,
            'service_type' => $servicePayment->serviceType,
            'service_data' => safeJsonEncode($servicePayment->data),
            'service_tax_data' => safeJsonEncode($servicePayment->globalTaxesArray),
            'status' => self::STATUS_PENDING,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($servicePayment->payment_method === 'bank_transfer') {
            $checkoutData['transaction_number'] = $this->generateTransactionNumber();
        } elseif ($servicePayment->payment_method === 'wallet_balance') {
            $checkoutData['transaction_number'] = $this->generateTransactionNumber('WLT');
        }

        $this->builderCheckouts->insert($checkoutData);
        $checkoutId = $this->db->insertID();

        if (!$checkoutId) {
            $this->db->transRollback();
            return null;
        }

        $this->db->transComplete();

        if ($this->db->transStatus()) {
            return $this->getCheckout($checkoutId);
        }

        return null;
    }

    /**
     * Creates a service payment data object and stores it in the session.
     *
     * @param string $serviceType The type of service (e.g., 'add_funds', 'membership').
     * @param string $paymentName A human-readable title for the service (e.g., "Add Funds to Wallet").
     * @param float $paymentAmount The amount for the service.
     * @param object $serviceData Service data
     * @return bool Always returns true to indicate the session has been set.
     */
    public function setServicePaymentSession(string $serviceType, string $paymentName, float $paymentAmount, ?object $data = null): bool
    {
        if ($serviceType === 'add_funds' && $this->defaultCurrency->code != $this->selectedCurrency->code) {
            $paymentAmount = convertToDefaultCurrency($paymentAmount, $this->selectedCurrency->code, false);
        }

        // Create a standard object to hold the service payment details.
        $serviceData = new \stdClass();
        $serviceData->serviceType = $serviceType;
        $serviceData->paymentName = $paymentName;
        $serviceData->subtotal = $paymentAmount;
        $serviceData->grandTotal = $paymentAmount;
        $serviceData->currencyCode = $this->defaultCurrency->code;
        $serviceData->data = !empty($data) ? $data : '';

        helperSetSession('mds_service_payment', $serviceData);

        return true;
    }

    /**
     * Retrieves a single checkout record by its public, secure token.
     *
     * @param string $token The 64-character secure token.
     * @return object|null
     */
    public function getCheckoutByToken(string $token): ?object
    {
        return $this->builderCheckouts->where('checkout_token', cleanStr($token))->get()->getRow();
    }

    /**
     * Retrieves the most recent 'pending' checkout for the current user or guest session
     *
     * @param string $checkoutType The type of checkout to search for (e.g., 'product', 'service').
     * @return object|null         The checkout object if a pending one is found, otherwise null.
     */
    public function getActiveCheckout(string $checkoutType): ?object
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

        $builder = $this->builderCheckouts->select('*');
        if ($userId) {
            $builder->where('user_id', $userId);
        } else {
            $builder->where('session_id', $cartSessionId);
        }

        $builder->where('status', self::STATUS_PENDING)->where('checkout_type', $checkoutType);

        return $builder->orderBy('id', 'DESC')->limit(1)->get()->getRow();
    }

    /**
     * Retrieves a single checkout record by its internal ID.
     *
     * @param int $id The ID of the checkout.
     * @return object|null A single checkout object without its items.
     */
    public function getCheckout(int $id): ?object
    {
        return $this->builderCheckouts->where('id', $id)->get()->getRow();
    }

    /**
     * Retrieves all items associated with a specific checkout.
     *
     * @param int $checkoutId The ID of the checkout.
     * @return array An array of item objects.
     */
    public function getCheckoutItems(int $checkoutId): array
    {
        return $this->builderCheckoutItems->where('checkout_id', $checkoutId)->get()->getResult();
    }

    /**
     * Updates the status of a specific checkout record.
     *
     * @param int $id The ID of the checkout.
     * @param string $status The new status (e.g., 'paid', 'failed').
     * @return bool
     */
    public function updateCheckoutStatus(int $id, string $status): bool
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_EXPIRED, self::STATUS_PAID])) {
            return false;
        }
        return $this->builderCheckouts->where('id', $id)->update(['status' => $status]);
    }

    /**
     * Sets the generated payment gateway URL for a specific checkout.
     *
     * @param int $id The ID of the checkout.
     * @param string $url The payment URL received from the gateway.
     * @return bool
     */
    public function setPaymentUrl(int $id, string $url): bool
    {
        return $this->builderCheckouts->where('id', $id)->update(['payment_url' => $url]);
    }

    /**
     * Expires ALL previous pending checkout attempts for the current user or guest session
     *
     * @param string $checkoutType The type of checkout to expire (e.g., 'product', 'service').
     * @return bool                True on success, false on failure.
     */
    public function expirePreviousPendingCheckouts(string $checkoutType): ?bool
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

        $builder = $this->builderCheckouts->select('id');
        if ($userId) {
            $builder->where('user_id', $userId);
        } else {
            $builder->where('session_id', $cartSessionId);
        }

        $checkoutsToExpire = $builder->where('status', self::STATUS_PENDING)->where('checkout_type', $checkoutType)
            ->get()->getResultArray();

        if (empty($checkoutsToExpire)) {
            return true;
        }

        $idsToExpire = array_column($checkoutsToExpire, 'id');

        $updateData = [
            'status' => self::STATUS_EXPIRED,
            'expires_at' => date('Y-m-d H:i:s')
        ];

        return $this->builderCheckouts->whereIn('id', $idsToExpire)->update($updateData);
    }

    /**
     * Delete checkouts that have expired
     */
    public function deleteExpiredCheckouts()
    {
        $expiredCheckoutIds = $this->builderCheckouts->where('status', self::STATUS_EXPIRED)->select('id')->get()->getResultArray();
        if (empty($expiredCheckoutIds)) {
            return;
        }

        $idsToDelete = array_column($expiredCheckoutIds, 'id');

        $this->db->transStart();

        $this->builderCheckoutItems->whereIn('checkout_id', $idsToDelete)->delete();
        $this->builderCheckouts->whereIn('id', $idsToDelete)->delete();

        $this->db->transComplete();
    }

    /**
     * Generates unique, concise, and human-readable token
     *
     * @param string $prefix The prefix for the transaction number (e.g., 'BTR').
     * @return string The unique transaction number.
     */
    private function generateTransactionNumber(string $prefix = 'BTR'): string
    {
        $micro = microtime(true);
        $microInt = (int)($micro * 1_000_000);

        $timePart = strtoupper(base_convert($microInt, 10, 36)); // e.g. KHI7ABG

        $randomPart = strtoupper(base_convert(bin2hex(random_bytes(5)), 16, 36)); // ~10 chars

        // Final format: BTR-KHI7ABG-2M9PFD7LW
        return $prefix . '-' . $timePart . '-' . $randomPart;
    }

    /**
     * Handles the final creation or update of an order after a successful payment.
     *
     * @param object $checkout The checkout object that has been marked as 'paid'.
     * @param object $transaction The transaction data received from the payment gateway.
     * @return array Response containing status and redirect URL or error.
     */
    public function handleProductCheckout(object $checkout, object $transaction)
    {
        if (empty($checkout) || empty($transaction)) {
            log_message('error', "handleProductCheckout: Checkout or transaction data is missing.");
            return $this->errorCheckoutResponse();
        }

        $orderModel = new OrderModel();

        $this->db->transStart();

        $sql = $this->db->table('orders')->where('checkout_token', $checkout->checkout_token)->getCompiledSelect() . ' FOR UPDATE';
        $order = $this->db->query($sql)->getRow();

        if (empty($order)) {
            $orderId = $orderModel->addOrder($checkout, $transaction);
            if (empty($orderId)) {
                $this->db->transRollback();
                return $this->errorCheckoutResponse();
            }

            $order = $orderModel->getOrder($orderId);
            if (empty($order)) {
                $this->db->transRollback();
                return $this->errorCheckoutResponse();
            }

            $orderModel->decreaseProductStockAfterSale($order->id);
        } else {
            if ($order->payment_status == 'pending_payment') {
                $orderAdminModel = new OrderAdminModel();
                $orderAdminModel->updateOrderPaymentReceived($order->id);
                $orderAdminModel->updatePaymentStatusIfAllReceived($order->id);
                $orderAdminModel->updateOrderStatusIfCompleted($order->id);
            }

            $order = $orderModel->getOrder($order->id);
        }

        //set checkout as paid
        $this->updateCheckoutStatus($checkout->id, self::STATUS_PAID);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'OrderCheckout failed for checkout token: ' . $checkout->checkout_token);
            return $this->errorCheckoutResponse(trans("msg_payment_database_error"));
        }

        //clear cart
        $cartModel = new CartModel();
        $cartModel->clearCart($checkout);

        $isGuest = ($order->buyer_id == 0);
        if ($isGuest) {
            helperSetSession('mds_show_order_completed_page', 1);
        }

        return [
            'status' => 1,
            'message' => $isGuest ? null : trans("msg_order_completed"),
            'redirectUrl' => generateUrl($isGuest ? 'order_completed' : 'order_details') . '/' . $order->order_number,
            'orderId' => $order->id,
            'orderNumber' => $order->order_number
        ];
    }

    /**
     * Handles the checkout process for membership-based services.
     *
     * @param object $checkout The checkout object containing payment and plan information.
     * @param object $transaction The transaction data returned from the payment gateway.
     * @return array Response containing status and redirect URL or error.
     */
    public function handleMembershipServiceCheckout(object $checkout, object $transaction)
    {
        if (empty($checkout) || empty($transaction)) {
            return $this->errorCheckoutResponse();
        }

        $serviceData = !empty($checkout->service_data) ? safeJsonDecode($checkout->service_data) : null;

        if (empty($serviceData->planId) || empty($serviceData->planRequestType)) {
            return $this->errorCheckoutResponse();
        }

        $plan = getMembershipPlan($serviceData->planId);
        if (empty($plan)) {
            return $this->errorCheckoutResponse();
        }

        $membershipModel = new MembershipModel();
        $transactionId = null;

        $this->db->transStart();

        $sql = $this->db->table('membership_transactions')->where('checkout_token', $checkout->checkout_token)->getCompiledSelect() . ' FOR UPDATE';
        $existingRecord = $this->db->query($sql)->getRow();

        if (empty($existingRecord)) {
            $transactionId = $membershipModel->addMembershipTransaction($checkout, $transaction, $plan);
        } else {
            $transactionId = $existingRecord->id;
            if ($existingRecord->payment_status == 'pending_payment') {
                $membershipModel->approveTransactionPayment($existingRecord->id);
            }
        }

        //set checkout as paid
        $this->updateCheckoutStatus($checkout->id, self::STATUS_PAID);

        $this->db->transComplete();

        if ($this->db->transStatus() === false || empty($transactionId)) {
            log_message('error', 'MembershipServiceCheckout failed for checkout token: ' . $checkout->checkout_token);
            return $this->errorCheckoutResponse(trans("msg_payment_database_error"));
        }

        //clear cart
        $cartModel = new CartModel();
        $cartModel->clearCart($checkout, true);

        return [
            'status' => 1,
            'redirectUrl' => generateUrl('checkout', 'service_payment_completed') . '?checkout=' . $checkout->checkout_token . '&tx_id=' . $transactionId
        ];
    }

    /**
     * Handles the checkout process for product promotion services.
     *
     * @param object $checkout The checkout object containing user and promotion info.
     * @param object $transaction The transaction object containing payment info.
     * @return array Result array with status and redirect URL, or error response.
     */
    public function handleProductPromotionServiceCheckout(object $checkout, object $transaction)
    {
        if (empty($checkout) || empty($transaction)) {
            return $this->errorCheckoutResponse();
        }

        $promoteModel = new PromoteModel();
        $transactionId = null;

        $this->db->transStart();

        $sql = $this->db->table('promoted_transactions')->where('checkout_token', $checkout->checkout_token)->getCompiledSelect() . ' FOR UPDATE';
        $existingRecord = $this->db->query($sql)->getRow();

        if (empty($existingRecord)) {
            $transactionId = $promoteModel->addPromoteTransaction($checkout, $transaction);
        } else {
            $transactionId = $existingRecord->id;

            if ($existingRecord->payment_status == 'pending_payment') {
                $productAdminModel = new ProductAdminModel();
                $productAdminModel->addToFeaturedProducts($existingRecord->product_id, $existingRecord->day_count, $existingRecord->id);
            }
        }

        //set checkout as paid
        $this->updateCheckoutStatus($checkout->id, self::STATUS_PAID);

        $this->db->transComplete();

        if ($this->db->transStatus() === false || empty($transactionId)) {
            log_message('error', 'ProductPromotionServiceCheckout failed for token: ' . $checkout->checkout_token);
            return $this->errorCheckoutResponse(trans("msg_payment_database_error"));
        }

        //clear cart
        $cartModel = new CartModel();
        $cartModel->clearCart($checkout, true);

        return [
            'status' => 1,
            'redirectUrl' => generateUrl('checkout', 'service_payment_completed') . '?checkout=' . $checkout->checkout_token . '&tx_id=' . $transactionId
        ];
    }

    /**
     * Handles the wallet deposit service checkout process.
     *
     * @param object $checkout The checkout object containing deposit details.
     * @param object $transaction The transaction object containing payment info.
     * @return array Result array with status and redirect URL, or error response.
     */
    public function handleAddFundsServiceCheckout(object $checkout, object $transaction)
    {
        if (empty($checkout) || empty($transaction)) {
            return $this->errorCheckoutResponse();
        }

        $earningsModel = new EarningsModel();
        $transactionId = null;

        $this->db->transStart();

        // Row lock to prevent concurrent inserts
        $sql = $this->db->table('wallet_deposits')->where('checkout_token', $checkout->checkout_token)->getCompiledSelect() . ' FOR UPDATE';
        $existingRecord = $this->db->query($sql)->getRow();

        if (empty($existingRecord)) {
            $transactionId = $earningsModel->addWalletDeposit($checkout, $transaction);
        } else {
            $transactionId = $existingRecord->id;

            if ($existingRecord->payment_status == 0) {
                $earningsModel->addFundsWallet($existingRecord->deposit_amount, $existingRecord->currency, $existingRecord->user_id);
                $earningsModel->setDepositPaymentReceived($existingRecord);
            }
        }

        //set checkout as paid
        $this->updateCheckoutStatus($checkout->id, self::STATUS_PAID);

        $this->db->transComplete();

        if ($this->db->transStatus() === false || empty($transactionId)) {
            log_message('error', 'AddFundsServiceCheckout failed for token: ' . $checkout->checkout_token);
            return $this->errorCheckoutResponse(trans("msg_payment_database_error"));
        }

        //clear cart
        $cartModel = new CartModel();
        $cartModel->clearCart($checkout, true);

        return [
            'status' => 1,
            'redirectUrl' => generateUrl('checkout', 'service_payment_completed') . '?checkout=' . $checkout->checkout_token . '&tx_id=' . $transactionId
        ];
    }

    /**
     * Creates the appropriate redirect URL after a checkout process is completed.
     *
     * @param object $checkout The checkout object containing details like type and token.
     * @return string The URL to redirect to.
     */
    public function createOrderRedirectUrl($checkout): string
    {
        if ($checkout->checkout_type == 'product') {
            $order = $this->db->table('orders')->where('checkout_token', $checkout->checkout_token)->get()->getRow();

            if ($order) {
                $isGuest = ($order->buyer_id == 0);
                if ($isGuest) {
                    helperSetSession('mds_show_order_completed_page', 1);
                }

                setSuccessMessage(trans("msg_order_completed"));
                $redirectRoute = $isGuest ? 'order_completed' : 'order_details';
                return generateUrl($redirectRoute) . '/' . $order->order_number;
            }
        } else {
            $serviceTableMap = [
                'membership' => 'membership_transactions',
                'promote' => 'promoted_transactions',
                'add_funds' => 'wallet_deposits',
            ];

            $tableName = $serviceTableMap[$checkout->service_type] ?? null;

            if ($tableName) {

                $transaction = $this->db->table($tableName)->where('checkout_token', $checkout->checkout_token)->get()->getRow();
                if ($transaction) {
                    $queryParams = http_build_query([
                        'checkout' => $checkout->checkout_token,
                        'tx_id' => $transaction->id,
                    ]);
                    return generateUrl('checkout', 'service_payment_completed') . '?' . $queryParams;
                }
            }
        }

        return langBaseUrl();
    }

    /**
     * Updates the relevant transaction record after a successful webhook.
     *
     * @param object $checkout The checkout object from your system.
     * @param object $transaction The transaction object created from the payment gateway's data.
     */
    public function updatePaymentTransactionAfterWebHook($checkout, $transaction)
    {
        if ($checkout->checkout_type == 'product') {
            $order = $this->db->table('orders')->where('checkout_token', $checkout->checkout_token)->get()->getRow();

            if (!empty($order)) {
                $row = $this->db->table('transactions')->where('checkout_token', $checkout->checkout_token)->get()->getRow();

                if (empty($row)) {
                    $orderModel = new OrderModel();
                    $orderModel->addPaymentTransaction($checkout, $transaction, $order->id);
                }
            }
        } elseif ($checkout->checkout_type == 'service') {
            $serviceTables = ['membership_transactions', 'promoted_transactions', 'wallet_deposits'];

            foreach ($serviceTables as $table) {
                $builder = $this->db->table($table);
                $row = $builder->where('checkout_token', $checkout->checkout_token)->get()->getRow();

                if (!empty($row)) {
                    $builder->where('checkout_token', $checkout->checkout_token)->update(['payment_id' => $transaction->payment_id]);
                    break;
                }
            }
        }
    }

    /**
     * Generates a standard error response array for failed payments or operations.
     *
     * @param string|null $message Error message to display.
     * @param string|null $baseUrl Optional base URL for redirect. Defaults to langBaseUrl().
     * @return array Standardized error response.
     */
    public function errorCheckoutResponse(?string $message = null, ?string $baseUrl = null): array
    {
        $message = $message ?: trans("msg_error");
        $baseUrl = $baseUrl ?: langBaseUrl();

        return [
            'status' => 0,
            'message' => $message,
            'redirectUrl' => $baseUrl . getRoute('cart', true) . getRoute('payment')
        ];
    }
}