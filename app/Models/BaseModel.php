<?php namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    public $request;
    public $session;
    public $activeLanguages;
    public $activeLang;
    public $activeStorage;
    public $defaultLang;
    public $generalSettings;
    public $productSettings;
    public $storageSettings;
    public $settings;
    public $paymentSettings;
    public $defaultCurrency;
    public $selectedCurrency;

    public function __construct()
    {
        parent::__construct();
        $this->request = \Config\Services::request();
        $this->session = \Config\Services::session();
        $this->activeLanguages = getContextValue('languages');
        $this->defaultLang = getContextValue('defaultLang');
        $this->activeLang = getContextValue('activeLang');
        $this->activeStorage = getContextValue('activeStorage');
        $this->generalSettings = getContextValue('generalSettings');
        $this->productSettings = getContextValue('productSettings');
        $this->storageSettings = getContextValue('storageSettings');
        $this->settings = getContextValue('settings');
        $this->paymentSettings = getContextValue('paymentSettings');
        $this->defaultCurrency = getContextValue('defaultCurrency');
        $this->selectedCurrency = getSelectedCurrency();
    }
}