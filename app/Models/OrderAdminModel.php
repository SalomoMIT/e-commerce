<?php namespace App\Models;

/*
 * STATUS
 * processing  : 0
 * completed   : 1
 * cancelled   : 2
 */

/*
 * ORDER STATUS FOR ORDER PRODUCTS
 *
 * 1. pending_payment
 * 2. payment_received
 * 3. order_processing
 * 4. shipped
 * 5. completed
 * 6. cancelled
 */

class OrderAdminModel extends BaseModel
{
    protected $builder;
    protected $builderOrderItems;
    protected $builderInvoices;
    protected $builderDigitalSales;
    protected $builderTransactions;
    protected $builderPromotedTransactions;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('orders');
        $this->builderOrderItems = $this->db->table('order_items');
        $this->builderInvoices = $this->db->table('invoices');
        $this->builderDigitalSales = $this->db->table('digital_sales');
        $this->builderTransactions = $this->db->table('transactions');
        $this->builderPromotedTransactions = $this->db->table('promoted_transactions');
    }

    //update order payment as received
    public function updateOrderPaymentReceived($orderId)
    {
        $order = $this->getOrder($orderId);
        if (empty($order)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->builder->where('id', $order->id)->update([
            'payment_status' => 'payment_received',
            'updated_at' => $now,
        ]);

        $orderProducts = $this->getOrderItems($orderId);
        if (empty($orderProducts)) {
            return true;
        }

        $orderModel = new OrderModel();
        $earningsModel = new EarningsModel();

        foreach ($orderProducts as $product) {
            $updateData = [
                'order_status' => 'payment_received',
                'updated_at' => $now,
            ];

            if ($product->product_type === 'digital') {
                $updateData['order_status'] = 'completed';

                $orderModel->addDigitalSale($product->product_id, $orderId);
                $earningsModel->addSellerEarnings($product);
            }

            $this->builderOrderItems->where('id', $product->id)->update($updateData);
        }

        return true;
    }


    //update order product status
    public function updateOrderProductStatus($orderProductId)
    {
        $orderProduct = $this->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            $data = [
                'order_status' => inputPost('order_status'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($data["order_status"] == 'completed' || $data['order_status'] == 'cancelled') {
                $data['is_approved'] = 1;
            } else {
                $data['is_approved'] = 0;
            }
            return $this->builderOrderItems->where('id', $orderProduct->id)->update($data);
        }
        return false;
    }

    //check order products status / update if all suborders completed
    public function updateOrderStatusIfCompleted($orderId)
    {
        $allComplated = true;
        $orderProducts = $this->getOrderItems($orderId);
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct->order_status == 'pending_payment' || $orderProduct->order_status == 'payment_received' || $orderProduct->order_status == 'order_processing' || $orderProduct->order_status == 'shipped') {
                    $allComplated = false;
                }
            }
            $data = [
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($allComplated == true) {
                $data['status'] = 1;
            }
            $this->builder->where('id', clrNum($orderId))->update($data);
        }
    }

    //check order payment status / update if all payments received
    public function updatePaymentStatusIfAllReceived($orderId)
    {
        $allReceived = true;
        $orderProducts = $this->getOrderItems($orderId);
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct->order_status == 'pending_payment') {
                    $allReceived = false;
                }
            }
            $data = [
                'payment_status' => 'pending_payment',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($allReceived == true) {
                $data['payment_status'] = 'payment_received';
            }
            $this->builder->where('id', clrNum($orderId))->update($data);
        }
    }

    //approve guest order product
    public function approveGuestOrderProduct($orderProductId)
    {
        $orderProduct = $this->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            $data = [
                'is_approved' => 1,
                'order_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            return $this->builderOrderItems->where('id', $orderProduct->id)->update($data);
        }
        return false;
    }

    //delete order product
    public function deleteOrderProduct($orderProductId)
    {
        $orderProduct = $this->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            return $this->builderOrderItems->where('id', $orderProduct->id)->delete();
        }
        return false;
    }

    //filter by values
    public function filterOrders($formMethod = 'GET')
    {
        $data = [
            'status' => inputGet('status'),
            'payment_status' => inputGet('payment_status'),
            'q' => inputGet('q'),
        ];
        if ($formMethod == 'POST') {
            $data = [
                'status' => inputPost('status'),
                'payment_status' => inputPost('payment_status'),
                'q' => inputPost('q'),
            ];
        }
        if (!empty($data['status'])) {
            if ($data['status'] == 'completed') {
                $this->builder->where('orders.status', 1);
            } elseif ($data['status'] == 'cancelled') {
                $this->builder->where('orders.status', 2);
            } elseif ($data['status'] == 'processing') {
                $this->builder->where('orders.status', 0);
            }
        }
        if (!empty($data['payment_status'])) {
            $this->builder->where('orders.payment_status', $data['payment_status']);
        }
        $data['q'] = trim($data['q'] ?? '');
        if (!empty($data['q'])) {
            $data['q'] = str_replace('#', '', $data['q']);
            $this->builder->where('orders.order_number', $data['q']);
        }
        $this->builder->select('orders.*, 
        (SELECT username FROM users WHERE orders.buyer_id = users.id) AS buyer_username, 
        (SELECT slug FROM users WHERE orders.buyer_id = users.id) AS buyer_slug');
    }

    //get orders count
    public function getOrdersCount()
    {
        $this->filterOrders();
        return $this->builder->countAllResults();
    }

    //get all orders count
    public function getAllOrdersCount()
    {
        return $this->builder->countAllResults();
    }

    //get orders limited
    public function getOrdersLimited($limit)
    {
        return $this->builder->orderBy('orders.id DESC')->get(clrNum($limit))->getResult();
    }

    //get paginated orders
    public function getOrdersPaginated($perPage, $offset)
    {
        $this->filterOrders();
        return $this->builder->orderBy('orders.id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get order items
    public function getOrderItems($orderId)
    {
        return $this->builderOrderItems->where('order_id', clrNum($orderId))->get()->getResult();
    }

    //get export orders
    public function getFilteredOrdersExport()
    {
        $this->filterOrders('POST');
        return $this->builder->orderBy('orders.id DESC')->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //get order
    public function getOrder($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get order product
    public function getOrderProduct($id)
    {
        return $this->builderOrderItems->where('id', clrNum($id))->get()->getRow();
    }

    //delete order
    public function deleteOrder($id)
    {
        $order = $this->getOrder($id);
        if (!empty($order)) {
            //delete order products
            $orderProducts = $this->getOrderItems($id);
            if (!empty($orderProducts)) {
                foreach ($orderProducts as $orderProduct) {
                    $this->builderOrderItems->where('id', $orderProduct->id)->delete();
                }
            }
            //delete invoice
            $this->builderInvoices->where('order_id', $order->id)->delete();
            //delete order
            return $this->builder->where('id', $order->id)->delete();
        }
        return false;
    }

    //get digital sale
    public function getDigitalSale($id)
    {
        return $this->builderDigitalSales->where('id', clrNum($id))->get()->getRow();
    }

    //get digital sales count
    public function getDigitalSalesCount()
    {
        $q = inputGet('q');
        if (!empty($q)) {
            $this->builderDigitalSales->like('purchase_code', removeSpecialCharacters($q));
        }
        return $this->builderDigitalSales->countAllResults();
    }

    //get digital sales
    public function getDigitalSalesPaginated($perPage, $offset)
    {
        $q = inputGet('q');
        if (!empty($q)) {
            $this->builderDigitalSales->like('purchase_code', removeSpecialCharacters($q));
        }
        $this->builderDigitalSales->select('digital_sales.*, 
        (SELECT order_number FROM orders WHERE digital_sales.order_id = orders.id) AS order_number,
        (SELECT username FROM users WHERE digital_sales.seller_id = users.id) AS seller_username,
        (SELECT slug FROM users WHERE digital_sales.seller_id = users.id) AS seller_slug,
        (SELECT username FROM users WHERE digital_sales.buyer_id = users.id) AS buyer_username,
        (SELECT slug FROM users WHERE digital_sales.buyer_id = users.id) AS buyer_slug');
        return $this->builderDigitalSales->orderBy('purchase_date DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get export digital sales
    public function getDigitalSalesExport()
    {
        $q = inputPost('q');
        if (!empty($q)) {
            $this->builderDigitalSales->like('purchase_code', removeSpecialCharacters($q));
        }
        $this->builderDigitalSales->select('digital_sales.*, 
        (SELECT username FROM users WHERE digital_sales.seller_id = users.id) AS seller_username,
        (SELECT username FROM users WHERE digital_sales.buyer_id = users.id) AS buyer_username');
        return $this->builderDigitalSales->orderBy('purchase_date DESC')->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //delete digital sale
    public function deleteDigitalSale($id)
    {
        $sale = $this->getDigitalSale($id);
        if (!empty($sale)) {
            return $this->builderDigitalSales->where('id', $sale->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Sales Statistics
     * --------------------------------------------------------------------
     */

    //get active sales count by seller
    public function getActiveSalesCountBySeller($sellerId)
    {
        return $this->builder->select('orders.id')->join('order_items', 'order_items.order_id = orders.id')
            ->where('order_items.seller_id', clrNum($sellerId))->where('order_status !=', 'completed')->where('order_status !=', 'cancelled')->distinct()->countAllResults();
    }

    //get completed sales count by seller
    public function getCompletedSalesCountBySeller($sellerId)
    {
        return $this->builder->select('orders.id')->join('order_items', 'order_items.order_id = orders.id')
            ->where('order_items.seller_id', clrNum($sellerId))->where('order_status', 'completed')->distinct()->countAllResults();
    }

    //get sales sum by month
    public function getSalesSumByMonth($sellerId)
    {
        return $this->db->query('SELECT SUM(product_total_price) AS total_amount, MONTH(created_at) AS month FROM order_items WHERE seller_id = ? AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)', [clrNum($sellerId)])->getResult();
    }

    /*
     * --------------------------------------------------------------------
     * Transactions
     * --------------------------------------------------------------------
     */

    //get transactions count
    public function getTransactionsCount()
    {
        $this->filterTransactions();
        return $this->builderTransactions->countAllResults();
    }

    //get paginated transactions
    public function getTransactionsPaginated($perPage, $offset)
    {
        $this->filterTransactions();
        return $this->builderTransactions->orderBy('transactions.created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get export transactions
    public function getTransactionsExport()
    {
        $this->filterTransactions('POST');
        return $this->builderTransactions->orderBy('transactions.created_at DESC')->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //filter transactions
    public function filterTransactions($formMethod = 'GET')
    {
        $q = inputGet('q');
        if ($formMethod == 'POST') {
            $q = inputPost('q');
        }
        if (!empty($q)) {
            $order = getOrderByOrderNumber($q);
            if (!empty($order)) {
                $this->builderTransactions->where('transactions.order_id', $order->id);
            } else {
                $this->builderTransactions->where('transactions.order_id', 0);
            }
        }
        $this->builderTransactions->select('transactions.*, 
        (SELECT order_number FROM orders WHERE transactions.order_id = orders.id) AS order_number,
        (SELECT username FROM users WHERE transactions.user_id = users.id) AS user_username,
        (SELECT slug FROM users WHERE transactions.user_id = users.id) AS user_slug');
    }

    //get transactions limited
    public function getTransactionsLimited($limit)
    {
        return $this->builderTransactions->orderBy('created_at DESC')->get(clrNum($limit))->getResult();
    }

    //get transaction
    public function getTransaction($id)
    {
        return $this->builderTransactions->where('id', clrNum($id))->get()->getRow();
    }

    //get transaction by order id
    public function getTransactionByOrderId($orderId)
    {
        return $this->builderTransactions->where('order_id', clrNum($orderId))->get()->getRow();
    }

    //delete transaction
    public function deleteTransaction($id)
    {
        $transaction = $this->getTransaction($id);
        if (!empty($transaction)) {
            return $this->builderTransactions->where('id', $transaction->id)->delete();
        }
        return false;
    }

    //get promoted transactions limited
    public function getPromotedTransactionsLimited($limit)
    {
        return $this->builderPromotedTransactions->orderBy('created_at DESC')->get(clrNum($limit))->getResult();
    }

    //auto approve orders
    public function autoApproveOrders()
    {
        if (!empty($this->generalSettings->auto_approve_orders_days)) {
            $daysStr = '-' . clrNum($this->generalSettings->auto_approve_orders_days) . ' days';
            $newDate = date('Y-m-d H:i:s', strtotime($daysStr));
            $orderProducts = $this->builderOrderItems->where('order_status', 'shipped')->where('updated_at <=', $newDate)->get()->getResult();
            if (!empty($orderProducts)) {
                $earningsModel = new EarningsModel();
                $orderModel = new OrderModel();
                foreach ($orderProducts as $orderProduct) {
                    if ($orderModel->approveOrderProduct($orderProduct->id, true)) {
                        //add seller earnings
                        $earningsModel->addSellerEarnings($orderProduct);
                        //update order status
                        $this->updateOrderStatusIfCompleted($orderProduct->order_id);
                    }
                }
            }
        }
    }

}
