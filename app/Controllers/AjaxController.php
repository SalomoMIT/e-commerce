<?php

namespace App\Controllers;

use App\Models\BlogModel;
use App\Models\ChatModel;
use App\Models\CommonModel;
use App\Models\CouponModel;
use App\Models\EmailModel;
use App\Models\FieldModel;
use App\Models\LocationModel;
use App\Models\NewsletterModel;
use App\Models\ShippingModel;
use App\Models\TagModel;

class AjaxController extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        if (!$this->request->isAJAX()) {
            exit();
        }
    }

    /**
     * Load Products
     */
    public function loadProducts()
    {
        $userId = inputGet('user_id');
        $couponId = inputGet('coupon_id');
        $arrayParams = inputGet('params');
        $categoryId = inputGet('category_id');
        $sysLangId = inputGet('sysLangId');
        $page = 1;
        if (!empty($arrayParams)) {
            if (!empty($arrayParams['page'])) {
                $page = getValidPageNumber($arrayParams['page']);
            }
        }
        $category = null;
        $customFilters = null;
        if (!empty($categoryId)) {
            $fieldModel = new FieldModel();
            $category = $this->categoryModel->getCategory($categoryId);
            $parentCategoriesTree = $this->categoryModel->getCategoryParentTree($categoryId);
            $customFilters = $fieldModel->getCustomFilters($categoryId, $sysLangId, $parentCategoriesTree);
        }

        $objParams = new \stdClass();
        $objParams->pageNumber = $page;
        $objParams->category = $category;
        $objParams->userId = $userId;
        $objParams->customFilters = $customFilters;
        $objParams->arrayParams = $arrayParams;
        $objParams->couponId = $couponId;
        $objParams->langId = $sysLangId;
        $products = $this->productModel->loadProducts($objParams);
        $dataJson = [
            'result' => 0,
            'htmlContent' => '',
            'hasMore' => false
        ];
        $htmlContent = '';
        if (!empty($products)) {
            $i = 0;
            foreach ($products as $product) {
                if ($i < $this->productSettings->pagination_per_page) {
                    $vars = [
                        'product' => $product,
                        'promoted_badge' => true
                    ];
                    $htmlContent .= '<div class="col-6 col-sm-4 col-md-4 col-lg-3 col-product">' . view('product/_product_item', $vars) . '</div>';
                }
                $i++;
            }
            $dataJson = [
                'result' => 1,
                'htmlContent' => $htmlContent,
                'hasMore' => countItems($products) > $this->productSettings->pagination_per_page ? true : false,
                'pageNumber' => $page
            ];
        }

        return jsonResponse($dataJson);
    }

    /**
     * Load More Promoted Products
     */
    public function loadMorePromotedProducts()
    {
        $perPage = $this->generalSettings->index_promoted_products_count;
        $page = clrNum(inputPost('page'));
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;
        $promotedProducts = $this->productModel->getPromotedProductsLimited($this->activeLang->id, $perPage, $offset);
        $htmlContent = '';
        if (!empty($promotedProducts)) {
            $i = 0;
            foreach ($promotedProducts as $product) {
                if ($i < $perPage) {
                    $vars = [
                        'product' => $product,
                        'promoted_badge' => false
                    ];
                    $rowClass = $this->generalSettings->index_products_per_row == 5 ? 'col-product-5' : 'col-product-6';
                    $htmlContent .= '<div class="col-6 col-sm-4 col-md-3 col-product ' . $rowClass . '">' . view('product/_product_item', $vars) . '</div>';
                }
                $i++;
            }
        }
        $dataJson = [
            'result' => 1,
            'htmlContent' => $htmlContent,
            'hasMore' => countItems($promotedProducts) > $perPage ? true : false
        ];

        return jsonResponse($dataJson);
    }

    /**
     * Load More Filter Options
     */
    public function loadFilterOptions()
    {
        $offset = (int)$this->request->getPost('offset');
        $limit = (int)$this->request->getPost('limit');
        $currentUrl = $this->request->getPost('current_url', FILTER_SANITIZE_URL);
        $filterId = $this->request->getPost('filter_id');
        $searchTerm = $this->request->getPost('search_term', FILTER_SANITIZE_STRING);
        $langId = (int)$this->request->getPost('sysLangId');
        $categoryId = (int)$this->request->getPost('category_id');

        $queryParams = [];
        $queryString = parse_url($currentUrl, PHP_URL_QUERY);
        if (!empty($queryString)) {
            parse_str($queryString, $queryParams);
        }

        $options = [];
        $hasMore = false;
        $rawOptions = [];
        $filterKey = '';

        if ($filterId == 'brand') {
            $filterKey = 'brand';
            $commonModel = new CommonModel();
            $result = $commonModel->getBrands($langId, $categoryId, $searchTerm, $limit, $offset);
            $options = $result['brands'];
            $hasMore = $result['hasMore'];

            foreach ($options as $option) {
                $rawOptions[] = (object)[
                    'name' => $option->brand_name,
                    'value_for_url' => $option->id,
                ];
            }
        } else {
            $fieldModel = new FieldModel();
            $field = $fieldModel->getField($filterId);
            if (!empty($field)) {
                $filterKey = $field->product_filter_key;
            }

            $result = $fieldModel->loadFilterOptions($field, $langId, $searchTerm, $limit, $offset);
            $options = $result['options'];
            $hasMore = $result['hasMore'];

            foreach ($options as $option) {
                $rawOptions[] = (object)[
                    'name' => $option->name,
                    'value_for_url' => $option->option_key,
                ];
            }
        }

        $results = [];
        if (!empty($rawOptions) && !empty($filterKey)) {
            foreach ($rawOptions as $option) {
                $optionValue = $option->value_for_url;
                $isChecked = false;

                if (isset($queryParams[$filterKey])) {
                    $selectedValues = $queryParams[$filterKey];

                    if (is_string($selectedValues)) {
                        $selectedValues = explode(',', $selectedValues);
                    }

                    $selectedValues = (array)$selectedValues;
                    $selectedValues = array_map('trim', $selectedValues);
                    if (in_array((string)$optionValue, $selectedValues, true)) {
                        $isChecked = true;
                    }
                }

                $results[] = [
                    'name' => esc($option->name),
                    'filter_key' => esc($filterKey),
                    'option_key' => esc($optionValue),
                    'isChecked' => $isChecked,
                ];
            }
        }

        return jsonResponse([
            'status' => 1,
            'options' => $results,
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * Create Affiliate Link
     */
    public function createAffiliateLink()
    {
        $productId = inputPost('product_id');
        $langId = inputPost('lang_id');
        $data = [
            'status' => 0,
            'response' => ''
        ];
        $product = getProduct($productId);
        if (!empty($product)) {
            $vendor = getUser($product->user_id);
            if (!empty($vendor) && isActiveAffiliateProduct($product, $vendor)) {
                $this->commonModel->createAffiliateLink(user()->id, $productId, $langId);
                $url = $this->commonModel->getAffiliateLink(user()->id, $productId, $langId);
                if (!empty($url)) {
                    $data = [
                        'status' => 1,
                        'response' => generateUrl('affiliate') . '/' . $url->link_short
                    ];
                }
            }
        }
        return jsonResponse($data);
    }

    /**
     * Select Coupon Category
     */
    public function selectCouponCategoryPost()
    {
        $couponId = inputPost('coupon_id');
        $categoryId = inputPost('category_id');
        $action = inputPost('action');
        if (!authCheck()) {
            return jsonResponse();
        }
        $couponModel = new CouponModel();
        $coupon = $couponModel->getCoupon($couponId);
        if (empty($coupon) || $coupon->seller_id != user()->id) {
            return jsonResponse();
        }
        $couponModel->setCouponCategories($coupon, $categoryId, $action);
        return jsonResponse();
    }

    /**
     * Select Coupon Product
     */
    public function selectCouponProductPost()
    {
        $couponId = inputPost('coupon_id');
        $productId = inputPost('product_id');
        $action = inputPost('action');
        if (!authCheck()) {
            return jsonResponse();
        }
        $couponModel = new CouponModel();
        $coupon = $couponModel->getCoupon($couponId);
        $product = getProduct($productId);
        if (empty($coupon) || empty($product) || $coupon->seller_id != user()->id || $product->user_id != user()->id) {
            return jsonResponse();
        }
        $couponModel->addRemoveCouponProduct($coupon, $product, $action);
        return jsonResponse();
    }

    /**
     * AI Writer
     */
    public function generateTextAI()
    {
        hasPermission("ai_writer");

        // Get language code
        $langId = inputPost('sysLangId');
        $lang = getLanguage($langId);
        $langName = (!empty($lang)) ? $lang->name : 'English';

        $options = (object)[
            'model' => inputPost('model'),
            'temperature' => inputPost('temperature'),
            'tone' => inputPost('tone'),
            'length' => inputPost('length'),
            'topic' => inputPost('topic'),
            'contentType' => inputPost('content_type'),
            'langName' => $langName,
        ];

        $data = \Config\AIWriter::generateText($options);
        return jsonResponse($data);
    }

    /*
     * --------------------------------------------------------------------
     * Location
     * --------------------------------------------------------------------
     */

    //load active countries
    public function loadActiveCountries()
    {
        $countryId = inputPost('country_id');
        $countries = $this->locationModel->getActiveCountries();
        $htmlContent = '<option value="">' . trans('country') . '</option>';
        if (!empty($countries)) {
            foreach ($countries as $item) {
                $htmlContent .= '<option value="' . $item->id . '" ' . ($item->id == $countryId ? "selected" : "") . '>' . esc($item->name) . '</option>';
            }
        }

        return jsonResponse([
            'status' => 1,
            'htmlContent' => $htmlContent
        ]);
    }

    //get states
    public function getStates()
    {
        $countryId = inputPost('country_id');
        $states = $this->locationModel->getStatesByCountry($countryId);
        $status = 0;
        $content = '<option value="">' . trans('state') . '</option>';
        if (!empty($states)) {
            $status = 1;
            foreach ($states as $item) {
                $content .= '<option value="' . $item->id . '">' . esc($item->name) . '</option>';
            }
        }
        $data = [
            'result' => $status,
            'content' => $content
        ];
        return jsonResponse($data);
    }

    //get cities
    public function getCities()
    {
        $stateId = inputPost('state_id');
        $cities = $this->locationModel->getCitiesByState($stateId);
        $status = 0;
        $content = '<option value="">' . trans("city") . '</option>';
        if (!empty($cities)) {
            $status = 1;
            foreach ($cities as $item) {
                $content .= '<option value="' . $item->id . '">' . esc($item->name) . '</option>';
            }
        }
        $data = [
            'result' => $status,
            'content' => $content
        ];
        return jsonResponse($data);
    }

    //get countries by continent
    public function getCountriesByContinent()
    {
        $key = inputPost('key');
        $data = ['result' => 0];
        $model = new LocationModel();
        $countries = $model->getCountriesByContinent($key);
        $options = '';
        if (!empty($countries)) {
            foreach ($countries as $country) {
                $options .= "<option value='" . $country->id . "'>" . esc($country->name) . "</option>";
            }
        }
        if (!empty($options)) {
            $data = ['result' => 1, 'options' => $options];
        }
        return jsonResponse($data);
    }

    //get states by country
    public function getStatesByCountry()
    {
        $countryId = inputPost('country_id');
        $data = ['result' => 0];
        $model = new LocationModel();
        $states = $model->getStatesByCountry($countryId);
        $options = '';
        if (!empty($states)) {
            foreach ($states as $state) {
                $options .= "<option value='" . $state->id . "'>" . esc($state->name) . "</option>";
            }
        }
        if (!empty($options)) {
            $data = ['result' => 1, 'options' => $options];
        }
        return jsonResponse($data);
    }

    //get product shipping cost
    public function getProductShippingCost()
    {
        $stateId = inputPost('state_id');
        $productId = inputPost('product_id');
        $shippingModel = new ShippingModel();
        return $shippingModel->getProductShippingCost($stateId, $productId);
    }

    /*
     * --------------------------------------------------------------------
     * Search
     * --------------------------------------------------------------------
     */

    //ajax search
    public function ajaxSearch()
    {
        $searchTerm = inputPost('input_value');
        $langId = inputPost('sysLangId');

        $htmlResponse = '';
        $totalCount = 0;

        if (!empty($searchTerm) && mb_strlen($searchTerm, 'UTF-8') > 2) {
            $suggestions = $this->commonModel->getSearchSuggestions($searchTerm, (int)$langId);
            $totalCount = count($suggestions['tags']) + count($suggestions['categories']) + count($suggestions['brands']) + count($suggestions['shops']);

            if ($totalCount > 0) {
                $viewData = [
                    'suggestions' => $suggestions
                ];
                $htmlResponse = view('partials/_ajax_search_results', $viewData);
            }
        }

        return jsonResponse([
            'status' => 1,
            'htmlContent' => $htmlResponse,
            'count' => $totalCount
        ]);
    }

    //get subcategories
    public function getSubCategories()
    {
        $parentId = inputPost('parent_id');
        $langId = inputPost('lang_id');
        $showIds = inputPost('show_ids');
        $htmlContent = '';
        if (!empty($parentId)) {
            $subCategories = $this->categoryModel->getSubCategoriesByParentId($parentId);
            foreach ($subCategories as $item) {
                if (!empty($showIds)) {
                    $htmlContent .= "<option value='" . $item->id . "'>" . esc($item->cat_name) . " (ID: " . $item->id . ")</option>";
                } else {
                    $htmlContent .= "<option value = '" . $item->id . "'> " . esc($item->cat_name) . "</option>";
                }
            }
        }
        $data = [
            'result' => 1,
            'htmlContent' => $htmlContent,
        ];
        return jsonResponse($data);
    }

    /**
     * Get Product Tag Suggestions
     */
    public function getProductTagSuggestions()
    {
        if (!isVendor() && !hasPermission('products')) {
            return false;
        }
        $data = ['result' => 0];
        $q = inputPost('searchTerm');
        $langId = inputPost('lang_id');
        $tagModel = new TagModel();
        $tags = $tagModel->getTagSuggestions($q, $langId);
        if (!empty($tags)) {
            $data = [
                'result' => 1,
                'tags' => $tags
            ];
        }
        return $this->response->setHeader('X-CSRF-TOKEN', csrf_hash())->setJSON($data);
    }

    /*
     * --------------------------------------------------------------------
     * Wishlist
     * --------------------------------------------------------------------
     */

    //add or remove wishlist
    public function addRemoveWishlist()
    {
        $productId = inputPost('product_id');
        $this->productModel->addRemoveWishlist($productId);
        return jsonResponse();
    }

    /*
     * --------------------------------------------------------------------
     * Product Comment
     * --------------------------------------------------------------------
     */

    //add comment
    public function addComment()
    {
        if ($this->generalSettings->product_comments != 1) {
            exit();
        }
        if (!empty(inputPost('comment_name'))) {
            exit();
        }

        //bot verification
        if (!authCheck() && !verifyTurnstile()) {
            return jsonResponse([
                'status' => 0,
                'type' => 'message',
                'message' => trans("msg_bot_verification_failed")
            ]);
        }

        $productId = inputPost('product_id');
        $limit = inputPost('limit');
        $product = getProduct($productId);
        if (!empty($product)) {

            $this->commonModel->addComment();

            if ($this->generalSettings->comment_approval_system == 1 && !hasPermission('comments')) {
                $data = [
                    'status' => 1,
                    'type' => 'message',
                    'message' => trans("msg_comment_sent_successfully")
                ];
                return jsonResponse($data);
            } else {
                $commentsArray = $this->commonModel->getProductCommentsByOffset($productId, $limit, 0);
                $parentComments = [];
                if (!empty($commentsArray) && !empty($commentsArray[0]) && countItems($commentsArray[0]) > 0) {
                    $parentComments = $commentsArray[0];
                }
                $data = [
                    'status' => 1,
                    'type' => 'comments',
                    'htmlContent' => view('product/details/_comments_list', ['product' => $product, 'comments' => $parentComments, 'commentsArray' => $commentsArray])
                ];
                return jsonResponse($data);
            }
        }
        return jsonResponse();
    }

    //add review
    public function addReviewPost()
    {
        $canProceed = true;
        if ($this->generalSettings->reviews != 1 || !authCheck()) {
            $canProceed = false;
        }

        $rating = inputPost('rating');
        $productId = inputPost('product_id');
        $reviewText = inputPost('review');
        $userId = user()->id;

        $product = $this->productModel->getProduct($productId);
        if (empty($product)) {
            $canProceed = false;
        }

        $isAuthorizedToReview = false;
        if ($canProceed) {
            if ($product->user_id != $userId) {
                if ($product->listing_type == 'ordinary_listing' || $product->is_free_product) {
                    $isAuthorizedToReview = true;
                } else {
                    if (checkUserBoughtProduct($userId, $product->id)) {
                        $isAuthorizedToReview = true;
                    }
                }
            }
        }

        if (!$canProceed || !$isAuthorizedToReview) {
            $canProceed = false;
        }

        if ($canProceed) {
            try {
                $existingReview = $this->commonModel->getReview($productId, $userId);
                if (!empty($existingReview)) {
                    $this->commonModel->updateReview($existingReview->id, $rating, $productId, $reviewText);
                } else {
                    $this->commonModel->addReview($rating, $productId, $reviewText);
                }
            } catch (\Exception $e) {
                $canProceed = false;
            }
        }

        if ($canProceed) {
            return jsonResponse([
                'status' => 1,
                'message' => "<span class='text-success'>" . trans("msg_review_added") . "</span>"
            ]);
        } else {
            return jsonResponse([
                'status' => 0,
                'message' => "<span class='text-danger'>" . trans("msg_error") . "</span>"
            ]);
        }
    }

    //load more reviews
    public function loadMoreReviews()
    {
        $productId = inputPost('product_id');
        $offset = inputPost('offset');
        $product = getProduct($productId);
        $data = ['status' => 0];
        if (!empty($product)) {
            $reviews = $this->commonModel->getProductReviewsByOffset($productId, REVIEWS_LOAD_LIMIT, $offset);
            $data = [
                'status' => 1,
                'htmlContent' => view('product/details/_reviews_list', ['product' => $product, 'reviews' => $reviews])
            ];
        }
        return jsonResponse($data);
    }

    //load more comments
    public function loadMoreComments()
    {
        $productId = inputPost('product_id');
        $offset = inputPost('offset');
        $product = getProduct($productId);
        $data = ['status' => 0];
        if (!empty($product)) {
            $commentsArray = $this->commonModel->getProductCommentsByOffset($productId, COMMENTS_LOAD_LIMIT, $offset);
            $parentComments = [];
            if (!empty($commentsArray) && !empty($commentsArray[0]) && countItems($commentsArray[0]) > 0) {
                $parentComments = $commentsArray[0];
            }
            $data = [
                'status' => 1,
                'htmlContent' => view('product/details/_comments_list', ['product' => $product, 'comments' => $parentComments, 'commentsArray' => $commentsArray])
            ];
        }
        return jsonResponse($data);
    }

    //delete comment
    public function deleteComment()
    {
        $id = inputPost('id');
        $comment = $this->commonModel->getComment($id);
        if (authCheck() && !empty($comment)) {
            if (hasPermission('comments') || user()->id == $comment->user_id) {
                $this->commonModel->deleteComment($id);
            }
        }
        return jsonResponse();
    }

    //delete review
    public function deleteReview()
    {
        if (authCheck()) {
            $id = inputPost('id');
            $review = $this->commonModel->getReviewById($id);
            if (!empty($review) && $review->user_id == user()->id) {
                $this->commonModel->deleteReview($id);
            }
        }
        return jsonResponse();
    }

    //load subcomment form
    public function loadSubCommentForm()
    {
        $commentId = inputPost('comment_id');
        $vars = [
            'parentComment' => $this->commonModel->getComment($commentId)
        ];
        $data = [
            'status' => 1,
            'htmlContent' => view('product/details/_add_subcomment', $vars),
        ];
        return jsonResponse($data);
    }

    /*
     * --------------------------------------------------------------------
     * Blog
     * --------------------------------------------------------------------
     */

    /**
     * Get Blog Categories by Language
     */
    public function getBlogCategoriesByLang()
    {
        $model = new BlogModel();
        $langId = inputPost('lang_id');
        $data = ['result' => 0];
        if (!empty($langId)) {
            $categories = $model->getCategoriesByLang($langId);
            $options = '';
            if (!empty($categories)) {
                foreach ($categories as $item) {
                    $options .= '<option value="' . $item->id . '">' . esc($item->name) . '</option>';
                }
            }
        }
        if (!empty($options)) {
            $data = ['result' => 1, 'options' => $options];
        }
        return jsonResponse($data);
    }

    /**
     * Add Blog Comment
     */
    public function addBlogComment()
    {
        if ($this->generalSettings->blog_comments != 1) {
            exit();
        }

        //bot verification
        if (!authCheck() && !verifyTurnstile()) {
            return jsonResponse([
                'status' => 0,
                'type' => 'message',
                'message' => trans("msg_bot_verification_failed")
            ]);
        }

        $postId = inputPost('post_id');
        $limit = inputPost('limit');
        $blogModel = new BlogModel();
        $blogModel->addComment();

        if ($this->generalSettings->comment_approval_system == 1 && !hasPermission('comments')) {
            $data = [
                'type' => 'message',
                'message' => trans("msg_comment_sent_successfully")
            ];
            return jsonResponse($data);
        } else {
            return $this->generateCommentBlogHtmlContent($blogModel, $postId, $limit);
        }
    }

    /**
     * Delete Blog Comment
     */
    public function deleteBlogComment()
    {
        $commentId = inputPost('comment_id');
        $postId = inputPost('post_id');
        $limit = inputPost('limit');
        $blogModel = new BlogModel();
        $comment = $blogModel->getComment($commentId);
        if (authCheck() && !empty($comment)) {
            if (hasPermission('comments') || user()->id == $comment->user_id) {
                $blogModel->deleteComment($comment->id);
            }
        }
        return $this->generateCommentBlogHtmlContent($blogModel, $postId, $limit);
    }

    /**
     * Load More Comments
     */
    public function loadMoreBlogComments()
    {
        $blogModel = new BlogModel();
        $postId = inputPost('post_id');
        $limit = inputPost('limit');
        $newLimit = $limit + COMMENTS_LOAD_LIMIT;
        return $this->generateCommentBlogHtmlContent($blogModel, $postId, $newLimit);
    }

    //generate blog comment html content
    private function generateCommentBlogHtmlContent($blogModel, $postId, $limit)
    {
        $vars = [
            'comments' => $blogModel->getCommentsByPostId($postId, $limit),
            'commentPostId' => $postId,
            'commentsCount' => $blogModel->getActiveCommentsCountByPostId($postId),
            'commentLimit' => $limit
        ];
        $data = [
            'type' => 'comments',
            'htmlContent' => view('blog/_blog_comments', $vars),
        ];
        return jsonResponse($data);
    }

    /*
     * --------------------------------------------------------------------
     * Abuse Reports
     * --------------------------------------------------------------------
     */

    //report abuse
    public function reportAbusePost()
    {
        if (!authCheck()) {
            return jsonResponse();
        }
        $data = [
            'message' => "<p class='text-danger'>" . trans("msg_error") . "</p>"
        ];
        if ($this->commonModel->reportAbuse()) {
            $data['message'] = "<p class='text-success'>" . trans("abuse_report_msg") . "</p>";
        }
        return jsonResponse($data);
    }


    /*
     * --------------------------------------------------------------------
     * Chat
     * --------------------------------------------------------------------
     */

    /**
     * Add Chat Post
     */
    public function addChatPost()
    {
        if (!authCheck()) {
            return jsonResponse();
        }
        $receiverId = inputPost('receiver_id');
        $data = [
            'result' => 0,
            'senderId' => 0,
            'htmlContent' => ''
        ];
        if (user()->id == $receiverId) {
            setErrorMessage(trans("msg_message_sent_error"));
            $data['result'] = 1;
            $data['htmlContent'] = view('partials/_messages');
            resetFlashData();
        } else {
            $chatModel = new ChatModel();
            $chatId = $chatModel->addChat();
            if ($chatId) {
                $messageId = $chatModel->addMessage($chatId);
                if ($messageId) {
                    setSuccessMessage(trans("msg_message_sent"));
                    $data['result'] = 1;
                    $data['senderId'] = user()->id;
                    $data['htmlContent'] = view('partials/_messages');
                    resetFlashData();
                } else {
                    setErrorMessage(trans("msg_error"));
                    $data['result'] = 1;
                    $data["htmlContent"] = view('partials/_messages');
                    resetFlashData();
                }
            } else {
                setErrorMessage(trans("msg_error"));
                $data['result'] = 1;
                $data['htmlContent'] = view('partials/_messages');
                resetFlashData();
            }
        }
        return jsonResponse($data);
    }

    //send message
    public function sendMessagePost()
    {
        if (!authCheck()) {
            return jsonResponse(['status' => 0, 'reason' => 'auth_required']);
        }

        $jsonData = ['status' => 0];

        $chatModel = new ChatModel();

        $chatId = inputPost('chatId');
        $lastChatMessageId = inputPost('lastChatMessageId');
        $userId = user()->id;

        $chat = $chatModel->getChat($chatId);
        if (!empty($chat) && ($chat->sender_id == $userId || $chat->receiver_id == $userId)) {
            $chatModel->addMessage($chatId);
            $messages = $chatModel->getMessagesArray($userId, $chatId, $lastChatMessageId);

            $jsonData = [
                'status' => 1,
                'chatId' => $chat->id,
                'arrayMessages' => array_slice($messages, 0, 10)
            ];
        }
        return jsonResponse($jsonData);
    }

    //load mesages post
    public function loadChatPost()
    {
        if (!authCheck()) {
            return jsonResponse();
        }
        $jsonData = ['status' => 0];
        $chatId = inputPost('chat_id');
        $userId = user()->id;

        $chatModel = new ChatModel();
        $chat = $chatModel->getChat($chatId);
        $jsonData = [
            'status' => 0
        ];
        if (!empty($chat)) {
            if ($chat->sender_id != $userId && $chat->receiver_id != $userId) {
                exit();
            }

            $chats = $chatModel->getChats($userId);
            $messages = $chatModel->getMessages($chatId);

            //chat receiver
            $receiverId = $chat->sender_id;
            if ($userId == $chat->sender_id) {
                $receiverId = $chat->receiver_id;
            }

            $jsonData = [
                'status' => 1,
                'htmlchatUser' => view('chat/_chat_user', ['chat' => $chat]),
                'htmlContacts' => view('chat/_contacts', ['chat' => $chat, 'chats' => $chats]),
                'htmlContentMessages' => view('chat/_messages', ['chat' => $chat, 'messages' => $messages]),
                'htmlChatForm' => view('chat/_chat_form', ['chat' => $chat]),
                'receiverId' => $receiverId
            ];
            $chatModel->setChatMessagesAsRead($chat->id);
        }

        return jsonResponse($jsonData);
    }

    //delete chat
    public function deleteChatPost()
    {
        if (!authCheck()) {
            return jsonResponse();
        }
        $chatModel = new ChatModel();
        $chatId = inputPost('chat_id');
        $chatModel->deleteChat($chatId);
        return jsonResponse();
    }

    /*
     * --------------------------------------------------------------------
     * Newsletter
     * --------------------------------------------------------------------
     */

    /**
     * Load More Users
     */
    public function loadMoreUsers()
    {
        checkPermission('newsletter');
        $page = clrNum(inputPost('page'));
        $q = inputPost('q');
        $perPage = 500;
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;
        $authModel = new \App\Models\AuthModel();
        $users = $authModel->loadMoreUsers($q, $perPage, $offset);
        $htmlContent = '';
        $hasMore = true;
        if (!empty($users)) {
            foreach ($users as $user) {
                $htmlContent .= '<tr><td><input type="checkbox" name="user_id[]" value="' . $user->id . '"></td><td>' . $user->id . '</td><td>' . esc($user->username) . '</td><td>' . esc($user->email) . '</td></tr>';
            }
        } else {
            $hasMore = false;
            if ($page < 2) {
                $htmlContent .= '<tr><td colspan="5"><p class="text-muted text-center">' . esc(trans("no_results_found")) . '</p></td></tr>';
            }
        }
        $data = [
            'result' => 1,
            'hasMore' => $hasMore,
            'htmlContent' => $htmlContent
        ];
        return jsonResponse($data);
    }

    /**
     * Load More Subscribers
     */
    public function loadMoreSubscribers()
    {
        checkPermission('newsletter');
        $page = clrNum(inputPost('page'));
        $q = inputPost('q');
        $perPage = 500;
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;
        $model = new NewsletterModel();
        $subscribers = $model->loadMoreSubscribers($q, $perPage, $offset);
        $htmlContent = '';
        $hasMore = true;
        if (!empty($subscribers)) {
            foreach ($subscribers as $subscriber) {
                $htmlContent .= '<tr>
                    <td><input type="checkbox" name="subscriber_id[]" value="' . esc($subscriber->id) . '"></td>
                    <td>' . $subscriber->id . '</td>
                    <td>' . esc($subscriber->email) . '</td>
                    <td>
                        <a href="javascript:void(0)" 
                           onclick="deleteItem(\'Admin/deleteSubscriberPost\', \'' . $subscriber->id . '\', \'' . trans("confirm_delete", true) . '\');" 
                           class="text-danger"><i class="fa fa-trash-can"></i>&nbsp;&nbsp;' . trans('delete') . '
                        </a>
                    </td>
                </tr>';
            }
        } else {
            $hasMore = false;
            if ($page < 2) {
                $htmlContent .= '<tr><td colspan="5"><p class="text-muted text-center">' . esc(trans("no_results_found")) . '</p></td></tr>';
            }
        }
        $data = [
            'result' => 1,
            'hasMore' => $hasMore,
            'htmlContent' => $htmlContent
        ];
        return jsonResponse($data);
    }

    /**
     * Add to Newsletter
     */
    public function addToNewsletter()
    {
        $vld = inputPost('url');
        if (!empty($vld)) {
            return jsonResponse();
        }
        $data = [
            'result' => 0,
            'message' => '',
            'isSuccess' => 0,
        ];
        $email = cleanStr(inputPost('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['message'] = trans("msg_invalid_email");
        } else {
            if ($email) {
                $newsletterModel = new NewsletterModel();
                if (empty($newsletterModel->getSubscriber($email))) {
                    if ($newsletterModel->addSubscriber($email)) {
                        $data['message'] = trans("msg_newsletter_success");
                        $data['isSuccess'] = 1;
                    }
                } else {
                    $data['message'] = trans("msg_newsletter_error");
                }
                $data['result'] = 1;
            }
        }
        return jsonResponse($data);
    }

    /**
     * Run QueueWorker
     */
    public function runQueueWorker()
    {
        try {
            // Run email queue
            $emailModel = new EmailModel();
            $emailModel->runEmailQueue();

            // Update last seen
            if (authCheck()) {
                $this->authModel->updateLastSeen();
            }
        } catch (\Throwable $e) {
        }

        return jsonResponse();
    }

    /*
     * --------------------------------------------------------------------
     * Email Functions
     * --------------------------------------------------------------------
     */

    /**
     * Hide Cookies Warning
     */
    public function hideCookiesWarning()
    {
        helperSetCookie('cks_warning', '1', time() + (86400 * 365));
        return jsonResponse();
    }

    /**
     * Load Users Dropdown
     */
    public function loadUsersDropdown()
    {
        $query = inputPost('q');
        $users = $this->authModel->loadUsersDropdown($query);
        return jsonResponse(['items' => $users]);
    }
}
