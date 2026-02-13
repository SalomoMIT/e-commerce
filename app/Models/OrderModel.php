<?php namespace App\Models;

/*
 * STATUS
 * processing  : 0
 * completed   : 1
 * cancelled   : 2
 */

class OrderModel extends BaseModel
{
    protected $builder;
    protected $builderOrderItems;
    protected $builderRefundRequests;
    protected $builderDigitalSales;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('orders');
        $this->builderOrderItems = $this->db->table('order_items');
        $this->builderRefundRequests = $this->db->table('refund_requests');
        $this->builderDigitalSales = $this->db->table('digital_sales');
    }

    //add order
    public function addOrder(?object $checkout, ?object $transaction): ?int
    {
        if (empty($checkout) || empty($checkout->grand_total)) {
            return false;
        }

        $this->db->transStart();

        $checkoutModel = new CheckoutModel();
        $checkoutItems = $checkoutModel->getCheckoutItems($checkout->id);

        $isPendingPayment = false;
        $paymentStatus = 'payment_received';
        $orderStatus = 'payment_received';

        // If the payment method is bank transfer
        if ($checkout->payment_method === 'bank_transfer') {
            $isPendingPayment = true;
            $paymentStatus = 'pending_payment';
            $orderStatus = 'pending_payment';
        } // If the payment method is cash on delivery
        elseif ($checkout->payment_method === 'cash_on_delivery') {
            $isPendingPayment = true;
            $paymentStatus = 'pending_payment';
            $orderStatus = 'order_processing';
        }

        // If the transaction is not successful
        if ($transaction->status != 1) {
            $isPendingPayment = true;
            $paymentStatus = 'pending_payment';
            $orderStatus = 'pending_payment';
        }

        $data = [
            'order_number' => uniqid(),
            'buyer_id' => $checkout->user_id ?: 0,
            'buyer_type' => empty($checkout->user_id) ? 'guest' : 'registered',
            'price_subtotal' => numToDecimal($checkout->subtotal),
            'price_shipping' => numToDecimal($checkout->shipping_cost),
            'price_total' => numToDecimal($checkout->grand_total),
            'price_currency' => $checkout->currency_code,
            'status' => (!$isPendingPayment && $checkout->has_physical_product != 1) ? 1 : 0,
            'payment_method' => $checkout->payment_method,
            'payment_status' => $paymentStatus,
            'bank_transaction_number' => $checkout->transaction_number ?? '',
            'checkout_token' => $checkout->checkout_token,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        //set shipping data
        $shippingData = !empty($checkout->shipping_data) ? safeJsonDecode($checkout->shipping_data) : null;
        if (!empty($shippingData)) {
            $data['shipping'] = serialize($shippingData);
        }

        $cartTotals = !empty($checkout->cart_totals_data) ? safeJsonDecode($checkout->cart_totals_data, true) : [];

        if (!empty($cartTotals)) {
            //set VAT and transaction fees
            $data['price_vat'] = $cartTotals['vat'] ?? 0;
            $data['transaction_fee_rate'] = $cartTotals['transaction_fee_rate'] ?? 0;
            $data['transaction_fee'] = $cartTotals['transaction_fee'] ?? 0;

            //set global taxes
            if (!empty($cartTotals['global_taxes_array'])) {
                $data['global_taxes_data'] = serialize($cartTotals['global_taxes_array']);
            }

            //set coupon data
            $data['coupon_code'] = $checkout->coupon_code ?? '';
            $data['coupon_products'] = $cartTotals['coupon_discount_products'] ?? '';
            $data['coupon_discount_rate'] = $cartTotals['coupon_discount_rate'] ?? 0;
            $data['coupon_discount'] = isset($cartTotals['coupon_discount']) ? numToDecimal($cartTotals['coupon_discount']) : 0;
            $data['coupon_seller_id'] = $cartTotals['coupon_seller_id'] ?? 0;

            //set affiliate data
            if (!empty($cartTotals['affiliate_commission']) || !empty($cartTotals['affiliate_discount'])) {
                $arrayAffiliate = [
                    'id' => $cartTotals['affiliate_id'] ?? 0,
                    'referrerId' => $cartTotals['affiliate_referrer_id'] ?? 0,
                    'sellerId' => $cartTotals['affiliate_seller_id'] ?? 0,
                    'productId' => $cartTotals['affiliate_product_id'] ?? 0,
                    'commissionRate' => $cartTotals['affiliate_commission_rate'] ?? 0,
                    'commission' => $cartTotals['affiliate_commission'] ?? 0,
                    'discountRate' => $cartTotals['affiliate_discount_rate'] ?? 0,
                    'discount' => $cartTotals['affiliate_discount'] ?? 0
                ];
                $data['affiliate_data'] = serialize($arrayAffiliate);
            }
        }

        //insert the main order record.
        $this->builder->insert($data);
        $orderId = $this->db->insertID();

        //if order creation fails
        if (empty($orderId)) {
            $this->db->transRollback();
            return false;
        }

        //update order number
        $this->updateOrderNumber($orderId);

        //add order items
        $this->addOrderItems($orderId, $checkout, $checkoutItems, $orderStatus);

        if (!$isPendingPayment) {
            //add digital sales
            $this->addDigitalSales($orderId, $checkoutItems);
            //add seller earnings
            $this->addDigitalSalesSellerEarnings($orderId);
            //add payment transaction
            $this->addPaymentTransaction($checkout, $transaction, $orderId);
        }

        //set bidding quotes as completed
        $biddingModel = new BiddingModel();
        $biddingModel->setBiddingQuotesAsCompletedAfterPurchase($checkoutItems);

        //set used coupon
        if (!empty($data['coupon_code'])) {
            $couponModel = new CouponModel();
            $couponModel->addUsedCoupon($orderId, $data['coupon_code']);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', '[OrderModel] Failed to create a complete order within a transaction for Checkout ID: ' . $checkout->id);
            return false;
        }

        $this->addInvoice($orderId);
        $this->addOrderEmail($orderId);

        return $orderId;
    }

    //add order items
    public function addOrderItems(int $orderId, object $checkout, array $checkoutItems, string $orderStatus): bool
    {
        if (empty($orderId) || empty($checkoutItems) || empty($orderStatus)) {
            return false;
        }

        $this->db->transStart();

        $itemsToInsert = [];
        $productsToUpdateStock = [];
        $productIdsForAffiliate = [];
        $liveProductsMap = [];

        //find out if there are any digital products in the cart
        $digitalProductIds = [];
        foreach ($checkoutItems as $item) {
            if ($item->product_type === 'digital') {
                $digitalProductIds[] = $item->product_id;
            }
        }

        if (!empty($digitalProductIds)) {
            $liveProductsData = $this->db->table('products')->whereIn('id', $digitalProductIds)->get()->getResultObject();
            foreach ($liveProductsData as $product) {
                $liveProductsMap[$product->id] = $product;
            }
        }

        foreach ($checkoutItems as $item) {
            $data = [
                'order_id' => $orderId,
                'seller_id' => $item->seller_id,
                'buyer_id' => $checkout->user_id ?: 0,
                'buyer_type' => !empty($checkout->user_id) ? 'registered' : 'guest',
                'product_id' => $item->product_id,
                'product_type' => $item->product_type,
                'listing_type' => $item->listing_type,
                'product_title' => $item->product_title,
                'product_sku' => $item->product_sku,
                'product_unit_price' => numToDecimal($item->unit_price),
                'product_quantity' => $item->quantity,
                'product_currency' => $checkout->currency_code,
                'product_vat_rate' => $item->product_vat_rate,
                'product_vat' => numToDecimal($item->product_vat),
                'image_id' => $item->product_image_id,
                'image_data' => !empty($item->product_image_data) ? sanitizeJsonString($item->product_image_data) : '',
                'product_options_snapshot' => !empty($item->product_options_snapshot) ? sanitizeJsonString($item->product_options_snapshot) : '',
                'product_options_summary' => !empty($item->product_options_summary) ? sanitizeJsonString($item->product_options_summary) : '',
                'commission_rate' => $item->product_commission_rate ?? 0,
                'order_status' => ($item->product_type === 'digital' && $orderStatus == 'payment_received') ? 'completed' : $orderStatus,
                'is_approved' => ($item->product_type === 'digital') ? 1 : 0,
                'shipping_tracking_number' => '',
                'shipping_tracking_url' => '',
                'shipping_method' => '',
                'seller_shipping_cost' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $data['product_total_price'] = numToDecimal($item->total_price) + numToDecimal($item->product_vat);

            //if the product is physical, add its specific shipping data.
            if ($item->product_type !== 'digital') {
                $sellerId = $item->seller_id;

                $cartShippingMethods = safeJsonDecode($checkout->shipping_cost_data);
                if (!empty($cartShippingMethods)) {
                    foreach ($cartShippingMethods as $method) {
                        if ($method->shop_id == $sellerId) {
                            $data['shipping_method'] = $method->shipping_method;
                            $data['seller_shipping_cost'] = $method->shipping_cost;
                        }
                    }
                }
            }

            $itemsToInsert[] = $data;
            $productIdsForAffiliate[] = $item->product_id;

            //if the product is digital and not for multiple sale, prepare it for a stock update.
            if ($item->product_type === 'digital') {
                $product = $liveProductsMap[$item->product_id] ?? null;
                if (!empty($product) && $product->multiple_sale != 1) {
                    $productsToUpdateStock[] = $product->id;
                }
            }
        }

        //insert all order items
        if (!empty($itemsToInsert)) {
            $this->builderOrderItems->insertBatch($itemsToInsert);
        }

        //update stock for all relevant digital products
        if (!empty($productsToUpdateStock)) {
            $this->db->table('products')->whereIn('id', $productsToUpdateStock)->update(['is_sold' => 1]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', '[CheckoutModel] Failed to create order items within a transaction for Order ID: ' . $orderId);
            return false;
        }

        $commonModel = new CommonModel();
        $commonModel->deleteAffiliateCookie($productIdsForAffiliate);

        return true;
    }

    //update order number
    public function updateOrderNumber($orderId)
    {
        $this->builder->where('id', $orderId)->update(['order_number' => clrNum($orderId) + 10000]);
    }

    //add digital sales
    public function addDigitalSales($orderId, $checkoutItems)
    {
        $order = $this->getOrder($orderId);
        if (!empty($order) && !empty($checkoutItems)) {
            foreach ($checkoutItems as $item) {
                $this->addDigitalSale($item->product_id, $order->id);
            }
        }
    }

    //add digital sale
    public function addDigitalSale($productId, $orderId)
    {
        $product = getActiveProduct($productId);
        $order = $this->getOrder($orderId);
        if (!empty($product) && $product->product_type == 'digital' && !empty($order)) {
            $dataDigital = [
                'order_id' => $orderId,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'seller_id' => $product->user_id,
                'buyer_id' => $order->buyer_id,
                'license_key' => '',
                'purchase_code' => generatePurchaseCode(),
                'currency' => $product->currency,
                'price' => $product->price,
                'purchase_date' => date('Y-m-d H:i:s')
            ];
            if ($this->builderDigitalSales->insert($dataDigital)) {
                $saleId = $this->db->insertID();

                $productModel = new ProductModel();
                $licenseKey = $productModel->purchaseLicenseKey($product->id);
                if (!empty($licenseKey)) {
                    $this->builderDigitalSales->where('id', $saleId)->update(['license_key' => $licenseKey]);
                }
            }
        }
    }

    //add digital sales seller earnings
    public function addDigitalSalesSellerEarnings($orderId)
    {
        $earningsModel = new EarningsModel();
        $orderProducts = $this->getOrderItems($orderId);
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct->product_type == 'digital') {
                    $earningsModel->addSellerEarnings($orderProduct);
                }
            }
        }
    }

    //add payment transaction
    public function addPaymentTransaction($checkout, $transaction, $orderId)
    {
        $data = [
            'payment_method' => $transaction->payment_method,
            'payment_id' => $transaction->payment_id,
            'order_id' => $orderId,
            'user_id' => $checkout->user_id,
            'user_type' => !empty($checkout->user_id) ? 'registered' : 'guest',
            'currency' => $checkout->currency_code,
            'payment_amount' => numToDecimal($checkout->grand_total),
            'payment_status' => $transaction->status_text,
            'checkout_token' => $checkout->checkout_token,
            'ip_address' => getIPAddress() ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $row = $this->db->table('transactions')->where('order_id', clrNum($orderId))->get()->getRow();
        if (empty($row)) {
            $this->db->table('transactions')->insert($data);
        }
    }

    //update order payment as received
    public function updateOrderPaymentReceived($order)
    {
        if (!empty($order)) {
            //update product payment status
            $dataOrder = [
                'payment_status' => 'payment_received',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->builder->where('id', $order->id)->update($dataOrder)) {
                //update order products payment status
                $orderProducts = $this->getOrderItems($order->id);
                if (!empty($orderProducts)) {
                    foreach ($orderProducts as $orderProduct) {
                        $data = [
                            'order_status' => 'payment_received',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        $this->builderOrderItems->where('id', $orderProduct->id)->update($data);
                    }
                }
                //add invoice
                $this->addInvoice($order->id);
            }
        }
    }

    //get product commission rate
    public function getProductCommissionRate($productId)
    {
        // If no product ID is provided, there's nothing to do.
        if (empty($productId)) {
            return 0;
        }

        $product = getProduct($productId);

        if (empty($product)) {
            return 0;
        }

        // Check for a commission rate set directly on the product.
        if ($product->is_commission_set) {
            return (float)$product->commission_rate;
        }

        // Check for a commission rate set on the product's seller.
        $seller = getUser($product->user_id);
        if (!empty($seller) && $seller->is_commission_set) {
            return (float)$seller->commission_rate;
        }

        // Check for a commission rate set on the product's category or its parents.
        $category = getCategory($product->category_id);
        if (!empty($category)) {
            // Check the product's immediate category first
            if ($category->is_commission_set == 1) {
                return (float)$category->commission_rate;
            }

            // Check parent categories.
            // need to find the *nearest* ancestor that has 'is_commission_set' enabled by filtering 'c.is_commission_set = 1'
            $parent = $this->db->table('category_paths cp')
                ->select('c.commission_rate')
                ->join('categories c', 'cp.ancestor_id = c.id')
                ->where('cp.descendant_id', $category->id)
                ->where('c.is_commission_set', 1)
                ->where('c.id !=', $category->id)
                ->orderBy('cp.depth', 'ASC')
                ->get()
                ->getRow();

            // If a parent with a set rate is found, return it.
            if (!empty($parent)) {
                return (float)$parent->commission_rate;
            }
        }

        // As a last resort, use the system-wide default commission rate.
        $settings = getContextValue('paymentSettings');
        if (!empty($settings) && $settings->commission_rate !== null) {
            return (float)$settings->commission_rate;
        }

        // If no commission rate is found at any level, return 0.
        return 0;
    }

    //get orders count
    public function getOrdersCount($userId)
    {
        return $this->builder->where('buyer_id', clrNum($userId))->countAllResults();
    }

    //get paginated orders
    public function getOrdersPaginated($userId, $perPage, $offset)
    {
        return $this->builder->where('buyer_id', clrNum($userId))->orderBy('id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get orders by buyer id
    public function getOrdersByBuyerId($userId)
    {
        return $this->builder->where('buyer_id', clrNum($userId))->orderBy('orders.created_at DESC')->get()->getResult();
    }

    //get order items
    public function getOrderItems($orderId)
    {
        return $this->builderOrderItems->where('order_id', clrNum($orderId))->get()->getResult();
    }

    //get seller order products
    public function getSellerOrderProducts($orderId, $sellerId)
    {
        return $this->builderOrderItems->where('order_id', clrNum($orderId))->where('seller_id', clrNum($sellerId))->get()->getResult();
    }

    //get order product
    public function getOrderProduct($id)
    {
        return $this->builderOrderItems->where('id', clrNum($id))->get()->getRow();
    }

    //get order
    public function getOrder($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get order by order number
    public function getOrderByOrderNumber($orderNumber)
    {
        return $this->builder->where('order_number', clrNum($orderNumber))->get()->getRow();
    }
    public function getOrderByCheckoutToken($orderNumber)
    {
        return $this->builder->where('order_number', clrNum($orderNumber))->get()->getRow();
    }
    //get order by checkout token
    public function getOrderByCheckoutToken($token)
    {
        return $this->builder->where('checkout_token', cleanStr($token))->get()->getRow();
    }

    //update order product status
    public function updateOrderProductStatus($orderProductId)
    {
        $orderProduct = $this->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            if ($orderProduct->seller_id == user()->id) {
                $data = [
                    'order_status' => inputPost('order_status'),
                    'is_approved' => 0,
                    'shipping_tracking_number' => inputPost('shipping_tracking_number'),
                    'shipping_tracking_url' => inputPost('shipping_tracking_url'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($orderProduct->product_type == 'digital' && $data['order_status'] == 'payment_received') {
                    $data['order_status'] = 'completed';
                }
                if ($data['order_status'] == 'shipped') {
                    //send email
                    if (getEmailOptionStatus($this->generalSettings, 'order_shipped') == 1) {
                        $order = $this->getOrder($orderProduct->order_id);
                        if (!empty($order)) {
                            $buyerEmail = '';
                            if ($order->buyer_type == 'guest') {
                                $shipping = unserializeData($order->shipping);
                                if (!empty($shipping)) {
                                    if (!empty($shipping->sEmail)) {
                                        $buyerEmail = $shipping->sEmail;
                                    }
                                }
                            } else {
                                $buyer = getUser($orderProduct->buyer_id);
                                if (!empty($buyer)) {
                                    $buyerEmail = $buyer->email;
                                }
                            }
                            if (!empty($buyerEmail)) {
                                $emailData = [
                                    'email_type' => 'order_shipped',
                                    'email_address' => $buyerEmail,
                                    'email_subject' => trans("your_order_shipped"),
                                    'email_data' => serialize(['orderProductId' => $orderProduct->id]),
                                    'template_path' => 'email/order_shipped'
                                ];
                                addToEmailQueue($emailData);
                            }
                        }
                    }
                }
                return $this->builderOrderItems->where('id', $orderProduct->id)->update($data);
            }
        }
        return false;
    }

    //get sales count
    public function getSalesCount($status, $userId)
    {
        $this->filterSales($status);
        return $this->builder->join('order_items', 'order_items.order_id = orders.id')->select('orders.id')->groupBy('orders.id')
            ->where('order_items.seller_id', clrNum($userId))->where('order_items.order_status !=', 'refund_approved')->countAllResults();
    }

    //get paginated sales
    public function getSalesPaginated($status, $userId, $perPage, $offset)
    {
        $this->filterSales($status);
        return $this->builder->join('order_items', 'order_items.order_id = orders.id')->select('orders.*')->groupBy('orders.id')->where('order_items.seller_id', clrNum($userId))
            ->where('order_items.order_status !=', 'refund_approved')->orderBy('orders.id DESC')->limit($perPage, $offset)->get()->getResult();
    }    //get paginated sales

    //get export sales
    public function getSalesExport($status, $userId)
    {
        $this->filterSales($status, 'POST');
        return $this->builder->join('order_items', 'order_items.order_id = orders.id')->select('orders.*')->groupBy('orders.id')->where('order_items.seller_id', clrNum($userId))
            ->where('order_items.order_status !=', 'refund_approved')->orderBy('orders.id DESC')->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //filter sales
    public function filterSales($status, $formMethod = 'GET')
    {
        $paymentStatus = cleanStr(inputGet('payment_status'));
        $q = cleanStr(inputGet('q'));
        if ($formMethod == 'POST') {
            $paymentStatus = cleanStr(inputPost('payment_status'));
            $q = cleanStr(inputPost('q'));
        }
        if (!empty($paymentStatus) && ($paymentStatus == 'payment_received' || $paymentStatus == 'pending_payment')) {
            $this->builder->where('orders.payment_status', $paymentStatus);
        }
        if (!empty($q)) {
            $this->builder->where('orders.order_number', $q);
        }
        if ($status == 'active') {
            $this->builder->where('order_items.order_status !=', 'completed')->where('order_items.order_status !=', 'cancelled');
        } elseif ($status == 'completed') {
            $this->builder->where('order_items.order_status =', 'completed');
        } elseif ($status == 'cancelled') {
            $this->builder->where('order_items.order_status =', 'cancelled');
        }
    }

    //get limited sales by seller
    public function getSalesBySellerLimited($userId, $limit)
    {
        return $this->builder->join('order_items', 'order_items.order_id = orders.id')->select('orders.*')->groupBy('orders.id')
            ->where('order_items.seller_id', clrNum($userId))->orderBy('orders.created_at DESC')->limit($limit)->get()->getResult();
    }

    //check order seller
    public function checkOrderSeller($orderId)
    {
        $orderProducts = $this->getOrderItems($orderId);
        $result = false;
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $product) {
                if ($product->seller_id == user()->id) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    //get seller total price
    public function getSellerFinalPrice($orderId)
    {
        $order = $this->getOrder($orderId);
        if (!empty($order)) {
            $orderProducts = $this->getOrderItems($orderId);
            $total = 0;
            $sellerShipping = 0;
            if (!empty($orderProducts)) {
                foreach ($orderProducts as $orderProduct) {
                    if ($orderProduct->seller_id == user()->id) {
                        $total += $orderProduct->product_total_price;
                        $sellerShipping = $orderProduct->seller_shipping_cost;
                    }
                }
            }
            $total = $total + $sellerShipping;
            if (user()->id == $order->coupon_seller_id && !empty($order->coupon_discount)) {
                $total = $total - $order->coupon_discount;
            }
            $affiliateSettings = getSettingsUnserialized('affiliate');
            if ($affiliateSettings->status == 1 && $affiliateSettings->type == 'seller_based') {
                $affiliate = unserializeData($order->affiliate_data);
                if (!empty($affiliate) && !empty($affiliate['discount']) && !empty($affiliate['sellerId']) && user()->id == $affiliate['sellerId']) {
                    $affDiscount = numToDecimal($affiliate['discount']);
                    $total = $total - $affDiscount;
                }
            }
            return $total;
        }
    }

    //approve order product
    public function approveOrderProduct($orderProductId, $autoUpdate = false)
    {
        $orderProduct = $this->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            if ($autoUpdate == true || (user()->id == $orderProduct->buyer_id)) {
                $data = [
                    'is_approved' => 1,
                    'order_status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                if ($this->builderOrderItems->where('id', $orderProduct->id)->update($data)) {
                    $this->builder->where('id', $orderProduct->order_id)->update(['payment_status' => 'payment_received']);
                }
                return true;
            }
        }
        return false;
    }

    //decrease product stock after sale
    public function decreaseProductStockAfterSale($orderId)
    {
        $orderItems = $this->getOrderItems($orderId);
        if (empty($orderItems)) {
            return;
        }

        foreach ($orderItems as $item) {
            $product = getProduct($item->product_id);
            if (empty($product) || $product->product_type === 'digital') {
                continue;
            }

            $variant = null;
            $optionsSnapshot = safeJsonDecode($item->product_options_snapshot);
            if (!empty($optionsSnapshot) && !empty($optionsSnapshot->variant_hash)) {
                $productOptionsModel = new ProductOptionsModel();
                $variant = $productOptionsModel->getVariantByHash($optionsSnapshot->variant_hash);
            }

            if (!empty($variant)) {
                $newStock = max(0, $variant->quantity - $item->product_quantity);
                $this->db->table('product_option_variants')->where('variant_hash', $variant->variant_hash)->update(['quantity' => $newStock]);
            } else {
                $newStock = max(0, $product->stock - $item->product_quantity);
                $this->db->table('products')->where('id', $product->id)->update(['stock' => $newStock]);
            }
        }
    }

    //check if user bought product
    public function checkUserBoughtProduct($userId, $productId)
    {
        if (!empty($this->builderOrderItems->where('buyer_id', clrNum($userId))->where('product_id', clrNum($productId))->get()->getRow())) {
            return true;
        }
        return false;
    }

    //add invoice
    public function addInvoice($orderId)
    {
        $order = $this->getOrder($orderId);
        if (empty($order)) {
            return false;
        }
        $orderShipping = unserializeData($order->shipping);
        $invoice = $this->getInvoiceByOrderNumber($order->order_number);
        if (empty($invoice)) {
            $invoiceItems = array();
            $orderProducts = $this->getOrderItems($orderId);
            if (!empty($orderProducts)) {
                foreach ($orderProducts as $orderProduct) {
                    $seller = getUser($orderProduct->seller_id);
                    $item = [
                        'id' => $orderProduct->id,
                        'sku' => $orderProduct->product_sku,
                        'seller' => !empty($seller) ? getUsername($seller) : ''
                    ];
                    array_push($invoiceItems, $item);
                }
            }
            $client = getUser($order->buyer_id);
            if (!empty($client)) {
                $country = getCountry($client->country_id);
                $state = getState($client->state_id);
                $city = getCity($client->city_id);
                $data = [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'client_username' => getUsername($client),
                    'client_first_name' => $client->first_name,
                    'client_last_name' => $client->last_name,
                    'client_email' => $client->email,
                    'client_phone_number' => $client->phone_number,
                    'client_tax_number' => $client->tax_registration_number,
                    'client_address' => $client->address,
                    'client_country' => !empty($country) ? $country->name : '',
                    'client_state' => !empty($state) ? $state->name : '',
                    'client_city' => !empty($city) ? $city->name : '',
                    'invoice_items' => @serialize($invoiceItems),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if (!empty($orderShipping)) {
                    $data['client_first_name'] = $orderShipping->bFirstName;
                    $data['client_last_name'] = $orderShipping->bLastName;
                    $data['client_email'] = $orderShipping->bEmail;
                    $data['client_phone_number'] = $orderShipping->bPhoneNumber;
                    $data['client_address'] = $orderShipping->bAddress;
                    $data['client_country'] = $orderShipping->bCountry;
                    $data['client_state'] = $orderShipping->bState;
                    $data['client_city'] = $orderShipping->bCity;
                }
                return $this->db->table('invoices')->insert($data);
            } else {
                if (!empty($orderShipping)) {
                    $data['order_id'] = $order->id;
                    $data['order_number'] = $order->order_number;
                    $data['client_username'] = 'guest';
                    $data['client_first_name'] = $orderShipping->bFirstName;
                    $data['client_last_name'] = $orderShipping->bLastName;
                    $data['client_email'] = $orderShipping->bEmail;
                    $data['client_phone_number'] = $orderShipping->bPhoneNumber;
                    $data['client_address'] = $orderShipping->bAddress;
                    $data['client_country'] = $orderShipping->bCountry;
                    $data['client_state'] = $orderShipping->bState;
                    $data['client_city'] = $orderShipping->bCity;
                    $data['invoice_items'] = @serialize($invoiceItems);
                    $data['created_at'] = date('Y-m-d H:i:s');
                    return $this->db->table('invoices')->insert($data);
                }
            }
        }

        return false;
    }

    //get invoice
    public function getInvoice($id)
    {
        return $this->db->table('invoices')->where('id', clrNum($id))->get()->getRow();
    }

    //get invoice by order number
    public function getInvoiceByOrderNumber($orderNumber)
    {
        return $this->db->table('invoices')->where('order_number', clrNum($orderNumber))->get()->getRow();
    }

    /*
     * --------------------------------------------------------------------
     * Refund
     * --------------------------------------------------------------------
     */

    //add refund request
    public function addRefundRequest($orderProduct)
    {
        if (!empty($orderProduct)) {
            $order = $this->getOrder($orderProduct->order_id);
            if (!empty($order) && $order->status != 2) {
                if ($order->buyer_id == user()->id) {
                    $data = [
                        'buyer_id' => $orderProduct->buyer_id,
                        'seller_id' => $orderProduct->seller_id,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_product_id' => $orderProduct->id,
                        'status' => 0,
                        'is_completed' => 0,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    if ($this->builderRefundRequests->insert($data)) {
                        $id = $this->db->insertID();
                        $this->addRefundMessage($id, true);
                    }
                    return $id;
                }
            }
        }
        return false;
    }

    //add refund request message
    public function addRefundMessage($requestId, $isBuyer)
    {
        $data = [
            'request_id' => $requestId,
            'user_id' => user()->id,
            'is_buyer' => $isBuyer,
            'message' => inputPost('message'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $data['message'] = str_replace('\n', '<br/>', $data['message'] ?? '');
        if ($this->db->table('refund_requests_messages')->insert($data)) {
            $this->builderRefundRequests->where('id', clrNum($requestId))->update(['updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    //get refund requests
    public function getRefundRequest($id)
    {
        return $this->builderRefundRequests->where('id', clrNum($id))->get()->getRow();
    }

    //get refund request count
    public function getRefundRequestCount($userId, $type)
    {
        if ($type == 'buyer') {
            $this->builderRefundRequests->where('buyer_id', clrNum($userId));
        } elseif ($type == 'seller') {
            $this->builderRefundRequests->where('seller_id', clrNum($userId));
        }
        return $this->builderRefundRequests->countAllResults();
    }

    //get paginated orders
    public function getRefundRequestsPaginated($userId, $type, $perPage, $offset)
    {
        if ($type == 'buyer') {
            $this->builderRefundRequests->where('buyer_id', clrNum($userId));
        } elseif ($type == 'seller') {
            $this->builderRefundRequests->where('seller_id', clrNum($userId));
        }
        return $this->builderRefundRequests->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get buyer active refund request ids
    public function getBuyerActiveRefundRequestIds($userId)
    {
        $idsArray = array();
        $rows = $this->builderRefundRequests->where('buyer_id', clrNum($userId))->where('status !=', 2)->get()->getResult();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                array_push($idsArray, $row->order_product_id);
            }
        }
        return $idsArray;
    }

    //get seller active refund request count
    public function getSellerActiveRefundRequestCount($userId)
    {
        return $this->builderRefundRequests->where('seller_id', clrNum($userId))->where('status = 0')->countAllResults();
    }

    //get refund messages
    public function getRefundMessages($id)
    {
        return $this->db->table('refund_requests_messages')->where('request_id', clrNum($id))->orderBy('id')->get()->getResult();
    }

    //approve or decline refund request
    public function approveDeclineRefund()
    {
        $id = inputPost('id');
        $request = $this->getRefundRequest($id);
        if (!empty($request)) {
            if ($request->seller_id == user()->id) {
                $submit = inputPost('submit');
                if ($submit == 1) {
                    $data = [
                        'status' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->builderRefundRequests->where('id', $request->id)->update($data);
                } else {
                    $data = [
                        'status' => 2,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->builderRefundRequests->where('id', $request->id)->update($data);
                }
            }
            //send email
            $user = getUser($request->buyer_id);
            if (!empty($this->generalSettings->mail_username) && !empty($user)) {
                $emailData = [
                    'email_type' => 'refund',
                    'email_address' => $user->email,
                    'email_subject' => trans("refund_request"),
                    'template_path' => 'email/main',
                    'email_data' => serialize([
                        'content' => trans("msg_refund_request_update_email"),
                        'url' => generateUrl('refund_requests') . '/' . $request->id,
                        'buttonText' => trans("see_details")
                    ])
                ];
                addToEmailQueue($emailData);
            }
            return true;
        }
        return false;
    }

    //cancel order
    public function cancelOrder($orderId)
    {
        $order = $this->getOrder($orderId);
        if (!empty($order)) {
            $updateOrder = false;
            if (isAdmin()) {
                $updateOrder = true;
            } else {
                if ($order->buyer_id == user()->id && !isThereShippedProductOrder($order->id)) {
                    if ($order->payment_method != 'cash_on_delivery' || ($order->payment_method == 'cash_on_delivery' && dateDifferenceInHours(date('Y-m-d H:i:s'), $order->created_at) <= 24)) {
                        $updateOrder = true;
                    }
                }
            }
            if ($updateOrder == true) {
                $data = [
                    'order_status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                if ($this->builderOrderItems->where('order_id', $orderId)->update($data)) {
                    return $this->builder->where('id', $orderId)->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
        }
        return false;
    }

    //get transaction by payment type
    public function getTransactionByPaymentType($paymentType, $paymentId, $paymentMethod)
    {
        if ($paymentType == 'service') {
            $servicePayment = helperGetSession('mds_service_payment');
            if (!empty($servicePayment)) {
                if ($servicePayment->paymentType == 'membership') {
                    return $this->db->table('membership_transactions')->where('payment_method', cleanStr($paymentMethod))->where('payment_id', cleanStr($paymentId))->get()->getRow();
                } elseif ($servicePayment->paymentType == 'promote') {
                    return $this->db->table('promoted_transactions')->where('payment_method', cleanStr($paymentMethod))->where('payment_id', cleanStr($paymentId))->get()->getRow();
                } elseif ($servicePayment->paymentType == 'add_funds') {
                    return $this->db->table('wallet_deposits')->where('payment_method', cleanStr($paymentMethod))->where('payment_id', cleanStr($paymentId))->get()->getRow();
                }
            }
        } else {
            return $this->db->table('transactions')->where('payment_method', cleanStr($paymentMethod))->where('payment_id', cleanStr($paymentId))->get()->getRow();
        }
        return false;
    }

    //build order email
    public function addOrderEmail($orderId)
    {
        if (getEmailOptionStatus($this->generalSettings, 'new_order') == 1) {
            $order = $this->getOrder($orderId);
            if (!empty($order)) {
                $orderProducts = $this->getOrderItems($order->id);
                $shipping = unserializeData($order->shipping);
                if (!empty($order)) {
                    //send to buyer
                    $to = '';
                    if (!empty($shipping)) {
                        $to = $shipping->sEmail;
                    }
                    if ($order->buyer_type == 'registered') {
                        $user = getUser($order->buyer_id);
                        if (!empty($user)) {
                            $to = $user->email;
                        }
                    }
                    $emailData = [
                        'email_type' => 'new_order',
                        'email_address' => $to,
                        'email_subject' => trans("email_text_thank_for_order"),
                        'email_data' => serialize(['orderId' => $order->id]),
                        'template_path' => 'email/new_order'
                    ];
                    addToEmailQueue($emailData);
                    //send to sellers
                    if (!empty($orderProducts)) {
                        $sentArray = array();
                        foreach ($orderProducts as $orderProduct) {
                            $seller = getUser($orderProduct->seller_id);
                            if (!empty($seller) && !in_array($seller->id, $sentArray)) {
                                $emailData = [
                                    'email_type' => 'new_order_seller',
                                    'email_address' => $seller->email,
                                    'email_subject' => trans("you_have_new_order"),
                                    'email_data' => serialize(['orderId' => $order->id, 'sellerId' => $seller->id]),
                                    'template_path' => 'email/new_order_seller'
                                ];
                                addToEmailQueue($emailData);
                                array_push($sentArray, $seller->id);
                            }
                        }
                    }
                }
            }
        }
    }
}