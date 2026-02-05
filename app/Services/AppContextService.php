<?php

namespace App\Services;

use App\Libraries\Storage;
use Config\App;
use Config\Database;
use Config\Services;

class AppContextService
{
    protected $db;
    protected $session;
    protected $generalSettings;
    protected $paymentSettings;
    protected $productSettings;
    protected $routes;
    protected $languages;
    protected $rolesPermissions;
    protected $langSegment = '';
    protected $defaultLang;
    protected $activeLang;
    protected $languageTranslations;
    protected $langBaseUrl;
    protected $authCheck = false;
    protected $authUser;
    protected $settings;
    protected $currencies = [];
    protected $defaultCurrency;
    protected $defaultLocation;
    protected $activeStorage = 'local';
    protected $storageSettings;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->session = session();
        $this->initialize();
    }

    //initialize
    protected function initialize(): void
    {
        $this->generalSettings = $this->getFirstRow('general_settings');
        $this->paymentSettings = $this->getCachedRow('payment_settings');
        $this->productSettings = $this->getCachedRow('product_settings');
        $this->routes = $this->getRoutes();
        $this->languages = $this->getLanguages();
        $this->rolesPermissions = $this->getRolesPermissions();

        if (!empty($this->generalSettings->timezone)) {
            date_default_timezone_set($this->generalSettings->timezone);
        }

        $this->defaultLang = $this->getLanguage($this->generalSettings->site_lang);
        if (empty($this->defaultLang)) {
            $this->defaultLang = $this->db->table('languages')->get()->getFirstRow();
        }

        $this->activeLang = $this->setActiveLanguage();
        $this->langBaseUrl = base_url($this->activeLang->short_form);
        if ($this->activeLang->id == $this->defaultLang->id) {
            $this->langBaseUrl = base_url();
        }

        $this->settings = $this->getSettings($this->activeLang->id);

        // Set storage
        $this->activeStorage = 'local';
        $this->storageSettings = $this->getStorageSettings($this->generalSettings->storage_settings);
        $cloudStorages = \Config\Defaults::$cloudStorages;

        if (!empty($this->storageSettings->storage) && in_array($this->storageSettings->storage, $cloudStorages, true)) {
            $this->activeStorage = $this->storageSettings->storage;
        }

        $this->initAuth();
        $this->initCurrencies();
        $this->defaultLocation = $this->initDefaultLocation();
    }

    //get by key
    public function __get(string $key)
    {
        return $this->{$key} ?? null;
    }

    //set by key
    public function set(string $key, $value): void
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }
    }

    //storage
    public function storage(): Storage
    {
        return $this->storage;
    }

    //get first row
    protected function getFirstRow($table)
    {
        return $this->db->table($table)->get()->getFirstRow();
    }

    //get cached row
    protected function getCachedRow($table)
    {
        return $this->getCache($table, function () use ($table) {
            return $this->getFirstRow($table);
        });
    }

    //get languages
    protected function getLanguages()
    {
        return $this->getCache('languages', function () {
            return $this->db->table('languages')->where('status', 1)->orderBy('language_order')->get()->getResult();
        });
    }

    //get language
    protected function getLanguage($id)
    {
        return $this->getCache('language_' . $id, function () use ($id) {
            return $this->db->table('languages')->where('id', $id)->get()->getRow();
        });
    }

    //get language translations by lang
    protected function getLanguageTranslations($langId)
    {
        return $this->getCache('language_translations_' . $langId, function () use ($langId) {
            $rows = $this->db->table('language_translations')->where('lang_id', $langId)->get()->getResult();
            $translations = [];
            foreach ($rows as $item) {
                $translations[$item->label] = $item->translation;
            }
            return $translations;
        });
    }

    //set active language
    protected function setActiveLanguage()
    {
        $segment = getSegmentValue(1);
        $langId = null;

        foreach ($this->languages as $lang) {
            if ($segment == $lang->short_form) {
                $this->langSegment = $lang->short_form;
                $langId = $lang->id;
                break;
            }
        }

        if (empty($langId)) {
            $langId = $this->defaultLang->id;
        }

        $activeLang = $this->getLanguage($langId);
        $this->languageTranslations = $this->getLanguageTranslations($langId);

        return $activeLang;
    }

    //set active language by given language id
    public function setActiveLanguageById(int $langId)
    {
        if (empty($langId) || $this->activeLang->id == $langId) {
            return;
        }

        $language = $this->getLanguage($langId);

        if (!empty($language)) {
            $this->activeLang = $language;
            $this->languageTranslations = $this->getLanguageTranslations($langId);
            $this->langBaseUrl = base_url($language->short_form);
            $this->settings = $this->getSettings($langId);
        }
    }

    //get routes
    protected function getRoutes()
    {
        return $this->getCache('routes', function () {
            $rows = $this->db->table('routes')->get()->getResult();
            $routes = new \stdClass();
            foreach ($rows as $row) {
                $routes->{$row->route_key} = $row->route;
            }
            return $routes;
        });
    }

    //get settings by lang
    protected function getSettings($langId)
    {
        return $this->getCache('settings_' . $langId, function () use ($langId) {
            return $this->db->table('settings')->where('lang_id', $langId)->get()->getRow();
        });
    }

    //get role permissions
    protected function getRolesPermissions()
    {
        return $this->getCache('roles_permissions', function () {
            return $this->db->table('roles_permissions')->get()->getResult();
        });
    }

    //init auth
    protected function initAuth(): void
    {
        $userId = clrNum($this->session->get('auth_user_id'));
        $authToken = $this->session->get('auth_token');

        if (!empty($userId) && !empty($authToken)) {
            $user = $this->db->table('users')->join('roles_permissions', 'roles_permissions.id = users.role_id')
                ->select('users.*, role_name, permissions, is_super_admin, is_admin, is_vendor, is_member')
                ->where('users.id', $userId)->get()->getRow();

            if (!empty($user) && $user->banned != 1 && $user->token == $authToken) {
                $this->authCheck = true;
                $this->authUser = $user;
            }
        }
    }

    //init currencies
    protected function initCurrencies(): void
    {
        $currencyList = $this->db->table('currencies')->orderBy('status DESC, id')->get()->getResult();

        foreach ($currencyList as $currency) {
            $this->currencies[$currency->code] = $currency;
            if ($currency->code == $this->paymentSettings->default_currency) {
                $this->defaultCurrency = $currency;
            }
        }

        if (empty($this->defaultCurrency) && !empty($currencyList)) {
            $this->defaultCurrency = $currencyList[0];
        }
    }

    protected function initDefaultLocation()
    {
        $location = (object)[
            'country_id' => '',
            'state_id' => '',
            'city_id' => '',
        ];

        $sessLocation = $this->session->get('mds_default_location');
        if (!empty($sessLocation)) {
            $sessLocation = unserializeData($sessLocation);
            $location->country_id = $sessLocation->country_id ?? '';
            $location->state_id = $sessLocation->state_id ?? '';
            $location->city_id = $sessLocation->city_id ?? '';
        }

        return $location;
    }

    //get storage settings
    function getStorageSettings($rawData): object
    {
        $allKeys = getAppDefault('formInputKeys');
        $fieldTypes = $allKeys['storage'] ?? [];

        $defaults = array_fill_keys(array_keys($fieldTypes), null);

        $data = unserializeData($rawData);
        $data = is_array($data) ? $data : [];

        return (object)($data + $defaults);
    }

    //get cache data
    private function getCache(string $cacheKey, callable $callback)
    {
        if (!empty($this->generalSettings) && $this->generalSettings->static_cache_system == 1) {
            $fullKey = 'cstatic_' . $cacheKey;
            $time = defined('STATIC_CACHE_REFRESH_TIME') ? STATIC_CACHE_REFRESH_TIME : 86400;

            $data = cache($fullKey);

            if ($data !== null) {
                return $data;
            }

            $data = $callback();
            if ($data !== null) {
                cache()->save($fullKey, $data, $time);
            }

            return $data;
        }

        return $callback();
    }
}