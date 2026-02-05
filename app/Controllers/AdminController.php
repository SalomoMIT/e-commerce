<?php

namespace App\Controllers;

use App\Models\BlogModel;
use App\Models\ChatModel;
use App\Models\CurrencyModel;
use App\Models\EarningsModel;
use App\Models\EmailModel;
use App\Models\LanguageModel;
use App\Models\LocationModel;
use App\Models\MembershipModel;
use App\Models\NewsletterModel;
use App\Models\OrderAdminModel;
use App\Models\PageModel;
use App\Models\ProductAdminModel;
use App\Models\ProductModel;
use App\Models\PromoteModel;
use App\Models\SitemapModel;

class AdminController extends BaseAdminController
{
    protected $orderAdminModel;
    protected $productModel;
    protected $productAdminModel;
    protected $blogModel;
    protected $pageModel;
    protected $locationModel;
    protected $currencyModel;
    protected $newsletterModel;
    protected $chatModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->orderAdminModel = new OrderAdminModel();
        $this->productModel = new ProductModel();
        $this->productAdminModel = new ProductAdminModel();
        $this->blogModel = new BlogModel();
        $this->pageModel = new PageModel();
        $this->locationModel = new LocationModel();
        $this->currencyModel = new CurrencyModel();
        $this->newsletterModel = new NewsletterModel();
        $this->chatModel = new ChatModel();

        //auto approve orders
        if ($this->generalSettings->auto_approve_orders == 1) {
            $this->orderAdminModel->autoApproveOrders();
        }

        //check category paths
        $this->categoryModel->ensureCategoryPathExists();
    }

    public function index()
    {
        $data['title'] = trans("admin_panel");

        $data['orderCount'] = $this->orderAdminModel->getAllOrdersCount();
        $data['productsCount'] = $this->productAdminModel->getProductsCount();
        $data['pendingProductCount'] = $this->productAdminModel->getPendingProductsCount();
        $data['membersCount'] = $this->authModel->getUsersCount();

        $data['latestOrders'] = $this->orderAdminModel->getOrdersLimited(15);
        $data['latestPendingProducts'] = $this->productAdminModel->getLatestPendingProducts(15);
        $data['latestProducts'] = $this->productModel->getLatestProducts($this->activeLang->id, 15);
        $data['latestReviews'] = $this->commonModel->getLatestReviews(15);
        $data['latestComments'] = $this->commonModel->getLatestComments(15);
        $data['latestMembers'] = $this->authModel->getLatestUsers(6);
        $data['panelSettings'] = getPanelSettings();
        $data['latestTransactions'] = $this->orderAdminModel->getTransactionsLimited(15);
        $data['latestPromotedTransactions'] = $this->orderAdminModel->getPromotedTransactionsLimited(15);

        echo view('admin/includes/_header', $data);
        echo view('admin/index');
        echo view('admin/includes/_footer');
    }

    /*
    * Theme
    */
    public function theme()
    {
        checkPermission('theme');
        $data['title'] = trans("theme");

        echo view('admin/includes/_header', $data);
        echo view('admin/theme', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Theme Post
     */
    public function themePost()
    {
        checkPermission('theme');
        if ($this->settingsModel->editTheme()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('theme'));
    }

    /*
    * Slider
    */
    public function slider()
    {
        checkPermission('slider');
        $data['title'] = trans("slider_items");
        $data['sliderItems'] = $this->commonModel->getSliderItems();
        $data['langSearchColumn'] = 3;
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/slider/slider', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Slider Item Post
     */
    public function addSliderItemPost()
    {
        checkPermission('slider');
        if ($this->commonModel->addSliderItem()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Update Slider Item
     */
    public function editSliderItem($id)
    {
        checkPermission('slider');
        $data['title'] = trans("update_slider_item");
        $data['item'] = $this->commonModel->getSliderItem($id);
        if (empty($data['item'])) {
            return redirect()->back();
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/slider/edit_slider', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit Slider Item Post
     */
    public function editSliderItemPost()
    {
        checkPermission('slider');
        $id = inputPost('id');
        if ($this->commonModel->editSliderItem($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Slider Settings Post
     */
    public function editSliderSettingsPost()
    {
        checkPermission('slider');
        if ($this->commonModel->editSliderSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Slider Item Post
     */
    public function deleteSliderItemPost()
    {
        checkPermission('slider');
        $id = inputPost('id');
        if ($this->commonModel->deleteSliderItem($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /*
    * Homepage Manager
    */
    public function homepageManager()
    {
        checkPermission('homepage_manager');
        $data['title'] = trans("homepage_manager");
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['featuredCategories'] = $this->categoryModel->getFeaturedCategories($this->activeLang->id);
        $data['indexCategories'] = $this->categoryModel->getIndexCategories($this->activeLang->id);
        $data['indexBanners'] = $this->commonModel->getIndexBanners();
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/homepage_manager/homepage_manager', $data);
        echo view('admin/includes/_footer');
    }

    /*
    * Homepage Manager Post
    */
    public function homepageManagerPost()
    {
        checkPermission('homepage_manager');
        $submit = inputPost('submit');
        if ($this->request->isAJAX()) {
            $categoryId = inputPost('category_id');
        } else {
            $categoryId = getDropdownCategoryId();
        }
        if ($submit == 'featured_categories') {
            $this->categoryModel->setUnsetFeaturedCategory($categoryId);
        }
        if ($submit == 'products_by_category') {
            $this->categoryModel->setUnsetIndexCategory($categoryId);
        }
        if (!$this->request->isAJAX()) {
            redirectToBackUrl();
        }

        return jsonResponse();
    }

    /*
    * Homepage Manager Settings Post
    */
    public function homepageManagerSettingsPost()
    {
        checkPermission('homepage_manager');
        $this->settingsModel->editHomepageManagerSettings();
        setSuccessMessage(trans("msg_updated"));
        redirectToBackUrl();
    }

    /*
    * Add Index Banner Post
    */
    public function addIndexBannerPost()
    {
        checkPermission('homepage_manager');
        if ($this->commonModel->addIndexBanner()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Edit Index Banner
    */
    public function editIndexBanner($id)
    {
        checkPermission('homepage_manager');
        $data['title'] = trans("edit_banner");
        $data['banner'] = $this->commonModel->getIndexBanner($id);
        if (empty($data['banner'])) {
            return redirect()->back();
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/homepage_manager/edit_banner', $data);
        echo view('admin/includes/_footer');
    }

    /*
    * Edit Index Banner Post
    */
    public function editIndexBannerPost()
    {
        checkPermission('homepage_manager');
        $id = inputPost('id');
        if ($this->commonModel->editIndexBanner($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Delete Index Banner Post
    */
    public function deleteIndexBannerPost()
    {
        checkPermission('homepage_manager');
        $id = inputPost('id');
        $this->commonModel->deleteIndexBanner($id);
    }

    /*
     * --------------------------------------------------------------------
     * Pages
     * --------------------------------------------------------------------
     */

    /**
     * Pages
     */
    public function pages()
    {
        checkPermission('pages');
        $data['title'] = trans("pages");
        $data['pages'] = $this->pageModel->getPages();
        $data['langSearchColumn'] = 2;
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/page/pages', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Page
     */
    public function addPage()
    {
        checkPermission('pages');
        $data['title'] = trans("add_page");
        $data['aiContentType'] = 'page';

        echo view('admin/includes/_header', $data);
        echo view('admin/page/add', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Page Post
     */
    public function addPagePost()
    {
        checkPermission('pages');
        $val = \Config\Services::validation();
        $val->setRule('title', trans("title"), 'required|max_length[500]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            if ($this->pageModel->addPage()) {
                setSuccessMessage(trans("msg_added"));
                return redirect()->back();
            }
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Edit Page
     */
    public function editPage($id)
    {
        checkPermission('pages');
        $data['title'] = trans("update_page");
        $data['page'] = $this->pageModel->getPageById($id);
        if (empty($data['page'])) {
            return redirect()->to(adminUrl('pages'));
        }
        $data['aiContentType'] = 'page';

        echo view('admin/includes/_header', $data);
        echo view('admin/page/edit', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit Page Post
     */
    public function editPagePost()
    {
        checkPermission('pages');
        $val = \Config\Services::validation();
        $val->setRule('title', trans("title"), 'required|max_length[500]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $id = inputPost('id');
            $redirectUrl = inputPost('redirect_url');
            if ($this->pageModel->editPage($id)) {
                setSuccessMessage(trans("msg_updated"));
                if (!empty($redirectUrl)) {
                    return redirect()->to($redirectUrl);
                }
                return redirect()->to(adminUrl('pages'));
            }
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Delete Page Post
     */
    public function deletePagePost()
    {
        checkPermission('pages');
        $id = inputPost('id');
        $page = $this->pageModel->getPageById($id);
        if (!empty($page) && $page->is_custom == 1) {
            if ($this->pageModel->deletePage($id)) {
                setSuccessMessage(trans("msg_deleted"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Newsletter
     * --------------------------------------------------------------------
     */

    /**
     * Newsletter
     */
    public function newsletter()
    {
        checkPermission('newsletter');
        $data['title'] = trans("newsletter");
        $data['panelSettings'] = getPanelSettings();
        $data['subscribersCount'] = $this->newsletterModel->getSubscribersCount();
        $data['usersCount'] = $this->authModel->getUsersCount();
        $data['newsletterSettings'] = getSettingsUnserialized('newsletter');

        echo view('admin/includes/_header', $data);
        echo view('admin/newsletter/newsletter', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Newsletter Select Emails
     */
    public function newsletterSelectEmailsPost()
    {
        checkPermission('newsletter');
        $data['title'] = trans("newsletter");
        $data['submit'] = inputPost('submit');
        $data['emails'] = null;
        $subscriberType = '';
        if ($data['submit'] == 'users') {
            $subscriberType = 'users';
            $ids = inputPost('user_ids');
            $array = null;
            if (!empty($ids)) {
                $array = explode(',', $ids);
            }
            if (!empty($array)) {
                $data['emails'] = $this->authModel->getUserEmailsByIds($array);
            }
        } elseif ($data['submit'] == 'subscribers') {
            $subscriberType = 'subscribers';
            $model = new NewsletterModel();
            $ids = inputPost('subscriber_ids');
            $array = null;
            if (!empty($ids)) {
                $array = explode(',', $ids);
            }
            if (!empty($array)) {
                $data['emails'] = $model->getSubscriberEmailsByIds($array);
            }
        }
        if (empty($data['emails'])) {
            setErrorMessage('newsletter_email_error');
            return redirect()->to(adminUrl('newsletter'));
        }

        $newsletter = [
            'subscriberType' => $subscriberType,
            'emails' => $data['emails']
        ];

        cache()->save('newsletter_data', $newsletter, 86400);//1 day
        return redirect()->to(adminUrl('newsletter-send-email'));
    }

    /**
     * Send Email
     */
    public function newsletterSendEmail()
    {
        checkPermission('newsletter');
        $data = cache('newsletter_data');
        $data['title'] = trans("newsletter");

        if (empty($data) || empty($data['subscriberType']) || empty($data['emails'])) {
            return redirect()->to(adminUrl('newsletter'));
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/newsletter/send_email', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Send Email Post
     */
    public function newsletterSendEmailPost()
    {
        checkPermission('newsletter');
        if ($this->newsletterModel->sendEmail()) {
            return jsonResponse(['result' => 1]);
        }
        return jsonResponse(['result' => 0]);
    }

    /**
     * Delete Subscriber Post
     */
    public function deleteSubscriberPost()
    {
        checkPermission('newsletter');
        $id = inputPost('id');
        if ($this->newsletterModel->deleteFromSubscribers($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Newsletter Settings Post
     */
    public function newsletterSettingsPost()
    {
        checkPermission('newsletter');
        if ($this->newsletterModel->updateSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Contact Message Post
     */
    public function deleteContactMessagePost()
    {
        checkPermission('contact_messages');
        $id = inputPost('id');
        if ($this->commonModel->deleteContactMessage($id)) {
            setSuccessMessage(trans("msg_message_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Affiliate Program
     */
    public function affiliateProgram()
    {
        checkPermission('general_settings');
        $data['title'] = trans("affiliate_program");
        $data['settingsLang'] = inputGet('lang');
        if (empty($data['settingsLang'])) {
            $data['settingsLang'] = selectedLangId();
            return redirect()->to(adminUrl('affiliate-program?lang=' . $data['settingsLang']));
        }
        $data['settings'] = $this->settingsModel->getSettings($data['settingsLang']);
        $data['affDesc'] = !empty($data['settings']->affiliate_description) ? unserializeData($data['settings']->affiliate_description) : '';
        $data['affContent'] = !empty($data['settings']->affiliate_content) ? unserializeData($data['settings']->affiliate_content) : '';
        $data['affWorks'] = !empty($data['settings']->affiliate_works) ? unserializeData($data['settings']->affiliate_works) : '';
        $data['affFaq'] = !empty($data['settings']->affiliate_faq) ? unserializeData($data['settings']->affiliate_faq) : '';

        echo view('admin/includes/_header', $data);
        echo view('admin/affiliate_program', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Affiliate Program Post
     */
    public function affiliateProgramPost()
    {
        checkPermission('general_settings');
        if ($this->settingsModel->updateAffiliateSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Seo Tools
    */
    public function seoTools()
    {
        checkPermission('seo_tools');
        $data['title'] = trans("seo_tools");
        echo view('admin/includes/_header', $data);
        echo view('admin/seo_tools', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Seo Tools Post
     */
    public function seoToolsPost()
    {
        checkPermission('seo_tools');
        if ($this->settingsModel->updateSeoTools()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Generate Sitemap Post
     */
    public function generateSitemapPost()
    {
        checkPermission('seo_tools');

        $model = new SitemapModel();
        $model->updateSitemapSettings();
        $model->generate();

        setSuccessMessage(trans("msg_updated"));
        redirectToBackUrl();
    }

    /**
     * Download Sitemap Post
     */
    public function downloadSitemapPost()
    {
        checkPermission('seo_tools');
        $fileName = inputPost('file_name');
        $security = \Config\Services::security();
        $fileName = $security->sanitizeFilename($fileName);
        if (file_exists(FCPATH . $fileName)) {
            return $this->response->download(FCPATH . $fileName, null)->setFileName($fileName);
        }
        return redirect()->back();
    }

    /**
     * Delete Sitemap Post
     */
    public function deleteSitemapPost()
    {
        checkPermission('seo_tools');
        $fileName = inputPost('file_name');
        if (!empty($fileName)) {
            $fileName = basename($fileName);
            if (file_exists(FCPATH . $fileName)) {
                @unlink(FCPATH . $fileName);
            }
        }
        return redirect()->back();
    }

    /**
     * Ad Spaces
     */
    public function adSpaces()
    {
        checkPermission('ad_spaces');
        $data['title'] = trans("ad_spaces");
        $data['adSpaceKey'] = inputGet('ad_space');
        if (empty($data['adSpaceKey'])) {
            $data['adSpaceKey'] = 'index_1';
        }
        $data['arrayAdSpaces'] = [
            'index_1' => trans("index_ad_space_1"),
            'index_2' => trans("index_ad_space_2"),
            'products_1' => trans("products_ad_space") . ' 1',
            'products_2' => trans("products_ad_space") . ' 2',
            'product_1' => trans("product_ad_space") . ' 1',
            'product_2' => trans("product_ad_space") . ' 2',
            'blog_1' => trans("blog_ad_space_1"),
            'blog_2' => trans("blog_ad_space_2")
        ];
        $data['panelSettings'] = getPanelSettings();
        $data['adSpace'] = $this->commonModel->getAdSpace($data['adSpaceKey'], $data['arrayAdSpaces']);
        if (empty($data['adSpace'])) {
            return redirect()->to(adminUrl('ad-spaces?ad_space=index_1'));
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/ad_spaces', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Ad Spaces Post
     */
    public function adSpacesPost()
    {
        checkPermission('ad_spaces');
        $id = inputPost('id');
        if ($this->commonModel->updateAdSpaces($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Google Adsense Code Post
     */
    public function googleAdsenseCodePost()
    {
        checkPermission('ad_spaces');
        if ($this->commonModel->updateGoogleAdsenseCode()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Chat Messages
     */
    public function chatMessages()
    {
        checkPermission('chat_messages');
        $data['title'] = trans("chat_messages");
        $data['numRows'] = $this->chatModel->getChatsAllCount();
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['chats'] = $this->chatModel->getChatsAllPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/chat/chat_messages', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Delete Chat Post
     */
    public function deleteChatPost()
    {
        checkPermission('chat_messages');
        $id = inputPost('id');
        if ($this->chatModel->deleteChatPermanently($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Delete Chat Message Post
     */
    public function deleteChatMessagePost()
    {
        checkPermission('chat_messages');
        $id = inputPost('id');
        $this->chatModel->deleteChatMessagePermanently($id);
        return jsonResponse();
    }

    /**
     * Contact Messages
     */
    public function contactMessages()
    {
        checkPermission('contact_messages');
        $data['title'] = trans("contact_messages");
        $data['messages'] = $this->commonModel->getContactMessages();

        echo view('admin/includes/_header', $data);
        echo view('admin/contact_messages', $data);
        echo view('admin/includes/_footer');
    }

    /*
      * --------------------------------------------------------------------
      * Abuse Reports
      * --------------------------------------------------------------------
      */

    /**
     * Abuse Reports
     */
    public function abuseReports()
    {
        checkPermission('abuse_reports');
        $data['title'] = trans("abuse_reports");
        $data['panelSettings'] = getPanelSettings();
        $data['numRows'] = $this->commonModel->getAbuseReportsCount();
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['abuseReports'] = $this->commonModel->getAbuseReportsPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/abuse_reports', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Delete Abuse Report
     */
    public function deleteAbuseReportPost()
    {
        checkPermission('abuse_reports');
        $id = inputPost('id');
        if ($this->commonModel->deleteAbuseReport($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /*
     * --------------------------------------------------------------------
     * Settings
     * --------------------------------------------------------------------
     */

    /**
     * Preferences
     */
    public function preferences()
    {
        checkPermission('preferences');
        $data['title'] = trans("preferences");
        $data['panelSettings'] = getPanelSettings();
        $data['storageSettings'] = getSettingsUnserialized('storage');
        $data['activeStorage'] = getActiveStorage();
        $this->orderAdminModel->autoApproveOrders();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/preferences', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Preferences Post
     */
    public function preferencesPost()
    {
        checkPermission('preferences');
        $form = inputPost('submit');
        $tab = inputPost('active_tab');
        if ($this->settingsModel->updatePreferences($form)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('preferences') . '?tab=' . clrNum($tab));
    }

    /**
     * Storage Settings Post
     */
    public function storageSettingsPost()
    {
        checkPermission('storage');
        if ($this->settingsModel->updateStorageSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * AI Writer Post
     */
    public function aiWriterPost()
    {
        checkPermission('preferences');
        if ($this->settingsModel->updateAIWriterSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('preferences'));
    }

    /*
     * General Settings Settings
     */
    public function generalSettings()
    {
        checkPermission('general_settings');
        $data['title'] = trans("general_settings");
        $data['panelSettings'] = getPanelSettings();
        $data['settingsLang'] = inputGet('lang');
        if (empty($data['settingsLang'])) {
            $data['settingsLang'] = selectedLangId();
            return redirect()->to(adminUrl('general-settings?lang=' . $data['settingsLang']));
        }
        $data['settings'] = $this->settingsModel->getSettings($data['settingsLang']);

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/general_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Settings Post
     */
    public function generalSettingsPost()
    {
        checkPermission('general_settings');
        $activeTab = inputPost('active_tab');
        $langId = inputPost('lang_id');
        if ($this->settingsModel->updateSettings()) {
            $this->settingsModel->updateGeneralSettings();
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl() . 'general-settings?lang=' . clrNum($langId) . '&tab=' . clrNum($activeTab));
    }

    /**
     * Cloudflare Turnstile Settings Post
     */
    public function cloudflareTurnstileSettingsPost()
    {
        checkPermission('general_settings');
        $langId = inputPost('lang_id');
        if ($this->settingsModel->updateCloudflareTurnstileSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('general-settings?lang=' . clrNum($langId)));
    }

    /**
     * Maintenance Mode Post
     */
    public function maintenanceModePost()
    {
        checkPermission('general_settings');
        $langId = inputPost('lang_id');
        if ($this->settingsModel->updateMaintenanceModeSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('general-settings?lang=' . clrNum($langId)));
    }

    /**
     * Email Settings
     */
    public function emailSettings()
    {
        checkPermission('settings');
        $data['title'] = trans("email_settings");
        $data['service'] = inputGet('service');
        $data['protocol'] = inputGet('protocol');
        $data['panelSettings'] = getPanelSettings();
        $data['emailServices'] = getAppDefault('emailServices');
        $data['emailSettings'] = getSettingsUnserialized('email');

        if (empty($data['service'])) {
            $data['service'] = $data['emailSettings']->mail_service;
        }
        if (!array_key_exists($data['service'], $data['emailServices'])) {
            $data['service'] = 'php-mailer';
        }
        if (empty($data['protocol'])) {
            $data['protocol'] = $data['emailSettings']->mail_protocol;
        }
        if ($data['protocol'] != 'smtp' && $data['protocol'] != 'mail') {
            $data['protocol'] = 'smtp';
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/email_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update Email Settings Post
     */
    public function emailSettingsPost()
    {
        checkPermission('settings');
        if ($this->settingsModel->updateEmailSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Email Options Post
     */
    public function emailOptionsPost()
    {
        checkPermission('general_settings');
        if ($this->settingsModel->updateEmailOptions()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Send Test Email Post
     */
    public function sendTestEmailPost()
    {
        checkPermission('general_settings');
        $email = inputPost('email');
        $subject = $this->generalSettings->application_name . " Test Email";
        $message = "<p>This is a test email.</p>";
        $model = new EmailModel();
        if (!empty($email)) {
            if (!$model->sendTestEmail($email, $subject, $message)) {
                setErrorMessage(trans("msg_error"));
                redirectToBackUrl();
            }
            setSuccessMessage(trans("msg_email_sent"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Social Login Settings
    */
    public function socialLoginSettings()
    {
        checkPermission('general_settings');
        $data['title'] = trans("social_login");

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/social_login', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Social Login Settings Post
     */
    public function socialLoginSettingsPost()
    {
        checkPermission('general_settings');
        $submit = inputPost('submit');
        if ($this->settingsModel->updateSocialLoginSettings($submit)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Visual Settings
    */
    public function visualSettings()
    {
        checkPermission('visual_settings');
        $data['title'] = trans("visual_settings");
        $data['watermarkSettings'] = getSettingsUnserialized('watermark');

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/visual_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Visual Settings Post
     */
    public function visualSettingsPost()
    {
        checkPermission('visual_settings');
        if ($this->settingsModel->updateVisualSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Update Watermak Settings
     */
    public function updateWatermarkSettingsPost()
    {
        checkPermission('visual_settings');
        if ($this->settingsModel->updateWatermarkSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Font Settings
     */
    public function fontSettings()
    {
        checkPermission('visual_settings');
        $data['langId'] = inputGet('lang');
        if (empty($data['langId'])) {
            $data['langId'] = selectedLangId();
            return redirect()->to(adminUrl('font-settings?lang=' . $data['langId']));
        }
        $data['panelSettings'] = getPanelSettings();
        $data['title'] = trans("font_settings");
        $data['fonts'] = $this->settingsModel->getFonts();
        $data['settings'] = $this->settingsModel->getSettings($data['langId']);

        echo view('admin/includes/_header', $data);
        echo view('admin/font/fonts', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Font Post
     */
    public function addFontPost()
    {
        checkPermission('visual_settings');
        if ($this->settingsModel->addFont()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Set Site Font Post
     */
    public function setSiteFontPost()
    {
        checkPermission('visual_settings');
        if ($this->settingsModel->setSiteFont()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Update Font
     */
    public function editFont($id)
    {
        checkPermission('visual_settings');
        $data['title'] = trans("update_font");
        $data['font'] = $this->settingsModel->getFont($id);
        if (empty($data['font'])) {
            return redirect()->to(adminUrl('font-settings'));
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/font/edit', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update Font Post
     */
    public function editFontPost()
    {
        checkPermission('visual_settings');
        $id = inputPost('id');
        if ($this->settingsModel->editFont($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('font-settings'));
    }

    /**
     * Delete Font Post
     */
    public function deleteFontPost()
    {
        checkPermission('visual_settings');
        $id = inputPost('id');
        if ($this->settingsModel->deleteFont($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /*
    * Product Settings
    */
    public function productSettings()
    {
        checkPermission('product_settings');
        $data['title'] = trans("product_settings");

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/product_settings', $data);
        echo view('admin/includes/_footer');
    }

    /*
    * Product Settings Post
    */
    public function productSettingsPost()
    {
        checkPermission('product_settings');
        $this->settingsModel->updateProductSettings();
        setSuccessMessage(trans("msg_updated"));
        redirectToBackUrl();
    }

    /*
    * Payment Settings
    */
    public function paymentSettings()
    {
        checkPermission('payment_settings');
        $data['title'] = trans("payment_settings");
        $data['paymentGateways'] = $this->settingsModel->getPaymentGateways();
        $data['currencies'] = $this->currencyModel->getCurrencies();
        $data['countries'] = $this->locationModel->getCountries();
        $data['taxes'] = $this->settingsModel->getTaxes();
        $data['activeTab'] = inputGet('gateway') ?? 'paypal';

        //add mercado pago if not exists
        $this->settingsModel->addMercadoPago();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/payment_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Payment Settings Post
     */
    public function paymentGatewaySettingsPost()
    {
        checkPermission('payment_settings');
        $nameKey = inputPost('name_key');
        if ($this->settingsModel->updatePaymentGateway($nameKey)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        if (!empty($nameKey)) {
            return redirect()->to(adminUrl('payment-settings') . '?gateway=' . esc($nameKey));
        }
        return redirect()->to(adminUrl('payment-settings'));
    }

    /**
     * Commission Settings Post
     */
    public function commissionSettingsPost()
    {
        checkPermission('payment_settings');
        if ($this->settingsModel->updateCommissionSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('payment-settings'));
    }

    /**
     * Add Tax
     */
    public function addTax()
    {
        checkPermission('payment_settings');
        $data['title'] = trans("add_tax");
        $data['countries'] = $this->locationModel->getCountries();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/add_tax', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Tax
     */
    public function addTaxPost()
    {
        checkPermission('payment_settings');
        if ($this->settingsModel->addTax()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Tax
     */
    public function editTax($id)
    {
        checkPermission('payment_settings');
        $data['title'] = trans("edit_tax");
        $data['tax'] = $this->settingsModel->getTax($id);
        if (empty($data['tax'])) {
            redirectToUrl(adminUrl('payment-settings'));
        }
        $data['countries'] = $this->locationModel->getCountries();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/edit_tax', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit Tax Post
     */
    public function editTaxPost()
    {
        checkPermission('payment_settings');
        if ($this->settingsModel->editTax()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Tax
     */
    public function deleteTaxPost()
    {
        checkPermission('payment_settings');
        $id = inputPost('id');
        if ($this->settingsModel->deleteTax($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        exit();
    }

    /**
     * Additional Invoice Information Post
     */
    public function additionalInvoiceInfoPost()
    {
        checkPermission('payment_settings');
        if ($this->settingsModel->updateAdditionalInvoiceInfo()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('payment-settings'));
    }

    /*
    * Currency Settings
    */
    public function currencySettings()
    {
        checkPermission('payment_settings');
        $data['title'] = trans("currency_settings");
        $data['currencies'] = $this->currencyModel->getCurrencies();
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/currency/currency_settings', $data);
        echo view('admin/includes/_footer');
    }

    /*
    * Currency Settings Post
    */
    public function currencySettingsPost()
    {
        checkPermission('payment_settings');
        if ($this->currencyModel->updateCurrencySettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Currency Converter Post
    */
    public function currencyConverterPost()
    {
        checkPermission('payment_settings');
        if ($this->currencyModel->updateCurrencyConverterSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Add Currency
     */
    public function addCurrency()
    {
        checkPermission('payment_settings');
        $data['title'] = trans("add_currency");

        echo view('admin/includes/_header', $data);
        echo view('admin/currency/add_currency', $data);
        echo view('admin/includes/_footer');
    }

    /*
    * Add Currency Post
    */
    public function addCurrencyPost()
    {
        checkPermission('payment_settings');
        if ($this->currencyModel->addCurrency()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Update Currency
     */
    public function editCurrency($id)
    {
        checkPermission('payment_settings');
        $data['title'] = trans("update_currency");
        $data['currency'] = $this->currencyModel->getCurrency($id);
        if (empty($data['currency'])) {
            return redirect()->to(adminUrl('currency-settings'));
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/currency/edit_currency', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update Currency Rate
     */
    public function updateCurrencyRate()
    {
        checkPermission('payment_settings');
        $this->currencyModel->updateCurrencyRate();
        return jsonResponse();
    }

    /**
     * Update Currency Post
     */
    public function editCurrencyPost()
    {
        checkPermission('payment_settings');
        $id = inputPost('id');
        if ($this->currencyModel->editCurrency($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    // Update Currency Rates
    public function updateCurrencyRates()
    {
        checkPermission('payment_settings');
        if ($this->currencyModel->updateCurrencyRates()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Delete Currency Post
    */
    public function deleteCurrencyPost()
    {
        checkPermission('payment_settings');
        $id = inputPost('id');
        if ($this->currencyModel->deleteCurrency($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Membership Payments
     */
    public function membershipPayments()
    {
        checkPermission('payments');
        $membershipModel = new MembershipModel();
        $data['title'] = trans("membership_payments");
        $data['numRows'] = $membershipModel->getMembershipTransactionsCount(null);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['transactions'] = $membershipModel->getMembershipTransactionsPaginated($this->perPage, $data['pager']->offset, null);

        echo view('admin/includes/_header', $data);
        echo view('admin/payments/membership_payments');
        echo view('admin/includes/_footer');
    }

    /**
     * Approve Membership Payment Post
     */
    public function approveMembershipPaymentPost()
    {
        checkPermission('payments');
        $membershipModel = new MembershipModel();

        $id = inputPost('id');
        if($membershipModel->approveTransactionPayment($id)){
            $this->commonModel->approveBankTransferByTransaction($id, 'membership');
        }

        setSuccessMessage(trans("msg_updated"));
        redirectToBackUrl();
    }

    /**
     * Delete Membership Payment Post
     */
    public function deleteMembershipPaymentPost()
    {
        checkPermission('payments');
        $membershipModel = new MembershipModel();
        $id = inputPost('id');
        $membershipModel->deleteTransaction($id);
    }

    /**
     * Featured Products Payments
     */
    public function promotionPayments()
    {
        checkPermission('payments');
        $data['title'] = trans("promotion_payments");
        $model = new PromoteModel();
        $numRows = $model->getTransactionsCount(null);
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['transactions'] = $model->getTransactionsPaginated(null, $this->perPage, $data['pager']->offset);
        $data['userSession'] = getUserSession();

        echo view('admin/includes/_header', $data);
        echo view('admin/payments/promotion_payments', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Approve Promotion Payment Post
     */
    public function approvePromotionPaymentPost()
    {
        checkPermission('payments');
        $transactionId = inputPost('transaction_id');
        $model = new PromoteModel();
        $transaction = $model->getTransaction($transactionId);
        if (!empty($transaction)) {
            if ($this->productAdminModel->addToFeaturedProducts($transaction->product_id, $transaction->day_count, $transactionId)) {

                $this->commonModel->approveBankTransferByTransaction($transaction->id, 'promote');

                setSuccessMessage(trans("msg_updated"));
                resetCacheDataOnChange();
                redirectToBackUrl();
            }
        }
        setErrorMessage(trans("msg_error"));
        redirectToBackUrl();
    }

    /**
     * Delete Featured Payment Post
     */
    public function deletePromotionPaymentsPost()
    {
        checkPermission('payments');
        $id = inputPost('id');
        $model = new PromoteModel();
        if ($model->deleteTransaction($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Wallet Deposits
     */
    public function walletDeposits()
    {
        checkPermission('payments');
        $model = new EarningsModel();
        $data['title'] = trans("wallet_deposits");
        $data['numRows'] = $model->getDepositsCount(null);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['transactions'] = $model->getPaginatedDeposits($this->perPage, $data['pager']->offset, null);

        echo view('admin/includes/_header', $data);
        echo view('admin/payments/wallet_deposits');
        echo view('admin/includes/_footer');
    }

    /**
     * Approve Wallet Deposit Payment Post
     */
    public function approveWalletDepositPaymentPost()
    {
        checkPermission('payments');
        $transactionId = inputPost('id');
        $depositAmount = numToDecimal(inputPost('deposit_amount'));
        $bankTransferId = numToDecimal(inputPost('bank_transfer_id'));

        $earningsModel = new EarningsModel();
        $deposit = $earningsModel->getDepositTransaction($transactionId);
        if (!empty($deposit) && $deposit->payment_status != 1) {
            $earningsModel->addFundsWallet($depositAmount, $deposit->currency, $deposit->user_id);
            $earningsModel->setDepositPaymentReceived($deposit);

            //update if deposit amount different
            if ($deposit->deposit_amount != $depositAmount) {
                $earningsModel->updateDepositAmount($deposit, $depositAmount);
            }

            //set bank transfer status
            if (!empty($bankTransferId)) {
                $transfer = $this->commonModel->getBankTransfer($bankTransferId);
                if (!empty($transfer)) {
                    $this->commonModel->updateBankTransferStatus($transfer, 'approved');
                }
            }

            $this->commonModel->approveBankTransferByTransaction($deposit->id, 'wallet_deposit');
        }

        setSuccessMessage(trans("msg_updated"));
        redirectToBackUrl();
    }

    /**
     * Delete Wallet Deposit Post
     */
    public function deleteWalletDepositPost()
    {
        checkPermission('payments');
        $id = inputPost('id');
        $model = new EarningsModel();
        if ($model->deleteWalletDeposit($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Bank Transfer Reports
     */
    public function bankTransferReports()
    {
        checkPermission('payments');
        $data['title'] = trans("bank_transfer_reports");
        $numRows = $this->commonModel->getBankTransfersCount();
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['bankTransfers'] = $this->commonModel->getBankTransfersPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/payments/bank_transfer_reports', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Bank Transfer Options Post
     */
    public function bankTransferOptionsPost()
    {
        checkPermission('payments');
        $reportId = inputPost('report_id');
        $option = inputPost('option');
        $transfer = $this->commonModel->getBankTransfer($reportId);
        if (!empty($transfer)) {
            //update transfer status
            if ($this->commonModel->updateBankTransferStatus($transfer, $option)) {
                if ($option == 'approved') {
                    if ($transfer->report_type == 'order') {
                        $order = getOrderByOrderNumber($transfer->order_number);
                        if (!empty($order) && $order->payment_status == 'pending_payment') {
                            $this->orderAdminModel->updateOrderPaymentReceived($order->id);
                            $this->orderAdminModel->updateOrderStatusIfCompleted($order->id);
                        }
                    } elseif ($transfer->report_type == 'membership') {
                        $membershipModel = new MembershipModel();
                        $transaction = $membershipModel->getMembershipTransaction($transfer->report_item_id);
                        if (!empty($transaction) && $transaction->payment_status == 'pending_payment') {
                            $membershipModel->approveTransactionPayment($transaction->id);
                        }
                    } elseif ($transfer->report_type == 'promote') {
                        $promoteModel = new PromoteModel();
                        $transaction = $promoteModel->getTransaction($transfer->report_item_id);
                        if (!empty($transaction) && $transaction->payment_status == 'pending_payment') {
                            $serviceData = new \stdClass();
                            $serviceData->productId = $transaction->product_id;
                            $serviceData->purchasedPlan = $transaction->purchased_plan;
                            $serviceData->dayCount = $transaction->day_count;
                            $promoteModel->addToPromotedProducts($serviceData);
                            $promoteModel->setTransactionAsPaymentReceived($transaction->id);
                        }
                    } elseif ($transfer->report_type == 'wallet_deposit') {
                        $earningsModel = new EarningsModel();
                        $deposit = $earningsModel->getDepositTransaction($transfer->report_item_id);
                        if (!empty($deposit) && $deposit->payment_status != 1) {
                            $earningsModel->addFundsWallet($deposit->deposit_amount, $deposit->currency, $deposit->user_id);
                            $earningsModel->setDepositPaymentReceived($deposit);
                        }
                    }
                }
            }
        }
        setSuccessMessage(trans("msg_updated"));
        if ($option != 'approved') {
            redirectToBackUrl();
        }
        return jsonResponse();
    }

    /**
     * Delete Bank Transfer Post
     */
    public function deleteBankTransferPost()
    {
        checkPermission('payments');
        $id = inputPost('id');
        if ($this->commonModel->deleteBankTransfer($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /*
    * System Settings
    */
    public function systemSettings()
    {
        checkPermission('system_settings');
        $data['title'] = trans("system_settings");
        $data['currencies'] = $this->currencyModel->getCurrencies();
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/system_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * System Settings Post
     */
    public function systemSettingsPost()
    {
        checkPermission('system_settings');
        //check product type
        $physicalProductsSystem = inputPost('physical_products_system');
        $digitalProductsSystem = inputPost('digital_products_system');
        if ($physicalProductsSystem == 0 && $digitalProductsSystem == 0) {
            setErrorMessage(trans("msg_error_product_type"));
            redirectToBackUrl();
        }
        $marketplaceSystem = inputPost('marketplace_system');
        $classifiedAdsSystem = inputPost('classified_ads_system');
        $biddingSystem = inputPost('bidding_system');
        if ($marketplaceSystem == 0 && $classifiedAdsSystem == 0 && $biddingSystem == 0) {
            setErrorMessage(trans("msg_error_selected_system"));
            redirectToBackUrl();
        }
        if ($this->settingsModel->updateSystemSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
    * Route Settings
    */
    public function routeSettings()
    {
        checkPermission('system_settings');
        $data['title'] = trans("route_settings");
        $data['routes'] = $this->settingsModel->getRoutes();

        echo view('admin/includes/_header', $data);
        echo view('admin/settings/route_settings', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Route Settings Post
     */
    public function routeSettingsPost()
    {
        checkPermission('system_settings');
        if ($this->settingsModel->updateRouteSettings()) {
            setSuccessMessage(trans("msg_updated"));
            $routeAdmin = $this->settingsModel->getRouteByKey('admin');
            if (!empty($routeAdmin)) {
                redirectToUrl(base_url($routeAdmin->route . '/route-settings'));
            }
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Cache System
     */
    public function cacheSystem()
    {
        checkPermission('cache_system');
        $data['title'] = trans("cache_system");

        echo view('admin/includes/_header', $data);
        echo view('admin/cache_system', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Cache System Post
     */
    public function cacheSystemPost()
    {
        checkPermission('cache_system');
        $action = inputPost('action');
        if ($action == 'reset') {
            resetCacheData();
            setSuccessMessage(trans("msg_reset_cache"));
        }
        if ($action == 'reset_static') {
            resetCacheData('static');
            setSuccessMessage(trans("msg_reset_cache"));
        } else {
            if ($this->settingsModel->updateCacheSystem()) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Location
     * --------------------------------------------------------------------
     */

    /**
     * Countries
     */
    public function countries()
    {
        checkPermission('location');
        $data['title'] = trans("countries");

        $numRows = $this->locationModel->getCountryCount();
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['countries'] = $this->locationModel->getCountriesPaginated($this->perPage, $data['pager']->offset);
        $data['activeCountries'] = $this->locationModel->getActiveCountries();
        $data['panelSettings'] = getPanelSettings();

        echo view('admin/includes/_header', $data);
        echo view('admin/location/countries', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Country
     */
    public function addCountry()
    {
        checkPermission('location');
        $data['title'] = trans("add_country");

        echo view('admin/includes/_header', $data);
        echo view('admin/location/add_country', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Country Post
     */
    public function addCountryPost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            if ($this->locationModel->addCountry()) {
                setSuccessMessage(trans("msg_added"));
                redirectToBackUrl();
            }
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Edit Country
     */
    public function editCountry($id)
    {
        checkPermission('location');
        $data['title'] = trans("update_country");
        $data['country'] = $this->locationModel->getCountry($id);
        if (empty($data['country'])) {
            return redirect()->to(adminUrl('countries'));
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/location/edit_country', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit Country Post
     */
    public function editCountryPost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $id = inputPost('id');
            if ($this->locationModel->editCountry($id)) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /**
     * Delete Country Post
     */
    public function deleteCountryPost()
    {
        checkPermission('location');
        $id = inputPost('id');
        if ($this->locationModel->deleteCountry($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Location Settings Post
     */
    public function locationSettingsPost()
    {
        if ($this->locationModel->updateLocationSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * States
     */
    public function states()
    {
        checkPermission('location');
        $data['title'] = trans("states");
        $data['countries'] = $this->locationModel->getCountries();

        $numRows = $this->locationModel->getStateCount();
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['states'] = $this->locationModel->getStatesPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/location/states', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add State
     */
    public function addState()
    {
        checkPermission('location');
        $data['title'] = trans("add_state");
        $data['countries'] = $this->locationModel->getCountries();

        echo view('admin/includes/_header', $data);
        echo view('admin/location/add_state', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add State Post
     */
    public function addStatePost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            if ($this->locationModel->addState()) {
                setSuccessMessage(trans("msg_added"));
                redirectToBackUrl();
            }
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Edit State
     */
    public function editState($id)
    {
        checkPermission('location');
        $data['title'] = trans("update_state");
        $data['state'] = $this->locationModel->getState($id);
        if (empty($data['state'])) {
            return redirect()->to(adminUrl('states'));
        }
        $data['countries'] = $this->locationModel->getCountries();

        echo view('admin/includes/_header', $data);
        echo view('admin/location/edit_state', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit State Post
     */
    public function editStatePost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $id = inputPost('id');
            if ($this->locationModel->editState($id)) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /**
     * Delete State Post
     */
    public function deleteStatePost()
    {
        checkPermission('location');
        $id = inputPost('id');
        if ($this->locationModel->deleteState($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Cities
     */
    public function cities()
    {
        checkPermission('location');
        $data['title'] = trans("cities");
        $data['countries'] = $this->locationModel->getCountries();
        $data['states'] = $this->locationModel->getStates();

        $numRows = $this->locationModel->getCityCount();
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['cities'] = $this->locationModel->getCitiesPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/location/cities', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Cities
     */
    public function addCity()
    {
        checkPermission('location');
        $data['title'] = trans("add_city");
        $data['countries'] = $this->locationModel->getCountries();

        echo view('admin/includes/_header', $data);
        echo view('admin/location/add_city', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add City Post
     */
    public function addCityPost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            if ($this->locationModel->addCity()) {
                setSuccessMessage(trans("msg_added"));
                redirectToBackUrl();
            }
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Edit City
     */
    public function editCity($id)
    {
        checkPermission('location');
        $data['title'] = trans("update_city");
        $data['city'] = $this->locationModel->getCity($id);
        if (empty($data['city'])) {
            return redirect()->to(adminUrl('cities'));
        }
        $data['countries'] = $this->locationModel->getCountries();
        $data['states'] = $this->locationModel->getStatesByCountry($data['city']->country_id);

        echo view('admin/includes/_header', $data);
        echo view('admin/location/edit_city', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update City Post
     */
    public function editCityPost()
    {
        checkPermission('location');
        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[200]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $id = inputPost('id');
            if ($this->locationModel->editCity($id)) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /**
     * Delete City Post
     */
    public function deleteCityPost()
    {
        checkPermission('location');
        $id = inputPost('id');
        if ($this->locationModel->deleteCity($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    //activate inactivate countries
    public function activateInactivateCountries()
    {
        checkPermission('location');
        $action = inputPost('action');
        $this->locationModel->activateInactivateCountries($action);
        return jsonResponse();
    }

    /**
     * Control Panel Language Post
     */
    public function setActiveLanguagePost()
    {
        $langId = inputPost('lang_id');
        $languageModel = new LanguageModel();
        $language = $languageModel->getLanguage($langId);
        if (!empty($language)) {
            $this->session->set('mds_control_panel_lang', $language->id);
        }
        redirectToBackUrl();
    }

    /**
     * Download Database Backup
     */
    public function downloadDatabaseBackup()
    {
        if (isSuperAdmin()) {
            $response = \Config\Services::response();
            $data = $this->settingsModel->downloadBackup();
            $name = 'db_backup-' . date('Y-m-d H-i-s') . '.sql';
            return $response->download($name, $data);
        }
        return redirect()->to(adminUrl());
    }
}
