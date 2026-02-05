<?php

use CodeIgniter\Router\RouteCollection;

$uri = service('uri');
$serviceRoutes = [
    ['service', 'chat', 'sync']
];

$isServiceRoute = false;
foreach ($serviceRoutes as $routeSegments) {
    if ($uri->getTotalSegments() !== count($routeSegments)) {
        continue;
    }
    $match = true;
    for ($i = 0; $i < count($routeSegments); $i++) {
        if ($uri->getSegment($i + 1) !== $routeSegments[$i]) {
            $match = false;
            break;
        }
    }
    if ($match) {
        $isServiceRoute = true;
        break;
    }
}

if ($isServiceRoute) {
    $routes->post('service/chat/sync', 'ServiceController::syncChat');
    return;
}

$languages = getContextValue('languages');
$generalSettings = getContextValue('generalSettings');
$csrt = getContextValue('routes');
$rtAdmin = $csrt->admin;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');
$routes->get('/' . $csrt->affiliate . '/(:any)', 'HomeController::affiliate/$1');
$routes->get('cron/update-sitemap', 'HomeController::cronUpdateSitemap');
$routes->get('auth/unsubscribe', 'HomeController::unSubscribe');
$routes->get('connect-with-facebook', 'AuthController::connectWithFacebook');
$routes->get('facebook-callback', 'AuthController::facebookCallback');
$routes->get('connect-with-google', 'AuthController::connectWithGoogle');
$routes->get('connect-with-vk', 'AuthController::connectWithVk');

/*
 * --------------------------------------------------------------------
 * Admin Routes
 * --------------------------------------------------------------------
 */

$routes->get($rtAdmin, 'AdminController::index');
//navigation
$routes->get($rtAdmin . '/theme', 'AdminController::theme');
$routes->get($rtAdmin . '/homepage-manager', 'AdminController::homepageManager');
$routes->get($rtAdmin . '/edit-banner/(:num)', 'AdminController::editIndexBanner/$1');
//slider
$routes->get($rtAdmin . '/slider', 'AdminController::slider');
$routes->get($rtAdmin . '/edit-slider-item/(:num)', 'AdminController::editSliderItem/$1');
//page
$routes->get($rtAdmin . '/add-page', 'AdminController::addPage');
$routes->get($rtAdmin . '/edit-page/(:num)', 'AdminController::editPage/$1');
$routes->get($rtAdmin . '/pages', 'AdminController::pages');
//order
$routes->get($rtAdmin . '/orders', 'OrderAdminController::orders');
$routes->get($rtAdmin . '/order-details/(:num)', 'OrderAdminController::orderDetails/$1');
$routes->get($rtAdmin . '/transactions', 'OrderAdminController::transactions');
$routes->get($rtAdmin . '/digital-sales', 'OrderAdminController::digitalSales');
//product
$routes->get($rtAdmin . '/products', 'ProductController::products');
$routes->get($rtAdmin . '/product-details/(:num)', 'ProductController::productDetails/$1');
$routes->get($rtAdmin . '/featured-products-pricing', 'ProductController::featuredProductsPricing');
//payments
$routes->get($rtAdmin . '/membership-payments', 'AdminController::membershipPayments');
$routes->get($rtAdmin . '/promotion-payments', 'AdminController::promotionPayments');
$routes->get($rtAdmin . '/wallet-deposits', 'AdminController::walletDeposits');
$routes->get($rtAdmin . '/bank-transfer-reports', 'AdminController::bankTransferReports');
//bidding
$routes->get($rtAdmin . '/quote-requests', 'ProductController::quoteRequests');
//category
$routes->get($rtAdmin . '/add-category', 'CategoryController::addCategory');
$routes->get($rtAdmin . '/categories', 'CategoryController::categories');
$routes->get($rtAdmin . '/edit-category/(:num)', 'CategoryController::editCategory/$1');
$routes->get($rtAdmin . '/tags', 'CategoryController::tags');
//bulk
$routes->get($rtAdmin . '/bulk-category-upload', 'BulkController::categoryUpload');
$routes->get($rtAdmin . '/bulk-custom-field-upload', 'BulkController::bulkCustomFieldUpload');
//brand
$routes->get($rtAdmin . '/brands', 'CategoryController::brands');
$routes->get($rtAdmin . '/add-brand', 'CategoryController::AddBrand');
$routes->get($rtAdmin . '/edit-brand/(:num)', 'CategoryController::editBrand/$1');
//custom fields
$routes->get($rtAdmin . '/add-custom-field', 'CategoryController::addCustomField');
$routes->get($rtAdmin . '/custom-fields', 'CategoryController::customFields');
$routes->get($rtAdmin . '/edit-custom-field/(:num)', 'CategoryController::editCustomField/$1');
$routes->get($rtAdmin . '/custom-field-options/(:num)', 'CategoryController::customFieldOptions/$1');
//earnings
$routes->get($rtAdmin . '/earnings', 'EarningsController::earnings');
$routes->get($rtAdmin . '/payout-requests', 'EarningsController::payoutRequests');
$routes->get($rtAdmin . '/payout-settings', 'EarningsController::payoutSettings');
$routes->get($rtAdmin . '/add-payout', 'EarningsController::addPayout');
$routes->get($rtAdmin . '/seller-balances', 'EarningsController::sellerBalances');
//blog
$routes->get($rtAdmin . '/blog-add-post', 'BlogController::addPost');
$routes->get($rtAdmin . '/blog-posts', 'BlogController::posts');
$routes->get($rtAdmin . '/edit-blog-post/(:num)', 'BlogController::editPost/$1');
$routes->get($rtAdmin . '/blog-categories', 'BlogController::categories');
$routes->get($rtAdmin . '/edit-blog-category/(:num)', 'BlogController::editCategory/$1');
//comments & contant & reviews
$routes->get($rtAdmin . '/pending-product-comments', 'ProductController::pendingComments');
$routes->get($rtAdmin . '/product-comments', 'ProductController::comments');
$routes->get($rtAdmin . '/pending-blog-comments', 'BlogController::pendingComments');
$routes->get($rtAdmin . '/blog-comments', 'BlogController::comments');
$routes->get($rtAdmin . '/reviews', 'ProductController::reviews');
$routes->get($rtAdmin . '/contact-messages', 'AdminController::contactMessages');
$routes->get($rtAdmin . '/chat-messages', 'AdminController::chatMessages');
//abuse reports
$routes->get($rtAdmin . '/abuse-reports', 'AdminController::abuseReports');
//ad spaces
$routes->get($rtAdmin . '/ad-spaces', 'AdminController::adSpaces');
//seo tools
$routes->get($rtAdmin . '/seo-tools', 'AdminController::seoTools');
//location
$routes->get($rtAdmin . '/location-settings', 'AdminController::locationSettings');
$routes->get($rtAdmin . '/countries', 'AdminController::countries');
$routes->get($rtAdmin . '/states', 'AdminController::states');
$routes->get($rtAdmin . '/add-country', 'AdminController::addCountry');
$routes->get($rtAdmin . '/edit-country/(:num)', 'AdminController::editCountry/$1');
$routes->get($rtAdmin . '/add-state', 'AdminController::addState');
$routes->get($rtAdmin . '/edit-state/(:num)', 'AdminController::editState/$1');
$routes->get($rtAdmin . '/cities', 'AdminController::cities');
$routes->get($rtAdmin . '/add-city', 'AdminController::addCity');
$routes->get($rtAdmin . '/edit-city/(:num)', 'AdminController::editCity/$1');
//membership
$routes->get($rtAdmin . '/users', 'MembershipController::users');
$routes->get($rtAdmin . '/user-login-activities', 'MembershipController::userLoginActivities');
$routes->get($rtAdmin . '/account-deletion-requests', 'MembershipController::accountDeletionRequests');
$routes->get($rtAdmin . '/shop-opening-requests', 'MembershipController::shopOpeningRequests');
$routes->get($rtAdmin . '/add-user', 'MembershipController::addUser');
$routes->get($rtAdmin . '/edit-user/(:num)', 'MembershipController::editUser/$1');
$routes->get($rtAdmin . '/user-details/(:num)', 'MembershipController::userDetails/$1');
$routes->get($rtAdmin . '/membership-plans', 'MembershipController::membershipPlans');
$routes->get($rtAdmin . '/edit-plan/(:num)', 'MembershipController::editPlan/$1');
$routes->get($rtAdmin . '/roles-permissions', 'MembershipController::rolesPermissions');
$routes->get($rtAdmin . '/add-role', 'MembershipController::addRole');
$routes->get($rtAdmin . '/edit-role/(:num)', 'MembershipController::editRole/$1');
//support
$routes->get($rtAdmin . '/knowledge-base', 'SupportAdminController::knowledgeBase');
$routes->get($rtAdmin . '/knowledge-base/add-content', 'SupportAdminController::addContent');
$routes->get($rtAdmin . '/knowledge-base/edit-content/(:num)', 'SupportAdminController::editContent/$1');
$routes->get($rtAdmin . '/knowledge-base-categories', 'SupportAdminController::categories');
$routes->get($rtAdmin . '/knowledge-base/add-category', 'SupportAdminController::addCategory');
$routes->get($rtAdmin . '/knowledge-base/edit-category/(:num)', 'SupportAdminController::editCategory/$1');
$routes->get($rtAdmin . '/support-tickets', 'SupportAdminController::supportTickets');
$routes->get($rtAdmin . '/support-ticket/(:num)', 'SupportAdminController::supportTicket/$1');
//refund
$routes->get($rtAdmin . '/refund-requests', 'OrderAdminController::refundRequests');
$routes->get($rtAdmin . '/refund-requests/(:num)', 'OrderAdminController::refund/$1');
//languages
$routes->get($rtAdmin . '/language-settings', 'LanguageController::languageSettings');
$routes->get($rtAdmin . '/edit-language/(:num)', 'LanguageController::editLanguage/$1');
$routes->get($rtAdmin . '/edit-translations/(:num)', 'LanguageController::editTranslations/$1');
$routes->get($rtAdmin . '/search-phrases', 'LanguageController::searchPhrases');
//newsletter
$routes->get($rtAdmin . '/newsletter', 'AdminController::newsletter');
$routes->get($rtAdmin . '/newsletter-send-email', 'AdminController::newsletterSendEmail');
//affiliate program
$routes->get($rtAdmin . '/affiliate-program', 'AdminController::affiliateProgram');
//currency
$routes->get($rtAdmin . '/currency-settings', 'AdminController::currencySettings');
$routes->get($rtAdmin . '/add-currency', 'AdminController::addCurrency');
$routes->get($rtAdmin . '/edit-currency/(:num)', 'AdminController::editCurrency/$1');
//settings
$routes->get($rtAdmin . '/general-settings', 'AdminController::generalSettings');
$routes->get($rtAdmin . '/email-settings', 'AdminController::emailSettings');
$routes->get($rtAdmin . '/social-login', 'AdminController::socialLoginSettings');
$routes->get($rtAdmin . '/visual-settings', 'AdminController::visualSettings');
$routes->get($rtAdmin . '/preferences', 'AdminController::preferences');
$routes->get($rtAdmin . '/product-settings', 'AdminController::productSettings');
$routes->get($rtAdmin . '/font-settings', 'AdminController::fontSettings');
$routes->get($rtAdmin . '/edit-font/(:num)', 'AdminController::editFont/$1');
$routes->get($rtAdmin . '/route-settings', 'AdminController::routeSettings');
$routes->get($rtAdmin . '/cache-system', 'AdminController::cacheSystem');
$routes->get($rtAdmin . '/payment-settings', 'AdminController::paymentSettings');
$routes->get($rtAdmin . '/add-tax', 'AdminController::addTax');
$routes->get($rtAdmin . '/edit-tax/(:num)', 'AdminController::editTax/$1');
//home
$routes->post('contact-post', 'HomeController::contactPost');
$routes->post('set-selected-currency-post', 'HomeController::setSelectedCurrency');
$routes->post('submit-request-post', 'SupportController::submitRequestPost');
$routes->post('reply-ticket-post', 'SupportController::replyTicketPost');
$routes->get('download-attachment', 'SupportController::downloadAttachment');
//auth
$routes->post('login-post', 'AuthController::loginPost');
$routes->post('forgot-password-post', 'AuthController::forgotPasswordPost');
$routes->post('reset-password-post', 'AuthController::resetPasswordPost');
$routes->post('register-post', 'AuthController::registerPost');
$routes->get('confirm-account', 'AuthController::confirmAccount');
$routes->post('logout', 'AuthController::logout');
$routes->get($rtAdmin . '/login', 'AuthController::adminLogin');
$routes->post($rtAdmin . '/login-post', 'AuthController::adminLoginPost');
//bidding
$routes->post('bidding/submit-quote-post', 'DashboardController::submitQuotePost');
$routes->post('bidding/request-quote-post', 'OrderController::requestQuotePost');
$routes->post('bidding/accept-quote-post', 'OrderController::acceptQuote');
$routes->post('bidding/reject-quote-post', 'OrderController::rejectQuote');
//cart
$routes->post('cart/add-to-cart', 'CartController::addToCart');
$routes->post('add-to-cart-quote', 'CartController::addToCartQuote');
$routes->post('cart/update-quantity', 'CartController::updateCartItemQuantity');
$routes->post('cart/payment-method-post', 'CartController::paymentMethodPost');
$routes->post('cart/coupon-code-post', 'CartController::couponCodePost');
//checkout
$routes->post('checkout/complete-bank-transfer-order', 'CheckoutController::placeBankTransferOrder');
$routes->post('checkout/complete-paypal-payment', 'CheckoutController::completePaypalPayment');
$routes->post('checkout/complete-paystack-payment', 'CheckoutController::completePaystackPayment');
$routes->post('checkout/complete-razorpay-payment', 'CheckoutController::completeRazorpayPayment');
$routes->post('checkout/complete-midtrans-payment', 'CheckoutController::completeMidtransPayment');
$routes->post('checkout/complete-bank-transfer-payment', 'CheckoutController::completeBankTransferPayment');
$routes->post('checkout/complete-cash-on-delivery-payment', 'CheckoutController::completeCashOnDeliveryPayment');
$routes->post('checkout/complete-wallet-balance-payment', 'CheckoutController::completeWalletBalancePayment');
$routes->get('checkout/complete-stripe-payment', 'CheckoutController::completeStripePayment');
$routes->get('checkout/complete-flutterwave-payment', 'CheckoutController::completeFlutterwavePayment');
$routes->get('checkout/complete-dlocalgo-payment', 'CheckoutController::completeDLocalGoPayment');
$routes->get('checkout/complete-iyzico-payment', 'CheckoutController::completeIyzicoPayment');
$routes->get('checkout/complete-yoomoney-payment', 'CheckoutController::completeYoomoneyPayment');
$routes->get('checkout/complete-paytabs-payment', 'CheckoutController::completePayTabsPayment');
$routes->get('checkout/complete-mercado-pago-payment', 'CheckoutController::completeMercadoPagoPayment');

//payment webhooks
$routes->post('payment/webhook/stripe', 'CheckoutController::handleStripeWebhook');
$routes->post('payment/webhook/paytabs', 'CheckoutController::handlePayTabsWebhook');
$routes->post('payment/webhook/dlocalgo', 'CheckoutController::handleDlocalGoWebhook');
$routes->post('payment/webhook/razorpay', 'CheckoutController::handleRazorpayWebhook');
$routes->post('payment/webhook/yoomoney', 'CheckoutController::handleYooMoneyWebhook');
$routes->post('payment/webhook/mercado-pago', 'CheckoutController::handleMercadoPagoWebhook');

//order
$routes->post('submit-refund-request', 'OrderController::submitRefundRequest');
$routes->post('add-refund-message', 'OrderController::addRefundMessage');
//wallet
$routes->post('wallet/new-payout-request-post', 'ProfileController::newPayoutRequestPost');
$routes->post('wallet/set-payout-account-post', 'ProfileController::setPayoutAccountPost');
//message
$routes->post('send-message-post', 'HomeController::sendMessagePost');
//file
$routes->post('upload-digital-file-post', 'FileController::uploadDigitalFile');
$routes->post('upload-video-post', 'FileController::uploadVideo');
$routes->post('load-video-preview-post', 'FileController::loadVideoPreview');
$routes->post('download-purchased-digital-file-post', 'FileController::downloadPurchasedDigitalFile');
$routes->post('download-free-digital-file-post', 'FileController::downloadFreeDigitalFile');
//product
$routes->post('add-product-post', 'DashboardController::addProductPost');
$routes->post('edit-product-post', 'DashboardController::editProductPost');
$routes->post('edit-product-details-post', 'DashboardController::editProductDetailsPost');
$routes->post('start-selling-post', 'HomeController::startSellingPost');
//profile
$routes->post('social-media-post', 'ProfileController::socialMediaPost');
$routes->post('edit-profile-post', 'ProfileController::editProfilePost');
$routes->post('cover-image-post', 'ProfileController::coverImagePost');
$routes->post('follow-unfollow-user-post', 'ProfileController::followUnfollowUser');
$routes->post('change-password-post', 'ProfileController::changePasswordPost');
$routes->post('delete-account-post', 'ProfileController::deleteAccountPost');

$routes->post('savecourier', 'ProfileController::saveCourier');
$routes->post('add-shipping-address-post', 'ProfileController::addShippingAddressPost');
$routes->post('edit-shipping-address-post', 'ProfileController::editShippingAddressPost');
$routes->post('getprov/', 'ProfileController::getProv');
$routes->post('getcities/(:num)', 'ProfileController::getCities/$1');
$routes->post('getdistrict/(:num)', 'ProfileController::getDistrict/$1');
$routes->post('edit-location-post', 'ProfileController::locationPost');
//shop & shipping settings
$routes->post('shop-settings-post', 'DashboardController::shopSettingsPost');
$routes->post('add-shipping-zone-post', 'DashboardController::addShippingZonePost');
$routes->post('edit-shipping-zone-post', 'DashboardController::editShippingZonePost');
$routes->post('add-shipping-delivery-time-post', 'DashboardController::addShippingDeliveryTimePost');
$routes->post('edit-shipping-delivery-time-post', 'DashboardController::editShippingDeliveryTimePost');
//order dash
$routes->post('update-order-product-status-post', 'DashboardController::updateOrderProductStatusPost');
//promote
$routes->post('promote-product-post', 'DashboardController::promoteProductPost');
//coupon
$routes->post('add-coupon-post', 'DashboardController::addCouponPost');
$routes->post('edit-coupon-post', 'DashboardController::editCouponPost');

$routes->get('File/downloadShopDocument', 'FileController::downloadShopDocument');
$routes->get('Ajax/loadProducts', 'AjaxController::loadProducts');
$routes->get('ajax/run-queue-worker', 'AjaxController::runQueueWorker');

$postArray = [
    //Admin
    'Admin/editSliderSettingsPost',
    'Admin/deleteAbuseReportPost',
    'Admin/adSpacesPost',
    'Admin/googleAdsenseCodePost',
    'Admin/cacheSystemPost',
    'Admin/deleteContactMessagePost',
    'Admin/themePost',
    'Admin/generateSitemapPost',
    'Admin/downloadSitemapPost',
    'Admin/deleteSitemapPost',
    'Admin/seoToolsPost',
    'Admin/storageSettingsPost',
    'Admin/addCurrencyPost',
    'Admin/currencySettingsPost',
    'Admin/currencyConverterPost',
    'Admin/updateCurrencyRates',
    'Admin/deleteCurrencyPost',
    'Admin/editCurrencyPost',
    'Admin/editFontPost',
    'Admin/setSiteFontPost',
    'Admin/addFontPost',
    'Admin/deleteFontPost',
    'Admin/editIndexBannerPost',
    'Admin/homepageManagerPost',
    'Admin/deleteIndexBannerPost',
    'Admin/homepageManagerSettingsPost',
    'Admin/addIndexBannerPost',
    'Admin/setActiveLanguagePost',
    'Admin/downloadDatabaseBackup',
    'Admin/addCityPost',
    'Admin/addCountryPost',
    'Admin/addStatePost',
    'Admin/deleteCityPost',
    'Admin/deleteCountryPost',
    'Admin/locationSettingsPost',
    'Admin/editCityPost',
    'Admin/editCountryPost',
    'Admin/editStatePost',
    'Admin/deleteStatePost',
    'Admin/newsletterSelectEmailsPost',
    'Admin/deleteSubscriberPost',
    'Admin/newsletterSettingsPost',
    'Admin/newsletterSendEmailPost',
    'Admin/addPagePost',
    'Admin/editPagePost',
    'Admin/deletePagePost',
    'Admin/emailSettingsPost',
    'Admin/sendTestEmailPost',
    'Admin/emailOptionsPost',
    'Admin/generalSettingsPost',
    'Admin/cloudflareTurnstileSettingsPost',
    'Admin/maintenanceModePost',
    'Admin/paymentGatewaySettingsPost',
    'Admin/commissionSettingsPost',
    'Admin/deleteTaxPost',
    'Admin/editTaxPost',
    'Admin/addTaxPost',
    'Admin/additionalInvoiceInfoPost',
    'Admin/preferencesPost',
    'Admin/aiWriterPost',
    'Admin/productSettingsPost',
    'Admin/routeSettingsPost',
    'Admin/socialLoginSettingsPost',
    'Admin/visualSettingsPost',
    'Admin/updateWatermarkSettingsPost',
    'Admin/editSliderItemPost',
    'Admin/addSliderItemPost',
    'Admin/deleteSliderItemPost',
    'Admin/activateInactivateCountries',
    'Admin/homepageManagerPost',
    'Admin/updateCurrencyRate',
    'Admin/affiliateProgramPost',
    'Admin/deleteChatPost',
    'Admin/deleteChatMessagePost',
    'Admin/approveMembershipPaymentPost',
    'Admin/approvePromotionPaymentPost',
    'Admin/approveWalletDepositPaymentPost',
    'Admin/deleteMembershipPaymentPost',
    'Admin/deletePromotionPaymentsPost',
    'Admin/bankTransferOptionsPost',
    'Admin/deleteWalletDepositPost',
    'Admin/deleteBankTransferPost',
    //Ajax
    'Ajax/addRemoveWishlist',
    'Ajax/getStates',
    'Ajax/getCities',
    'Ajax/getSubCategories',
    'Ajax/searchCategories',
    'Ajax/getSubCategories',
    'Ajax/getBlogCategoriesByLang',
    'Ajax/getCountriesByContinent',
    'Ajax/getStatesByCountry',
    'Ajax/addComment',
    'Ajax/loadMoreComments',
    'Ajax/addReviewPost',
    'Ajax/loadMoreReviews',
    'Ajax/deleteComment',
    'Ajax/deleteReview',
    'Ajax/loadSubCommentForm',
    'Ajax/addBlogComment',
    'Ajax/loadMoreBlogComments',
    'Ajax/deleteBlogComment',
    'Ajax/addChatPost',
    'Ajax/loadChatPost',
    'Ajax/sendMessagePost',
    'Ajax/deleteChatPost',
    'Ajax/reportAbusePost',
    'Ajax/ajaxSearch',
    'Ajax/loadMorePromotedProducts',
    'Ajax/hideCookiesWarning',
    'Ajax/getProductShippingCost',
    'Ajax/addToNewsletter',
    'Ajax/createAffiliateLink',
    'Ajax/selectCouponCategoryPost',
    'Ajax/selectCouponProductPost',
    'Ajax/getProductTagSuggestions',
    'Ajax/loadMoreUsers',
    'Ajax/loadMoreSubscribers',
    'Ajax/generateTextAI',
    'Ajax/loadUsersDropdown',
    'Ajax/loadFilterOptions',
    'Ajax/loadActiveCountries',
    //Auth
    'Auth/sendActivationEmailPost',
    'Auth/joinAffiliateProgramPost',
    //Blog
    'Blog/addPostPost',
    'Blog/addCategoryPost',
    'Blog/deleteCategoryPost',
    'Blog/approveCommentPost',
    'Blog/deleteComment',
    'Blog/editCategoryPost',
    'Blog/editPostPost',
    'Blog/deletePostPost',
    'Blog/approveSelectedComments',
    'Blog/deleteSelectedComments',
    'Blog/deletePostImagePost',
    //Bulk Upload
    'Bulk/uploadCsvFilePost',
    'Bulk/processCsvChunk',
    'Bulk/downloadCsvFilesPost',
    //Cart
    'Cart/removeCartDiscountCoupon',
    'Cart/removeFromCart',
    // 'Cart/getShippingMethodsByLocation',
    'Cart/getShippingFeeByDistrict',
    'Cart/shippingPost',
    //Category
    'Category/deleteBrandPost',
    'Category/editBrandPost',
    'Category/brandSettingsPost',
    'Category/addBrandPost',
    'Category/addCategoryPost',
    'Category/addCustomFieldPost',
    'Category/generateCsvObjectPost',
    'Category/categorySettingsPost',
    'Category/deleteCategoryPost',
    'Category/loadCategories',
    'Category/editCustomFieldOptionPost',
    'Category/addCustomFieldOptionPost',
    'Category/addCategoryToCustomField',
    'Category/customFieldSettingsPost',
    'Category/addRemoveCustomFieldFiltersPost',
    'Category/deleteCustomFieldPost',
    'Category/editCategoryPost',
    'Category/editCustomFieldPost',
    'Category/deleteCustomFieldOption',
    'Category/deleteCategoryFromField',
    'Category/editFeaturedCategoriesOrderPost',
    'Category/editIndexCategoriesOrderPost',
    'Category/deleteCategoryImagePost',
    'Category/addTagPost',
    'Category/editTagPost',
    'Category/deleteTagPost',
    //Checkout
    'Checkout/bankTransferPaymentPost',
    //Dashboard
    'Dashboard/deleteCouponPost',
    'Dashboard/generateCsvObjectPost',
    'Dashboard/importCsvItemPost',
    'Dashboard/duplicateProductPost',
    'Dashboard/deleteProduct',
    'Dashboard/approveDeclineRefund',
    'Dashboard/deleteShippingLocationPost',
    'Dashboard/selectShippingMethod',
    'Dashboard/deleteShippingMethodPost',
    'Dashboard/deleteShippingZonePost',
    'Dashboard/deleteShippingDeliveryTimePost',
    'Dashboard/addShippingMethodPost',
    'Dashboard/editShippingMethodPost',
    'Dashboard/addLicenseKeys',
    'Dashboard/deleteLicenseKey',
    'Dashboard/loadLicenseKeysList',
    'Dashboard/getSubCategories',
    'Dashboard/affiliateProgramPost',
    'Dashboard/addRemoveAffiliateProductPost',
    'Dashboard/exportTableDataPost',
    'Dashboard/cashOnDeliverySettingsPost',
    'Dashboard/shopPoliciesPost',
    //Earnings
    'Earnings/addPayoutPost',
    'Earnings/deleteEarningPost',
    'Earnings/completePayoutRequestPost',
    'Earnings/deletePayoutPost',
    'Earnings/payoutSettingsPost',
    'Earnings/editSellerBalancePost',
    //File
    'File/uploadImage',
    'File/uploadImageSession',
    'File/getUploadedImage',
    'File/uploadBlogImage',
    'File/getSessUploadedImage',
    'File/downloadDigitalFile',
    'File/setImageMainSession',
    'File/setImageMain',
    'File/deleteImageSession',
    'File/deleteImage',
    'File/deleteVideo',
    'File/uploadAudio',
    'File/loadAudioPreview',
    'File/deleteAudio',
    'File/deleteDigitalFile',
    'File/getBlogImages',
    'File/deleteBlogImage',
    'File/loadMoreBlogImages',
    'File/uploadFileManagerImagePost',
    'File/getFileManagerImages',
    'File/deleteFileManagerImage',
    'File/exportTableDataPost',
    'File/uploadOptionImage',
    'File/loadUploadedOptionImages',
    'File/deleteOptionImage',
    //Home
    'Home/selectMembershipPlanPost',
    'Home/setDefaultLocationPost',
    'Home/bankTransferPaymentReportPost',
    //Language
    'Language/editLanguagePost',
    'Language/setDefaultLanguagePost',
    'Language/exportLanguagePost',
    'Language/deleteLanguagePost',
    'Language/addLanguagePost',
    'Language/importLanguagePost',
    'Language/editTranslationsPost',
    //Membership
    'Membership/addRolePost',
    'Membership/addUserPost',
    'Membership/editPlanPost',
    'Membership/editRolePost',
    'Membership/editUserPost',
    'Membership/addPlanPost',
    'Membership/settingsPost',
    'Membership/deletePlanPost',
    'Membership/deleteRolePost',
    'Membership/approveShopOpeningRequest',
    'Membership/deleteUserPost',
    'Membership/assignMembershipPlanPost',
    'Membership/changeUserRolePost',
    'Membership/confirmUserEmail',
    'Membership/banRemoveBanUser',
    'Membership/addDeleteUserAffiliateProgram',
    'Membership/loginToUserAccountPost',
    'Membership/rejectShopOpeningRequest',
    'Membership/cancelAccountDeleteRequestPost',
    //OrderAdmin
    'OrderAdmin/deleteDigitalSalePost',
    'OrderAdmin/approveGuestOrderProduct',
    'OrderAdmin/deleteOrderProductPost',
    'OrderAdmin/updateOrderProductStatusPost',
    'OrderAdmin/orderPaymentReceivedPost',
    'OrderAdmin/deleteOrderPost',
    'OrderAdmin/deleteTransactionPost',
    'OrderAdmin/approveRefundPost',
    //Order
    'Order/deleteQuoteRequest',
    'Order/addRefundMessage',
    'Order/cancelOrderPost',
    'Order/approveOrderProductPost',
    'Order/cancelOrderPost',
    'Order/deleteQuoteRequest',
    //Product
    'Product/deleteReviewPost',
    'Product/deleteCommentPost',
    'Product/deleteQuoteRequestPost',
    'Product/approveCommentPost',
    'Product/deleteCommentPost',
    'Product/deleteProductPermanently',
    'Product/deleteProduct',
    'Product/featuredProductsPricingPost',
    'Product/addRemoveFeaturedProduct',
    'Product/rejectProduct',
    'Product/approveProduct',
    'Product/deleteSelectedProducts',
    'Product/deleteSelectedProductsPermanently',
    'Product/addRemoveSpecialOffer',
    'Product/restoreProduct',
    'Product/deleteSelectedReviews',
    'Product/approveSelectedComments',
    'Product/deleteSelectedComments',
    'Product/approveSelectedEditedProducts',
    //Profile
    'Profile/deleteCoverImagePost',
    'Profile/deleteShippingAddressPost',
    'Profile/addFundsPost',
    'Profile/deleteAffiliateLinkPost',
    //SupportAdmin
    'SupportAdmin/addCategoryPost',
    'SupportAdmin/addContentPost',
    'SupportAdmin/editCategoryPost',
    'SupportAdmin/editContentPost',
    'SupportAdmin/deleteContentPost',
    'SupportAdmin/deleteCategoryPost',
    'SupportAdmin/sendMessagePost',
    'SupportAdmin/deleteTicketPost',
    'SupportAdmin/changeTicketStatusPost',
    'SupportAdmin/getCategoriesByLang',
    //Support
    'Support/uploadSupportAttachment',
    'Support/deleteSupportAttachmentPost',
    'Support/closeTicketPost',
];

foreach ($postArray as $item) {
    $array = explode('/', $item);
    $routes->post($item, $array[0] . 'Controller::' . $array[1]);
}

/*
 * --------------------------------------------------------------------
 * Dynamic Routes
 * --------------------------------------------------------------------
 */

if (!empty($languages)) {
    foreach ($languages as $language) {
        $key = '';
        if ($generalSettings->site_lang != $language->id) {
            $key = $language->short_form . '/';
            $routes->get($language->short_form, 'HomeController::index');
        }
        $routes->get($key . $csrt->affiliate . '/(:any)', 'HomeController::affiliate/$1');
        //auth
        $routes->get($key . $csrt->register, 'AuthController::register');
        $routes->get($key . $csrt->register_success, 'AuthController::registerSuccess');
        $routes->get($key . $csrt->forgot_password, 'AuthController::forgotPassword');
        $routes->get($key . $csrt->reset_password, 'AuthController::resetPassword');
        //profile
        $routes->get($key . $csrt->profile . '/(:any)', 'ProfileController::profile/$1');
        $routes->get($key . $csrt->wishlist, 'HomeController::wishlist');
        $routes->get($key . $csrt->followers . '/(:any)', 'ProfileController::followers/$1');
        $routes->get($key . $csrt->following . '/(:any)', 'ProfileController::following/$1');
        $routes->get($key . $csrt->reviews . '/(:any)', 'ProfileController::reviews/$1');
        $routes->get($key . $csrt->my_reviews . '/(:any)', 'ProfileController::myReviews/$1');
        $routes->get($key . $csrt->shop_policies . '/(:any)', 'ProfileController::shopPolicies/$1');
        $routes->get($key . $csrt->my_coupons, 'ProfileController::myCoupons');
        //settings
        $routes->get($key . $csrt->settings, 'ProfileController::editProfile');
        $routes->get($key . $csrt->settings . '/' . $csrt->edit_profile, 'ProfileController::editProfile');
        $routes->get($key . $csrt->settings . '/' . $csrt->location, 'ProfileController::location');
        $routes->get($key . $csrt->settings . '/' . $csrt->shipping_address, 'ProfileController::shippingAddress');
        $routes->get($key . $csrt->settings . '/' . $csrt->affiliate_links, 'ProfileController::affiliateLinks');
        $routes->get($key . $csrt->settings . '/' . $csrt->social_media, 'ProfileController::socialMedia');
        $routes->get($key . $csrt->settings . '/' . $csrt->change_password, 'ProfileController::changePassword');
        $routes->get($key . $csrt->settings . '/' . $csrt->delete_account, 'ProfileController::deleteAccount');
        //wallet
        $routes->get($key . $csrt->wallet, 'ProfileController::wallet');
        //affiliate
        $routes->get($key . $csrt->affiliate_program, 'HomeController::affiliateProgram');
        //product
        $routes->get($key . $csrt->select_membership_plan, 'HomeController::selectMembershipPlan');
        $routes->get($key . $csrt->start_selling, 'HomeController::startSelling');
        $routes->get($key . $csrt->search, 'HomeController::search');
        $routes->get($key . $csrt->products, 'HomeController::products');
        $routes->get($key . $csrt->downloads, 'OrderController::downloads');
        //blog
        $routes->get($key . $csrt->blog, 'HomeController::blog');
        $routes->get($key . $csrt->blog . '/' . $csrt->tag . '/(:any)', 'HomeController::tag/$1');
        $routes->get($key . $csrt->blog . '/(:any)/(:any)', 'HomeController::post/$1/$2');
        $routes->get($key . $csrt->blog . '/(:any)', 'HomeController::blogCategory/$1');
        //shops
        $routes->get($key . $csrt->shops, 'HomeController::shops');
        //contact
        $routes->get($key . $csrt->contact, 'HomeController::contact');
        //chat
        $routes->get($key . $csrt->messages, 'HomeController::chat');
        //rss feeds
        $routes->get($key . $csrt->rss_feeds, 'HomeController::rssFeeds');
        $routes->get($key . 'rss/' . $csrt->latest_products, 'HomeController::latestProducts');
        $routes->get($key . 'rss/' . $csrt->featured_products, 'HomeController::featuredProducts');
        $routes->get($key . 'rss/' . $csrt->category . '/(:any)', 'HomeController::rssByCategory/$1');
        $routes->get($key . 'rss/' . $csrt->seller . '/(:any)', 'HomeController::rssBySeller/$1');
        //cart
        $routes->get($key . $csrt->cart, 'CartController::cart');
        $routes->get($key . $csrt->cart . '/' . $csrt->shipping, 'CartController::shipping');
        $routes->get($key . $csrt->cart . '/' . $csrt->payment_method, 'CartController::paymentMethod');
        $routes->get($key . $csrt->cart . '/' . $csrt->payment, 'CartController::payment');
        //orders
        $routes->get($key . $csrt->orders, 'OrderController::orders');
        $routes->get($key . $csrt->order_details . '/(:num)', 'OrderController::order/$1');
        $routes->get($key . $csrt->order_completed . '/(:num)', 'CheckoutController::orderCompleted/$1');
        $routes->get($key . $csrt->checkout . '/' . $csrt->service_payment_completed, 'CheckoutController::servicePaymentCompleted');
        $routes->get($key . 'invoice/(:num)', 'HomeController::invoice/$1');
        $routes->get($key . 'invoice-promotion/(:num)', 'HomeController::invoicePromotion/$1');
        $routes->get($key . 'invoice-membership/(:num)', 'HomeController::invoiceMembership/$1');
        $routes->get($key . 'invoice-wallet-deposit/(:num)', 'HomeController::invoiceWalletDeposit/$1');
        $routes->get($key . 'invoice-expense/(:num)', 'HomeController::invoiceExpense/$1');
        //refund
        $routes->get($key . $csrt->refund_requests, 'OrderController::refundRequests');
        $routes->get($key . $csrt->refund_requests . '/(:num)', 'OrderController::refund/$1');
        //bidding
        $routes->get($key . $csrt->quote_requests, 'OrderController::quoteRequests');
        //terms & conditions
        $routes->get($key . $csrt->terms_conditions, 'HomeController::termsConditions');
        //dashboard
        $routes->get($key . $csrt->dashboard, 'DashboardController::index');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->add_product, 'DashboardController::addProduct');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->product . '/' . $csrt->product_details . '/(:num)', 'DashboardController::editProductDetails/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->edit_product . '/(:num)', 'DashboardController::editProduct/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->products, 'DashboardController::products');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->bulk_product_upload, 'DashboardController::bulkProductUpload');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->sales, 'DashboardController::sales');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->sale . '/(:num)', 'DashboardController::sale/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->quote_requests, 'DashboardController::quoteRequests');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->cash_on_delivery, 'DashboardController::cashOnDelivery');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->payments, 'DashboardController::payments');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->comments, 'DashboardController::comments');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->reviews, 'DashboardController::reviews');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->shop_settings, 'DashboardController::shopSettings');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->shop_policies, 'DashboardController::shopPolicies');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->shipping_settings, 'DashboardController::shippingSettings');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->add_shipping_zone, 'DashboardController::addShippingZone');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->edit_shipping_zone . '/(:num)', 'DashboardController::editShippingZone/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->coupons, 'DashboardController::coupons');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->coupon_products . '/(:num)', 'DashboardController::couponProducts/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->add_coupon, 'DashboardController::addCoupon');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->edit_coupon . '/(:num)', 'DashboardController::editCoupon/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->refund_requests, 'DashboardController::refundRequests');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->refund_requests . '/(:num)', 'DashboardController::refund/$1');
        $routes->get($key . $csrt->dashboard . '/' . $csrt->affiliate_program, 'DashboardController::affiliateProgram');
        //help center
        $routes->get($key . $csrt->help_center, 'SupportController::helpCenter');
        $routes->get($key . $csrt->help_center . '/' . $csrt->tickets, 'SupportController::tickets');
        $routes->get($key . $csrt->help_center . '/' . $csrt->submit_request, 'SupportController::submitRequest');
        $routes->get($key . $csrt->help_center . '/' . $csrt->ticket . '/(:num)', 'SupportController::ticket/$1');
        $routes->get($key . $csrt->help_center . '/' . $csrt->search, 'SupportController::search');
        $routes->get($key . $csrt->help_center . '/' . $csrt->ticket . '/(:num)', 'SupportController::ticket/$1');
        $routes->get($key . $csrt->help_center . '/(:any)/(:any)', 'SupportController::article/$1/$2');
        $routes->get($key . $csrt->help_center . '/(:any)', 'SupportController::category/$1');

        if ($generalSettings->site_lang != $language->id) {
            $routes->get($key . '(:any)/(:any)/(:any)', 'HomeController::error404');
            $routes->get($key . '(:any)/(:any)', 'HomeController::subCategory/$1/$2');
            $routes->get($key . '(:any)', 'HomeController::any/$1');
        }
    }
}

$routes->get('(:any)/(:any)/(:any)', 'HomeController::error404');
$routes->get('(:any)/(:any)', 'HomeController::subCategory/$1/$2');
$routes->get('(:any)', 'HomeController::any/$1');
