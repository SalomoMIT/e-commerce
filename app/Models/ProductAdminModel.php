<?php namespace App\Models;

class ProductAdminModel extends BaseModel
{
    protected $builder;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('products');
    }

    //build query
    public function buildQuery($isForCount = false, $langId = null, $type = null, $getExpired = true)
    {
        if (empty($langId)) {
            $langId = $this->activeLang->id;
        }
        $defLangId = $this->defaultLang->id;

        $this->builder->resetQuery();
        $this->builder->join('users', 'users.id = products.user_id AND users.banned = 0');

        // If the query is not for counting, add all the extra SELECTs and JOINs needed to fetch full data.
        if (!$isForCount) {
            $this->builder->select("products.*, users.username AS user_username, users.role_id AS role_id, users.slug AS user_slug");

            if ($langId != $defLangId) {
                $this->builder->select("COALESCE(pd_selected.title, pd_default.title) AS title")
                    ->join('product_details AS pd_selected', 'pd_selected.product_id = products.id AND pd_selected.lang_id = ' . $this->db->escape($langId), 'left')
                    ->join('product_details AS pd_default', 'pd_default.product_id = products.id AND pd_default.lang_id = ' . $this->db->escape($defLangId), 'left');
            } else {
                $this->builder->select("pd.title")
                    ->join('product_details AS pd', 'pd.product_id = products.id AND pd.lang_id = ' . $this->db->escape($defLangId), 'left');
            }

            // Subquery for category name
            $compiledSubQuery = $this->db->table('category_lang')->select('name')->where('category_lang.category_id = products.category_id')->whereIn('lang_id', $langId != $defLangId ? [$langId, $defLangId] : [$langId])
                ->when($langId != $defLangId, fn($qb) => $qb->orderBy("lang_id = $langId", 'DESC', false))->limit(1)->getCompiledSelect(false);
            $this->builder->select("($compiledSubQuery) AS cat_name");
        }

        if ($this->generalSettings->membership_plans_system == 1 && $getExpired == true) {
            if ($type == 'expired') {
                $this->builder->where('users.is_membership_plan_expired = 1');
            } else {
                $this->builder->where('users.is_membership_plan_expired = 0');
            }
        }
    }

    //get products count
    public function getProductsCount()
    {
        $this->buildQuery(true);
        return $this->builder->where('products.is_deleted', 0)->where('products.is_draft', 0)->where('products.status', 1)->where('visibility', 1)->countAllResults();
    }

    //get latest pending products
    public function getLatestPendingProducts($limit)
    {
        $this->buildQuery();
        return $this->builder->where('products.status !=', 1)->where('products.is_draft', 0)->where('products.is_deleted', 0)->orderBy('products.id DESC')->get(clrNum($limit))->getResult();
    }

    //get pending products count
    public function getPendingProductsCount()
    {
        $this->buildQuery(true);
        return $this->builder->where('products.is_deleted', 0)->where('products.is_draft', 0)->where('products.status =', 0)->countAllResults();
    }

    //get paginated products count
    public function getFilteredProductCount($list)
    {
        if ($list == 'expired_products') {
            $this->buildQuery(true, null, 'expired', true);
        } elseif ($list == 'deleted_products') {
            $this->buildQuery(true, null, null, false);
        } else {
            $this->buildQuery(true);
        }
        $this->filterProducts($list);
        return $this->builder->countAllResults();
    }

    //get paginated products
    public function getFilteredProductsPaginated($perPage, $offset, $list)
    {
        if ($list == 'expired_products') {
            $this->buildQuery(false, null, 'expired', true);
        } elseif ($list == 'deleted_products') {
            $this->buildQuery(false, null, null, false);
        } else {
            $this->buildQuery();
        }
        $this->filterProducts($list);
        return $this->builder->limit($perPage, $offset)->get()->getResult();
    }

    //get export products
    public function getFilteredProductsExport($list)
    {
        if ($list == 'expired_products') {
            $this->buildQuery(false, null, 'expired', true);
        } elseif ($list == 'deleted_products') {
            $this->buildQuery(false, null, null, false);
        } else {
            $this->buildQuery();
        }
        $this->builder->select('(SELECT username FROM users WHERE products.user_id = users.id) AS seller_username')
            ->select("(SELECT GROUP_CONCAT(storage, ':::', image_big) FROM images WHERE images.product_id = products.id) AS images_big")
            ->select('(SELECT CONCAT(short_description, ":::", description)  FROM product_details WHERE product_details.product_id = products.id AND product_details.lang_id = ' . clrNum($this->activeLang->id) . ' LIMIT 1) AS product_content')
            ->select('(SELECT name FROM category_lang WHERE products.category_id = category_lang.category_id AND category_lang.lang_id = ' . clrNum($this->activeLang->id) . ' LIMIT 1) AS category_name');
        $this->filterProducts($list, 'POST');
        return $this->builder->limit(LIMIT_EXPORT_ROW)->get()->getResult();
    }

    //filter by values
    public function filterProducts($list, $formMethod = 'GET')
    {
        $listingType = inputGet('listing_type');
        $productType = inputGet('product_type');
        $stock = inputGet('stock');
        $q = inputGet('q');
        $categoryId = inputGet('category');
        $subCategoryId = inputGet('subcategory');
        if ($formMethod == 'POST') {
            $listingType = inputPost('listing_type');
            $productType = inputPost('product_type');
            $stock = inputPost('stock');
            $q = inputPost('q');
            $categoryId = inputPost('category');
            $subCategoryId = inputPost('subcategory');
        }

        $arrayCategoryIds = array();
        if (!empty($subCategoryId)) {
            $categoryId = $subCategoryId;
        }
        if (!empty($categoryId)) {
            $categoryModel = new CategoryModel();
            $arrayCategoryIds = $categoryModel->getSubCategoriesTreeIds($categoryId, false);
        }

        if (!empty($arrayCategoryIds)) {
            $this->builder->whereIn('products.category_id', $arrayCategoryIds);
        }
        if (!empty($q)) {
            $search = removeForbiddenCharacters($q);
            $escSearch = $this->db->escape($search);
            $this->builder->join('product_search_indexes psi', 'psi.product_id = products.id AND psi.lang_id = ' . selectedLangId())
                ->where("MATCH(psi.search_index) AGAINST({$escSearch} IN NATURAL LANGUAGE MODE)");
        }
        if ($listingType == 'sell_on_site' || $listingType == 'ordinary_listing' || $listingType == 'bidding' || $listingType == 'license_key') {
            $this->builder->where('products.listing_type', $listingType);
        }
        if ($productType == 'physical' || $productType == 'digital') {
            $this->builder->where('products.product_type', $productType);
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
        if (!empty($list)) {
            if ($list == 'products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.status', 1)
                    ->where('products.visibility', 1);
            }
            if ($list == 'featured_products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.status', 1)
                    ->where('products.visibility', 1)
                    ->where('products.is_promoted', 1);
            }
            if ($list == 'special_offers') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.status', 1)
                    ->where('products.visibility', 1)
                    ->where('products.is_special_offer', 1);
            }
            if ($list == 'hidden_products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.status', 1)
                    ->where('products.visibility', 0);
            }
            if ($list == 'pending_products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.status =', 0)
                    ->where('products.visibility', 1)
                    ->where('products.is_edited =', 0);
            }
            if ($list == 'edited_products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0)
                    ->where('products.visibility', 1)
                    ->where('products.is_edited', 1);
            }
            if ($list == 'sold_products') {
                $this->builder->where('products.is_sold', 1)
                    ->where('products.is_deleted', 0);
            }
            if ($list == 'drafts') {
                $this->builder->where('products.is_draft', 1)
                    ->where('products.is_deleted', 0);
            }
            if ($list == 'deleted_products') {
                $this->builder->where('products.is_deleted', 1);
            }
            if ($list == 'expired_products') {
                $this->builder->where('products.is_deleted', 0)
                    ->where('products.is_draft', 0);
            }
        }

        if ($list == 'special_offers') {
            $this->builder->orderBy('products.special_offer_date DESC');
        } else {
            if (empty($q)) {
                $this->builder->orderBy('products.id DESC');
            }
        }
    }

    //get product
    public function getProduct($id)
    {
        return $this->builder->where('products.id', clrNum($id))->get()->getRow();
    }

    //get product by slug
    public function isProductSlugUnique($productId, $slug)
    {
        if ($this->builder->where('products.id !=', clrNum($productId))->where('products.slug', removeSpecialCharacters($slug))->get()->getRow()) {
            return false;
        }
        return true;
    }

    //approve product
    public function approveProduct($id)
    {
        $product = $this->getProduct($id);
        if (!empty($product)) {
            $data = [
                'status' => 1,
                'is_active' => 1,
                'is_edited' => 0,
                'is_rejected' => 0,
                'reject_reason' => '',
                'created_at' => date('Y-m-d H:i:s')
            ];
            return $this->builder->where('id', $product->id)->update($data);
        }
        return false;
    }

    //reject product
    public function rejectProduct($id)
    {
        $product = $this->getProduct($id);
        if (!empty($product)) {
            $data = [
                'status' => 0,
                'is_active' => 0,
                'is_rejected' => 1,
                'reject_reason' => inputPost('reject_reason')
            ];
            return $this->builder->where('id', $product->id)->update($data);
        }
        return false;
    }

    //add to featured products
    public function addToFeaturedProducts($productId, $dayCount, $transactionId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product) && $product->is_promoted != 1) {
            $date = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime($date . ' + ' . clrNum($dayCount) . ' days'));
            $data = [
                'is_promoted' => 1,
                'promote_start_date' => $date,
                'promote_end_date' => $endDate
            ];
            $transaction = $this->db->table('promoted_transactions')->where('id', clrNum($transactionId))->get()->getRow();
            if (!empty($transaction)) {
                $data["promote_plan"] = $transaction->purchased_plan;
                $data["promote_day"] = $transaction->day_count;
            }

            $result = $this->builder->where('id', $product->id)->update($data);

            if ($result && !empty($transaction)) {
                $this->db->table('promoted_transactions')->where('id', $transaction->id)->update(['payment_status' => "Completed"]);
            }
            return true;
        }
        return false;
    }

    //remove from featured products
    public function removeFromFeaturedProducts($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product) && $product->is_promoted == 1) {
            return $this->builder->where('id', $product->id)->update(['is_promoted' => 0]);
        }
        return false;
    }

    //add remove special offers
    public function addRemoveSpecialOffer($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            if ($product->is_special_offer == 1) {
                $data = [
                    'is_special_offer' => 0,
                    'special_offer_date' => ''
                ];
            } else {
                $data = [
                    'is_special_offer' => 1,
                    'special_offer_date' => date('Y-m-d H:i:s')
                ];
            }
            return $this->builder->where('id', $product->id)->update($data);
        }
        return false;
    }

    //add remove affiliate product
    public function addRemoveAffiliateProduct($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product) && $product->user_id == user()->id) {
            $data['is_affiliate'] = $product->is_affiliate == 1 ? 0 : 1;
            return $this->builder->where('id', $product->id)->update($data);
        }
        return false;
    }

    //set product as edited
    public function setProductAsEdited($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            $data = ['updated_at' => date('Y-m-d H:i:s')];
            if (!empty($this->generalSettings->approve_after_editing)) {
                if (!hasPermission('products')) {
                    if ($product->is_draft != 1 && $product->status == 1) {
                        $data['is_edited'] = 1;
                        if ($this->generalSettings->approve_after_editing == 2) {
                            $data['status'] = 0;
                            $data['is_active'] = 0;
                        }
                    }
                }
            }
            $this->builder->where('id', $product->id)->update($data);
        }
    }

    //approve multi edited products
    public function approveMultiEditedProducts($productIds)
    {
        if (!empty($productIds)) {
            foreach ($productIds as $id) {
                $this->approveProduct($id);
            }
        }
    }

    //delete product
    public function deleteProduct($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            $data = [
                'is_deleted' => 1,
                'is_active' => 0,
            ];
            return $this->builder->where('id', $product->id)->update($data);
        }
        return false;
    }

    //delete product permanently
    public function deleteProductPermanently($id)
    {
        $product = $this->getProduct($id);

        if (empty($product)) {
            return false;
        }

        $this->db->transStart();

        // Delete product details
        $this->db->table('product_details')->where('product_id', $product->id)->delete();

        // Delete product license keys
        $this->db->table('product_license_keys')->where('product_id', $product->id)->delete();

        // Delete comments
        $this->db->table('comments')->where('product_id', $product->id)->delete();

        // Delete reviews
        $this->db->table('reviews')->where('product_id', $product->id)->delete();

        // Delete from wishlist
        $this->db->table('wishlist')->where('product_id', $product->id)->delete();

        // Delete custom field values
        $this->db->table('custom_fields_product')->where('product_id', $product->id)->delete();

        // Delete tags
        $this->db->table('product_tags')->where('product_id', $product->id)->delete();

        // Delete product options and their values
        $productOptions = $this->db->table('product_options')->where('product_id', $product->id)->get()->getResult();
        if (!empty($productOptions)) {
            foreach ($productOptions as $productOption) {
                $this->db->table('product_option_values')->where('option_id', $productOption->id)->delete();
                $this->db->table('product_options')->where('id', $productOption->id)->delete();
            }
        }

        // Delete product variants and their values
        $productVariants = $this->db->table('product_option_variants')->where('product_id', $product->id)->get()->getResult();
        if (!empty($productVariants)) {
            foreach ($productVariants as $productVariant) {
                $this->db->table('product_option_variant_values')->where('variant_id', $productVariant->id)->delete();
                $this->db->table('product_option_variants')->where('id', $productVariant->id)->delete();
            }
        }

        // Delete the main product record
        $this->builder->where('id', $product->id)->delete();

        $this->db->transComplete();

        // Delete physical files
        $fileModel = new FileModel();
        $fileModel->deleteProductImages($product->id);

        // Delete digital file if it exists
        if ($product->product_type == 'digital') {
            $digitalFile = $fileModel->getProductDigitalFile($product->id);
            if (!empty($digitalFile)) {
                $fileModel->deleteDigitalFile($digitalFile->id);
            }
        }

        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

    //delete multi product
    public function deleteMultiProducts($productIds)
    {
        if (!empty($productIds)) {
            foreach ($productIds as $id) {
                $this->deleteProduct($id);
            }
        }
    }

    //delete multi product
    public function deleteSelectedProductsPermanently($productIds)
    {
        if (!empty($productIds)) {
            foreach ($productIds as $id) {
                $this->deleteProductPermanently($id);
            }
        }
    }

    //restore product
    public function restoreProduct($productId)
    {
        $product = $this->getProduct($productId);
        if (!empty($product)) {
            return $this->builder->where('id', $product->id)->update(['is_deleted' => 0, 'is_active' => 1]);
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * CSV Bulk Upload
     * --------------------------------------------------------------------
     */

    //insert csv item
    public function insertCSVItem($row)
    {
        if (empty($row)) {
            return false;
        }

        $productModel = new ProductModel();
        $membershipModel = new MembershipModel();
        $tagModel = new TagModel();
        if (!$membershipModel->isAllowedAddingProduct()) {
            return false;
        }

        $defaultLangId = $this->generalSettings->site_lang ?? 1;
        $listingType = inputPost('listing_type');
        $currency = inputPost('currency');

        $slug = getCsvText($row, 'slug');
        $price = numToDecimal(getCsvText($row, 'price'));
        $priceDiscounted = numToDecimal(getCsvText($row, 'price_discounted'));

        if (empty($priceDiscounted) || $priceDiscounted > $price) {
            $priceDiscounted = $price;
        }

        $discountRate = 0;
        if (!empty($price) && $priceDiscounted < $price) {
            $discountRate = intval((($price - $priceDiscounted) * 100) / $price);
        }

        $updatedAt = getCsvText($row, 'updated_at');
        $createdAt = getCsvText($row, 'created_at');
        $data['currency'] = !empty($currency) ? $currency : 'USD';

        //try to generate slug from title if empty
        if (empty($slug)) {
            $title = getCsvText($row, 'title');
            if (!empty($title)) {
                $slug = strSlug($title);
            }
        }

        //insert base product
        $productData = [
            'slug' => $slug,
            'product_type' => 'physical',
            'listing_type' => !empty($listingType) ? $listingType : 'sell_on_site',
            'sku' => getCsvText($row, 'sku'),
            'category_id' => getCsvNum($row, 'category_id', 0),
            'price' => $price,
            'price_discounted' => $priceDiscounted,
            'currency' => !empty($currency) ? $currency : 'USD',
            'discount_rate' => $discountRate,
            'vat_rate' => floatval(getCsvNum($row, 'vat_rate', 0) ?? 0),
            'user_id' => user()->id,
            'status' => 0,
            'is_promoted' => 0,
            'is_special_offer' => 0,
            'visibility' => 1,
            'rating' => 0,
            'pageviews' => 0,
            'external_link' => getCsvText($row, 'external_link'),
            'stock' => getCsvNum($row, 'stock', 0),
            'chargeable_weight' => number_format((float)getCsvText($row, 'weight'), 3, '.', ''),
            'brand_id' => getCsvNum($row, 'brand_id', 0),
            'is_sold' => 0,
            'is_deleted' => 0,
            'is_draft' => 0,
            'is_edited' => 0,
            'is_active' => 0,
            'is_free_product' => 0,
            'is_rejected' => 0,
            'is_affiliate' => 0,
            'updated_at' => !empty(getCsvText($row, 'updated_at')) ? getCsvText($row, 'updated_at') : null,
            'created_at' => !empty(getCsvText($row, 'created_at')) ? getCsvText($row, 'created_at') : date('Y-m-d H:i:s')
        ];

        if ($this->generalSettings->approve_before_publishing == 0 || hasPermission('products')) {
            $productData['status'] = 1;
            $productData['is_active'] = 1;
        }

        if ($this->builder->insert($productData)) {
            $productId = $this->db->insertID();

            //update slug
            $productModel->updateSlug($productId);

            foreach ($this->activeLanguages as $language) {
                $suffix = $language->id == $defaultLangId ? '' : '_lang' . $language->id;

                //add product details
                $title = getCsvText($row, 'title' . $suffix);
                $shortDescription = getCsvText($row, 'short_description' . $suffix);
                $description = getCsvText($row, 'description' . $suffix);
                if ($title !== '') {
                    $langData = [
                        'product_id' => $productId,
                        'lang_id' => $language->id,
                        'title' => $title,
                        'short_description' => $shortDescription,
                        'description' => $description
                    ];
                    $this->db->table('product_details')->insert($langData);

                    //add tags
                    $tags = getCsvText($row, 'tags' . $suffix);
                    if (!empty($tags)) {
                        $tagsArray = explode(',', $tags);
                        if (!empty($tagsArray)) {
                            $tagModel->saveProductTags($productId, $language->id, $tagsArray, false);
                        }
                    }
                }
            }

            //add product search index
            $productModel = new ProductModel();
            $productModel->syncProductSearchIndex($productId);

            //add product images
            $imageUrl = getCsvText($row, 'image_url');
            $this->addProductImagesCsv($imageUrl, $productId);
        }
        return true;
    }

    //update csv item
    public function updateCSVItem($row)
    {
        $productId = getCsvNum($row, 'id');
        if (empty($productId)) {
            return false;
        }

        $product = $this->builder->where('id', $productId)->get()->getRow();
        if (!$product) {
            return false;
        }

        $updateData = [];

        $fields = [
            'slug', 'sku', 'category_id', 'brand_id', 'vat_rate', 'stock',
            'external_link', 'currency'
        ];

        foreach ($fields as $field) {
            $val = getCsvText($row, $field);
            if ($val !== '') {
                $updateData[$field] = in_array($field, ['category_id', 'stock', 'brand_id', 'vat_rate']) ? (float)$val : $val;
            }
        }

        $hasPrice = getCsvText($row, 'price') !== '';
        $hasDiscount = getCsvText($row, 'price_discounted') !== '';

        if ($hasPrice && $hasDiscount) {
            $price = numToDecimal(getCsvText($row, 'price'));
            $discounted = numToDecimal(getCsvText($row, 'price_discounted'));
            $discounted = ($discounted > $price) ? $price : $discounted;

            $updateData['price'] = $price;
            $updateData['price_discounted'] = $discounted;
            $updateData['discount_rate'] = $price > 0 ? intval((($price - $discounted) * 100) / $price) : 0;
        }

        $weight = number_format((float)getCsvText($row, 'weight'), 3, '.', '');
        if (!empty($weight) && $weight > 0) {
            $updateData['chargeable_weight'] = $weight;
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->builder->where('id', $productId)->update($updateData);
        }

        foreach ($this->activeLanguages as $language) {
            $suffix = $language->id == ($this->generalSettings->site_lang ?? 1) ? '' : '_lang' . $language->id;

            $title = getCsvText($row, 'title' . $suffix);
            $shortDescription = getCsvText($row, 'short_description' . $suffix);
            $description = getCsvText($row, 'description' . $suffix);

            if ($title !== '' || $shortDescription !== '' || $description !== '') {
                $detailTable = $this->db->table('product_details');

                $existing = $detailTable->where('product_id', clrNum($productId))->where('lang_id', $language->id)->get()->getRow();

                $data = [];

                if ($title !== '') {
                    $data['title'] = $title;
                }
                if ($shortDescription !== '') {
                    $data['short_description'] = $shortDescription;
                }
                if ($description !== '') {
                    $data['description'] = $description;
                }

                if ($existing) {
                    $detailTable->where('product_id', clrNum($productId))->where('lang_id', $language->id)->update($data);
                } else {
                    $data['product_id'] = $productId;
                    $data['lang_id'] = $language->id;
                    $detailTable->insert($data);
                }

                $tags = getCsvText($row, 'tags' . $suffix);
                if (!empty($tags)) {
                    $tagArray = explode(',', $tags);
                    $tagModel = new TagModel();
                    $tagModel->saveProductTags($productId, $language->id, $tagArray, true);
                }
            }
        }

        //update product search index
        $productModel = new ProductModel();
        $productModel->syncProductSearchIndex($productId);

        $imageUrl = getCsvText($row, 'image_url');
        if (!empty($imageUrl)) {
            $this->addProductImagesCsv($imageUrl, $productId, true);
        }

        return true;
    }

    //add product csv images
    public function addProductImagesCsv($imageUrl, $productId, $deleteOldImages = false)
    {
        if (empty($imageUrl)) {
            return false;
        }

        $arrayImageUrls = array_filter(array_map('trim', explode(',', $imageUrl)));
        if (empty($arrayImageUrls)) {
            return false;
        }

        $uploadModel = new UploadModel();
        $uploadSucceeded = false;
        $oldImages = [];

        //get old images if they are to be deleted later
        if ($deleteOldImages) {
            $oldImages = $this->db->table('images')->where('product_id', clrNum($productId))->get()->getResult();
        }

        foreach ($arrayImageUrls as $url) {
            if (!isValidImageUrl($url)) {
                continue;
            }

            try {
                $tempFileName = 'temp-' . user()->id . '-' . uniqid();
                $tempPath = $uploadModel->downloadTempImage($url, $tempFileName);

                if (!$tempPath || !file_exists($tempPath)) {
                    continue;
                }

                $dataImage = [
                    'product_id' => clrNum($productId),
                    'image_small' => $uploadModel->optimizeImage('resize', $tempPath, 'images', 'img_', PRODUCT_IMAGE_SMALL, null, 'product'),
                    'image_default' => $uploadModel->optimizeImage('resize', $tempPath, 'images', 'img_', PRODUCT_IMAGE_DEFAULT, null, 'product'),
                    'image_big' => $uploadModel->optimizeImage('resize', $tempPath, 'images', 'img_', PRODUCT_IMAGE_BIG, null, 'product'),
                    'is_main' => 0,
                    'storage' => $this->activeStorage
                ];

                $uploadModel->deleteTempFile($tempPath);

                if (!$this->db->connID) {
                    $this->db->reconnect();
                }

                if ($this->db->table('images')->insert($dataImage)) {
                    $uploadSucceeded = true;
                }

            } catch (\Throwable $e) {
                log_message('error', 'Exception while processing image URL: ' . $url . ' | ' . $e->getMessage());
                continue;
            }
        }

        // Delete old images if needed
        if ($deleteOldImages && $uploadSucceeded && !empty($oldImages)) {
            $fileModel = new FileModel();
            foreach ($oldImages as $img) {
                $fileModel->deleteProductImage($img->id);
            }
        }

        //create product image cache
        $fileModel = new FileModel();
        $fileModel->updateProductImageCache($productId);

        return $uploadSucceeded;
    }
}
