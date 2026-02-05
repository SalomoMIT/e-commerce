<?php

//get menu categories
if (!function_exists('renderCategoryMenu')) {
    function renderCategoryMenu($langId, $parentCategories)
    {
        $model = new \App\Models\CategoryModel();
        return $model->renderCategoryMenu($langId, $parentCategories);
    }
}

//get category by id
if (!function_exists('getCategory')) {
    function getCategory($id)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getCategory($id);
    }
}

//get category details
if (!function_exists('getCategoryDetails')) {
    function getCategoryDetails($id, $langId)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getCategoryDetails($id, $langId);
    }
}

//get categories by id array
if (!function_exists('getCategoriesByIdArray')) {
    function getCategoriesByIdArray($array)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getCategoriesByIdArray($array);
    }
}

//get subcategories by id
if (!function_exists('getSubCategoriesByParentId')) {
    function getSubCategoriesByParentId($parentId)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getSubCategoriesByParentId($parentId);
    }
}

//get category parent tree
if (!function_exists('getCategoryParentTree')) {
    function getCategoryParentTree($categoryId, $onlyActive = true)
    {
        $model = new \App\Models\CategoryModel();
        return $model->getCategoryParentTree($categoryId, $onlyActive);
    }
}

//get dropdown category id
if (!function_exists('getDropdownCategoryId')) {
    function getDropdownCategoryId()
    {
        $categoryId = 0;
        $categoryIds = inputPost('category_id');
        if (!empty($categoryIds)) {
            $categoryIds = array_reverse($categoryIds);
            foreach ($categoryIds as $id) {
                if (!empty($id)) {
                    $categoryId = $id;
                    break;
                }
            }
        }
        return $categoryId;
    }
}

//render index category products
if (!function_exists('renderIndexCategoryProducts')) {
    function renderIndexCategoryProducts($categories, $langId)
    {
        $model = new \App\Models\ProductModel();
        return $model->renderIndexCategoryProducts($categories, $langId);
    }
}

//get product
if (!function_exists('getProduct')) {
    function getProduct($id)
    {
        $model = new \App\Models\ProductModel();
        return $model->getProduct($id);
    }
}

//get active product
if (!function_exists('getActiveProduct')) {
    function getActiveProduct($id)
    {
        $model = new \App\Models\ProductModel();
        return $model->getActiveProduct($id);
    }
}

//get downloadable product
if (!function_exists('getDownloadableProduct')) {
    function getDownloadableProduct($id)
    {
        $model = new \App\Models\ProductModel();
        return $model->getDownloadableProduct($id);
    }
}

//get product details
if (!function_exists('getProductDetails')) {
    function getProductDetails($id, $langId, $getMainOnNull = true)
    {
        $model = new \App\Models\ProductModel();
        return $model->getProductDetails($id, $langId, $getMainOnNull);
    }
}

//is product in wishlist
if (!function_exists('isProductInWishlist')) {
    function isProductInWishlist($product)
    {
        if (authCheck()) {
            if (!empty($product->is_in_wishlist)) {
                return true;
            }
        } else {
            $session = \Config\Services::session();
            $wishlist = $session->get('mds_guest_wishlist');
            if (!empty($wishlist)) {
                if (in_array($product->id, $wishlist)) {
                    return true;
                }
            }
        }
        return false;
    }
}

//get product main image
if (!function_exists('getProductMainImage')) {
    function getProductMainImage($productId, $sizeName)
    {
        $model = new \App\Models\FileModel();
        $image = $model->getProductMainImage($productId);

        if (empty($image)) {
            return base_url('assets/img/no-image.jpg');
        }

        $path = 'uploads/images/' . $image->$sizeName;
        return getStorageFileUrl($path, $image->storage);
    }
}

//get product item image
if (!function_exists('getProductItemImage')) {
    function getProductItemImage($product, $getSecond = false)
    {
        $defaultImage = base_url('assets/img/no-image.jpg');

        if (empty($product) || empty($product->image_cache)) {
            return $defaultImage;
        }

        $images = json_decode($product->image_cache, true);

        $index = $getSecond ? 1 : 0;

        if ($getSecond && empty($images[1]['image'])) {
            return '';
        }

        if (!isset($images[$index]['storage'], $images[$index]['image'])) {
            return $defaultImage;
        }

        $storage = $images[$index]['storage'];
        $filename = $images[$index]['image'];
        $path = 'uploads/images/' . $filename;

        return getStorageFileUrl($path, $storage);
    }
}

//get product image url
if (!function_exists('getProductImageURL')) {
    function getProductImageURL($image, $sizeName)
    {
        if (empty($image) || empty($sizeName)) {
            return '';
        }

        $path = 'uploads/images/' . $image->$sizeName;
        return getStorageFileUrl($path, $image->storage);
    }
}

//get order image
if (!function_exists('getOrderImageUrl')) {
    function getOrderImageUrl($imageData, $productId)
    {
        $imageUrl = '';
        if (!empty($imageData)) {
            $image = safeJsonDecode($imageData);
            if (!empty($image->path) && !empty($image->storage)) {
                $path = 'uploads/images/' . $image->path;
                $imageUrl = getStorageFileUrl($path, $image->storage);
            }
        }

        if (empty($imageUrl)) {
            $imageUrl = getProductMainImage($productId, 'image_small');
        }

        return $imageUrl;
    }
}

//get order sku
if (!function_exists('getOrderSku')) {
    function getOrderSku($orderProduct)
    {
        if (empty($orderProduct)) {
            return '';
        }

        if (!empty($orderProduct->product_sku)) {
            return $orderProduct->product_sku;
        }

        $product = getProduct($orderProduct->product_id);
        if (!empty($product)) {
            return $product->sku;
        }
        return '';
    }
}

//get product images
if (!function_exists('getProductImages')) {
    function getProductImages($productId)
    {
        $model = new \App\Models\FileModel();
        return $model->getProductImages($productId);
    }
}

//get product listing type
if (!function_exists('getProductListingType')) {
    function getProductListingType($product)
    {
        if (!empty($product)) {
            if ($product->listing_type == 'sell_on_site') {
                return trans("add_product_for_sale");
            }
            if ($product->listing_type == 'ordinary_listing') {
                return trans("add_product_services_listing");
            }
        }
    }
}

//get product video url
if (!function_exists('getProductVideoUrl')) {
    function getProductVideoUrl($video)
    {
        if (empty($video)) {
            return '';
        }

        $path = 'uploads/videos/' . $video->file_name;
        return getStorageFileUrl($path, $video->storage);
    }
}

//get product audio url
if (!function_exists('getProductAudioUrl')) {
    function getProductAudioUrl($audio)
    {
        if (empty($audio)) {
            return '';
        }

        $path = 'uploads/audios/' . $audio->file_name;
        return getStorageFileUrl($path, $audio->storage);
    }
}

//check sell active
if (!function_exists('isSaleActive')) {
    function isSaleActive()
    {
        if (getContextValue('generalSettings')->marketplace_system == 1 || getContextValue('generalSettings')->bidding_system == 1) {
            return true;
        }
        return false;
    }
}

//get decimal separator
if (!function_exists('getDecimalSeparator')) {
    function getDecimalSeparator()
    {
        $decimalSeparator = '.';
        if (getContextValue('defaultCurrency')->currency_format == 'european') {
            $decimalSeparator = ',';
        }
        return $decimalSeparator;
    }
}

//price formatted
if (!function_exists('priceFormatted')) {
    function priceFormatted($price, $currencyCode, $convertCurrency = false)
    {
        //convert currency
        if (getContextValue('paymentSettings')->currency_converter == 1 && $convertCurrency == true) {
            $rate = 1;
            $selectedCurrency = getSelectedCurrency();
            if (isset($selectedCurrency) && isset($selectedCurrency->exchange_rate)) {
                $rate = $selectedCurrency->exchange_rate;
                $price = $price * $rate;
                $currencyCode = $selectedCurrency->code;
            }
        }
        $decPoint = '.';
        $thousandsSep = ',';
        if (!empty(getContextValue('currencies')[$currencyCode]) && getContextValue('currencies')[$currencyCode]->currency_format != 'us') {
            $decPoint = ',';
            $thousandsSep = '.';
        }
        if (!empty($price)) {
            if (filter_var($price, FILTER_VALIDATE_INT) !== false) {
                $price = number_format($price, 0, $decPoint, $thousandsSep);
            } else {
                $price = number_format($price, 2, $decPoint, $thousandsSep);
            }
        }
        return priceCurrencyFormat($price, $currencyCode);
    }
}

//price cart
if (!function_exists('priceDecimal')) {
    function priceDecimal($price, $currencyCode, $convertCurrency = false, $moneySign = true)
    {
        //convert currency
        if (getContextValue('paymentSettings')->currency_converter == 1 && $convertCurrency == true) {
            $rate = 1;
            $selectedCurrency = getSelectedCurrency();
            if (isset($selectedCurrency) && isset($selectedCurrency->exchange_rate)) {
                $rate = $selectedCurrency->exchange_rate;
                $price = $price * $rate;
                $currencyCode = $selectedCurrency->code;
            }
        }
        $decPoint = '.';
        $thousandsSep = ',';
        if (!empty(getContextValue('currencies')[$currencyCode]) && getContextValue('currencies')[$currencyCode]->currency_format != 'us') {
            $decPoint = ',';
            $thousandsSep = '.';
        }
        if (!empty($price)) {
            if (strpos($price, '.00') !== false) {
                $price = str_replace('.00', '', $price);
            }
            if (filter_var($price, FILTER_VALIDATE_INT) !== false) {
                $price = number_format($price, 0, $decPoint, $thousandsSep);
            } else {
                $price = number_format($price, 2, $decPoint, $thousandsSep);
            }
        }
        if ($moneySign == false) {
            return $price;
        }
        return priceCurrencyFormat($price, $currencyCode);
    }
}

//price currency format
if (!function_exists('priceCurrencyFormat')) {
    function priceCurrencyFormat($price, $currencyCode)
    {
        if (!empty(getContextValue('currencies')[$currencyCode])) {
            $currency = getContextValue('currencies')[$currencyCode];
            $space = '';
            if ($currency->space_money_symbol == 1) {
                $space = ' ';
            }
            if ($currency->currency_format == 'us') {
                if (strpos($price, '.00') !== false) {
                    $price = str_replace('.00', '', $price);
                }
            } else {
                if (strpos($price, ',00') !== false) {
                    $price = str_replace(',00', '', $price);
                }
            }
            if ($currency->symbol_direction == 'left') {
                $price = $currency->symbol . $space . $price;
            } else {
                $price = $price . $space . $currency->symbol;
            }
        }
        return $price;
    }
}

//convert currency for payments in the cart
if (!function_exists('convertCurrencyByExchangeRate')) {
    function convertCurrencyByExchangeRate($amount, $exchangeRate)
    {
        if ($amount <= 0) {
            return 0;
        }
        if (empty($exchangeRate)) {
            $exchangeRate = 1;
        }
        if (getContextValue('paymentSettings')->currency_converter == 1) {
            $amount = $amount * $exchangeRate;
            if (!empty($amount)) {
                if (filter_var($amount, FILTER_VALIDATE_INT) !== false) {
                    $amount = number_format($amount, 0, '.', '');
                } else {
                    $amount = number_format($amount, 2, '.', '');
                }
            }
        }
        return $amount;
    }
}

//convert amount to default currency
if (!function_exists('convertToDefaultCurrency')) {
    function convertToDefaultCurrency($decimalAmount, $currencyCode, $round = true)
    {
        if (getContextValue('paymentSettings')->default_currency == $currencyCode) {
            return $decimalAmount;
        }
        $amount = $decimalAmount;
        if (!empty($decimalAmount) && !empty(getContextValue('currencies')[$currencyCode])) {
            $currency = getContextValue('currencies')[$currencyCode];
            if (!empty($currency->exchange_rate) && $currency->exchange_rate > 0) {
                $amount = $decimalAmount / $currency->exchange_rate;
                if ($round == true) {
                    $amount = number_format($amount, 2, '.', '');
                }
            }
        }
        return $amount;
    }
}

//get unread chats count
if (!function_exists('getUnreadChatsCount')) {
    function getUnreadChatsCount($receiverId)
    {
        $model = new \App\Models\ChatModel();
        return $model->getUnreadChatsCount($receiverId);
    }
}

//format product options for products in cart
if (!function_exists('formatCartOptionsSummary')) {
    function formatCartOptionsSummary(?string $snapshotJson, string $languageCode = 'en', bool $includeOptionName = true, string $separator = ' / '): string
    {
        if (empty($snapshotJson)) {
            return '';
        }

        $snapshotData = json_decode($snapshotJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($snapshotData['options']) || !is_array($snapshotData['options'])) {
            return '';
        }

        // Group values by option name first to handle checkboxes correctly.
        $groupedOptions = [];
        foreach ($snapshotData['options'] as $option) {
            $valuePart = null;
            if (isset($option['valueNames']) && is_array($option['valueNames'])) {
                $valuePart = $option['valueNames'][$languageCode] ?? reset($option['valueNames']) ?: null;
            } elseif (isset($option['userInput'])) {
                $userInput = trim($option['userInput']);
                if ($userInput !== '') {
                    $valuePart = $userInput;
                }
            }

            if ($valuePart) {
                $optionKey = json_encode($option['optionNames']);
                $groupedOptions[$optionKey][] = $valuePart;
            }
        }

        // Now, build the summary from the grouped data.
        $summaryParts = [];
        foreach ($groupedOptions as $optionKeyJson => $values) {
            $combinedValues = implode(', ', array_map('htmlspecialchars', $values));

            if ($includeOptionName) {
                $optionNames = json_decode($optionKeyJson, true);
                $optionName = $optionNames[$languageCode] ?? reset($optionNames) ?: null;

                if ($optionName) {
                    // Wrap the formatted string in a div for CSS styling.
                    $summaryParts[] = sprintf('<div class="option-item display-inline-block m-r-5"><strong>%s:</strong> %s</div>', htmlspecialchars($optionName), $combinedValues);
                } else {
                    // Fallback for values without an option name
                    $summaryParts[] = sprintf('<div class="option-item display-inline-block m-r-5">%s</div>', $combinedValues);
                }
            } else {
                // Original behavior: just the values, no HTML wrapping.
                $summaryParts[] = $combinedValues;
            }
        }

        // If we wrapped items in divs, join them without a separator.
        // Otherwise, use the specified separator.
        $finalSeparator = $includeOptionName ? '' : $separator;

        return implode($finalSeparator, $summaryParts);
    }
}

//get blog image url
if (!function_exists('getBlogImageURL')) {
    function getBlogImageURL($post, $sizeName)
    {
        if (empty($post) || empty($sizeName)) {
            return '';
        }

        $path = $post->$sizeName;
        return getStorageFileUrl($path, $post->storage);
    }
}

//get file manager image
if (!function_exists('getFileManagerImageUrl')) {
    function getFileManagerImageUrl($image)
    {
        if (empty($image)) {
            return '';
        }

        $path = 'uploads/images-file-manager/' . $image->image_path;
        return getStorageFileUrl($path, $image->storage, 'no-image');
    }
}

//get blog content image
if (!function_exists('getBlogFileManagerImage')) {
    function getBlogFileManagerImage($image)
    {
        if (empty($image)) {
            return '';
        }

        $path = $image->image_path;
        return getStorageFileUrl($path, $image->storage, 'no-image');
    }
}

//get new quote requests count
if (!function_exists('getNewQuoteRequestsCount')) {
    function getNewQuoteRequestsCount($userId)
    {
        $model = new \App\Models\BiddingModel();
        return $model->getNewQuoteRequestsCount($userId);
    }
}

//get seller active refund requests count
if (!function_exists('getSellerActiveRefundRequestCount')) {
    function getSellerActiveRefundRequestCount($userId)
    {
        $model = new \App\Models\OrderModel();
        return $model->getSellerActiveRefundRequestCount($userId);
    }
}

//get coupon products array
if (!function_exists('getCouponProductsArray')) {
    function getCouponProductsArray($order)
    {
        if (!empty($order) && !empty($order->coupon_products)) {
            return explode(',', $order->coupon_products);
        }
        return array();
    }
}

//get seller final price
if (!function_exists('getSellerFinalPrice')) {
    function getSellerFinalPrice($orderId)
    {
        $model = new \App\Models\OrderModel();
        return $model->getSellerFinalPrice($orderId);
    }
}

//check if order has shipped product
if (!function_exists('isThereShippedProductOrder')) {
    function isThereShippedProductOrder($orderId)
    {
        $status = false;
        $orderProducts = getOrderItems($orderId);
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct->order_status == 'shipped') {
                    $status = true;
                }
            }
        }
        return $status;
    }
}

//get new quote requests count
if (!function_exists('getProductDigitalFile')) {
    function getProductDigitalFile($productId)
    {
        $model = new \App\Models\FileModel();
        return $model->getProductDigitalFile($productId);
    }
}

//generate filter string
if (!function_exists('generateFilterString')) {
    function generateFilterString($key, $arrayValues)
    {
        if (!is_string($key) || empty($key)) {
            return '';
        }

        if (!is_array($arrayValues) || empty($arrayValues)) {
            return '';
        }

        //remove invalid entries and encode values
        $filteredValues = array_filter($arrayValues, function ($val) {
            return is_scalar($val) && $val !== '';
        });

        if (empty($filteredValues)) {
            return '';
        }

        $encodedValues = array_map('urlencode', $filteredValues);
        return urlencode($key) . '=' . implode(',', $encodedValues);
    }
}

//get query string array to array of objects
if (!function_exists('convertQueryStringToObjectArray')) {
    function convertQueryStringToObjectArray($queryStringArray)
    {
        $result = [];
        if (!is_array($queryStringArray) || empty($queryStringArray)) {
            return $result;
        }

        foreach ($queryStringArray as $key => $values) {
            if (!is_array($values) || empty($values)) {
                continue;
            }
            foreach ($values as $value) {
                if (!is_scalar($value) || $value === '') {
                    continue;
                }
                $obj = new stdClass();
                $obj->key = $key;
                $obj->value = $value;
                $result[] = $obj;
            }
        }

        return $result;
    }
}

//is product filter option selected
if (!function_exists('isFilterOptionSelected')) {
    function isFilterOptionSelected($key, $value)
    {
        $request = \Config\Services::request();

        $urlValuesString = $request->getGet($key);

        if (is_null($urlValuesString)) {
            return false;
        }

        $selectedValuesArray = explode(',', $urlValuesString);

        return in_array((string)$value, $selectedValuesArray, true);
    }
}

//get product filter id by key
if (!function_exists('getProductFilterIdByKey')) {
    function getProductFilterIdByKey($customFilters, $key)
    {
        if (!empty($customFilters)) {
            foreach ($customFilters as $item) {
                if ($item->product_filter_key == $key) {
                    return $item->id;
                    break;
                }
            }
        }
        return false;
    }
}

//get seller products count
if (!function_exists('getSellerTotalProductsCount')) {
    function getSellerTotalProductsCount($userId)
    {
        $model = new \App\Models\ProductModel();
        return $model->getSellerTotalProductsCount($userId);
    }
}

//get product wishlist count
if (!function_exists('getUserWishlistProductsCount')) {
    function getUserWishlistProductsCount($userId)
    {
        $model = new \App\Models\ProductModel();
        return $model->getUserWishlistProductsCount($userId);
    }
}

//calculate discount
if (!function_exists('calculateDiscount')) {
    function calculateDiscount($originalPrice, $discountedPrice): int
    {
        if ($originalPrice <= 0 || $discountedPrice <= 0 || $discountedPrice >= $originalPrice) {
            return 0;
        }

        $discount = $originalPrice - $discountedPrice;
        return round(($discount * 100) / $originalPrice);
    }
}

//discount rate format
if (!function_exists('discountRateFormat')) {
    function discountRateFormat($discountRate)
    {
        return $discountRate . '%';
    }
}

//get product stock
if (!function_exists('getProductStock')) {
    function getProductStock(?object $product, ?object $variant = null): int
    {
        if (empty($product) || !is_object($product)) {
            return 0;
        }

        if (isset($product->product_type) && $product->product_type === 'digital') {
            return 1;
        }

        if (!empty($variant) && is_object($variant) && isset($variant->quantity)) {
            return max(0, (int)$variant->quantity);
        }

        if (isset($product->stock)) {
            return max(0, (int)$product->stock);
        }

        return 0;
    }
}

//get product variant by hash
if (!function_exists('getVariantByHash')) {
    function getVariantByHash($hash)
    {
        $model = new \App\Models\ProductOptionsModel();
        return $model->getVariantByHash($hash);
    }
}

//get product stock status
if (!function_exists('getProductStockStatus')) {
    function getProductStockStatus($product, $addHTML = true)
    {
        if (!empty($product)) {
            if ($product->product_type == 'digital') {
                if ($addHTML == false) {
                    return trans("in_stock");
                }
                return '<span class="text-success">' . trans("in_stock") . '</span>';
            } elseif ($product->listing_type == 'ordinary_listing') {
                if ($product->is_sold == 1) {
                    if ($addHTML == false) {
                        return trans("sold");
                    }
                    return '<span class="text-danger">' . trans("sold") . '</span>';
                } else {
                    if ($addHTML == false) {
                        return trans("active");
                    }
                    return '<span class="text-success">' . trans("active") . '</span>';
                }
            } else {
                if ($product->stock < 1) {
                    if ($addHTML == false) {
                        return trans("out_of_stock");
                    }
                    return '<span class="text-danger">' . trans("out_of_stock") . '</span>';
                } else {
                    if ($addHTML == false) {
                        return trans("in_stock") . ' (' . $product->stock . ')';
                    }
                    return '<span class="text-success">' . trans("in_stock") . ' (' . $product->stock . ')' . '</span>';
                }
            }
        }
        return '';
    }
}

//Calculates the minimum required height for the product options container
if (!function_exists('getProductVariationOptions')) {
    function calculateOptionsContainerHeight(?array $productOptionsData): int
    {
        $minHeight = 0;
        if (empty($productOptionsData['options']) || !is_array($productOptionsData['options'])) {
            return 0;
        }

        foreach ($productOptionsData['options'] as $option) {
            if ($option['is_enabled'] != 1) {
                continue;
            }
            switch ($option['type']) {
                case 'swatch-image':
                    $minHeight += 100;
                    break;
                case 'radio':
                    $minHeight += 90;
                    break;
                case 'dropdown':
                    $minHeight += 80;
                    break;
                case 'swatch-color':
                    $minHeight += 80;
                    break;
                case 'checkbox':
                    $minHeight += 40 + (count($option['values']) * 30);
                    break;
                case 'text':
                case 'number':
                    $minHeight += 80;
                    break;
                default:
                    $minHeight += 80;
                    break;
            }
        }

        return $minHeight;
    }
}

//get custom field optiom name
if (!function_exists('getCustomFieldOptionName')) {
    function getCustomFieldOptionName($optionNameData, $langId)
    {
        $optionName = null;
        $optionDefaultName = '';
        $defaultLangId = getContextValue('activeLang')->id;
        if (!empty($optionNameData)) {
            $nameArray = unserializeData($optionNameData);
            if (!empty($nameArray) && countItems($nameArray) > 0) {
                foreach ($nameArray as $item) {
                    if (isset($item['lang_id']) && isset($item['name'])) {
                        if ($item['lang_id'] == $langId) {
                            $optionName = $item['name'];
                        }
                        if ($item['lang_id'] == $defaultLangId) {
                            $optionDefaultName = $item['name'];
                        }
                    }
                }
            }
        }
        if (!isset($optionName)) {
            $optionName = $optionDefaultName;
        }

        return $optionName;
    }
}

//get custom field options
if (!function_exists('getCustomFieldOptions')) {
    function getCustomFieldOptions($customField, $langId)
    {
        $model = new \App\Models\FieldModel();
        return $model->getFieldOptions($customField, $langId);
    }
}

//get selected custom field values for product
if (!function_exists('getSelectedCustomFieldValuesForProduct')) {
    function getSelectedCustomFieldValuesForProduct($fieldId, $productId, $langId)
    {
        $model = new \App\Models\FieldModel();
        return $model->getSelectedCustomFieldValuesForProduct($fieldId, $productId, $langId);
    }
}

//get selected custom field values for product
if (!function_exists('getProductCustomFieldInputValue')) {
    function getProductCustomFieldInputValue($fieldId, $productId)
    {
        $model = new \App\Models\FieldModel();
        return $model->getProductCustomFieldInputValue($fieldId, $productId);
    }
}

//get active payout options
if (!function_exists('getActivePayoutOptions')) {
    function getActivePayoutOptions()
    {
        $options = [];
        $settings = getContextValue('paymentSettings');

        if ($settings->payout_paypal_enabled == 1) {
            $options[] = 'paypal';
        }
        if ($settings->payout_bitcoin_enabled == 1) {
            $options[] = 'bitcoin';
        }
        if ($settings->payout_iban_enabled == 1) {
            $options[] = 'iban';
        }
        if ($settings->payout_swift_enabled == 1) {
            $options[] = 'swift';
        }

        return $options;
    }
}

//get product tags string
if (!function_exists('getProductTagsString')) {
    function getProductTagsString($productId, $langId)
    {
        $model = new \App\Models\TagModel();
        return $model->getProductTagsString($productId, $langId);
    }
}

//set product as edited
if (!function_exists('setProductAsEdited')) {
    function setProductAsEdited($productId)
    {
        $model = new \App\Models\ProductAdminModel();
        $model->setProductAsEdited($productId);
    }
}
