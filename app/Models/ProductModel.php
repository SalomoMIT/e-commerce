<?php namespace App\Models;

class ProductModel extends BaseModel
{
    protected $builder;
    protected $builderProductDetails;
    protected $builderTags;
    protected $builderProductLicenseKeys;
    protected $builderCustomFieldsProduct;
    protected $builderDigitalSales;
    protected $builderWishlist;
    protected $builderUsers;
    protected $builderSearchIndexes;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('products');
        $this->builderProductDetails = $this->db->table('product_details');
        $this->builderTags = $this->db->table('product_tags');
        $this->builderProductLicenseKeys = $this->db->table('product_license_keys');
        $this->builderCustomFieldsProduct = $this->db->table('custom_fields_product');
        $this->builderDigitalSales = $this->db->table('digital_sales');
        $this->builderWishlist = $this->db->table('wishlist');
        $this->builderUsers = $this->db->table('users');
        $this->builderSearchIndexes = $this->db->table('product_search_indexes');
    }

    //add product
    public function addProduct()
    {
        $data = [
            'slug' => strSlug(inputPost('title_' . defaultLangId())),
            'product_type' => inputPost('product_type'),
            'listing_type' => inputPost('listing_type'),
            'sku' => '',
            'price' => 0,
            'price_discounted' => 0,
            'currency' => '',
            'discount_rate' => 0,
            'vat_rate' => 0,
            'user_id' => activeUserId(),
            'status' => 0,
            'is_promoted' => 0,
            'promote_start_date' => null,
            'promote_end_date' => null,
            'promote_plan' => 'none',
            'promote_day' => 0,
            'visibility' => 1,
            'rating' => 0,
            'pageviews' => 0,
            'demo_url' => '',
            'external_link' => '',
            'files_included' => '',
            'stock' => 1,
            'shipping_delivery_time_id' => 0,
            'multiple_sale' => 1,
            'digital_file_download_link' => '',
            'is_deleted' => 0,
            'is_draft' => 1,
            'is_free_product' => 0,
            'country_id' => 0,
            'state_id' => 0,
            'city_id' => 0,
            'address' => '',
            'zip_code' => '',
            'is_active' => 0,
            'updated_at' => null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (empty($data['sku'])) {
            $data['sku'] = '';
        }
        if (!empty($data['slug'])) {
            $data['slug'] = substr($data['slug'], 0, 200);
        }
        if (empty($data['multiple_sale'])) {
            $data['multiple_sale'] = 0;
        }
        //set category id
        $data['category_id'] = getDropdownCategoryId();
        if ($this->builder->insert($data)) {
            return $this->db->insertID();
        }
        return false;
    }

    //add product title and desc
    public function addProductTitleDesc($productId)
    {
        $mainTitle = inputPost('title_' . defaultLangId());
        $mainTitle = trim($mainTitle ?? '');
        foreach ($this->activeLanguages as $language) {
            $title = inputPost('title_' . $language->id);
            $title = trim($title ?? '');
            if (!empty($title)) {
                $data = [
                    'product_id' => $productId,
                    'lang_id' => $language->id,
                    'title' => !empty($title) ? $title : $mainTitle,
                    'description' => inputPost('description_' . $language->id),
                    'short_description' => inputPost('short_description_' . $language->id)
                ];
                $this->builderProductDetails->insert($data);
            }
            //save product tags
            $tagModel = new TagModel();
            $tagModel->saveProductTags($productId, $language->id);
        }
    }

    //edit product title and desc
    public function editProductTitleDesc($productId)
    {
        $mainTitle = inputPost('title_' . defaultLangId());
        $mainTitle = trim($mainTitle ?? '');
        foreach ($this->activeLanguages as $language) {
            $title = inputPost('title_' . $language->id);
            $title = trim($title ?? '');
            $data = [
                'product_id' => $productId,
                'lang_id' => $language->id,
                'title' => !empty($title) ? $title : $mainTitle,
                'description' => inputPost('description_' . $language->id),
                'short_description' => inputPost('short_description_' . $language->id)
            ];
            $row = getProductDetails($productId, $language->id, false);
            if (empty($row)) {
                $this->builderProductDetails->insert($data);
            } else {
                $this->builderProductDetails->where('product_id', clrNum($productId))->where('lang_id', $language->id)->update($data);
            }
            //save product tags
            $tagModel = new TagModel();
            $tagModel->saveProductTags($productId, $language->id);
        }
    }

    //edit product details
    public function editProductDetails($id)
    {
        $product = $this->getProduct($id);
        if (empty($product)) {
            return false;
        }

        $seller = getUser($product->user_id);
        if (empty($seller)) {
            return false;
        }

        $price = numToDecimal(inputPost('price') ?? 0);
        $priceDiscounted = numToDecimal(inputPost('price_discounted') ?? 0);

        if (empty($priceDiscounted) || $priceDiscounted > $price) {
            $priceDiscounted = $price;
        }

        $discountRate = 0;
        if ($price != 0 && $priceDiscounted < $price) {
            $discountRate = @intval((($price - $priceDiscounted) * 100) / $price);
            if (empty($discountRate)) {
                $discountRate = 0;
            }
        }

        if (!empty(inputPost('checkbox_has_discount'))) {
            $priceDiscounted = $price;
            $discountRate = 0;
        }

        $data = [
            'sku' => inputPost('sku'),
            'price' => $price,
            'price_discounted' => $priceDiscounted,
            'discount_rate' => $discountRate,
            'currency' => inputPost('currency'),
            'vat_rate' => !empty(inputPost('vat_rate')) ? inputPost('vat_rate') : 0,
            'demo_url' => !empty(inputPost('demo_url')) ? inputPost('demo_url') : '',
            'external_link' => !empty(inputPost('external_link')) ? inputPost('external_link') : '',
            'files_included' => inputPost('files_included'),
            'stock' => !empty(inputPost('stock')) ? inputPost('stock') : 0,
            'multiple_sale' => !empty(inputPost('multiple_sale')) ? 1 : 0,
            'digital_file_download_link' => inputPost('digital_file_download_link'),
            'is_free_product' => !empty(inputPost('is_free_product')) ? 1 : 0,
            'is_draft' => 0,
            'country_id' => !empty(inputPost('country_id')) ? inputPost('country_id') : 0,
            'state_id' => !empty(inputPost('state_id')) ? inputPost('state_id') : 0,
            'city_id' => !empty(inputPost('city_id')) ? inputPost('city_id') : 0,
            'province_id' => !empty(inputPost('province_id')) ? inputPost('province_id') : 0,
            'province_name' => !empty(inputPost('province_name')) ? inputPost('province_name') : '',
            'city_id_modif' => !empty(inputPost('city_id_modif')) ? inputPost('city_id_modif') : 0,
            'city_name_modif' => !empty(inputPost('city_name_modif')) ? inputPost('city_name_modif') : '',
            'district_id' => !empty(inputPost('district_id')) ? inputPost('district_id') : 0,
            'district_name' => !empty(inputPost('district_name')) ? inputPost('district_name') : '',
            'address' => !empty(inputPost('address')) ? inputPost('address') : '',
            'zip_code' => !empty(inputPost('zip_code')) ? inputPost('zip_code') : '',
            'shipping_delivery_time_id' => !empty(inputPost('shipping_delivery_time_id')) ? inputPost('shipping_delivery_time_id') : 0
        ];
        
        if (empty($data['currency'])) {
            $data['currency'] = $this->paymentSettings->default_currency;
        }

        if (inputPost('brand_id')) {
            $data['brand_id'] = !empty(inputPost('brand_id')) ? inputPost('brand_id') : 0;
        }

        //set location by seller location
        $data['country_id'] = empty($data['country_id']) ? $seller->country_id : $data['country_id'];
        $data['state_id'] = empty($data['state_id']) ? $seller->state_id : $data['state_id'];
        $data['city_id'] = empty($data['city_id']) ? $seller->city_id : $data['city_id'];

        //shipping
        $shippingModel = new ShippingModel();
        $shippingDimensions = [
            'weight' => inputPost('product_weight'),
            'length' => inputPost('product_length'),
            'width' => inputPost('product_width'),
            'height' => inputPost('product_height')
        ];
        $hasValue = false;
        foreach ($shippingDimensions as $value) {
            if ($value !== null && $value !== '') {
                $hasValue = true;
                break;
            }
        }
        $data['shipping_dimensions'] = $hasValue ? json_encode($shippingDimensions) : null;
        $data['chargeable_weight'] = $shippingModel->calculateChargeableWeight($shippingDimensions);

        //validate sku
        $isSkuValid = true;
        if (!empty($data['sku'])) {
            $row = $this->builder->where('sku', removeSpecialCharacters($data['sku']))->where('id != ', clrNum($id))->where('user_id', clrNum(activeUserId()))->get()->getRow();
            if (!empty($row)) {
                $isSkuValid = false;
                $data['sku'] = '';
            }
        }

        if ($data['stock'] < 0) {
            $data['stock'] = 0;
        }
        if (inputPost('submit') == 'save_as_draft') {
            $data['is_draft'] = 1;
            $data['is_active'] = 0;
        } else {
            if ($this->generalSettings->approve_before_publishing == 0 || hasPermission('products')) {
                $data['status'] = 1;
                $data['is_active'] = 1;
            }
        }
        if ($this->builder->where('id', $product->id)->update($data)) {

            //edit default option variant
            $variant = $this->db->table('product_option_variants')->where('product_id', $product->id)->where('is_active', 1)->orderBy('is_default DESC, id')->limit(1)->get()->getRow();
            if (!empty($variant)) {
                $this->db->table('product_option_variants')->where('id', $variant->id)->update([
                    'price' => $data['price'],
                    'price_discounted' => $data['price_discounted'],
                    'quantity' => $data['stock'],
                    'weight' => $data['chargeable_weight']
                ]);
            }

            if ($isSkuValid == false) {
                setErrorMessage(trans("msg_error_sku"));
                return redirect()->back();
            }
            return true;
        }
        return false;
    }

    //edit product
    public function editProduct($product, $slug)
    {
        if (!empty($product)) {
            $data = [
                'product_type' => inputPost('product_type'),
                'listing_type' => inputPost('listing_type'),
                'slug' => $slug
            ];
            $data['category_id'] = getDropdownCategoryId();
            $data['is_sold'] = $product->is_sold;
            $data['visibility'] = $product->visibility;
            if ($product->is_draft != 1 && $product->status == 1) {
                $data['is_sold'] = inputPost('is_sold');
                $data['visibility'] = inputPost('visibility');
            }
            if (empty($data['visibility'])) {
                $data['is_active'] = 0;
            } else {
                if ($product->status == 1 && $product->is_deleted != 1 && $product->is_draft != 1) {
                    $data['is_active'] = 1;
                }
            }
            if (!empty($data['slug'])) {
                $data['slug'] = str_replace(' ', '-', $data['slug'] ?? '');
                $data['slug'] = removeSpecialCharacters($data['slug']);
                $data['slug'] = substr($data['slug'], 0, 200);
            }
            if ($data['is_sold'] == 1) {
                $data['stock'] = 0;
            }

            //set commission
            if (isAdmin() || hasPermission('products')) {
                $data = setCommissionFormValues($data);
            }

            return $this->builder->where('id', $product->id)->update($data);
        }
    }

    //update custom fields
    public function updateProductCustomFields($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            $fieldModel = new FieldModel();
            $customFields = $fieldModel->getCustomFieldsByCategory($product->category_id);
            if (!empty($customFields)) {
                $fieldModel->deleteFieldProductValuesByProductId($productId);
                foreach ($customFields as $customField) {
                    $inputValue = inputPost('field_' . $customField->id);
                    if (!empty($inputValue)) {
                        if ($customField->field_type == 'multi_select') {
                            foreach ($inputValue as $key => $value) {
                                $data = [
                                    'field_id' => $customField->id,
                                    'product_id' => $productId,
                                    'product_filter_key' => $customField->product_filter_key
                                ];
                                $data['field_value'] = '';
                                $data['selected_option_id'] = $value;
                                $this->db->table('custom_fields_product')->insert($data);
                            }
                        } else {
                            $data = [
                                'field_id' => $customField->id,
                                'product_id' => clrNum($productId),
                                'product_filter_key' => $customField->product_filter_key,
                            ];
                            if ($customField->field_type == 'single_select') {
                                $data['field_value'] = '';
                                $data['selected_option_id'] = $inputValue;
                            } else {
                                $data['field_value'] = $inputValue;
                                $data['selected_option_id'] = 0;
                            }
                            $this->db->table('custom_fields_product')->insert($data);
                        }
                    }
                }
            }
        }
    }

    //update slug
    public function updateSlug($id)
    {
        $product = $this->getProduct($id);
        if (!empty($product)) {
            if (empty($product->slug) || $product->slug == '-') {
                $data = ['slug' => $product->id];
            } else {
                if ($this->generalSettings->product_link_structure == 'id-slug') {
                    $data = ['slug' => $product->id . '-' . $product->slug];
                } else {
                    $data = ['slug' => $product->slug . '-' . $product->id];
                }
            }
            $pageModel = new PageModel();
            if (!empty($pageModel->checkPageSlugForProduct($data['slug']))) {
                $data['slug'] .= uniqid();
            }
            return $this->builder->where('id', $product->id)->update($data);
        }
    }

    //set base query
    public function setBaseQuery($onlyActiveSeller = false, $isForCount = false)
    {
        $this->builder->resetQuery();

        if (!$isForCount) {
            $langId = $this->activeLang->id;
            $defLangId = $this->defaultLang->id;

            $this->builder->select("products.*, users.username AS user_username, users.role_id AS role_id, users.slug AS user_slug")
                ->select("(SELECT COUNT(wishlist.id) FROM wishlist WHERE products.id = wishlist.product_id) AS wishlist_count")
                ->select("(SELECT id FROM product_options WHERE products.id = product_options.product_id LIMIT 1) IS NOT NULL AS has_options");

            if (authCheck()) {
                $this->builder->select("(SELECT COUNT(wishlist.id) FROM wishlist WHERE wishlist.product_id = products.id AND wishlist.user_id = " . clrNum(user()->id) . ") AS is_in_wishlist");
            } else {
                $this->builder->select("0 AS is_in_wishlist");
            }

            if ($langId != $defLangId) {
                $this->builder->select("COALESCE(pd_selected.title, pd_default.title) AS title")
                    ->join('product_details AS pd_selected', 'pd_selected.product_id = products.id AND pd_selected.lang_id = ' . $this->db->escape($langId), 'left')
                    ->join('product_details AS pd_default', 'pd_default.product_id = products.id AND pd_default.lang_id = ' . $this->db->escape($defLangId), 'left');
            } else {
                $this->builder->select("pd.title")
                    ->join('product_details AS pd', 'pd.product_id = products.id AND pd.lang_id = ' . $this->db->escape($defLangId), 'left');
            }
        }

        $userJoinConditions = [
            'users.id = products.user_id',
            'users.banned = 0'
        ];

        if ($onlyActiveSeller) {
            $userJoinConditions[] = 'users.vacation_mode = 0';
            if ($this->generalSettings->membership_plans_system == 1) {
                $userJoinConditions[] = 'users.is_membership_plan_expired = 0';
            }
        }

        $this->builder->join('users', implode(' AND ', $userJoinConditions));
    }

    //build sql query string
    public function buildQuery($isPreview = false)
    {
        $this->setBaseQuery(true);

        if (authCheck()) {
            $this->select("(SELECT COUNT(wishlist.id) FROM wishlist WHERE products.id = wishlist.product_id AND wishlist.user_id = " . clrNum(user()->id) . ") AS is_in_wishlist");
        } else {
            $this->select("0 AS is_in_wishlist");
        }

        if ($isPreview === false) {
            $this->where('products.is_active', 1);
        }

        if ($this->generalSettings->show_sold_products != 1) {
            $this->builder->where('products.is_sold', 0);
        }
        $defaultLocation = getContextValue('defaultLocation');
        if (!empty($defaultLocation->country_id)) {
            $this->builder->where('products.country_id', clrNum($defaultLocation->country_id));
        }
        if (!empty($defaultLocation->state_id)) {
            $this->builder->where('products.state_id', clrNum($defaultLocation->state_id));
        }
        if (!empty($defaultLocation->city_id)) {
            $this->builder->where('products.city_id', clrNum($defaultLocation->city_id));
        }
    }

    //load products
    public function loadProducts($objParams)
    {
        if ($objParams->arrayParams == null) {
            foreach ($_GET as $key => $value) {
                $objParams->arrayParams[$key] = $value;
            }
        }
        $perPage = $this->productSettings->pagination_per_page;
        $offset = ($objParams->pageNumber - 1) * $perPage;
        $langId = $objParams->langId;
        $search = '';
        $sort = '';
        $pMin = '';
        $pMax = '';
        $brand = '';
        $arrayFilterQueries = [];
        if (!empty($objParams->arrayParams) && countItems($objParams->arrayParams) > 0) {
            foreach ($objParams->arrayParams as $param => $value) {
                if (!empty($value)) {
                    if ($param == 'search') {
                        $search = removeSpecialCharacters($value);
                    } elseif ($param == 'sort') {
                        $sort = $value;
                    } elseif ($param == 'p_min') {
                        $pMin = numToDecimal($value);
                    } elseif ($param == 'p_max') {
                        $pMax = numToDecimal($value);
                    } elseif ($param == 'brand') {
                        $brand = $value;
                    } else {
                        if ($param != 'page' && $param != 'brand') {
                            if (!empty($objParams->customFilters)) {
                                foreach ($objParams->customFilters as $filter) {
                                    if ($filter->product_filter_key == $param) {
                                        $arrayValues = explode(',', $value);
                                        if (!empty($arrayValues) && countItems($arrayValues) > 0) {
                                            $arrayFilterQueries[] = $this->builderCustomFieldsProduct->join('custom_fields_options', 'custom_fields_options.id = custom_fields_product.selected_option_id')->select('product_id')
                                                ->where('custom_fields_product.field_id', $filter->id)->groupStart()->whereIn('custom_fields_options.option_key', $arrayValues)->groupEnd()->getCompiledSelect();
                                            $this->builderCustomFieldsProduct->resetQuery();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($this->selectedCurrency) && $this->selectedCurrency->id != $this->defaultCurrency->id) {
            if (!empty($pMin)) {
                $pMin = convertToDefaultCurrency($pMin, $this->selectedCurrency->code);
            }
            if (!empty($pMax)) {
                $pMax = convertToDefaultCurrency($pMax, $this->selectedCurrency->code);
            }
        }

        //set builder
        $this->buildQuery();

        // Filter products by category and its subcategories
        if (!empty($objParams->category)) {
            $categoryId = clrNum($objParams->category->id);
            $this->builder->join('category_paths AS cp', 'products.category_id = cp.descendant_id');
            $this->builder->where('cp.ancestor_id', $categoryId);
        }

        //filter by brand
        if (!empty($brand)) {
            $brandArray = explode(',', $brand);
            $brandArrayNew = array();
            if (!empty($brandArray) && is_array($brandArray)) {
                foreach ($brandArray as $item) {
                    $valInt = @intval($item);
                    if (!empty($valInt)) {
                        array_push($brandArrayNew, $valInt);
                    }
                }
            }
            if (!empty($brandArrayNew) && countItems($brandArrayNew) > 0) {
                $this->builder->whereIn('products.brand_id', $brandArrayNew, false);
            }
        }

        //filter by custom filters
        if (!empty($arrayFilterQueries)) {
            foreach ($arrayFilterQueries as $query) {
                $this->builder->where('products.id IN (' . $query . ')');
            }
        }

        //filter by price
        if (!empty($pMin) && $pMin >= 0) {
            $this->builder->where('price_discounted >=', $pMin);
        }
        if (!empty($pMax) && $pMax > 0) {
            $this->builder->where('price_discounted <=', $pMax);
        }

        //filter by vendor
        if (!empty($objParams->userId)) {
            $this->builder->where('products.user_id', clrNum($objParams->userId));
        }

        //filter by coupon
        if (!empty($objParams->couponId)) {
            $this->builder->where('products.id IN (SELECT product_id FROM coupon_products WHERE coupon_id = ' . clrNum($objParams->couponId) . ')');
        }

        //search
        $processedTerm = '';
        if (!empty($search)) {
            $processedTerm = $this->prepareSearchTerm($search);
            if (!empty($processedTerm)) {
                $this->builder->select('products.*, MATCH(psi.search_index) AGAINST(' . $this->db->escape($processedTerm) . ' IN BOOLEAN MODE) AS relevance', false)
                    ->join('product_search_indexes psi', 'psi.product_id = products.id AND psi.lang_id = ' . clrNum($langId))
                    ->where('MATCH(psi.search_index) AGAINST(' . $this->db->escape($processedTerm) . ' IN BOOLEAN MODE) >', 0, false);
            }
        }

        // sort products
        if (!empty($processedTerm)) {
            $this->builder->orderBy('relevance', 'DESC');

            if (empty($sort) && $this->productSettings->sort_by_featured_products == 1) {
                $this->builder->orderBy('products.is_promoted', 'DESC');
            }
        } else {
            // if no custom sort is selected and setting is enabled
            if (empty($sort) && $this->productSettings->sort_by_featured_products == 1) {
                $this->builder->orderBy('products.is_promoted', 'DESC');
            }

            // if no custom sort is selected
            if (empty($sort)) {
                $this->builder->orderBy('products.created_at', 'DESC');
            }
        }

        // if a custom sort is selected -> ignore "is_promoted"
        if (!empty($sort)) {
            if ($sort == 'most_recent') {
                $this->builder->orderBy('products.created_at', 'DESC');
            } elseif ($sort == 'lowest_price') {
                $this->builder->orderBy('price_discounted', 'ASC');
            } elseif ($sort == 'highest_price') {
                $this->builder->orderBy('price_discounted', 'DESC');
            } elseif ($sort == 'highest_rating') {
                $this->builder->orderBy('rating', 'DESC');
            }
        }

        return $this->builder->limit($perPage + 1, $offset)->get()->getResult();
    }

    //get seller products count
    public function getSellerTotalProductsCount($userId, $status = 'active')
    {
        $cacheKey = 'seller_product_count_' . $userId . '_' . ($status == 'active' ? 'active' : 'pending');
        return getCacheData($cacheKey, function () use ($userId, $status) {
            $this->setBaseQuery(false, true);
            if ($status == 'pending') {
                return $this->builder->where('users.id', clrNum($userId))->where('products.is_deleted', 0)->where('products.status', 0)->where('products.is_draft', 0)->countAllResults();
            } else {
                return $this->builder->where('users.id', clrNum($userId))->where('products.is_active', 1)->countAllResults();
            }
        }, 'product');
    }

    //get latest products
    public function getLatestProducts($langId, $limit)
    {
        $cacheKey = 'latest_products_lang_' . $langId . '_limit_' . $limit;
        return getCacheData($cacheKey, function () use ($limit) {
            $this->buildQuery();
            return $this->builder->orderBy('products.created_at DESC')->get(clrNum($limit))->getResult();
        }, 'product');
    }

    //get promoted products
    public function getPromotedProducts()
    {
        $this->buildQuery();
        return $this->builder->where('products.is_promoted', 1)->orderBy('products.promote_start_date', 'DESC')->get()->getResult();
    }

    //get promoted products limited
    public function getPromotedProductsLimited($langId, $perPage, $offset)
    {
        $cacheKey = 'promoted_products_lang_' . $langId . '_limit_' . $perPage . '_' . $offset;
        return getCacheData($cacheKey, function () use ($perPage, $offset) {
            $this->buildQuery();
            return $this->builder->where('products.is_promoted', 1)->orderBy('products.promote_start_date DESC')->limit(clrNum($perPage) + 1, clrNum($offset))->get()->getResult();
        }, 'product');
    }

    //check promoted products
    public function checkPromotedProducts()
    {
        $products = $this->builder->where('is_promoted', 1)->get()->getResult();
        if (!empty($products)) {
            foreach ($products as $item) {
                if (dateDifference($item->promote_end_date, date('Y-m-d H:i:s')) < 1) {
                    $this->builder->where('id', $item->id)->update(['is_promoted' => 0]);
                }
            }
        }
    }

    //get special offers
    public function getSpecialOffers($langId)
    {
        $cacheKey = 'special_offers_lang_' . $langId;
        return getCacheData($cacheKey, function () {
            $this->buildQuery();
            return $this->builder->where('products.is_special_offer', 1)->orderBy('products.special_offer_date', 'DESC')->limit(LIMIT_SPECIAL_OFFERS)->get()->getResult();
        }, 'product');
    }

    //render index category products
    public function renderIndexCategoryProducts($categories, $langId)
    {
        return getCacheData('index_category_products_' . $langId, function () use ($categories, $langId) {

            $productsArray = [];
            if (empty($categories)) {
                return '';
            }

            foreach ($categories as $category) {
                $categoryId = clrNum($category->id);

                $this->buildQuery();

                if ($category->show_subcategory_products == 1) {
                    $this->builder->whereIn('products.category_id', function ($query) use ($categoryId) {
                        $query->select('descendant_id')->from('category_paths')->where('ancestor_id', $categoryId);
                    });
                } else {
                    $this->builder->where('products.category_id', $categoryId);
                }

                $productsArray[$category->id] = $this->builder->orderBy('products.id', 'DESC')->get(NUM_INDEX_CATEGORY_PRODUCTS)->getResult();
            }


            $rawHtml = view("product/_index_category_products", ['indexCategories' => $categories, 'categoriesProductsArray' => $productsArray]);

            return minifyHtmlOutput($rawHtml);

        }, 'product');
    }

    //get related products
    public function getRelatedProducts($productId, $categoryId)
    {
        $this->buildQuery();
        return $this->builder->where('products.category_id', clrNum($categoryId))->where('products.id !=', clrNum($productId))->get(100)->getResult();
    }

    //get more products by user
    public function getMoreProductsByUser($userId, $productId)
    {
        $this->buildQuery();
        return $this->builder->where('users.id', clrNum($userId))->where('products.id != ', clrNum($productId))->orderBy('products.id DESC')->get(6)->getResult();
    }

    //get user wishlist products count
    public function getUserWishlistProductsCount($userId)
    {
        $this->buildQuery();
        $this->builder->join('wishlist', 'products.id = wishlist.product_id');
        return $this->builder->where('wishlist.user_id', clrNum($userId))->countAllResults();
    }

    //get user wishlist products
    public function getPaginatedUserWishlistProducts($userId, $perPage, $offset)
    {
        $this->buildQuery();
        $this->builder->join('wishlist', 'products.id = wishlist.product_id');
        return $this->builder->where('wishlist.user_id', clrNum($userId))->orderBy('products.id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get guest wishlist products count
    public function getGuestWishlistProductsCount()
    {
        $wishlist = helperGetSession('mds_guest_wishlist');
        if (!empty($wishlist) && countItems($wishlist) > 0) {
            $this->buildQuery();
            return $this->builder->whereIn('products.id', $wishlist, FALSE)->countAllResults();
        }
        return 0;
    }

    //get guest wishlist products
    public function getGuestWishlistProductsPaginated($perPage, $offset)
    {
        $wishlist = helperGetSession('mds_guest_wishlist');
        if (!empty($wishlist) && countItems($wishlist) > 0) {
            $this->buildQuery();
            return $this->builder->whereIn('products.id', $wishlist, FALSE)->orderBy('products.id DESC')->limit($perPage, $offset)->get()->getResult();
        }
        return array();
    }

    //get downloadable product
    public function getDownloadableProduct($id)
    {
        $this->setBaseQuery();
        return $this->builder->where('products.id', clrNum($id))->get()->getRow();
    }

    //get user downloads count
    public function getUserDownloadsCount($userId)
    {
        return $this->builderDigitalSales->where('buyer_id', clrNum($userId))->countAllResults();
    }

    //get paginated downloads
    public function getUserDownloadsPaginated($userId, $perPage, $offset)
    {
        return $this->builderDigitalSales->where('buyer_id', clrNum($userId))->orderBy('purchase_date DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get digital sale
    public function getDigitalSale($saleId)
    {
        return $this->builderDigitalSales->where('id', clrNum($saleId))->get()->getRow();
    }

    //get digital sale by buyer id
    public function getDigitalSaleByBuyerId($buyerId, $productId)
    {
        return $this->builderDigitalSales->where('buyer_id', clrNum($buyerId))->where('product_id', clrNum($productId))->get()->getRow();
    }

    //get digital sale by order id
    public function getDigitalSaleByOrderId($buyerId, $productId, $orderId)
    {
        return $this->builderDigitalSales->where('buyer_id', clrNum($buyerId))->where('product_id', clrNum($productId))->where('order_id', clrNum($orderId))->get()->getRow();
    }

    //get product by id
    public function getProduct($id)
    {
        return $this->builder->select("products.*, pd.title")
            ->join('product_details AS pd', 'pd.product_id = products.id AND pd.lang_id = ' . $this->db->escape($this->defaultLang->id), 'left')
            ->where('products.id', clrNum($id))->get()->getRow();
    }

    //get available product
    public function getActiveProduct($id)
    {
        $this->buildQuery();
        return $this->builder->where('products.id', clrNum($id))->get()->getRow();
    }

    //get product by slug
    public function getProductBySlug($slug)
    {
        $this->buildQuery(true);
        $row = $this->builder->where('products.slug', strSlug($slug))->get()->getRow();
        if (!empty($row) && $row->is_draft == 0 && $row->is_deleted == 0) {
            return $row;
        }
        return null;
    }

    //get product details
    public function getProductDetails($id, $langId, $getMainOnNull = true)
    {
        $row = $this->builderProductDetails->where('product_id', clrNum($id))->where('lang_id', clrNum($langId))->get()->getRow();
        if ((empty($row) || empty($row->title)) && $getMainOnNull == true) {
            $row = $this->builderProductDetails->where('product_id', clrNum($id))->limit(1)->get()->getRow();
        }
        return $row;
    }

    //is product in wishlist
    public function isProductInWishlist($productId)
    {
        if (authCheck()) {
            if (!empty($this->builderWishlist->where('user_id', user()->id)->where('product_id', clrNum($productId))->get()->getRow())) {
                return true;
            }
        } else {
            $wishlist = $this->session->get('mds_guest_wishlist');
            if (!empty($wishlist)) {
                if (in_array($productId, $wishlist)) {
                    return true;
                }
            }
        }
        return false;
    }

    //get product wishlist count
    public function getProductWishlistCount($productId)
    {
        return $this->builderWishlist->where('product_id', clrNum($productId))->countAllResults();
    }

    //add remove wishlist
    public function addRemoveWishlist($productId)
    {
        if (authCheck()) {
            if ($this->isProductInWishlist($productId)) {
                $this->builderWishlist->where('user_id', user()->id)->where('product_id', clrNum($productId))->delete();
            } else {
                $data = [
                    'user_id' => user()->id,
                    'product_id' => clrNum($productId)
                ];
                $this->builderWishlist->insert($data);
            }
        } else {
            if ($this->isProductInWishlist($productId)) {
                $wishlist = array();
                if (!empty(helperGetSession('mds_guest_wishlist'))) {
                    $wishlist = helperGetSession('mds_guest_wishlist');
                }
                $new = array();
                if (!empty($wishlist)) {
                    foreach ($wishlist as $item) {
                        if ($item != clrNum($productId)) {
                            array_push($new, $item);
                        }
                    }
                }
                helperSetSession('mds_guest_wishlist', $new);
            } else {
                $wishlist = array();
                if (!empty(helperGetSession('mds_guest_wishlist'))) {
                    $wishlist = helperGetSession('mds_guest_wishlist');
                }
                array_push($wishlist, clrNum($productId));
                helperSetSession('mds_guest_wishlist', $wishlist);
            }
        }
    }

    //increase product pageviews
    public function increaseProductPageviews($product)
    {
        if (!empty($product)) {
            if (empty(helperGetSession('pr_' . $product->id))) {
                helperSetSession('pr_' . $product->id, '1');
                $this->builder->where('id', $product->id)->update(['pageviews' => $product->pageviews + 1]);
            }
        }
    }

    //get rss products by category
    public function getRssProductsByCategory($categoryId, $langId)
    {
        $cacheKey = 'rss_products_category_' . $categoryId . '_lang_' . $langId;
        return getCacheData($cacheKey, function () use ($categoryId) {
            $categoryModel = new CategoryModel();
            $categoryIds = $categoryModel->getSubCategoriesTreeIds($categoryId, true);
            if (empty($categoryIds) || countItems($categoryIds) < 1) {
                return array();
            }
            $this->buildQuery();
            return $this->builder->whereIn('products.category_id', $categoryIds, FALSE)->orderBy('products.id DESC')->limit(LIMIT_RSS_PRODUCTS)->get()->getResult();
        }, 'product');
    }

    //get rss products by user
    public function getRssProductsByUser($userId, $langId)
    {
        $cacheKey = 'rss_products_user_' . $userId . '_lang_' . $langId;
        return getCacheData($cacheKey, function () use ($userId) {
            $this->buildQuery();
            return $this->builder->where('users.id', clrNum($userId))->orderBy('products.id DESC')->limit(LIMIT_RSS_PRODUCTS)->get()->getResult();
        }, 'product');
    }

    //get paginated sitemap products
    public function getSitemapProductsPaginated($perPage, $offset)
    {
        $this->buildQuery();
        return $this->builder->limit($perPage, $offset)->get()->getResult();
    }

    /*
     * --------------------------------------------------------------------
     * Dashboard
     * --------------------------------------------------------------------
     */

    //get vendor products count
    public function getVendorProductsCount($userId, $listType)
    {
        $this->filterUserProducts($listType, $userId, true);
        return $this->builder->countAllResults();
    }

    //get vendor products
    public function getVendorProductsPaginated($userId, $listType, $perPage, $offset)
    {
        $this->filterUserProducts($listType, $userId);
        return $this->builder->limit($perPage, $offset)->get()->getResult();
    }

    //get seller products coupon count
    public function getSellerProductsCouponCount($userId)
    {
        $this->filterUserProducts('active', $userId, true);
        return $this->builder->countAllResults();
    }

    //get seller products coupon
    public function getSellerProductsCouponPaginated($userId, $couponId, $perPage, $offset)
    {
        $this->filterUserProducts('active', $userId);
        $this->builder->select('(SELECT coupon_products.id FROM coupon_products WHERE products.id = coupon_products.product_id AND coupon_products.coupon_id = ' . clrNum($couponId) . ' LIMIT 1) AS is_selected');
        return $this->builder->limit($perPage, $offset)->get()->getResult();
    }

    //get vendor products export
    public function getVendorProductsExport($userId, $listType)
    {
        $this->filterUserProducts($listType, $userId, false, 'POST');
        return $this->builder->select("(SELECT GROUP_CONCAT(storage, ':::', image_big) FROM images WHERE images.product_id = products.id) AS images_big")
            ->select('(SELECT CONCAT(short_description, ":::", description)  FROM product_details WHERE product_details.product_id = products.id AND product_details.lang_id = ' . clrNum($this->activeLang->id) . ' LIMIT 1) AS product_content')
            ->select('(SELECT name FROM category_lang WHERE products.category_id = category_lang.category_id AND category_lang.lang_id = ' . clrNum($this->activeLang->id) . ' LIMIT 1) AS category_name')
            ->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //filter user products
    public function filterUserProducts($listType, $userId, $isForCount = false, $formMethod = 'GET')
    {
        $listingType = inputGet('listing_type');
        $productType = inputGet('product_type');
        $category = clrNum(inputGet('category'));
        $subCategory = clrNum(inputGet('subcategory'));
        $stock = inputGet('stock');
        $q = removeSpecialCharacters(inputGet('q'));
        if ($formMethod == 'POST') {
            $listingType = inputPost('listing_type');
            $productType = inputPost('product_type');
            $productType = inputPost('product_type');
            $category = clrNum(inputPost('category'));
            $subCategory = clrNum(inputPost('subcategory'));
            $stock = inputPost('stock');
            $q = removeSpecialCharacters(inputPost('q'));
        }

        $categoryIds = array();
        $categoryId = $category;
        if (!empty($subCategory)) {
            $categoryId = $subCategory;
        }
        $categoryModel = new CategoryModel();
        if (!empty($categoryId)) {
            $categoryIds = $categoryModel->getSubCategoriesTreeIds($categoryId, true);
        }

        $this->setBaseQuery(false, $isForCount);

        $this->builder->where('users.id', clrNum($userId))->where('products.is_deleted', 0);

        if ($listType == 'pending') {
            $this->builder->where('products.is_draft', 0)->where('products.status', 0);
        } elseif ($listType == 'draft') {
            $this->builder->where('products.is_draft', 1);
        } elseif ($listType == 'hidden') {
            $this->builder->where('products.is_draft', 0)->where('products.visibility', 0);
        } elseif ($listType == 'sold') {
            $this->builder->where('products.is_sold', 1);
        } else {
            $this->builder->where('products.is_draft', 0)->where('products.status', 1)->where('products.visibility', 1);
        }
        if ($listingType == 'sell_on_site' || $listingType == 'ordinary_listing' || $listingType == 'bidding' || $listingType == 'license_key') {
            $this->builder->where('products.listing_type', $listingType);
        }
        if ($productType == 'physical' || $productType == 'digital') {
            $this->builder->where('products.product_type', $productType);
        }
        if (!empty($categoryIds)) {
            $this->builder->whereIn("products.category_id", $categoryIds, FALSE);
        }
        if ($stock == 'in_stock' || $stock == 'out_of_stock') {
            $this->builder->groupStart();
            if ($stock == 'out_of_stock') {
                $this->builder->where("products.product_type = 'physical' AND products.stock <=", 0);
            } else {
                $this->builder->where("products.product_type = 'digital' OR products.stock >", 0);
            }
            $this->builder->groupEnd();
        }

        $processedTerm = '';
        if (!empty($q)) {
            $processedTerm = $this->prepareSearchTerm($q);
            if (!empty($processedTerm)) {
                $this->builder->select('products.*, MATCH(psi.search_index) AGAINST(' . $this->db->escape($processedTerm) . ' IN BOOLEAN MODE) AS relevance', false)
                    ->join('product_search_indexes psi', 'psi.product_id = products.id AND psi.lang_id = ' . clrNum($this->activeLang->id))
                    ->where('MATCH(psi.search_index) AGAINST(' . $this->db->escape($processedTerm) . ' IN BOOLEAN MODE) >', 0, false);
            }
        }

        //category name
        $langId = clrNum($this->activeLang->id);
        $defaultLangId = clrNum($this->defaultLang->id);
        $compiledSubQuery = $this->db->table('category_lang')->select('name')->where('category_lang.category_id = products.category_id')->whereIn('lang_id', $langId != $defaultLangId ? [$langId, $defaultLangId] : [$langId])
            ->when($langId != $defaultLangId, fn($qb) => $qb->orderBy("lang_id = $langId", 'DESC', false))->limit(1)->getCompiledSelect(false);
        $this->builder->select("($compiledSubQuery) AS cat_name");

        if (!empty($processedTerm)) {
            $this->builder->orderBy('relevance', 'DESC');
        } else {
            $this->builder->orderBy('products.id DESC');
        }
    }

    /*
     * --------------------------------------------------------------------
     * Tags & Search Indexes
     * --------------------------------------------------------------------
     */

    //add or update product search index
    public function syncProductSearchIndex($productId)
    {
        $product = $this->getProduct($productId);
        if (empty($product)) {
            return;
        }

        $productDetails = $this->builderProductDetails->where('product_id', $product->id)->get()->getResult();
        if (empty($productDetails)) {
            return;
        }

        $existingIndexes = $this->builderSearchIndexes->where('product_id', $product->id)->get()->getResult();

        $indexMap = [];
        foreach ($existingIndexes as $index) {
            $indexMap[$index->lang_id] = $index->id;
        }

        $indexesToUpdate = [];
        $indexesToInsert = [];

        foreach ($productDetails as $detailRow) {
            $searchIndex = $this->buildSearchIndexString($product, $detailRow);
            if (empty($searchIndex)) {
                continue;
            }

            if (isset($indexMap[$detailRow->lang_id])) {
                $indexesToUpdate[] = [
                    'id' => $indexMap[$detailRow->lang_id],
                    'search_index' => $searchIndex
                ];
            } else {
                $indexesToInsert[] = [
                    'product_id' => $product->id,
                    'lang_id' => $detailRow->lang_id,
                    'search_index' => $searchIndex
                ];
            }
        }

        if (!empty($indexesToUpdate)) {
            $this->builderSearchIndexes->updateBatch($indexesToUpdate, 'id');
        }

        if (!empty($indexesToInsert)) {
            $this->builderSearchIndexes->insertBatch($indexesToInsert);
        }
    }

    // build the final search index string for a product detail row
    private function buildSearchIndexString($product, $detailRow)
    {
        $index = $this->cleanTextForIndex($detailRow->title);

        $tagsStr = getProductTagsString($product->id, $detailRow->lang_id);
        if (!empty($tagsStr)) {
            $index .= ' ' . $this->cleanTextForIndex($tagsStr);
        }

        $wordsArray = explode(' ', $index);
        $uniqueWordsArray = array_unique($wordsArray);
        $filteredIndex = implode(' ', $uniqueWordsArray);

        if (!empty($product->sku)) {
            $filteredIndex .= ' ' . trim($product->sku);
        }

        return trim($filteredIndex);
    }

    //prepare search term
    private function prepareSearchTerm($searchTerm)
    {
        $cleaned = $this->cleanTextForIndex($searchTerm);
        $words = explode(' ', $cleaned);

        $words = array_filter($words, function ($word) {
            return mb_strlen($word, 'UTF-8') > 2;
        });

        if (empty($words)) {
            return '';
        }

        $prefixedWords = array_map(function ($word) {
            return '+' . $word;
        }, $words);

        return implode(' ', $prefixedWords);
    }

    //clean a string for indexing
    private function cleanTextForIndex($text)
    {
        $text = removeSpecialCharacters($text);
        $text = str_replace(['&', ',', '-', '_'], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return mb_strtolower(trim($text), 'UTF-8');
    }

    //add or update product tags
    private function addUpdateProductTags($productId, $langId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            $tagsStr = $this->getProductTagsString($product, $langId);
            $newTagsStr = '';
            $tagsInput = inputPost('tags_' . $langId);
            $tags = [];
            if (!empty($tagsInput)) {
                $tagsArray = explode(',', $tagsInput);
                if (!empty($tagsArray) && countItems($tagsArray) > 0) {
                    $tagsArray = array_slice($tagsArray, 0, PRODUCT_TAG_LIMIT);
                    foreach ($tagsArray as $item) {
                        if (!empty($item)) {
                            $item = removeSpecialCharacters($item);
                            if (!empty($item)) {
                                $item = mb_strtolower($item, 'UTF-8');
                            }
                            if (!empty($item) && strlen($item) > 1 && !in_array($item, $tags)) {
                                array_push($tags, $item);
                            }
                        }
                    }
                }
            }
            if (!empty($tags) && countItems($tags) > 0) {
                $newTagsStr = implode(',', $tags);
            }
            if ($tagsStr != $newTagsStr) {
                //delete old tags
                $this->builderTags->where('product_id', clrNum($productId))->where('lang_id', clrNum($langId))->delete();
                //add new tags
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            $tag = strlen($tag) > PRODUCT_TAG_CHAR_LIMIT ? substr($tag, 0, PRODUCT_TAG_CHAR_LIMIT) : $tag;
                            $data = [
                                'product_id' => clrNum($productId),
                                'lang_id' => clrNum($langId),
                                'tag' => $tag,
                            ];
                            $this->builderTags->insert($data);
                        }
                    }
                }
            }
        }
    }

    //duplicate a product with all its related data including details, images, options, and variants
    public function duplicateProduct($sourceProductId)
    {
        $this->db->transStart();

        $sourceProduct = $this->db->table('products')->where('id', $sourceProductId)->get()->getRowArray();
        if (empty($sourceProduct) || $sourceProduct['user_id'] != user()->id) {
            return false;
        }

        // Load Options Model for helper methods (generateImmutableKey, generateVariantHash)
        $optionsModel = new ProductOptionsModel();

        // Duplicate Main Product Data
        $uniqKey = time();
        $newProductData = $sourceProduct;
        unset($newProductData['id']);

        // Generate unique fields
        $newProductData['slug'] = $sourceProduct['slug'] . "-" . $uniqKey;
        $newProductData['sku'] = $sourceProduct['sku'] ? $sourceProduct['sku'] . '-' . $uniqKey : null;

        // Reset status fields
        $newProductData['is_active'] = 0;
        $newProductData['status'] = 0;
        $newProductData['is_draft'] = 1;
        $newProductData['is_sold'] = 0;
        $newProductData['is_deleted'] = 0;
        $newProductData['is_promoted'] = 0;
        $newProductData['pageviews'] = 0;
        $newProductData['rating'] = 0;
        $newProductData['created_at'] = date('Y-m-d H:i:s');
        $newProductData['updated_at'] = date('Y-m-d H:i:s');

        $this->builder->insert($newProductData);
        $newProductId = $this->db->insertID();

        // Duplicate Product Details
        $productDetails = $this->db->table('product_details')->where('product_id', $sourceProductId)->get()->getResultArray();
        foreach ($productDetails as $detail) {
            unset($detail['id']);
            $detail['product_id'] = $newProductId;
            $this->builderProductDetails->insert($detail);
        }

        // Duplicate Images (ONLY LOCAL)
        $imageIdMap = [];
        $productImages = $this->db->table('images')->where('product_id', $sourceProductId)->get()->getResultArray();
        $uploadPath = FCPATH . 'uploads/images/';

        foreach ($productImages as $image) {
            // Check storage type first
            if ($image['storage'] !== 'local') {
                continue;
            }

            $oldImageId = $image['id'];
            $newImageData = $image;
            unset($newImageData['id']);
            $newImageData['product_id'] = $newProductId;

            $timestamp = time();
            $imageFields = ['image_default', 'image_big', 'image_small'];
            $copiedFiles = [];
            $allFilesCopiedSuccessfully = true;

            foreach ($imageFields as $field) {
                if (!empty($image[$field])) {
                    $sourceFilePath = $uploadPath . $image[$field];

                    if (file_exists($sourceFilePath)) {
                        $pathInfo = pathinfo($image[$field]);
                        $newFilename = $pathInfo['filename'] . '-cp-' . $uniqKey . '-' . uniqid() . '.' . $pathInfo['extension'];
                        $destinationFilePath = $uploadPath . $newFilename;

                        if (copy($sourceFilePath, $destinationFilePath)) {
                            $newImageData[$field] = $newFilename;
                            $copiedFiles[] = $destinationFilePath;
                        } else {
                            $allFilesCopiedSuccessfully = false;
                            break;
                        }
                    } else {
                        // Source file missing on disk
                        $allFilesCopiedSuccessfully = false;
                        break;
                    }
                }
            }

            if ($allFilesCopiedSuccessfully) {
                $this->db->table('images')->insert($newImageData);
                $newImageId = $this->db->insertID();
                $imageIdMap[$oldImageId] = $newImageId;
            } else {
                // Rollback: delete any files physically copied for this failed image entry
                foreach ($copiedFiles as $file) {
                    @unlink($file);
                }
            }
        }

        // Duplicate Tags
        $productTags = $this->db->table('product_tags')->where('product_id', $sourceProductId)->get()->getResultArray();
        foreach ($productTags as $tag) {
            $this->db->table('product_tags')->insert([
                'product_id' => $newProductId,
                'tag_id' => $tag['tag_id']
            ]);
        }

        // Duplicate Custom Fields
        $customFields = $this->db->table('custom_fields_product')->where('product_id', $sourceProductId)->get()->getResultArray();
        foreach ($customFields as $field) {
            unset($field['id']);
            $field['product_id'] = $newProductId;
            $this->db->table('custom_fields_product')->insert($field);
        }

        // Duplicate Options & Values (Generating NEW Immutable Keys)
        $optionIdMap = [];   // Old Option ID => New Option ID
        $optionKeyMap = [];  // Old Option ID => New Option Key (string)
        $valueIdMap = [];    // Old Value ID => New Value ID
        $valueKeyMap = [];   // Old Value ID => New Value Key (string)

        // Copy Options
        $productOptions = $this->db->table('product_options')->where('product_id', $sourceProductId)->get()->getResultArray();
        foreach ($productOptions as $option) {
            $oldOptionId = $option['id'];

            // Generate a fresh unique key
            $newOptionKey = $optionsModel->generateImmutableKey('opt');

            $newOptionData = $option;
            unset($newOptionData['id']);
            $newOptionData['product_id'] = $newProductId;
            $newOptionData['option_key'] = $newOptionKey; // Use new key

            $this->db->table('product_options')->insert($newOptionData);
            $newOptionId = $this->db->insertID();

            $optionIdMap[$oldOptionId] = $newOptionId;
            $optionKeyMap[$oldOptionId] = $newOptionKey;
        }

        // Copy Option Values
        if (!empty($optionIdMap)) {
            $sourceOptionIds = array_keys($optionIdMap);
            $optionValues = $this->db->table('product_option_values')->whereIn('option_id', $sourceOptionIds)->get()->getResultArray();

            foreach ($optionValues as $value) {
                $oldValueId = $value['id'];
                $oldOptionId = $value['option_id'];

                // Remap Gallery Images (only if they exist in imageIdMap - meaning local and successfully copied)
                $newGalleryIds = [];
                if (!empty($value['gallery_image_ids'])) {
                    $oldGalleryIds = json_decode($value['gallery_image_ids'], true);
                    if (is_array($oldGalleryIds)) {
                        foreach ($oldGalleryIds as $oldId) {
                            if (isset($imageIdMap[$oldId])) {
                                $newGalleryIds[] = (string)$imageIdMap[$oldId];
                            }
                        }
                    }
                }

                // Remap Swatch Image
                $newSwatchId = null;
                if (!empty($value['primary_swatch_image_id']) && isset($imageIdMap[$value['primary_swatch_image_id']])) {
                    $newSwatchId = $imageIdMap[$value['primary_swatch_image_id']];
                }

                // Generate a fresh unique key
                $newValueKey = $optionsModel->generateImmutableKey('val');

                $newValueData = $value;
                unset($newValueData['id']);
                $newValueData['option_id'] = $optionIdMap[$oldOptionId];
                $newValueData['value_key'] = $newValueKey; // Use new key
                $newValueData['gallery_image_ids'] = !empty($newGalleryIds) ? json_encode($newGalleryIds) : null;
                $newValueData['primary_swatch_image_id'] = $newSwatchId;

                $this->db->table('product_option_values')->insert($newValueData);
                $newValueId = $this->db->insertID();

                $valueIdMap[$oldValueId] = $newValueId;
                $valueKeyMap[$oldValueId] = $newValueKey;
            }
        }

        // Duplicate Variants (Recalculating Hash with NEW Keys)
        $productVariants = $this->db->table('product_option_variants')->where('product_id', $sourceProductId)->get()->getResultArray();

        foreach ($productVariants as $variant) {
            $oldVariantId = $variant['id'];

            // Insert variant with empty hash initially
            $newVariantData = $variant;
            unset($newVariantData['id']);
            $newVariantData['product_id'] = $newProductId;
            $newVariantData['sku'] = $variant['sku'] ? $variant['sku'] . '-' . $uniqKey : null;
            $newVariantData['is_default'] = 0;
            $newVariantData['variant_hash'] = '';

            $this->db->table('product_option_variants')->insert($newVariantData);
            $newVariantId = $this->db->insertID();

            // Fetch old composition
            $oldVariantValues = $this->db->table('product_option_variant_values')
                ->where('variant_id', $oldVariantId)
                ->get()->getResultArray();

            $keysForHash = [];

            foreach ($oldVariantValues as $vv) {
                $oldValueId = $vv['value_id'];

                if (isset($valueIdMap[$oldValueId])) {
                    $newValueId = $valueIdMap[$oldValueId];

                    // Link new variant to new value
                    $this->db->table('product_option_variant_values')->insert([
                        'variant_id' => $newVariantId,
                        'value_id' => $newValueId
                    ]);

                    $parentOldOptionId = null;

                    // Simple search in the previously fetched $optionValues array
                    foreach($optionValues as $ovItem) {
                        if($ovItem['id'] == $oldValueId) {
                            $parentOldOptionId = $ovItem['option_id'];
                            break;
                        }
                    }

                    if ($parentOldOptionId && isset($optionKeyMap[$parentOldOptionId]) && isset($valueKeyMap[$oldValueId])) {
                        $newOptKey = $optionKeyMap[$parentOldOptionId];
                        $newValKey = $valueKeyMap[$oldValueId];
                        $keysForHash[$newOptKey] = $newValKey;
                    }
                }
            }

            // Recalculate Hash using new keys
            if (!empty($keysForHash)) {
                $newHash = $optionsModel->generateVariantHash($keysForHash);
                $this->db->table('product_option_variants')->where('id', $newVariantId)->update(['variant_hash' => $newHash]);
            }
        }

        // Commit Transaction
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

    /*
     * --------------------------------------------------------------------
     * License Key
     * --------------------------------------------------------------------
     */

    //add license keys
    public function addLicenseKeys($productId)
    {
        $licenseKeys = inputPost('license_keys');
        $allowDuplicate = inputPost('allow_duplicate');
        $licenseKeysArray = explode(',', $licenseKeys ?? '');
        if (!empty($licenseKeysArray)) {
            foreach ($licenseKeysArray as $licenseKey) {
                $licenseKey = trim($licenseKey);
                if (!empty($licenseKey)) {
                    //check duplicate
                    $addKey = true;
                    if (empty($allowDuplicate)) {
                        if (!empty($this->checkLicenseKey($productId, $licenseKey))) {
                            $addKey = false;
                        }
                    }
                    //add license key
                    if ($addKey) {
                        $data = [
                            'product_id' => $productId,
                            'license_key' => trim($licenseKey ?? ''),
                            'is_used' => 0
                        ];
                        $this->builderProductLicenseKeys->insert($data);
                    }
                }
            }
        }
    }

    //get license keys
    public function getProductLicenseKeys($productId)
    {
        return $this->builderProductLicenseKeys->where('product_id', clrNum($productId))->get()->getResult();
    }

    //get license key
    public function getLicenseKey($id)
    {
        return $this->builderProductLicenseKeys->where('id', clrNum($id))->get()->getRow();
    }

    //purchase license key
    public function purchaseLicenseKey($productId)
    {
        $product = $this->getProduct($productId);
        if (empty($product)) {
            return false;
        }

        $this->db->transStart();

        $license = $this->builderProductLicenseKeys->where('product_id', $product->id)->where('is_used', 0)->orderBy('id', 'ASC')->get()->getRow();

        if (!empty($license)) {
            $this->builderProductLicenseKeys->where('id', $license->id)->update(['is_used' => 1]);

            $unUsedCount = $this->builderProductLicenseKeys->where('product_id', $product->id)->where('is_used', 0)->countAllResults();

            if ($product->listing_type == 'license_key') {
                if ($unUsedCount < 1) {
                    $this->db->table('products')->where('id', $product->id)->update(['is_sold' => 1]);
                }
            } else {
                $hasLicense = $this->builderProductLicenseKeys->where('product_id', $product->id)->get()->getRow();
                if (!empty($hasLicense) && $unUsedCount < 1) {
                    $this->db->table('products')->where('id', $product->id)->update(['is_sold' => 1]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === true) {
                return $license->license_key;
            }
        }

        $this->db->transComplete();
        return false;
    }

    //check license key
    public function checkLicenseKey($productId, $licenseKey)
    {
        return $this->builderProductLicenseKeys->where('product_id', clrNum($productId))->where('license_key', $licenseKey)->get()->getRow();
    }

    //delete license key
    public function deleteLicenseKey($id)
    {
        $licenseKey = $this->getLicenseKey($id);
        if (!empty($licenseKey)) {
            return $this->builderProductLicenseKeys->where('id', $licenseKey->id)->delete();
        }
        return false;
    }
}