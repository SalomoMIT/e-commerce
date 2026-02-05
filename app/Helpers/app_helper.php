<?php

//get default language id
if (!function_exists('defaultLangId')) {
    function defaultLangId()
    {
        if (!empty(getContextValue('defaultLang'))) {
            return getContextValue('defaultLang')->id;
        }
        return 0;
    }
}

//get active language id
if (!function_exists('selectedLangId')) {
    function selectedLangId()
    {
        if (!empty(getContextValue('activeLang'))) {
            return getContextValue('activeLang')->id;
        }
        return 0;
    }
}

//set page meta data
if (!function_exists('setPageMeta')) {
    function setPageMeta($pageTitle, $data = null)
    {
        if ($data == null) {
            $data = array();
        }

        $settings = getContextValue('generalSettings');

        $data['title'] = $pageTitle;
        $data['description'] = $pageTitle . ' - ' . $settings->application_name;
        $data['keywords'] = $pageTitle . ', ' . $settings->application_name;
        return $data;
    }
}

//get user avatar
if (!function_exists('getUserAvatar')) {
    function getUserAvatar($path, $storage = 'local')
    {
        return getStorageFileUrl($path, $storage, 'user');
    }
}

//get user payout info
if (!function_exists('getUserPayoutInfo')) {
    function getUserPayoutInfo($user): object
    {
        $allKeys = getAppDefault('formInputKeys');
        $fieldTypes = $allKeys['payout'] ?? [];

        $defaults = array_fill_keys(array_keys($fieldTypes), null);

        $data = [];
        if (!empty($user)) {
            $data = unserializeData($user->payout_info);
            $data = is_array($data) ? $data : [];
        }

        return (object)($data + $defaults);
    }
}

//get page by default name
if (!function_exists('getPageByDefaultName')) {
    function getPageByDefaultName($defaultName, $langId)
    {
        $model = new \App\Models\PageModel();
        return $model->getPageByDefaultName($defaultName, $langId);
    }
}

//get continent name by key
if (!function_exists('getContinentNameByKey')) {
    function getContinentNameByKey($continentKey)
    {
        $continents = getAppDefault('continents');
        if (!empty($continents)) {
            foreach ($continents as $key => $value) {
                if ($key == $continentKey) {
                    return $value;
                }
            }
        }
        return '';
    }
}

//get countries
if (!function_exists('getCountries')) {
    function getCountries()
    {
        $model = new \App\Models\LocationModel();
        return $model->getCountries();
    }
}

//get country
if (!function_exists('getCountry')) {
    function getCountry($id)
    {
        $model = new \App\Models\LocationModel();
        return $model->getCountry($id);
    }
}

//get state
if (!function_exists('getState')) {
    function getState($id)
    {
        $model = new \App\Models\LocationModel();
        return $model->getState($id);
    }
}

//get city
if (!function_exists('getCity')) {
    function getCity($id)
    {
        $model = new \App\Models\LocationModel();
        return $model->getCity($id);
    }
}

//get states by country
if (!function_exists('getStatesByCountry')) {
    function getStatesByCountry($countryId)
    {
        $model = new \App\Models\LocationModel();
        return $model->getStatesByCountry($countryId);
    }
}

//get cities by state
if (!function_exists('getCitiesByState')) {
    function getCitiesByState($stateId)
    {
        $model = new \App\Models\LocationModel();
        return $model->getCitiesByState($stateId);
    }
}

//get role
if (!function_exists('getRoleById')) {
    function getRoleById($id)
    {
        $model = new \App\Models\MembershipModel();
        return $model->getRole($id);
    }
}

//get role name
if (!function_exists('getRoleName')) {
    function getRoleName($role)
    {
        $name = '';
        $nameDefault = '';
        $nameFirst = '';
        if (!empty($role)) {
            $nameArray = unserializeData($role->role_name);
            if (!empty($nameArray) && countItems($nameArray) > 0) {
                $i = 0;
                foreach ($nameArray as $item) {
                    if (!empty($item['lang_id']) && !empty($item['name'])) {
                        if ($item['lang_id'] == selectedLangId()) {
                            $name = $item['name'];
                        }
                        if ($item['lang_id'] == getContextValue('defaultLang')->id) {
                            $nameDefault = $item['name'];
                        }
                        if ($i == 0) {
                            $nameFirst = $item['name'];
                        }
                    }
                    $i++;
                }
            }
        }
        if (empty($name)) {
            $name = $nameDefault;
        }
        if (empty($name)) {
            $name = $nameFirst;
        }
        return $name;
    }
}

//get membership plan
if (!function_exists('getMembershipPlan')) {
    function getMembershipPlan($id)
    {
        $model = new \App\Models\MembershipModel();
        return $model->getPlan($id);
    }
}

//get membership plan title
if (!function_exists('getMembershipPlanTitle')) {
    function getMembershipPlanTitle($id)
    {
        $model = new \App\Models\MembershipModel();
        return $model->getMembershipPlanTitle($id);
    }
}

//get membership plan name
if (!function_exists('getMembershipPlanName')) {
    function getMembershipPlanName($titleArray, $langId)
    {
        if (!empty($titleArray)) {
            $array = unserializeData($titleArray);
            if (!empty($array)) {
                $main = '';
                foreach ($array as $item) {
                    if ($item['lang_id'] == $langId) {
                        return $item['title'];
                    }
                    if ($item['lang_id'] == getContextValue('generalSettings')->site_lang) {
                        $main = $item['title'];
                    }
                }
                return $main;
            }
        }
        return '';
    }
}

//get membership plan features
if (!function_exists('getMembershipPlanFeatures')) {
    function getMembershipPlanFeatures($featuresArray, $langId)
    {
        if (!empty($featuresArray)) {
            $array = unserializeData($featuresArray);
            if (!empty($array)) {
                $main = '';
                foreach ($array as $item) {
                    if ($item['lang_id'] == $langId) {
                        if (!empty($item['features'])) {
                            return $item['features'];
                        }
                    }
                    if ($item['lang_id'] == getContextValue('defaultLang')->id) {
                        if (!empty($item['features'])) {
                            $main = $item['features'];
                        }
                    }
                }
                return $main;
            }
        }
        return '';
    }
}

//get location
if (!function_exists('getLocation')) {
    function getLocation($object, $isForEstimatedDelivery = false)
    {
        $model = new \App\Models\LocationModel();
        $location = '';
        if (!empty($object)) {
            if ($isForEstimatedDelivery == false) {
                if (!empty($object->address)) {
                    $location = $object->address;
                }
                if (!empty($object->zip_code)) {
                    $location .= ' ' . $object->zip_code;
                }
                if (!empty($object->city_id)) {
                    $city = $model->getCity($object->city_id);
                    if (!empty($city)) {
                        if (!empty($object->address) || !empty($object->zip_code)) {
                            $location .= " ";
                        }
                        $location .= $city->name;
                    }
                }
            }
            if (!empty($object->state_id)) {
                $state = $model->getState($object->state_id);
                if (!empty($state)) {
                    if (!empty($object->address) || !empty($object->zip_code) || !empty($object->city_id)) {
                        $location .= ', ';
                    }
                    if ($isForEstimatedDelivery == true) {
                        $location = '';
                    }
                    $location .= $state->name;
                }
            }
            if (!empty($object->country_id)) {
                $country = $model->getCountry($object->country_id);
                if (!empty($country)) {
                    if (!empty($object->state_id) || !empty($object->city_id) || !empty($object->address) || !empty($object->zip_code)) {
                        $location .= ', ';
                    }
                    $location .= $country->name;
                }
            }
        }
        return $location;
    }
}

//get estimated delivery location
if (!function_exists('getEstimatedDeliveryLocation')) {
    function getEstimatedDeliveryLocation()
    {
        $objUser = new stdClass();
        if (authCheck()) {
            $objUser = user();
        } else {
            $location = helperGetSession('mds_estimated_delivery_location');
            if (!empty($location) && !empty($location['country_id']) && !empty($location['state_id'])) {
                $objUser->country_id = $location['country_id'];
                $objUser->state_id = $location['state_id'];
            }
        }
        if (!empty($objUser) && !empty($objUser->country_id) && !empty($objUser->state_id)) {
            return getLocation($objUser, true);
        }
        return '';
    }
}

//add to email queue
if (!function_exists('addToEmailQueue')) {
    function addToEmailQueue($data)
    {
        $model = new \App\Models\EmailModel();
        return $model->addToEmailQueue($data);
    }
}

//get order
if (!function_exists('getOrder')) {
    function getOrder($id)
    {
        $model = new \App\Models\OrderModel();
        return $model->getOrder($id);
    }
}

//get order by order number
if (!function_exists('getOrderByOrderNumber')) {
    function getOrderByOrderNumber($orderNumber)
    {
        $model = new \App\Models\OrderModel();
        return $model->getOrderByOrderNumber($orderNumber);
    }
}

//get order product
if (!function_exists('getOrderProduct')) {
    function getOrderProduct($id)
    {
        $model = new \App\Models\OrderModel();
        return $model->getOrderProduct($id);
    }
}

//get earning by order product
if (!function_exists('getEarningByOrderProductId')) {
    function getEarningByOrderProductId($orderProductId, $orderNumber)
    {
        $model = new \App\Models\EarningsModel();
        return $model->getEarningByOrderProductId($orderProductId, $orderNumber);
    }
}

//check if user bought product
if (!function_exists('checkUserBoughtProduct')) {
    function checkUserBoughtProduct($userId, $productId)
    {
        $model = new \App\Models\OrderModel();
        return $model->checkUserBoughtProduct($userId, $productId);
    }
}

//get currency by code
if (!function_exists('getCurrencyByCode')) {
    function getCurrencyByCode($currencyCode)
    {
        if (!empty(getContextValue('currencies')[$currencyCode])) {
            return getContextValue('currencies')[$currencyCode];
        }
    }
}

//get currency symbol
if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol($currencyCode)
    {
        if (!empty(getContextValue('currencies'))) {
            if (isset(getContextValue('currencies')[$currencyCode])) {
                return getContextValue('currencies')[$currencyCode]->symbol;
            }
        }
        return '';
    }
}

//get shipping locations by zone
if (!function_exists('getShippingLocationsByZone')) {
    function getShippingLocationsByZone($zoneId)
    {
        $model = new \App\Models\ShippingModel();
        return $model->getShippingLocationsByZone($zoneId);
    }
}

//get shipping payment methods by zone
if (!function_exists('getShippingPaymentMethodsByZone')) {
    function getShippingPaymentMethodsByZone($zoneId)
    {
        $model = new \App\Models\ShippingModel();
        return $model->getShippingZoneMethods($zoneId);
    }
}

//get coupon
if (!function_exists('getCouponById')) {
    function getCouponById($id)
    {
        $model = new \App\Models\CouponModel();
        return $model->getCoupon($id);
    }
}

//get coupon by code
if (!function_exists('getCouponByCode')) {
    function getCouponByCode($code)
    {
        $model = new \App\Models\CouponModel();
        return $model->getCouponByCode($code);
    }
}

//get used coupons count
if (!function_exists('getUsedCouponsCount')) {
    function getUsedCouponsCount($couponCode)
    {
        $model = new \App\Models\CouponModel();
        return $model->getUsedCouponsCount($couponCode);
    }
}

//get subcategories
if (!function_exists('getSubCategories')) {
    function getSubCategories($parentId)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getSubCategoriesByParentId($parentId);
    }
}

//get coupon products by category
if (!function_exists('getCouponProductsByCategory')) {
    function getCouponProductsByCategory($userId, $categoryId)
    {
        $model = new \App\Models\CouponModel();
        return $model->getCouponProductsByCategory($userId, $categoryId);
    }
}

//get user plan
if (!function_exists('getUserPlanByUserId')) {
    function getUserPlanByUserId($userId, $onlyActive = true)
    {
        $model = new \App\Models\MembershipModel();
        return $model->getUserPlanByUserId($userId, $onlyActive);
    }
}

//calculate user rating
if (!function_exists('calculateUserRating')) {
    function calculateUserRating($userId)
    {
        $model = new \App\Models\CommonModel();
        return $model->calculateUserRating($userId);
    }
}

//get user drafts count
if (!function_exists('getUserDownloadsCount')) {
    function getUserDownloadsCount($userId)
    {
        $model = new \App\Models\ProductModel();
        return $model->getUserDownloadsCount($userId);
    }
}

//get followers count
if (!function_exists('getFollowersCount')) {
    function getFollowersCount($followingId)
    {
        $model = new \App\Models\ProfileModel();
        return $model->getFollowersCount($followingId);
    }
}

//get following users count
if (!function_exists('getFollowingUsersCount')) {
    function getFollowingUsersCount($followerId)
    {
        $model = new \App\Models\ProfileModel();
        return $model->getFollowingUsersCount($followerId);
    }
}

//get my reviews count
if (!function_exists('getMyReviewsCount')) {
    function getMyReviewsCount($userId)
    {
        $model = new \App\Models\CommonModel();
        return $model->getMyReviewsCount($userId);
    }
}

//get user profile stats
if (!function_exists('getUserProfileStats')) {
    function getUserProfileStats($user)
    {
        $cacheKey = 'profile_stats_' . $user->id;
        $cache = \Config\Services::cache();

        if (SHORT_CACHE_STATUS == 1) {
            if ($cachedData = $cache->get($cacheKey)) {
                return $cachedData;
            }
        }

        $profileModel = new \App\Models\ProfileModel();
        $commonModel = new \App\Models\CommonModel();

        $stats = [
            'productCount' => 0,
            'followersCount' => $profileModel->getFollowersCount($user->id),
            'followingCount' => $profileModel->getFollowingUsersCount($user->id),
            'userReviewsCount' => $commonModel->getUserReviewsCount($user->id, true),
            'userRating' => 0,
            'userRatingCount' => 0,
        ];

        if (isVendor($user)) {
            $productModel = new \App\Models\ProductModel();
            $stats['productCount'] = $productModel->getSellerTotalProductsCount($user->id);
            $userRating = calculateUserRating($user->id);
            $stats['userRating'] = !empty($userRating) && !empty($userRating->rating) ? $userRating->rating : 0;
            $stats['userRatingCount'] = !empty($userRating) && !empty($userRating->count) ? $userRating->count : 0;
        }

        foreach ($stats as $key => &$value) {
            $value = numberFormatShort($value);
        }
        unset($value);

        $statsObject = (object)$stats;

        if (SHORT_CACHE_STATUS == 1) {
            $cache->save($cacheKey, $statsObject, SHORT_CACHE_REFRESH_TIME);
        }

        return $statsObject;
    }
}

if (!function_exists('isUserOnline')) {
    function isUserOnline($timestamp)
    {
        $timeAgo = strtotime($timestamp);
        $currentTime = time();
        $timeDifference = $currentTime - $timeAgo;
        $seconds = $timeDifference;
        $minutes = round($seconds / 60);
        if ($minutes <= 2) {
            return true;
        } else {
            return false;
        }
    }
}

//check user follows
if (!function_exists('isUserFollows')) {
    function isUserFollows($followingId, $followerId)
    {
        $model = new \App\Models\ProfileModel();
        return $model->isUserFollows($followingId, $followerId);
    }
}

//get review
if (!function_exists('getReview')) {
    function getReview($productId, $userId)
    {
        $model = new \App\Models\CommonModel();
        return $model->getReview($productId, $userId);
    }
}

//get digital sale by buyer id
if (!function_exists('getDigitalSaleByBuyerId')) {
    function getDigitalSaleByBuyerId($buyerId, $productId)
    {
        $model = new \App\Models\ProductModel();
        return $model->getDigitalSaleByBuyerId($buyerId, $productId);
    }
}

//get digital sale by order id
if (!function_exists('getDigitalSaleByOrderId')) {
    function getDigitalSaleByOrderId($buyerId, $productId, $orderId)
    {
        $model = new \App\Models\ProductModel();
        return $model->getDigitalSaleByOrderId($buyerId, $productId, $orderId);
    }
}

//get order items
if (!function_exists('getOrderItems')) {
    function getOrderItems($orderId)
    {
        $model = new \App\Models\OrderModel();
        return $model->getOrderItems($orderId);
    }
}

//get payment gateway
if (!function_exists('getPaymentGateway')) {
    function getPaymentGateway($nameKey)
    {
        $model = new \App\Models\SettingsModel();
        return $model->getPaymentGateway($nameKey);
    }
}

//get payment method
if (!function_exists('getPaymentMethod')) {
    function getPaymentMethod($paymentMethod)
    {
        if (empty($paymentMethod)) {
            return '';
        }

        return trans(strtolower($paymentMethod));
    }
}

//get payment status
if (!function_exists('getPaymentStatus')) {
    function getPaymentStatus($paymentStatus)
    {
        if ($paymentStatus == "payment_received") {
            return trans("payment_received");
        } elseif ($paymentStatus == "pending_payment") {
            return trans("pending_payment");
        } elseif ($paymentStatus == "Completed") {
            return trans("completed");
        } else {
            return $paymentStatus;
        }
    }
}

//get active payment gateways
if (!function_exists('getActivePaymentGateways')) {
    function getActivePaymentGateways()
    {
        $model = new \App\Models\SettingsModel();
        return $model->getActivePaymentGateways();
    }
}

//get transaction by order id
if (!function_exists('getTransactionByOrderId')) {
    function getTransactionByOrderId($orderId)
    {
        $model = new \App\Models\OrderAdminModel();
        return $model->getTransactionByOrderId($orderId);
    }
}

//get deposit transaction
if (!function_exists('getDepositTransaction')) {
    function getDepositTransaction($id)
    {
        $model = new \App\Models\EarningsModel();
        return $model->getDepositTransaction($id);
    }
}

//get cart customer data
if (!function_exists('getCartCustomerData')) {
    function getCartCustomerData($checkout)
    {
        $user = null;
        if (authCheck()) {
            $user = user();
        } else {
            $user = new stdClass();
            $user->id = 0;
            $user->first_name = '';
            $user->last_name = '';
            $user->email = "unknown@domain.com";
            $user->phone_number = "11111111";
            $cartShipping = safeJsonDecode($checkout->shipping_data);
            if (!empty($cartShipping)) {
                if (!empty($cartShipping->sFirstName)) {
                    $user->first_name = $cartShipping->sFirstName;
                }
                if (!empty($cartShipping->sLastName)) {
                    $user->last_name = $cartShipping->sLastName;
                }
                if (!empty($cartShipping->sEmail)) {
                    $user->email = $cartShipping->sEmail;
                }
                if (!empty($cartShipping->sPhoneNumber)) {
                    $user->phone_number = $cartShipping->sPhoneNumber;
                }
            }
        }
        return $user;
    }
}

//get checkout payment title
if (!function_exists('getCheckoutPaymentTitle')) {
    function getCheckoutPaymentTitle($checkout)
    {
        $translationKey = 'cart_payment';
        if ($checkout->checkout_type == 'service') {
            $translationKey = match ($checkout->service_type) {
                'add_funds' => 'wallet_deposit',
                'promote' => 'product_promoting_payment',
                'membership' => 'membership_plan_payment',
                default => $translationKey,
            };
        }

        return trans($translationKey);
    }
}

//get tax name
if (!function_exists('getTaxName')) {
    function getTaxName($nameArray, $langId)
    {
        $default = 'Global Tax';

        if (empty($nameArray)) {
            return $default;
        }

        if (!empty($nameArray[$langId])) {
            return $nameArray[$langId];
        }

        $defaultLangId = getContextValue('defaultLang')->id ?? null;
        if ($defaultLangId && !empty($nameArray[$defaultLangId])) {
            return $nameArray[$defaultLangId];
        }

        return $default;
    }
}

//can user pay with balance
if (!function_exists('canPayWithBalance')) {
    function canPayWithBalance($total, $currencyCode)
    {
        if (getContextValue('paymentSettings')->pay_with_wallet_balance && authCheck() && !empty($total) && !empty($currencyCode)) {
            $balance = user()->balance;
            $total = numToDecimal(convertToDefaultCurrency($total, $currencyCode));
            if ($balance >= $total) {
                return true;
            }
        }
        return false;
    }
}

//check if product is an active affiliate product
if (!function_exists('isActiveAffiliateProduct')) {
    function isActiveAffiliateProduct($product, $vendor)
    {
        $status = false;
        $affiliateSettings = getSettingsUnserialized('affiliate');
        if ($affiliateSettings->status == 1 && !empty($product) && !empty($vendor)) {
            if (authCheck() && user()->is_affiliate == 1 && $product->listing_type != 'ordinary_listing' && $product->listing_type != 'bidding' && $product->is_free_product != 1) {
                $status = true;
            }
            if ($affiliateSettings->type == 'seller_based') {
                if ($vendor->vendor_affiliate_status == 0) {
                    $status = false;
                } elseif ($vendor->vendor_affiliate_status == 2 && $product->is_affiliate != 1) {
                    $status = false;
                }
            }
            if (authCheck() && user()->id == $product->user_id) {
                $status = false;
            }
        }
        return $status;
    }
}

//get affiliate rates
if (!function_exists('getAffiliateRates')) {
    function getAffiliateRates($product)
    {
        $data = new stdClass();
        $data->commissionRate = 0;
        $data->discountRate = 0;
        if (!empty($product)) {
            $affiliateSettings = getSettingsUnserialized('affiliate');
            if ($affiliateSettings->status == 1) {
                if ($affiliateSettings->type == 'seller_based') {
                    $seller = getUser($product->user_id);
                    if (!empty($seller)) {
                        if (($seller->vendor_affiliate_status == 1) || ($seller->vendor_affiliate_status == 2 && $product->is_affiliate == 1)) {
                            $data->commissionRate = !empty($seller->affiliate_commission_rate) ? $seller->affiliate_commission_rate : 0;
                            $data->discountRate = !empty($seller->affiliate_discount_rate) ? $seller->affiliate_discount_rate : 0;
                        }
                    }
                } else {
                    $data->commissionRate = !empty($affiliateSettings->commission_rate) ? $affiliateSettings->commission_rate : 0;
                    $data->discountRate = !empty($affiliateSettings->discount_rate) ? $affiliateSettings->discount_rate : 0;
                }
            }
        }
        return $data;
    }
}

//get additional invoice info
if (!function_exists('getAdditionalInvoiceInfo')) {
    function getAdditionalInvoiceInfo($langId)
    {
        $info = '';
        $paymentSettings = getContextValue('paymentSettings');
        if (!empty($paymentSettings->additional_invoice_info)) {
            $data = unserializeData($paymentSettings->additional_invoice_info);
            if (!empty($data)) {
                foreach ($data as $item) {
                    if (!empty($item['lang_id']) && !empty($item['text'])) {
                        if ($item['lang_id'] == $langId) {
                            $info = $item['text'];
                        }
                    }
                }
            }
        }
        return $info;
    }
}


//get email option status
if (!function_exists('getEmailOptionStatus')) {
    function getEmailOptionStatus($generalSettings, $key)
    {
        if (!empty($generalSettings->email_options)) {
            $data = unserializeData($generalSettings->email_options);
            if (!empty($data) && !empty($data[$key]) && $data[$key] == 1) {
                return 1;
            }
        }
        return 0;
    }
}

//get last bank transfer record
if (!function_exists('getLastBankTransfer')) {
    function getLastBankTransfer($reportType, $itemId)
    {
        $model = new \App\Models\CommonModel();
        return $model->getLastBankTransfer($reportType, $itemId);
    }
}

//check vendor commission debt
if (!function_exists('checkVendorCommissionDept')) {
    function checkVendorCommissionDept()
    {
        if (authCheck()) {
            if (user()->commission_debt > 0) {
                $earningsModel = new \App\Models\EarningsModel();
                $earningsModel->deductCommissionDebtFromWallet();
            }
        }
    }
}

//set commission form values
if (!function_exists('setCommissionFormValues')) {
    function setCommissionFormValues($data)
    {
        $validModes = ['default', 'custom', 'none'];
        $mode = inputPost('commission_mode');

        if (!in_array($mode, $validModes, true)) {
            $mode = 'default';
        }

        $rate = inputPost('commission_rate');

        if ($mode === 'custom') {
            $data['is_commission_set'] = 1;
            $data['commission_rate'] = (is_numeric($rate) && $rate >= 0) ? (float)$rate : 0;
        } elseif ($mode === 'none') {
            $data['is_commission_set'] = 1;
            $data['commission_rate'] = 0;
        } else {
            // default (inherit)
            $data['is_commission_set'] = 0;
            $data['commission_rate'] = 0;
        }

        return $data;
    }
}

//get vendor pages
if (!function_exists('getVendorPages')) {
    function getVendorPages($userId)
    {
        $model = new \App\Models\PageModel();
        return $model->getVendorPagesByUserId($userId);
    }
}

//convert number to decimal format
if (!function_exists('numToDecimal')) {
    function numToDecimal($price)
    {
        if ($price === null || trim($price) === '') {
            return 0;
        }

        $sanitized = preg_replace('/[^\d.,-]/', '', $price);

        $lastCommaPos = strrpos($sanitized, ',');
        $lastDotPos = strrpos($sanitized, '.');

        if ($lastCommaPos !== false && $lastDotPos !== false) {
            if ($lastCommaPos > $lastDotPos) {
                $sanitized = str_replace('.', '', $sanitized);
                $sanitized = str_replace(',', '.', $sanitized);
            } else {
                $sanitized = str_replace(',', '', $sanitized);
            }
        } elseif ($lastCommaPos !== false) {
            $sanitized = str_replace(',', '.', $sanitized);
        }

        if (!is_numeric($sanitized)) {
            return 0;
        }

        $priceFloat = (float)$sanitized;
        $maxValue = 9999999999.99;

        if ($priceFloat < 0 || $priceFloat > $maxValue) {
            return 0;
        }

        if (fmod($priceFloat, 1.0) === 0.0) {
            return (string)(int)$priceFloat;
        }

        return number_format($priceFloat, 2, '.', '');
    }
}

//format price
if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        $price = numToDecimal($price);

        if (empty($price)) {
            return '0';
        }

        $isWholeNumber = ($price == (int)$price);
        $decimals = $isWholeNumber ? 0 : 2;

        $defaultCurrency = getContextValue('defaultCurrency');

        if (!empty($defaultCurrency->currency_format) && $defaultCurrency->currency_format === 'european') {
            return number_format($price, $decimals, ',', '');
        }

        return number_format($price, $decimals, '.', '');
    }
}

//render price input
if (!function_exists('renderPriceInput')) {
    function renderPriceInput(string $name, $value = null, ?array $options = [], ?bool $useInputGroup = true): string
    {
        $id = $options['id'] ?? 'input-' . uniqid();

        $class = 'form-control form-input input-price';
        if (!empty($options['class'])) {
            $class .= ' ' . trim($options['class']);
        }

        // Lang & placeholder based on currency format
        $defaultCurrency = getContextValue('defaultCurrency');
        $placeholder = '0.00';
        if (!empty($defaultCurrency->currency_format) && $defaultCurrency->currency_format === 'european') {
            $placeholder = '0,00';
        }

        // Required attribute
        $required = !empty($options['required']) ? 'required' : '';

        // HTML input tag
        $inputTag = '<input type="text" name="' . esc($name) . '" id="' . esc($id) . '" class="' . esc($class) . '" value="' . esc(formatPrice($value)) . '" inputmode="decimal" placeholder="' . esc($placeholder) . '" maxlength="13" ' . $required . '>';

        if ($useInputGroup && !empty($defaultCurrency)) {
            return '<div class="input-group"><span class="input-group-addon">' . esc($defaultCurrency->code) . ' (' . esc($defaultCurrency->symbol) . ')' . '</span>' . $inputTag . '</div>';
        }

        return $inputTag;
    }
}

//create form checkbox
if (!function_exists('formCheckbox')) {
    function formCheckbox($inputName, $val, $text, $checkedValue = null)
    {
        $id = 'c' . uniqid();
        $check = $checkedValue == $val ? ' checked' : '';
        return '<div class="custom-control custom-checkbox">' . PHP_EOL .
            '<input type="checkbox" name="' . $inputName . '" value="' . $val . '" id="' . $id . '" class="custom-control-input"' . $check . '>' . PHP_EOL .
            '<label for="' . $id . '" class="custom-control-label">' . $text . '</label>' . PHP_EOL .
            '</div>';
    }
}

//create form radio button
if (!function_exists('formRadio')) {
    function formRadio($inputName, $val1, $val2, $op1Text, $op2Text, $checkedValue = null, $colClass = 'col-md-12 col-lg-4')
    {
        $id1 = 'r' . uniqid();
        $id2 = 'r' . uniqid();
        $op1Check = $checkedValue == $val1 ? ' checked' : '';
        $op2Check = $checkedValue != $val1 ? ' checked' : '';
        return
            '<div class="row">' . PHP_EOL .
            '    <div class="' . $colClass . ' col-sm-12">' . PHP_EOL .
            '        <div class="custom-control custom-radio">' . PHP_EOL .
            '            <input type="radio" name="' . $inputName . '" value="' . $val1 . '" id="' . $id1 . '" class="custom-control-input"' . $op1Check . '>' . PHP_EOL .
            '            <label for="' . $id1 . '" class="custom-control-label">' . $op1Text . '</label>' . PHP_EOL .
            '        </div>' . PHP_EOL .
            '    </div>' . PHP_EOL .
            '    <div class="' . $colClass . ' col-sm-12">' . PHP_EOL .
            '         <div class="custom-control custom-radio">' . PHP_EOL .
            '             <input type="radio" name="' . $inputName . '" value="' . $val2 . '" id="' . $id2 . '" class="custom-control-input"' . $op2Check . '>' . PHP_EOL .
            '             <label for="' . $id2 . '" class="custom-control-label">' . $op2Text . '</label>' . PHP_EOL .
            '        </div>' . PHP_EOL .
            '    </div>' . PHP_EOL .
            '</div>';
    }
}

if (!function_exists('formSwitch')) {
    function formSwitch($name, $label = '', $value = null)
    {
        $id = 'sw_' . uniqid();
        $checkedAttr = !empty($value) ? ' checked' : '';
        return
            '<div class="form-check form-switch">' . PHP_EOL .
            '    <input class="form-check-input" type="checkbox" name="' . esc($name) . '" id="' . esc($id) . '" value="1"' . $checkedAttr . '>' . PHP_EOL .
            '    <label class="form-check-label" for="' . esc($id) . '">' . esc($label) . '</label>' . PHP_EOL .
            '</div>';
    }
}