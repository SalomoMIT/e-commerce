<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code


defined('MODESY_VERSION')                   || define('MODESY_VERSION', '2.6.3');
//app
defined('LIMIT_NAV_LEVEL2')                 || define('LIMIT_NAV_LEVEL2', 20);
defined('LIMIT_NAV_LEVEL3')                 || define('LIMIT_NAV_LEVEL3', 6);
defined('LIMIT_SPECIAL_OFFERS')             || define('LIMIT_SPECIAL_OFFERS', 30);
defined('LIMIT_RSS_PRODUCTS')               || define('LIMIT_RSS_PRODUCTS', 50);
defined('CUSTOM_FILTERS_LOAD_LIMIT')        || define('CUSTOM_FILTERS_LOAD_LIMIT', 50);
defined('CUSTOM_FILTERS_COLLAPSE_LIMIT')    || define('CUSTOM_FILTERS_COLLAPSE_LIMIT', 2);
defined('SHOW_CONTACT_INFO_GUEST')          || define('SHOW_CONTACT_INFO_GUEST', 1);
defined('LIMIT_EXPORT_ROW')                 || define('LIMIT_EXPORT_ROW', 5000);
defined('NAV_LARGER_CAT_LEVEL3_LIMIT')      || define('NAV_LARGER_CAT_LEVEL3_LIMIT', 30);
defined('CAT_QUERY_SEPARATOR')              || define('CAT_QUERY_SEPARATOR', '|||');
defined('CAT_QUERY_SEPARATOR_SUB')          || define('CAT_QUERY_SEPARATOR_SUB', ':::');
defined('CHAT_UPDATE_TIME')                 || define('CHAT_UPDATE_TIME', 3);
defined('AFFILIATE_COOKIE_NAME')            || define('AFFILIATE_COOKIE_NAME', 'aff_id');
defined('AFFILIATE_COOKIE_TIME')            || define('AFFILIATE_COOKIE_TIME', 30); // 30 DAYS
defined('SHOW_NEWSLETTER_POPUP_TIME')       || define('SHOW_NEWSLETTER_POPUP_TIME', 10); //Show after 10 seconds
//product image
defined('PRODUCT_IMAGE_QUALITY')            || define('PRODUCT_IMAGE_QUALITY', 85); //85%
defined('PRODUCT_IMAGE_SMALL')              || define('PRODUCT_IMAGE_SMALL', 480); // 480px width
defined('PRODUCT_IMAGE_DEFAULT')            || define('PRODUCT_IMAGE_DEFAULT', 960); // 960px width
defined('PRODUCT_IMAGE_BIG')                || define('PRODUCT_IMAGE_BIG', 1600); // 1600px width
//tags
defined('PRODUCT_TAGS_LIMIT')               || define('PRODUCT_TAGS_LIMIT', 15); //max number of tags per product
defined('PRODUCT_TAG_LIMIT')                || define('PRODUCT_TAG_LIMIT', 15); //max number of tags per product
defined('PRODUCT_TAG_CHAR_LIMIT')           || define('PRODUCT_TAG_CHAR_LIMIT', 100); //max number of characters per tag
//reviews&comments
defined('REVIEWS_LOAD_LIMIT')               || define('REVIEWS_LOAD_LIMIT', 10);
defined('COMMENTS_LOAD_LIMIT')              || define('COMMENTS_LOAD_LIMIT', 10);
defined('COMMENT_CHARACTER_LIMIT')          || define('COMMENT_CHARACTER_LIMIT', 5000);
defined('REVIEW_CHARACTER_LIMIT')           || define('REVIEW_CHARACTER_LIMIT', 10000);
defined('NUM_INDEX_CATEGORY_PRODUCTS')      || define('NUM_INDEX_CATEGORY_PRODUCTS', 15); //Number of products to display for each category on the homepage
//cache
defined('STATIC_CACHE_REFRESH_TIME')        || define('STATIC_CACHE_REFRESH_TIME', 604800); // 7 days - Cache for Static Data (Settings, Pages, Categories etc.)
defined('SHORT_CACHE_STATUS')               || define('SHORT_CACHE_STATUS', 1); // A short time cache to cache dynamic data
defined('SHORT_CACHE_REFRESH_TIME')         || define('SHORT_CACHE_REFRESH_TIME', 300); // 5 minutes
//shipping
defined('SHIPPING_VOLUMETRIC_DIVISOR')      || define('SHIPPING_VOLUMETRIC_DIVISOR', 5000);