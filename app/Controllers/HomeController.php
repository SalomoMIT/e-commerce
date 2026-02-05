<?php

namespace App\Controllers;

use App\Models\BlogModel;
use App\Models\CategoryModel;
use App\Models\ChatModel;
use App\Models\CheckoutModel;
use App\Models\CommonModel;
use App\Models\CurrencyModel;
use App\Models\EarningsModel;
use App\Models\FieldModel;
use App\Models\FileModel;
use App\Models\LocationModel;
use App\Models\MembershipModel;
use App\Models\NewsletterModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\ProfileModel;
use App\Models\PromoteModel;
use App\Models\ShippingModel;
use App\Models\SitemapModel;
use App\Models\UploadModel;
use App\Models\ProductOptionsModel;
use App\Libraries\JsonLdGenerator;
use App\Libraries\Turnstile;

class HomeController extends BaseController
{
    protected $blogModel;
    protected $blogPerPage;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->blogModel = new BlogModel();
        $this->blogPerPage = 12;
    }

    /**
     * Index
     */
    public function index()
    {
        $data = [
            'title' => $this->settings->homepage_title,
            'description' => $this->settings->site_description,
            'keywords' => $this->settings->keywords,
            'app_name' => $this->generalSettings->application_name,
            'isTranslatable' => true,
        ];
        $data['sliderItems'] = $this->commonModel->getSliderItemsByLang(selectedLangId());
        $data['featuredCategories'] = $this->categoryModel->getFeaturedCategories($this->activeLang->id);
        $data['indexBannersArray'] = $this->commonModel->getIndexBannersArray();
        $data['specialOffers'] = $this->productModel->getSpecialOffers($this->activeLang->id);
        $data['indexCategories'] = $this->categoryModel->getIndexCategories($this->activeLang->id);
        $data['promotedProducts'] = $this->productModel->getPromotedProductsLimited($this->activeLang->id, $this->generalSettings->index_promoted_products_count, 0);
        $data['userSession'] = getUserSession();
        $data['latestProducts'] = $this->productModel->getLatestProducts($this->activeLang->id, $this->generalSettings->index_latest_products_count);
        $data["brands"] = $this->commonModel->getBrandSliderItems($this->activeLang->id);
        $data["blogSliderPosts"] = $this->blogModel->getPosts($this->activeLang->id, 10);
        $data["socialMediaLinks"] = getSocialLinksArray($this->settings, false);

        $jsonLdGenerator = new JsonLdGenerator();
        $typesToGenerate = ['website', 'organization'];
        $data['jsonLdScript'] = $jsonLdGenerator->generate($typesToGenerate, $data);

        echo view('partials/_header', $data);
        echo view('index', $data);
        echo view('partials/_footer', $data);
    }

    /**
     * Dynamic Page by Name Slug
     */
    public function any($slug)
    {
        if (empty($slug)) {
            return redirect()->to(langBaseUrl());
        }
        $page = $this->pageModel->getPage($slug);
        $data['userSession'] = getUserSession();
        if (!empty($page)) {
            $this->page($page);
        } else {
            $category = $this->categoryModel->getCategoryBySlug($slug, 'parent');
            if (!empty($category)) {
                $this->category($category);
            } else {
                $this->product($slug);
            }
        }
    }

    /**
     * Page
     */
    private function page($page)
    {
        if (empty($page)) {
            return redirect()->to(langBaseUrl());
        }
        if ($page->visibility == 0 || !empty($page->page_default_name)) {
            $this->error404();
        } else {

            $data = [
                'title' => $page->title,
                'description' => $page->description,
                'keywords' => $page->keywords,
                'page' => $page,
                'isTranslatable' => false
            ];

            echo view('partials/_header', $data);
            echo view('page', $data);
            echo view('partials/_footer');
        }
    }

    /**
     * Products
     */
    public function products()
    {
        $data = $this->categoryModel->getCachedCategoryPageData($this->activeLang->id, null);

        $data = setPageMeta(trans("products"), $data);
        $data['categories'] = $this->parentCategories;
        $data['parentCategoriesTree'] = null;
        $data['userSession'] = getUserSession();
        $data['isTranslatable'] = true;

        //get selected brand ids
        $brandIdsStr = inputGet('brand');
        $brandIds = explode(',', $brandIdsStr ?? '');
        $brandIds = array_map('intval', $brandIds);
        $brandIds = array_filter($brandIds);
        $data['brandNameArray'] = $this->commonModel->getBrandNameArray($brandIds, $this->activeLang->id);

        $data['queryParams'] = $this->request->getGet();

        $objParams = (object)[
            'pageNumber' => getValidPageNumber(inputGet('page')),
            'category' => null,
            'userId' => null,
            'customFilters' => null,
            'arrayParams' => null,
            'couponId' => null,
            'langId' => $this->activeLang->id
        ];

        $data['products'] = $this->productModel->loadProducts($objParams);

        echo view('partials/_header', $data);
        echo view('product/products', $data);
        echo view('partials/_footer');
    }

    /**
     * Category
     */
    private function category($category)
    {
        if (empty($category)) {
            return redirect()->to(langBaseUrl());
        }

        $data = $this->categoryModel->getCachedCategoryPageData($this->activeLang->id, $category);

        $pageTitle = '';
        if (!empty($data['parentCategoriesTree'])) {
            foreach ($data['parentCategoriesTree'] as $item) {
                if (!empty($pageTitle)) {
                    $pageTitle .= ' | ';
                }
                $pageTitle .= $item->cat_name;
            }
        }

        $data['title'] = !empty($data['categoryDetails']) && !empty($data['categoryDetails']->meta_title) ? $data['categoryDetails']->meta_title : $pageTitle;
        $data['description'] = !empty($data['categoryDetails']) ? $data['categoryDetails']->meta_description : '';
        $data['keywords'] = !empty($data['categoryDetails']) ? $data['categoryDetails']->meta_keywords : '';
        //og tags
        $data['showOgTags'] = true;
        $data['ogTitle'] = $data['title'];
        $data['ogDescription'] = $data['description'];
        $data['ogType'] = 'website';
        $data['ogUrl'] = generateCategoryUrl($category);
        $data['ogImage'] = getStorageFileUrl($category->image, $category->storage);
        $data['ogWidth'] = '420';
        $data['ogHeight'] = '420';
        $data['ogCreator'] = $this->generalSettings->application_name;
        $data['isTranslatable'] = true;
        $data['category'] = $category;

        $fieldModel = new FieldModel();
        $data['customFiltersDisplayNames'] = $fieldModel->getCustomFiltersDisplayNames($this->activeLang->id);

        //get selected brand ids
        $brandIdsStr = inputGet('brand');
        $brandIds = explode(',', $brandIdsStr ?? '');
        $brandIds = array_map('intval', $brandIds);
        $brandIds = array_filter($brandIds);
        $data['brandNameArray'] = $this->commonModel->getBrandNameArray($brandIds, $this->activeLang->id);

        $data['queryParams'] = $this->request->getGet();

        $objParams = (object)[
            'pageNumber' => getValidPageNumber(inputGet('page')),
            'category' => $category,
            'userId' => null,
            'customFilters' => $data['customFilters'],
            'arrayParams' => null,
            'couponId' => null,
            'langId' => selectedLangId()
        ];

        $data['products'] = $this->productModel->loadProducts($objParams);

        $jsonLdGenerator = new JsonLdGenerator();
        $typesToGenerate = ['breadcrumb'];
        $data['jsonLdScript'] = $jsonLdGenerator->generate($typesToGenerate, $data);

        echo view('partials/_header', $data);
        echo view('product/products', $data);
        echo view('partials/_footer');
    }

    /**
     * SubCategory
     */
    public function subCategory($parentSlug, $slug)
    {
        $subCategory = $this->categoryModel->getCategoryBySlug($slug, 'sub');
        if (!empty($subCategory)) {
            $this->category($subCategory);
        } else {
            $this->error404();
        }
    }

    /**
     * Product
     */
    public function product($slug)
    {
        $product = $this->productModel->getProductBySlug($slug);
        if (empty($product)) {
            $this->error404();
        } else {
            if ($product->status == 0 || $product->visibility == 0) {
                if (!authCheck()) {
                    $this->error404();
                }
                if ($product->user_id != user()->id && !hasPermission('products')) {
                    $this->error404();
                }
            }
            $data['productDetails'] = $this->productModel->getProductDetails($product->id, selectedLangId(), true);
            if (empty($data['productDetails'])) {
                $data['productDetails'] = array();
            }
            $data['parentCategoriesTree'] = $this->categoryModel->getCategoryParentTree($product->category_id);

            //related products
            $data['relatedProducts'] = $this->productModel->getRelatedProducts($product->id, $product->category_id);
            $data['user'] = $this->authModel->getUser($product->user_id);
            //user products
            $data['userProducts'] = $this->productModel->getMoreProductsByUser($data['user']->id, $product->id);
            $data['reviews'] = $this->commonModel->getProductReviewsByOffset($product->id, REVIEWS_LOAD_LIMIT, 0);
            $data['reviewsCount'] = $this->commonModel->getReviewsCountByProductId($product->id);
            $data['commentsArray'] = $this->commonModel->getProductCommentsByOffset($product->id, COMMENTS_LOAD_LIMIT, 0);
            $data['commentsCount'] = $this->commonModel->getProductCommentCount($product->id);
            $data['wishlistCount'] = $this->productModel->getProductWishlistCount($product->id);
            $data['isProductInWishlist'] = $this->productModel->isProductInWishlist($product->id);
            $data['isTranslatable'] = true;
            $data['product'] = $product;

            //brand
            $brand = $this->commonModel->getProductBrand($product->brand_id, $this->activeLang->id);
            $data['productBrandName'] = !empty($brand) ? $brand->brand_name : '';
            //custom fields
            $fieldModel = new FieldModel();
            $data['productCustomFieldsValues'] = $fieldModel->getProductCustomFieldsValues($product->id, $data['productBrandName'], $this->activeLang->id);

            $fileModel = new FileModel();
            $data['video'] = $fileModel->getProductVideo($product->id);
            $data['audio'] = $fileModel->getProductAudio($product->id);
            $data["digitalSale"] = null;
            if ($product->product_type == 'digital' && authCheck()) {
                $data["digitalSale"] = getDigitalSaleByBuyerId(user()->id, $product->id);
            }
            //shipping
            $data['shippingStatus'] = $this->productSettings->marketplace_shipping;
            $data['productLocationStatus'] = $this->productSettings->marketplace_product_location;
            if ($product->listing_type == 'ordinary_listing' || $product->product_type != 'physical') {
                $data['shippingStatus'] = 0;
            }
            if ($product->product_type == 'digital' || ($product->listing_type == 'ordinary_listing' && !$this->productSettings->classified_product_location)) {
                $data['productLocationStatus'] = 0;
            }
            $shippingModel = new ShippingModel();
            $data['deliveryTime'] = $shippingModel->getShippingDeliveryTime($product->shipping_delivery_time_id);
            $data['estimatedDelivery'] = $shippingModel->getProductEstimatedDelivery($product, selectedLangId());

            $data['title'] = !empty($data['productDetails']) ? $data['productDetails']->title : '';
            $data['description'] = !empty($data['productDetails']->short_description) ? $data['productDetails']->short_description : $data['title'];
            $data['keywords'] = getProductTagsString($product, $this->activeLang->id);
            //og tags
            $data['showOgTags'] = true;
            $data['ogTitle'] = $data['title'];
            $data['ogDescription'] = $data['description'];
            $data['ogType'] = 'product';
            $data['ogUrl'] = generateProductUrl($product);
            $data['ogImage'] = getProductMainImage($product->id, 'image_default');
            $data['ogWidth'] = '750';
            $data['ogHeight'] = '500';
            if (!empty($data['user'])) {
                $data['ogCreator'] = getUsername($data['user']);
                $data['ogAuthor'] = getUsername($data['user']);
            } else {
                $data['ogCreator'] = '';
                $data['ogAuthor'] = '';
            }
            $data['ogPublishedTime'] = $product->created_at;
            $data['ogModifiedTime'] = $product->created_at;
            $data['productImages'] = getProductImages($product->id);
            $data['productSku'] = $product->sku;
            $convertCurrency = $product->listing_type == 'ordinary_listing' ? false : true;
            $data['productPrice'] = !empty($product->price) && $product->price > 0 ? priceFormatted($product->price, $product->currency, $convertCurrency) : '';
            $data['productPriceDiscounted'] = priceFormatted($product->price_discounted, $product->currency, $convertCurrency);
            $data['productDiscountRate'] = calculateDiscount($product->price, $product->price_discounted);
            $data['productStock'] = $product->stock;

            //slider images
            $productSliderImages = [];
            foreach ($data['productImages'] as $image) {
                $productSliderImages[] = [
                    'id' => $image->id,
                    'url_main' => getProductImageURL($image, 'image_default'),
                    'url_thumb' => getProductImageURL($image, 'image_small'),
                    'url_full' => getProductImageURL($image, 'image_big'),
                ];
            }

            //product options
            $productOptionsModel = new ProductOptionsModel();
            $data['initialProductData_json'] = null;
            $data['allProductImages_json'] = null;
            $data['initialVariant'] = null;
            $data['optionsContainerMinHeight'] = 0;

            $productOptionsData = $productOptionsModel->loadProductOptionsData($product, true);
            if (!empty($productOptionsData) && !empty($productOptionsData['options']) && countItems($productOptionsData['options']) > 0) {
                //get options data
                $productOptionsData = $productOptionsModel->getFormattedVariantDataForDetailPage($productOptionsData, $product);
                $data['initialProductData_json'] = json_encode($productOptionsData, JSON_UNESCAPED_UNICODE);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $data['initialProductData_json'] = null;
                }
                $data['optionsContainerMinHeight'] = calculateOptionsContainerHeight($productOptionsData);

                //get options images
                $allProductImages = $productOptionsModel->getFormattedProductImages($product->id);
                if (!empty($allProductImages)) {
                    $data['allProductImages_json'] = json_encode($allProductImages);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $data['allProductImages_json'] = null;
                    }
                }

                //set initial variant
                $skuFromUrl = inputGet('sku');
                $initialVariant = null;
                if (!empty($skuFromUrl)) {
                    $initialVariant = $productOptionsModel->getVariantBySku($skuFromUrl, $product->id);
                }
                if (empty($initialVariant)) {
                    $initialVariant = $productOptionsModel->getDefaultVariant($product->id);
                }
                if (!empty($initialVariant)) {
                    $price = $initialVariant->price;
                    $priceDiscounted = $initialVariant->price_discounted;
                    if ($priceDiscounted <= 0 && $price <= 0) {
                        $price = $product->price;
                        $priceDiscounted = $product->price_discounted;
                    }

                    $data['initialVariant'] = $initialVariant;
                    $data['productSku'] = $initialVariant->sku;
                    $data['productPrice'] = !empty($price) && $price > 0 ? priceFormatted($price, $product->currency, $convertCurrency) : '';
                    $data['productPriceDiscounted'] = priceFormatted($priceDiscounted, $product->currency, $convertCurrency);
                    $data['productDiscountRate'] = calculateDiscount($price, $priceDiscounted);
                    $data['productStock'] = getProductStock($product, $initialVariant);
                    $data['hasOptionImages'] = $productOptionsModel->hasImagesForVariant($initialVariant, $productOptionsData) ? true : false;

                    $variantImageIds = $productOptionsModel->getVariantImageIds($initialVariant->id);
                    if (!empty($variantImageIds)) {
                        $filteredImages = array_filter($allProductImages, function ($image) use ($variantImageIds) {
                            return in_array((string)$image['id'], $variantImageIds, true);
                        });
                        if (!empty($filteredImages)) {
                            $productSliderImages = array_values($filteredImages); // Re-index the array
                        }
                    }
                }
            }

            $data['productSliderImages'] = $productSliderImages;

            //set JSON-LD data
            $jsonLdGenerator = new JsonLdGenerator();
            $typesToGenerate = ['product', 'breadcrumb'];
            $data['jsonLdScript'] = $jsonLdGenerator->generate($typesToGenerate, $data);

            echo view('partials/_header', $data);
            echo view('product/details/product', $data);
            echo view('partials/_footer');

            //increase pageviews
            $this->productModel->increaseProductPageviews($product);
        }
    }

    /**
     * Search
     */
    public function search()
    {
        $search = removeSpecialCharacters(inputGet('search'));
        if (empty($search)) {
            return redirect()->to(langBaseUrl());
        }
        return redirect()->to(generateUrl('products') . '?search=' . $search);
    }

    /**
     * Shops
     */
    public function shops()
    {
        if ($this->generalSettings->multi_vendor_system != 1) {
            return redirect()->to(langBaseUrl());
        }
        $page = $this->pageModel->getPageByDefaultName('shops', selectedLangId());
        if (empty($page)) {
            return redirect()->to(langBaseUrl());
        }
        if ($page->visibility == 0) {
            $this->error404();
        } else {
            $data = [
                'title' => $page->title,
                'description' => $page->description,
                'keywords' => $page->keywords,
                'page' => $page,
                'userSession' => getUserSession(),
                'isTranslatable' => true
            ];

            $numRows = $this->authModel->getVendorsCount();
            $data['pager'] = paginate(40, $numRows);
            $data['shops'] = $this->authModel->getVendorsPaginated(40, $data['pager']->offset);

            echo view('partials/_header', $data);
            echo view('shops', $data);
            echo view('partials/_footer');
        }
    }

    /**
     * Contact
     */
    public function contact()
    {
        $page = $this->pageModel->getPageByDefaultName('contact', selectedLangId());
        if (empty($page)) {
            return redirect()->to(langBaseUrl());
        }
        if ($page->visibility == 0) {
            $this->error404();
        } else {
            $data = [
                'title' => $page->title,
                'description' => $page->description . ' - ' . $this->baseVars->appName,
                'keywords' => $page->keywords . ' - ' . $this->baseVars->appName,
                'page' => $page,
                'userSession' => getUserSession(),
                'isTranslatable' => true
            ];

            echo view('partials/_header', $data);
            echo view('contact', $data);
            echo view('partials/_footer');
        }
    }

    /**
     * Contact Page Post
     */
    public function contactPost()
    {
        //bot verification
        if (!authCheck()) {
            verifyTurnstile();
        }

        $contactUrl = inputPost('contact_url');
        if (!empty($contactUrl)) {
            exit();
        }

        $val = \Config\Services::validation();
        $val->setRule('name', trans("name"), 'required|max_length[255]');
        $val->setRule('email', trans("email_address"), 'required|valid_email|max_length[255]');
        $val->setRule('message', trans("message"), 'required|max_length[5000]');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            if ($this->commonModel->addContactMessage()) {
                setSuccessMessage(trans("msg_message_sent"));
                return redirect()->to(generateUrl('contact'));
            } else {
                setErrorMessage(trans("msg_contact_error"));
                return redirect()->back()->withInput();
            }
        }
    }

    /**
     * Affiliate
     */
    public function affiliate($slug)
    {
        if ($this->affiliateSettings->status == 1) {
            if ($affiliateLink = $this->commonModel->getAffiliateLinkBySlug($slug)) {
                if (!empty($affiliateLink->link_short)) {
                    $url = $this->commonModel->convertAffiliateLink($affiliateLink);
                    if (!empty($url)) {
                        $this->commonModel->setAffiliateCookie($affiliateLink);
                        header('Location: ' . $url);
                        exit();
                    }
                }
            }
        }
        return redirect()->to(langBaseUrl());
    }

    /**
     * Affiliate Program
     */
    public function affiliateProgram()
    {
        if ($this->affiliateSettings->status != 1) {
            return redirect()->to(langBaseUrl());
        }

        $data = setPageMeta(trans("affiliate_program"));

        $data['userSession'] = getUserSession();
        $data['affDesc'] = !empty($this->settings->affiliate_description) ? unserializeData($this->settings->affiliate_description) : '';
        $data['affContent'] = !empty($this->settings->affiliate_content) ? unserializeData($this->settings->affiliate_content) : '';
        $data['affWorks'] = !empty($this->settings->affiliate_works) ? unserializeData($this->settings->affiliate_works) : '';
        $data['affFaq'] = !empty($this->settings->affiliate_faq) ? unserializeData($this->settings->affiliate_faq) : '';
        $data['isTranslatable'] = true;

        if (authCheck() && !empty(user()->country_id)) {
            $data['states'] = $this->locationModel->getStatesByCountry(user()->country_id);
        }

        echo view('partials/_header', $data);
        echo view('affiliate_program', $data);
        echo view('partials/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Membership
     * --------------------------------------------------------------------
     */

    /**
     * Start Selling
     */
    public function startSelling()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        if (isVendor()) {
            return redirect()->to(langBaseUrl());
        }
        if ($this->generalSettings->email_verification == 1 && user()->email_status != 1) {
            setErrorMessage(trans("msg_confirmed_required"));
            return redirect()->to(generateUrl('settings', 'edit_profile'));
        }

        $data = setPageMeta(trans("start_selling"));

        $locationModel = new LocationModel();
        $data['states'] = $locationModel->getStatesByCountry(user()->country_id);
        $data['cities'] = $locationModel->getCitiesByState(user()->state_id);
        $data['isTranslatable'] = true;

        echo view('partials/_header', $data);
        echo view('product/start_selling', $data);
        echo view('partials/_footer');
    }

    /**
     * Start Selling Post
     */
    public function startSellingPost()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        if (isVendor()) {
            return redirect()->to(langBaseUrl());
        }
        $data = [
            'username' => removeSpecialCharacters(inputPost('username')),
            'first_name' => inputPost('first_name'),
            'last_name' => inputPost('last_name'),
            'phone_number' => inputPost('phone_number'),
            'country_id' => inputPost('country_id'),
            'state_id' => inputPost('state_id'),
            'city_id' => inputPost('city_id'),
            'about_me' => inputPost('about_me'),
            'vendor_documents' => '',
            'is_active_shop_request' => 1,
            'shop_request_date' => date('Y-m-d H:i:s')
        ];
        //is shop name unique
        if (!$this->authModel->isUniqueUsername($data['username'], user()->id)) {
            setErrorMessage(trans("msg_shop_name_unique_error"));
            redirectToBackUrl();
        }
        $membershipModel = new MembershipModel();
        //validate uploaded files
        if ($this->generalSettings->request_documents_vendors == 1) {
            $filesValid = true;
            if (!empty($_FILES['file'])) {
                for ($i = 0; $i < countItems($_FILES['file']['name']); $i++) {
                    if ($_FILES['file']['size'][$i] > 5242880) {
                        $filesValid = false;
                    }
                }
            }
            if ($filesValid == false) {
                setErrorMessage(trans("file_too_large") . ' 5MB');
                redirectToBackUrl();
            }
            $uploadModel = new UploadModel();
            $vendorDocs = $uploadModel->uploadVendorDocuments();
            if (!empty($vendorDocs)) {
                $data['vendor_documents'] = serialize($vendorDocs);
            }
        }
        if ($membershipModel->addShopOpeningRequest($data)) {
            //send email
            $membershipModel->addShopOpeningEmail(user()->id);
            $membershipModel->addShopOpeningEmailAdmin();
            setSuccessMessage(trans("msg_start_selling"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Select Membership Plan
     */
    public function selectMembershipPlan()
    {
        if ($this->generalSettings->membership_plans_system != 1) {
            return redirect()->to(langBaseUrl());
        }
        if (!isVendor()) {
            return redirect()->to(langBaseUrl());
        }
        if ($this->generalSettings->email_verification == 1 && user()->email_status != 1) {
            setErrorMessage(trans("msg_confirmed_required"));
            return redirect()->to(generateUrl('settings', 'edit_profile'));
        }

        $data = setPageMeta(trans("select_your_plan"));

        $membershipModel = new MembershipModel();
        $data["membershipPlans"] = $membershipModel->getPlans();
        $data['userCurrentPlan'] = $membershipModel->getUserPlanByUserId(user()->id);
        $data['userAdsCount'] = $membershipModel->getUserAdsCount(user()->id);

        echo view('partials/_header', $data);
        echo view('product/select_membership_plan', $data);
        echo view('partials/_footer');
    }

    /**
     * Select Membership Plan Post
     */
    public function selectMembershipPlanPost()
    {
        if ($this->generalSettings->membership_plans_system != 1) {
            return redirect()->to(langBaseUrl());
        }
        if (!isVendor()) {
            return redirect()->to(langBaseUrl());
        }
        if ($this->generalSettings->email_verification == 1 && user()->email_status != 1) {
            setErrorMessage(trans("msg_confirmed_required"));
            return redirect()->to(generateUrl('settings', 'edit_profile'));
        }
        $membershipModel = new MembershipModel();
        $planId = inputPost('plan_id');
        if (empty($planId)) {
            return redirect()->back();
        }
        $plan = $membershipModel->getPlan($planId);
        if (empty($plan)) {
            return redirect()->back();
        }
        if ($plan->is_free == 1) {
            $membershipModel->addUserFreePlan($plan, user()->id);
            return redirect()->to(generateDashUrl('shop_settings'));
        }

        //check user has plan
        $requestType = 'new';
        if (!empty($membershipModel->getUserPlanByUserId(user()->id, false))) {
            $requestType = 'renew';
        }

        $data = new \stdClass();
        $data->planRequestType = $requestType;
        $data->planId = $plan->id;

        $checkoutModel = new CheckoutModel();
        $checkoutModel->setServicePaymentSession('membership', trans("membership_plan_payment"), $plan->price, $data);

        return redirect()->to(generateUrl('cart', 'payment_method'));
    }

    /*
     * --------------------------------------------------------------------
     * Blog
     * --------------------------------------------------------------------
     */

    /**
     * Blog
     */
    public function blog()
    {
        $page = $this->pageModel->getPageByDefaultName('blog', selectedLangId());
        if (empty($page)) {
            return redirect()->to(langBaseUrl());
        }
        if ($page->visibility == 0) {
            $this->error404();
        } else {

            $data = [
                'title' => $page->title,
                'description' => $page->description,
                'keywords' => $page->keywords,
                'activeCategory' => 'all',
                'userSession' => getUserSession(),
                'isTranslatable' => false,
            ];

            $numRows = $this->blogModel->getPostCount();
            $data['pager'] = paginate($this->blogPerPage, $numRows);
            $data['posts'] = $this->blogModel->getPostsPaginated($this->blogPerPage, $data['pager']->offset);
            $data['categories'] = $this->blogModel->getCategoriesByLang(selectedLangId());

            echo view('partials/_header', $data);
            echo view('blog/index', $data);
            echo view('partials/_footer');
        }
    }

    /**
     * Blog Category
     */
    public function blogCategory($slug)
    {
        $data['category'] = $this->blogModel->getCategoryBySlug($slug);
        if (empty($data['category'])) {
            return redirect()->to(generateUrl('blog'));
        }

        $data['title'] = $data['category']->name;
        $data['description'] = $data['category']->description;
        $data['keywords'] = $data['category']->keywords;
        $data["activeCategory"] = $data['category']->slug;
        $data['userSession'] = getUserSession();
        $data['isTranslatable'] = false;

        $numRows = $this->blogModel->getPostCountByCategory($data['category']->id);
        $data['pager'] = paginate($this->blogPerPage, $numRows);
        $data['posts'] = $this->blogModel->getCategoryPostsPaginated($data['category']->id, $this->blogPerPage, $data['pager']->offset);
        $data['categories'] = $this->blogModel->getCategoriesByLang(selectedLangId());

        echo view('partials/_header', $data);
        echo view('blog/index', $data);
        echo view('partials/_footer');
    }

    /**
     * Tag
     */
    public function tag($tagSlug)
    {
        $data['tag'] = $this->blogModel->getPostTag($tagSlug);
        if (empty($data['tag'])) {
            return redirect()->to(generateUrl('blog'));
        }

        $data['title'] = $data['tag']->tag;
        $data['description'] = trans("tag") . ': ' . $data['tag']->tag . ' - ' . $this->baseVars->appName;
        $data['keywords'] = trans("tag") . ',' . $data['tag']->tag . ',' . $this->baseVars->appName;
        $data['isTranslatable'] = false;

        $numRows = $this->blogModel->getTagPostsCount($tagSlug);
        $data['pager'] = paginate($this->blogPerPage, $numRows);
        $data['posts'] = $this->blogModel->getTagPostsPaginated($tagSlug, $this->blogPerPage, $data['pager']->offset);

        echo view('partials/_header', $data);
        echo view('blog/tag', $data);
        echo view('partials/_footer');
    }

    /**
     * Post
     */
    public function post($categorySlug, $slug)
    {
        $data['post'] = $this->blogModel->getPostBySlug($slug);
        if (empty($data['post'])) {
            return redirect()->to(generateUrl('blog'));
        }
        $data['title'] = $data['post']->title;
        $data['description'] = $data['post']->summary;
        $data['keywords'] = $data['post']->keywords;
        $data['relatedPosts'] = $this->blogModel->getRelatedPosts($data['post']->category_id, $data['post']->id);
        $data['latestPosts'] = $this->blogModel->getPostsPaginated(3, 0);
        $data['randomTags'] = $this->blogModel->getRandomPostTags();
        $data['postTags'] = $this->blogModel->getPostTags($data['post']->id);
        $data['comments'] = $this->blogModel->getCommentsByPostId($data['post']->id, COMMENTS_LOAD_LIMIT);
        $data['commentsCount'] = $this->blogModel->getActiveCommentsCountByPostId($data['post']->id);
        $data['commentLimit'] = COMMENTS_LOAD_LIMIT;
        $data['postUser'] = getUser($data['post']->user_id);
        $data["category"] = $this->blogModel->getCategory($data['post']->category_id);
        //og tags
        $data['showOgTags'] = true;
        $data['ogTitle'] = $data['post']->title;
        $data['ogDescription'] = $data['post']->summary;
        $data['ogType'] = 'article';
        $data['ogUrl'] = generateUrl('blog') . '/' . $data['post']->category_slug . '/' . $data['post']->slug;
        $data['ogImage'] = getBlogImageURL($data['post'], 'image_default');
        $data['ogWidth'] = '750';
        $data['ogHeight'] = '500';
        $data['ogCreator'] = '';
        $data['ogAuthor'] = '';
        if (!empty($data['postUser'])) {
            $data['ogCreator'] = getUsername($data['postUser']);
            $data['ogAuthor'] = getUsername($data['postUser']);
        }
        $data['ogPublishedTime'] = $data['post']->created_at;
        $data['ogModifiedTime'] = $data['post']->created_at;
        $data['ogTags'] = $data['postTags'];
        $data['isTranslatable'] = false;

        echo view('partials/_header', $data);
        echo view('blog/post', $data);
        echo view('partials/_footer');
    }

    /**
     * Terms & Conditions
     */
    public function termsConditions()
    {
        $page = $this->pageModel->getPageByDefaultName('terms_conditions', selectedLangId());
        if (empty($page)) {
            return redirect()->to(langBaseUrl());
        }
        if ($page->visibility == 0) {
            $this->error404();
        } else {

            $data = [
                'title' => $page->title,
                'description' => $page->description . ' - ' . $this->baseVars->appName,
                'keywords' => $page->keywords . ' - ' . $this->baseVars->appName,
                'page' => $page,
                'isTranslatable' => false,
            ];

            echo view('partials/_header', $data);
            echo view('page', $data);
            echo view('partials/_footer');
        }
    }

    /**
     * Wishlist
     */
    public function wishlist()
    {
        $data = setPageMeta(trans("wishlist"));

        $perPage = $this->productSettings->pagination_per_page;
        if (authCheck()) {
            $data['user'] = user();
            $data['userSession'] = getUserSession();
            $numRows = $this->productModel->getUserWishlistProductsCount($data['user']->id);
            $data['pager'] = paginate($perPage, $numRows);
            $data['products'] = $this->productModel->getPaginatedUserWishlistProducts($data['user']->id, $perPage, $data['pager']->offset);
        } else {
            $numRows = $this->productModel->getGuestWishlistProductsCount();
            $data['pager'] = paginate($perPage, $numRows);
            $data['products'] = $this->productModel->getGuestWishlistProductsPaginated($perPage, $data['pager']->offset);
        }
        $data['isTranslatable'] = true;

        echo view('partials/_header', $data);
        echo view('wishlist', $data);
        echo view('partials/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Chat
     * --------------------------------------------------------------------
     */

    /**
     * Chat
     */
    public function chat()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $data = setPageMeta(trans("messages"));

        $chatModel = new ChatModel();
        $data['chats'] = $chatModel->getChats(user()->id);

        echo view('partials/_header', $data);
        echo view('chat/chat', $data);
        echo view('partials/_footer');
    }

    /**
     * Invoice
     */
    public function invoice($orderNumber)
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }

        $data = setPageMeta(trans("invoice"));

        $data['userSession'] = getUserSession();
        $isValidReq = true;
        $type = inputGet('type');
        if (empty($type) || ($type != 'admin' && $type != 'seller' && $type != 'buyer')) {
            $isValidReq = false;
        }
        if ($type == 'admin') {
            setContextActiveLang(helperGetSession('mds_control_panel_lang'));
        }

        $orderModel = new OrderModel();
        $data['order'] = $orderModel->getOrderByOrderNumber($orderNumber);
        if (empty($data['order'])) {
            return redirect()->to(langBaseUrl());
        }
        $data['invoice'] = $orderModel->getInvoiceByOrderNumber($orderNumber);
        if (empty($data['invoice'])) {
            $orderModel->addInvoice($data['order']->id);
        }

        if (empty($data['invoice'])) {
            return redirect()->to(langBaseUrl());
        }
        $data['invoiceItems'] = unserializeData($data['invoice']->invoice_items);
        $data['orderProducts'] = $orderModel->getOrderItems($data['order']->id);

        $isSeller = false;
        $isBuyer = false;
        if (!empty($data['orderProducts'])) {
            foreach ($data['orderProducts'] as $item) {
                if ($item->seller_id == user()->id) {
                    $isSeller = true;
                }
                if ($item->buyer_id == user()->id) {
                    $isBuyer = true;
                }
            }
        }
        //check permission
        if ($type == 'admin' && !hasPermission('orders')) {
            $isValidReq = false;
        }
        if ($type == 'seller' && $isSeller == false) {
            $isValidReq = false;
        }
        if ($type == 'buyer' && $isBuyer == false) {
            $isValidReq = false;
        }
        if (!$isValidReq) {
            return redirect()->to(langBaseUrl());
        }
        if ($type == 'admin' || $type == 'buyer') {
            echo view('invoice/invoice', $data);
        } elseif ($type == 'seller') {
            echo view('invoice/invoice_seller', $data);
        }
    }

    /**
     * Invoice Membership
     */
    public function invoiceMembership($id)
    {
        $data = setPageMeta(trans("invoice"));

        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        $membershipModel = new MembershipModel();
        $data['transaction'] = $membershipModel->getMembershipTransaction($id);
        if (empty($data['transaction'])) {
            return redirect()->to(langBaseUrl());
        }
        if (!hasPermission('membership') && !hasPermission('payments')) {
            if (user()->id != $data['transaction']->user_id) {
                return redirect()->to(langBaseUrl());
            }
        }
        $data['user'] = getUser($data['transaction']->user_id);
        if (empty($data['user'])) {
            return redirect()->to(langBaseUrl());
        }
        echo view('invoice/invoice_membership', $data);
    }

    /**
     * Invoice Promotion
     */
    public function invoicePromotion($id)
    {
        $data = setPageMeta(trans("invoice"));

        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        $promoteModel = new PromoteModel();
        $data['transaction'] = $promoteModel->getTransaction($id);
        if (empty($data['transaction'])) {
            return redirect()->to(langBaseUrl());
        }
        if (!hasPermission('products') && !hasPermission('payments')) {
            if (user()->id != $data['transaction']->user_id) {
                return redirect()->to(langBaseUrl());
            }
        }
        $data['user'] = getUser($data['transaction']->user_id);
        if (empty($data['user'])) {
            return redirect()->to(langBaseUrl());
        }
        echo view('invoice/invoice_promotion', $data);
    }

    /**
     * Invoice Wallet Deposit
     */
    public function invoiceWalletDeposit($id)
    {
        $data = setPageMeta(trans("invoice"));

        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        $earningsModel = new EarningsModel();
        $data['transaction'] = $earningsModel->getDepositTransaction($id);
        if (empty($data['transaction'])) {
            return redirect()->to(langBaseUrl());
        }
        if (!hasPermission('payments')) {
            if (user()->id != $data['transaction']->user_id) {
                return redirect()->to(langBaseUrl());
            }
        }
        $data['user'] = getUser($data['transaction']->user_id);
        if (empty($data['user'])) {
            return redirect()->to(langBaseUrl());
        }
        echo view('invoice/invoice_deposit', $data);
    }

    /**
     * Invoice Expense
     */
    public function invoiceExpense($id)
    {
        $data = setPageMeta(trans("invoice"));

        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        $earningsModel = new EarningsModel();
        $data['transaction'] = $earningsModel->getExpense($id);
        if (empty($data['transaction'])) {
            return redirect()->to(langBaseUrl());
        }
        if (!hasPermission('payments')) {
            if (user()->id != $data['transaction']->user_id) {
                return redirect()->to(langBaseUrl());
            }
        }
        $data['user'] = getUser($data['transaction']->user_id);
        if (empty($data['user'])) {
            return redirect()->to(langBaseUrl());
        }
        echo view('invoice/invoice_expense', $data);
    }


    /**
     * Rss Feeds
     */
    public function rssFeeds()
    {
        if ($this->generalSettings->rss_system != 1) {
            redirectToUrl(langBaseUrl());
        }

        $data = setPageMeta(trans("rss_feeds"));

        echo view('partials/_header', $data);
        echo view('rss/rss_feeds', $data);
        echo view('partials/_footer');
    }

    /**
     * Rss Latest Products
     */
    public function latestProducts()
    {
        if ($this->generalSettings->rss_system != 1) {
            return $this->response->setStatusCode(403, 'RSS Disabled');
        }

        $data = [
            'feedName' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . trans("latest_products"),
            'encoding' => 'utf-8',
            'feedUrl' => langBaseUrl() . 'rss/' . getRoute("latest_products"),
            'pageDescription' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . trans("latest_products"),
            'products' => $this->productModel->getLatestProducts($this->activeLang->id, 30)
        ];

        header('Content-Type: application/rss+xml; charset=utf-8');
        return $this->response->setXML(view('rss/rss', $data));
    }

    /**
     * Rss Featured Products
     */
    public function featuredProducts()
    {
        if ($this->generalSettings->rss_system != 1) {
            return $this->response->setStatusCode(403, 'RSS Disabled');
        }

        $data = [
            'feedName' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . trans("featured_products"),
            'encoding' => 'utf-8',
            'feedUrl' => langBaseUrl() . 'rss/' . getRoute("featured_products"),
            'pageDescription' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . trans("featured_products"),
            'products' => $this->productModel->getPromotedProducts()
        ];

        header('Content-Type: application/rss+xml; charset=utf-8');
        return $this->response->setXML(view('rss/rss', $data));
    }

    /**
     * Rss By Category
     */
    public function rssByCategory($slug)
    {
        if ($this->generalSettings->rss_system != 1) {
            return $this->response->setStatusCode(403, 'RSS Disabled');
        }

        $category = $this->categoryModel->getCategoryBySlug($slug);
        if (empty($category)) {
            return redirect()->to(generateUrl('rss_feeds'));
        }

        $data = [
            'feedName' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . esc($category->cat_name),
            'encoding' => 'utf-8',
            'feedUrl' => langBaseUrl() . 'rss/' . getRoute("category", true) . $slug,
            'pageDescription' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . esc($category->cat_name),
            'products' => $this->productModel->getRssProductsByCategory($category->id, $this->activeLang->id)
        ];

        header('Content-Type: application/rss+xml; charset=utf-8');
        return $this->response->setXML(view('rss/rss', $data));
    }

    /**
     * Rss By Seller
     */
    public function rssBySeller($slug)
    {
        if ($this->generalSettings->rss_system != 1) {
            return $this->response->setStatusCode(403, 'RSS Disabled');
        }

        $user = $this->authModel->getUserBySlug($slug);
        if (empty($user)) {
            return redirect()->to(generateUrl('rss_feeds'));
        }
        if ($user->show_rss_feeds != 1) {
            return redirect()->to(generateProfileUrl($slug));
        }

        $data = [
            'feedName' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . getUsername($user),
            'encoding' => 'utf-8',
            'feedUrl' => langBaseUrl() . 'rss/' . getRoute("seller", true) . $slug,
            'pageDescription' => $this->baseVars->appName . ' ' . trans("rss_feeds") . ' - ' . getUsername($user),
            'products' => $this->productModel->getRssProductsByUser($user->id, $this->activeLang->id)
        ];

        header('Content-Type: application/rss+xml; charset=utf-8');
        return $this->response->setXML(view('rss/rss', $data));
    }

    /**
     * Bank Transfer Payment Report Post
     */
    public function bankTransferPaymentReportPost()
    {
        if (authCheck()) {
            $this->commonModel->addBankTransferPaymentReport();
        }
        redirectToBackUrl();
    }

    /**
     * Unsubscribe
     */
    public function unSubscribe()
    {
        $data = setPageMeta(trans("unsubscribe"));

        $newsletterModel = new NewsletterModel();
        $token = removeSpecialCharacters(inputGet('token'));
        $subscriber = $newsletterModel->getSubscriberByToken($token);
        if (empty($subscriber)) {
            redirectToUrl(langBaseUrl());
        }
        $newsletterModel->unSubscribeEmail($subscriber->email);

        echo view('partials/_header', $data);
        echo view('unsubscribe');
        echo view('partials/_footer');
    }

    /**
     * Set Location Post
     */
    public function setDefaultLocationPost()
    {
        if (inputPost('form_type') == 'set_user_location') {
            if (authCheck()) {
                $profileModel = new ProfileModel();
                $profileModel->updateLocation();
            } else {
                $data = [
                    'country_id' => inputPost('country_id'),
                    'state_id' => inputPost('state_id')
                ];
                helperSetSession('mds_estimated_delivery_location', $data);
            }
        } else {
            $this->locationModel->setDefaultLocation();
        }
        return redirect()->back();
    }

    /**
     * Cron Update Sitemap
     */
    public function cronUpdateSitemap()
    {
        $model = new SitemapModel();
        $model->generate();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Sitemap updated successfully!']);
    }

    /**
     * Set currency
     */
    public function setSelectedCurrency()
    {
        $currencyModel = new CurrencyModel();
        $currencyModel->setSelectedCurrency();
        return redirect()->back();
    }

    /**
     * Error 404
     */
    public function error404()
    {
        http_response_code(404);

        $data = setPageMeta(trans("page_not_found"));

        echo view('partials/_header', $data);
        echo view('errors/html/error_404');
        echo view('partials/_footer', $data);

        exit;
    }
}
