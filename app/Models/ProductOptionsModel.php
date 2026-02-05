<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ProductOptionsModel extends Model
{
    /**
     * Load all options and variants data for a given product.
     *
     * @param object $product The product.
     * @return array An array containing 'options' and 'variants' data.
     */
    public function loadProductOptionsData($product, $onlyActiveVariants = false): array
    {
        $productData = ['options' => [], 'variants' => []];

        if (!empty($product)) {
            $productData['options'] = $this->loadOptionsForProduct($product->id);
            $productData['variants'] = $this->loadVariantsForProduct($product->id, $onlyActiveVariants);
            $productData['baseSku'] = $product->sku;
            $productData['product_price'] = $product->price;
            $productData['product_price_discounted'] = $product->price_discounted;
            $productData['discount_rate'] = calculateDiscount($product->price, $product->price_discounted);
            $productData['final_price_formatted'] = priceFormatted($product->price_discounted, $product->currency, true);
        }

        return $productData;
    }

    /**
     * Load options and their values for a specific product.
     *
     * @param int $productId
     * @return array
     */
    private function loadOptionsForProduct(int $productId): array
    {
        $optionsData = [];
        $optionsResult = $this->db->table('product_options')
            ->select('id, option_key, option_name_translations, option_type, display_order, is_active')
            ->where('product_id', $productId)
            ->orderBy('display_order', 'ASC')
            ->get()->getResultObject();

        foreach ($optionsResult as $optionRow) {
            $optionsData[] = [
                'option_server_id' => $optionRow->id,
                'option_key' => $optionRow->option_key,
                'name_translations' => $this->decodeJsonSafely($optionRow->option_name_translations),
                'type' => $optionRow->option_type,
                'sort_order' => (int)$optionRow->display_order,
                'is_enabled' => (bool)$optionRow->is_active,
                'values' => $this->loadValuesForOption($optionRow->id)
            ];
        }
        return $optionsData;
    }

    /**
     * Load values for a specific option and provides the image IDs.
     *
     * @param int $optionId
     * @return array
     */
    private function loadValuesForOption(int $optionId): array
    {
        $valuesData = [];
        $optionValuesResult = $this->db->table('product_option_values')
            ->select('id, value_key, value_name_translations, color_code, primary_swatch_image_id, gallery_image_ids, display_order')
            ->where('option_id', $optionId)
            ->orderBy('display_order', 'ASC')
            ->get()->getResultObject();

        foreach ($optionValuesResult as $valueRow) {
            $galleryIds = json_decode($valueRow->gallery_image_ids, true) ?: [];

            $valuesData[] = [
                'value_server_id' => $valueRow->id,
                'value_key' => $valueRow->value_key,
                'name_translations' => $this->decodeJsonSafely($valueRow->value_name_translations),
                'color' => $valueRow->color_code,
                'sort_order' => (int)$valueRow->display_order,
                'image_ids' => is_array($galleryIds) ? array_map('strval', $galleryIds) : [],
                'primary_swatch_id' => !empty($valueRow->primary_swatch_image_id) ? (string)$valueRow->primary_swatch_image_id : null,
            ];
        }
        return $valuesData;
    }

    /**
     * Load variants and their compositions for a specific product.
     *
     * @param int $productId
     * @return array
     */
    private function loadVariantsForProduct(int $productId, bool $onlyActiveVariants = false): array
    {
        $variantsData = [];
        $builder = $this->db->table('product_option_variants')
            ->select('id, variant_hash, sku, price, price_discounted, quantity, weight, is_default, is_active')
            ->where('product_id', $productId);

        if ($onlyActiveVariants) {
            $builder->where('is_active', 1);
        }

        $variantsResult = $builder->get()->getResultObject();

        foreach ($variantsResult as $variantRow) {
            $variantComposition = $this->loadCompositionForVariant($variantRow->id);

            if (!empty($variantComposition['value_ids'])) {
                sort($variantComposition['value_ids'], SORT_NUMERIC);
                $variantsData[] = [
                    'id' => $variantRow->id,
                    'variant_hash' => $variantRow->variant_hash,
                    'stable_key' => implode('_', $variantComposition['value_ids']),
                    'name' => implode(' / ', $variantComposition['value_names']),
                    'sku' => $variantRow->sku,
                    'price' => formatPrice($variantRow->price),
                    'price_discounted' => formatPrice($variantRow->price_discounted),
                    'quantity' => $variantRow->quantity === null ? '' : (string)$variantRow->quantity,
                    'weight' => $variantRow->weight === null ? '' : (string)$variantRow->weight,
                    'is_default' => (bool)$variantRow->is_default,
                    'is_active' => (bool)$variantRow->is_active
                ];
            }
        }
        return $variantsData;
    }

    /**
     * Load the composition (value IDs and names) for a specific variant.
     *
     * @param int $variantId
     * @return array ['value_ids' => [], 'value_names' => []]
     */
    private function loadCompositionForVariant(int $variantId): array
    {
        $valueNames = [];
        $valueIds = [];

        $variantValuesResult = $this->db->table('product_option_variant_values pvov')
            ->select('pov.id as value_server_id, pov.value_name_translations')
            ->join('product_option_values pov', 'pvov.value_id = pov.id')
            ->join('product_options po', 'pov.option_id = po.id')
            ->where('pvov.variant_id', $variantId)
            ->orderBy('po.display_order', 'ASC')
            ->orderBy('pov.display_order', 'ASC')
            ->get()->getResultObject();

        foreach ($variantValuesResult as $valueRow) {
            $valueIds[] = $valueRow->value_server_id;
            $translations = $this->decodeJsonSafely($valueRow->value_name_translations);
            $valueNames[] = $translations['en'] ?? reset($translations) ?: 'Value';
        }
        return ['value_ids' => $valueIds, 'value_names' => $valueNames];
    }


    /**
     * Save product options and variants data.
     *
     * @param int $productId The ID of the product.
     * @param array $optionsToSave Array of option data.
     * @param array $variantsToSave Array of variant data.
     * @return bool True on success, false on failure.
     * @throws Exception If a database operation fails within the transaction.
     */
    public function saveProductOptionsData(int $productId, array $optionsToSave, array $variantsToSave): bool
    {
        $this->db->transStart();

        try {
            // Pre-fetch existing option keys to preserve them even if names change.
            $existingOptionKeysMap = array_column($this->db->table('product_options')->select('id, option_key')->where('product_id', $productId)->get()->getResultArray(), 'option_key', 'id');

            $submittedOptionServerIds = [];
            $clientToServerOptionIdMap = [];
            $clientToServerValueIdMap = [];

            // Maps to track immutable keys during the transaction.
            $clientValueToOptionKeyMap = [];
            $clientValueToValueKeyMap = [];

            if (is_array($optionsToSave)) {
                foreach ($optionsToSave as $optionData) {
                    $processedOptionData = $this->processSingleOption($productId, $optionData, $clientToServerValueIdMap, $clientValueToOptionKeyMap, $clientValueToValueKeyMap, $existingOptionKeysMap);
                    $clientToServerOptionIdMap[$optionData['client_id']] = $processedOptionData['option_server_id'];
                    if (!empty($optionData['server_id'])) {
                        $submittedOptionServerIds[] = (int)$optionData['server_id'];
                    }
                }
            }

            $this->deleteOrphanedOptions($productId, $clientToServerOptionIdMap, $submittedOptionServerIds);

            // Pass the new key maps to the variant processing method.
            $this->processProductVariants($productId, $variantsToSave, $clientToServerValueIdMap, $clientValueToOptionKeyMap, $clientValueToValueKeyMap);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            } else {
                $this->db->transCommit();
                return true;
            }
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', "Transaction ROLLED BACK for Product ID: {$productId}. Exception: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Process a single option: inserts or updates it, and handles its values.
     *
     * @param int $productId
     * @param array $optionData Data for the single option.
     * @param array &$clientToServerValueIdMap Passed by reference to be updated.
     * @param array $existingOptionKeysMap Pre-fetched map of [server_id => option_key].
     * @return array ['option_server_id' => int]
     * @throws Exception
     */
    private function processSingleOption(int $productId, array $optionData, array &$clientToServerValueIdMap, array &$clientValueToOptionKeyMap, array &$clientValueToValueKeyMap, array $existingOptionKeysMap): array
    {
        $optionServerId = !empty($optionData['server_id']) ? (int)$optionData['server_id'] : null;

        // Use the pre-fetched key for existing options, or generate a new one.
        $optionKey = $existingOptionKeysMap[$optionServerId] ?? $this->generateImmutableKey('opt');

        $optionDbPayload = [
            'option_name_translations' => json_encode($optionData['name_translations'], JSON_UNESCAPED_UNICODE),
            'option_type' => $optionData['type'],
            'display_order' => (int)$optionData['sort_order'],
            'is_active' => $optionData['is_enabled'] ? 1 : 0,
            'product_id' => $productId,
            'option_key' => $optionKey
        ];

        if ($optionServerId) {
            $this->db->table('product_options')->where('id', $optionServerId)->where('product_id', $productId)->update($optionDbPayload);
            $currentDbOptionId = $optionServerId;
        } else {
            $this->db->table('product_options')->insert($optionDbPayload);
            $currentDbOptionId = $this->db->insertID();
        }

        if ($currentDbOptionId) {
            $this->db->table('product_option_values')->where('option_id', $currentDbOptionId)->delete();

            if (is_array($optionData['values'])) {
                foreach ($optionData['values'] as $valueData) {
                    $valueKey = !empty($valueData['value_key']) ? $valueData['value_key'] : $this->generateImmutableKey('val');

                    $valueDbPayload = [
                        'option_id' => $currentDbOptionId,
                        'value_name_translations' => json_encode($valueData['name_translations'], JSON_UNESCAPED_UNICODE),
                        'color_code' => !empty($valueData['color']) ? $valueData['color'] : null,
                        'primary_swatch_image_id' => !empty($valueData['primary_swatch_id']) ? (int)$valueData['primary_swatch_id'] : null,
                        'gallery_image_ids' => json_encode($valueData['image_ids'] ?? [], JSON_UNESCAPED_UNICODE),
                        'display_order' => (int)$valueData['sort_order'],
                        'value_key' => $valueKey
                    ];
                    $this->db->table('product_option_values')->insert($valueDbPayload);
                    $newDbValueId = $this->db->insertID();

                    // Populate all maps to track keys through the process.
                    $clientToServerValueIdMap[$valueData['client_id']] = $newDbValueId;
                    $clientValueToOptionKeyMap[$valueData['client_id']] = $optionKey;
                    $clientValueToValueKeyMap[$valueData['client_id']] = $valueKey;
                }
            }
        }
        return ['option_server_id' => $currentDbOptionId];
    }

    /**
     * Delete options (and their values) that were not part of the submitted data.
     *
     * @param int $productId
     * @param array $clientToServerOptionIdMap
     * @param array $submittedOptionServerIds
     * @throws Exception
     */
    private function deleteOrphanedOptions(int $productId, array $clientToServerOptionIdMap, array $submittedOptionServerIds): void
    {
        $existingOptionsResult = $this->db->table('product_options')->select('id')->where('product_id', $productId)->get()->getResultArray();
        $existingDbOptionIds = array_column($existingOptionsResult, 'id');

        $optionsToKeepServerIds = array_unique(array_filter(array_merge($submittedOptionServerIds, array_values($clientToServerOptionIdMap))));

        $optionsToDeleteServerIds = array_values(array_diff($existingDbOptionIds, $optionsToKeepServerIds));

        if (!empty($optionsToDeleteServerIds)) {
            $this->db->table('product_option_values')->whereIn('option_id', $optionsToDeleteServerIds)->delete();
            $this->db->table('product_options')->whereIn('id', $optionsToDeleteServerIds)->where('product_id', $productId)->delete();
        }
    }

    /**
     * Delete all existing variants and their compositions, then inserts new ones.
     *
     * @param int $productId
     * @param array $variantsToSave
     * @param array $clientToServerValueIdMap
     * @throws Exception
     */
    private function processProductVariants(int $productId, array $variantsToSave, array $clientToServerValueIdMap, array $clientValueToOptionKeyMap, array $clientValueToValueKeyMap): void
    {
        $existingVariantsResult = $this->db->table('product_option_variants')->select('id')->where('product_id', $productId)->get()->getResultArray();

        if (!empty($existingVariantsResult)) {
            $existingVariantServerIds = array_column($existingVariantsResult, 'id');
            $this->db->table('product_option_variant_values')->whereIn('variant_id', $existingVariantServerIds)->delete();
        }

        $this->db->table('product_option_variants')->where('product_id', $productId)->delete();

        if (is_array($variantsToSave) && !empty($variantsToSave)) {
            foreach ($variantsToSave as $variantData) {

                $keysForHash = [];
                if (is_array($variantData['composition'])) {
                    foreach ($variantData['composition'] as $compositionValue) {
                        $valueClientId = $compositionValue['value_client_id'];
                        if (isset($clientValueToOptionKeyMap[$valueClientId], $clientValueToValueKeyMap[$valueClientId])) {
                            $optionKey = $clientValueToOptionKeyMap[$valueClientId];
                            $valueKey = $clientValueToValueKeyMap[$valueClientId];
                            $keysForHash[$optionKey] = $valueKey;
                        }
                    }
                }
                $variantHash = $this->generateVariantHash($keysForHash);

                $variantDbPayload = [
                    'product_id' => $productId,
                    'sku' => $variantData['sku'],
                    'price' => numToDecimal($variantData['price']),
                    'price_discounted' => numToDecimal($variantData['price_discounted']),
                    'quantity' => (isset($variantData['quantity']) && $variantData['quantity'] !== '' && is_numeric($variantData['quantity'])) ? (int)$variantData['quantity'] : 0,
                    'weight' => (isset($variantData['weight']) && $variantData['weight'] !== '' && is_numeric($variantData['weight'])) ? $variantData['weight'] : 0,
                    'is_default' => (isset($variantData['is_default']) && ($variantData['is_default'] === 1 || $variantData['is_default'] === true || $variantData['is_default'] === "1")) ? 1 : 0,
                    'is_active' => (isset($variantData['is_active']) && ($variantData['is_active'] === 1 || $variantData['is_active'] === true || $variantData['is_active'] === "1")) ? 1 : 0,
                    'variant_hash' => $variantHash
                ];

                $variantDbPayload['price'] = str_replace(',', '.', $variantDbPayload['price'] ?? '');
                $variantDbPayload['price_discounted'] = str_replace(',', '.', $variantDbPayload['price_discounted'] ?? '');

                $this->db->table('product_option_variants')->insert($variantDbPayload);
                $newVariantId = $this->db->insertID();

                if ($newVariantId > 0 && is_array($variantData['composition'])) {
                    foreach ($variantData['composition'] as $compositionValue) {
                        $dbValueId = $clientToServerValueIdMap[$compositionValue['value_client_id']] ?? ($compositionValue['value_server_id'] ?? null);

                        if ($dbValueId) {
                            $this->db->table('product_option_variant_values')->insert([
                                'variant_id' => $newVariantId,
                                'value_id' => $dbValueId
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve all images for a given product, formatted for the frontend.
     *
     * @param int $productId
     * @return array
     */
    public function getFormattedProductImages(int $productId): array
    {
        $imagesResult = $this->db->table('images')
            ->where('product_id', $productId)
            ->orderBy('is_main', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()->getResultObject();

        return $this->formatProductImagesArray($imagesResult);
    }

    /**
     * Format an array of raw image result objects.
     *
     * @param array $imagesResult An array of raw image objects (e.g., from database query results).
     * @return array An array of formatted image objects.
     */
    public function formatProductImagesArray(array $imagesResult): array
    {
        if (empty($imagesResult)) {
            return [];
        }

        $formattedImages = [];
        foreach ($imagesResult as $imageRow) {
            $formattedImages[] = [
                'id' => (string)$imageRow->id,
                'url_main' => getStorageFileUrl('uploads/images/' . $imageRow->image_default, $imageRow->storage),
                'url_thumb' => getStorageFileUrl('uploads/images/' . $imageRow->image_small, $imageRow->storage),
                'url_full' => getStorageFileUrl('uploads/images/' . $imageRow->image_big, $imageRow->storage)
            ];
        }
        return $formattedImages;
    }

    /**
     * Find a specific variant by its SKU.
     *
     * @param string $sku The SKU to search for.
     * @return object|null The variant object if found, otherwise null.
     */
    public function getVariantBySku(string $sku, int $productId)
    {
        if (empty($sku) || empty($productId)) {
            return null;
        }

        return $this->db->table('product_option_variants')->where('sku', $sku)->where('product_id', clrNum($productId))->get()->getRow();
    }

    /**
     * Get default variant for a product that is active and in stock.
     *
     * @param int $productId The ID of the product.
     * @return object|null The first available variant object, otherwise null.
     */
    public function getDefaultVariant(int $productId)
    {
        return $this->db->table('product_option_variants')->where('product_id', $productId)->where('is_active', 1)->orderBy('is_default DESC, id')->limit(1)->get()->getRow();
    }

    /**
     * Take the raw product options data and adds formatted prices and a discount rate.
     *
     * @param array $productOptionsData The data array from `loadProductOptionsData`.
     * @return array The data with added 'price_formatted', 'price_discounted_formatted', and 'discount_rate' keys for each variant.
     */
    public function getFormattedVariantDataForDetailPage(array $productOptionsData, $product): array
    {
        if (empty($product) || empty($productOptionsData['variants']) || !is_array($productOptionsData['variants'])) {
            return $productOptionsData;
        }

        foreach ($productOptionsData['variants'] as &$variant) {
            $price = numToDecimal($variant['price']);
            $discountedPrice = numToDecimal($variant['price_discounted']);

            //fallback to product prices if variant prices are not valid
            if ($discountedPrice <= 0 && $price <= 0) {
                $price = numToDecimal($product->price);
                $discountedPrice = numToDecimal($product->price_discounted);
            }

            $finalPrice = $discountedPrice > 0 ? $discountedPrice : $price;
            $discountRate = $discountedPrice > 0 ? calculateDiscount($price, $discountedPrice) : 0;

            $variant['price'] = priceFormatted($price, $product->currency, true);
            $variant['price_discounted'] = priceFormatted($discountedPrice, $product->currency, true);
            $variant['final_variant_price'] = priceFormatted($finalPrice, $product->currency, true);
            $variant['discount_rate'] = $discountRate;
        }

        unset($variant);

        return $productOptionsData;
    }


    /**
     * Check if there are any images associated with the active variant's selected option values.
     *
     * @param object $initialVariant The currently selected or initial product variant object.
     * @param array $productOptionsData Formatted product options data (decoded array).
     * @return bool True if images are found for the variant's selected options, false otherwise.
     */
    public function hasImagesForVariant($initialVariant, $productOptionsData): bool
    {
        $actualStableKey = '';

        // Find the stable_key for the initial variant from the productOptionsData['variants'] array
        if (isset($initialVariant->id) && isset($productOptionsData['variants']) && is_array($productOptionsData['variants'])) {
            foreach ($productOptionsData['variants'] as $variantFromArray) {
                if (isset($variantFromArray['id']) && $variantFromArray['id'] == $initialVariant->id) {
                    if (isset($variantFromArray['stable_key'])) {
                        $actualStableKey = $variantFromArray['stable_key'];
                        break;
                    }
                }
            }
        }

        // If a stable_key is found, check if any associated option values have image_ids
        if (!empty($actualStableKey)) {
            $selectedOptionValueIds = array_map('strval', explode('_', $actualStableKey));

            if (isset($productOptionsData['options']) && is_array($productOptionsData['options'])) {
                foreach ($productOptionsData['options'] as $option) {
                    if (isset($option['values']) && is_array($option['values'])) {
                        foreach ($option['values'] as $value) {
                            $currentValueId = strval($value['value_server_id']);
                            if (in_array($currentValueId, $selectedOptionValueIds) && !empty($value['image_ids'])) {
                                // If any selected option value has non-empty image_ids, return true immediately
                                return true;
                            }
                        }
                    }
                }
            }
        }

        // If no stable_key found, or no associated option values have image_ids, return false
        return false;
    }


    /**
     * Get option values by variant ID
     *
     * @param int|null $variantId The ID of the variant.
     * @return array An array of option value objects.
     */
    public function getOptionValuesByVariantId(?int $variantId): array
    {
        if (empty($variantId)) {
            return [];
        }

        return $this->db->table('product_option_variant_values pvov')->select('pov.*, po.option_name_translations')
            ->join('product_option_values pov', 'pvov.value_id = pov.id', 'inner')->join('product_options po', 'pov.option_id = po.id', 'inner')
            ->where('pvov.variant_id', $variantId)->orderBy('po.display_order', 'ASC')->orderBy('pov.display_order', 'ASC')->get()->getResultObject();
    }

    /**
     * Get all images of variant
     *
     * @param int $variantId The ID of the variant.
     * @return array An array of unique image IDs.
     */
    public function getVariantImageIds(int $variantId): array
    {
        $result = $this->db->table('product_option_variant_values as vv')->select('v.gallery_image_ids')->join('product_option_values as v', 'v.id = vv.value_id')
            ->where('vv.variant_id', $variantId)->get()->getResultObject();

        $imageIds = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                if (!empty($row->gallery_image_ids)) {
                    $galleryIds = json_decode($row->gallery_image_ids, true);
                    if (is_array($galleryIds)) {
                        $imageIds = array_merge($imageIds, array_map('strval', $galleryIds));
                    }
                }
            }
        }

        return array_unique($imageIds);
    }

    /**
     * Create a JSON snapshot of a variant's and any extra options' crucial data for storing in an order.
     *
     * @param int|null $variantId The ID of the variant to snapshot.
     * @param array|null $extraOptionsData Extra options data posted from the form, structured as [option_id => value(s)].
     * @return string|null A JSON-encoded string of the snapshot data, or null if the variant is not found.
     */
    public function getVariantSnapshot(?int $variantId, ?array $extraOptionsData = []): ?string
    {
        if (empty($variantId) && empty($extraOptionsData)) {
            return null;
        }

        $variant = null;

        if (!empty($variantId)) {
            $variant = $this->db->table('product_option_variants')->where('id', $variantId)->get()->getRow();
        }

        if (!empty($variantId) && !$variant && empty($extraOptionsData)) {
            return null;
        }

        // Initialize default values (assuming no variant)
        $price = 0;
        $priceDiscounted = null;
        $discountRate = 0;
        $variantHash = null;
        $sku = null;

        // Populate values if variant exists
        if ($variant) {
            $variantHash = $variant->variant_hash;
            $sku = $variant->sku;
            $price = (int)$variant->price;
            $priceDiscounted = $variant->price_discounted !== null ? (int)$variant->price_discounted : null;

            if ($price > 0 && $priceDiscounted !== null && $priceDiscounted < $price) {
                $discountRate = (int)round((($price - $priceDiscounted) / $price) * 100);
            }
        }

        $snapshotData = [
            'variant_hash' => $variantHash,
            'sku' => $sku,
            'price' => $price,
            'price_discounted' => $priceDiscounted,
            'discount_rate' => $discountRate,
            'image_id' => null,
            'color_code' => null,
            'options' => []
        ];

        // Process VARIANT options (Only if variant exists)
        if ($variant) {
            $selectedValues = $this->getOptionValuesByVariantId($variantId);

            foreach ($selectedValues as $value) {
                // Find the representative image ID for this variant, but only do it once.
                if ($snapshotData['image_id'] === null) {
                    $imageIdToFetch = null;
                    if (!empty($value->primary_swatch_image_id)) {
                        $imageIdToFetch = $value->primary_swatch_image_id;
                    } else {
                        $galleryIds = json_decode($value->gallery_image_ids, true);
                        if (is_array($galleryIds) && !empty($galleryIds)) {
                            $imageIdToFetch = $galleryIds[0];
                        }
                    }

                    if ($imageIdToFetch) {
                        $snapshotData['image_id'] = (int)$imageIdToFetch;
                    }
                }

                // Find the representative color code, but only do it once.
                if ($snapshotData['color_code'] === null && !empty($value->color_code)) {
                    $snapshotData['color_code'] = $value->color_code;
                }

                // Add the option and value names to the snapshot
                $snapshotData['options'][] = [
                    'optionNames' => $this->decodeJsonSafely($value->option_name_translations, []),
                    'valueNames' => $this->decodeJsonSafely($value->value_name_translations, [])
                ];
            }
        }

        // Process EXTRA options (e.g., text inputs, checkboxes)
        if (!empty($extraOptionsData)) {
            foreach ($extraOptionsData as $optionId => $submittedValue) {
                $optionInfo = $this->db->table('product_options')->where('id', $optionId)->get()->getRow();
                if (!$optionInfo) continue; // Skip if option ID is invalid

                $optionNameTranslations = $this->decodeJsonSafely($optionInfo->option_name_translations, []);

                // Handle checkboxes (multiple values for one option)
                if (is_array($submittedValue)) {
                    foreach ($submittedValue as $valueId) {
                        $valueInfo = $this->db->table('product_option_values')->where('id', $valueId)->get()->getRow();
                        if ($valueInfo) {
                            $snapshotData['options'][] = [
                                'optionNames' => $optionNameTranslations,
                                'valueNames' => $this->decodeJsonSafely($valueInfo->value_name_translations, [])
                            ];
                        }
                    }
                } else {
                    // Handle text, number, etc. (single value)
                    if (!empty($submittedValue)) {
                        $snapshotData['options'][] = [
                            'optionNames' => $optionNameTranslations,
                            'userInput' => $submittedValue
                        ];
                    }
                }
            }
        }

        return json_encode($snapshotData, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Take a raw extra_options array and converts it into a canonical, sorted JSON string for reliable comparison.
     *
     * @param array|null $options The raw array, e.g., from inputPost('extra_options').
     * @return string A stable, sorted JSON string. Returns an empty JSON object '{}' if input is empty.
     */
    public function getCanonicalExtraOptionsJson(?array $options): string
    {
        if (empty($options) || !is_array($options)) {
            return '{}';
        }

        // Remove any options that have an empty value ('', null, [])
        $filteredOptions = array_filter($options, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });

        // Sort the main array by its keys to ensure order ('option_1', 'option_2', etc.)
        ksort($filteredOptions);

        // If any value is itself an array (like from checkboxes), sort it as well.
        foreach ($filteredOptions as &$value) {
            if (is_array($value)) {
                sort($value, SORT_STRING); // Sorts checkbox values, e.g. ['a', 'c', 'b'] becomes ['a', 'b', 'c']
            }
        }
        unset($value);

        // Convert the clean, sorted array into a JSON string. This is our "fingerprint".
        return json_encode($filteredOptions);
    }

    /**
     * Helper to decode JSON safely, returning a default if decoding fails or is empty
     *
     * @param string|null $jsonString
     * @param array $default Default value if JSON is invalid or empty.
     * @return array
     */
    private function decodeJsonSafely(?string $jsonString, array $default = ['en' => '']): array
    {
        if ($jsonString === null) {
            return $default;
        }
        $decoded = json_decode($jsonString, true);
        return is_array($decoded) && !empty($decoded) ? $decoded : $default;
    }

    /**
     * Generate a unique and consistent hash from a set of IMMUTABLE, system-generated keys.
     *
     * @param array $optionsKeysArray An associative array of immutable keys (e.g., ['opt_a1b2c3d4' => 'val_e5f6g7h8']).
     * @return string A unique hash for this combination.
     */
    public function generateVariantHash(array $optionsKeysArray): string
    {
        if (empty($optionsKeysArray)) {
            return '';
        }
        ksort($optionsKeysArray);
        $jsonString = json_encode($optionsKeysArray);
        return hash('sha256', $jsonString);
    }


    /**
     * Copies a product option, its values, and all associated variants to another product
     *
     * @param int $sourceOptionId The ID of the `product_options` row to copy.
     * @param int $targetProductId The ID of the product to copy the option to.
     * @return array An associative array with 'success' (boolean) and 'message' (string).
     */
    function copyProductOption(int $sourceOptionId, int $targetProductId): array
    {
        $product = $this->db->table('products')->where('id', $targetProductId)->get()->getRow();
        if (empty($product) || $product->user_id != user()->id) {
            return false;
        }

        $this->db->transStart();

        $sourceOption = $this->db->table('product_options')->where('id', $sourceOptionId)->get()->getRowArray();
        if (empty($sourceOption)) {
            return false;
        }

        // Create a new option
        $newOptionData = [
            'product_id' => $product->id,
            'option_name_translations' => $sourceOption['option_name_translations'],
            'option_type' => $sourceOption['option_type'],
            'display_order' => $sourceOption['display_order'],
            'is_active' => $sourceOption['is_active'],
            'option_key' => $sourceOption['option_key']
        ];
        $this->db->table('product_options')->insert($newOptionData);
        $targetOptionId = $this->db->insertID();

        // Fetch all values from the source option and create copies for the new target option
        $sourceValues = $this->db->table('product_option_values')->where('option_id', $sourceOptionId)->orderBy('id', 'ASC')->get()->getResultArray();

        $valueIdMap = []; // This map will hold [sourceValueId => targetValueId]
        foreach ($sourceValues as $sourceValue) {
            $newValueData = [
                'option_id' => $targetOptionId,
                'value_name_translations' => $sourceValue['value_name_translations'],
                'color_code' => $sourceValue['color_code'],
                'primary_swatch_image_id' => $sourceValue['primary_swatch_image_id'],
                'gallery_image_ids' => $sourceValue['gallery_image_ids'],
                'display_order' => $sourceValue['display_order'],
                'value_key' => $sourceValue['value_key']
            ];
            $this->db->table('product_option_values')->insert($newValueData);
            $targetValueId = $this->db->insertID();
            $valueIdMap[$sourceValue['id']] = $targetValueId;
        }

        // If the option had no values, there are no variants to copy
        if (empty($valueIdMap)) {
            $this->db->transComplete();
            return true;
        }

        // Identify all unique variants from the source product that use the values of the option we are copying
        $sourceValueIds = array_keys($valueIdMap);
        $sourceVariantIdsResult = $this->db->table('product_option_variant_values')->distinct()->select('variant_id')->whereIn('value_id', $sourceValueIds)->get()->getResultArray();
        $sourceVariantIds = array_column($sourceVariantIdsResult, 'variant_id');

        // Loop through each source variant and attempt to create a copy for the target product
        $copiedVariantsCount = 0;
        $skippedVariantsCount = 0;
        foreach ($sourceVariantIds as $sourceVariantId) {
            // Get the source variant's main data.
            $sourceVariant = $this->db->table('product_option_variants')->where('id', $sourceVariantId)->get()->getRowArray();

            // b. Get all values (and their keys) that define this variant.
            $sourceVariantValues = $this->db->table('product_option_variant_values povv')
                ->join('product_option_values pov', 'povv.value_id = pov.id')
                ->select('povv.value_id, pov.value_key')
                ->where('povv.variant_id', $sourceVariantId)
                ->get()
                ->getResultArray();

            $targetVariantValueIds = [];
            $canCreateVariant = true;

            // c. Try to map every value of the source variant to a corresponding value on the target product.
            foreach ($sourceVariantValues as $svValue) {
                $oldValueId = $svValue['value_id'];
                $oldValueKey = $svValue['value_key'];

                if (isset($valueIdMap[$oldValueId])) {
                    // This value is part of the option we just copied. We already know its new ID.
                    $targetVariantValueIds[] = $valueIdMap[$oldValueId];
                } else {
                    // This value is from a different option. We must find its equivalent on the target product using its key.
                    $foundTargetValueId = $this->db->table('product_option_values pov')
                        ->join('product_options po', 'pov.option_id = po.id')
                        ->select('pov.id')
                        ->where('po.product_id', $targetProductId)
                        ->where('pov.value_key', $oldValueKey)
                        ->get()
                        ->getRow('id');

                    if ($foundTargetValueId) {
                        $targetVariantValueIds[] = $foundTargetValueId;
                    } else {
                        $canCreateVariant = false;
                        break;
                    }
                }
            }

            // d. If all values were successfully mapped, create the new variant and its value links.
            if ($canCreateVariant && !empty($targetVariantValueIds)) {
                $newVariantData = [
                    'product_id' => $targetProductId,
                    'sku' => $sourceVariant['sku'],
                    'price' => $sourceVariant['price'],
                    'price_discounted' => $sourceVariant['price_discounted'],
                    'quantity' => $sourceVariant['quantity'],
                    'weight' => $sourceVariant['weight'],
                    'is_default' => 0, // Set to 0 to avoid multiple defaults
                    'is_active' => $sourceVariant['is_active'],
                    'variant_hash' => null // variant_hash should be recalculated
                ];
                $this->db->table('product_option_variants')->insert($newVariantData);
                $targetVariantId = $this->db->insertID();

                // Link the new variant to the target values.
                foreach ($targetVariantValueIds as $valueId) {
                    $this->db->table('product_option_variant_values')->insert([
                        'variant_id' => $targetVariantId,
                        'value_id' => $valueId
                    ]);
                }
                $copiedVariantsCount++;
            } else {
                $skippedVariantsCount++;
            }
        }

        // Complete the transaction.
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            // The transaction failed.
            return ['success' => false, 'message' => 'An error occurred during the copy process. The transaction was rolled back.'];
        }

        return [
            'success' => true,
            'message' => "Option copied successfully. New Option ID: {$targetOptionId}. Copied {$copiedVariantsCount} variants. Skipped {$skippedVariantsCount} variants due to missing corresponding values on the target product."
        ];
    }

    /**
     * Generate a language-independent, unique, and immutable key for an option or value.
     *
     * @param string $prefix A prefix to distinguish keys ('opt' or 'val').
     * @return string A cryptographically secure, unique key.
     */
    public function generateImmutableKey(string $prefix = 'key'): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }

    //get variation hash by id
    public function getVariantById($id)
    {
        return $this->db->table('product_option_variants')->where('id', clrNum($id))->get()->getRow();
    }

    //get variation by hash
    public function getVariantByHash($hash)
    {
        return $this->db->table('product_option_variants')->where('variant_hash', cleanStr($hash))->get()->getRow();
    }
}
