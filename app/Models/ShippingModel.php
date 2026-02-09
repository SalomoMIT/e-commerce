<?php namespace App\Models;

class ShippingModel extends BaseModel
{
    protected $builderZones;
    protected $builderZoneLocations;
    protected $buildersMethods;
    protected $buildersDeliveryTimes;

    public function __construct()
    {
        parent::__construct();
        $this->builderZones = $this->db->table('shipping_zones');
        $this->builderZoneLocations = $this->db->table('shipping_zone_locations');
        $this->buildersMethods = $this->db->table('shipping_zone_methods');
        $this->buildersDeliveryTimes = $this->db->table('shipping_delivery_times');
    }

    /*
     * --------------------------------------------------------------------
     * Cart
     * --------------------------------------------------------------------
     */
    
    //get seller shipping methods array
    public function getSellerShippingMethodsArray($cartItems, $stateId, $currencyCode)
    {
        $currency = getCurrencyByCode($currencyCode);
        if (empty($currency)) {
            return [];
        }

        $cartModel = new CartModel();
        // Get unique seller IDs from the items in the cart.
        $sellerIds = $cartModel->getCartSellerIds($cartItems);

        $sellerShippingMethods = [];
        if (empty($sellerIds)) {
            return [];
        }

        foreach ($sellerIds as $sellerId) {
            $seller = getUser($sellerId);
            if (empty($seller)) {
                continue; // Skip if seller data cannot be fetched.
            }

            $item = (object)[
                'shop_id' => $seller->id,
                'total_shipping_cost' => 0,
                'shop_name' => getUsername($seller),
                'methods' => []
            ];

            $zone = $this->getSellerShippingZoneByState($seller->id, $stateId);
            if (empty($zone)) {
                $sellerShippingMethods[] = $item;
                continue;
            }

            $methodsInZone = $this->getShippingZoneMethodsBySeller($zone->id, $seller->id);
            if (empty($methodsInZone)) {
                $sellerShippingMethods[] = $item;
                continue;
            }

            foreach ($methodsInZone as $methodRecord) {
                $method = null;
                $methodName = parseSerializedNameArray($methodRecord->name_array, $this->activeLang->id);

                switch ($methodRecord->method_type) {
                    case 'free_shipping':
                        // Calculate if the cart total for this seller qualifies for free shipping.
                        $sellerTotalAmount = $cartModel->getCartTotalAmountBySeller($cartItems, $seller->id);
                        $totalConverted = convertToDefaultCurrency($sellerTotalAmount, $currencyCode);
                        $minAmount = numToDecimal($methodRecord->free_shipping_min_amount);

                        if ($totalConverted >= $minAmount) {
                            $method = (object)[
                                'method_db_id' => $methodRecord->id,
                                'method' => 'free_shipping',
                                'name' => $methodName,
                                'cost' => 0,
                                'min_amount' => $minAmount,
                                'is_selected' => 0,
                                'order' => 1
                            ];
                        }
                        break;

                    case 'local_pickup':
                        // Create the local pickup method.
                        $method = (object)[
                            'method_db_id' => $methodRecord->id,
                            'method' => 'local_pickup',
                            'name' => $methodName,
                            'cost' => numToDecimal($methodRecord->local_pickup_cost),
                            'is_selected' => 0,
                            'order' => 2
                        ];
                        break;

                    case 'flat_rate':

                        if (!in_array($methodRecord->cost_calculation_type, ['total_weight', 'per_order', 'per_item'])) {
                            continue 2;
                        }

                        // Calculate the flat rate cost based on weight or item count.
                        $totalChargeableWeight = $cartModel->getCartSellerChargeableWeightSum($cartItems, $sellerId);
                        $sellerItemCount = $cartModel->getCartSellerItemCount($cartItems, $sellerId);

                        $cost = $this->calculateFlatRateCost(
                            $methodRecord->cost_calculation_type,
                            $totalChargeableWeight,
                            $sellerItemCount,
                            $methodRecord->shipping_flat_cost,
                            $methodRecord->flat_rate_costs
                        );

                        // Only add the method if a valid cost was calculated.
                        if (isset($cost)) {
                            $method = (object)[
                                'method_db_id' => $methodRecord->id,
                                'zone_id' => $zone->id,
                                'shop_id' => $seller->id,
                                'method' => 'flat_rate',
                                'name' => $methodName,
                                'cost' => numToDecimal($cost),
                                'is_selected' => 0,
                                'order' => 3
                            ];
                        }
                        break;
                }

                if (!empty($method)) {
                    $method->id = md5($methodRecord->id . $seller->id);
                    $item->methods[] = $method;
                }
            }

            if (count($item->methods) > 1) {
                usort($item->methods, function ($a, $b) {
                    return $a->order <=> $b->order;
                });
            }

            $sellerShippingMethods[] = $item;
        }

        return $sellerShippingMethods;
    }

    //get shipping zone by seller and state
    public function getSellerShippingZoneByState($sellerId, $stateId)
    {
        $continentCode = '';
        $countryId = '';
        //get the state
        $state = getState($stateId);
        if (!empty($state)) {
            //get country
            $country = getCountry($state->country_id);
            if (!empty($country)) {
                $countryId = $country->id;
                $continentCode = $country->continent_code;
            }
            //get shipping options by state
            $zoneLocations = array();
            $zoneIds = array();
            if (!empty($state->id)) {
                $zoneLocations = $this->builderZoneLocations->where('state_id', clrNum($state->id))->where('user_id', clrNum($sellerId))->get()->getResult();
            }
            //get shipping options by country
            if (empty($zoneLocations) && countItems($zoneLocations) < 1 && !empty($countryId)) {
                $zoneLocations = $this->builderZoneLocations->where('country_id', clrNum($countryId))->where('state_id', 0)->where('user_id', clrNum($sellerId))->get()->getResult();
            }
            //get shipping options by continent
            if (empty($zoneLocations) && countItems($zoneLocations) < 1 && !empty($continentCode)) {
                $zoneLocations = $this->builderZoneLocations->where('continent_code', cleanStr($continentCode))->where('country_id', 0)->where('state_id', 0)->where('user_id', clrNum($sellerId))->get()->getResult();
            }
            if (!empty($zoneLocations)) {
                foreach ($zoneLocations as $location) {
                    array_push($zoneIds, $location->zone_id);
                }
            }
            //get shipping methods
            if (!empty($zoneIds)) {
                return $this->builderZones->whereIn('id', $zoneIds, FALSE)->where('user_id', clrNum($sellerId))->get()->getFirstRow();
            }
        }
        return array();
    }

    //calculate flat rate cost
    function calculateFlatRateCost(string $calculationType, float $totalWeight, int $totalItems, ?float $flatCost, ?string $ratesJson): ?float
    {
        switch ($calculationType) {

            case 'per_order':
                // For 'per_order', the cost is simply the flat rate, regardless of cart content.
                return is_numeric($flatCost) ? (float)$flatCost : null;

            case 'per_item':
                // For 'per_item', multiply the flat cost by the total number of items.
                if (is_numeric($flatCost) && $totalItems > 0) {
                    return (float)$flatCost * $totalItems;
                }
                return null;

            case 'total_weight':
                // For 'total_weight', use the original logic to find a matching weight range.
                if (empty($ratesJson)) {
                    return null;
                }
                $rates = safeJsonDecode($ratesJson, true);
                if (!is_array($rates)) {
                    return null;
                }

                foreach ($rates as $rate) {
                    // Validate that the essential keys exist and are numeric
                    if (!isset($rate['min_weight'], $rate['cost']) || !is_numeric($rate['min_weight']) || !is_numeric($rate['cost'])) {
                        continue;
                    }

                    $minWeight = (float)$rate['min_weight'];
                    $cost = (float)$rate['cost'];

                    // If max_weight is not set, empty, or null, it means "and above".
                    $maxWeight = (!isset($rate['max_weight']) || $rate['max_weight'] === '' || $rate['max_weight'] === null)
                        ? PHP_FLOAT_MAX
                        : (float)$rate['max_weight'];

                    if ($totalWeight >= $minWeight && $totalWeight <= $maxWeight) {
                        return $cost; // Return the cost of the first matching rule.
                    }
                }
                return null; // No matching weight rule found.

            default:
                // If the calculation type is unknown or not provided, return null.
                return null;
        }
    }

    //get product shipping cost
    public function getProductShippingCost($stateId, $productId)
    {
        $product = getProduct($productId);
        if (!empty($product)) {
            $cartItems = [];
            $cartItem = (object)[
                'product_id' => $product->id,
                'product_type' => $product->product_type,
                'quantity' => 1,
                'seller_id' => $product->user_id,
                'chargeable_weight' => $product->chargeable_weight,
                'total_price' => $product->price_discounted,
            ];
            $cartItems[] = $cartItem;

            $shippingMethods = $this->getSellerShippingMethodsArray($cartItems, $stateId, $this->selectedCurrency->code);

            $hasMethods = false;
            if (!empty($shippingMethods)) {
                foreach ($shippingMethods as $shippingMethod) {
                    if (!empty($shippingMethod->methods) && countItems($shippingMethod->methods) > 0) {
                        $hasMethods = true;
                    }
                }
            }

            $response = '';
            if (!empty($shippingMethods)) {
                foreach ($shippingMethods as $shippingMethod) {
                    if (!empty($shippingMethod->methods)) {
                        foreach ($shippingMethod->methods as $method) {
                            if ($method->method == 'free_shipping') {
                                $response .= "<p><strong class='method-name'>" . esc($method->name) . "</strong><strong>&nbsp(" . trans("minimum_order_amount") . ":&nbsp;" . priceDecimal($method->min_amount, getSelectedCurrency()->code) . ")</strong></p>";
                            } else {
                                $response .= "<p><strong class='method-name'>" . esc($method->name) . "</strong><strong>:&nbsp;" . priceDecimal($method->cost, getSelectedCurrency()->code, true) . "</strong></p>";
                            }
                        }
                    }
                }
            }
            if (empty($response)) {
                $response = '<p class="text-muted">' . trans("product_does_not_ship_location") . '</p>';
            }
            $data = [
                'result' => 1,
                'response' => $response
            ];
            return jsonResponse($data);
        }
        return jsonResponse();
    }

    //get product estimated delivery
    public function getProductEstimatedDelivery($product, $langId)
    {
        if (!empty($product)) {
            $countryId = null;
            $stateId = null;
            if (authCheck()) {
                $countryId = user()->country_id;
                $stateId = user()->state_id;
            } else {
                $location = helperGetSession('mds_estimated_delivery_location');
                if (!empty($location) && !empty($location['country_id']) && !empty($location['state_id'])) {
                    $countryId = $location['country_id'];
                    $stateId = $location['state_id'];
                }
            }
            if (!empty($countryId) && !empty($stateId)) {
                $country = getCountry($countryId);
                $continentCode = '';
                if (!empty($country)) {
                    $continentCode = $country->continent_code;
                }
                $shippingLocations = $this->db->table('shipping_zone_locations')
                    ->select('shipping_zone_locations.*, (SELECT estimated_delivery FROM shipping_zones WHERE shipping_zones.id = shipping_zone_locations.zone_id) AS estimated_delivery')
                    ->where('user_id', $product->user_id)->groupStart()
                    ->orWhere('continent_code', cleanStr($continentCode))->orWhere('country_id', clrNum($countryId))->orWhere('state_id', clrNum($stateId))
                    ->groupEnd()->get()->getResult();
                if (!empty($shippingLocations)) {
                    foreach ($shippingLocations as $location) {
                        if ($location->country_id == $countryId && $location->state_id == $stateId) {
                            return '<span class="result-delivery font-600">' . @parseSerializedNameArray($location->estimated_delivery, $langId) . '</span>';
                        }
                    }
                    foreach ($shippingLocations as $location) {
                        if ($location->country_id == $countryId) {
                            return '<span class="result-delivery font-600">' . @parseSerializedNameArray($location->estimated_delivery, $langId) . '</span>';
                        }
                    }
                    foreach ($shippingLocations as $location) {
                        if ($location->continent_code == $continentCode) {
                            return '<span class="result-delivery font-600">' . @parseSerializedNameArray($location->estimated_delivery, $langId) . '</span>';
                        }
                    }
                }
                return '<span class="result-delivery text-danger">' . trans("no_delivery_this_location") . '</span>';
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Dashboard
     * --------------------------------------------------------------------
     */

    //add shipping zone
    public function addShippingZone()
    {
        $nameArray = array();
        $estDeliveryArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'name' => inputPost('zone_name_lang_' . $language->id)
            ];
            array_push($nameArray, $item);
            $item = [
                'lang_id' => $language->id,
                'name' => inputPost('estimated_delivery_lang_' . $language->id)
            ];
            array_push($estDeliveryArray, $item);
        }
        $data = [
            'name_array' => serialize($nameArray),
            'estimated_delivery' => serialize($estDeliveryArray),
            'user_id' => user()->id
        ];

        if ($this->builderZones->insert($data)) {
            $zoneId = $this->db->insertID();
            //add locations
            $this->addShippingZoneLocations($zoneId);
            return $zoneId;
        }
        return false;
    }

    //add shipping zone locations
    public function addShippingZoneLocations($zoneId)
    {
        $continentCodes = inputPost('continent');
        if (!empty($continentCodes)) {
            foreach ($continentCodes as $continentCode) {
                $array = array_keys(getAppDefault('continents'));
                if (in_array($continentCode, $array)) {
                    //check if already exists
                    $zoneContinent = $this->builderZoneLocations->where('continent_code', cleanStr($continentCode))->where('zone_id', clrNum($zoneId))->get()->getRow();
                    if (empty($zoneContinent)) {
                        $item = [
                            'zone_id' => $zoneId,
                            'user_id' => user()->id,
                            'continent_code' => $continentCode,
                            'country_id' => 0,
                            'state_id' => 0
                        ];
                        $this->builderZoneLocations->insert($item);
                    }
                }
            }
        }
        $countryIds = inputPost('country');
        if (!empty($countryIds)) {
            foreach ($countryIds as $countryId) {
                $country = getCountry($countryId);
                if (!empty($country)) {
                    //check if already exists
                    $zoneCountry = $this->builderZoneLocations->where('country_id', clrNum($countryId))->where('zone_id', clrNum($zoneId))->get()->getRow();
                    if (empty($zoneCountry)) {
                        $item = [
                            'zone_id' => $zoneId,
                            'user_id' => user()->id,
                            'continent_code' => $country->continent_code,
                            'country_id' => $country->id,
                            'state_id' => 0
                        ];
                        $this->builderZoneLocations->insert($item);
                    }
                }
            }
        }
        $stateIds = inputPost('state');
        if (!empty($stateIds)) {
            foreach ($stateIds as $stateId) {
                $state = getState($stateId);
                if (!empty($state)) {
                    $country = getCountry($state->country_id);
                    if (!empty($country)) {
                        //check if already exists
                        $zoneState = $this->builderZoneLocations->where('state_id', clrNum($stateId))->where('zone_id', clrNum($zoneId))->get()->getRow();
                        if (empty($zoneState)) {
                            $item = [
                                'zone_id' => $zoneId,
                                'user_id' => user()->id,
                                'continent_code' => $country->continent_code,
                                'country_id' => $country->id,
                                'state_id' => $state->id
                            ];
                            $this->builderZoneLocations->insert($item);
                        }
                    }
                }
            }
        }
    }

    //edit shipping zone
    public function editShippingZone($zoneId)
    {
        $nameArray = array();
        $estDeliveryArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'name' => inputPost('zone_name_lang_' . $language->id)
            ];
            array_push($nameArray, $item);
            $item = [
                'lang_id' => $language->id,
                'name' => inputPost('estimated_delivery_lang_' . $language->id)
            ];
            array_push($estDeliveryArray, $item);
        }
        $data = [
            'name_array' => serialize($nameArray),
            'estimated_delivery' => serialize($estDeliveryArray),
        ];

        if ($this->builderZones->where('id', clrNum($zoneId))->update($data)) {
            //add locations
            $this->addShippingZoneLocations($zoneId);
            return true;
        }
        return false;
    }

    //get shipping zone
    public function getShippingZone($id)
    {
        return $this->builderZones->where('id', clrNum($id))->get()->getRow();
    }

    //get shipping zones
    public function getShippingZones($userId)
    {
        return $this->builderZones->where('user_id', clrNum($userId))->orderBy('id DESC')->get()->getResult();
    }

    //get shipping locations by zone
    public function getShippingLocationsByZone($zoneId)
    {
        return $this->builderZoneLocations->select("shipping_zone_locations.*, (SELECT name FROM location_countries WHERE location_countries.id = shipping_zone_locations.country_id LIMIT 1) As country_name, 
        (SELECT name FROM location_states WHERE location_states.id = shipping_zone_locations.state_id LIMIT 1) As state_name")->where('zone_id', clrNum($zoneId))->get()->getResult();
    }

    //get shipping methods by zone
    public function getShippingMethodsByZone($zoneId)
    {
        return $this->buildersMethods->where('zone_id', clrNum($zoneId))->get()->getResult();
    }

    //get shipping method
    public function getShippingMethod($id)
    {
        return $this->buildersMethods->where('id', clrNum($id))->get()->getRow();
    }

    //get shippping zone methods
    public function getShippingZoneMethods($zoneId)
    {
        return $this->buildersMethods->where('zone_id', clrNum($zoneId))->where('status', 1)->get()->getResult();
    }

    //get shippping zone methods by seller
    public function getShippingZoneMethodsBySeller($zoneId, $sellerId)
    {
        return $this->buildersMethods->where('zone_id', clrNum($zoneId))->where('user_id', $sellerId)->where('status', 1)->get()->getResult();
    }

    //add shipping method
    public function addShippingMethod($zoneId, $shippingMethod)
    {
        $methods = ['flat_rate', 'local_pickup', 'free_shipping'];
        if (!in_array($shippingMethod, $methods)) {
            $shippingMethod = 'flat_rate';
        }

        $nameArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'name' => trans($shippingMethod)
            ];
            array_push($nameArray, $item);
        }

        $data = [
            'name_array' => serialize($nameArray),
            'zone_id' => $zoneId,
            'user_id' => user()->id,
            'method_type' => $shippingMethod
        ];

        return $this->buildersMethods->insert($data);
    }

    //edit shipping method
    public function editShippingMethod($methodId)
    {
        $method = $this->getShippingMethod($methodId);
        if (empty($method) || $method->user_id != user()->id) {
            return false;
        }

        $nameArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'name' => inputPost('method_name_' . $methodId . '_lang_' . $language->id)
            ];
            array_push($nameArray, $item);
        }

        $data = [
            'status' => inputPost('status') ? 1 : 0,
            'name_array' => serialize($nameArray),
            'free_shipping_min_amount' => numToDecimal(inputPost('free_shipping_min_amount')),
            'local_pickup_cost' => numToDecimal(inputPost('local_pickup_cost')),
            'shipping_flat_cost' => numToDecimal(inputPost('shipping_flat_cost')),
            'cost_calculation_type' => inputPost('cost_calculation_type'),
        ];

        if (!in_array($data['cost_calculation_type'], ['total_weight', 'per_order', 'per_item'])) {
            $data['cost_calculation_type'] = 'total_weight';
        }

        $ratesData = inputPost('rates');
        $ratesJson = '';
        if (!empty($ratesData) && is_array($ratesData)) {
            $cleanRates = array_filter($ratesData, function ($rate) {
                return isset($rate['cost']) && $rate['cost'] !== '';
            });
            $cleanRates = array_values($cleanRates);
            $ratesJson = safeJsonEncode($cleanRates);
        }

        $data['flat_rate_costs'] = $ratesJson;

        return $this->buildersMethods->where('id', $method->id)->update($data);
    }

    //delete shipping method
    public function deleteShippingMethod($methodId)
    {
        $method = $this->getShippingMethod($methodId);
        if (empty($method) || $method->user_id != user()->id) {
            return false;
        }

        return $this->buildersMethods->where('id', $method->id)->delete();
    }

    //add shipping delivery time
    public function addShippingDeliveryTime()
    {
        $optionArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'option' => inputPost('option_lang_' . $language->id)
            ];
            array_push($optionArray, $item);
        }
        $data = [
            'user_id' => user()->id,
            'option_array' => serialize($optionArray)
        ];
        return $this->buildersDeliveryTimes->insert($data);
    }

    //edit shipping delivery time
    public function editShippingDeliveryTime($id)
    {
        $row = $this->getShippingDeliveryTime($id);
        if (empty($row) || $row->user_id != user()->id) {
            return false;
        }
        $optionArray = array();
        foreach ($this->activeLanguages as $language) {
            $item = [
                'lang_id' => $language->id,
                'option' => inputPost('option_lang_' . $language->id, true)
            ];
            array_push($optionArray, $item);
        }
        $data = [
            'option_array' => serialize($optionArray)
        ];
        return $this->buildersDeliveryTimes->where('id', $row->id)->update($data);
    }

    //get shipping delivery times
    public function getShippingDeliveryTimes($userId, $sort = '')
    {
        $this->buildersDeliveryTimes->where('user_id', clrNum($userId));
        if (!empty($sort)) {
            $this->buildersDeliveryTimes->orderBy('id DESC');
        } else {
            $this->buildersDeliveryTimes->orderBy('id');
        }
        return $this->buildersDeliveryTimes->get()->getResult();
    }

    //get shipping delivery time
    public function getShippingDeliveryTime($id)
    {
        return $this->buildersDeliveryTimes->where('id', clrNum($id))->get()->getRow();
    }

    //delete shipping location
    public function deleteShippingLocation($id)
    {
        if (!authCheck()) {
            return false;
        }

        $row = $this->builderZoneLocations->join('shipping_zones', 'shipping_zones.id = shipping_zone_locations.zone_id')->select('shipping_zone_locations.*')
            ->where('shipping_zone_locations.id', clrNum($id))->where('shipping_zones.user_id', user()->id)->get()->getRow();
        if (!empty($row)) {
            return $this->builderZoneLocations->where('id', clrNum($id))->delete();
        }
        return false;
    }

    //delete shipping delivery time
    public function deleteShippingDeliveryTime($id)
    {
        $row = $this->getShippingDeliveryTime($id);
        if (!empty($row) && $row->user_id == user()->id) {
            return $this->buildersDeliveryTimes->where('id', clrNum($id))->delete();
        }
        return false;
    }

    //delete shipping zone
    public function deleteShippingZone($id)
    {
        $row = $this->builderZones->where('shipping_zones.id', clrNum($id))->where('shipping_zones.user_id', user()->id)->get()->getRow();
        if (!empty($row)) {
            //delete locations
            $this->builderZoneLocations->where('zone_id', clrNum($id))->delete();
            //delete zone
            $this->builderZones->where('id', clrNum($id))->delete();
        }
    }

    //calculate the chargeable weight for shipping
    function calculateChargeableWeight(?array $data): float
    {
        if (empty($data)) {
            return 0.0;
        }

        $actualWeight = (float)($data['weight'] ?? 0.0);
        $length = (float)($data['length'] ?? 0.0);
        $width = (float)($data['width'] ?? 0.0);
        $height = (float)($data['height'] ?? 0.0);

        // calculate the volumetric weight (desi).
        $desi = 0.0;
        if ($length > 0 && $width > 0 && $height > 0) {
            $volumetricWeight = ($length * $width * $height) / SHIPPING_VOLUMETRIC_DIVISOR;
            $desi = $volumetricWeight;
        }

        return max($actualWeight, $desi);
    }
}