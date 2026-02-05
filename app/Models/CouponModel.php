<?php namespace App\Models;

class CouponModel extends BaseModel
{
    protected $builder;
    protected $builderCouponProducts;
    protected $builderUsed;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('coupons');
        $this->builderCouponProducts = $this->db->table('coupon_products');
        $this->builderUsed = $this->db->table('coupons_used');
    }

    //input values
    public function inputValues()
    {
        $data = [
            'coupon_code' => removeSpecialCharacters(inputPost('coupon_code')),
            'discount_rate' => inputPost('discount_rate'),
            'coupon_count' => inputPost('coupon_count'),
            'minimum_order_amount' => inputPost('minimum_order_amount', true),
            'currency' => $this->defaultCurrency->code,
            'usage_type' => inputPost('usage_type'),
            'is_public' => !empty(inputPost('is_public')) ? 1 : 0,
            'category_ids' => '',
            'expiry_date' => inputPost('expiry_date')
        ];
        if ($data['discount_rate'] > 99) {
            $data['discount_rate'] = 99;
        }
        if ($data['discount_rate'] < 1) {
            $data['discount_rate'] = 1;
        }
        if ($data['usage_type'] != 'single' && $data['usage_type'] != 'multiple') {
            $data['usage_type'] = 'single';
        }
        if ($data['coupon_count'] <= 0) {
            $data['discount_rate'] = 0;
        }
        //selected category ids
        $array = array();
        $categoryIds = inputPost('category_id');
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $id) {
                array_push($array, $id);
            }
            $data['category_ids'] = implode(',', $array);
        }
        return $data;
    }

    //add coupon
    public function addCoupon()
    {
        $data = $this->inputValues();
        $data['minimum_order_amount'] = numToDecimal($data['minimum_order_amount']);
        if (empty($data['minimum_order_amount'])) {
            $data['minimum_order_amount'] = 0;
        }
        $data['seller_id'] = user()->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $inputCategories = inputPost('category_ids');
        if (!empty($inputCategories)) {
            $categoryIds = array_filter(array_map('intval', explode(',', $inputCategories)));
            if (!empty($categoryIds)) {
                $data['category_ids'] = implode(',', $categoryIds);
            }
        }
        return $this->builder->insert($data);
    }

    //edit coupon
    public function editCoupon($id)
    {
        $data = $this->inputValues();
        $data['minimum_order_amount'] = numToDecimal($data['minimum_order_amount']);
        if (empty($data['minimum_order_amount'])) {
            $data['minimum_order_amount'] = 0;
        }
        return $this->builder->where('id', clrNum($id))->update($data);
    }

    //add used coupon
    public function addUsedCoupon($orderId, $couponCode)
    {
        $userId = 0;
        if (authCheck()) {
            $userId = user()->id;
        }
        $data = [
            'order_id' => $orderId,
            'user_id' => $userId,
            'coupon_code' => $couponCode,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->builderUsed->insert($data);
    }

    //get coupon
    public function getCoupon($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get coupon by code
    public function getCouponByCode($code)
    {
        return $this->builder->where('coupon_code', removeSpecialCharacters($code))->get()->getRow();
    }

    //get coupon by code cart
    public function getCouponByCodeCart($code, $productIds)
    {
        if (!is_array($productIds) || count($productIds) < 1) {
            return null;
        }

        $cleanCode = removeSpecialCharacters($code);
        $builder = $this->builder;

        $coupon = $builder->select('coupons.id, coupons.seller_id, coupons.coupon_code, coupons.discount_rate, coupons.coupon_count, coupons.minimum_order_amount, coupons.currency, coupons.usage_type, coupons.expiry_date,
        (SELECT COUNT(cu.id) FROM coupons_used cu WHERE cu.coupon_code = coupons.coupon_code) AS used_coupon_count')->where('coupon_code', $cleanCode)->get()->getRow();

        if (!$coupon) {
            return null;
        }
        $productIdsResult = $this->builderCouponProducts->select('product_id')->where('coupon_id', $coupon->id)->whereIn('product_id', $productIds)->get()->getResult();

        if (empty($productIdsResult)) {
            return null;
        }

        $coupon->product_ids = array_map(function ($item) {
            return $item->product_id;
        }, $productIdsResult);

        return $coupon;
    }

    //get coupons count
    public function getCouponsCount($isPublic = false)
    {
        if ($isPublic) {
            $this->builder->where('is_public', 1);
        }
        return $this->builder->where('seller_id != ', user()->id)->countAllResults();
    }

    //get coupons paginated
    public function getCouponsPaginated($perPage, $offset, $isPublic = false)
    {
        if ($isPublic) {
            $this->builder->where('is_public', 1);
        }
        return $this->builder->select('coupons.*, users.username AS shop_name, users.first_name AS first_name, users.last_name AS last_name, users.slug AS user_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar')
            ->join('users', 'coupons.seller_id = users.id')->where('coupons.seller_id !=', user()->id)->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get vendor coupons count
    public function getVendorCouponsCount($userId)
    {
        return $this->builder->where('seller_id', clrNum($userId))->countAllResults();
    }

    //get vendor coupons paginated
    public function getVendorCouponsPaginated($userId, $perPage, $offset)
    {
        return $this->builder->where('seller_id', clrNum($userId))->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get used coupons count
    public function getUsedCouponsCount($couponCode)
    {
        return $this->builderUsed->where('coupon_code', removeSpecialCharacters($couponCode))->countAllResults();
    }

    //check coupon used
    public function isCouponUsed($userId, $couponCode)
    {
        if ($this->builderUsed->where('coupon_code', removeSpecialCharacters($couponCode))->where('user_id', clrNum($userId))->countAllResults() > 0) {
            return true;
        }
        return false;
    }

    //get coupon products by category
    public function getCouponProductsByCategory($userId, $categoryId)
    {
        return $this->db->table('products')->select('products.*, product_details.title')->join('product_details', 'product_details.product_id = products.id')
            ->where('product_details.lang_id', selectedLangId())->where('products.user_id', clrNum($userId))->where('products.category_id', clrNum($categoryId))
            ->where('products.listing_type', 'sell_on_site')->where('products.status', 1)->where('products.visibility', 1)->where('products.is_draft', 0)
            ->where('products.is_deleted', 0)->orderBy('products.created_at DESC')->get()->getResult();
    }

    //set coupon categories
    public function setCouponCategories($coupon, $categoryId, $action)
    {
        if (empty($coupon)) {
            return;
        }

        $action = strtolower(trim($action));

        $existingIds = !empty($coupon->category_ids) ? array_filter(array_map('intval', explode(',', $coupon->category_ids))) : [];

        if ($action === 'add') {
            if (!in_array($categoryId, $existingIds)) {
                $existingIds[] = $categoryId;
            }
        } elseif ($action === 'delete') {
            $existingIds = array_filter($existingIds, fn($id) => $id !== (int)$categoryId);
        } else {
            return;
        }

        $strCats = !empty($existingIds) ? implode(',', $existingIds) : '';
        $this->builder->where('id', $coupon->id)->update(['category_ids' => $strCats]);

        $productIds = $this->db->table('products')->select('id')->where('category_id', clrNum($categoryId))->get()->getResultArray();
        if (empty($productIds)) {
            return;
        }

        $productIdList = array_column($productIds, 'id');

        if ($action === 'add') {
            $existing = $this->builderCouponProducts->select('product_id')->where('coupon_id', $coupon->id)->whereIn('product_id', $productIdList)->get()->getResultArray();

            $existingIds = array_column($existing, 'product_id');
            $toInsert = array_filter($productIdList, fn($id) => !in_array($id, $existingIds));

            $batchData = array_map(fn($pid) => [
                'coupon_id'  => $coupon->id,
                'product_id' => $pid
            ], $toInsert);

            if (!empty($batchData)) {
                $this->builderCouponProducts->insertBatch($batchData);
            }

        } elseif ($action === 'delete') {
            $this->builderCouponProducts->where('coupon_id', $coupon->id)->whereIn('product_id', $productIdList)->delete();
        }
    }


    //add remove coupon
    public function addRemoveCouponProduct($coupon, $product, $action)
    {
        if (!empty($coupon) && !empty($product)) {
            if ($action == 'add') {
                $this->builderCouponProducts->insert(['coupon_id' => $coupon->id, 'product_id' => $product->id]);
            } else {
                $this->builderCouponProducts->where('coupon_id', $coupon->id)->where('product_id', $product->id)->delete();
            }
        }
    }

    //delete coupon
    public function deleteCoupon($coupon)
    {
        if (!empty($coupon)) {
            if ($this->builder->where('id', $coupon->id)->delete()) {
                $this->builderCouponProducts->where('coupon_id', $coupon->id)->delete();
                $this->builderUsed->where('coupon_code', $coupon->coupon_code)->delete();
                return true;
            }
        }
        return false;
    }
}
