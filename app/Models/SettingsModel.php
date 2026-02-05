<?php namespace App\Models;

class SettingsModel extends BaseModel
{
    protected $builder;
    protected $builderGeneral;
    protected $builderFonts;
    protected $builderPaymentSettings;
    protected $builderPaymentGateways;
    protected $builderProductSettings;
    protected $builderTaxes;
    protected $builderRoutes;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('settings');
        $this->builderGeneral = $this->db->table('general_settings');
        $this->builderFonts = $this->db->table('fonts');
        $this->builderPaymentSettings = $this->db->table('payment_settings');
        $this->builderPaymentGateways = $this->db->table('payment_gateways');
        $this->builderProductSettings = $this->db->table('product_settings');
        $this->builderTaxes = $this->db->table('taxes');
        $this->builderRoutes = $this->db->table('routes');
    }

    //edit homepage manager settings
    public function editHomepageManagerSettings()
    {
        $data = [
            'featured_categories' => inputPost('featured_categories'),
            'index_promoted_products' => inputPost('index_promoted_products'),
            'index_latest_products' => inputPost('index_latest_products'),
            'index_blog_slider' => inputPost('index_blog_slider'),
            'index_products_per_row' => inputPost('index_products_per_row') == 5 ? 5 : 6,
            'index_promoted_products_count' => inputPost('index_promoted_products_count'),
            'index_latest_products_count' => inputPost('index_latest_products_count')
        ];
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update settings
    public function updateSettings()
    {
        $data = [
            'site_title' => inputPost('site_title'),
            'homepage_title' => inputPost('homepage_title'),
            'site_description' => inputPost('site_description'),
            'keywords' => inputPost('keywords'),
            'about_footer' => inputPost('about_footer'),
            'contact_text' => inputPost('contact_text'),
            'contact_address' => inputPost('contact_address'),
            'contact_email' => inputPost('contact_email'),
            'contact_phone' => inputPost('contact_phone'),
            'copyright' => inputPost('copyright'),
            'cookies_warning' => inputPost('cookies_warning'),
            'cookies_warning_text' => inputPost('cookies_warning_text'),
            'bulk_upload_documentation' => inputPost('bulk_upload_documentation')
        ];

        $social = $this->getSocialMediaData(false);
        $data['social_media_data'] = !empty($social) ? serialize($social) : '';

        $langId = inputPost('lang_id');
        $language = getLanguage($langId);
        if (!empty($language)) {
            return $this->builder->where('lang_id', $language->id)->update($data);
        }
        return false;
    }

    //get socail social media links
    public function getSocialMediaData($personalWebsite = true)
    {
        $data = array();
        if ($personalWebsite == true && !empty(inputPost('personal_website_url'))) {
            $data['personal_website_url'] = addHttpsToUrl(trim(inputPost('personal_website_url')));
        }
        $socialArray = getSocialLinksArray();
        foreach ($socialArray as $item) {
            $inputValue = inputPost($item['inputName']);
            if (!empty($inputValue)) {
                $inputValue = trim($inputValue);
                if (!empty($inputValue)) {
                    $data[$item['inputName']] = addHttpsToUrl($inputValue);
                }
            }
        }
        return $data;
    }

    //update general settings
    public function updateGeneralSettings()
    {
        $data = [
            'application_name' => inputPost('application_name'),
            'custom_header_codes' => inputPost('custom_header_codes'),
            'custom_footer_codes' => inputPost('custom_footer_codes'),
            'facebook_comment_status' => inputPost('facebook_comment_status'),
            'facebook_comment' => inputPost('facebook_comment')
        ];
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update captcha settings
    public function updateCloudflareTurnstileSettings()
    {
        $data = [
            'turnstile_status' => inputPost('turnstile_status'),
            'turnstile_site_key' => inputPost('turnstile_site_key'),
            'turnstile_secret_key' => inputPost('turnstile_secret_key')
        ];

        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update maintenance mode settings
    public function updateMaintenanceModeSettings()
    {
        $data = [
            'maintenance_mode_title' => inputPost('maintenance_mode_title'),
            'maintenance_mode_description' => inputPost('maintenance_mode_description'),
            'maintenance_mode_status' => inputPost('maintenance_mode_status'),
        ];
        if (empty($data['maintenance_mode_status'])) {
            $data['maintenance_mode_status'] = 0;
        }
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update email settings
    public function updateEmailSettings()
    {
        $data = processFormData('email');
        $serialized = serialize($data);
        return $this->builderGeneral->where('id', 1)->update(['email_settings' => $serialized]);
    }

    //update email options
    public function updateEmailOptions()
    {
        $data = [
            'email_verification' => inputPost('email_verification'),
            'mail_options_account' => inputPost('mail_options_account')
        ];
        $dataOptions = [
            'new_product' => !empty(inputPost('new_product')) ? 1 : 0,
            'new_order' => !empty(inputPost('new_order')) ? 1 : 0,
            'order_shipped' => !empty(inputPost('order_shipped')) ? 1 : 0,
            'contact_messages' => !empty(inputPost('contact_messages')) ? 1 : 0,
            'shop_opening_request' => !empty(inputPost('shop_opening_request')) ? 1 : 0,
            'bidding_system' => !empty(inputPost('bidding_system')) ? 1 : 0,
            'support_system' => !empty(inputPost('support_system')) ? 1 : 0
        ];
        $data['email_options'] = serialize($dataOptions);
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update social login
    public function updateSocialLoginSettings($submit)
    {
        if ($submit == 'facebook') {
            $data = [
                'facebook_app_id' => inputPost('facebook_app_id'),
                'facebook_app_secret' => inputPost('facebook_app_secret')
            ];
        }
        if ($submit == 'google') {
            $data = [
                'google_client_id' => inputPost('google_client_id'),
                'google_client_secret' => inputPost('google_client_secret')
            ];
        }
        if ($submit == 'vk') {
            $data = [
                'vk_app_id' => inputPost('vk_app_id'),
                'vk_secure_key' => inputPost('vk_secure_key')
            ];
        }
        if (!empty($data)) {
            return $this->builderGeneral->where('id', 1)->update($data);
        }
        return false;
    }

    //update seo tools
    public function updateSeoTools()
    {
        $data = [
            'google_analytics' => inputPost('google_analytics')
        ];
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update pricing settings
    public function updateFeaturedProductsPricingSettings()
    {
        $data = [
            'price_per_day' => numToDecimal(inputPost('price_per_day')),
            'price_per_month' => numToDecimal(inputPost('price_per_month')),
            'free_product_promotion' => inputPost('free_product_promotion')
        ];
        return $this->builderPaymentSettings->where('id', 1)->update($data);
    }

    //update preferences
    public function updatePreferences($form)
    {
        if ($form == 'homepage') {
            $data = [
                'index_promoted_products' => inputPost('index_promoted_products'),
            ];
        } elseif ($form == 'system') {

            $physicalProductsSystem = inputPost('physical_products_system');
            $digitalProductsSystem = inputPost('digital_products_system');
            if (empty($physicalProductsSystem) && empty($digitalProductsSystem)) {
                setErrorMessage(trans("msg_error_product_type"));
                redirectToBackUrl();
                exit();
            }

            $data = [
                'physical_products_system' => $physicalProductsSystem,
                'digital_products_system' => $digitalProductsSystem,
                'marketplace_system' => inputPost('marketplace_system'),
                'classified_ads_system' => inputPost('classified_ads_system'),
                'bidding_system' => inputPost('bidding_system'),
                'selling_license_keys_system' => inputPost('selling_license_keys_system'),
                'multi_vendor_system' => inputPost('multi_vendor_system'),
                'timezone' => trim(inputPost('timezone'))
            ];
        } elseif ($form == 'general') {
            $data = [
                'multilingual_system' => inputPost('multilingual_system'),
                'rss_system' => inputPost('rss_system'),
                'vendor_verification_system' => inputPost('vendor_verification_system'),
                'show_vendor_contact_information' => inputPost('show_vendor_contact_information'),
                'show_vendor_contact_info_guests' => inputPost('show_vendor_contact_info_guests'),
                'guest_checkout' => inputPost('guest_checkout'),
                'location_search_header' => inputPost('location_search_header'),
                'pwa_status' => inputPost('pwa_status')
            ];

            //pwa logo
            $uploadModel = new UploadModel();
            $tempFile = $uploadModel->uploadTempFile('pwa_logo');
            if (!empty($tempFile) && !empty($tempFile['path'])) {
                $pwaLogo = $this->generalSettings->pwa_logo;
                if (!empty($pwaLogo)) {
                    $pwaLogoArr = unserializeData($pwaLogo);
                    if (!empty($pwaLogoArr) && countItems($pwaLogoArr) > 0) {
                        if (!empty($pwaLogoArr['lg'])) {
                            deleteStorageFile($pwaLogoArr['lg']);
                        }
                        if (!empty($pwaLogoArr['md'])) {
                            deleteStorageFile($pwaLogoArr['md']);
                        }
                        if (!empty($pwaLogoArr['sm'])) {
                            deleteStorageFile($pwaLogoArr['sm']);
                        }
                    }
                }
                $newLogo = $uploadModel->uploadPwaLogo($tempFile['path']);
                $data['pwa_logo'] = serialize($newLogo);
            }
        } elseif ($form == 'products') {
            $data = [
                'approve_before_publishing' => inputPost('approve_before_publishing'),
                'approve_after_editing' => inputPost('approve_after_editing'),
                'promoted_products' => inputPost('promoted_products'),
                'vendor_bulk_product_upload' => inputPost('vendor_bulk_product_upload'),
                'show_sold_products' => inputPost('show_sold_products'),
                'product_link_structure' => inputPost('product_link_structure'),
                'reviews' => inputPost('reviews'),
                'product_comments' => inputPost('product_comments'),
                'blog_comments' => inputPost('blog_comments'),
                'comment_approval_system' => inputPost('comment_approval_system')
            ];
        } elseif ($form == 'shop') {
            $data = [
                'refund_system' => inputPost('refund_system'),
                'profile_number_of_sales' => inputPost('profile_number_of_sales'),
                'vendors_change_shop_name' => inputPost('vendors_change_shop_name'),
                'show_customer_email_seller' => inputPost('show_customer_email_seller'),
                'show_customer_phone_seller' => inputPost('show_customer_phone_seller'),
                'auto_approve_orders' => inputPost('auto_approve_orders'),
                'request_documents_vendors' => inputPost('request_documents_vendors')
            ];
            if (!empty($data['auto_approve_orders'])) {
                $data['auto_approve_orders_days'] = inputPost('auto_approve_orders_days');
            }
            if (!empty($data['request_documents_vendors'])) {
                $data['explanation_documents_vendors'] = inputPost('explanation_documents_vendors');
            }
        } elseif ($form == 'wallet') {
            $data = [
                'wallet_status' => inputPost('wallet_status'),
                'wallet_deposit' => inputPost('wallet_deposit'),
                'pay_with_wallet_balance' => inputPost('pay_with_wallet_balance'),
                'wallet_min_deposit' => numToDecimal(inputPost('wallet_min_deposit'))
            ];
            return $this->builderPaymentSettings->where('id', 1)->update($data);
        } elseif ($form == 'file_upload') {
            $data = [
                'image_file_format' => inputPost('image_file_format'),
                'is_product_image_required' => !empty(inputPost('is_product_image_required')) ? 1 : 0,
                'product_image_limit' => inputPost('product_image_limit'),
                'max_file_size_image' => inputPost('max_file_size_image') * 1048576,
                'max_file_size_video' => inputPost('max_file_size_video') * 1048576,
                'max_file_size_audio' => inputPost('max_file_size_audio') * 1048576,
            ];
            return $this->builderProductSettings->where('id', 1)->update($data);
        }
        if (!empty($data)) {
            return $this->builderGeneral->where('id', 1)->update($data);
        }
    }

    //update ai writer settings
    public function updateAIWriterSettings()
    {
        $data = [
            'status' => inputPost('status'),
            'api_key' => inputPost('api_key'),
            'temperature' => inputPost('temperature')
        ];
        return $this->builderGeneral->where('id', 1)->update(['ai_writer' => serialize($data)]);
    }

    //update visual settings
    public function updateVisualSettings()
    {
        $data = ['site_color' => inputPost('site_color')];

        $uploadModel = new UploadModel();
        $logo = $uploadModel->uploadLogo('logo');
        if (!empty($logo) && !empty($logo['path'])) {
            deleteStorageFile($this->generalSettings->logo);
            $data['logo'] = $logo['path'];
        }

        $logoEmail = $uploadModel->uploadLogo('logo_email');
        if (!empty($logoEmail) && !empty($logoEmail['path'])) {
            deleteStorageFile($this->generalSettings->logo_email);
            $data['logo_email'] = $logoEmail['path'];
        }

        $favicon = $uploadModel->uploadFavicon('favicon');
        if (!empty($favicon) && !empty($favicon['path'])) {
            deleteStorageFile($this->generalSettings->favicon);
            $data['favicon'] = $favicon['path'];
        }

        $data['logo_size'] = '';
        $logoWidth = inputPost('logo_width');
        $logoHeight = inputPost('logo_height');
        if (!empty($logoWidth)) {
            $logoWidth = intval($logoWidth);
            if (intval($logoWidth) < 10 || intval($logoWidth) > 300) {
                $logoWidth = 160;
            }
            $data['logo_size'] .= $logoWidth;
        }
        if (!empty($logoHeight)) {
            $logoHeight = intval($logoHeight);
            if (intval($logoHeight) < 10 || intval($logoHeight) > 300) {
                $logoWidth = 60;
            }
            $data['logo_size'] .= 'x' . $logoHeight;
        }

        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update watermark settings
    public function updateWatermarkSettings()
    {
        $data = processFormData('watermark');
        $serialized = serialize($data);
        return $this->builderGeneral->where('id', 1)->update(['watermark_settings' => $serialized]);
    }

    //update cache system
    public function updateCacheSystem()
    {
        if (inputPost('action') == 'save_static') {
            $data = [
                'static_cache_system' => inputPost('static_cache_system'),
                'category_cache_system' => inputPost('category_cache_system')
            ];
        } else {
            $data = [
                'cache_system' => inputPost('cache_system'),
                'refresh_cache_database_changes' => inputPost('refresh_cache_database_changes'),
                'cache_refresh_time' => inputPost('cache_refresh_time') * 60
            ];
        }
        return $this->builderGeneral->where('id', 1)->update($data);
    }

    //update storage settings
    public function updateStorageSettings()
    {
        $data = processFormData('storage');
        $serialized = serialize($data);
        return $this->builderGeneral->where('id', 1)->update(['storage_settings' => $serialized]);
    }

    //get routes
    public function getRoutes()
    {
        return $this->builderRoutes->get()->getResult();
    }

    //get route by key
    public function getRouteByKey($key)
    {
        return $this->builderRoutes->where('route_key', cleanStr($key))->get()->getRow();
    }

    //update route settings
    public function updateRouteSettings()
    {
        $routes = $this->getRoutes();
        if (!empty($routes)) {
            foreach ($routes as $route) {
                $data = [
                    'route' => inputPost('route_' . $route->id)
                ];
                $this->builderRoutes->where('id', $route->id)->update($data);
            }
        }
        return true;
    }

    //edit theme
    public function editTheme()
    {
        if (inputPost('submit') == 'nav') {
            $data = [
                'menu_limit' => inputPost('menu_limit'),
                'selected_navigation' => inputPost('selected_navigation') == 2 ? 2 : 1
            ];
        } elseif (inputPost('submit') == 'cat') {
            $data = [
                'fea_categories_design' => inputPost('fea_categories_design') == 'grid_layout' ? 'grid_layout' : 'round_boxes'
            ];
        } elseif (inputPost('submit') == 'img_mode') {
            $data = [
                'product_img_display_mode' => inputPost('product_img_display_mode') == 'cover' ? 'cover' : 'full_image'
            ];
        }

        if (!empty($data)) {
            return $this->builderGeneral->where('id', 1)->update($data);
        }
        return false;
    }

    //get settings
    public function getSettings($langId)
    {
        return $this->builder->where('lang_id', clrNum($langId))->get()->getRow();
    }

    //update product settings
    public function updateProductSettings()
    {
        $submit = inputPost('submit');
        if ($submit == 'search_listing') {
            $data = [
                'sort_by_featured_products' => !empty(inputPost('sort_by_featured_products')) ? 1 : 0,
                'pagination_per_page' => inputPost('pagination_per_page')
            ];
            if ($data['pagination_per_page'] < 4 || $data['pagination_per_page'] > 1000) {
                $data['pagination_per_page'] = 40;
            }

        } elseif ($submit == 'marketplace') {
            $data = [
                'marketplace_sku' => getCheckboxValue(inputPost('marketplace_sku')),
                'marketplace_variations' => getCheckboxValue(inputPost('marketplace_variations')),
                'marketplace_shipping' => getCheckboxValue(inputPost('marketplace_shipping')),
                'marketplace_product_location' => getCheckboxValue(inputPost('marketplace_product_location'))
            ];
        } elseif ($submit == 'classified_ads') {
            $data = [
                'classified_price' => getCheckboxValue(inputPost('classified_price')),
                'classified_price_required' => getCheckboxValue(inputPost('classified_price_required')),
                'classified_product_location' => getCheckboxValue(inputPost('classified_product_location')),
                'classified_external_link' => getCheckboxValue(inputPost('classified_external_link'))
            ];
        } elseif ($submit == 'physical_products') {
            $data = [
                'physical_demo_url' => getCheckboxValue(inputPost('physical_demo_url')),
                'physical_video_preview' => getCheckboxValue(inputPost('physical_video_preview')),
                'physical_audio_preview' => getCheckboxValue(inputPost('physical_audio_preview'))
            ];
        } elseif ($submit == 'digital_products') {
            $data = [
                'digital_demo_url' => getCheckboxValue(inputPost('digital_demo_url')),
                'digital_video_preview' => getCheckboxValue(inputPost('digital_video_preview')),
                'digital_audio_preview' => getCheckboxValue(inputPost('digital_audio_preview')),
                'digital_external_link' => getCheckboxValue(inputPost('digital_external_link')),
                'digital_allowed_file_extensions' => ''
            ];


            $exts = inputPost('digital_allowed_file_extensions');
            $extsDecoded = json_decode($exts, true);
            $extArray = [];

            if (is_array($extsDecoded)) {
                foreach ($extsDecoded as $item) {
                    if (isset($item['value']) && is_string($item['value'])) {
                        $extArray[] = trim($item['value']);
                    }
                }
            }

            if (!empty($extArray)) {
                $data['digital_allowed_file_extensions'] = implode(',', $extArray);
            }
        }
        if (!empty($data)) {
            return $this->builderProductSettings->where('id', 1)->update($data);
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Payment Settings
     * --------------------------------------------------------------------
     */

    //update payment gateway
    public function updatePaymentGateway($nameKey)
    {
        if ($nameKey == 'bank_transfer') {
            $data = [
                'bank_transfer_enabled' => inputPost('bank_transfer_enabled'),
                'bank_transfer_accounts' => inputPost('bank_transfer_accounts')
            ];
            return $this->builderPaymentSettings->where('id', 1)->update($data);
        } elseif ($nameKey == 'cash_on_delivery') {
            $data = [
                'cash_on_delivery_enabled' => inputPost('cash_on_delivery_enabled'),
                'cash_on_delivery_debt_limit' => numToDecimal(inputPost('cash_on_delivery_debt_limit'))
            ];
            return $this->builderPaymentSettings->where('id', 1)->update($data);
        } else {
            $gateway = $this->getPaymentGateway($nameKey);
            if (!empty($gateway)) {
                $data = [
                    'public_key' => inputPost('public_key'),
                    'secret_key' => inputPost('secret_key'),
                    'environment' => !empty(inputPost('environment')) ? inputPost('environment') : 'production',
                    'status' => !empty(inputPost('status')) ? 1 : 0,
                    'transaction_fee' => inputPost('transaction_fee')
                ];

                if (!empty(inputPost('webhook_secret'))) {
                    $data['webhook_secret'] = inputPost('webhook_secret');
                }

                return $this->builderPaymentGateways->where('name_key', cleanStr($nameKey))->update($data);
            }
            return false;
        }
        return false;
    }

    //add mercado pago if not exists
    public function addMercadoPago()
    {
        $row = $this->builderPaymentGateways->where('name_key', 'mercado_pago')->get()->getRow();
        if (empty($row)) {
            $this->builderPaymentGateways->insert([
                'name' => 'Mercado Pago',
                'name_key' => 'mercado_pago',
                'environment' => 'sandbox',
                'logos' => 'visa,mastercard,amex,discover,boleto,mercado_pago'
            ]);
        }
    }

    //update commission settings
    public function updateCommissionSettings()
    {
        $data = [
            'commission_rate' => inputPost('commission_rate'),
            'vat_status' => inputPost('vat_status'),
            'cart_location_selection' => inputPost('cart_location_selection')
        ];
        if (empty(inputPost('commission'))) {
            $data['commission_rate'] = 0;
        }
        return $this->builderPaymentSettings->where('id', 1)->update($data);
    }

    //update additional invoice information
    public function updateAdditionalInvoiceInfo()
    {
        $array = array();
        foreach ($this->activeLanguages as $language) {
            $data = [
                'lang_id' => $language->id,
                'text' => inputPost('info_' . $language->id)
            ];
            if (!empty($data['text'])) {
                $data['text'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $data['text']);
            }
            array_push($array, $data);
        }
        $array = serialize($array);
        return $this->builderPaymentSettings->where('id', 1)->update(['additional_invoice_info' => $array]);
    }

    //get payment gateway
    public function getPaymentGateway($nameKey)
    {
        return $this->builderPaymentGateways->where('name_key', strSlug($nameKey))->get()->getRow();
    }

    //get active payment gateways
    public function getActivePaymentGateways()
    {
        return $this->builderPaymentGateways->where('status', 1)->get()->getResult();
    }

    //get payment gateways
    public function getPaymentGateways()
    {
        return $this->builderPaymentGateways->get()->getResult();
    }

    //get payment settings
    public function getPaymentSettings()
    {
        return $this->builderPaymentSettings->where('id', 1)->get()->getRow();
    }

    //get taxes
    public function getTaxes()
    {
        return $this->builderTaxes->get()->getResult();
    }

    //get tax
    public function getTax($id)
    {
        return $this->builderTaxes->where('id', clrNum($id))->get()->getRow();
    }

    //add tax
    public function addTax()
    {
        $data = $this->setTaxData();
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->builderTaxes->insert($data);
    }

    //add global tax
    public function editTax()
    {
        $id = inputPost('id');
        $tax = $this->getTax($id);
        if (!empty($tax)) {
            $data = $this->setTaxData();
            return $this->builderTaxes->where('id', $tax->id)->update($data);
        }
        return false;
    }

    //set tax data
    private function setTaxData()
    {
        $taxNameArray = array();
        foreach ($this->activeLanguages as $language) {
            $taxNameArray[$language->id] = inputPost('tax_name_' . $language->id);
        }
        $arrayCountryIds = array();
        if (!empty(inputPost('countries'))) {
            $arrayCountryIds = inputPost('countries');
        }
        $arrayStateIds = inputPost('states');
        $finalStateIds = array();
        if (!empty($arrayStateIds)) {
            foreach ($arrayStateIds as $sId) {
                $state = getState($sId);
                if (!empty($state) && !in_array($state->country_id, $arrayCountryIds) && !in_array($state->id, $finalStateIds)) {
                    array_push($finalStateIds, $state->id);
                }
            }
        }
        $data = [
            'name_data' => serialize($taxNameArray),
            'tax_rate' => inputPost('tax_rate'),
            'is_all_countries' => inputPost('all_countries') == 1 ? 1 : 0,
            'country_ids' => serialize($arrayCountryIds),
            'state_ids' => serialize($finalStateIds),
            'product_sales' => !empty(inputPost('product_sales')) ? 1 : 0,
            'service_payments' => !empty(inputPost('service_payments')) ? 1 : 0,
            'status' => !empty(inputPost('status')) ? 1 : 0
        ];
        if ($data['is_all_countries']) {
            $data['country_ids'] = '';
            $data['state_ids'] = '';
        }
        return $data;
    }

    //delete tax
    public function deleteTax($id)
    {
        $tax = $this->getTax($id);
        if (!empty($tax)) {
            return $this->builderTaxes->where('id', $tax->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Affiliate Program
     * --------------------------------------------------------------------
     */

    //update affiliate settings
    public function updateAffiliateSettings()
    {
        $submit = inputPost('submit');
        $langId = inputPost('lang_id');
        if ($submit == 'settings') {
            $dataGeneral = processFormData('affiliate');
            $dataGeneral['type'] = $dataGeneral['type'] === 'seller_based' ? 'seller_based' : 'site_based';

            $uploadModel = new UploadModel();
            $file = $uploadModel->uploadTempFile('file');
            if (!empty($file) && !empty($file['path'])) {
                $dataGeneral['image'] = $uploadModel->uploadAffiliateImage($file['path']);
                $dataGeneral['storage'] = $this->activeStorage;

                $existing = getSettingsUnserialized('affiliate');
                if (!empty($existing->image)) {
                    deleteStorageFile($existing->image, $existing->storage);
                }
                $uploadModel->deleteTempFile($file['path']);
            }

            $serialized = serialize($dataGeneral);
            return $this->builderGeneral->where('id', 1)->update(['affiliate_settings' => $serialized]);

        } elseif ($submit == 'description') {
            $desc = [
                'title' => inputPost('title'),
                'description' => inputPostTextarea('description')
            ];
            $data['affiliate_description'] = serialize($desc);
        } elseif ($submit == 'content') {
            $content = [
                'title' => inputPost('title'),
                'content' => inputPostTextarea('content')
            ];
            $data['affiliate_content'] = serialize($content);
        } elseif ($submit == 'how_it_works') {
            $arrayData = array();
            array_push($arrayData, ['title' => inputPost('title1'), 'description' => inputPostTextarea('description1')]);
            array_push($arrayData, ['title' => inputPost('title2'), 'description' => inputPostTextarea('description2')]);
            array_push($arrayData, ['title' => inputPost('title3'), 'description' => inputPostTextarea('description3')]);
            if (!empty($arrayData)) {
                $data['affiliate_works'] = serialize($arrayData);
            }
        } elseif ($submit == 'questions') {
            $arrayData = array();
            $questionIds = inputPost('question_id');
            if (!empty($questionIds)) {
                foreach ($questionIds as $id) {
                    $item = array(
                        'o' => inputPost('order_' . $id),
                        'q' => inputPost('question_' . $id),
                        'a' => inputPostTextarea('answer_' . $id)
                    );
                    array_push($arrayData, $item);
                }
            }
            if (!empty($arrayData)) {
                $data['affiliate_faq'] = serialize($arrayData);
            }
        }

        if (!empty($data)) {
            return $this->builder->where('lang_id', clrNum($langId))->update($data);
        }
    }

    /*
     * --------------------------------------------------------------------
     * Font Settings
     * --------------------------------------------------------------------
     */

    //get selected fonts
    public function getSelectedFonts($settings)
    {
        return getCacheData('fonts_' . $settings->id, function () use ($settings) {

            $arrayFonts = array();
            $fonts = $this->builderFonts->whereIn('id', [clrNum($settings->site_font), clrNum($settings->dashboard_font)], false)->get()->getResult();
            if (!empty($fonts)) {
                foreach ($fonts as $font) {
                    if ($font->id == $settings->site_font) {
                        $arrayFonts['site_font'] = $font;
                    }
                    if ($font->id == $settings->dashboard_font) {
                        $arrayFonts['dashboard_font'] = $font;
                    }
                }
            }
            return $arrayFonts;

        }, 'static');
    }

    //get fonts
    public function getFonts()
    {
        return $this->builderFonts->orderBy('font_name')->get()->getResult();
    }

    //get font
    public function getFont($id)
    {
        return $this->builderFonts->where('id', clrNum($id))->get()->getRow();
    }

    //add font
    public function addFont()
    {
        $data = [
            'font_name' => inputPost('font_name'),
            'font_url' => inputPost('font_url'),
            'font_family' => inputPost('font_family'),
            'is_default' => 0
        ];
        return $this->builderFonts->insert($data);
    }

    //set site font
    public function setSiteFont()
    {
        $langId = inputPost('lang_id');
        $data = [
            'site_font' => inputPost('site_font'),
            'dashboard_font' => inputPost('dashboard_font')
        ];
        return $this->builder->where('lang_id', clrNum($langId))->update($data);
    }

    //edit font
    public function editFont($id)
    {
        $font = $this->getFont($id);
        if (!empty($font)) {
            $data = array(
                'font_name' => inputPost('font_name'),
                'font_url' => inputPost('font_url'),
                'font_family' => inputPost('font_family')
            );
            return $this->builderFonts->where('id', clrNum($id))->update($data);
        }
        return false;
    }

    //delete font
    public function deleteFont($id)
    {
        $font = $this->getFont($id);
        if (!empty($font)) {
            return $this->builderFonts->where('id', $font->id)->delete();
        }
        return false;
    }

    //delete old sessions
    function deleteOldSessions()
    {
        $this->db->query("DELETE FROM ci_sessions WHERE timestamp < NOW() - INTERVAL 7 DAY");
    }

    //set last cron update
    public function setLastCronUpdate()
    {
        $this->builderGeneral->where('id', 1)->update(['last_cron_update' => date('Y-m-d H:i:s')]);
    }

    //download database backup
    public function downloadBackup()
    {
        $prefs = array(
            'tables' => array(),
            'ignore' => array(),
            'filename' => '',
            'format' => 'gzip', // gzip, zip, txt
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n",
            'foreign_key_checks' => TRUE
        );
        if (count($prefs['tables']) === 0) {
            $prefs['tables'] = $this->db->listTables();
        }
        // Extract the prefs for simplicity
        extract($prefs);
        $output = '';
        // Do we need to include a statement to disable foreign key checks?
        if ($foreign_key_checks === FALSE) {
            $output .= 'SET foreign_key_checks = 0;' . $newline;
        }
        foreach ((array)$tables as $table) {
            // Is the table in the "ignore" list?
            if (in_array($table, (array)$ignore, TRUE)) {
                continue;
            }
            // Get the table schema
            $query = $this->db->query('SHOW CREATE TABLE ' . $this->db->escapeIdentifiers($this->db->database . '.' . $table));
            // No result means the table name was invalid
            if ($query === FALSE) {
                continue;
            }
            // Write out the table schema
            $output .= '#' . $newline . '# TABLE STRUCTURE FOR: ' . $table . $newline . '#' . $newline . $newline;

            if ($add_drop === TRUE) {
                $output .= 'DROP TABLE IF EXISTS ' . $this->db->protectIdentifiers($table) . ';' . $newline . $newline;
            }
            $i = 0;
            $result = $query->getResultArray();
            foreach ($result[0] as $val) {
                if ($i++ % 2) {
                    $output .= $val . ';' . $newline . $newline;
                }
            }
            // If inserts are not needed we're done...
            if ($add_insert === FALSE) {
                continue;
            }
            // Grab all the data from the current table
            $query = $this->db->query('SELECT * FROM ' . $this->db->protectIdentifiers($table));

            if ($query->getFieldCount() === 0) {
                continue;
            }
            // Fetch the field names and determine if the field is an
            // integer type. We use this info to decide whether to
            // surround the data with quotes or not
            $i = 0;
            $field_str = '';
            $isInt = array();
            while ($field = $query->resultID->fetch_field()) {
                // Most versions of MySQL store timestamp as a string
                $isInt[$i] = in_array($field->type, array(MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_INT24, MYSQLI_TYPE_LONG), TRUE);

                // Create a string of field names
                $field_str .= $this->db->escapeIdentifiers($field->name) . ', ';
                $i++;
            }
            // Trim off the end comma
            $field_str = preg_replace('/, $/', '', $field_str);
            // Build the insert string
            foreach ($query->getResultArray() as $row) {
                $valStr = '';
                $i = 0;
                foreach ($row as $v) {
                    if ($v === NULL) {
                        $valStr .= 'NULL';
                    } else {
                        // Escape the data if it's not an integer
                        $valStr .= ($isInt[$i] === FALSE) ? $this->db->escape($v) : $v;
                    }
                    // Append a comma
                    $valStr .= ', ';
                    $i++;
                }
                // Remove the comma at the end of the string
                $valStr = preg_replace('/, $/', '', $valStr);
                // Build the INSERT string
                $output .= 'INSERT INTO ' . $this->db->protectIdentifiers($table) . ' (' . $field_str . ') VALUES (' . $valStr . ');' . $newline;
            }
            $output .= $newline . $newline;
        }
        // Do we need to include a statement to re-enable foreign key checks?
        if ($foreign_key_checks === FALSE) {
            $output .= 'SET foreign_key_checks = 1;' . $newline;
        }
        return $output;
    }
}