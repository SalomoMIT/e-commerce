<?php namespace App\Models;

class PromoteModel extends BaseModel
{
    protected $builder;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('promoted_transactions');
    }

    //add promote transaction
    public function addPromoteTransaction($checkout, $transaction)
    {
        if (empty($checkout) || empty($checkout->user_id) || empty($transaction) || empty($transaction->payment_id)) {
            return false;
        }

        $promoteData = safeJsonDecode($checkout->service_data);
        $promoteTaxData = safeJsonDecode($checkout->service_tax_data, true);
        $productId = !empty($promoteData) && !empty($promoteData->productId) ? $promoteData->productId : 0;
        $dayCount = !empty($promoteData) && !empty($promoteData->dayCount) ? $promoteData->dayCount : 0;
        $purchasedPlan = !empty($promoteData) && !empty($promoteData->purchasedPlan) ? $promoteData->purchasedPlan : '';

        $data = [
            'payment_method' => $transaction->payment_method,
            'payment_id' => $transaction->payment_id,
            'user_id' => $checkout->user_id,
            'product_id' => $productId,
            'currency' => $checkout->currency_code,
            'payment_amount' => $checkout->grand_total,
            'payment_status' => $transaction->status_text,
            'purchased_plan' => $purchasedPlan,
            'day_count' => $dayCount,
            'checkout_token' => $checkout->checkout_token,
            'ip_address' => getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($promoteTaxData)) {
            $cartModel = new CartModel();
            $array = $cartModel->convertServiceTaxesCurrency($promoteTaxData, $checkout->currency_code);
            $data['global_taxes_data'] = serialize($array);
        }

        if ($this->builder->insert($data)) {
            $id = $this->db->insertID();

            if ($transaction->status == 1) {
                $serviceData = (object)[
                    'productId' => $productId,
                    'dayCount' => $dayCount,
                    'purchasedPlan' => $purchasedPlan,
                ];

                $this->addToPromotedProducts($serviceData);
            }

            return $id;
        }

        return false;
    }

    //add to promoted products
    public function addToPromotedProducts($serviceData)
    {
        if (!empty($serviceData)) {
            $product = getProduct($serviceData->productId);
            if (!empty($product)) {
                $date = date('Y-m-d H:i:s');
                $endDate = date('Y-m-d H:i:s', strtotime($date . ' + ' . $serviceData->dayCount . ' days'));
                $data = [
                    'promote_plan' => $serviceData->purchasedPlan,
                    'promote_day' => $serviceData->dayCount,
                    'is_promoted' => 1,
                    'promote_start_date' => $date,
                    'promote_end_date' => $endDate
                ];
                return $this->db->table('products')->where('id', $product->id)->update($data);
            }
        }
        return false;
    }

    //get transactions count
    public function getTransactionsCount($userId)
    {
        $this->filterTransactions($userId);
        return $this->builder->countAllResults();
    }

    //get transactions paginated
    public function getTransactionsPaginated($userId, $perPage, $offset)
    {
        $this->filterTransactions($userId);
        return $this->builder->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter transactions
    public function filterTransactions($userId)
    {
        $q = inputGet('q');
        $paymentStatus = inputGet('payment_status');
        if (!empty($q)) {
            $this->builder->where('promoted_transactions.payment_id', $q);
        }
        if (!empty($userId)) {
            $this->builder->where('user_id', clrNum($userId));
        }
        if (!empty($paymentStatus)) {
            if ($paymentStatus == 'pending_payment') {
                $this->builder->where('payment_status', 'pending_payment');
            } else {
                $this->builder->where('payment_status !=', 'pending_payment');
            }
        }
        $this->builder->join('users', 'users.id = promoted_transactions.user_id')
            ->select('promoted_transactions.*, users.slug AS user_slug, users.username AS user_username');
    }

    //get transaction
    public function getTransaction($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //set transaction as payment receved
    public function setTransactionAsPaymentReceived($id)
    {
        $transaction = $this->getTransaction($id);
        if (!empty($transaction)) {
            $this->db->table('promoted_transactions')->where('id', $transaction->id)->update(['payment_status' => "Completed"]);
        }
    }

    //delete transaction
    public function deleteTransaction($id)
    {
        $transaction = $this->getTransaction($id);
        if (!empty($transaction)) {
            return $this->builder->where('id', $transaction->id)->delete();
        }
        return false;
    }

}
