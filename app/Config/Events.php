<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            //throw FrameworkException::forEnabledZlibOutputCompression();
            return;
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    // Iyzico Payment Callback Handler
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/mds-cl-iyzico-payment-redirect') !== false && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['token'] ?? null;
        if (empty($token)) {
            return;
        }

        $appBaseUrl = env('app.baseURL') ?: '/';
        $urlArray = parse_url($_SERVER['REQUEST_URI'] ?? '');
        if (!empty($urlArray['query'])) {
            parse_str($urlArray['query'], $paramArray);

            $conversationId = $paramArray['conversation_id'] ?? '';
            $lang = $paramArray['lang'] ?? '';
            $checkoutToken = $paramArray['checkout_token'] ?? '';

            $queryParams = http_build_query([
                'token' => $token,
                'conversation_id' => $conversationId,
                'lang' => $lang,
                'checkout_token' => $checkoutToken
            ]);

            header('Location: ' . rtrim($appBaseUrl, '/') . '/checkout/complete-iyzico-payment?' . $queryParams);
            exit();
        }

        header('Location: ' . $appBaseUrl);
        exit();
    }

    // PayTabs Payment Callback Handler
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/mds-cl-paytabs-payment-redirect') !== false && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $postData = $_POST ?? [];
        $lang = '';

        $queryString = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        if (!empty($queryString)) {
            parse_str($queryString, $queryParams);
            $lang = isset($queryParams['lang']) ? trim(strip_tags($queryParams['lang'])) : '';
        }

        $encodedData = base64_encode(json_encode($postData));
        $baseUrl = env('app.baseURL') ?: '/';
        $redirectUrl = rtrim($baseUrl, '/') . '/checkout/complete-paytabs-payment';
        $finalUrl = $redirectUrl . '?lang=' . urlencode($lang) . '&post_data=' . urlencode($encodedData);

        header('Location: ' . $finalUrl);
        exit();
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

Events::on(
    'DBQuery',
    static function (\CodeIgniter\Database\Query $query) {
        $sql = $query->getOriginalQuery();
        $lowerSql = strtolower($sql);
        if (!empty($lowerSql)) {
            if (strpos($lowerSql, 'select') === 0) {
                $operation = 'SELECT';
            } elseif (strpos($lowerSql, 'insert') === 0) {
                $operation = 'INSERT';
            } elseif (strpos($lowerSql, 'update') === 0) {
                $operation = 'UPDATE';
            } elseif (strpos($lowerSql, 'delete') === 0) {
                $operation = 'DELETE';
            } else {
                $operation = 'UNKNOWN';
            }

            //static cache
            $tablesAffected = ['ad_spaces', 'fonts', 'general_settings', 'languages', 'language_translations', 'pages', 'polls', 'roles_permissions', 'settings', 'blog_posts', 'slider', 'homepage_banners'];
            foreach ($tablesAffected as $table) {
                if (strpos($lowerSql, $table) !== false && in_array($operation, ['INSERT', 'UPDATE', 'DELETE'])) {
                    resetCacheData('static');
                }
            }

            //category cache
            $tablesAffected = ['categories', 'category_lang', 'category_paths', 'brands', 'brand_category', 'brand_lang',
                'custom_fields', 'custom_fields_category', 'custom_fields_options', 'custom_fields_product', 'custom_field_lang', 'custom_field_option_lang'];
            foreach ($tablesAffected as $table) {
                if (strpos($lowerSql, $table) !== false && in_array($operation, ['INSERT', 'UPDATE', 'DELETE'])) {
                    resetCacheData('category');
                }
            }
        }
    }
);