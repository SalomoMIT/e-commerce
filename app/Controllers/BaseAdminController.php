<?php

namespace App\Controllers;

use App\Models\AdModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AuthModel;
use App\Models\CategoryModel;
use App\Models\CommonModel;
use App\Models\FileModel;
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
abstract class BaseAdminController extends Controller
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
    public $commonModel;
    public $categoryModel;
    public $fileModel;
    public $generalSettings;
    public $paymentSettings;
    public $productSettings;
    public $affiliateSettings;
    public $settings;
    public $activeLanguages;
    public $activeLang;
    public $defaultCurrency;
    public $perPage;
    public $baseVars;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

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
        setContextActiveLang(helperGetSession('mds_control_panel_lang'));

        $this->settingsModel = new SettingsModel();
        $this->authModel = new AuthModel();
        $this->commonModel = new CommonModel();
        $this->categoryModel = new CategoryModel();
        $this->fileModel = new FileModel();
        //check auth
        if (!authCheck()) {
            redirectToUrl(adminUrl('login'));
            exit();
        }
        //check admin
        if (!isAdmin()) {
            redirectToUrl(langBaseUrl());
            exit();
        }
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
        //active lang
        $this->activeLang = getContextValue('activeLang');
        //default currency
        $this->defaultCurrency = getContextValue('defaultCurrency');

        //per page
        $this->perPage = 15;
        if (!empty(clrNum(inputGet('show')))) {
            $this->perPage = clrNum(inputGet('show'));
        }

        //variables
        $this->baseVars = new \stdClass();
        $this->baseVars->rtl = false;
        $this->baseVars->unreadMessageCount = 0;
        $this->baseVars->decimalSeparator = $this->defaultCurrency->currency_format === 'european' ? ',' : '.';

        if ($this->activeLang->text_direction == 'rtl') {
            $this->baseVars->rtl = true;
        }
        if (authCheck()) {
            $this->baseVars->unreadMessageCount = getUnreadChatsCount(user()->id);
        }
        //maintenance mode
        if ($this->generalSettings->maintenance_mode_status == 1) {
            if (!isAdmin()) {
                $authModel = new AuthModel();
                $authModel->logout();
                redirectToUrl(adminUrl('login'));
                exit();
            }
        }

        //view variables
        $view = \Config\Services::renderer();
        $view->setData([
            'generalSettings' => $this->generalSettings,
            'paymentSettings' => $this->paymentSettings,
            'productSettings' => $this->productSettings,
            'affiliateSettings' => $this->affiliateSettings,
            'baseSettings' => $this->settings,
            'activeLanguages' => $this->activeLanguages,
            'activeLang' => $this->activeLang,
            'defaultCurrency' => $this->defaultCurrency,
            'baseVars' => $this->baseVars
        ]);
    }
}
