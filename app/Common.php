<?php

if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = str_replace('/index.php', '', $_SERVER['REQUEST_URI']);

    $newUrl = $protocol . '://' . $host . $uri;

    if ($newUrl !== ($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])) {
        header('Location: ' . $newUrl, true, 301);
        exit();
    }
}

//get a specific value from AppContextService
if (!function_exists('getContextValue')) {
    function getContextValue(string $key, $default = null)
    {
        $ctx = service('appContext');
        return $ctx->{$key} ?? $default;
    }
}

//set a context value in AppContextService
if (!function_exists('setContextValue')) {
    function setContextValue(string $key, $value): void
    {
        $ctx = service('appContext');
        $ctx->set($key, $value);
    }
}

//set context active lang
if (!function_exists('setContextActiveLang')) {
    function setContextActiveLang($langId): void
    {
        if (empty($langId) || !is_numeric($langId)) {
            return;
        }

        $appContext = service('appContext');
        $appContext->setActiveLanguageById($langId);
    }
}

//get a defined array or value from Config\Defaults by key
if (!function_exists('getAppDefault')) {
    function getAppDefault(string $key): mixed
    {
        $class = \Config\Defaults::class;

        return property_exists($class, $key) ? $class::$$key : [];
    }
}

//current full url
if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl($esc = true)
    {
        $currentURL = current_url();
        if (!empty($_SERVER['QUERY_STRING'])) {
            $currentURL = $currentURL . "?" . $_SERVER['QUERY_STRING'];
        }
        if ($esc) {
            return esc($currentURL);
        }
        return $currentURL;
    }
}

//language base URL
if (!function_exists('langBaseUrl')) {
    function langBaseUrl(string $route = ''): string
    {
        $base = rtrim(getContextValue('langBaseUrl'), '/') . '/';

        return $route !== ''
            ? $base . ltrim($route, '/')
            : $base;
    }
}

//generate base URL by language id
if (!function_exists('generateBaseURLByLangId')) {
    function generateBaseURLByLangId($langId): string
    {
        $langId = (int)$langId;
        $defaultLangId = (int)getContextValue('generalSettings')->site_lang;

        if ($langId === $defaultLangId) {
            return base_url();
        }

        $languages = getContextValue('languages');
        foreach ($languages as $lang) {
            if ((int)$lang->id === $langId && !empty($lang->short_form)) {
                return base_url($lang->short_form) . '/';
            }
        }

        return base_url();
    }
}

//admin url
if (!function_exists('adminUrl')) {
    function adminUrl(?string $route = null): string
    {
        $adminRoute = getContextValue('routes')->admin ?? 'admin';

        if (!empty($route)) {
            return base_url(rtrim($adminRoute, '/') . '/' . ltrim($route, '/'));
        }

        return rtrim(base_url($adminRoute), '/') . '/';
    }
}

//dashboard url
if (!function_exists('dashboardUrl')) {
    function dashboardUrl(?string $route = null): string
    {
        $dashboardRoute = getContextValue('routes')->dashboard ?? 'dashboard';
        $base = rtrim(langBaseUrl(), '/') . '/';
        $dashboard = trim($dashboardRoute, '/');

        $url = $base . $dashboard;

        if (!empty($route)) {
            return $url . '/' . ltrim($route, '/');
        }

        return $url . '/';
    }
}

//escape variable for safe JavaScript output
if (!function_exists('escJs')) {
    function escJs($data): string
    {
        return esc($data, 'js');
    }
}

//print meta tag
if (!function_exists('escMeta')) {
    function escMeta($str)
    {
        if (empty($str)) {
            return '';
        }

        return htmlspecialchars((string)$str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

//auth check
if (!function_exists('authCheck')) {
    function authCheck(): bool
    {
        return getContextValue('authCheck') === true;
    }
}

//get active user
if (!function_exists('user')) {
    function user(): ?object
    {
        return getContextValue('authUser');
    }
}

//get active user id
if (!function_exists('activeUserId')) {
    function activeUserId()
    {
        if (authCheck()) {
            return user()->id;
        }
        return 0;
    }
}

//get user by id
if (!function_exists('getUser')) {
    function getUser($id)
    {
        $model = new \App\Models\AuthModel();
        return $model->getUser($id);
    }
}

//get username
if (!function_exists('getUsername')) {
    function getUsername($user)
    {
        $isMember = true;
        if (!empty($user)) {
            if (hasPermission('all', $user) || hasPermission('admin_panel', $user) || hasPermission('vendor', $user)) {
                $isMember = false;
            }
            if (!$isMember && !empty($user->username)) {
                return $user->username;
            }
            return $user->first_name . ' ' . $user->last_name;
        }
        return 'user';
    }
}

//get username by user id
if (!function_exists('getUsernameByUserId')) {
    function getUsernameByUserId($userId)
    {
        $user = getUser($userId);
        return getUsername($user);
    }
}

//is super admin
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
        if (authCheck() && hasPermission('all')) {
            return true;
        }
        return false;
    }
}

//is admin
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        if (authCheck() && hasPermission('admin_panel')) {
            return true;
        }
        return false;
    }
}

//is vendor
if (!function_exists('isVendor')) {
    function isVendor($user = null)
    {
        if ($user == null && authCheck()) {
            $user = user();
        }
        if (!empty($user)) {
            if ($user->role_id == 1) {
                return true;
            }
            if (getContextValue('generalSettings')->multi_vendor_system == 1) {
                if (getContextValue('generalSettings')->vendor_verification_system != 1) {
                    return true;
                } else {
                    if (hasPermission('vendor', $user)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

//is user vendor by role id
if (!function_exists('isVendorByRoleId')) {
    function isVendorByRoleId($roleId = null): bool
    {
        $roleId = (int)$roleId;
        $roles = getContextValue('rolesPermissions');
        if (empty($roles) || $roleId === 0) {
            return false;
        }

        foreach ($roles as $role) {
            if ((int)$role->id === $roleId) {
                return ($role->is_super_admin ?? 0) == 1 || ($role->is_vendor ?? 0) == 1;
            }
        }

        return false;
    }
}

//get logo
if (!function_exists('getLogo')) {
    function getLogo(): string
    {
        $logoPath = getContextValue('generalSettings')->logo ?? '';
        if (!empty($logoPath) && file_exists(FCPATH . $logoPath)) {
            return base_url($logoPath);
        }

        return base_url('assets/img/logo.svg');
    }
}

//get logo size
if (!function_exists('getLogoSize')) {
    function getLogoSize(?object $generalSettings, string $param = 'width'): int
    {
        $defaultWidth = 160;
        $defaultHeight = 60;

        if (!is_object($generalSettings)) {
            return $param === 'height' ? $defaultHeight : $defaultWidth;
        }

        $sizeStr = trim($generalSettings->logo_size ?? '');
        if ($sizeStr === '') {
            return $param === 'height' ? $defaultHeight : $defaultWidth;
        }

        $parts = explode('x', strtolower($sizeStr));
        if (count($parts) !== 2) {
            return $param === 'height' ? $defaultHeight : $defaultWidth;
        }

        [$widthRaw, $heightRaw] = $parts;
        $width = is_numeric($widthRaw) ? (int)$widthRaw : 0;
        $height = is_numeric($heightRaw) ? (int)$heightRaw : 0;

        $width = ($width >= 10 && $width <= 300) ? $width : $defaultWidth;
        $height = ($height >= 10 && $height <= 300) ? $height : $defaultHeight;

        return $param === 'height' ? $height : $width;
    }
}

//get logo email
if (!function_exists('getLogoEmail')) {
    function getLogoEmail(): string
    {
        $logoPath = getContextValue('generalSettings')->logo_email ?? '';
        if (!empty($logoPath) && file_exists(FCPATH . $logoPath)) {
            return base_url($logoPath);
        }

        return base_url('assets/img/logo.png');
    }
}

//get favicon
if (!function_exists('getFavicon')) {
    function getFavicon(): string
    {
        $faviconPath = getContextValue('generalSettings')->favicon ?? '';
        if (!empty($faviconPath) && file_exists(FCPATH . $faviconPath)) {
            return base_url($faviconPath);
        }

        return base_url('assets/img/favicon.png');
    }
}

//get selected currency
if (!function_exists('getSelectedCurrency')) {
    function getSelectedCurrency(): ?object
    {
        $paymentSettings = getContextValue('paymentSettings');
        $currencies = getContextValue('currencies');
        $defaultCurrency = getContextValue('defaultCurrency');

        if ($paymentSettings && ($paymentSettings->currency_converter ?? 0) == 1) {
            $sessCurrency = helperGetSession('mds_selected_currency');
            if (!empty($sessCurrency) && isset($currencies[$sessCurrency])) {
                return $currencies[$sessCurrency];
            }
        }

        return $defaultCurrency;
    }
}

//redirect to URL
if (!function_exists('redirectToUrl')) {
    function redirectToUrl(string $url): void
    {
        redirect()->to($url)->send();
        exit;
    }
}

//redirect to back URL
if (!function_exists('redirectToBackURL')) {
    function redirectToBackURL()
    {
        $backURL = inputPost('back_url');
        if (!empty($backURL)) {
            $parsedBase = parse_url(base_url());
            $parsedUrl = parse_url($backURL);

            if (isset($parsedUrl['host']) && $parsedUrl['host'] === $parsedBase['host']) {
                redirectToUrl($backURL);
                exit;
            }
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            $parsedBase = parse_url(base_url());
            $parsedReferer = parse_url($referer);

            if (isset($parsedReferer['host']) && $parsedReferer['host'] === $parsedBase['host']) {
                redirectToUrl($referer);
                exit;
            }
        }

        redirectToUrl(base_url());
        exit;
    }
}

//character limiter
if (!function_exists('characterLimiter')) {
    function characterLimiter($str, $limit, $endChar = '')
    {
        if (!empty($str)) {
            return character_limiter($str, $limit, $endChar);
        }
    }
}

//translation
if (!function_exists('trans')) {
    function trans(string $str, bool $clearQuotes = false): string
    {
        if ($str === 'mercado_pago') {
            return 'Mercado Pago';
        }

        $translations = getContextValue('languageTranslations');
        if (isset($translations[$str])) {
            return $clearQuotes ? clrQuotes($translations[$str]) : $translations[$str];
        }

        return $str;
    }
}

//get translated message
if (!function_exists('transWithField')) {
    function transWithField(string $str, string $value): string
    {
        $translations = getContextValue('languageTranslations');
        if (!empty($translations[$str])) {
            return str_replace('{field}', $value, $translations[$str]);
        }

        return '';
    }
}

//convert URL by language
if (!function_exists('convertUrlByLanguage')) {
    function convertUrlByLanguage(?object $language): string
    {
        // Validate the language object
        if (empty($language) || !isset($language->id, $language->short_form)) {
            return base_url();
        }

        // Get the current full URL
        $currentUrl = getCurrentUrl();
        $urlParts = parse_url($currentUrl);

        // Extract path, query string, and fragment from the URL
        $path = $urlParts['path'] ?? '/';
        $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        // Detect if the site runs in a subdirectory (e.g., domain.com/subdir/)
        $basePath = trim(parse_url(base_url(), PHP_URL_PATH) ?? '', '/');

        // Normalize relative path (remove base path if exists)
        $relativePath = trim($path, '/');
        if ($basePath !== '' && str_starts_with($relativePath, $basePath)) {
            $relativePath = trim(substr($relativePath, strlen($basePath)), '/');
        }

        // Remove current language segment from the path (if exists)
        $currentLangSegment = getContextValue('langSegment');
        if (!empty($currentLangSegment)) {
            if ($relativePath === $currentLangSegment) {
                $relativePath = '';
            } elseif (str_starts_with($relativePath, $currentLangSegment . '/')) {
                $relativePath = substr($relativePath, strlen($currentLangSegment) + 1);
            }
        }

        // Check if the target language is the default site language
        $siteDefaultLangId = (int)getContextValue('generalSettings')->site_lang;
        $isTargetLangDefault = ($siteDefaultLangId === (int)$language->id);

        // Build new path segments
        $segments = [];
        if (!$isTargetLangDefault) {
            $segments[] = $language->short_form; // Add language prefix only if not default
        }
        if ($relativePath !== '') {
            $segments[] = $relativePath;
        }

        // Create the final path
        $finalPath = implode('/', $segments);

        // Ensure base_url() does not cause double slashes
        $finalBase = rtrim(base_url(), '/');

        // Return the fully reconstructed URL
        return $finalBase . '/' . $finalPath . $query . $fragment;
    }
}

//get validation rules
if (!function_exists('getValRules')) {
    function getValRules($val)
    {
        $rules = $val->getRules();
        $newRules = array();
        if (!empty($rules)) {
            foreach ($rules as $key => $rule) {
                $newRules[$key] = [
                    'label' => $rule['label'],
                    'rules' => $rule['rules'],
                    'errors' => [
                        'required' => trans("form_validation_required"),
                        'min_length' => trans("form_validation_min_length"),
                        'max_length' => trans("form_validation_max_length"),
                        'matches' => trans("form_validation_matches"),
                        'is_unique' => trans("form_validation_is_unique")
                    ]
                ];
            }
        }
        return $newRules;
    }
}

//get segment value
if (!function_exists('getSegmentValue')) {
    function getSegmentValue($segmentNumber)
    {
        try {
            $uri = service('uri');
            if ($uri->getSegment($segmentNumber) !== null) {
                return $uri->getSegment($segmentNumber);
            }
        } catch (Exception $e) {
        }
        return null;
    }
}

//get request
if (!function_exists('inputGet')) {
    function inputGet($inputName)
    {
        $input = \Config\Services::request()->getGet($inputName);
        if (!empty($input) && !is_array($input)) {
            $input = trim($input);
        }
        return $input;
    }
}

//post request
if (!function_exists('inputPost')) {
    function inputPost(string $inputName, ?bool $isPrice = false)
    {
        $input = \Config\Services::request()->getPost($inputName);

        if (empty($input)) {
            return '';
        }

        if (is_array($input)) {
            return $input;
        }

        $input = trim((string)$input);

        if ($isPrice) {
            $input = str_replace(',', '.', $input);
        }

        return $input;
    }
}

//post request textarea
if (!function_exists('inputPostTextarea')) {
    function inputPostTextarea($inputName)
    {
        $val = inputPost($inputName);
        if (!empty($val)) {
            $val = str_replace('\n', '<br/>', $val);
        }
        return $val;
    }
}

//is value exists in array
if (!function_exists('isItemInArray')) {
    function isItemInArray($item, $array)
    {
        if (empty($array) || empty($item) || !is_array($array)) {
            return false;
        }
        if (in_array($item, $array)) {
            return true;
        }
        return false;
    }
}

//generate ids string
if (!function_exists('generateIdsString')) {
    function generateIdsString($array)
    {
        if (!empty($array)) {
            return implode(',', $array);
        }
        return '0';
    }
}

//convert string to slug
if (!function_exists('strSlug')) {
    function strSlug($str)
    {
        $str = trim($str ?? '');
        if (!empty($str)) {
            return url_title(convert_accented_characters($str), '-', TRUE);
        }
    }
}

//generate slug
if (!function_exists('generateSlug')) {
    function generateSlug($slug, $title)
    {
        if (empty($slug)) {
            return strSlug($title);
        } else {
            $newSlug = removeSpecialCharacters($slug);
            if (!empty($newSlug)) {
                $newSlug = str_replace(' ', '-', $newSlug);
            }
            return $newSlug;
        }
    }
}

//clean string
if (!function_exists('cleanStr')) {
    function cleanStr($str)
    {
        $str = trim($str ?? '');
        $str = esc($str ?? '');
        return removeSpecialCharacters($str);
    }
}

//remove special characters
if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters($str = null, $removeQuotes = false)
    {
        if ($str === null) {
            return '';
        }

        $forbiddenChars = ['#', '!', '(', ')'];

        $str = str_replace($forbiddenChars, '', removeForbiddenCharacters($str));

        if ($removeQuotes) {
            $str = clrQuotes($str);
        }

        return $str;
    }
}

//clean number
if (!function_exists('clrNum')) {
    function clrNum($num)
    {
        if ($num === null || is_array($num) || is_object($num)) {
            return 0;
        }
        return intval($num);
    }
}

//remove forbidden characters
if (!function_exists('removeForbiddenCharacters')) {
    function removeForbiddenCharacters(?string $str): string
    {
        $str = trim($str ?? '');

        $forbiddenChars = [
            ';', '"', '$', '%', '*', '/', "'", '<', '>', '=',
            '?', '[', ']', '\\', '^', '`', '{', '}', '|', '~', '+'
        ];

        return str_replace($forbiddenChars, '', $str);
    }
}

//remove special characters
if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters(?string $str, bool $removeQuotes = false): string
    {
        $str = removeForbiddenCharacters($str ?? '');

        $extraChars = ['#', '!', '(', ')'];

        $str = str_replace($extraChars, '', $str);

        if ($removeQuotes) {
            $str = clrQuotes($str);
        }

        return $str;
    }
}

//clean quotes
if (!function_exists('clrQuotes')) {
    function clrQuotes($str)
    {
        $str = str_replace('"', '', $str ?? '');
        $str = str_replace("'", '', $str ?? '');
        return $str;
    }
}

//clean double quotes
if (!function_exists('clrDoubleQuotes')) {
    function clrDoubleQuotes($str)
    {
        return str_replace('"', '', $str ?? '');
    }
}

//set success message
if (!function_exists('setSuccessMessage')) {
    function setSuccessMessage($message)
    {
        if (!empty($message)) {
            $session = \Config\Services::session();
            $session->setFlashdata('success', $message);
        }
    }
}

//set error message
if (!function_exists('setErrorMessage')) {
    function setErrorMessage($message)
    {
        if (!empty($message)) {
            $session = \Config\Services::session();
            $session->setFlashdata('error', $message);
        }
    }
}

//count items
if (!function_exists('countItems')) {
    function countItems($items)
    {
        if (!empty($items) && is_array($items)) {
            return count($items);
        }
        return 0;
    }
}

//get font
if (!function_exists('getFontClient')) {
    function getFontClient($activeFonts, $type)
    {
        if (!empty($activeFonts)) {
            if ($type == 'site' && !empty($activeFonts['site_font'])) {
                return $activeFonts['site_font'];
            }
            if ($type == 'dashboard' && !empty($activeFonts['dashboard_font'])) {
                return $activeFonts['dashboard_font'];
            }
        }
        return null;
    }
}

//get route
if (!function_exists('getRoute')) {
    function getRoute(string $key, bool $slash = false): string
    {
        $routes = getContextValue('routes');
        $route = $routes->$key ?? $key;

        if ($slash === true && $route !== '') {
            $route .= '/';
        }

        return $route;
    }
}

//generate static url
if (!function_exists('generateUrl')) {
    function generateUrl($route1, $route2 = null)
    {
        if (!empty($route2)) {
            return langBaseUrl(getRoute($route1, true) . getRoute($route2));
        } else {
            return langBaseUrl(getRoute($route1));
        }
    }
}

//generate menu item url
if (!function_exists('generateMenuItemUrl')) {
    function generateMenuItemUrl($item)
    {
        if (!empty($item)) {
            return langBaseUrl($item->slug);
        }
    }
}

//generate profile url
if (!function_exists('generateProfileUrl')) {
    function generateProfileUrl($slug)
    {
        if (!empty($slug)) {
            return langBaseUrl(getRoute('profile', true) . $slug);
        }
    }
}

//generate category url
if (!function_exists('generateCategoryUrl')) {
    function generateCategoryUrl($category)
    {
        if (!empty($category)) {
            if ($category->parent_id == 0) {
                return langBaseUrl($category->slug);
            } else {
                return langBaseUrl($category->parent_slug . '/' . $category->slug);
            }
        }
    }
}

//generate category url path
if (!function_exists('generateCategoryUrl')) {
    function generateCategoryUrl($category)
    {
        if (!empty($category)) {
            if ($category->parent_id == 0) {
                return langBaseUrl($category->slug);
            } else {
                return langBaseUrl($category->parent_slug . '/' . $category->slug);
            }
        }
    }
}

//generate product url
if (!function_exists('generateProductUrl')) {
    function generateProductUrl($product)
    {
        if (!empty($product)) {
            return langBaseUrl($product->slug);
        }
    }
}

//generate product url by slug
if (!function_exists('generateProductUrlBySlug')) {
    function generateProductUrlBySlug($slug)
    {
        if (!empty($slug)) {
            return langBaseUrl($slug);
        }
    }
}

//generate blog url
if (!function_exists('generatePostUrl')) {
    function generatePostUrl($post)
    {
        if (!empty($post)) {
            return langBaseUrl(getRoute('blog', true) . $post->category_slug . '/' . $post->slug);
        }
    }
}

//generate dash url
if (!function_exists('generateDashUrl')) {
    function generateDashUrl($route1, $route2 = null)
    {
        if (!empty($route2)) {
            return dashboardUrl(getRoute($route1, true) . getRoute($route2));
        } else {
            return dashboardUrl(getRoute($route1));
        }
    }
}

//get image url by storage
if (!function_exists('getStorageFileUrl')) {
    function getStorageFileUrl(?string $path, ?string $storage = 'local', ?string $defaultImage = ''): ?string
    {
        if (empty($path)) {
            if (!empty($defaultImage)) {
                return base_url("assets/img/{$defaultImage}.jpg");
            }
            return '';
        }

        if ($storage === 'local') {
            $localPath = FCPATH . ltrim($path, '/');
            if (file_exists($localPath)) {
                return base_url(ltrim($path, '/'));
            }
        } else {
            $settings = getContextValue('storageSettings');
            $baseUrl = '';

            if (!empty($settings)) {
                switch ($storage) {
                    case 'aws_s3':
                        if (!empty($settings->aws_bucket) && !empty($settings->aws_region)) {
                            $baseUrl = "https://{$settings->aws_bucket}.s3.{$settings->aws_region}.amazonaws.com";
                        }
                        break;

                    case 'cloudflare_r2':
                        if (!empty($settings->r2_public_url)) {
                            $baseUrl = rtrim($settings->r2_public_url, '/');
                        }
                        break;

                    case 'backblaze_b2':
                        if (!empty($settings->b2_public_url)) {
                            $baseUrl = rtrim($settings->b2_public_url, '/');
                        }
                        break;
                }
            }

            if (!empty($baseUrl)) {
                return "{$baseUrl}/{$path}";
            }
        }

        if (!empty($defaultImage)) {
            return base_url("assets/img/{$defaultImage}.jpg");
        }

        return '';
    }
}

//get permissions array
if (!function_exists('getPermissionsArray')) {
    function getPermissionsArray()
    {
        return getAppDefault('rolePermissions');
    }
}

//get permission index key
if (!function_exists('getPermissionIndex')) {
    function getPermissionIndex($permission)
    {
        $array = getPermissionsArray();
        foreach ($array as $key => $value) {
            if ($value == $permission) {
                return $key;
            }
        }
        return null;
    }
}

//get permission by index
if (!function_exists('getPermissionByIndex')) {
    function getPermissionByIndex($index)
    {
        $array = getPermissionsArray();
        if (isset($array[$index])) {
            return $array[$index];
        }
        return null;
    }
}

//has permission
if (!function_exists('hasPermission')) {
    function hasPermission($permission, $user = null)
    {
        if (authCheck() && empty($user)) {
            $user = user();
        }
        if (!empty($user) && !empty($user->permissions)) {
            if ($user->permissions == 'all') {
                return true;
            }
            $array = explode(',', $user->permissions);
            $index = getPermissionIndex($permission);
            if (!empty($index) && !empty($array) && in_array($index, $array)) {
                return true;
            }
        }
        return false;
    }
}

//check permission
if (!function_exists('checkPermission')) {
    function checkPermission($permission)
    {
        if (!hasPermission($permission)) {
            redirectToUrl(base_url());
        }
    }
}

//check admin nav
if (!function_exists('isAdminNavActive')) {
    function isAdminNavActive($arrayNavItems)
    {
        $segment = getSegmentValue(2);
        if (!empty($segment) && !empty($arrayNavItems)) {
            if (in_array($segment, $arrayNavItems)) {
                echo ' ' . 'active';
            }
        }
    }
}

//date format
if (!function_exists('formatDate')) {
    function formatDate($timestamp, $showHour = true)
    {
        if (!empty($timestamp)) {
            if ($showHour == false) {
                return date('Y-m-d', strtotime($timestamp));
            }
            return date('Y-m-d / H:i', strtotime($timestamp));
        }
    }
}

//date format
if (!function_exists('formatDateLong')) {
    function formatDateLong($datetime, $showDay = true): string
    {
        if (empty($datetime) || strtotime($datetime) === false) {
            return '';
        }

        $timestamp = strtotime($datetime);
        $format = $showDay ? 'j M Y' : 'M Y';
        $date = date($format, $timestamp);

        $map = [
            'Jan' => trans("january"),
            'Feb' => trans("february"),
            'Mar' => trans("march"),
            'Apr' => trans("april"),
            'May' => trans("may"),
            'Jun' => trans("june"),
            'Jul' => trans("july"),
            'Aug' => trans("august"),
            'Sep' => trans("september"),
            'Oct' => trans("october"),
            'Nov' => trans("november"),
            'Dec' => trans("december"),
        ];

        return strtr($date, $map);
    }
}

//get language
if (!function_exists('getLanguage')) {
    function getLanguage($langId)
    {
        $model = new \App\Models\LanguageModel();
        return $model->getLanguage($langId);
    }
}

//unserialize data
if (!function_exists('unserializeData')) {
    function unserializeData(mixed $serializedData): mixed
    {
        if (!is_string($serializedData)) {
            return null;
        }

        $trimmedData = trim($serializedData);
        if ($trimmedData === '') {
            return null;
        }

        try {
            // Allow all classes (safe only if data is trusted)
            $data = unserialize($trimmedData, ['allowed_classes' => true]);

            if ($data === false && $trimmedData !== 'b:0;') {
                return null;
            }

            return $data;
        } catch (Throwable) {
            return null;
        }
    }
}

//get csv text
if (!function_exists('getCsvText')) {
    function getCsvText($row, $key, $default = '')
    {
        if (!is_array($row)) {
            return $default;
        }

        if (isset($row[$key])) {
            $value = trim((string)$row[$key]);

            if (strtoupper($value) === 'NULL' || $value === '') {
                return $default;
            }

            return $value;
        }

        return $default;
    }
}

//get csv number
if (!function_exists('getCsvNum')) {
    function getCsvNum($row, $key, $default = 0)
    {
        if (!is_array($row)) {
            return $default;
        }

        if (isset($row[$key])) {
            $value = trim((string)$row[$key]);

            if (strtoupper($value) === 'NULL' || $value === '') {
                return $default;
            }

            if (is_numeric($value)) {
                return (int)$value;
            }
        }

        return $default;
    }
}

//process form data by input types
if (!function_exists('processFormData')) {
    function processFormData(string $groupKey, $prevArray = null): array
    {
        $formInputKeys = getAppDefault('formInputKeys');
        $fieldTypes = $formInputKeys[$groupKey] ?? [];

        if (empty($fieldTypes) || !is_array($fieldTypes)) {
            return [];
        }

        if ($prevArray !== null) {
            $existing = $prevArray;
        } else {
            $existing = (array)getSettingsUnserialized($groupKey);
        }

        $request = \Config\Services::request();
        $post = $request->getPost();
        $updated = [];

        foreach ($fieldTypes as $key => $type) {
            switch ($type) {
                case 'bool':
                    $updated[$key] = array_key_exists($key, $post) ? $post[$key] : 0;
                    break;

                case 'text':
                    if (array_key_exists($key, $post)) {
                        $updated[$key] = $post[$key];
                    }
                    break;

                case 'number':
                    if (array_key_exists($key, $post)) {
                        $updated[$key] = is_numeric($post[$key]) ? (float)$post[$key] : 0;
                    }
                    break;

                case 'file':
                    break;
            }
        }

        return array_merge($existing, $updated);
    }
}

//unserialize and return settings
if (!function_exists('getSettingsUnserialized')) {
    function getSettingsUnserialized(string $key): object
    {
        $allKeys = getAppDefault('formInputKeys');
        $fieldTypes = $allKeys[$key] ?? [];

        $defaults = array_fill_keys(array_keys($fieldTypes), null);

        $dataKey = $key . '_settings';
        $rawData = getContextValue('generalSettings')->{$dataKey} ?? null;

        $data = unserializeData($rawData);
        $data = is_array($data) ? $data : [];

        return (object)($data + $defaults);
    }
}

//get active storage
if (!function_exists('getActiveStorage')) {
    function getActiveStorage(): string
    {
        $settings = getSettingsUnserialized('storage');
        $cloudStorages = \Config\Defaults::$cloudStorages;

        return in_array($settings->storage, $cloudStorages, true)
            ? $settings->storage
            : 'local';
    }
}

//parse serialized name array
if (!function_exists('parseSerializedNameArray')) {
    function parseSerializedNameArray($nameArray, $langId, $getMainName = true)
    {
        if (!empty($nameArray)) {
            $nameArray = unserializeData($nameArray);
            if (!empty($nameArray)) {
                foreach ($nameArray as $item) {
                    if ($item['lang_id'] == $langId && !empty($item['name'])) {
                        return esc($item['name']);
                    }
                }
            }
            //if not exist
            if ($getMainName == true) {
                if (!empty($nameArray)) {
                    foreach ($nameArray as $item) {
                        if ($item['lang_id'] == getContextValue('defaultLang')->id && !empty($item['name'])) {
                            return esc($item['name']);
                        }
                    }
                }
            }
        }
        return '';
    }
}

//parse serialized option array
if (!function_exists('parseSerializedOptionArray')) {
    function parseSerializedOptionArray($optionArray, $langId, $getMainName = true)
    {
        if (!empty($optionArray)) {
            $optionArray = unserializeData($optionArray);
            if (!empty($optionArray)) {
                foreach ($optionArray as $item) {
                    if ($item['lang_id'] == $langId && !empty($item['option'])) {
                        return esc($item['option']);
                    }
                }
            }
            //if not exist
            if ($getMainName == true) {
                if (!empty($optionArray)) {
                    foreach ($optionArray as $item) {
                        if ($item['lang_id'] == getContextValue('defaultLang')->id && !empty($item['option'])) {
                            return esc($item['option']);
                        }
                    }
                }
            }
        }
        return '';
    }
}

//set cookie
if (!function_exists('helperSetCookie')) {
    function helperSetCookie($name, $value, $time = null)
    {
        if ($time == null) {
            $time = time() + (86400 * 30);
        }
        $params = [
            'expires' => $time,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        if (!empty(env('cookie.prefix'))) {
            $name = env('cookie.prefix') . $name;
        }
        setcookie($name, $value, $params);
    }
}

//get cookie
if (!function_exists('helperGetCookie')) {
    function helperGetCookie($name)
    {
        if (!empty(env('cookie.prefix'))) {
            $name = env('cookie.prefix') . $name;
        }
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return false;
    }
}

//delete cookie
if (!function_exists('helperDeleteCookie')) {
    function helperDeleteCookie($name)
    {
        if (!empty(helperGetCookie($name))) {
            helperSetCookie($name, '', time() - 3600);
        }
    }
}

//set session
if (!function_exists('helperSetSession')) {
    function helperSetSession($name, $value)
    {
        $session = \Config\Services::session();
        $session->set($name, $value);
    }
}

//get session
if (!function_exists('helperGetSession')) {
    function helperGetSession($name)
    {
        $session = \Config\Services::session();
        if ($session->get($name) !== null) {
            return $session->get($name);
        }
        return null;
    }
}

//delete session
if (!function_exists('helperDeleteSession')) {
    function helperDeleteSession($name)
    {
        $session = \Config\Services::session();
        if ($session->get($name) !== null) {
            $session->remove($name);
        }
    }
}

//get cache data
if (!function_exists('getCacheData')) {
    function getCacheData(string $cacheKey, callable $callback, string $cacheType = 'dynamic')
    {
        $generalSettings = getContextValue('generalSettings');

        // Location key for product cache
        $locationKey = '';
        if ($cacheType === 'product') {
            $defaultLocation = getContextValue('defaultLocation');
            if (!empty($defaultLocation->country_id) || !empty($defaultLocation->state_id)) {
                $locationKey = '_loc_' . ($defaultLocation->country_id ?? '');
                if (!empty($defaultLocation->state_id)) {
                    $locationKey .= '_' . $defaultLocation->state_id;
                }
            }
        }

        $cacheConfigs = [
            'static' => [
                'active' => !empty($generalSettings) && $generalSettings->static_cache_system == 1,
                'prefix' => 'cstatic_',
                'ttl' => defined('STATIC_CACHE_REFRESH_TIME') ? STATIC_CACHE_REFRESH_TIME : 86400, // 24 hours
            ],
            'category' => [
                'active' => !empty($generalSettings) && $generalSettings->category_cache_system == 1,
                'prefix' => 'ccat_',
                'ttl' => 604800, // 7 days
            ],
            'product' => [
                'active' => !empty($generalSettings) && $generalSettings->cache_system == 1,
                'prefix' => 'cproduct_',
                'ttl' => !empty($generalSettings) && is_numeric($generalSettings->cache_refresh_time) ? (int)$generalSettings->cache_refresh_time : 7200, // 2 hours
            ],
            'dynamic' => [ // Default case
                'active' => false
            ]
        ];

        $config = $cacheConfigs[$cacheType] ?? $cacheConfigs['dynamic'];
        if (empty($config['active'])) {
            return $callback();
        }

        // Construct the final cache key
        $finalCacheKey = $config['prefix'];
        $finalCacheKey .= $cacheKey;

        if ($cacheType === 'product' && !empty($locationKey)) {
            $finalCacheKey .= $locationKey;
        }

        $cachedData = cache($finalCacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        $dataToCache = $callback();

        if ($dataToCache !== null) {
            cache()->save($finalCacheKey, $dataToCache, $config['ttl']);
        }

        return $dataToCache;
    }
}

//reset cache data
if (!function_exists('resetCacheData')) {
    function resetCacheData($type = 'all')
    {
        $cachePath = WRITEPATH . 'cache/';
        $files = glob($cachePath . '*');

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if (!is_file($file) || basename($file) === 'index.html') {
                continue;
            }

            $shouldDelete = false;

            switch ($type) {
                case 'category':
                    if (strpos($file, 'ccat_') !== false) {
                        $shouldDelete = true;
                    }
                    break;

                case 'static':
                    if (strpos($file, 'cstatic_') !== false || strpos($file, 'ccat_') !== false) {
                        $shouldDelete = true;
                    }
                    break;

                case 'product':
                    if (strpos($file, 'cproduct_') !== false) {
                        $shouldDelete = true;
                    }
                    break;

                case 'all':
                default:
                    $shouldDelete = true;
                    break;
            }

            if ($shouldDelete) {
                unlink($file);
            }
        }
    }
}

//reset cache data on change
if (!function_exists('resetCacheDataOnChange')) {
    function resetCacheDataOnChange()
    {
        if (getContextValue('generalSettings')->refresh_cache_database_changes == 1) {
            resetCacheData('product');
        }
    }
}

//get checkbox value
if (!function_exists('getCheckboxValue')) {
    function getCheckboxValue($inputPost)
    {
        if (empty($inputPost)) {
            return 0;
        }
        return 1;
    }
}

//generate token
if (!function_exists('generateToken')) {
    function generateToken($isFileName = false)
    {
        $token = str_replace('.', '-', uniqid('', true));

        if ($isFileName) {
            return $token;
        }

        $token .= bin2hex(random_bytes(4));
        return hash('sha1', $token);
    }
}

//generate purchase code
if (!function_exists('generatePurchaseCode')) {
    function generatePurchaseCode()
    {
        $id = uniqid('', TRUE);
        $id = str_replace('.', '-', $id);
        $id .= '-' . rand(100000, 999999);
        $id .= '-' . rand(100000, 999999);
        return $id;
    }
}

//generate transaction number
if (!function_exists('generateTransactionNumber')) {
    function generateTransactionNumber()
    {
        $num = uniqid('', TRUE);
        return str_replace('.', '-', $num);
    }
}

//generate random string
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 6)
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $length);
    }
}

//delete file from local or storage
if (!function_exists('deleteStorageFile')) {
    function deleteStorageFile($path, $storage = 'local')
    {
        if (empty($path)) {
            return false;
        }

        if ($storage !== 'local') {
            try {
                $settings = getSettingsUnserialized('storage');
                $storageLib = new \App\Libraries\Storage($settings);
                return $storageLib->deleteObject($path, $storage);

            } catch (\Throwable $e) {
                return false;
            }
        }

        $fullPath = FCPATH . ltrim($path, '/');

        if (!file_exists($fullPath) || !is_file($fullPath) || !is_writable($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }
}

//add HTTPS
if (!function_exists('addHttpsToUrl')) {
    function addHttpsToUrl($url)
    {
        if (!is_scalar($url) || trim((string)$url) == '') {
            return '';
        }
        $url = trim((string)$url);
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }
}

//download file by path
if (!function_exists('downloadFileByPath')) {
    function downloadFileByPath(string $path, string $storage, string $orjName = 'file')
    {
        if (empty($path)) {
            return null;
        }

        // Sanitize filename
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($orjName));

        // Local storage
        if ($storage === 'local') {
            $fullPath = realpath(FCPATH . ltrim($path, '/'));
            $baseDir = realpath(FCPATH . 'uploads');

            if ($fullPath === false || !is_file($fullPath) || ($baseDir !== false && strpos($fullPath, $baseDir) !== 0)) {
                return null;
            }

            return \Config\Services::response()->download($fullPath, null)->setFileName($safeName);
        }

        // Remote storage
        try {
            $settings = getSettingsUnserialized('storage');
            $storageLib = new \App\Libraries\Storage($settings);

            $tempDir = FCPATH . 'uploads/temp/';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            if (!is_writable($tempDir)) {
                return null;
            }

            $tempFilePath = $tempDir . uniqid('download_', true) . '_' . $safeName;

            if ($storageLib->downloadFile($path, $tempFilePath, $storage)) {
                register_shutdown_function('unlink', $tempFilePath);

                return \Config\Services::response()->download($tempFilePath, null)->setFileName($safeName);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

//check if valid image url
if (!function_exists('isValidImageUrl')) {
    function isValidImageUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Use parse_url for validation as it handles non-ASCII paths better than filter_var
        $urlParts = parse_url($url);
        if ($urlParts === false || !isset($urlParts['scheme'], $urlParts['host'], $urlParts['path'])) {
            return false;
        }

        $imgExts = ['jpg', 'jpeg', 'webp', 'png', 'gif'];
        $extension = strtolower(pathinfo($urlParts['path'], PATHINFO_EXTENSION));

        return in_array($extension, $imgExts, true);
    }
}

//paginate
if (!function_exists('paginate')) {
    function paginate($perPage, $total)
    {
        $page = @intval(inputGet('page') ?? '');
        if (empty($page) || $page < 1) {
            $page = 1;
        }
        $pager = \Config\Services::pager();
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'default_full');
        $pageObject = new stdClass();
        $pageObject->page = $page;
        $pageObject->offset = ($page - 1) * $perPage;
        $pageObject->links = $pagerLinks;
        return $pageObject;
    }
}

//get valid page number
if (!function_exists('getValidPageNumber')) {
    function getValidPageNumber($input)
    {
        if (!empty($input)) {
            if (is_numeric($input) && $input > 0) {
                return (int)$input;
            }
        }
        return 1;
    }
}

//get previous page url
if (!function_exists('getPreviousPageURL')) {
    function getPreviousPageURL()
    {
        $currentUrl = current_url();
        $currentPage = inputGet('page') ?? 1;
        $previousPage = $currentPage > 1 ? $currentPage - 1 : 1;
        $urlComponents = parse_url($currentUrl);
        parse_str($urlComponents['query'] ?? '', $queryParams);
        $queryParams['page'] = $previousPage;
        $updatedQuery = http_build_query($queryParams);
        return $urlComponents['path'] . '?' . $updatedQuery;
    }
}

//date diff
if (!function_exists('dateDifference')) {
    function dateDifference($endDate, $startDate, $format = '%a')
    {
        $datetime1 = date_create($endDate);
        $datetime2 = date_create($startDate);
        $diff = date_diff($datetime1, $datetime2);
        $day = $diff->format($format) + 1;
        if ($startDate > $endDate) {
            $day = 0 - $day;
        }
        return $day;
    }
}

//date difference in hours
if (!function_exists('dateDifferenceInHours')) {
    function dateDifferenceInHours($date1, $date2)
    {
        $datetime1 = date_create($date1);
        $datetime2 = date_create($date2);
        $diff = date_diff($datetime1, $datetime2);
        $days = $diff->format('%a');
        $hours = $diff->format('%h');
        return $hours + ($days * 24);
    }
}

//check cron time
if (!function_exists('checkCronTime')) {
    function checkCronTime($hour)
    {
        if (empty(getContextValue('generalSettings')->last_cron_update) || dateDifferenceInHours(date('Y-m-d H:i:s'), getContextValue('generalSettings')->last_cron_update) >= $hour) {
            return true;
        }
        return false;
    }
}

//time ago
if (!function_exists('timeAgo')) {
    function timeAgo($timestamp)
    {
        if (empty($timestamp)) {
            return '';
        }
        $timeAgo = strtotime($timestamp);
        if (!$timeAgo) {
            return '';
        }
        $currentTime = time();
        $timeDifference = $currentTime - $timeAgo;
        if ($timeDifference < 60) {
            return trans("just_now");
        }

        $minute = 60;
        $hour = 60 * $minute;
        $day = 24 * $hour;
        $week = 7 * $day;
        $month = 30 * $day;
        $year = 365 * $day;

        switch (true) {
            case ($timeDifference < $hour):
                $minutes = round($timeDifference / $minute);
                return $minutes == 1 ? "1 " . trans("minute_ago") : "$minutes " . trans("minutes_ago");

            case ($timeDifference < $day):
                $hours = round($timeDifference / $hour);
                return $hours == 1 ? "1 " . trans("hour_ago") : "$hours " . trans("hours_ago");

            case ($timeDifference < $month):
                $days = round($timeDifference / $day);
                return $days == 1 ? "1 " . trans("day_ago") : "$days " . trans("days_ago");

            case ($timeDifference < $year):
                $months = round($timeDifference / $month);
                return $months == 1 ? "1 " . trans("month_ago") : "$months " . trans("months_ago");

            default:
                $years = round($timeDifference / $year);
                return $years == 1 ? "1 " . trans("year_ago") : "$years " . trans("years_ago");
        }
    }
}

//format size units
if (!function_exists('formatSizeUnits')) {
    function formatSizeUnits($bytes): string
    {
        $units = ['GB' => 1073741824, 'MB' => 1048576, 'KB' => 1024];

        foreach ($units as $unit => $value) {
            if ($bytes >= $value) {
                return number_format($bytes / $value, 2) . " $unit";
            }
        }

        if ($bytes === 1) {
            return '1 byte';
        }

        return $bytes > 1 ? "$bytes bytes" : '0 bytes';
    }
}

//reset flash data
if (!function_exists('resetFlashData')) {
    function resetFlashData()
    {
        $session = \Config\Services::session();
        $session->setFlashdata('errors', '');
        $session->setFlashdata('error', '');
        $session->setFlashdata('success', '');
    }
}

//get IP address
if (!function_exists('getIPAddress')) {
    function getIPAddress()
    {
        $request = \Config\Services::request();
        return $request->getIPAddress();
    }
}

//verify the Cloudflare Turnstile submission
if (!function_exists('verifyTurnstile')) {
    function verifyTurnstile()
    {
        $settings = getContextValue('generalSettings');

        if (!empty($settings->turnstile_status)) {
            $turnstile = new \App\Services\TurnstileService($settings);

            if (!$turnstile->verify()) {

                $request = \Config\Services::request();
                if ($request->isAJAX()) {
                    return false;
                } else {
                    setErrorMessage(trans("msg_bot_verification_failed"));
                    redirect()->back()->withInput()->send();
                    exit();
                }
            }

            return true;
        }
    }
}

//check newsletter modal
if (!function_exists('checkNewsletterModal')) {
    function checkNewsletterModal($newsletterSettings)
    {
        if ($newsletterSettings->status != 1 || $newsletterSettings->is_popup_active != 1) {
            return false;
        }

        $cookie = helperGetCookie('newsletter_mdl');
        $session = helperGetSession('newsletter_mdl');

        if (!empty($cookie) || !empty($session)) {
            return false;
        }

        $firstVisitTime = helperGetSession('first_visit_time');

        if (empty($firstVisitTime)) {
            helperSetSession('first_visit_time', time());
            return false;
        }

        $elapsed = time() - $firstVisitTime;

        if ($elapsed >= SHOW_NEWSLETTER_POPUP_TIME) {
            helperSetCookie('newsletter_mdl', '1');
            helperSetSession('newsletter_mdl', '1');
            return true;
        }

        return false;
    }
}

//get pwa logo
if (!function_exists('getPwaLogo')) {
    function getPwaLogo($generalSettings, $size = 'lg')
    {
        $pwaLogo = $generalSettings->pwa_logo;
        if (!empty($pwaLogo)) {
            $pwaLogoArr = unserializeData($pwaLogo);
            if (!empty($pwaLogoArr) && countItems($pwaLogoArr)) {
                if (!empty($pwaLogoArr[$size])) {
                    return $pwaLogoArr[$size];
                }
            }
        }
        return '';
    }
}

//get social links array
if (!function_exists('getSocialLinksArray')) {
    function getSocialLinksArray($obj = null, $personalWebsite = false)
    {
        $data = null;
        if (!empty($obj->social_media_data)) {
            $data = unserializeData($obj->social_media_data);
        }
        $array = array(
            array('name' => 'facebook', 'inputName' => 'facebook_url', 'value' => !empty($data) && !empty($data['facebook_url']) ? $data['facebook_url'] : ''),
            array('name' => 'twitter', 'inputName' => 'twitter_url', 'value' => !empty($data) && !empty($data['twitter_url']) ? $data['twitter_url'] : ''),
            array('name' => 'instagram', 'inputName' => 'instagram_url', 'value' => !empty($data) && !empty($data['instagram_url']) ? $data['instagram_url'] : ''),
            array('name' => 'tiktok', 'inputName' => 'tiktok_url', 'value' => !empty($data) && !empty($data['tiktok_url']) ? $data['tiktok_url'] : ''),
            array('name' => 'whatsapp', 'inputName' => 'whatsapp_url', 'value' => !empty($data) && !empty($data['whatsapp_url']) ? $data['whatsapp_url'] : ''),
            array('name' => 'youtube', 'inputName' => 'youtube_url', 'value' => !empty($data) && !empty($data['youtube_url']) ? $data['youtube_url'] : ''),
            array('name' => 'discord', 'inputName' => 'discord_url', 'value' => !empty($data) && !empty($data['discord_url']) ? $data['discord_url'] : ''),
            array('name' => 'telegram', 'inputName' => 'telegram_url', 'value' => !empty($data) && !empty($data['telegram_url']) ? $data['telegram_url'] : ''),
            array('name' => 'pinterest', 'inputName' => 'pinterest_url', 'value' => !empty($data) && !empty($data['pinterest_url']) ? $data['pinterest_url'] : ''),
            array('name' => 'linkedin', 'inputName' => 'linkedin_url', 'value' => !empty($data) && !empty($data['linkedin_url']) ? $data['linkedin_url'] : ''),
            array('name' => 'twitch', 'inputName' => 'twitch_url', 'value' => !empty($data) && !empty($data['twitch_url']) ? $data['twitch_url'] : ''),
            array('name' => 'vk', 'inputName' => 'vk_url', 'value' => !empty($data) && !empty($data['vk_url']) ? $data['vk_url'] : '')
        );
        if ($personalWebsite == true) {
            array_push($array, array('name' => 'globe', 'inputName' => 'personal_website_url', 'value' => !empty($data) && !empty($data['personal_website_url']) ? $data['personal_website_url'] : ''));
        }
        return $array;
    }
}

//convert number short version
function numberFormatShort($n, $prec = 1)
{
    if ($n < 999) {
        $nFormat = number_format($n, $prec);
        $suffix = '';
    } else if ($n < 900000) {
        $nFormat = number_format($n / 1000, $prec);
        $suffix = trans("number_short_thousand");
    } else if ($n < 900000000) {
        $nFormat = number_format($n / 1000000, $prec);
        $suffix = trans("number_short_million");
    } else if ($n < 900000000000) {
        $nFormat = number_format($n / 1000000000, $prec);
        $suffix = trans("number_short_billion");
    } else {
        $nFormat = number_format($n / 1000000000000, $prec);
        $suffix = 't';
    }
    if ($prec > 0) {
        $dotzero = '.' . str_repeat('0', $prec);
        $nFormat = str_replace($dotzero, '', $nFormat);
    }
    return $nFormat . $suffix;
}

//add https to the links
if (!function_exists('addHttpsToUrl')) {
    function addHttpsToUrl($url)
    {
        if (!empty($url)) {
            $url = trim($url);
            if (!empty($url)) {
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
            }
            return $url;
        }
    }
}

//safe implode id
if (!function_exists('implodeSafeIds')) {
    function implodeSafeIds($ids, $separator = ',')
    {
        if (empty($ids) || !is_array($ids)) {
            return '';
        }

        $safeIds = array_filter(array_map('intval', $ids), function ($id) {
            return $id !== null && $id > 0;
        });

        return implode($separator, $safeIds);
    }
}

//get ai writer
if (!function_exists('aiWriter')) {
    function aiWriter()
    {
        $settings = getContextValue('generalSettings')->ai_writer ?? null;
        $data = is_string($settings) ? unserializeData($settings) : [];

        $aiWriter = new \stdClass();
        $aiWriter->status = !empty($data['status']);
        $aiWriter->apiKey = $data['api_key'] ?? '';

        return $aiWriter;
    }
}


if (!function_exists('renderTextEditorAdmin')) {
    function renderTextEditorAdmin($name, $label, $content = '', $fileManager = true, $aiWriter = true, $class = 'tinyMCE')
    {
        return view('admin/includes/_text_editor', [
            'editorInputName' => $name,
            'editorLabel' => $label,
            'editorContent' => $content,
            'editorFileManager' => $fileManager,
            'editorAiWriter' => $aiWriter,
            'editorClass' => $class
        ]);
    }
}

//convert xml character
if (!function_exists('convertToXmlCharacter')) {
    function convertToXmlCharacter($str)
    {
        if (!empty($str)) {
            return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $str);
        }
    }
}

//get controller name
if (!function_exists('getControllerName')) {
    function getControllerName()
    {
        $router = service('router');
        $controllerName = $router->controllerName();
        if (!empty($controllerName)) {
            $controllerName = str_replace('\App\Controllers\\', '', $controllerName);
        }
        return $controllerName;
    }
}

//format a decimal number naturally
if (!function_exists('formatDecimalClean')) {
    function formatDecimalClean($value, $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return rtrim(rtrim(number_format((float)$value, $decimals, '.', ''), '0'), '.');
    }
}

//return JSON response
if (!function_exists('jsonResponse')) {
    function jsonResponse(array $data = [], int $statusCode = 200)
    {
        return service('response')
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }
}

//encodes data into a JSON string with robust error handling and sensible defaults
if (!function_exists('safeJsonEncode')) {
    /**
     * Safely encodes data into a JSON string with robust error handling and sensible defaults.
     *
     * @param mixed $data The data to be encoded.
     * @param int $options Bitmask of json_encode options.
     * @param int $depth Maximum depth.
     * @return string|null The JSON encoded string, or null if encoding fails.
     */
    function safeJsonEncode(mixed $data, int $options = 0, int $depth = 512): ?string
    {
        // Set robust default options for clean, readable, and safe JSON.
        $defaultOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

        // Combine user-provided options with the defaults.
        $json = json_encode($data, $options | $defaultOptions, $depth);

        // Return null on failure instead of throwing an exception.
        if ($json === false) {
            return null;
        }

        return $json;
    }
}

//decodes a JSON string with robust error handling.
if (!function_exists('safeJsonDecode')) {
    /**
     * Safely decodes a JSON string with robust error handling.
     *
     * @param string|null $json The JSON string to decode.
     * @param bool $assoc When TRUE, returns an associative array. When FALSE, returns an object.
     * @param int $depth Maximum depth.
     * @param int $flags Bitmask of json_decode options.
     * @return mixed The decoded data (object, array, etc.), or null on failure or for empty input.
     */
    function safeJsonDecode(?string $json, bool $assoc = false, int $depth = 512, int $flags = 0): mixed
    {
        // Return null for empty or null input to avoid errors.
        if ($json === null || trim($json) === '') {
            return null;
        }

        // The $assoc parameter controls the output type. false = object, true = array.
        $data = json_decode($json, $assoc, $depth, $flags);

        // If decoding fails, json_last_error() will return a value other than JSON_ERROR_NONE.
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }
}

//sanitizes a string that is supposed to be JSON.
if (!function_exists('sanitizeJsonString')) {
    function sanitizeJsonString(?string $jsonString): ?string
    {
        if ($jsonString === null || trim($jsonString) === '') {
            return null;
        }
        // Decode the string into a PHP array.
        $decoded = safeJsonDecode($jsonString, true);

        // Check if decoding was successful and resulted in an array.
        if (is_array($decoded)) {
            // Re-encode it to a clean, standard JSON string.
            return safeJsonEncode($decoded);
        }

        // Return null if the original string was not valid JSON.
        return null;
    }
}

if (!function_exists('generateUrlWithExistingParams')) {
    function generateUrlWithExistingParams(string $path, array $except = [])
    {
        $baseUrl = langBaseUrl($path);

        $request = \Config\Services::request();
        $queryParams = $request->getGet();

        // Exclude any specified keys from the parameters.
        // This is useful for removing pagination when a new filter is applied.
        if (!empty($except)) {
            foreach ($except as $key) {
                unset($queryParams[$key]);
            }
        }

        // Also, always exclude the pagination parameter to start from the first page.
        unset($queryParams['page']);

        // Re-build the query string from the remaining parameters.
        if (!empty($queryParams)) {
            // http_build_query() will automatically and safely handle URL encoding.
            $queryString = http_build_query($queryParams);
            return $baseUrl . '?' . $queryString;
        }

        // If there are no other query parameters, return the base URL.
        return $baseUrl;
    }
}


if (!function_exists('generateProfileFilterUrl')) {
    function generateProfileFilterUrl(array $add = [], array $except = []): string
    {
        // Always start with the current page's URL, without its query string.
        $baseUrl = strtok(current_url(), '?');

        // Get all GET parameters from the current incoming request.
        $request = \Config\Services::request();
        $queryParams = $request->getGet();

        // Add or overwrite parameters with the ones from the $add array.
        $queryParams = array_merge($queryParams, $add);

        // Exclude any specified keys from the parameters.
        if (!empty($except)) {
            foreach ($except as $key) {
                unset($queryParams[$key]);
            }
        }

        // Always exclude the 'page' parameter to reset pagination.
        unset($queryParams['page']);

        // Re-build the query string.
        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams);
            return $baseUrl . '?' . $queryString;
        }

        return $baseUrl;
    }
}

// Takes an HTML string and removes unnecessary whitespace and comments.
if (!function_exists('minifyHtmlOutput')) {
    function minifyHtmlOutput(string $html): string
    {
        $search = [
            '/\>[^\S ]+/s',      // Whitespace after tags
            '/[^\S ]+\</s',      // Whitespace before tags
            '/(\s)+/s',          // Multiple whitespace to single
            '/<!--(.|\s)*?-->/'  // HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        $minifiedHtml = preg_replace($search, $replace, $html);

        return $minifiedHtml !== null ? trim($minifiedHtml) : '';
    }
}

if (!function_exists('cleanSeoUrl')) {
    /**
     * Removes query strings (like ?brand=x, ?sort=asc) from the URL.
     * However, it preserves the pagination parameter (?page=2).
     *
     * @param string $url
     * @return string
     */
    function cleanSeoUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        // strip everything after the question mark
        $cleanUrl = strtok($url, '?');

        // safely retrieve the 'page' parameter.
        $request = \Config\Services::request();
        $page = $request->getGet('page');
        if ($page && is_numeric($page) && $page > 1) {
            // if page number is greater than 1, append it back to the clean URL
            $cleanUrl .= '?page=' . $page;
        }

        return $cleanUrl;
    }
}

if (!function_exists('seoRobotsTag')) {
    /**
     * Sayfanın index durumunu belirler.
     * @param bool $shouldIndex True ise indexler, False ise indexlemez.
     */
    function seoRobotsTag(bool $shouldIndex = true): string
    {
        $content = $shouldIndex ? 'all' : 'noindex, follow';

        return '<meta name="robots" content="' . $content . '">' . PHP_EOL;
    }
}

if (!function_exists('seoCanonicalTag')) {
    /**
     * Generates the Canonical link tag for the current page.
     * Uses cleanSeoUrl to prevent duplicate content issues.
     *
     * @return string
     */
    function seoCanonicalTag(): string
    {
        $currentUrl = (string)current_url(true);

        $finalUrl = cleanSeoUrl($currentUrl);

        return '<link rel="canonical" href="' . escMeta($finalUrl) . '"/>';
    }
}

if (!function_exists('seoHreflangTags')) {
    /**
     * Generates hreflang tags for multi-language sites.
     * Cleans URLs to ensure they match the logic used in the Canonical tag.
     *
     * @param bool $isTranslatable
     * @return string
     */
    function seoHreflangTags(bool $isTranslatable = false): string
    {
        // get the active languages array
        $activeLanguages = function_exists('getContextValue') ? getContextValue('languages') : [];
        if (empty($activeLanguages) || count($activeLanguages) < 2) {
            return '';
        }

        $tags = [];

        foreach ($activeLanguages as $lang) {
            $rawUrl = getTranslatedUrl($lang->language_code, $isTranslatable);
            $cleanUrl = cleanSeoUrl($rawUrl);
            if (!empty($cleanUrl)) {
                $tags[] = '<link rel="alternate" hreflang="' . esc($lang->language_code, 'attr') . '" href="' . escMeta($cleanUrl) . '"/>';
            }
        }

        // generate the x-default tag (usually points to the default language or fallback)
        $generalSettings = function_exists('getContextValue') ? getContextValue('generalSettings') : null;
        $defaultLangCode = $generalSettings->site_lang_code ?? ($activeLanguages[0]->language_code ?? '');

        if ($defaultLangCode) {
            $rawDefaultUrl = getTranslatedUrl($defaultLangCode, $isTranslatable);
            $cleanDefaultUrl = cleanSeoUrl($rawDefaultUrl);

            if (!empty($cleanDefaultUrl)) {
                $tags[] = '<link rel="alternate" hreflang="x-default" href="' . escMeta($cleanDefaultUrl) . '"/>';
            }
        }

        return implode(PHP_EOL, $tags);
    }
}

if (!function_exists('generateUuidV4')) {
    /**
     * Generates a standard Version 4 UUID.
     *
     * @return string A 36-character UUID, e.g., "a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d"
     */
    function generateUuidV4(): string
    {
        // Generate 16 bytes of random data
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

/**
 * Generate the correct URL for the current page in a target language.
 */
if (!function_exists('getTranslatedUrl')) {
    function getTranslatedUrl(string $targetLangCode, bool $isTranslatable): ?string
    {
        // Find the full language object for the target language.
        $targetLang = null;
        foreach (getContextValue('languages') as $lang) {
            if ($lang->language_code == $targetLangCode) {
                $targetLang = $lang;
                break;
            }
        }
        if (!$targetLang) {
            return null;
        }

        // Determine the language prefix for the target URL (e.g., 'de/'). Empty for the default language.
        $targetLangShortForm = ($targetLang->id == getContextValue('generalSettings')->site_lang) ? '' : $targetLang->short_form . '/';

        if ($isTranslatable) {

            // Get the current URI object
            $uri = service('uri');
            $segments = $uri->getSegments();

            // Get an array of all known language short forms (excluding the default lang).
            $knownLangShortForms = [];
            foreach (getContextValue('languages') as $lang) {
                if ($lang->id != getContextValue('generalSettings')->site_lang) {
                    $knownLangShortForms[] = $lang->short_form;
                }
            }

            // Check if the first segment of the URI is a known language code.
            if (!empty($segments) && in_array($segments[0], $knownLangShortForms)) {
                array_shift($segments);
            }

            // Reconstruct the base path from the remaining segments.
            $basePath = implode('/', $segments);

            // Construct the new URL by prepending the target language's prefix to the base path.
            return base_url($targetLangShortForm . $basePath);
        } else {
            // This logic is for pages unique to a language (e.g., blog posts).
            // A hreflang link is only generated for the page's own language.
            $contentObject = getContextValue('post'); // Assuming context key is 'post'
            if ($contentObject && isset($contentObject->lang_id)) {
                if ($contentObject->lang_id == $targetLang->id) {
                    return getCurrentUrl();
                }
            }
        }
        return null;
    }
}