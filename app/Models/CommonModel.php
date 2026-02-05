<?php namespace App\Models;

class CommonModel extends BaseModel
{
    protected $builderSlider;
    protected $builderBanners;
    protected $builderAbuseReports;
    protected $builderReviews;
    protected $builderComments;
    protected $builderContact;
    protected $builderAds;
    protected $builderAffiliateLinks;
    protected $builderBankTransfers;

    public function __construct()
    {
        parent::__construct();
        $this->builderSlider = $this->db->table('slider');
        $this->builderBanners = $this->db->table('homepage_banners');
        $this->builderAbuseReports = $this->db->table('abuse_reports');
        $this->builderReviews = $this->db->table('reviews');
        $this->builderComments = $this->db->table('comments');
        $this->builderContact = $this->db->table('contacts');
        $this->builderAds = $this->db->table('ad_spaces');
        $this->builderAffiliateLinks = $this->db->table('affiliate_links');
        $this->builderBankTransfers = $this->db->table('bank_transfers');
    }

    //get ajax search suggestions
    public function getSearchSuggestions($searchTerm, $langId)
    {
        $cleanedTerm = trim(removeSpecialCharacters($searchTerm));

        if (mb_strlen($cleanedTerm, 'UTF-8') < 2) {
            return ['tags' => [], 'categories' => [], 'brands' => [], 'shops' => []];
        }

        // Tag suggestions
        $tagSuggestions = $this->db->table('tags')->select('tag')->where('lang_id', (int)$langId)
            ->like('tag', $cleanedTerm, 'after')->limit(10)->get()->getResult();

        // Check if $searchTerm already exists in suggestions
        $exists = false;
        foreach ($tagSuggestions as $tag) {
            if (strcasecmp($tag->tag, $searchTerm) === 0) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $newTag = new \stdClass();
            $newTag->tag = $searchTerm;
            array_unshift($tagSuggestions, $newTag);
        }

        // Category suggestions
        $categorySuggestions = $this->db->table('categories')
            ->select('categories.*, category_lang.name, (SELECT slug FROM categories AS parent_cat WHERE parent_cat.id = categories.parent_id) AS parent_slug')
            ->join('category_lang', 'category_lang.category_id = categories.id')
            ->where('category_lang.lang_id', (int)$langId)->like('category_lang.name', $cleanedTerm, 'after')
            ->limit(3)->get()->getResult();

        // Brand suggestions
        $brandSuggestions = $this->db->table('brand_lang')->select('brands.id, brand_lang.name')->join('brands', 'brands.id = brand_lang.brand_id')
            ->where('brand_lang.lang_id', (int)$langId)->like('brand_lang.name', $cleanedTerm, 'after')
            ->limit(3)->get()->getResult();

        // Shop suggestions
        $shopSuggestions = $this->db->table('users')->select('username, slug')
            ->where('users.banned', 0)->where('users.vacation_mode', 0)
            ->having('EXISTS (SELECT 1 FROM products WHERE products.user_id = users.id)')
            ->like('username', $cleanedTerm, 'after')
            ->limit(3)->get()->getResult();

        return [
            'tags' => $tagSuggestions,
            'categories' => $categorySuggestions,
            'brands' => $brandSuggestions,
            'shops' => $shopSuggestions
        ];
    }

    /*
     * --------------------------------------------------------------------
     * Slider
     * --------------------------------------------------------------------
     */

    //add item
    public function addSliderItem()
    {
        $data = [
            'lang_id' => inputPost('lang_id'),
            'title' => inputPost('title'),
            'description' => inputPost('description'),
            'link' => inputPost('link'),
            'item_order' => inputPost('item_order'),
            'button_text' => inputPost('button_text'),
            'text_color' => inputPost('text_color'),
            'button_color' => inputPost('button_color'),
            'button_text_color' => inputPost('button_text_color'),
            'animation_title' => inputPost('animation_title'),
            'animation_description' => inputPost('animation_description'),
            'animation_button' => inputPost('animation_button')
        ];
        $uploadModel = new UploadModel();
        $tempFile = $uploadModel->uploadTempFile('file');
        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data['image'] = $uploadModel->uploadSliderImage($tempFile['path'], false);
            $uploadModel->deleteTempFile($tempFile['path']);
        }
        $tempFileMobile = $uploadModel->uploadTempFile('file_mobile');
        if (!empty($tempFileMobile) && !empty($tempFileMobile['path'])) {
            $data['image_mobile'] = $uploadModel->uploadSliderImage($tempFileMobile['path'], true);
            $uploadModel->deleteTempFile($tempFileMobile['path']);
        }
        return $this->builderSlider->insert($data);
    }

    //edit slider item
    public function editSliderItem($id)
    {
        $item = $this->getSliderItem($id);
        if (!empty($item)) {
            $data = [
                'lang_id' => inputPost('lang_id'),
                'title' => inputPost('title'),
                'description' => inputPost('description'),
                'link' => inputPost('link'),
                'item_order' => inputPost('item_order'),
                'button_text' => inputPost('button_text'),
                'text_color' => inputPost('text_color'),
                'button_color' => inputPost('button_color'),
                'button_text_color' => inputPost('button_text_color'),
                'animation_title' => inputPost('animation_title'),
                'animation_description' => inputPost('animation_description'),
                'animation_button' => inputPost('animation_button')
            ];
            $uploadModel = new UploadModel();
            $tempFile = $uploadModel->uploadTempFile('file');
            if (!empty($tempFile) && !empty($tempFile['path'])) {
                deleteStorageFile($item->image);
                $data['image'] = $uploadModel->uploadSliderImage($tempFile['path'], false);
                $uploadModel->deleteTempFile($tempFile['path']);
            }
            $tempFileMobile = $uploadModel->uploadTempFile('file_mobile');
            if (!empty($tempFileMobile) && !empty($tempFileMobile['path'])) {
                deleteStorageFile($item->image_mobile);
                $data['image_mobile'] = $uploadModel->uploadSliderImage($tempFileMobile['path'], true);
                $uploadModel->deleteTempFile($tempFileMobile['path']);
            }
            if (!$this->db->connID || !mysqli_ping($this->db->connID)) {
                $this->db->reconnect();
            }
            return $this->builderSlider->where('id', $item->id)->update($data);
        }
        return false;
    }

    //get slider item
    public function getSliderItem($id)
    {
        return $this->builderSlider->where('id', clrNum($id))->get()->getRow();
    }

    //get slider items
    public function getSliderItems()
    {
        return $this->builderSlider->orderBy('item_order')->get()->getResult();
    }

    //get slider items by languages
    public function getSliderItemsByLang($langId)
    {
        return getCacheData('slider_' . $langId, function () use ($langId) {
            return $this->builderSlider->where('lang_id', clrNum($langId))->orderBy('item_order')->get()->getResult();
        }, 'static');
    }

    //edit slider settings
    public function editSliderSettings()
    {
        $data = [
            'slider_status' => inputPost('slider_status'),
            'slider_type' => inputPost('slider_type'),
            'slider_effect' => inputPost('slider_effect')
        ];
        return $this->db->table('general_settings')->where('id', 1)->update($data);
    }

    //delete slider item
    public function deleteSliderItem($id)
    {
        $item = $this->getSliderItem($id);
        if (!empty($item)) {
            deleteStorageFile($item->image);
            deleteStorageFile($item->image_mobile);
            return $this->builderSlider->where('id', $item->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Index Banners
     * --------------------------------------------------------------------
     */

    //add index banner
    public function addIndexBanner()
    {
        $data = [
            'banner_url' => addHttpsToUrl(inputPost('banner_url')),
            'banner_order' => inputPost('banner_order'),
            'banner_width' => inputPost('banner_width'),
            'banner_location' => inputPost('banner_location'),
            'lang_id' => inputPost('lang_id')
        ];
        if ($data['banner_width'] > 100) {
            $data['banner_width'] = 100;
        }

        $uploadModel = new UploadModel();
        $file = $uploadModel->uploadAd('file');
        if (!empty($file) && !empty($file['path'])) {
            $data['banner_image_path'] = $file['path'];
            $data['storage'] = $this->activeStorage;
        }

        return $this->builderBanners->insert($data);
    }

    //edit index banner
    public function editIndexBanner($id)
    {
        $banner = $this->getIndexBanner($id);
        if (!empty($banner)) {
            $data = [
                'banner_url' => addHttpsToUrl(inputPost('banner_url')),
                'banner_order' => inputPost('banner_order'),
                'banner_width' => inputPost('banner_width'),
                'banner_location' => inputPost('banner_location'),
                'lang_id' => inputPost('lang_id')
            ];
            if ($data['banner_width'] > 100) {
                $data['banner_width'] = 100;
            }

            $uploadModel = new UploadModel();
            $file = $uploadModel->uploadAd('file');
            if (!empty($file) && !empty($file['path'])) {
                $data['banner_image_path'] = $file['path'];
                $data['storage'] = $this->activeStorage;
                deleteStorageFile($banner->banner_image_path, $banner->storage);
            }

            return $this->builderBanners->where('id', $banner->id)->update($data);
        }
        return false;
    }

    //get index banner
    public function getIndexBanner($id)
    {
        return $this->builderBanners->where('id', clrNum($id))->get()->getRow();
    }

    //get index banners
    public function getIndexBanners()
    {
        return $this->builderBanners->orderBy('banner_order')->get()->getResult();
    }

    //get index banners array
    public function getIndexBannersArray()
    {
        $langId = $this->activeLang->id;
        return getCacheData('index_banners_' . $langId, function () use ($langId) {
            $banners = $this->getIndexBanners();
            $array = array();
            if (!empty($banners)) {
                foreach ($banners as $banner) {
                    if ($banner->lang_id == $langId) {
                        @$array[$banner->banner_location][] = $banner;
                    }
                }
            }
            return $array;
        }, 'static');
    }

    //delete index banner
    public function deleteIndexBanner($id)
    {
        $banner = $this->getIndexBanner($id);
        if (!empty($banner)) {
            deleteStorageFile($banner->banner_image_path, $banner->storage);
            return $this->builderBanners->where('id', $banner->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Abuse Reports
     * --------------------------------------------------------------------
     */

    //report abuse
    public function reportAbuse()
    {
        $data = [
            'item_type' => inputPost('item_type'),
            'item_id' => inputPost('id'),
            'report_user_id' => user()->id,
            'description' => inputPost('description'),
            'created_at' => date("Y-m-d H:i:s")
        ];
        if (empty($data['item_id'])) {
            $data['item_id'] = 0;
        }
        return $this->builderAbuseReports->insert($data);
    }

    //get abuse reports count
    public function getAbuseReportsCount()
    {
        return $this->builderAbuseReports->countAllResults();
    }

    //get paginated abuse reports
    public function getAbuseReportsPaginated($perPage, $offset)
    {
        return $this->builderAbuseReports->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //delete abuse report
    public function deleteAbuseReport($id)
    {
        return $this->builderAbuseReports->where('id', clrNum($id))->delete();
    }

    /*
     * --------------------------------------------------------------------
     * Ad Spaces
     * --------------------------------------------------------------------
     */

    public function updateAdSpaces($id)
    {
        $adSpace = $this->getAdSpaceById($id);
        if (!empty($adSpace)) {
            $uploadModel = new UploadModel();
            $data = [
                'ad_code_desktop' => inputPost('ad_code_desktop'),
                'ad_code_mobile' => inputPost('ad_code_mobile'),
                'desktop_width' => inputPost('desktop_width'),
                'desktop_height' => inputPost('desktop_height'),
                'mobile_width' => inputPost('mobile_width'),
                'mobile_height' => inputPost('mobile_height'),
                'storage' => $this->activeStorage
            ];
            $adURL = inputPost('url_ad_code_desktop');
            $file = $uploadModel->uploadAd('file_ad_code_desktop');
            if (!empty($file) && !empty($file['path'])) {
                $data['ad_code_desktop'] = $this->createAdCode($adURL, $file, $data['desktop_width'], $data['desktop_height']);
            }
            $adURL = inputPost('url_ad_code_mobile');
            $file = $uploadModel->uploadAd('file_ad_code_mobile');
            if (!empty($file) && !empty($file['path'])) {
                $data['ad_code_mobile'] = $this->createAdCode($adURL, $file, $data['mobile_width'], $data['mobile_height']);
            }
            return $this->builderAds->where('id', $adSpace->id)->update($data);
        }
        return false;
    }

    //get ad spaces
    public function getAdSpaces()
    {
        return getCacheData('ad_spaces', function () {
            return $this->builderAds->get()->getResult();
        }, 'static');
    }

    //get ad spaces by lang
    public function getAdSpacesByLang($langId)
    {
        return $this->builderAds->where('lang_id', clrNum($langId))->get()->getResult();
    }

    //get ad spaces by id
    public function getAdSpaceById($id)
    {
        return $this->builderAds->where('id', clrNum($id))->get()->getRow();
    }

    //get ad space
    public function getAdSpace($adSpace, $adSpaceArray)
    {
        $row = $this->builderAds->where('ad_space', cleanStr($adSpace))->get()->getRow();
        if (!empty($row)) {
            return $row;
        }
        $addNew = false;
        foreach ($adSpaceArray as $key => $value) {
            if ($key == strSlug($adSpace)) {
                $addNew = true;
            }
        }
        if ($addNew) {
            $data = [
                'ad_space' => strSlug($adSpace),
                'ad_code_desktop' => '',
                'desktop_width' => 728,
                'desktop_height' => 90,
                'ad_code_mobile' => '',
                'mobile_width' => 300,
                'mobile_height' => 250,
                'mobile_width' => 300,
            ];
            if ($adSpace == 'sidebar_1' || $adSpace == 'sidebar_2') {
                $data['desktop_width'] = 336;
                $data['desktop_height'] = 280;
            }
            $this->builderAds->insert($data);
            return $this->builderAds->where('ad_space', cleanStr($adSpace))->get()->getRow();
        }
        return false;
    }

    //create ad code
    public function createAdCode($url, $file, $width, $height)
    {
        $imgUrl = getStorageFileUrl($file['path'], $file['storage']);

        return '<a href="' . $url . '" aria-label="link-bn' . '"><img data-src="' . $imgUrl . '" width="' . $width . '" height="' . $height . '" alt="" class="lazyload"></a>';
    }

    //update google adsense code
    public function updateGoogleAdsenseCode()
    {
        return $this->db->table('general_settings')->where('id', 1)->update(['google_adsense_code' => inputPost('google_adsense_code')]);
    }

    /*
     * --------------------------------------------------------------------
     * Reviews
     * --------------------------------------------------------------------
     */

    //add review
    public function addReview($rating, $productId, $reviewText)
    {
        $data = [
            'product_id' => $productId,
            'user_id' => user()->id,
            'rating' => $rating,
            'review' => !empty($reviewText) ? $reviewText : '',
            'ip_address' => 0,
            'created_at' => date("Y-m-d H:i:s")
        ];
        $ip = getIPAddress();
        if (!empty($ip)) {
            $data['ip_address'] = $ip;
        }
        if (strlen($data['review']) > REVIEW_CHARACTER_LIMIT) {
            $data['review'] = substr($data['review'], 0, REVIEW_CHARACTER_LIMIT);
        }
        if (!empty($data['product_id']) && !empty($data['user_id']) && !empty($data['rating'])) {
            $this->builderReviews->insert($data);
            $this->updateProductRating($productId);
        }
    }

    //update review
    public function updateReview($review_id, $rating, $productId, $reviewText)
    {
        $data = [
            'rating' => $rating,
            'review' => $reviewText,
            'ip_address' => 0,
            'created_at' => date("Y-m-d H:i:s")
        ];
        $ip = getIPAddress();
        if (!empty($ip)) {
            $data['ip_address'] = $ip;
        }
        if (!empty($data['rating']) && !empty($data['review'])) {
            $this->builderReviews->where('product_id', clrNum($productId))->where('user_id', user()->id)->update($data);
            $this->updateProductRating($productId);
        }
    }

    //get reviews count
    public function getReviewsCount()
    {
        $this->filterReviews();
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id')
            ->select('reviews.*, users.username as user_username, users.slug as user_slug')->countAllResults();
    }

    //get paginated reviews
    public function getReviewsPaginated($perPage, $offset)
    {
        $this->filterReviews();
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id')
            ->select('reviews.*, users.username as user_username, users.slug as user_slug')->orderBy('reviews.created_at DESC')
            ->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter reviews
    public function filterReviews()
    {
        $q = inputGet('q');
        if (!empty($q)) {
            $this->builderReviews->like('review', cleanStr($q))->orLike('users.username', cleanStr($q));
        }
    }

    //get reviews count
    public function getReviewsCountByProductId($productId)
    {
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->where('reviews.product_id', clrNum($productId))->countAllResults();
    }

    //get reviews
    public function getReviewsByProductId($productId)
    {
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->select('reviews.*, users.username as user_username, users.slug as user_slug')
            ->where('reviews.product_id', clrNum($productId))->orderBy('reviews.created_at DESC')->get()->getResult();
    }

    //get latest reviews
    public function getLatestReviews($limit)
    {
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->select('reviews.*, users.username as user_username')
            ->orderBy('reviews.id DESC')->get(clrNum($limit))->getResult();
    }

    //get review
    public function getReview($productId, $userId)
    {
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->select('reviews.*, users.username as user_username, users.slug as user_slug')
            ->where('reviews.product_id', $productId)->where('users.id', $userId)->get()->getRow();
    }

    //get review by id
    public function getReviewById($id)
    {
        return $this->builderReviews->where('id', clrNum($id))->get()->getRow();
    }

    //update product rating
    public function updateProductRating($productId)
    {
        $reviews = $this->getReviewsByProductId($productId);
        $data = array();
        if (!empty($reviews)) {
            $count = countItems($reviews);
            $total = 0;
            foreach ($reviews as $review) {
                $total += $review->rating;
            }
            $data['rating'] = round($total / $count);
        } else {
            $data['rating'] = 0;
        }
        $this->db->table('products')->where('id', clrNum($productId))->update($data);
    }

    //get user reviews count
    public function getUserReviewsCount($userId, $isSelf = false)
    {
        $this->builderReviews->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id');
        $isSelf ? $this->builderReviews->where('reviews.user_id', $userId) : $this->builderReviews->where('products.user_id', $userId);
        return $this->builderReviews->countAllResults();
    }

    //get paginated vendor reviews
    public function getUserReviewsPaginated($userId, $perPage, $offset, $isSelf = false)
    {
        $this->builderReviews->select('reviews.*, users.username AS user_username, users.slug AS user_slug, products.slug AS product_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar,
        (SELECT title FROM product_details WHERE product_details.product_id = products.id AND product_details.lang_id = ' . $this->db->escape($this->defaultLang->id) . ' LIMIT 1) AS product_title')
            ->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id');
        $isSelf ? $this->builderReviews->where('reviews.user_id', clrNum($userId)) : $this->builderReviews->where('products.user_id', clrNum($userId));
        return $this->builderReviews->orderBy('reviews.created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get reviews load more
    public function getProductReviewsByOffset($productId, $perPage, $offset)
    {
        return $this->builderReviews->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id')
            ->select('reviews.*, users.username AS user_username, users.slug AS user_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar')
            ->where('products.id', clrNum($productId))->orderBy('reviews.created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //calculate user rating
    public function calculateUserRating($userId)
    {
        $std = new \stdClass();
        $std->count = 0;
        $std->rating = 0;
        $row = $this->builderReviews->join('users', 'users.id = reviews.user_id')->join('products', 'products.id = reviews.product_id')->select('COUNT(reviews.id) AS count, SUM(reviews.rating) AS total')
            ->where('products.user_id', clrNum($userId))->get()->getRow();
        if (!empty($row)) {
            $total = $row->total;
            $count = $row->count;
            if (!empty($total) && !empty($count)) {
                $avg = round($total / $count);
                $std->count = $count;
                $std->rating = $avg;
            }
        }
        return $std;
    }

    //delete review
    public function deleteReview($id, $productId = null)
    {
        $review = $this->getReviewById($id);
        if (!empty($review)) {
            if ($this->builderReviews->where('id', $review->id)->delete()) {
                $this->updateProductRating($review->product_id);
                return true;
            }
        }
        return false;
    }

    //delete multi reviews
    public function deleteSelectedReviews($reviewIds)
    {
        if (!empty($reviewIds)) {
            foreach ($reviewIds as $id) {
                $this->deleteReview($id);
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Comments
     * --------------------------------------------------------------------
     */

    //add comment
    public function addComment()
    {
        $data = [
            'parent_id' => inputPost('parent_id'),
            'product_id' => inputPost('product_id'),
            'user_id' => 0,
            'name' => inputPost('name'),
            'email' => inputPost('email'),
            'comment' => inputPost('comment'),
            'status' => 1,
            'ip_address' => 0,
            'created_at' => date("Y-m-d H:i:s")
        ];
        if ($this->generalSettings->comment_approval_system == 1 && !hasPermission('comments')) {
            $data['status'] = 0;
        }
        if (empty($data['parent_id'])) {
            $data['parent_id'] = 0;
        }
        if (authCheck()) {
            $data['user_id'] = user()->id;
            $data['name'] = getUsername(user());
            $data['email'] = user()->email;
            if (hasPermission('comments')) {
                $data['status'] = 1;
            }
        } else {
            if (empty($data['name']) || empty($data['email'])) {
                return false;
            }
        }
        if (empty($data['name'])) {
            $data['name'] = '';
        }
        if (empty($data['email'])) {
            $data['email'] = '';
        }
        $ip = getIPAddress();
        if (!empty($ip)) {
            $data['ip_address'] = $ip;
        }
        $data['parent_id'] = clrNum($data['parent_id']);
        $data['product_id'] = clrNum($data['product_id']);

        //check limits
        if (strlen($data['name']) > 255) {
            $data['name'] = substr($data['name'], 0, 255);
        }
        if (strlen($data['email']) > 255) {
            $data['email'] = substr($data['email'], 0, 255);
        }
        if (strlen($data['comment']) > COMMENT_CHARACTER_LIMIT) {
            $data['comment'] = substr($data['comment'], 0, COMMENT_CHARACTER_LIMIT);
        }
        if (!empty($data['product_id']) && !empty($data['comment'])) {
            $this->builderComments->insert($data);
        }
    }

    //get comment count
    public function getCommentCount($status)
    {
        return $this->builderComments->where('status', clrNum($status))->countAllResults();
    }

    //get paginated comments
    public function getCommentsPaginated($status, $perPage, $offset)
    {
        return $this->builderComments->where('status', clrNum($status))->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //latest comments
    public function getLatestComments($limit)
    {
        return $this->builderComments->orderBy('id DESC')->get(clrNum($limit))->getResult();
    }

    //comments
    public function getProductCommentsByOffset($productId, $perPage, $offset)
    {
        $arrayComments = [];
        $parentIds = [];
        $this->builderComments->select('comments.*, users.username AS user_username, users.slug AS user_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar')
            ->join('users', 'comments.user_id = users.id', 'left')->where('comments.product_id', clrNum($productId))->where('comments.parent_id', 0);
        if ($this->generalSettings->comment_approval_system == 1) {
            $this->builderComments->where('comments.status', 1);
        }
        $parentResult = $this->builderComments->orderBy('comments.id', 'DESC')->limit($perPage, $offset)->get()->getResult();

        if (!empty($parentResult)) {
            foreach ($parentResult as $parent) {
                $arrayComments[0][] = $parent;
                $parentIds[] = $parent->id;
            }
        }

        if (!empty($parentIds)) {
            $this->builderComments->select('comments.*, users.username AS user_username, users.slug AS user_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar')
                ->join('users', 'comments.user_id = users.id', 'left')->where('comments.product_id', clrNum($productId))->whereIn('comments.parent_id', $parentIds);
            if ($this->generalSettings->comment_approval_system == 1) {
                $this->builderComments->where('comments.status', 1);
            }
            $subResult = $this->builderComments->orderBy('comments.id', 'DESC')->get()->getResult();

            if (!empty($subResult)) {
                foreach ($subResult as $reply) {
                    $arrayComments[$reply->parent_id][] = $reply;
                }
            }
        }

        return $arrayComments;
    }

    //comment
    public function getComment($id)
    {
        return $this->builderComments->where('id', clrNum($id))->get()->getRow();
    }

    //product comment count
    public function getProductCommentCount($productId)
    {
        return $this->builderComments->where('product_id', clrNum($productId))->where('parent_id', 0)->where('status', 1)->countAllResults();
    }

    //get vendor comments count
    public function getVendorCommentsCount($userId)
    {
        return $this->builderComments->join('products', 'comments.product_id = products.id')->where('products.user_id', clrNum($userId))->where('products.status', 1)
            ->where('products.visibility', 1)->where('products.is_draft', 0)->where('products.is_deleted', 0)->countAllResults();
    }

    //get paginated vendor comments
    public function getVendorCommentsPaginated($userId, $perPage, $offset)
    {
        return $this->builderComments
            ->join('products USE INDEX (idx_active_user_products)', 'comments.product_id = products.id')
            ->select('comments.*, products.slug AS product_slug, 
        (SELECT users.slug FROM users WHERE comments.user_id = users.id LIMIT 1) AS user_slug, 
        (SELECT title FROM product_details WHERE product_details.product_id = products.id AND product_details.lang_id = ' . $this->db->escape($this->defaultLang->id) . ' LIMIT 1) AS title')
            ->where('products.user_id', clrNum($userId))->where('products.is_active', 1)
            ->orderBy('comments.id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //approve comment
    public function approveComment($id)
    {
        $comment = $this->getComment($id);
        if (!empty($comment)) {
            return $this->builderComments->where('id', $comment->id)->update(['status' => 1]);
        }
        return false;
    }

    //approve multi comments
    public function approveMultiComments($commentIds)
    {
        if (!empty($commentIds)) {
            foreach ($commentIds as $id) {
                $this->approveComment($id);
            }
        }
    }

    //delete comment
    public function deleteComment($id)
    {
        $comment = $this->getComment($id);
        if (!empty($comment)) {
            $this->builderComments->where('parent_id', $comment->id)->delete();
            return $this->builderComments->where('id', $comment->id)->delete();
        }
        return false;
    }

    //delete multi comments
    public function deleteMultiComments($commentIds)
    {
        if (!empty($commentIds)) {
            foreach ($commentIds as $id) {
                $this->deleteComment($id);
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Contact Messages
     * --------------------------------------------------------------------
     */

    //add contact message
    public function addContactMessage()
    {
        $data = [
            'name' => inputPost('name'),
            'email' => inputPost('email'),
            'message' => inputPost('message'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        //send email
        if (getEmailOptionStatus($this->generalSettings, 'contact_messages') == 1) {
            $emailData = [
                'email_type' => 'contact',
                'email_address' => $this->generalSettings->mail_options_account,
                'email_data' => serialize(['messageName' => $data['name'], 'messageEmail' => $data['email'], 'messageText' => $data['message']]),
                'email_subject' => trans("contact_message"),
                'template_path' => 'email/contact_message'
            ];
            addToEmailQueue($emailData);
        }
        return $this->builderContact->insert($data);
    }

    //get contact messages
    public function getContactMessages()
    {
        return $this->builderContact->orderBy('id DESC')->get()->getResult();
    }

    //get contact message
    public function getContactMessage($id)
    {
        return $this->builderContact->where('id', clrNum($id))->get()->getRow();
    }

    //get lastest contact messages
    public function getLastestContactMessages()
    {
        return $this->builderContact->orderBy('id DESC')->get(5)->getResult();
    }

    //delete contact message
    public function deleteContactMessage($id)
    {
        $contact = $this->getContactMessage($id);
        if (!empty($contact)) {
            return $this->builderContact->where('id', $contact->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Brands
     * --------------------------------------------------------------------
     */

    //add brand
    public function addBrand()
    {
        $data = [
            'name' => inputPost('name_' . $this->defaultLang->id),
            'show_on_slider' => !empty(inputPost('show_on_slider')) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $uploadModel = new UploadModel();
        $file = $uploadModel->uploadTempFile('file', true);
        if (!empty($file) && !empty($file['path'])) {
            $data['image_path'] = $uploadModel->uploadBrand($file['path']);
            $data['storage'] = $this->activeStorage;
            $uploadModel->deleteTempFile($file['path']);
        }

        if ($this->db->table('brands')->insert($data)) {
            $brandId = $this->db->insertID();
            $this->addEditBrandName($brandId);
            $this->updateBrandCategories($brandId);
            return true;
        }
    }

    //edit brand
    public function editBrand()
    {
        $id = inputPost('id');
        $brand = $this->getBrand($id);
        if (!empty($brand)) {

            $data = [
                'name' => inputPost('name_' . $this->defaultLang->id),
                'show_on_slider' => !empty(inputPost('show_on_slider')) ? 1 : 0
            ];

            $uploadModel = new UploadModel();
            $file = $uploadModel->uploadTempFile('file', true);
            if (!empty($file) && !empty($file['path'])) {
                deleteStorageFile($brand->image_path, $brand->storage);
                $data['image_path'] = $uploadModel->uploadBrand($file['path']);
                $data['storage'] = $this->activeStorage;
                $uploadModel->deleteTempFile($file['path']);
            }

            if ($this->db->table('brands')->where('id', $brand->id)->update($data)) {
                $this->addEditBrandName($brand->id);
                $this->updateBrandCategories($brand->id);
                return true;
            }
        }
        return false;
    }

    //add edit brand name
    public function addEditBrandName($brandId)
    {
        $brandId = clrNum($brandId);
        foreach ($this->activeLanguages as $language) {
            $data = [
                'name' => inputPost('name_' . $language->id)
            ];

            $exists = $this->db->table('brand_lang')->where('brand_id', $brandId)->where('lang_id', $language->id)->countAllResults();
            if ($exists > 0) {
                $this->db->table('brand_lang')->where('brand_id', $brandId)->where('lang_id', $language->id)->update($data);
            } else {
                $data['brand_id'] = $brandId;
                $data['lang_id'] = $language->id;
                $this->db->table('brand_lang')->insert($data);
            }
        }
    }

    //update brand categories
    public function updateBrandCategories($brandId)
    {
        $this->db->table('brand_category')->where('brand_id', $brandId)->delete();

        $categoryIds = inputPost('category_ids');

        if (!empty($categoryIds)) {
            $categoryIdsArr = explode(',', $categoryIds);
            $categoryIdsArr = array_map('intval', array_filter($categoryIdsArr));

            if (!empty($categoryIdsArr)) {
                $insertData = [];
                foreach ($categoryIdsArr as $id) {
                    $insertData[] = [
                        'brand_id'    => $brandId,
                        'category_id' => $id
                    ];
                }

                $this->db->table('brand_category')->insertBatch($insertData);
            }
        }
    }

    //get brand
    public function getBrand($id)
    {
        return $this->db->table('brands')->where('brands.id', clrNum($id))->get()->getRow();
    }

    //get product brand
    public function getProductBrand($id, $langId)
    {
        $builder = $this->db->table('brands');

        $builder->select('brands.*');

        $this->joinBrandNameQuery($builder, $langId);

        return $builder->where('brands.id', clrNum($id))->get()->getRow();
    }

    //get brands
    public function getBrands($langId, $categoryId = null, $searchTerm = null, $limit = null, $offset = 0, $isSlider = null)
    {
        $builder = $this->db->table('brands');

        $builder->select('brands.*');

        $this->joinBrandNameQuery($builder, $langId);

        if ($isSlider !== null) {
            $builder->where('brands.show_on_slider', 1);
        }

        if (!empty($categoryId)) {
            $categoryModel = new CategoryModel();
            $category = $categoryModel->getCategory($categoryId);
            if (!empty($category)) {
                $idsArray = [];
                $categories = $categoryModel->getCategoryParentTree($category->id);
                if (!empty($categories)) {
                    $idsArray = array_column($categories, 'id');
                }
                if (!empty($idsArray)) {
                    if (!empty($searchTerm)) {
                        $builder->join('brand_category', 'brand_category.brand_id = brands.id')->whereIn('brand_category.category_id', $idsArray);
                    } else {
                        $subQuery = $this->db->table('brand_category')->select('brand_id')->whereIn('category_id', $idsArray);
                        $builder->whereIn('brands.id', $subQuery);
                    }
                }
            }
        }

        if (!empty($searchTerm)) {
            $searchTerm = removeForbiddenCharacters($searchTerm);
            if ($langId == $this->defaultLang->id) {
                $builder->like('brand_lang.name', $searchTerm);
            } else {
                $builder->groupStart()->like('lang_selected.name', $searchTerm)->orLike('lang_default.name', $searchTerm)->groupEnd();
            }
        }

        $builder->distinct();

        if ($limit !== null) {
            $builder->limit($limit + 1, $offset);
        }

        $brands = $builder->orderBy('brands.name')->get()->getResult();

        $hasMore = false;
        if ($limit !== null) {
            $hasMore = count($brands) > $limit;
            if ($hasMore) {
                array_pop($brands);
            }
        }

        return ['brands' => $brands, 'hasMore' => $hasMore];
    }

    //get brands count
    public function getBrandsCount($q = null)
    {
        $builder = $this->db->table('brands');
        if (!empty($q)) {
            $builder->select('brands.*')->join('brand_lang', 'brand_lang.brand_id = brands.id', 'left')->like('brand_lang.name', $q)->distinct();
        }
        return $builder->countAllResults();
    }

    //get brands paginated
    public function getBrandsPaginated($langId, $perPage, $offset, $q = null)
    {
        $builder = $this->db->table('brands');

        $categorySubQuery = "(
            SELECT GROUP_CONCAT(COALESCE(cat_lang_selected.name, cat_lang_default.name) SEPARATOR '|||') 
            FROM brand_category
            LEFT JOIN category_lang AS cat_lang_selected 
                ON cat_lang_selected.category_id = brand_category.category_id AND cat_lang_selected.lang_id = " . $this->db->escape($langId) . "
            LEFT JOIN category_lang AS cat_lang_default 
                ON cat_lang_default.category_id = brand_category.category_id AND cat_lang_default.lang_id = " . $this->db->escape($this->defaultLang->id) . "
            WHERE brand_category.brand_id = brands.id
        ) AS category_names";

        $builder->select('brands.*')->select($categorySubQuery, false);
        $this->joinBrandNameQuery($builder, $langId);

        if (!empty($q)) {
            if ($langId == $this->defaultLang->id) {
                $builder->like('brand_lang.name', $q);
            } else {
                $builder->groupStart()->like('lang_selected.name', $q)->orLike('lang_default.name', $q)->groupEnd();
            }
        }

        return $builder->orderBy('brands.id', 'DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get brand name language array
    public function getBrandNameLanguageArray($brandId)
    {
        $nameArray = array();
        $result = $this->db->table('brand_lang')->where('brand_id', $brandId)->get()->getResult();
        if (!empty($result)) {
            foreach ($result as $item) {
                $nameArray[$item->lang_id] = $item->name;
            }
        }

        return $nameArray;
    }

    //get brand names by given brand ids
    public function getBrandNameArray($brandIds, $langId)
    {
        if (empty($brandIds)) {
            return [];
        }

        $builder = $this->db->table('brands');

        $builder->select('brands.id');

        $this->joinBrandNameQuery($builder, $langId);

        $queryResult = $builder->whereIn('brands.id', $brandIds)->get()->getResult();

        // Transform the array of objects into the desired [id => name] format.
        $brandsArray = [];
        if (!empty($queryResult)) {
            foreach ($queryResult as $brand) {
                $brandsArray[$brand->id] = $brand->brand_name;
            }
        }

        return $brandsArray;
    }

    //get brand slider items
    public function getBrandSliderItems($langId)
    {
        return getCacheData('brand_slider_items_' . $langId, function () use ($langId) {
            return $this->getBrands($langId, null, null, 50, 0, true);
        }, 'category');
    }

    //get brand category ids array
    public function getBrandCategoryIdsArray($brandId)
    {
        $array = [];
        $result = $this->db->table('brand_category')->where('brand_id', $brandId)->get()->getResult();
        if (!empty($result)) {
            $array = array_column($result, 'category_id');
        }

        return $array;
    }

    //join brand name query to get names by language
    private function joinBrandNameQuery(&$builder, $langId)
    {
        if ($langId == $this->defaultLang->id) {
            $builder->select('brand_lang.name as brand_name')->join('brand_lang', 'brand_lang.brand_id = brands.id AND brand_lang.lang_id = ' . $this->db->escape($langId), 'left');
        } else {
            $builder->select('COALESCE(lang_selected.name, lang_default.name) AS brand_name')
                ->join('brand_lang AS lang_selected', 'lang_selected.brand_id = brands.id AND lang_selected.lang_id = ' . $this->db->escape($langId), 'left')
                ->join('brand_lang AS lang_default', 'lang_default.brand_id = brands.id AND lang_default.lang_id = ' . $this->db->escape($this->defaultLang->id), 'left');
        }
    }

    //delete brand
    public function deleteBrand($id)
    {
        $brand = $this->getBrand($id);
        if (empty($brand)) {
            return false;
        }

        $this->db->transStart();

        $this->db->table('brand_lang')->where('brand_id', $brand->id)->delete();
        $this->db->table('brand_category')->where('brand_id', $brand->id)->delete();
        $this->db->table('brands')->where('id', $brand->id)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        deleteStorageFile($brand->image_path, $brand->storage);

        return true;
    }

    //update brand settings
    public function updateBrandSettings()
    {
        $data = [
            'brand_status' => !empty(inputPost('brand_status')) ? 1 : 0,
            'is_brand_optional' => !empty(inputPost('is_brand_optional')) ? 1 : 0,
            'brand_where_to_display' => inputPost('brand_where_to_display')
        ];
        return $this->db->table('product_settings')->where('id', 1)->update($data);
    }

    /*
     * --------------------------------------------------------------------
     * Affiliate Links
     * --------------------------------------------------------------------
     */

    //create affiliate link
    public function createAffiliateLink($userId, $productId, $langId)
    {
        $product = getProduct($productId);
        if (authCheck() && !empty($product)) {
            $data['referrer_id'] = clrNum($userId);
            $data['product_id'] = $product->id;
            $data['seller_id'] = $product->user_id;
            $data['lang_id'] = clrNum($langId);
            $data['link_short'] = uniqid();
            $data['created_at'] = date('Y-m-d H:i:s');
            if (empty($this->getAffiliateLink($userId, $product->id, $langId))) {
                return $this->builderAffiliateLinks->insert($data);
            }
        }
        return false;
    }

    //get affiliate link
    public function getAffiliateLink($userId, $productId, $langId)
    {
        return $this->builderAffiliateLinks->where('referrer_id', clrNum($userId))->where('product_id', clrNum($productId))->where('lang_id', clrNum($langId))->get()->getRow();
    }

    //get affiliate link by id
    public function getAffiliateLinkById($id)
    {
        return $this->builderAffiliateLinks->where('id', clrNum($id))->get()->getRow();
    }

    //get affiliate link by slug
    public function getAffiliateLinkBySlug($slug)
    {
        return $this->builderAffiliateLinks->where('link_short', cleanStr($slug))->get()->getRow();
    }

    //get user affiliate links count
    public function getUserAffiliateLinksCount($userId)
    {
        return $this->builderAffiliateLinks->where('referrer_id', clrNum($userId))->countAllResults();
    }

    //get user affiliate links paginated
    public function getUserAffiliateLinksPaginated($userId, $perPage, $offset)
    {
        return $this->builderAffiliateLinks->where('referrer_id', clrNum($userId))->orderBy('id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //convert affiliate link
    public function convertAffiliateLink($affiliateLink)
    {
        if (!empty($affiliateLink)) {
            $product = getProduct($affiliateLink->product_id);
            if (!empty($product)) {
                $url = '';
                $langBase = '';
                if ($this->generalSettings->site_lang != $affiliateLink->lang_id) {
                    $lang = getLanguage($affiliateLink->lang_id);
                    if (!empty($lang)) {
                        $langBase = $lang->short_form;
                    }
                }
                if (!empty($langBase)) {
                    $langBase = $langBase . '/';
                }
                return base_url($langBase . $product->slug);
            }
        }
        return false;
    }

    //set affiliate cookie
    public function setAffiliateCookie($affiliateLink)
    {
        if (!empty($affiliateLink)) {
            helperSetCookie(AFFILIATE_COOKIE_NAME, $affiliateLink->id, time() + (86400 * AFFILIATE_COOKIE_TIME));
        }
    }

    //delete affiliate cookie
    public function deleteAffiliateCookie($productIds)
    {
        if (!empty(helperGetCookie(AFFILIATE_COOKIE_NAME)) && !empty($productIds) && countItems($productIds) > 0) {
            $affiliateId = helperGetCookie(AFFILIATE_COOKIE_NAME);
            $affiliate = $this->getAffiliateLinkById($affiliateId);
            if (!empty($affiliate)) {
                if (in_array($affiliate->product_id, $productIds)) {
                    helperDeleteCookie(AFFILIATE_COOKIE_NAME);
                }
            }
        }
    }

    //delete affiliate link
    public function deleteAffiliateLink($id)
    {
        if (authCheck()) {
            $link = $this->getAffiliateLinkById($id);
            if (!empty($link) && user()->id == $link->referrer_id) {
                return $this->builderAffiliateLinks->where('id', $link->id)->delete();
            }
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Bank Transfer
     * --------------------------------------------------------------------
     */

    //add bank transfer payment report
    public function addBankTransferPaymentReport()
    {
        $reportType = inputPost('report_type');
        $reportItemId = inputPost('report_item_id');
        $orderNumber = inputPost('order_number');
        if (authCheck() && !empty($reportType) && !empty($reportItemId)) {
            if ($this->isValidBankReport($reportType) == false) {
                return false;
            }
            $data = [
                'report_type' => $reportType,
                'report_item_id' => $reportItemId,
                'order_number' => !empty($orderNumber) ? $orderNumber : 0,
                'payment_note' => inputPost('payment_note'),
                'receipt_path' => '',
                'user_id' => user()->id,
                'status' => "pending",
                'ip_address' => getIPAddress(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $uploadModel = new UploadModel();
            $file = $uploadModel->uploadReceipt('file');
            if (!empty($file) && !empty($file['path'])) {
                $data['receipt_path'] = $file['path'];
                $data['storage'] = $file['storage'];
            }
            return $this->builderBankTransfers->insert($data);
        }
        return false;
    }

    //get bank transfer notifications
    public function getBankTransfersCount()
    {
        $this->filterBankTransfers();
        return $this->builderBankTransfers->countAllResults();
    }

    //get paginated bank transfer notifications
    public function getBankTransfersPaginated($perPage, $offset)
    {
        $this->filterBankTransfers();
        return $this->builderBankTransfers->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter bank transfers
    public function filterBankTransfers()
    {
        $status = inputGet('status');
        $q = inputGet('q');
        if (!empty($status)) {
            $this->builderBankTransfers->where('status', $status);
        }
        if (!empty($q)) {
            $q = urldecode($q);
            $q = str_replace('#', '', $q);
            $this->builderBankTransfers->where('order_number', $q);
        }
        $this->builderBankTransfers->join('users', 'users.id = bank_transfers.user_id')
            ->select('bank_transfers.*, users.slug AS user_slug, users.username AS user_username');
    }

    //get bank transfer
    public function getBankTransfer($id)
    {
        return $this->builderBankTransfers->where('id', clrNum($id))->get()->getRow();
    }

    //get last bank transfer record
    public function getLastBankTransfer($reportType, $itemId)
    {
        if ($this->isValidBankReport($reportType) == false) {
            return false;
        }
        if ($reportType == 'order') {
            return $this->builderBankTransfers->where('report_type', cleanStr($reportType))->where('order_number', cleanStr($itemId))->orderBy('id DESC')->get(1)->getRow();
        } else {
            return $this->builderBankTransfers->where('report_type', cleanStr($reportType))->where('report_item_id', clrNum($itemId))->orderBy('id DESC')->get(1)->getRow();
        }
    }

    //update bank transfer status
    public function updateBankTransferStatus($transfer, $option)
    {
        if (!empty($transfer)) {
            return $this->builderBankTransfers->where('id', $transfer->id)->update(['status' => $option]);
        }
        return false;
    }

    //approve bank transfer by transaction type
    public function approveBankTransferByTransaction($itemId, $reportType)
    {
        $row = $this->builderBankTransfers->where('report_type', cleanStr($reportType))->where('report_item_id', clrNum($itemId))->get()->getRow();
        if (!empty($row)) {
            return $this->builderBankTransfers->where('report_type', cleanStr($reportType))->where('report_item_id', clrNum($itemId))->update(['status' => 'approved']);
        }

        return false;
    }

    //delete bank transfer
    public function deleteBankTransfer($id)
    {
        $transfer = $this->getBankTransfer($id);
        if (!empty($transfer)) {
            deleteStorageFile($transfer->receipt_path, $transfer->storage);
            return $this->builderBankTransfers->where('id', $transfer->id)->delete();
        }
        return false;
    }

    //check if report type is valid
    private function isValidBankReport($type)
    {
        if ($type != 'order' && $type != 'wallet_deposit' && $type != 'membership' && $type != 'promote') {
            return false;
        }
        return true;
    }

}
