<?php

namespace App\Controllers;

use App\Libraries\Export;
use App\Models\BiddingModel;
use App\Models\CheckoutModel;
use App\Models\CouponModel;
use App\Models\EarningsModel;
use App\Models\FieldModel;
use App\Models\FileModel;
use App\Models\LocationModel;
use App\Models\MembershipModel;
use App\Models\OrderAdminModel;
use App\Models\OrderModel;
use App\Models\PageModel;
use App\Models\ProductAdminModel;
use App\Models\ProductModel;
use App\Models\ProfileModel;
use App\Models\PromoteModel;
use App\Models\ShippingModel;
use App\Models\ProductOptionsModel;
use App\Services\OngkirService;

class DashboardController extends BaseController
{
    protected $orderAdminModel;
    protected $orderModel;
    protected $productAdminModel;
    protected $membershipModel;
    protected $shippingModel;
    protected $couponModel;
    protected $fileModel;
    protected $userId;
    protected $perPage;
    protected $isDashboard;
    protected $profileModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if (!authCheck()) {
            redirectToUrl(langBaseUrl());
        }
        if (!isVendor() && !hasPermission('products')) {
            if ($this->generalSettings->membership_plans_system == 1) {
                redirectToUrl(generateUrl('start_selling'));
            }
            redirectToUrl(generateUrl('start_selling'));
        }
        $this->profileModel = new ProfileModel();
        $this->orderAdminModel = new OrderAdminModel();
        $this->orderModel = new OrderModel();
        $this->productAdminModel = new ProductAdminModel();
        $this->membershipModel = new MembershipModel();
        $this->shippingModel = new ShippingModel();
        $this->couponModel = new CouponModel();
        $this->fileModel = new FileModel();
        $this->userId = user()->id;
        $this->perPage = 15;
        $this->isDashboard = true;

        checkVendorCommissionDept();
    }

    /**
     * Index
     */
    public function index()
    {
        $data = setPageMeta(getUsername(user()));
        $data['user'] = user();
        $data["activeTab"] = 'products';

        $data['activeSalesCount'] = $this->orderAdminModel->getActiveSalesCountBySeller($this->userId);
        $data['completedSalesCount'] = $this->orderAdminModel->getCompletedSalesCountBySeller($this->userId);
        $data['totalSalesCount'] = $data['activeSalesCount'] + $data['completedSalesCount'];
        $data['balance'] = priceFormatted(user()->balance, $this->defaultCurrency->code);
        $data['productsCount'] = $this->productModel->getSellerTotalProductsCount($this->userId);
        $data['pendingProductsCount'] = $this->productModel->getSellerTotalProductsCount($this->userId, 'pending');

        $data['latestSales'] = $this->orderModel->getSalesBySellerLimited($this->userId, 6);
        $data['latestComments'] = $this->commonModel->getVendorCommentsPaginated($this->userId, 6, 0);
        $data['panelSettings'] = getPanelSettings();
        $data['latestReviews'] = $this->commonModel->getUserReviewsPaginated($this->userId, 6, 0);
        $data['salesSum'] = $this->orderAdminModel->getSalesSumByMonth($this->userId);

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/index', $data);
        echo view('dashboard/includes/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Products
     * --------------------------------------------------------------------
     */

    /**
     * Add Product
     */
    public function addProduct()
    {
        $data = $this->setMetaData(trans("add_product"));
        $data['images'] = $this->fileModel->getSessProductImagesArray();
        $data["fileManagerImages"] = $this->fileModel->getUserFileManagerImages($this->userId);
        $view = !$this->membershipModel->isAllowedAddingProduct() ? 'plan_expired' : 'add_product';
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['panelSettings'] = getPanelSettings();

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/product/' . $view, $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Add Product Post
     */
    public function addProductPost()
    {
        if (!$this->membershipModel->isAllowedAddingProduct()) {
            setErrorMessage(trans("msg_plan_expired"));
            redirectToBackUrl();
        }
        //validate title
        if (empty(trim(inputPost('title_' . selectedLangId()) ?? ''))) {
            setErrorMessage(trans("msg_error"));
            redirectToBackUrl();
        }
        //add product
        if ($insertId = $this->productModel->addProduct()) {
            //add product title and desc
            $this->productModel->addProductTitleDesc($insertId);
            //update slug
            $this->productModel->updateSlug($insertId);
            //add product images
            $this->fileModel->addProductImages($insertId);
            //update search index
            $this->productModel->syncProductSearchIndex($insertId);
            return redirect()->to(generateDashUrl('product', 'product_details') . '/' . $insertId);
        } else {
            setErrorMessage(trans("msg_error"));
            redirectToBackUrl();
        }
    }

    /**
     * Edit Product
     */
    public function editProduct($id)
    {
        $product = $this->productAdminModel->getProduct($id);
        if (empty($product)) {
            return redirect()->to(dashboardUrl());
        }
        if ($product->is_deleted == 1) {
            if (!hasPermission('products')) {
                return redirect()->to(dashboardUrl());
            }
        }
        if ($product->user_id != $this->userId && !hasPermission('products')) {
            return redirect()->to(dashboardUrl());
        }
        $title = $product->is_draft == 1 ? trans('add_product') : trans('edit_product');
        $data = $this->setMetaData($title);
        $data['product'] = $product;
        $data['category'] = $this->categoryModel->getCategory($product->category_id);
        $data['productImages'] = $this->fileModel->getProductImages($product->id);
        $data['fileManagerImages'] = $this->fileModel->getUserFileManagerImages($this->userId);
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['panelSettings'] = getPanelSettings();

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/product/edit_product', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Edit Product Post
     */
    public function editProductPost()
    {
        $productId = inputPost('id');
        $userId = 0;
        $product = $this->productAdminModel->getProduct($productId);
        if (!empty($product)) {
            if ($product->user_id != $this->userId && !hasPermission('products')) {
                return redirect()->to(dashboardUrl());
            }
            if ($this->productSettings->is_product_image_required == 1 && countItems(getProductImages($product->id)) < 1) {
                setErrorMessage(trans("error_product_image_required"));
                return redirect()->to(generateDashUrl('edit_product') . '/' . $product->id);
            }
            //validate title
            $title = inputPost('title_' . selectedLangId());
            if (empty(trim($title ?? ''))) {
                setErrorMessage(trans("msg_error"));
                redirectToBackUrl();
            }
            //check slug is unique
            $slug = $product->slug;
            if (isAdmin() || hasPermission('products')) {
                $slug = inputPost('slug');
                if (empty($slug)) {
                    $slug = strSlug($title) . '-' . $product->id;
                }
                if (!$this->productAdminModel->isProductSlugUnique($product->id, $slug)) {
                    setErrorMessage(trans("msg_product_slug_used"));
                    redirectToBackUrl();
                }
            }
            if ($this->productModel->editProduct($product, $slug)) {
                setProductAsEdited($product->id);
                //edit product title and desc
                $this->productModel->editProductTitleDesc($product->id);
                //update search index
                $this->productModel->syncProductSearchIndex($product->id);
                if ($product->is_draft == 1) {
                    return redirect()->to(generateDashUrl('product', 'product_details') . '/' . $product->id);
                } else {
                    setSuccessMessage(trans("msg_updated"));
                    resetCacheDataOnChange();
                    redirectToBackUrl();
                }
            }
        }
        setErrorMessage(trans("msg_error"));
        redirectToBackUrl();
    }

    /**
     * Edit Product Details
     */
    public function editProductDetails($id)
    {
        $product = $this->productAdminModel->getProduct($id);
        if (empty($product)) {
            return redirect()->to(dashboardUrl());
        }
        if ($product->is_deleted == 1) {
            if (!hasPermission('products')) {
                return redirect()->to(dashboardUrl());
            }
        }
        if ($product->user_id != $this->userId && !hasPermission('products')) {
            return redirect()->to(dashboardUrl());
        }
        if ($this->productSettings->is_product_image_required == 1 && countItems(getProductImages($product->id)) < 1) {
            setErrorMessage(trans("error_product_image_required"));
            return redirect()->to(generateDashUrl('edit_product') . '/' . $product->id);
        }
        $category = getCategory($product->category_id);
        $title = $product->is_draft == 1 ? trans('add_product') : trans('edit_product');
        $data = $this->setMetaData($title);
        $data['product'] = $product;
        
        $fieldModel = new FieldModel();
        $data["customFields"] = $fieldModel->getCustomFieldsByCategory($product->category_id);

        //product options
        $productOptionsModel = new ProductOptionsModel();
        $data['initialProductData_json'] = null;
        $productOptionsData = $productOptionsModel->loadProductOptionsData($product);
        if (!empty($productOptionsData)) {
            $data['initialProductData_json'] = json_encode($productOptionsData, JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data['initialProductData_json'] = null;
            }
        }

        $data['licenseKeys'] = $this->productModel->getProductLicenseKeys($product->id);
        $data['productVideo'] = $this->fileModel->getProductVideo($product->id);
        $data['productAudio'] = $this->fileModel->getProductAudio($product->id);
        $shippingModel = new ShippingModel();
        $data['shippingStatus'] = $this->productSettings->marketplace_shipping;
        $data['brands'] = $this->commonModel->getBrands($this->activeLang->id, $category->id ?? '');
        $data['panelSettings'] = getPanelSettings();
        if ($data['product']->listing_type == 'ordinary_listing' || $data['product']->product_type != 'physical') {
            $data['shippingStatus'] = 0;
        }
        $data['shippingDeliveryTimes'] = $shippingModel->getShippingDeliveryTimes($product->user_id);
        $shippingZones = $shippingModel->getShippingZones($product->user_id);
        $data['showShippingOptionsWarning'] = false;
        if ($data['shippingStatus'] == 1 && empty($shippingZones)) {
            $data['showShippingOptionsWarning'] = true;
        }
        $data['commissionRate'] = $this->orderModel->getProductCommissionRate($product->id);
        $data['productImages'] = $this->fileModel->getProductImages($product->id, true);

        $ongkirService = new OngkirService();
        $result = $ongkirService->getProvinces('100')["data"];
        $data['shippingAddresses'] = $this->profileModel->getShippingAddresses(user()->id);
        $data['states'] = json_decode(json_encode($result));

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/product/edit_product_details', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Edit Product Details Post
     */
    public function editProductDetailsPost()
    {
        $productId = inputPost('id');
        $product = $this->productAdminModel->getProduct($productId);
        if (empty($product)) {
            return redirect()->to(dashboardUrl());
        }
        if ($product->is_deleted == 1) {
            if (!hasPermission('products')) {
                return redirect()->to(dashboardUrl());
            }
        }
        if ($product->user_id != $this->userId && !hasPermission('products')) {
            return redirect()->to(dashboardUrl());
        }

        //save product options
        if (!empty(inputPost('is_options_updated'))) {
            $optionsDataJson = inputPost('submitted_options_data') ?: '[]';
            $variantsDataJson = inputPost('submitted_variants_data') ?: '[]';
            $optionsToSave = json_decode($optionsDataJson, true);
            $variantsToSave = json_decode($variantsDataJson, true);

            //basic validation for JSON decoding
            $isValidJson = true;
            if (json_last_error() !== JSON_ERROR_NONE && $optionsDataJson !== '[]') {
                $isValidJson = false;
            }
            if (json_last_error() !== JSON_ERROR_NONE && $variantsDataJson !== '[]') {
                $isValidJson = false;
            }
            if ($isValidJson) {
                $productOptionsModel = new ProductOptionsModel();
                $productOptionsModel->saveProductOptionsData($product->id, $optionsToSave, $variantsToSave);
            }
        }

        //check digital file
        $digitalFileUploaded = true;
        if ($product->product_type == 'digital' && $product->listing_type != 'license_key') {
            if ($this->productSettings->digital_external_link == 1) {
                if (empty($this->fileModel->getProductDigitalFile($product->id)) && empty(trim(inputPost('digital_file_download_link') ?? ''))) {
                    $digitalFileUploaded = false;
                }
            } else {
                if (empty($this->fileModel->getProductDigitalFile($product->id))) {
                    $digitalFileUploaded = false;
                }
            }
        }
        if ($digitalFileUploaded == false) {
            setErrorMessage(trans("digital_file_required"));
            redirectToBackUrl();
        }
        
        if ($this->productModel->editProductDetails($product->id)) {
            $this->productModel->syncProductSearchIndex($product->id);
            setProductAsEdited($product->id);
            //edit custom fields
            $this->productModel->updateProductCustomFields($product->id);
            //reset cache
            resetCacheDataOnChange();
            if ($product->is_draft != 1) {
                setSuccessMessage(trans("msg_updated"));
                redirectToBackUrl();
            } else {
                //if draft
                if (inputPost('submit') == 'save_as_draft') {
                    setSuccessMessage(trans("draft_added"));
                } else {
                    if ($this->generalSettings->approve_before_publishing == 1 && !isAdmin()) {
                        setSuccessMessage(trans("product_added") . " " . trans("product_approve_published") . " <a href='" . generateProductUrl($product) . "' class='link-view-product'>" . trans("view_product") . "</a>");
                    } else {
                        setSuccessMessage(trans("product_added") . " <a href='" . generateProductUrl($product) . "' class='link-view-product' target='_blank'>" . trans("view_product") . "</a>");
                    }
                    //send email
                    if (getEmailOptionStatus($this->generalSettings, 'new_product') == 1) {
                        $emailData = [
                            'email_type' => 'new_product',
                            'email_address' => $this->generalSettings->mail_options_account,
                            'email_subject' => trans("email_text_new_product"),
                            'template_path' => 'email/main',
                            'email_data' => serialize([
                                'content' => trans("email_text_see_product"),
                                'url' => generateProductUrl($product),
                                'buttonText' => trans("view_product")
                            ])
                        ];
                        addToEmailQueue($emailData);
                    }
                }
                return redirect()->to(generateDashUrl('add_product'));
            }
        } else {
            setErrorMessage(trans('msg_error'));
            redirectToBackUrl();
        }
    }

    /**
     * Products
     */
    public function products()
    {
        $st = inputGet('st');
        $status = 'active';
        $page = trans("products");
        if (!empty($st)) {
            if ($st == 'pending') {
                $status = 'pending';
                $page = trans("pending_products");
            }
            if ($st == 'hidden') {
                $status = 'hidden';
                $page = trans("hidden_products");
            }
            if ($st == 'sold') {
                $status = 'sold';
                $page = trans("sold_products");
            }
            if ($st == 'draft') {
                $status = 'draft';
                $page = trans("drafts");
            }
        }
        $data = $this->setMetaData($page);
        $data['numRows'] = $this->productModel->getVendorProductsCount($this->userId, $status);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['products'] = $this->productModel->getVendorProductsPaginated($this->userId, $status, $this->perPage, $data['pager']->offset);
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['panelSettings'] = getPanelSettings();
        $data['productListStatus'] = $status;
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/product/products', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Duplicate Product
     */
    public function duplicateProductPost()
    {
        $productId = inputPost('product_id');

        if ($this->productModel->duplicateProduct($productId)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        exit();
    }

    /**
     * Delete Product
     */
    public function deleteProduct()
    {
        $id = inputPost('id');
        $userId = 0;
        $result = false;
        $product = $this->productAdminModel->getProduct($id);
        if (!empty($product)) {
            $userId = $product->user_id;
            if (hasPermission('products') || $this->userId == $userId) {
                if ($product->is_draft == 1) {
                    $result = $this->productAdminModel->deleteProductPermanently($id);
                } else {
                    $result = $this->productAdminModel->deleteProduct($id);
                }
            }
            if ($result) {
                setSuccessMessage(trans("msg_deleted"));
                resetCacheDataOnChange();
            } else {
                setErrorMessage(trans("msg_error"));
            }
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    //get subcategories
    public function getSubCategories()
    {
        $parentId = inputPost('parent_id');
        $data = ['result' => 0];
        if (!empty($parentId)) {
            $subCategories = $this->categoryModel->getSubCategoriesByParentId($parentId);
            $options = '';
            if (!empty($subCategories)) {
                foreach ($subCategories as $item) {
                    $options .= '<option value="' . $item->id . '">' . esc($item->cat_name) . '</option>';
                }
            }
        }
        if (!empty($options)) {
            $data = ['result' => 1, 'options' => $options];
        }
        return jsonResponse($data);
    }

    /*
     * --------------------------------------------------------------------
     * License Keys
     * --------------------------------------------------------------------
     */

    /**
     * Add License Keys
     */
    //
    public function addLicenseKeys()
    {
        $productId = inputPost('product_id');
        $product = getProduct($productId);
        $data = ['result' => 0];
        if (!empty($product)) {
            if ($this->userId == $product->user_id || hasPermission('products')) {
                $this->productModel->addLicenseKeys($productId);
                $data = [
                    'result' => 1,
                    'message' => trans("msg_add_license_keys")
                ];
            }
        }
        return jsonResponse($data);
    }

    //delete license key
    public function deleteLicenseKey()
    {
        $id = inputPost('id');
        $productId = inputPost('product_id');
        $product = getProduct($productId);
        if (!empty($product)) {
            if ($this->userId == $product->user_id || hasPermission('products')) {
                $this->productModel->deleteLicenseKey($id);
            }
        }
        return jsonResponse();
    }

    //load license keys list
    public function loadLicenseKeysList()
    {
        $productId = inputPost('product_id');
        $vars['product'] = getProduct($productId);
        if (!empty($vars['product'])) {
            if ($this->userId == $vars['product']->user_id || hasPermission('products')) {
                $vars['licenseKeys'] = $this->productModel->getProductLicenseKeys($productId);
                $data = [
                    'result' => 1,
                    'htmlContent' => view('dashboard/product/license/_license_keys_list', $vars)
                ];
            }
        } else {
            $data = ['result' => 0];
        }
        return jsonResponse($data);
    }

    /*
     * --------------------------------------------------------------------
     * Bulk Product Upload
     * --------------------------------------------------------------------
     */

    /**
     * Bulk Product Upload
     */
    public function bulkProductUpload()
    {
        $data = $this->setMetaData(trans("bulk_product_upload"));
        $view = !$this->membershipModel->isAllowedAddingProduct() ? 'plan_expired' : 'bulk_product_upload';
        if (!hasPermission('products') && $this->generalSettings->vendor_bulk_product_upload != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data['parentCategories'] = $this->categoryModel->getParentCategories();

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/product/' . $view, $data);
        echo view('dashboard/includes/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Promote
     * --------------------------------------------------------------------
     */

    /**
     * Promote Product Post
     */
    public function promoteProductPost()
    {
        $productId = inputPost('product_id');
        $product = getProduct($productId);
        if (!empty($product)) {
            if ($product->user_id != $this->userId) {
                setErrorMessage(trans("invalid_attempt"));
                redirectToBackUrl();
            }
            $planType = inputPost('plan_type');
            $pricePerDay = numToDecimal($this->paymentSettings->price_per_day);
            $pricePerMonth = numToDecimal($this->paymentSettings->price_per_month);
            $dayCount = inputPost('day_count');
            $monthCount = inputPost('month_count');
            $totalAmount = 0;
            if ($planType == 'daily') {
                $totalAmount = $dayCount * $pricePerDay;
                $purchasedPlan = trans("daily_plan") . ' (' . $dayCount . ' ' . trans("days") . ')';
            }
            if ($planType == 'monthly') {
                $dayCount = $monthCount * 30;
                $totalAmount = $monthCount * $pricePerMonth;
                $purchasedPlan = trans("monthly_plan") . ' (' . $dayCount . ' ' . trans("days") . ')';
            }

            $serviceData = new \stdClass();
            $serviceData->paymentType = 'promote';
            $serviceData->paymentName = trans("product_promoting_payment");
            $serviceData->planType = $planType;
            $serviceData->productId = $productId;
            $serviceData->dayCount = $dayCount;
            $serviceData->monthCount = $monthCount;
            $serviceData->purchasedPlan = $purchasedPlan;
            $serviceData->subtotal = numToDecimal($totalAmount);
            $serviceData->grandTotal = numToDecimal($totalAmount);
            $serviceData->currency = $this->defaultCurrency->code;

            if ($this->paymentSettings->free_product_promotion == 1) {
                $promoteModel = new PromoteModel();
                $promoteModel->addToPromotedProducts($serviceData);
                redirectToBackUrl();
            } else {

                $data = new \stdClass();
                $data->planType = $planType;
                $data->productId = $productId;
                $data->dayCount = $dayCount;
                $data->monthCount = $monthCount;
                $data->purchasedPlan = $purchasedPlan;

                $checkoutModel = new CheckoutModel();
                $checkoutModel->setServicePaymentSession('promote', trans("product_promoting_payment"), $totalAmount, $data);

                return redirect()->to(generateUrl('cart', 'payment_method'));
            }
        }
        setErrorMessage(trans("invalid_attempt"));
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Sales
     * --------------------------------------------------------------------
     */

    /**
     * Sales
     */
    public function sales()
    {
        if (!$this->baseVars->isSaleActive) {
            return redirect()->to(dashboardUrl());
        }
        $st = inputGet('st');
        $page = 'sales';
        $status = 'active';
        if ($st == 'completed') {
            $page = 'completed_sales';
            $status = 'completed';
        } elseif ($st == 'cancelled') {
            $page = 'cancelled_sales';
            $status = 'cancelled';
        }
        $data = $this->setMetaData(trans($page));
        $data['page'] = $page;
        $data['numRows'] = $this->orderModel->getSalesCount($status, $this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['sales'] = $this->orderModel->getSalesPaginated($status, $this->userId, $this->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/sales/sales', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Sale
     */
    public function sale($orderNumber)
    {
        if (!$this->baseVars->isSaleActive) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("sale"));
        $data['order'] = $this->orderModel->getOrderByOrderNumber($orderNumber);
        if (empty($data['order'])) {
            return redirect()->to(dashboardUrl());
        }
        if (!$this->orderModel->checkOrderSeller($data['order']->id)) {
            return redirect()->to(dashboardUrl());
        }
        $data['orderProducts'] = $this->orderModel->getOrderItems($data['order']->id);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/sales/sale', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Update Order Product Status Post
     */
    public function updateOrderProductStatusPost()
    {
        $id = inputPost('id');
        $orderProduct = $this->orderModel->getOrderProduct($id);
        if ($this->userId != $orderProduct->seller_id) {
            return redirect()->to(dashboardUrl());
        }
        if (!empty($orderProduct)) {
            if ($this->orderModel->updateOrderProductStatus($id)) {
                $this->orderAdminModel->updateOrderStatusIfCompleted($orderProduct->order_id);
            }
        }
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Quote Requests
     * --------------------------------------------------------------------
     */

    /**
     * Quote Requests
     */
    public function quoteRequests()
    {
        if ($this->generalSettings->bidding_system != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("quote_requests"));
        $biddingModel = new BiddingModel();
        $data['numRows'] = $biddingModel->getVendorQuoteRequestsCount($this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['quoteRequests'] = $biddingModel->getVendorQuoteRequestsPaginated($this->userId, $this->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/quote_requests', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Submit Quote
     */
    public function submitQuotePost()
    {
        $biddingModel = new BiddingModel();
        $id = inputPost('id');
        $quoteRequest = $biddingModel->getQuoteRequest($id);
        if ($biddingModel->submitQuote($quoteRequest)) {
            //send email
            $buyer = getUser($quoteRequest->buyer_id);
            if (!empty($buyer) && getEmailOptionStatus($this->generalSettings, 'bidding_system') == 1) {
                $emailData = [
                    'email_type' => 'quote',
                    'email_address' => $buyer->email,
                    'email_subject' => trans("quote_request"),
                    'template_path' => 'email/main',
                    'email_data' => serialize([
                        'content' => trans("your_quote_request_replied") . "<br>" . trans("quote") . ": " . "<strong>#" . $quoteRequest->id . "</strong>",
                        'url' => generateUrl('quote_requests'),
                        'buttonText' => trans("view_details")
                    ])
                ];
                addToEmailQueue($emailData);
            }
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Cash On Delivery
     * --------------------------------------------------------------------
     */

    /**
     * Cash On Delivery
     */
    public function cashOnDelivery()
    {
        if ($this->paymentSettings->cash_on_delivery_enabled != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("cash_on_delivery"));
        $earningsModel = new EarningsModel();
        $data['numRows'] = $earningsModel->getCodEarningsCount($this->userId);
        $data['pager'] = paginate($this->baseVars->perPage, $data['numRows']);
        $data['earnings'] = $earningsModel->getCodEarningsPaginated($this->userId, $this->baseVars->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/cash_on_delivery', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Cash On Delivery Settings Post
     */
    public function cashOnDeliverySettingsPost()
    {
        $profileModel = new ProfileModel();
        if ($profileModel->updateCashOnDelivery()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(generateDashUrl('cash_on_delivery'));
    }


    /*
     * --------------------------------------------------------------------
     * Coupons
     * --------------------------------------------------------------------
     */

    /**
     * Coupons
     */
    public function coupons()
    {
        $data = $this->setMetaData(trans("coupons"));
        $data['numRows'] = $this->couponModel->getVendorCouponsCount($this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['coupons'] = $this->couponModel->getVendorCouponsPaginated($this->userId, $this->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/coupon/coupons', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Add Coupon
     */
    public function addCoupon()
    {
        $data = $this->setMetaData(trans("add_coupon"));
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/coupon/add_coupon', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Add Coupon Post
     */
    public function addCouponPost()
    {
        $val = \Config\Services::validation();
        $val->setRule('coupon_code', trans("coupon_code"), 'required|max_length[49]');
        $val->setRule('discount_rate', trans("discount_rate"), 'required');
        $val->setRule('coupon_count', trans("number_of_coupons"), 'required');
        $val->setRule('expiry_date', trans("expiry_date"), 'required');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $code = inputPost('coupon_code');
            if (!empty($this->couponModel->getCouponByCode($code))) {
                setErrorMessage(trans("msg_coupon_code_added_before"));
                $this->session->setFlashdata('selectedProductsIds', $this->couponModel->getSelectedProductsArray());
                return redirect()->back()->withInput();
            }
            if ($this->couponModel->addCoupon()) {
                setSuccessMessage(trans("msg_added"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /**
     * Edit Coupon
     */
    public function editCoupon($id)
    {
        $data = $this->setMetaData(trans("edit_coupon"));
        $data['coupon'] = $this->couponModel->getCoupon($id);
        if (empty($data['coupon'])) {
            return redirect()->to(generateDashUrl('coupons'));
        }
        if ($data['coupon']->seller_id != $this->userId) {
            return redirect()->to(generateDashUrl('coupons'));
        }

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/coupon/edit_coupon', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Edit Coupon Post
     */
    public function editCouponPost()
    {
        $val = \Config\Services::validation();
        $val->setRule('coupon_code', trans("coupon_code"), 'required|max_length[49]');
        $val->setRule('discount_rate', trans("discount_rate"), 'required');
        $val->setRule('coupon_count', trans("number_of_coupons"), 'required');
        $val->setRule('expiry_date', trans("expiry_date"), 'required');
        if (!$this->validate(getValRules($val))) {
            $this->session->setFlashdata('errors', $val->getErrors());
            return redirect()->back()->withInput();
        } else {
            $couponId = inputPost('id');
            $coupon = $this->couponModel->getCoupon($couponId);
            if (empty($coupon) || ($coupon->seller_id != $this->userId)) {
                return redirect()->to(generateDashUrl('coupons'));
            }
            $code = inputPost('coupon_code');
            $couponByCode = $this->couponModel->getCouponByCode($code);
            if (!empty($couponByCode) && $couponByCode->id != $coupon->id) {
                setErrorMessage(trans("msg_coupon_code_added_before"));
                redirectToBackUrl();
            }
            if ($this->couponModel->editCoupon($couponId)) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        redirectToBackUrl();
    }

    /**
     * Delete Coupon Post
     */
    public function deleteCouponPost()
    {
        $id = inputPost('id');
        $coupon = $this->couponModel->getCoupon($id);
        if (empty($coupon)) {
            exit();
        }
        if ($coupon->seller_id != $this->userId) {
            exit();
        }
        if ($this->couponModel->deleteCoupon($coupon)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        exit();
    }

    /**
     * Coupon Products
     */
    public function couponProducts($id)
    {
        $coupon = $this->couponModel->getCoupon($id);
        if (empty($coupon) || $coupon->seller_id != $this->userId) {
            return redirect()->to(generateDashUrl('coupons'));
        }
        $data = $this->setMetaData(trans("coupon") . " " . trans("select_products"));
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['sellerCategories'] = $this->categoryModel->getSellerCategoriesResultArray($this->userId);
        if (!empty($data['sellerCategories'])) {
            usort($data['sellerCategories'], function ($a, $b) {
                return strcmp($a['slug'], $b['slug']);
            });
        }
        $this->perPage = 30;
        $data['numRows'] = $this->productModel->getSellerProductsCouponCount($this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['products'] = [];
        if ($data['numRows'] > 0) {
            $data['products'] = $this->productModel->getSellerProductsCouponPaginated($this->userId, $coupon->id, $this->perPage, $data['pager']->offset);
        }
        $data['coupon'] = $coupon;

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/coupon/coupon_products', $data);
        echo view('dashboard/includes/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Refund
     * --------------------------------------------------------------------
     */

    /**
     * Refund Requests
     */
    public function refundRequests()
    {
        if ($this->generalSettings->refund_system != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("refund_requests"));
        $data['numRows'] = $this->orderModel->getRefundRequestCount($this->userId, 'seller');
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['refundRequests'] = $this->orderModel->getRefundRequestsPaginated($this->userId, 'seller', $this->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/refund/refund_requests', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Refund
     */
    public function refund($id)
    {
        if ($this->generalSettings->refund_system != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("refund"));
        $data['refundRequest'] = $this->orderModel->getRefundRequest($id);
        if (empty($data['refundRequest']) || $data['refundRequest']->seller_id != $this->userId) {
            return redirect()->to(generateDashUrl('refund_requests'));
        }
        $data['product'] = getOrderProduct($data['refundRequest']->order_product_id);
        if (empty($data['product'])) {
            return redirect()->to(generateDashUrl('refund_requests'));
        }
        $data['messages'] = $this->orderModel->getRefundMessages($id);

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/refund/refund', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Approve or Decline Refund Request
     */
    public function approveDeclineRefund()
    {
        if ($this->orderModel->approveDeclineRefund()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Payment History
     * --------------------------------------------------------------------
     */

    /**
     * Payments
     */
    public function payments()
    {
        $payment = inputGet('payment');
        if ($payment == 'membership') {
            if ($this->generalSettings->membership_plans_system != 1) {
                return redirect()->to(dashboardUrl());
            }
            $data = $this->setMetaData(trans("membership_payments"));
            $data['numRows'] = $this->membershipModel->getMembershipTransactionsCount($this->userId);
            $data['pager'] = paginate($this->perPage, $data['numRows']);
            $data['transactions'] = $this->membershipModel->getMembershipTransactionsPaginated($this->perPage, $data['pager']->offset, $this->userId);
            echo view('dashboard/includes/_header', $data);
            echo view('dashboard/payments/membership_payments', $data);
            echo view('dashboard/includes/_footer');
        } elseif ($payment == 'promotion') {
            $data = $this->setMetaData(trans("promotion_payments"));
            $promoteModel = new PromoteModel();
            $data['numRows'] = $promoteModel->getTransactionsCount($this->userId);
            $data['pager'] = paginate($this->perPage, $data['numRows']);
            $data['transactions'] = $promoteModel->getTransactionsPaginated($this->userId, $this->perPage, $data['pager']->offset);
            echo view('dashboard/includes/_header', $data);
            echo view('dashboard/payments/promotion_payments', $data);
            echo view('dashboard/includes/_footer');
        } else {
            return redirect()->to(dashboardUrl());
        }
    }

    /**
     * Affiliate Program
     */
    public function affiliateProgram()
    {
        if ($this->affiliateSettings->status != 1 || $this->affiliateSettings->type != 'seller_based') {
            return redirect()->to(dashboardUrl());
        }

        $data['title'] = trans("affiliate_program");
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['categories'] = $this->categoryModel->getSellerCategories($this->userId, null);
        $data['categoryIds'] = array();
        if (!empty($data['categories']) && !empty($data['categories'][0])) {
            foreach ($data['categories'] as $item) {
                array_push($data['categoryIds'], $item->id);
            }
        }
        $data['selectedCategories'] = explode(',', '1,2,3');
        if (empty($data['selectedCategories'])) {
            $data['selectedCategories'] = array();
        }

        $data['selectedProducts'] = array();
        if (!empty($selectedProducts)) {
            foreach ($selectedProducts as $item) {
                array_push($data['selectedProducts'], $item->product_id);
            }
        }

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/affiliate_program', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Affiliate Program Post
     */
    public function affiliateProgramPost()
    {
        if ($this->affiliateSettings->status != 1 || $this->affiliateSettings->type != 'seller_based') {
            return redirect()->to(dashboardUrl());
        }

        if ($this->authModel->updateAffiliateSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Add Remove Affiliate Product
     */
    public function addRemoveAffiliateProductPost()
    {
        $productId = inputPost('product_id');
        if ($this->productAdminModel->addRemoveAffiliateProduct($productId)) {
            setSuccessMessage(trans("msg_updated"));
            resetCacheDataOnChange();
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /*
     * --------------------------------------------------------------------
     * Comments & Reviews
     * --------------------------------------------------------------------
     */

    /**
     * Comments
     */
    public function comments()
    {
        if ($this->generalSettings->product_comments != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("comments"));
        $data['numRows'] = $this->commonModel->getVendorCommentsCount($this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['comments'] = $this->commonModel->getVendorCommentsPaginated($this->userId, $this->perPage, $data['pager']->offset);
        $data['panelSettings'] = getPanelSettings();
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/comments', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Reviews
     */
    public function reviews()
    {
        if ($this->generalSettings->reviews != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("reviews"));
        $data['numRows'] = $this->commonModel->getUserReviewsCount($this->userId);
        $data['pager'] = paginate($this->perPage, $data['numRows']);
        $data['reviews'] = $this->commonModel->getUserReviewsPaginated($this->userId, $this->perPage, $data['pager']->offset);

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/reviews', $data);
        echo view('dashboard/includes/_footer');
    }

    /*
     * --------------------------------------------------------------------
     * Shop Settings
     * --------------------------------------------------------------------
     */

    /**
     * Shop Settings
     */
    public function shopSettings()
    {
        $data = $this->setMetaData(trans("shop_settings"));
        $data['userPlan'] = $this->membershipModel->getUserPlanByUserId($this->userId);
        $data['daysLeft'] = $this->membershipModel->getUserPlanRemainingDaysCount($data['userPlan']);
        $data['adsLeft'] = $this->membershipModel->getUserPlanRemainingAdsCount($data['userPlan']);
        $data['states'] = array();
        $data['cities'] = array();
        $data['panelSettings'] = getPanelSettings();
        $locationModel = new LocationModel();
        if (!empty(user()->country_id)) {
            $data['states'] = $locationModel->getStatesByCountry(user()->country_id);
        }
        if (!empty(user()->state_id)) {
            $data['cities'] = $locationModel->getCitiesByState(user()->state_id);
        }

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/shop_settings', $data);
        echo view('dashboard/includes/_footer');

    }

    /**
     * Shop Settings Post
     */
    public function shopSettingsPost()
    {
        $submit = inputPost('submit');
        $profileModel = new ProfileModel();
        if ($submit == 'vat') {
            if ($profileModel->updateVendorVatRates()) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        } else {
            $shopName = '';
            if ($submit == 'update') {
                $shopName = removeSpecialCharacters(inputPost('shop_name'));
                if (!$this->authModel->isUniqueUsername($shopName, $this->userId)) {
                    setErrorMessage(trans("msg_shop_name_unique_error"));
                    return redirect()->to(generateDashUrl('shop_settings'));
                }
            }
            if ($profileModel->updateShopSettings($shopName)) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
        return redirect()->to(generateDashUrl('shop_settings'));
    }

    /**
     * Shop Policies
     */
    public function shopPolicies()
    {
        $data = $this->setMetaData(trans("shop_policies"));
        $pageModel = new PageModel();
        $data['pages'] = $pageModel->getVendorPagesByUserId($this->userId);
        if (empty($data['pages'])) {
            $pageModel->addVendorPages($this->userId);
            $data['pages'] = $pageModel->getVendorPagesByUserId($this->userId);
        }
        if (empty($data['pages'])) {
            return redirect()->to(dashboardUrl());
        }
        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/shop_policies', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Shop Policies Post
     */
    public function shopPoliciesPost()
    {
        $pageModel = new PageModel();
        if ($pageModel->editVendorPages($this->userId)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(generateDashUrl('shop_policies'));
    }

    /*
     * --------------------------------------------------------------------
     * Shipping Settings
     * --------------------------------------------------------------------
     */

    /**
     * Shipping Settings
     */
    public function shippingSettings()
    {
        if (!$this->baseVars->isSaleActive || $this->generalSettings->physical_products_system != 1) {
            return redirect()->to(dashboardUrl());
        }
        $data = $this->setMetaData(trans("shipping_settings"));
        $data['shippingZones'] = $this->shippingModel->getShippingZones($this->userId);
        $data['shippingDeliveryTimes'] = $this->shippingModel->getShippingDeliveryTimes($this->userId, 'DESC');
        $data['panelSettings'] = getPanelSettings();

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/shipping/shipping_settings', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Add Shipping Zone
     */
    public function addShippingZone()
    {
        $data = $this->setMetaData(trans("add_shipping_zone"));
        $data['continents'] = getAppDefault('continents');
        $data['countries'] = $this->locationModel->getCountries();

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/shipping/add_shipping_zone', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Add Shipping Zone Post
     */
    public function addShippingZonePost()
    {
        $zoneId = $this->shippingModel->addShippingZone();
        if (!empty($zoneId)) {
            setSuccessMessage(trans("msg_added"));
            return redirect()->to(generateDashUrl('edit_shipping_zone') . '/' . $zoneId);
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Shipping Zone
     */
    public function editShippingZone($id)
    {
        $data = $this->setMetaData(trans("edit_shipping_zone"));
        $data['shippingZone'] = $this->shippingModel->getShippingZone($id);
        if (empty($data['shippingZone']) || user()->id != $data['shippingZone']->user_id) {
            return redirect()->to(generateDashUrl('shipping_settings'));
        }

        $data['continents'] = getAppDefault('continents');
        $data['countries'] = $this->locationModel->getCountries();
        $data['methods'] = $this->shippingModel->getShippingMethodsByZone($data['shippingZone']->id);

        echo view('dashboard/includes/_header', $data);
        echo view('dashboard/shipping/edit_shipping_zone', $data);
        echo view('dashboard/includes/_footer');
    }

    /**
     * Edit Shipping Zone Post
     */
    public function editShippingZonePost()
    {
        $zoneId = inputPost('zone_id');

        $shippingZone = $this->shippingModel->getShippingZone($zoneId);
        if (empty($shippingZone) || user()->id != $shippingZone->user_id) {
            return redirect()->to(generateDashUrl('shipping_settings'));
        }

        if ($this->shippingModel->editShippingZone($zoneId)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Add Shipping Method Post
     */
    public function addShippingMethodPost()
    {
        $zoneId = inputPost('zone_id');
        $shippingMethod = inputPost('shipping_method');
        $shippingZone = $this->shippingModel->getShippingZone($zoneId);
        if (empty($shippingZone) || user()->id != $shippingZone->user_id) {
            redirectToBackUrl();
        }

        if ($this->shippingModel->addShippingMethod($zoneId, $shippingMethod)) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Shipping Method Post
     */
    public function editShippingMethodPost()
    {
        $methodId = inputPost('method_id');

        if ($this->shippingModel->editShippingMethod($methodId)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Shipping Location
     */
    public function deleteShippingLocationPost()
    {
        $id = inputPost('id');
        $this->shippingModel->deleteShippingLocation($id);
        return jsonResponse();
    }

    //select shipping method
    public function selectShippingMethod()
    {
        $selectedOption = inputPost('selected_option');
        $vars = ['selectedOption' => $selectedOption, 'optionUniqueId' => uniqid()];
        $htmlContent = view('dashboard/shipping/_response_shipping_method', $vars);
        $data = [
            'result' => 1,
            'htmlContent' => $htmlContent
        ];
        return jsonResponse($data);
    }

    /**
     * Add Shipping Delivery Time Post
     */
    public function addShippingDeliveryTimePost()
    {
        if ($this->shippingModel->addShippingDeliveryTime()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Shipping Delivery Time Post
     */
    public function editShippingDeliveryTimePost()
    {
        $id = inputPost('id');
        if ($this->shippingModel->editShippingDeliveryTime($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Shipping Method
     */
    public function deleteShippingMethodPost()
    {
        $id = inputPost('id');
        $this->shippingModel->deleteShippingMethod($id);
        return jsonResponse();
    }

    /**
     * Delete Shipping Delivery Time
     */
    public function deleteShippingDeliveryTimePost()
    {
        $id = inputPost('id');
        if ($this->shippingModel->deleteShippingDeliveryTime($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Delete Shipping Zone
     */
    public function deleteShippingZonePost()
    {
        $id = inputPost('id');
        $this->shippingModel->deleteShippingZone($id);
    }

    //set meta data
    private function setMetaData($title)
    {
        return [
            'title' => $title,
            'description' => $title . ' - ' . $this->baseVars->appName,
            'keywords' => $title . ',' . $this->baseVars->appName,
        ];
    }

    /**
     * Export Table Data Post
     */
    public function exportTableDataPost()
    {
        $langId = inputPost('lang_id');
        $language = getLanguage($langId);
        $isRTL = false;
        if (!empty($language)) {
            setContextValue('activeLang', $language);
            if ($language->text_direction == 'rtl') {
                $isRTL = true;
            }
        }
        $dataExportType = inputPost('data_export_type');
        $dataExportFileType = inputPost('data_export_file_type');
        if ($dataExportFileType != 'excel' && $dataExportFileType != 'csv' && $dataExportFileType != 'xml') {
            $dataExportFileType = 'excel';
        }
        $fileName = '';
        $fields = array();
        $rows = array();
        if ($dataExportType == 'vendor_products') {
            $list = inputPost('st');
            $model = new ProductModel();
            $fileName = 'Products';
            $fields = ['id', 'title', 'slug', 'sku', 'listing_type', 'product_type', 'category_id', 'category_name'];
            if (!empty($this->generalSettings->promoted_products)) {
                array_push($fields, 'is_promoted');
                array_push($fields, 'promote_start_date');
                array_push($fields, 'promote_end_date');
                array_push($fields, 'purchased_plan');
            }
            $fields = array_merge($fields, ['is_special_offer', 'stock_status', 'price', 'price_discounted', 'currency', 'discount_rate', 'vat_rate', 'pageviews', 'demo_url', 'external_link', 'rating', 'is_free_product', 'visibility', 'status', 'location', 'short_description', 'description', 'tags', 'images', 'date']);
            $rows = $model->getVendorProductsExport($this->userId, $list);
        } elseif ($dataExportType == 'vendor_sales') {
            $list = inputPost('st');
            $status = 'active';
            if ($list == 'completed') {
                $list = 'completed_sales';
                $status = 'completed';
            } elseif ($list == 'cancelled') {
                $list = 'cancelled_sales';
                $status = 'cancelled';
            } else {
                $list = 'sales';
            }
            $model = new OrderModel();
            $fileName = trans($list);
            $fields = ['sale', 'total', 'currency', 'payment_status', 'status', 'date'];
            $rows = $model->getSalesExport($status, $this->userId);
        }

        $export = new Export($fields, $rows, $fileName, $dataExportType, $isRTL);
        if ($dataExportFileType == 'excel') {
            $export->exportAsExcel();
        } elseif ($dataExportFileType == 'csv') {
            $export->exportAsCsv();
        } elseif ($dataExportFileType == 'xml') {
            $export->exportAsXml();
        }
        redirectToBackUrl();
    }
}
