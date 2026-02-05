<?php

namespace App\Controllers;


use App\Models\AdModel;
use App\Models\CartModel;
use App\Models\CheckoutModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AuthModel;
use App\Models\CategoryModel;
use App\Models\CommonModel;
use App\Models\CurrencyModel;
use App\Models\LocationModel;
use App\Models\MembershipModel;
use App\Models\PageModel;
use App\Models\ProductModel;
use App\Models\SettingsModel;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['text', 'security', 'app', 'product'];

    public $session;
    public $settingsModel;
    public $authModel;
    public $pageModel;
    public $locationModel;
    public $categoryModel;
    public $productModel;
    public $commonModel;
    public $generalSettings;
    public $paymentSettings;
    public $productSettings;
    public $settings;
    public $activeLanguages;
    public $defaultLang;
    public $activeLang;
    public $currencies;
    public $defaultCurrency;
    public $selectedCurrency;
    public $activeFonts;
    public $menuLinks;
    public $activeCountries;
    public $adSpaces;
    public $parentCategories;
    public $affiliateSettings;
    public $baseVars;
    public $activeStorage;
    public $cartItemCount;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Load App Context Service
        service('appContext');

        // prevent iframe
        $this->response->setHeader('Content-Security-Policy', "frame-ancestors 'none';");

        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();
        $this->request = \Config\Services::request();

        //set active language
        if (strtolower($this->request->getMethod()) === 'post' && !empty(inputPost('sysLangId'))) {
            setContextActiveLang(inputPost('sysLangId'));
        }

        $this->settingsModel = new SettingsModel();
        $this->authModel = new AuthModel();
        $this->pageModel = new PageModel();
        $this->locationModel = new LocationModel();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->commonModel = new CommonModel();

        //general settings
        $this->generalSettings = getContextValue('generalSettings');
        //payment settings
        $this->paymentSettings = getContextValue('paymentSettings');
        //product settings
        $this->productSettings = getContextValue('productSettings');
        //affiliate settings
        $this->affiliateSettings = getSettingsUnserialized('affiliate');
        //settings
        $this->settings = getContextValue('settings');
        //active languages
        $this->activeLanguages = getContextValue('languages');
        //default lang
        $this->defaultLang = getContextValue('defaultLang');
        //active lang
        $this->activeLang = getContextValue('activeLang');
        //active storage
        $this->activeStorage = getContextValue('activeStorage');
        //currencies
        $this->currencies = getContextValue('currencies');
        //default currency
        $this->defaultCurrency = getContextValue('defaultCurrency');
        //default currency
        $this->selectedCurrency = getSelectedCurrency();
        //fonts
        $this->activeFonts = $this->settingsModel->getSelectedFonts($this->settings);
        //menu links
        $this->menuLinks = $this->pageModel->getMenuLinks($this->activeLang->id);
        //active countries
        $this->activeCountries = $this->locationModel->getActiveCountries();
        //ad spaces
        $this->adSpaces = $this->commonModel->getAdSpaces();
        //parent categories
        $this->parentCategories = $this->getParentCategories($this->activeLang->id);
        //set cart
        $cartModel = new CartModel();
        $cartRaw = $cartModel->fetchRawCartData();
        $this->cartItemCount = !empty($cartRaw) && !empty($cartRaw->num_items) ? $cartRaw->num_items : 0;

        //variables
        $this->baseVars = new \stdClass();
        $this->baseVars->appName = $this->generalSettings->application_name;
        $this->baseVars->rtl = false;
        $this->baseVars->unreadMessageCount = 0;
        $this->baseVars->usernameMaxlength = 40;
        $this->baseVars->perPage = 15;
        $this->baseVars->defaultLocation = getContextValue('defaultLocation');
        $this->baseVars->defaultLocationInput = $this->locationModel->getDefaultLocationInput($this->baseVars->defaultLocation);
        $this->baseVars->isSaleActive = false;
        $this->baseVars->decimalSeparator = $this->defaultCurrency->currency_format === 'european' ? ',' : '.';
        $this->baseVars->logoWidth = getLogoSize($this->generalSettings, 'width');
        $this->baseVars->logoHeight = getLogoSize($this->generalSettings, 'height');
        if ($this->activeLang->text_direction == 'rtl') {
            $this->baseVars->rtl = true;
        }
        if ($this->generalSettings->marketplace_system == 1 || $this->generalSettings->bidding_system == 1) {
            $this->baseVars->isSaleActive = true;
        }

        if (authCheck()) {
            $this->baseVars->unreadMessageCount = getUnreadChatsCount(user()->id);
        }

        //set if price single line
        $this->baseVars->isPriceSingleLine = false;
        $shortCurrencies = getAppDefault('shortCurrencies');
        if (in_array($this->selectedCurrency->code, $shortCurrencies)) {
            $this->baseVars->isPriceSingleLine = true;
        }

        //check maintenance mode
        $this->checkMaintenanceMode($this->generalSettings);

        if (checkCronTime(1)) {
            //update currency rates
            if ($this->paymentSettings->auto_update_exchange_rates == 1) {
                $currencyModel = new CurrencyModel();
                $currencyModel->updateCurrencyRates();
            }
            //check promoted products
            $this->productModel->checkPromotedProducts();

            //check users membership plans
            $membershipModel = new MembershipModel();
            $membershipModel->checkMembershipPlansExpired();

            //delete expired checkouts
            $checkoutModel = new CheckoutModel();
            $checkoutModel->deleteExpiredCheckouts();

            //delete old sessions
            $this->settingsModel->deleteOldSessions();

            //update cron time
            $this->settingsModel->setLastCronUpdate();
        }

        //view variables
        $view = \Config\Services::renderer();
        $view->setData([
            'generalSettings' => $this->generalSettings,
            'paymentSettings' => $this->paymentSettings,
            'productSettings' => $this->productSettings,
            'baseSettings' => $this->settings,
            'activeLanguages' => $this->activeLanguages,
            'defaultLang' => $this->defaultLang,
            'activeLang' => $this->activeLang,
            'activeStorage' => $this->activeStorage,
            'currencies' => $this->currencies,
            'defaultCurrency' => $this->defaultCurrency,
            'selectedCurrency' => $this->selectedCurrency,
            'activeFonts' => $this->activeFonts,
            'menuLinks' => $this->menuLinks,
            'activeCountries' => $this->activeCountries,
            'adSpaces' => $this->adSpaces,
            'parentCategories' => $this->parentCategories,
            'affiliateSettings' => $this->affiliateSettings,
            'cartItemCount' => $this->cartItemCount,
            'baseVars' => $this->baseVars
        ]);
    }

    /**
     * Check if maintenance mode should be displayed
     */
    private function checkMaintenanceMode($generalSettings)
    {
        if ($generalSettings->maintenance_mode_status != 1) {
            return;
        }

        if (isAdmin()) {
            return;
        }

        $controller = service('router')->controllerName();
        $method = service('router')->methodName();

        $excludedControllers = ['AuthController'];
        $excludedMethods = ['adminLogin', 'adminLoginPost'];

        foreach ($excludedControllers as $excluded) {
            if (strpos($controller, $excluded) !== false && in_array($method, $excludedMethods)) {
                return;
            }
        }

        echo view('maintenance', [
            'generalSettings' => $generalSettings,
            'baseSettings' => config('App')->settings ?? null
        ]);
        exit;
    }


    //get parent categories
    private function getParentCategories($langId)
    {
        $cacheKey = 'parent_categories_' . $langId;
        return getCacheData($cacheKey, function () {
            return $this->categoryModel->getParentCategories();
        }, 'category');
    }
}